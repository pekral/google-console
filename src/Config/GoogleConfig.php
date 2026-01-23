<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Config;

use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

final readonly class GoogleConfig
{

    public function __construct(public string $credentialsPath, public string $applicationName = 'Google Console Client')
    {
    }

    /**
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public static function fromCredentialsPath(string $path): self
    {
        if (!file_exists($path)) {
            throw new GoogleConsoleFailure(
                sprintf('Credentials file not found: %s', $path),
            );
        }

        return new self(credentialsPath: $path);
    }

}
