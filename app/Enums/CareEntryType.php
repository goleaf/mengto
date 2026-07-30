<?php

namespace App\Enums;

enum CareEntryType: string
{
    case Feeding = 'feeding';
    case Water = 'water';
    case Walk = 'walk';
    case Toilet = 'toilet';
    case Sleep = 'sleep';
    case Activity = 'activity';
    case Training = 'training';
    case Behavior = 'behavior';
    case Grooming = 'grooming';
    case Environment = 'environment';
    case Observation = 'observation';

    public function label(): string
    {
        return match ($this) {
            self::Feeding => 'Feeding',
            self::Water => 'Water',
            self::Walk => 'Walk',
            self::Toilet => 'Toilet',
            self::Sleep => 'Sleep',
            self::Activity => 'Activity',
            self::Training => 'Training',
            self::Behavior => 'Behavior',
            self::Grooming => 'Grooming',
            self::Environment => 'Environment',
            self::Observation => 'Observation',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Feeding => 'utensils',
            self::Water => 'droplets',
            self::Walk => 'footprints',
            self::Toilet => 'sparkles',
            self::Sleep => 'moon',
            self::Activity => 'dumbbell',
            self::Training => 'graduation-cap',
            self::Behavior => 'scan-heart',
            self::Grooming => 'brush-cleaning',
            self::Environment => 'thermometer-sun',
            self::Observation => 'notebook-pen',
        };
    }

    public function section(): string
    {
        return match ($this) {
            self::Feeding => 'feeding',
            self::Water => 'water',
            self::Walk => 'walks',
            self::Toilet => 'toilet',
            self::Sleep => 'sleep',
            self::Activity, self::Training => 'activity',
            self::Behavior, self::Observation => 'observations',
            self::Grooming, self::Environment => 'care',
        };
    }
}
