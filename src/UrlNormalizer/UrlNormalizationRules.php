<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\UrlNormalizer;

/**
 * Configurable rules for URL normalization (fragment, trailing slash, tracking params).
 */
final readonly class UrlNormalizationRules
{

    public function __construct(
        public bool $removeFragment = true,
        public TrailingSlashMode $trailingSlash = TrailingSlashMode::PRESERVE,
        public bool $stripUtmParams = false,
        public bool $stripGclid = false,
    ) {
    }

    public static function defaults(): self
    {
        return new self();
    }

    public static function forApiCalls(): self
    {
        return new self(removeFragment: true, trailingSlash: TrailingSlashMode::PRESERVE, stripUtmParams: true, stripGclid: true);
    }

}
