<?php

namespace Wnx\AlfredEmojiPack\DTOs;

class Snippet
{
    public function __construct(
        private string $snippet,
        private string $uuid,
        private string $name,
        private string $keyword,
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
