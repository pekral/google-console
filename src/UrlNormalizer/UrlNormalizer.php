<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\UrlNormalizer;

use InvalidArgumentException;

final readonly class UrlNormalizer
{

    private const string UTM_PREFIX = 'utm_';

    private const string GCLID_PARAM = 'gclid';

    public function __construct(private UrlNormalizationRules $urlNormalizationRules) {
    }

    public function normalize(string $url): string
    {
        $parsed = parse_url($url);

        if ($parsed === false || !isset($parsed['scheme'], $parsed['host'])) {
            throw new InvalidArgumentException(sprintf('Invalid URL: %s', $url));
        }

        $path = $parsed['path'] ?? '/';
        $path = $this->normalizeTrailingSlash($path);

        $query = $parsed['query'] ?? null;
        $query = $this->normalizeQuery($query);

        $authority = $parsed['host'];

        if (isset($parsed['port'])) {
            $authority .= ':' . $parsed['port'];
        }

        $normalized = $parsed['scheme'] . '://' . $authority . $path;

        if ($query !== null && $query !== '') {
            $normalized .= '?' . $query;
        }

        if (!$this->urlNormalizationRules->removeFragment && isset($parsed['fragment']) && $parsed['fragment'] !== '') {
            $normalized .= '#' . $parsed['fragment'];
        }

        return $normalized;
    }

    private function normalizeTrailingSlash(string $path): string
    {
        if ($path === '' || $path === '/') {
            return '/';
        }

        $hasTrailingSlash = str_ends_with($path, '/');

        return match ($this->urlNormalizationRules->trailingSlash) {
            TrailingSlashMode::PRESERVE => $path,
            TrailingSlashMode::ADD => $hasTrailingSlash ? $path : $path . '/',
            TrailingSlashMode::REMOVE => $hasTrailingSlash ? rtrim($path, '/') : $path,
        };
    }

    private function normalizeQuery(?string $query): ?string
    {
        if ($query === null || $query === '') {
            return $query;
        }

        $params = $this->parseQueryParams($query);

        if ($this->urlNormalizationRules->stripUtmParams) {
            $params = $this->removeParamsByPrefix($params, self::UTM_PREFIX);
        }

        if ($this->urlNormalizationRules->stripGclid) {
            unset($params[self::GCLID_PARAM]);
        }

        $result = http_build_query($params, encoding_type: PHP_QUERY_RFC3986);

        return $result === '' ? null : $result;
    }

    /**
     * @return array<int|string, array<mixed>|string>
     */
    private function parseQueryParams(string $query): array
    {
        $params = [];
        parse_str($query, $params);

        return $params;
    }

    /**
     * @param array<int|string, array<mixed>|string> $params
     * @return array<int|string, array<mixed>|string>
     */
    private function removeParamsByPrefix(array $params, string $prefix): array
    {
        foreach (array_keys($params) as $key) {
            if (str_starts_with((string) $key, $prefix)) {
                unset($params[$key]);
            }
        }

        return $params;
    }

}
