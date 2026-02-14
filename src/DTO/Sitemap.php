<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use DateTimeImmutable;

final readonly class Sitemap
{

    /**
     * @param list<\Pekral\GoogleConsole\DTO\SitemapContentEntry> $contents
     */
    public function __construct(
        public string $path,
        public ?DateTimeImmutable $lastSubmitted,
        public ?DateTimeImmutable $lastDownloaded,
        public int $errors,
        public int $warnings,
        public bool $isPending,
        public bool $isSitemapsIndex,
        public string $type,
        public array $contents,
    ) {
    }

    /**
     * @return array{
     *     path: string,
     *     lastSubmitted: ?string,
     *     lastDownloaded: ?string,
     *     errors: int,
     *     warnings: int,
     *     isPending: bool,
     *     isSitemapsIndex: bool,
     *     type: string,
     *     contents: list<array{type: string, submitted: int}>
     * }
     */
    public function toArray(): array
    {
        return [
            'contents' => array_map(
                static fn (SitemapContentEntry $e): array => ['type' => $e->type, 'submitted' => $e->submitted],
                $this->contents,
            ),
            'errors' => $this->errors,
            'isPending' => $this->isPending,
            'isSitemapsIndex' => $this->isSitemapsIndex,
            'lastDownloaded' => $this->lastDownloaded?->format('c'),
            'lastSubmitted' => $this->lastSubmitted?->format('c'),
            'path' => $this->path,
            'type' => $this->type,
            'warnings' => $this->warnings,
        ];
    }

}
