<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceType: string
{
    case GpsTracker = 'gps-tracker';
    case ActivityTracker = 'activity-tracker';
    case Feeder = 'feeder';
    case Waterer = 'waterer';
    case Camera = 'camera';
    case LitterBox = 'litter-box';
    case Scale = 'scale';
    case TemperatureSensor = 'temperature-sensor';
    case HumiditySensor = 'humidity-sensor';
    case SmartDoor = 'smart-door';

    public function label(): string
    {
        return __("devices.type.{$this->value}");
    }

    public function icon(): string
    {
        return match ($this) {
            self::GpsTracker => 'map-pin',
            self::ActivityTracker => 'activity',
            self::Feeder => 'utensils',
            self::Waterer => 'droplets',
            self::Camera => 'video',
            self::LitterBox => 'sparkles',
            self::Scale => 'scale',
            self::TemperatureSensor => 'thermometer',
            self::HumiditySensor => 'cloud-rain',
            self::SmartDoor => 'door-open',
        };
    }

    public function supportsLocation(): bool
    {
        return $this === self::GpsTracker;
    }

    public function supportsCamera(): bool
    {
        return $this === self::Camera;
    }

    public function supportsCommands(): bool
    {
        return in_array($this, [
            self::GpsTracker,
            self::Feeder,
            self::Waterer,
            self::Camera,
            self::LitterBox,
            self::SmartDoor,
        ], true);
    }
}
