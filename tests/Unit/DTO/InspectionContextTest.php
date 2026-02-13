<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\InspectionContext;
use Pekral\GoogleConsole\Enum\OperatingMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

describe(InspectionContext::class, function (): void {

    it('creates context with all null by default', function (): void {
        $context = new InspectionContext();

        expect($context->siteUrl)->toBeNull()
            ->and($context->urlNormalizer)->toBeNull()
            ->and($context->operatingMode)->toBeNull();
    });

    it('creates context with site url only', function (): void {
        $context = new InspectionContext(siteUrl: 'https://example.com/');

        expect($context->siteUrl)->toBe('https://example.com/')
            ->and($context->urlNormalizer)->toBeNull()
            ->and($context->operatingMode)->toBeNull();
    });

    it('creates context with url normalizer and operating mode', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());

        $context = new InspectionContext(
            siteUrl: 'sc-domain:example.com',
            urlNormalizer: $normalizer,
            operatingMode: OperatingMode::BEST_EFFORT,
        );

        expect($context->siteUrl)->toBe('sc-domain:example.com')
            ->and($context->urlNormalizer)->toBe($normalizer)
            ->and($context->operatingMode)->toBe(OperatingMode::BEST_EFFORT);
    });
});
