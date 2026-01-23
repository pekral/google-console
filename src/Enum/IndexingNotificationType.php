<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum IndexingNotificationType: string
{

    case URL_UPDATED = 'URL_UPDATED';

    case URL_DELETED = 'URL_DELETED';

}
