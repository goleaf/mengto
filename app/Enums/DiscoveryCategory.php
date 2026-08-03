<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscoveryCategory: string
{
    case All = 'all';
    case Events = 'events';
    case Groups = 'groups';
    case Places = 'places';
    case Experts = 'experts';
    case Pets = 'pets';

    public function label(): string
    {
        return __("discovery.categories.{$this->value}.label");
    }

    public function description(): string
    {
        return __("discovery.categories.{$this->value}.description");
    }

    public function icon(): string
    {
        return match ($this) {
            self::All => 'compass',
            self::Events => 'calendar-days',
            self::Groups => 'users-round',
            self::Places => 'map-pin',
            self::Experts => 'stethoscope',
            self::Pets => 'paw-print',
        };
    }

    public function directoryRoute(): string
    {
        return match ($this) {
            self::All => 'discover.index',
            self::Events => 'meetups.index',
            self::Groups => 'forum.groups.index',
            self::Places => 'places.index',
            self::Experts => 'experts.index',
            self::Pets => 'pets.index',
        };
    }

    /** @return list<self> */
    public static function recommendationCategories(): array
    {
        return [self::Events, self::Groups, self::Places, self::Experts, self::Pets];
    }

    /** @return list<string> */
    public static function recommendationValues(): array
    {
        return array_map(
            static fn (self $category): string => $category->value,
            self::recommendationCategories(),
        );
    }
}
