<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventRecurrenceFrequency: string
{
    case FixedOccurrences = 'fixed_occurrences';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case SelectedWeekdays = 'selected_weekdays';
    case Monthly = 'monthly';
    case CustomInterval = 'custom_interval';

    public function label(): string
    {
        return __('forum_events.recurrence_frequencies.'.$this->value);
    }
}
