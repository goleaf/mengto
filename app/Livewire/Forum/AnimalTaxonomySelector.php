<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Models\Taxon;
use App\Models\TaxonName;
use App\Models\TaxonVersion;
use App\Services\TaxonIdentity;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

final class AnimalTaxonomySelector extends Component
{
    private const ALLOWED_INPUT_NAMES = [
        'taxon_id',
        'taxon_ids[]',
    ];

    private TaxonIdentity $identity;

    public string $search = '';

    /** @var list<int> */
    #[Modelable]
    public array $selectedTaxonIds = [];

    public string $context = 'taxa';

    #[Locked]
    public string $inputName = 'taxon_ids[]';

    #[Locked]
    public int $selectionLimit = 5;

    public function boot(TaxonIdentity $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * @param  list<int|string>|int|string|null  $selected
     */
    public function mount(
        array|int|string|null $selected = [],
        string $inputName = 'taxon_ids[]',
        int $selectionLimit = 5,
    ): void {
        $this->inputName = in_array($inputName, self::ALLOWED_INPUT_NAMES, true)
            ? $inputName
            : 'taxon_ids[]';
        $this->selectionLimit = min(max($selectionLimit, 1), 5);

        $selectedIds = is_array($selected) ? $selected : [$selected];

        $this->selectedTaxonIds = collect($selectedIds)
            ->filter(static fn (mixed $id): bool => is_int($id) || is_string($id))
            ->map(static fn (int|string $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->take($this->selectionLimit)
            ->values()
            ->all();
    }

    /** @return list<array<string, int|string|bool|null>> */
    #[Computed]
    public function results(): array
    {
        $query = $this->identity->normalizeName($this->search);

        if (mb_strlen($query) < 2) {
            return [];
        }

        return TaxonName::query()
            ->select([
                'id',
                'taxon_id',
                'locale',
                'name',
                'name_type',
                'is_preferred',
                'is_verified',
            ])
            ->search($query)
            ->whereHas(
                'taxon',
                fn ($builder) => $builder
                    ->where('is_active', true)
                    ->whereNull('archived_at'),
            )
            ->with([
                'taxon' => fn ($builder) => $builder->select([
                    'id',
                    'stable_key',
                    'accepted_taxon_id',
                    'resolution_status',
                    'requires_review',
                    'is_active',
                    'archived_at',
                ]),
                'taxon.activeVersion' => fn ($builder) => $builder->select([
                    'id',
                    'taxon_id',
                    'rank',
                    'scientific_name',
                    'parent_taxon_id',
                    'is_active_version',
                ]),
            ])
            ->orderByDesc('is_preferred')
            ->orderByDesc('is_verified')
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->unique('taxon_id')
            ->take(12)
            ->map(static function (TaxonName $name): array {
                $activeVersion = $name->taxon->activeVersion;

                return [
                    'id' => $name->taxon_id,
                    'name' => $name->name,
                    'scientific_name' => $activeVersion instanceof TaxonVersion
                        ? $activeVersion->scientific_name
                        : $name->name,
                    'rank' => $activeVersion instanceof TaxonVersion
                        ? $activeVersion->rank
                        : __('taxonomy.unknown_rank'),
                    'is_synonym' => $name->taxon->accepted_taxon_id !== null,
                    'requires_review' => $name->taxon->requires_review,
                ];
            })
            ->values()
            ->all();
    }

    /** @return list<array<string, int|string|bool|null>> */
    #[Computed]
    public function selectedTaxa(): array
    {
        if ($this->selectedTaxonIds === []) {
            return [];
        }

        $locale = App::currentLocale();
        $fallback = (string) config('app.fallback_locale', 'en');

        return Taxon::query()
            ->active()
            ->whereKey($this->selectedTaxonIds)
            ->select([
                'id',
                'stable_key',
                'accepted_taxon_id',
                'resolution_status',
                'requires_review',
                'is_active',
                'archived_at',
            ])
            ->with([
                'activeVersion' => fn ($builder) => $builder->select([
                    'id',
                    'taxon_id',
                    'rank',
                    'scientific_name',
                    'parent_taxon_id',
                    'is_active_version',
                ]),
                'names' => fn ($builder) => $builder
                    ->select([
                        'id',
                        'taxon_id',
                        'locale',
                        'name',
                        'name_type',
                        'is_preferred',
                        'is_verified',
                    ])
                    ->where('is_active', true)
                    ->whereIn('locale', [$locale, $fallback])
                    ->orderByDesc('is_verified')
                    ->orderByDesc('is_preferred'),
            ])
            ->get()
            ->sortBy(fn (Taxon $taxon): int => (int) array_search(
                $taxon->id,
                $this->selectedTaxonIds,
                true,
            ))
            ->map(static function (Taxon $taxon) use ($fallback, $locale): array {
                $preferred = $taxon->names->firstWhere('locale', $locale)
                    ?? $taxon->names->firstWhere('locale', $fallback);
                $activeVersion = $taxon->activeVersion;

                return [
                    'id' => $taxon->id,
                    'name' => $preferred instanceof TaxonName
                        ? $preferred->name
                        : ($activeVersion instanceof TaxonVersion
                            ? $activeVersion->scientific_name
                            : __('taxonomy.unidentified')),
                    'scientific_name' => $activeVersion instanceof TaxonVersion
                        ? $activeVersion->scientific_name
                        : null,
                    'rank' => $activeVersion instanceof TaxonVersion
                        ? $activeVersion->rank
                        : __('taxonomy.unknown_rank'),
                    'requires_review' => $taxon->requires_review,
                ];
            })
            ->values()
            ->all();
    }

    public function selectTaxon(int $taxonId): void
    {
        if (
            $this->selectionLimit > 1
            && count($this->selectedTaxonIds) >= $this->selectionLimit
        ) {
            $this->addError('selectedTaxonIds', __('taxonomy.selection_limit'));

            return;
        }

        $taxon = Taxon::query()
            ->active()
            ->select([
                'id',
                'accepted_taxon_id',
                'is_active',
                'archived_at',
            ])
            ->findOrFail($taxonId);
        $selectedId = $taxon->accepted_taxon_id ?? $taxon->id;
        $this->selectedTaxonIds = $this->selectionLimit === 1
            ? [$selectedId]
            : collect([
                ...$this->selectedTaxonIds,
                $selectedId,
            ])->unique()->values()->all();
        $this->search = '';
        unset($this->results, $this->selectedTaxa);
    }

    public function removeTaxon(int $taxonId): void
    {
        $this->selectedTaxonIds = collect($this->selectedTaxonIds)
            ->reject(static fn (int $id): bool => $id === $taxonId)
            ->values()
            ->all();
        unset($this->selectedTaxa);
    }

    public function markUnidentified(): void
    {
        $this->selectedTaxonIds = [];
        $this->context = 'unidentified';
        unset($this->selectedTaxa);
    }

    public function render(): View
    {
        return view('livewire.forum.animal-taxonomy-selector');
    }
}
