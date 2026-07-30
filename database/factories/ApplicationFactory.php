<?php

declare(strict_types=1);

namespace Database\Factories;

use BackedEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @template TModel of Model
 *
 * @extends Factory<TModel>
 */
abstract class ApplicationFactory extends Factory
{
    public function withEnum(string $attribute, BackedEnum $value): static
    {
        $cast = $this->newModel()->getCasts()[$attribute] ?? null;

        if ($cast !== $value::class) {
            throw new InvalidArgumentException(
                sprintf('%s is not cast to %s.', $attribute, $value::class),
            );
        }

        return $this->state(fn (): array => [
            $attribute => $value,
        ]);
    }
}
