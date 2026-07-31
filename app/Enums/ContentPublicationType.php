<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentPublicationType: string
{
    case Post = 'post';
    case Article = 'article';
    case Photo = 'photo';
    case Album = 'album';
    case Video = 'video';
    case ShortVideo = 'short-video';
    case Audio = 'audio';
    case Story = 'story';
    case Poll = 'poll';
    case Question = 'question';
    case Event = 'event';
    case Adoption = 'adoption';
    case Urgent = 'urgent';
    case Professional = 'professional';
    case Advertisement = 'advertisement';
    case Fundraiser = 'fundraiser';
    case Memorial = 'memorial';
    case Link = 'link';

    public function label(): string
    {
        return __("content.publication_types.{$this->value}");
    }
}
