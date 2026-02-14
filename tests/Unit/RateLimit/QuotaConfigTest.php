<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\ApiFamily;
use Pekral\GoogleConsole\RateLimit\QuotaConfig;

describe(QuotaConfig::class, function (): void {

    it('returns URL Inspection QPD and QPM', function (): void {
        $config = new QuotaConfig();
        $limits = $config->getLimits(ApiFamily::URL_INSPECTION);

        expect($limits['qpd'])->toBe(QuotaConfig::URL_INSPECTION_QPD)
            ->and($limits['qpm'])->toBe(QuotaConfig::URL_INSPECTION_QPM)
            ->and($limits['qps'])->toBeNull();
    });

    it('returns Search Analytics QPM only', function (): void {
        $config = new QuotaConfig();
        $limits = $config->getLimits(ApiFamily::SEARCH_ANALYTICS);

        expect($limits['qpd'])->toBeNull()
            ->and($limits['qpm'])->toBe(QuotaConfig::SEARCH_ANALYTICS_QPM)
            ->and($limits['qps'])->toBeNull();
    });

    it('returns Indexing QPD and QPM', function (): void {
        $config = new QuotaConfig();
        $limits = $config->getLimits(ApiFamily::INDEXING);

        expect($limits['qpd'])->toBe(QuotaConfig::INDEXING_QPD)
            ->and($limits['qpm'])->toBe(QuotaConfig::INDEXING_QPM)
            ->and($limits['qps'])->toBeNull();
    });

    it('returns Other QPM and QPS', function (): void {
        $config = new QuotaConfig();
        $limits = $config->getLimits(ApiFamily::OTHER);

        expect($limits['qpd'])->toBeNull()
            ->and($limits['qpm'])->toBe(QuotaConfig::OTHER_QPM)
            ->and($limits['qps'])->toBe(QuotaConfig::OTHER_QPS);
    });

    it('reports daily limit only for URL Inspection and Indexing', function (): void {
        $config = new QuotaConfig();

        expect($config->hasDailyLimit(ApiFamily::URL_INSPECTION))->toBeTrue()
            ->and($config->hasDailyLimit(ApiFamily::INDEXING))->toBeTrue()
            ->and($config->hasDailyLimit(ApiFamily::SEARCH_ANALYTICS))->toBeFalse()
            ->and($config->hasDailyLimit(ApiFamily::OTHER))->toBeFalse();
    });

    it('reports QPS limit only for Other', function (): void {
        $config = new QuotaConfig();

        expect($config->hasQpsLimit(ApiFamily::OTHER))->toBeTrue()
            ->and($config->hasQpsLimit(ApiFamily::URL_INSPECTION))->toBeFalse();
    });
});
