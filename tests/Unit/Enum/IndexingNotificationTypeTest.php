<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\IndexingNotificationType;

describe(IndexingNotificationType::class, function (): void {

    it('has correct case values', function (): void {
        expect(IndexingNotificationType::URL_UPDATED->value)->toBe('URL_UPDATED')
            ->and(IndexingNotificationType::URL_DELETED->value)->toBe('URL_DELETED');
    });

    it('can be created from string', function (): void {
        $updated = IndexingNotificationType::from('URL_UPDATED');
        $deleted = IndexingNotificationType::from('URL_DELETED');

        expect($updated)->toBe(IndexingNotificationType::URL_UPDATED)
            ->and($deleted)->toBe(IndexingNotificationType::URL_DELETED);
    });

    it('throws exception for invalid value', function (): void {
        IndexingNotificationType::from('INVALID');
    })->throws(ValueError::class);
});
