<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum IndexingCheckStatus: string
{

    case INDEXED = 'INDEXED';

    case NOT_INDEXED = 'NOT_INDEXED';

    case UNKNOWN = 'UNKNOWN';

}
