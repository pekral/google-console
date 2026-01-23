<?php

declare(strict_types = 1);

use Google\Service\Webmasters\WmxSite;
use Pekral\GoogleConsole\DataBuilder\SiteDataBuilder;
use Pekral\GoogleConsole\DTO\Site;

describe(SiteDataBuilder::class, function (): void {

    it('maps WmxSite to Site DTO', function (): void {
        $wmxSite = Mockery::mock(WmxSite::class);
        $wmxSite->shouldReceive('getSiteUrl')->andReturn('https://example.com/');
        $wmxSite->shouldReceive('getPermissionLevel')->andReturn('siteOwner');

        $builder = new SiteDataBuilder();
        $site = $builder->fromWmxSite($wmxSite);

        expect($site)->toBeInstanceOf(Site::class)
            ->and($site->siteUrl)->toBe('https://example.com/')
            ->and($site->permissionLevel)->toBe('siteOwner');
    });

    it('maps array of WmxSite to array of Site DTO', function (): void {
        $wmxSite1 = Mockery::mock(WmxSite::class);
        $wmxSite1->shouldReceive('getSiteUrl')->andReturn('https://example.com/');
        $wmxSite1->shouldReceive('getPermissionLevel')->andReturn('siteOwner');

        $wmxSite2 = Mockery::mock(WmxSite::class);
        $wmxSite2->shouldReceive('getSiteUrl')->andReturn('https://example.org/');
        $wmxSite2->shouldReceive('getPermissionLevel')->andReturn('siteFullUser');

        $builder = new SiteDataBuilder();
        $sites = $builder->fromWmxSiteArray([$wmxSite1, $wmxSite2]);

        expect($sites)->toHaveCount(2)
            ->and($sites[0]->siteUrl)->toBe('https://example.com/')
            ->and($sites[1]->siteUrl)->toBe('https://example.org/');
    });

    it('returns empty array when mapping empty WmxSite array', function (): void {
        $builder = new SiteDataBuilder();
        $sites = $builder->fromWmxSiteArray([]);

        expect($sites)->toBe([]);
    });

    it('handles null values from WmxSite', function (): void {
        $wmxSite = Mockery::mock(WmxSite::class);
        $wmxSite->shouldReceive('getSiteUrl')->andReturn(null);
        $wmxSite->shouldReceive('getPermissionLevel')->andReturn(null);

        $builder = new SiteDataBuilder();
        $site = $builder->fromWmxSite($wmxSite);

        expect($site->siteUrl)->toBe('')
            ->and($site->permissionLevel)->toBe('');
    });
});
