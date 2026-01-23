<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

final readonly class SearchAnalyticsRow
{

    /**
     * @param array<string, string> $keys
     */
    public function __construct(public array $keys, public float $clicks, public float $impressions, public float $ctr, public float $position)
    {
    }

    /**
     * @param array{
     *     keys?: array<string>,
     *     clicks?: float,
     *     impressions?: float,
     *     ctr?: float,
     *     position?: float
     * } $data
     * @param array<string> $dimensions
     */
    public static function fromApiResponse(array $data, array $dimensions): self
    {
        $keys = [];

        foreach ($dimensions as $index => $dimension) {
            $keys[$dimension] = $data['keys'][$index] ?? '';
        }

        return new self(
            keys: $keys,
            clicks: $data['clicks'] ?? 0.0,
            impressions: $data['impressions'] ?? 0.0,
            ctr: $data['ctr'] ?? 0.0,
            position: $data['position'] ?? 0.0,
        );
    }

    public function getKey(string $dimension): ?string
    {
        return $this->keys[$dimension] ?? null;
    }

    public function getQuery(): ?string
    {
        return $this->getKey('query');
    }

    public function getPage(): ?string
    {
        return $this->getKey('page');
    }

    public function getCountry(): ?string
    {
        return $this->getKey('country');
    }

    public function getDevice(): ?string
    {
        return $this->getKey('device');
    }

    /**
     * @return array{
     *     keys: array<string, string>,
     *     clicks: float,
     *     impressions: float,
     *     ctr: float,
     *     position: float
     * }
     */
    public function toArray(): array
    {
        return [
            'clicks' => $this->clicks,
            'ctr' => $this->ctr,
            'impressions' => $this->impressions,
            'keys' => $this->keys,
            'position' => $this->position,
        ];
    }

}
