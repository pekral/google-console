<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\UrlNormalizer;

enum TrailingSlashMode: string
{

    case PRESERVE = 'preserve';

    case ADD = 'add';

    case REMOVE = 'remove';

}
