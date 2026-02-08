<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum IndexingCheckSourceType: string
{

    case AUTHORITATIVE = 'authoritative';

    case HEURISTIC = 'heuristic';

}
