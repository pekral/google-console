<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum OperatingMode: string
{

    case STRICT = 'strict';

    case BEST_EFFORT = 'best-effort';

}
