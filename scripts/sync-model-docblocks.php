<?php

declare(strict_types=1);

use App\Models\CareJournal;
use App\Models\Listing;
use App\Models\SmartDevice;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';

$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();

if (! in_array('--write', $argv, true)) {
    fwrite(STDERR, "Usage: php scripts/sync-model-docblocks.php --write\n");
    exit(1);
}

$modelFiles = glob(dirname(__DIR__).'/app/Models/*.php') ?: [];
$aggregateProperties = [
    CareJournal::class => [
        'today_entries_count' => 'int',
        'open_tasks_count' => 'int',
        'overdue_tasks_count' => 'int',
        'unusual_entries_count' => 'int',
    ],
    Listing::class => [
        'item_rating' => 'float|null',
    ],
    SmartDevice::class => [
        'open_events_count' => 'int',
        'urgent_events_count' => 'int',
        'enabled_automations_count' => 'int',
    ],
];
$changedFiles = 0;

sort($modelFiles);

foreach ($modelFiles as $modelFile) {
    $class = 'App\\Models\\'.pathinfo($modelFile, PATHINFO_FILENAME);

    if (! is_subclass_of($class, Model::class)) {
        continue;
    }

    /** @var Model $model */
    $model = new $class;
    $properties = [];
    $relations = [];
    $casts = $model->getCasts();
    $dateAttributes = $model->getDates();

    foreach (Schema::getColumns($model->getTable()) as $column) {
        $name = (string) $column['name'];
        $type = modelPropertyType(
            $casts[$name] ?? null,
            (string) $column['type_name'],
            in_array($name, $dateAttributes, true),
        );

        if (($column['nullable'] ?? false) && ! str_contains($type, 'null')) {
            $type .= '|null';
        }

        $properties[$name] = $type;
    }

    $reflection = new ReflectionClass($class);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $class
            || $method->getNumberOfRequiredParameters() !== 0) {
            continue;
        }

        $returnType = $method->getReturnType();

        if (! $returnType instanceof ReflectionNamedType
            || $returnType->isBuiltin()
            || ! is_subclass_of($returnType->getName(), Relation::class)) {
            continue;
        }

        /** @var Relation<Model, Model, mixed> $relation */
        $relation = $method->invoke($model);
        $related = '\\'.$relation->getRelated()::class;
        $properties[$method->getName()] = relationPropertyType($relation, $related);
        $relations[$method->getName()] = [
            'return' => $returnType->getName(),
            'related' => $related,
        ];
    }

    foreach ($aggregateProperties[$class] ?? [] as $name => $type) {
        $properties[$name] = $type;
    }

    ksort($properties);

    $source = file_get_contents($modelFile);

    if ($source === false) {
        throw new RuntimeException("Unable to read {$modelFile}");
    }

    $classNeedle = 'class '.$reflection->getShortName().' extends ';
    $classOffset = strpos($source, $classNeedle);

    if ($classOffset === false) {
        throw new RuntimeException("Unable to locate {$classNeedle} in {$modelFile}");
    }

    $classDocblock = modelDocblock($properties, array_keys($relations));
    $source = replacePrecedingDocblock($source, $classOffset, $classDocblock);

    foreach ($relations as $methodName => $relation) {
        $returnClass = $relation['return'];
        $returnShortName = substr($returnClass, strrpos($returnClass, '\\') + 1);
        $methodNeedle = "    public function {$methodName}(): {$returnShortName}\n";
        $methodOffset = strpos($source, $methodNeedle);

        if ($methodOffset === false) {
            throw new RuntimeException("Unable to locate {$methodName}() in {$modelFile}");
        }

        $genericType = relationReturnType($returnClass, $relation['related']);
        $source = ensureReturnDocblock($source, $methodOffset, $genericType);
    }

    if ($source === file_get_contents($modelFile)) {
        continue;
    }

    if (file_put_contents($modelFile, $source) === false) {
        throw new RuntimeException("Unable to write {$modelFile}");
    }

    $changedFiles++;
}

fwrite(STDOUT, "Synchronized model docblocks in {$changedFiles} files.\n");

/**
 * @param  array<string, string>  $properties
 * @param  list<string>  $relationNames
 */
function modelDocblock(array $properties, array $relationNames): string
{
    $lines = ["/**\n"];

    foreach ($properties as $name => $type) {
        $tag = in_array($name, $relationNames, true) ? '@property-read' : '@property';
        $lines[] = " * {$tag} {$type} \${$name}\n";
    }

    $lines[] = " */\n";

    return implode('', $lines);
}

function replacePrecedingDocblock(string $source, int $offset, string $docblock): string
{
    $prefix = substr($source, 0, $offset);

    if (preg_match('/\/\*\*[\s\S]*?\*\/\R$/', $prefix, $matches, PREG_OFFSET_CAPTURE) === 1) {
        $existingOffset = $matches[0][1];

        return substr($source, 0, $existingOffset).$docblock.substr($source, $offset);
    }

    return substr($source, 0, $offset).$docblock.substr($source, $offset);
}

function ensureReturnDocblock(string $source, int $offset, string $genericType): string
{
    $prefix = substr($source, 0, $offset);

    if (preg_match(
        '/(    \/\*\*(?:(?!\*\/)[\s\S])*\*\/\R)$/',
        $prefix,
        $matches,
        PREG_OFFSET_CAPTURE,
    ) === 1) {
        $docblock = $matches[1][0];

        if (str_contains($docblock, '@return ')) {
            $replacement = preg_replace(
                '/@return\s+[^\r\n*]+/',
                '@return '.$genericType,
                $docblock,
                1,
            );
        } else {
            $replacement = str_replace(
                "     */\n",
                "     *\n     * @return {$genericType}\n     */\n",
                $docblock,
            );
        }

        if (! is_string($replacement)) {
            throw new RuntimeException('Unable to update a relationship docblock.');
        }

        $docblockOffset = $matches[1][1];

        return substr($source, 0, $docblockOffset).$replacement.substr($source, $offset);
    }

    $docblock = "    /** @return {$genericType} */\n";

    return substr($source, 0, $offset).$docblock.substr($source, $offset);
}

/**
 * @param  class-string|string|null  $cast
 */
function modelPropertyType(?string $cast, string $databaseType, bool $isDateAttribute): string
{
    if ($cast !== null && enum_exists($cast)) {
        return '\\'.$cast;
    }

    if ($cast !== null) {
        $normalizedCast = strtolower($cast);

        if (str_starts_with($normalizedCast, 'encrypted:')) {
            $normalizedCast = substr($normalizedCast, strlen('encrypted:'));
        }

        if (str_starts_with($normalizedCast, 'decimal:')) {
            return 'numeric-string';
        }

        return match ($normalizedCast) {
            'array', 'json' => 'array<array-key, mixed>',
            'bool', 'boolean' => 'bool',
            'collection' => '\Illuminate\Support\Collection<array-key, mixed>',
            'date', 'datetime', 'custom_datetime' => '\Illuminate\Support\Carbon',
            'float', 'double', 'real' => 'float',
            'hashed', 'encrypted', 'string' => 'string',
            'immutable_date', 'immutable_datetime', 'immutable_custom_datetime' => '\Carbon\CarbonImmutable',
            'int', 'integer', 'timestamp' => 'int',
            'object' => '\stdClass',
            default => modelDatabaseType($databaseType, $isDateAttribute),
        };
    }

    return modelDatabaseType($databaseType, $isDateAttribute);
}

function modelDatabaseType(string $databaseType, bool $isDateAttribute): string
{
    if ($isDateAttribute) {
        return '\Illuminate\Support\Carbon';
    }

    return match (strtolower($databaseType)) {
        'bigint', 'integer', 'smallint', 'tinyint' => 'int',
        'boolean' => 'bool',
        'date', 'datetime', 'timestamp' => '\Illuminate\Support\Carbon',
        'decimal', 'numeric' => 'numeric-string',
        'double', 'float', 'real' => 'float',
        default => 'string',
    };
}

function relationPropertyType(Relation $relation, string $related): string
{
    if ($relation instanceof HasMany
        || $relation instanceof HasManyThrough
        || $relation instanceof BelongsToMany
        || $relation instanceof MorphMany
        || $relation instanceof MorphToMany) {
        return "\Illuminate\Database\Eloquent\Collection<int, {$related}>";
    }

    return "{$related}|null";
}

function relationReturnType(string $returnClass, string $related): string
{
    $returnShortName = substr($returnClass, strrpos($returnClass, '\\') + 1);

    if (is_a($returnClass, HasManyThrough::class, true)
        || is_a($returnClass, HasOneThrough::class, true)) {
        return "{$returnShortName}<{$related}, \Illuminate\Database\Eloquent\Model, \$this>";
    }

    if (is_a($returnClass, BelongsTo::class, true)
        || is_a($returnClass, HasMany::class, true)
        || is_a($returnClass, HasOne::class, true)
        || is_a($returnClass, BelongsToMany::class, true)
        || is_a($returnClass, MorphMany::class, true)
        || is_a($returnClass, MorphOne::class, true)
        || is_a($returnClass, MorphTo::class, true)
        || is_a($returnClass, MorphToMany::class, true)) {
        return "{$returnShortName}<{$related}, \$this>";
    }

    throw new LogicException("Unsupported relationship type {$returnClass}");
}
