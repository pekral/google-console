<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\Sitemap;
use Pekral\GoogleConsole\DTO\SitemapContentEntry;

describe(Sitemap::class, function (): void {

    it('exposes path and type', function (): void {
        $sitemap = new Sitemap(
            path: 'https://example.com/sitemap.xml',
            lastSubmitted: null,
            lastDownloaded: null,
            errors: 0,
            warnings: 0,
            isPending: false,
            isSitemapsIndex: false,
            type: 'sitemap',
            contents: [],
        );

        expect($sitemap->path)->toBe('https://example.com/sitemap.xml')
            ->and($sitemap->type)->toBe('sitemap')
            ->and($sitemap->contents)->toBe([]);
    });

    it('toArray returns expected shape', function (): void {
        $lastSubmitted = new DateTimeImmutable('2024-06-01 12:00:00');
        $sitemap = new Sitemap(
            path: 'https://example.com/sitemap.xml',
            lastSubmitted: $lastSubmitted,
            lastDownloaded: null,
            errors: 1,
            warnings: 2,
            isPending: true,
            isSitemapsIndex: false,
            type: 'sitemap',
            contents: [new SitemapContentEntry(type: 'web', submitted: 100)],
        );

        $array = $sitemap->toArray();

        expect($array)->toHaveKeys(['path', 'lastSubmitted', 'lastDownloaded', 'errors', 'warnings', 'isPending', 'isSitemapsIndex', 'type', 'contents'])
            ->and($array['path'])->toBe('https://example.com/sitemap.xml')
            ->and($array['errors'])->toBe(1)
            ->and($array['warnings'])->toBe(2)
            ->and($array['isPending'])->toBeTrue()
            ->and($array['contents'])->toHaveCount(1)
            ->and($array['contents'][0])->toBe(['type' => 'web', 'submitted' => 100]);
    });
});
