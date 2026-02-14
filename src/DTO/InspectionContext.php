<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use Pekral\GoogleConsole\Enum\OperatingMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

/**
 * Optional request context for URL inspection calls.
 * When provided, overrides instance-level site URL, URL normalizer, operating mode, and language for the request.
 *
 * languageCode: BCP-47 (e.g. "en", "en-US", "cs-CZ"). Reason-code mapping expects English coverageState;
 * use "en" (or leave default) for reliable heuristics. Other locales may break substring matching.
 */
final readonly class InspectionContext
{

    public function __construct(
        public ?string $siteUrl = null,
        public ?UrlNormalizer $urlNormalizer = null,
        public ?OperatingMode $operatingMode = null,
        public ?string $languageCode = null,
    ) {
    }

}
