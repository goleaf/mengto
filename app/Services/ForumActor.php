<?php

namespace App\Services;

class ForumActor
{
    public function key(): string
    {
        return 'mia-carter';
    }

    /**
     * @return array{key: string, name: string, initials: string, role: string}
     */
    public function identity(): array
    {
        return [
            'key' => $this->key(),
            'name' => 'Mia Carter',
            'initials' => 'MC',
            'role' => 'Scout and Nori owner',
        ];
    }
}
