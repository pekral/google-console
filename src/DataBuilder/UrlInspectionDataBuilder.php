<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use Google\Service\SearchConsole\IndexStatusInspectionResult;
use Google\Service\SearchConsole\MobileUsabilityInspectionResult;
use Google\Service\SearchConsole\UrlInspectionResult as GoogleUrlInspectionResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;

final class UrlInspectionDataBuilder
{

    public function fromGoogleResult(GoogleUrlInspectionResult $result): UrlInspectionResult
    {
        return UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => $this->buildIndexStatusData($result->getIndexStatusResult()),
            'inspectionResultLink' => is_string($result->getInspectionResultLink()) ? $result->getInspectionResultLink() : '',
            'mobileUsabilityResult' => $this->buildMobileUsabilityData($this->getMobileUsability($result)),
        ]);
    }

    /**
     * Wrapper for deprecated getMobileUsabilityResult() method.
     * Google deprecated Mobile Usability API in December 2023 but provides no alternative.
     * The data is still available in URL Inspection API responses.
     *
     * Note: Google's PHPDoc incorrectly declares non-nullable return type, but the method can return null.
     *
     * @see https://searchengineland.com/google-officially-drops-mobile-usability-report-mobile-friendly-test-tool-and-mobile-friendly-test-api-435377
     * @phpstan-ignore return.unusedType (Google's PHPDoc is incorrect - method can return null in practice)
     */
    private function getMobileUsability(GoogleUrlInspectionResult $result): ?MobileUsabilityInspectionResult
    {
        /** @phpstan-ignore method.deprecated (Google deprecated this API in Dec 2023 but provides no alternative) */
        return $result->getMobileUsabilityResult();
    }

    /**
     * @return array{
     *     coverageState?: string,
     *     crawledAs?: string,
     *     googleCanonical?: string,
     *     indexingState?: string,
     *     lastCrawlTime?: string,
     *     pageFetchState?: string,
     *     robotsTxtState?: string,
     *     userCanonical?: string,
     *     verdict?: string
     * }
     */
    private function buildIndexStatusData(?IndexStatusInspectionResult $indexStatus): array
    {
        if ($indexStatus === null) {
            return [];
        }

        return [
            'coverageState' => (string) $indexStatus->getCoverageState(),
            'crawledAs' => (string) $indexStatus->getCrawledAs(),
            'googleCanonical' => (string) $indexStatus->getGoogleCanonical(),
            'indexingState' => (string) $indexStatus->getIndexingState(),
            'lastCrawlTime' => (string) $indexStatus->getLastCrawlTime(),
            'pageFetchState' => (string) $indexStatus->getPageFetchState(),
            'robotsTxtState' => (string) $indexStatus->getRobotsTxtState(),
            'userCanonical' => (string) $indexStatus->getUserCanonical(),
            'verdict' => (string) $indexStatus->getVerdict(),
        ];
    }

    /**
     * @return array{
     *     issues?: array<array{issueType?: string}>,
     *     verdict?: string
     * }
     */
    private function buildMobileUsabilityData(?MobileUsabilityInspectionResult $mobileUsability): array
    {
        if ($mobileUsability === null) {
            return [];
        }

        $issues = [];

        /** @var \Google\Service\SearchConsole\MobileUsabilityIssue $issue */
        foreach ($mobileUsability->getIssues() ?? [] as $issue) {
            $issues[] = ['issueType' => (string) $issue->getIssueType()];
        }

        return [
            'issues' => $issues,
            'verdict' => (string) $mobileUsability->getVerdict(),
        ];
    }

}
