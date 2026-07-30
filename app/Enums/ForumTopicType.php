<?php

namespace App\Enums;

enum ForumTopicType: string
{
    case Question = 'question';
    case Discussion = 'discussion';
    case Recommendation = 'recommendation';
    case CaseStudy = 'case-study';
    case Journal = 'journal';
    case Guide = 'guide';
    case Comparison = 'comparison';
    case Poll = 'poll';
    case ExpertQa = 'expert-qa';
    case Update = 'update';
    case Support = 'support';
    case LostPet = 'lost-pet';

    public function label(): string
    {
        return match ($this) {
            self::Question => 'Question',
            self::Discussion => 'Discussion',
            self::Recommendation => 'Recommendation request',
            self::CaseStudy => 'Case review',
            self::Journal => 'Progress journal',
            self::Guide => 'Step-by-step guide',
            self::Comparison => 'Comparison',
            self::Poll => 'Poll',
            self::ExpertQa => 'Ask an expert',
            self::Update => 'News or update',
            self::Support => 'Support',
            self::LostPet => 'Lost pet coordination',
        };
    }
}
