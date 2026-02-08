<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

enum IndexingChangeType: string
{

    case NEWLY_INDEXED = 'NEWLY_INDEXED';

    case DROPPED_FROM_INDEX = 'DROPPED_FROM_INDEX';

    case BECAME_UNKNOWN = 'BECAME_UNKNOWN';

    case RECOVERED_FROM_UNKNOWN = 'RECOVERED_FROM_UNKNOWN';

    public static function between(IndexingCheckStatus $previous, IndexingCheckStatus $current): ?self
    {
        $pair = [$previous, $current];

        return match (true) {
            $pair === [IndexingCheckStatus::INDEXED, IndexingCheckStatus::INDEXED] => null,
            $pair === [IndexingCheckStatus::INDEXED, IndexingCheckStatus::NOT_INDEXED] => self::DROPPED_FROM_INDEX,
            $pair === [IndexingCheckStatus::INDEXED, IndexingCheckStatus::UNKNOWN] => self::BECAME_UNKNOWN,
            $pair === [IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::INDEXED] => self::NEWLY_INDEXED,
            $pair === [IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::NOT_INDEXED] => null,
            $pair === [IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::UNKNOWN] => self::BECAME_UNKNOWN,
            $pair === [IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::INDEXED] => self::RECOVERED_FROM_UNKNOWN,
            $pair === [IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::NOT_INDEXED] => self::RECOVERED_FROM_UNKNOWN,
            $pair === [IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::UNKNOWN] => null,
            default => null,
        };
    }

}
