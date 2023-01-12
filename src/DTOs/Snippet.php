<?php

namespace Wnx\AlfredEmojiPack\DTOs;

class Snippet
{
    public function __construct(
        private readonly string $snippet,
        private readonly string $uuid,
        private readonly string $name,
        private readonly string $keyword,
    ) {
    }

    public function toArray(): array
    {
        return [
            'alfredsnippet' => [
                'snippet' => $this->snippet,
                'uuid' => $this->uuid,
                'name' => $this->name,
                'keyword' => $this->keyword,
            ],
        ];
    }
}
