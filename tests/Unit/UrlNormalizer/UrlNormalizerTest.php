<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\UrlNormalizer\TrailingSlashMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

describe(UrlNormalizer::class, function (): void {

    it('removes fragment by default', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        expect($normalizer->normalize('https://example.com/page#section'))->toBe('https://example.com/page');
    });

    it('preserves fragment when removeFragment is false', function (): void {
        $rules = new UrlNormalizationRules(removeFragment: false);
        $normalizer = new UrlNormalizer($rules);

        expect($normalizer->normalize('https://example.com/page#section'))->toBe('https://example.com/page#section');
    });

    it('preserves path when trailing slash mode is preserve', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        expect($normalizer->normalize('https://example.com/page'))->toBe('https://example.com/page')
            ->and($normalizer->normalize('https://example.com/page/'))->toBe('https://example.com/page/');
    });

    it('adds trailing slash when mode is add', function (): void {
        $rules = new UrlNormalizationRules(trailingSlash: TrailingSlashMode::ADD);
        $normalizer = new UrlNormalizer($rules);

        expect($normalizer->normalize('https://example.com/page'))->toBe('https://example.com/page/')
            ->and($normalizer->normalize('https://example.com/page/'))->toBe('https://example.com/page/');
    });

    it('removes trailing slash when mode is remove', function (): void {
        $rules = new UrlNormalizationRules(trailingSlash: TrailingSlashMode::REMOVE);
        $normalizer = new UrlNormalizer($rules);

        expect($normalizer->normalize('https://example.com/page/'))->toBe('https://example.com/page')
            ->and($normalizer->normalize('https://example.com/page'))->toBe('https://example.com/page');
    });

    it('strips utm params when stripUtmParams is true', function (): void {
        $rules = UrlNormalizationRules::forApiCalls();
        $normalizer = new UrlNormalizer($rules);

        expect($normalizer->normalize('https://example.com/page?utm_source=google&foo=bar'))
            ->toBe('https://example.com/page?foo=bar');
    });

    it('strips gclid when stripGclid is true', function (): void {
        $rules = UrlNormalizationRules::forApiCalls();
        $normalizer = new UrlNormalizer($rules);

        expect($normalizer->normalize('https://example.com/page?gclid=abc123&q=1'))
            ->toBe('https://example.com/page?q=1');
    });

    it('strips all utm_ prefixed params', function (): void {
        $rules = new UrlNormalizationRules(stripUtmParams: true);
        $normalizer = new UrlNormalizer($rules);

        $url = 'https://example.com/?utm_source=s&utm_medium=m&utm_campaign=c&keep=yes';

        expect($normalizer->normalize($url))->toBe('https://example.com/?keep=yes');
    });

    it('preserves port when present', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        expect($normalizer->normalize('https://example.com:8443/path'))->toBe('https://example.com:8443/path');
    });

    it('keeps root path as single slash', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        expect($normalizer->normalize('https://example.com/'))->toBe('https://example.com/')
            ->and($normalizer->normalize('https://example.com'))->toBe('https://example.com/');
    });

    it('throws for invalid url', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        $normalizer->normalize('not-a-url');
    })->throws(InvalidArgumentException::class, 'Invalid URL');

    it('throws for url without scheme', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::defaults());

        $normalizer->normalize('//example.com/path');
    })->throws(InvalidArgumentException::class);

    it('normalizes url with fragment and query forApiCalls', function (): void {
        $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());

        $url = 'https://example.com/page?utm_campaign=test&gclid=x#anchor';

        expect($normalizer->normalize($url))->toBe('https://example.com/page');
    });
});
