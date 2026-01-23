<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use DateTimeImmutable;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;

final readonly class IndexingResult
{

    public function __construct(public string $url, public IndexingNotificationType $type, public ?DateTimeImmutable $notifyTime = null)
    {
    }

    /**
     * @return array{
     *     notifyTime: string|null,
     *     type: string,
     *     url: string
     * }
     */
    public function toArray(): array
    {
        return [
            'notifyTime' => $this->notifyTime?->format('Y-m-d H:i:s'),
            'type' => $this->type->value,
            'url' => $this->url,
        ];
    }

}
