<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\RateLimit;

use Closure;
use Pekral\GoogleConsole\Enum\ApiFamily;

/**
 * Enforces Search Console and Indexing API quotas per (apiFamily, siteUrl) or (apiFamily).
 * Uses token bucket for QPM (and QPS for "other") and daily budget for QPD.
 * When a limit is reached, throws QuotaExceededException (fail-fast).
 */
final readonly class RateLimiter implements RateLimiterInterface
{

    private DailyBudget $urlInspectionDaily;

    private DailyBudget $indexingDaily;

    private TokenBucket $urlInspectionQpm;

    private TokenBucket $searchAnalyticsQpm;

    private TokenBucket $indexingQpm;

    private TokenBucket $otherQpm;

    private TokenBucket $otherQps;

    /**
     * @param \Closure(): int|null $clockSeconds Monotonic clock in seconds (null = use time())
     * @param \Closure(): string|null $dateString Current date 'Y-m-d' (null = use date('Y-m-d'))
     */
    public function __construct(?Closure $clockSeconds = null, ?Closure $dateString = null) {
        $clock = $clockSeconds ?? static fn (): int => time();
        $date = $dateString ?? static fn (): string => date('Y-m-d');

        $this->urlInspectionDaily = new DailyBudget(QuotaConfig::URL_INSPECTION_QPD, $date);
        $this->indexingDaily = new DailyBudget(QuotaConfig::INDEXING_QPD, $date);

        $this->urlInspectionQpm = new TokenBucket(
            QuotaConfig::URL_INSPECTION_QPM,
            QuotaConfig::SECONDS_PER_MINUTE,
            QuotaConfig::URL_INSPECTION_QPM,
            $clock,
        );
        $this->searchAnalyticsQpm = new TokenBucket(
            QuotaConfig::SEARCH_ANALYTICS_QPM,
            QuotaConfig::SECONDS_PER_MINUTE,
            QuotaConfig::SEARCH_ANALYTICS_QPM,
            $clock,
        );
        $this->indexingQpm = new TokenBucket(QuotaConfig::INDEXING_QPM, QuotaConfig::SECONDS_PER_MINUTE, QuotaConfig::INDEXING_QPM, $clock);
        $this->otherQpm = new TokenBucket(QuotaConfig::OTHER_QPM, QuotaConfig::SECONDS_PER_MINUTE, QuotaConfig::OTHER_QPM, $clock);
        $this->otherQps = new TokenBucket(QuotaConfig::OTHER_QPS, 1, QuotaConfig::OTHER_QPS, $clock);
    }

    /**
     * Consumes one request from the appropriate quota(s). Key: (apiFamily, siteUrl) for URL Inspection and Search Analytics; (apiFamily) for Indexing and Other.
     *
     * @throws \Pekral\GoogleConsole\Exception\QuotaExceededException When QPD or QPM (or QPS for Other) is exceeded
     */
    public function consume(ApiFamily $apiFamily, ?string $siteUrl = null): void
    {
        $key = $this->buildKey($apiFamily, $siteUrl);

        match ($apiFamily) {
            ApiFamily::URL_INSPECTION => $this->consumeUrlInspection($key),
            ApiFamily::SEARCH_ANALYTICS => $this->searchAnalyticsQpm->consume($key),
            ApiFamily::INDEXING => $this->consumeIndexing($key),
            ApiFamily::OTHER => $this->consumeOther($key),
        };
    }

    private function buildKey(ApiFamily $apiFamily, ?string $siteUrl): string
    {
        if ($siteUrl !== null && $siteUrl !== '') {
            return $apiFamily->value . ':' . $siteUrl;
        }

        return $apiFamily->value;
    }

    private function consumeUrlInspection(string $key): void
    {
        $this->urlInspectionDaily->consume($key);
        $this->urlInspectionQpm->consume($key);
    }

    private function consumeIndexing(string $key): void
    {
        $this->indexingDaily->consume($key);
        $this->indexingQpm->consume($key);
    }

    private function consumeOther(string $key): void
    {
        $this->otherQps->consume($key);
        $this->otherQpm->consume($key);
    }

}
