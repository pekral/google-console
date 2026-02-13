<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use Pekral\GoogleConsole\Enum\OperatingMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

/**
 * Optional request context for URL inspection calls.
 * When provided, overrides instance-level site URL, URL normalizer, and operating mode for the request.
 */
final readonly class InspectionContext
{

    public function __construct(public ?string $siteUrl = null, public ?UrlNormalizer $urlNormalizer = null, public ?OperatingMode $operatingMode = null) {
    }

}
