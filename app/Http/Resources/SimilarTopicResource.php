<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimilarTopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this['title'],
            'status' => $this['status_label'],
            'category' => $this['category_label'],
            'answers' => $this['answers_count'],
            'url' => route('forum.topics.show', $this['slug']),
        ];
    }
}
