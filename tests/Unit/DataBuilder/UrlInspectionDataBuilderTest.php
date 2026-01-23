<?php

declare(strict_types = 1);

use Google\Service\SearchConsole\IndexStatusInspectionResult;
use Google\Service\SearchConsole\MobileUsabilityInspectionResult;
use Google\Service\SearchConsole\UrlInspectionResult as GoogleUrlInspectionResult;
use Pekral\GoogleConsole\DataBuilder\UrlInspectionDataBuilder;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;

describe(UrlInspectionDataBuilder::class, function (): void {

    it('maps Google result to UrlInspectionResult DTO', function (): void {
        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn('2024-01-15T10:30:00Z');
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $googleResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $googleResult->shouldReceive('getInspectionResultLink')->andReturn('https://search.google.com/inspect');
        $googleResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $googleResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $builder = new UrlInspectionDataBuilder();
        $result = $builder->fromGoogleResult($googleResult);

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->verdict)->toBe('PASS')
            ->and($result->coverageState)->toBe('Submitted and indexed')
            ->and($result->isMobileFriendly)->toBeTrue();
    });

    it('handles null index status and mobile usability', function (): void {
        $googleResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $googleResult->shouldReceive('getInspectionResultLink')->andReturn(null);
        $googleResult->shouldReceive('getIndexStatusResult')->andReturn(null);
        $googleResult->shouldReceive('getMobileUsabilityResult')->andReturn(null);

        $builder = new UrlInspectionDataBuilder();
        $result = $builder->fromGoogleResult($googleResult);

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->inspectionResultLink)->toBe('')
            ->and($result->verdict)->toBe('VERDICT_UNSPECIFIED')
            ->and($result->isMobileFriendly)->toBeFalse();
    });

    it('maps mobile usability issues', function (): void {
        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('');

        $mobileIssue = new class () {

            public function getIssueType(): string
            {
                return 'TEXT_TOO_SMALL';
            }

        };

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('FAIL');
        $mobileUsability->shouldReceive('getIssues')->andReturn([$mobileIssue]);

        $googleResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $googleResult->shouldReceive('getInspectionResultLink')->andReturn('https://search.google.com/inspect');
        $googleResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $googleResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $builder = new UrlInspectionDataBuilder();
        $result = $builder->fromGoogleResult($googleResult);

        expect($result->isMobileFriendly)->toBeFalse()
            ->and($result->mobileUsabilityIssue)->toBe('TEXT_TOO_SMALL');
    });
});
