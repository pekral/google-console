<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\RateLimit;

use Pekral\GoogleConsole\Enum\ApiFamily;

/**
 * Default quota limits per API family (Search Console and Indexing API).
 * All limits match Google's documented defaults; per-project/per-site.
 */
final readonly class QuotaConfig
{

    public const int URL_INSPECTION_QPD = 2_000;

    public const int URL_INSPECTION_QPM = 600;

    public const int SEARCH_ANALYTICS_QPM = 1_200;

    public const int INDEXING_QPD = 200;

    public const int INDEXING_QPM = 60;

    public const int OTHER_QPM = 200;

    public const int OTHER_QPS = 20;

    public const int SECONDS_PER_MINUTE = 60;

    public const int SECONDS_PER_DAY = 86_400;

    /**
     * @return array{ qpd: int|null, qpm: int, qps: int|null } for the given API family
     */
    public function getLimits(ApiFamily $apiFamily): array
    {
        return match ($apiFamily) {
            ApiFamily::URL_INSPECTION => ['qpd' => self::URL_INSPECTION_QPD, 'qpm' => self::URL_INSPECTION_QPM, 'qps' => null],
            ApiFamily::SEARCH_ANALYTICS => ['qpd' => null, 'qpm' => self::SEARCH_ANALYTICS_QPM, 'qps' => null],
            ApiFamily::INDEXING => ['qpd' => self::INDEXING_QPD, 'qpm' => self::INDEXING_QPM, 'qps' => null],
            ApiFamily::OTHER => ['qpd' => null, 'qpm' => self::OTHER_QPM, 'qps' => self::OTHER_QPS],
        };
    }

    public function hasDailyLimit(ApiFamily $apiFamily): bool
    {
        return $this->getLimits($apiFamily)['qpd'] !== null;
    }

    public function hasQpsLimit(ApiFamily $apiFamily): bool
    {
        return $this->getLimits($apiFamily)['qps'] !== null;
    }

}
