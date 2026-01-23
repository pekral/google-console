<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;

describe(IndexingResult::class, function (): void {

    it('creates result with all properties', function (): void {
        $notifyTime = new DateTimeImmutable('2024-01-15 10:30:00');

        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_UPDATED,
            notifyTime: $notifyTime,
        );

        expect($result->url)->toBe('https://example.com/page')
            ->and($result->type)->toBe(IndexingNotificationType::URL_UPDATED)
            ->and($result->notifyTime)->toBe($notifyTime);
    });

    it('creates result with null notify time', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_DELETED,
        );

        expect($result->url)->toBe('https://example.com/page')
            ->and($result->type)->toBe(IndexingNotificationType::URL_DELETED)
            ->and($result->notifyTime)->toBeNull();
    });

    it('converts to array with notify time', function (): void {
        $notifyTime = new DateTimeImmutable('2024-01-15 10:30:00');

        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_UPDATED,
            notifyTime: $notifyTime,
        );

        $array = $result->toArray();

        expect($array)->toBe([
            'notifyTime' => '2024-01-15 10:30:00',
            'type' => 'URL_UPDATED',
            'url' => 'https://example.com/page',
        ]);
    });

    it('converts to array with null notify time', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_DELETED,
        );

        $array = $result->toArray();

        expect($array)->toBe([
            'notifyTime' => null,
            'type' => 'URL_DELETED',
            'url' => 'https://example.com/page',
        ]);
    });
});
