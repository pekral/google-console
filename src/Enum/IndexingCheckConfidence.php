<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum IndexingCheckConfidence: string
{

    case HIGH = 'high';

    case MEDIUM = 'medium';

    case LOW = 'low';

}
