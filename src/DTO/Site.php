<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

final readonly class Site
{

    public function __construct(public string $siteUrl, public string $permissionLevel)
    {
    }

    /**
     * @param array{
     *     siteUrl?: string,
     *     permissionLevel?: string
     * } $data
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(siteUrl: $data['siteUrl'] ?? '', permissionLevel: $data['permissionLevel'] ?? '');
    }

    public function isOwner(): bool
    {
        return $this->permissionLevel === 'siteOwner';
    }

    public function hasFullAccess(): bool
    {
        return in_array($this->permissionLevel, ['siteOwner', 'siteFullUser'], true);
    }

    /**
     * @return array{
     *     siteUrl: string,
     *     permissionLevel: string,
     *     isOwner: bool,
     *     hasFullAccess: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'hasFullAccess' => $this->hasFullAccess(),
            'isOwner' => $this->isOwner(),
            'permissionLevel' => $this->permissionLevel,
            'siteUrl' => $this->siteUrl,
        ];
    }

}
