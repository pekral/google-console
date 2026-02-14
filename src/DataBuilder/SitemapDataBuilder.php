<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use DateTimeImmutable;
use DateTimeInterface;
use Google\Service\Webmasters\WmxSitemap;
use Google\Service\Webmasters\WmxSitemapContent;
use Pekral\GoogleConsole\DTO\Sitemap;
use Pekral\GoogleConsole\DTO\SitemapContentEntry;
use Throwable;

final readonly class SitemapDataBuilder
{

    public function fromWmxSitemap(WmxSitemap $wmx): Sitemap
    {
        return new Sitemap(
            path: $this->stringOrEmpty($wmx->getPath()),
            lastSubmitted: $this->parseDate($wmx->getLastSubmitted()),
            lastDownloaded: $this->parseDate($wmx->getLastDownloaded()),
            errors: $this->toInt($wmx->getErrors()),
            warnings: $this->toInt($wmx->getWarnings()),
            isPending: (bool) $wmx->getIsPending(),
            isSitemapsIndex: (bool) $wmx->getIsSitemapsIndex(),
            type: $this->stringOrEmpty($wmx->getType()),
            contents: $this->buildContents($wmx->getContents()),
        );
    }

    /**
     * @param array<\Google\Service\Webmasters\WmxSitemap> $wmxSitemaps
     * @return list<\Pekral\GoogleConsole\DTO\Sitemap>
     */
    public function fromWmxSitemapArray(array $wmxSitemaps): array
    {
        $result = [];

        foreach ($wmxSitemaps as $wmx) {
            if ($wmx instanceof WmxSitemap) {
                $result[] = $this->fromWmxSitemap($wmx);
            }
        }

        return $result;
    }

    /**
     * @return list<\Pekral\GoogleConsole\DTO\SitemapContentEntry>
     */
    private function buildContents(mixed $rawContents): array
    {
        if (!is_array($rawContents)) {
            return [];
        }

        $contents = [];

        foreach ($rawContents as $item) {
            if ($item instanceof WmxSitemapContent) {
                $contents[] = new SitemapContentEntry(
                    type: $this->stringOrEmpty($item->getType()),
                    submitted: $this->toInt($item->getSubmitted()),
                );
            }
        }

        return $contents;
    }

    private function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (is_string($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

}
