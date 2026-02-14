<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

final readonly class SitemapContentEntry
{

    public function __construct(public string $type, public int $submitted) {
    }

}
