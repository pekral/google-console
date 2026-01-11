<?php

declare(strict_types = 1);

namespace Pekral\PhpcsRulesBuild\Sniffs;

use SplFileInfo;

use function array_pop;
use function array_unique;
use function explode;
use function file_get_contents;
use function preg_match_all;
use function sprintf;
use function str_replace;
use function substr;

final class SniffHelper
{

    public static function getSniffName(SplFileInfo $fileInfo): string
    {
        $sniffName = substr($fileInfo->getFilename(), 0, -4);
        $sniffName = str_replace('Sniff', '', $sniffName);
        $info = explode('/', $fileInfo->getPath());
        $category = array_pop($info);

        return sprintf('SlevomatCodingStandard.%s.%s', $category, $sniffName);
    }

    /**
     * @return array<string>
     */
    public static function getAllSniffsFromRuleset(string $rulesetFilePath): array
    {
        $ruleset = file_get_contents($rulesetFilePath);
        
        if ($ruleset === false) {
            return [];
        }
        
        $allSniffs = [];
        
        preg_match_all('/<rule ref="(SlevomatCodingStandard\\.[^"]+)"/', $ruleset, $ruleMatches);

        $allSniffs = [...$allSniffs, ...$ruleMatches[1]];
        
        preg_match_all('/<exclude name="(SlevomatCodingStandard\\.[^"]+)"/', $ruleset, $excludeMatches);

        if (count($excludeMatches[1]) > 0) {
            $allSniffs = [...$allSniffs, ...$excludeMatches[1]];
        }

        return array_unique($allSniffs);
    }

}