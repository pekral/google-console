<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Helper;

use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;

final class IndexingCheckRecommendationProvider
{

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasonCodes
     * @return list<string>
     */
    public static function getRecommendations(array $reasonCodes): array
    {
        $map = self::reasonCodeToRecommendation();
        $seen = [];
        $out = [];

        foreach ($reasonCodes as $code) {
            $recommendation = $map[$code->value] ?? null;

            if ($recommendation === null || isset($seen[$recommendation])) {
                continue;
            }

            $seen[$recommendation] = true;
            $out[] = $recommendation;
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private static function reasonCodeToRecommendation(): array
    {
        return [
            IndexingCheckReasonCode::AUTH_REQUIRED_OR_FAILED->value => 'Make the page publicly accessible for Googlebot.',
            IndexingCheckReasonCode::CANONICAL_MISMATCH->value => 'Align the canonical URL with the intended target.',
            IndexingCheckReasonCode::CAPTCHA_OR_BLOCKED->value => 'Remove CAPTCHA or blocking for Googlebot.',
            IndexingCheckReasonCode::DUPLICATE_CANONICAL_OTHER->value => 'This URL is canonical elsewhere; keep or update the canonical.',
            IndexingCheckReasonCode::DUPLICATE_WITHOUT_CANONICAL->value => 'Add a canonical tag or consolidate duplicate content.',
            IndexingCheckReasonCode::HTTP_STATUS_NOT_200->value => 'Return HTTP 200 for the URL so it can be indexed.',
            IndexingCheckReasonCode::INDEXING_PENDING->value => 'Request indexing in Google Search Console to speed up.',
            IndexingCheckReasonCode::INSUFFICIENT_DATA->value => 'Request URL inspection or indexing in GSC for fresh data.',
            IndexingCheckReasonCode::META_NOINDEX->value => 'Remove meta noindex or allow indexing in page meta tags.',
            IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED->value => 'Check reason codes and fix blocking issues.',
            IndexingCheckReasonCode::RATE_LIMITED->value => 'Request indexing in Google Search Console after the rate limit window.',
            IndexingCheckReasonCode::REDIRECTED->value => 'Use a direct URL or set the correct redirect target for indexing.',
            IndexingCheckReasonCode::ROBOTS_BLOCKED->value => 'Allow crawling in robots.txt or remove the blocking rule.',
            IndexingCheckReasonCode::SOFT_404_SUSPECTED->value => 'Ensure the page returns real content or a proper 404.',
            IndexingCheckReasonCode::TIMEOUT->value => 'Improve server response time or fix timeouts.',
        ];
    }

}
