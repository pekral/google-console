<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

enum Dimension: string
{

    case QUERY = 'query';
    case PAGE = 'page';
    case COUNTRY = 'country';
    case DEVICE = 'device';
    case SEARCH_APPEARANCE = 'searchAppearance';
    case DATE = 'date';

    /**
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public static function fromString(string $dimension): self
    {
        $case = self::tryFrom($dimension);

        if ($case === null) {
            throw new GoogleConsoleFailure(
                sprintf(
                    'Invalid dimension "%s". Valid dimensions are: %s',
                    $dimension,
                    implode(', ', self::values()),
                ),
            );
        }

        return $case;
    }

    /**
     * @param array<string> $dimensions
     * @return array<self>
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public static function fromArray(array $dimensions): array
    {
        return array_map(
            static fn (string $dimension): self => self::fromString($dimension),
            $dimensions,
        );
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }

    /**
     * @param array<self> $dimensions
     * @return array<string>
     */
    public static function toStringArray(array $dimensions): array
    {
        return array_map(
            static fn (self $dimension): string => $dimension->value,
            $dimensions,
        );
    }

}
