<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\RateLimit;

use Pekral\GoogleConsole\Enum\ApiFamily;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming.SuperfluousSuffix -- conventional name for interface
interface RateLimiterInterface
{

    /**
     * Consumes one request from the appropriate quota(s).
     *
     * @throws \Pekral\GoogleConsole\Exception\QuotaExceededException When QPD or QPM (or QPS for Other) is exceeded
     */
    public function consume(ApiFamily $apiFamily, ?string $siteUrl = null): void;

}
