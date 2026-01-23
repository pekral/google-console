<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\Site;

describe(Site::class, function (): void {

    it('creates site from api response', function (): void {
        $data = [
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteOwner',
        ];

        $site = Site::fromApiResponse($data);

        expect($site->siteUrl)->toBe('https://example.com/')
            ->and($site->permissionLevel)->toBe('siteOwner');
    });

    it('creates site with default values for missing data', function (): void {
        $site = Site::fromApiResponse([]);

        expect($site->siteUrl)->toBe('')
            ->and($site->permissionLevel)->toBe('');
    });

    it('returns true for isOwner when permission level is siteOwner', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteOwner',
        ]);

        expect($site->isOwner())->toBeTrue();
    });

    it('returns false for isOwner when permission level is not siteOwner', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteFullUser',
        ]);

        expect($site->isOwner())->toBeFalse();
    });

    it('returns true for hasFullAccess when permission is siteOwner', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteOwner',
        ]);

        expect($site->hasFullAccess())->toBeTrue();
    });

    it('returns true for hasFullAccess when permission is siteFullUser', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteFullUser',
        ]);

        expect($site->hasFullAccess())->toBeTrue();
    });

    it('returns false for hasFullAccess when permission is restricted', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteRestrictedUser',
        ]);

        expect($site->hasFullAccess())->toBeFalse();
    });

    it('converts to array with all properties', function (): void {
        $site = Site::fromApiResponse([
            'siteUrl' => 'https://example.com/',
            'permissionLevel' => 'siteOwner',
        ]);

        $array = $site->toArray();

        expect($array['siteUrl'])->toBe('https://example.com/')
            ->and($array['permissionLevel'])->toBe('siteOwner')
            ->and($array['isOwner'])->toBeTrue()
            ->and($array['hasFullAccess'])->toBeTrue();
    });
});
