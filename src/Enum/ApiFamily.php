<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum ApiFamily: string
{

    case URL_INSPECTION = 'url_inspection';

    case SEARCH_ANALYTICS = 'search_analytics';

    case INDEXING = 'indexing';

    case OTHER = 'other';

}
