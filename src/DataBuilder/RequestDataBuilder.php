<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use DateTimeInterface;
use Google\Service\Webmasters\SearchAnalyticsQueryRequest;

final class RequestDataBuilder
{

    /**
     * @param array<string> $dimensions
     */
    public function buildSearchAnalyticsRequest(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        array $dimensions,
        int $rowLimit,
        int $startRow,
    ): SearchAnalyticsQueryRequest {
        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate->format('Y-m-d'));
        $request->setEndDate($endDate->format('Y-m-d'));
        $request->setDimensions($dimensions);
        $request->setRowLimit($rowLimit);
        $request->setStartRow($startRow);

        return $request;
    }

}
