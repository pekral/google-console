<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final readonly class PerUrlInspectionResult
{

    public function __construct(public string $url, public IndexingCheckStatus $status, public UrlInspectionResult $result) {
    }

}
