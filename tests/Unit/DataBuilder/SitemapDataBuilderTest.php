<?php

declare(strict_types = 1);

use Google\Service\Webmasters\WmxSitemap;
use Google\Service\Webmasters\WmxSitemapContent;
use Pekral\GoogleConsole\DataBuilder\SitemapDataBuilder;
use Pekral\GoogleConsole\DTO\Sitemap;
use Pekral\GoogleConsole\DTO\SitemapContentEntry;

describe(SitemapDataBuilder::class, function (): void {

    it('maps WmxSitemap to Sitemap DTO', function (): void {
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn('2024-06-01T12:00:00.000Z');
        $wmx->shouldReceive('getLastDownloaded')->andReturn('2024-06-02T08:00:00.000Z');
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(1);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap)->toBeInstanceOf(Sitemap::class)
            ->and($sitemap->path)->toBe('https://example.com/sitemap.xml')
            ->and($sitemap->errors)->toBe(0)
            ->and($sitemap->warnings)->toBe(1)
            ->and($sitemap->isPending)->toBeFalse()
            ->and($sitemap->type)->toBe('sitemap')
            ->and($sitemap->lastSubmitted)->toBeInstanceOf(DateTimeImmutable::class)
            ->and($sitemap->lastDownloaded)->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('maps contents to SitemapContentEntry', function (): void {
        $content = Mockery::mock(WmxSitemapContent::class);
        $content->shouldReceive('getType')->andReturn('web');
        $content->shouldReceive('getSubmitted')->andReturn(50);

        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn(null);
        $wmx->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(0);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([$content]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->contents)->toHaveCount(1)
            ->and($sitemap->contents[0])->toBeInstanceOf(SitemapContentEntry::class)
            ->and($sitemap->contents[0]->type)->toBe('web')
            ->and($sitemap->contents[0]->submitted)->toBe(50);
    });

    it('fromWmxSitemapArray returns list of Sitemap', function (): void {
        $wmx1 = Mockery::mock(WmxSitemap::class);
        $wmx1->shouldReceive('getPath')->andReturn('https://example.com/sitemap1.xml');
        $wmx1->shouldReceive('getLastSubmitted')->andReturn(null);
        $wmx1->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx1->shouldReceive('getErrors')->andReturn(0);
        $wmx1->shouldReceive('getWarnings')->andReturn(0);
        $wmx1->shouldReceive('getIsPending')->andReturn(false);
        $wmx1->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx1->shouldReceive('getType')->andReturn('sitemap');
        $wmx1->shouldReceive('getContents')->andReturn([]);

        $wmx2 = Mockery::mock(WmxSitemap::class);
        $wmx2->shouldReceive('getPath')->andReturn('https://example.com/sitemap2.xml');
        $wmx2->shouldReceive('getLastSubmitted')->andReturn(null);
        $wmx2->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx2->shouldReceive('getErrors')->andReturn(0);
        $wmx2->shouldReceive('getWarnings')->andReturn(0);
        $wmx2->shouldReceive('getIsPending')->andReturn(false);
        $wmx2->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx2->shouldReceive('getType')->andReturn('sitemap');
        $wmx2->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $list = $builder->fromWmxSitemapArray([$wmx1, $wmx2]);

        expect($list)->toHaveCount(2)
            ->and($list[0]->path)->toBe('https://example.com/sitemap1.xml')
            ->and($list[1]->path)->toBe('https://example.com/sitemap2.xml');
    });

    it('returns empty list for empty array', function (): void {
        $builder = new SitemapDataBuilder();
        $list = $builder->fromWmxSitemapArray([]);

        expect($list)->toBe([]);
    });

    it('handles null lastSubmitted and lastDownloaded', function (): void {
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn(null);
        $wmx->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(0);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->lastSubmitted)->toBeNull()
            ->and($sitemap->lastDownloaded)->toBeNull();
    });

    it('parses DateTimeInterface for lastSubmitted', function (): void {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn($date);
        $wmx->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(0);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->lastSubmitted)->toEqual($date);
    });

    it('returns null when date string is invalid', function (): void {
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn('not-a-valid-datetime-string-at-all');
        $wmx->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(0);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->lastSubmitted)->toBeNull();
    });

    it('handles non-string path and type and non-numeric errors', function (): void {
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn(new stdClass());
        $wmx->shouldReceive('getLastSubmitted')->andReturn(null);
        $wmx->shouldReceive('getLastDownloaded')->andReturn(null);
        $wmx->shouldReceive('getErrors')->andReturn(null);
        $wmx->shouldReceive('getWarnings')->andReturn('two');
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn(123);
        $wmx->shouldReceive('getContents')->andReturn(null);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->path)->toBe('')
            ->and($sitemap->type)->toBe('')
            ->and($sitemap->errors)->toBe(0)
            ->and($sitemap->warnings)->toBe(0)
            ->and($sitemap->lastSubmitted)->toBeNull()
            ->and($sitemap->contents)->toBe([]);
    });

    it('returns null from parseDate when value is not string or datetime', function (): void {
        $wmx = Mockery::mock(WmxSitemap::class);
        $wmx->shouldReceive('getPath')->andReturn('https://example.com/sitemap.xml');
        $wmx->shouldReceive('getLastSubmitted')->andReturn(12_345);
        $wmx->shouldReceive('getLastDownloaded')->andReturn([]);
        $wmx->shouldReceive('getErrors')->andReturn(0);
        $wmx->shouldReceive('getWarnings')->andReturn(0);
        $wmx->shouldReceive('getIsPending')->andReturn(false);
        $wmx->shouldReceive('getIsSitemapsIndex')->andReturn(false);
        $wmx->shouldReceive('getType')->andReturn('sitemap');
        $wmx->shouldReceive('getContents')->andReturn([]);

        $builder = new SitemapDataBuilder();
        $sitemap = $builder->fromWmxSitemap($wmx);

        expect($sitemap->lastSubmitted)->toBeNull()
            ->and($sitemap->lastDownloaded)->toBeNull();
    });
});
