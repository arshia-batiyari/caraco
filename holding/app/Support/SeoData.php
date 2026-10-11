<?php

namespace App\Support;

class SeoData
{
    public function __construct(
        public string $title,
        public string $description = '',
        public string $canonical = '',
        public ?string $image = null,
        public string $robots = 'index, follow',
    ) {}
}
