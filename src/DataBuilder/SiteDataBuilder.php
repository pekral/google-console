<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use Google\Service\Webmasters\WmxSite;
use Pekral\GoogleConsole\DTO\Site;

final class SiteDataBuilder
{

    public function fromWmxSite(WmxSite $wmxSite): Site
    {
        $permissionLevel = $wmxSite->getPermissionLevel();
        $siteUrl = $wmxSite->getSiteUrl();

        return Site::fromApiResponse([
            'permissionLevel' => is_string($permissionLevel) ? $permissionLevel : '',
            'siteUrl' => is_string($siteUrl) ? $siteUrl : '',
        ]);
    }

    /**
     * @param array<\Google\Service\Webmasters\WmxSite> $wmxSites
     * @return array<\Pekral\GoogleConsole\DTO\Site>
     */
    public function fromWmxSiteArray(array $wmxSites): array
    {
        return array_map(
            fn (WmxSite $wmxSite): Site => $this->fromWmxSite($wmxSite),
            $wmxSites,
        );
    }

}
