<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileCompletionStep: string
{
    case Basics = 'basics';
    case Photos = 'photos';
    case AgeAndSex = 'age-sex';
    case BreedAndOrigin = 'breed-origin';
    case Appearance = 'appearance';
    case Character = 'character';
    case SocialPreferences = 'social-preferences';
    case Location = 'location';
    case Owners = 'owners';
    case Privacy = 'privacy';
    case Documents = 'documents';
    case Preview = 'preview';

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Basics;
    }

    public function number(): int
    {
        foreach (self::cases() as $index => $step) {
            if ($step === $this) {
                return $index + 1;
            }
        }

        return 1;
    }

    public function label(): string
    {
        return __("pet_profiles.completion.steps.{$this->value}.label");
    }

    public function description(): string
    {
        return __("pet_profiles.completion.steps.{$this->value}.description");
    }

    public function why(): string
    {
        return __("pet_profiles.completion.steps.{$this->value}.why");
    }

    public function icon(): string
    {
        return match ($this) {
            self::Basics => 'paw-print',
            self::Photos => 'images',
            self::AgeAndSex => 'calendar-days',
            self::BreedAndOrigin => 'scan-search',
            self::Appearance => 'eye',
            self::Character => 'sparkles',
            self::SocialPreferences => 'heart-handshake',
            self::Location => 'map-pin',
            self::Owners => 'users',
            self::Privacy => 'shield-check',
            self::Documents => 'badge-check',
            self::Preview => 'panel-top-open',
        };
    }

    public function next(): ?self
    {
        return self::cases()[$this->number()] ?? null;
    }
}
