<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Exception;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousExceptionNaming.SuperfluousSuffix -- conventional name for exception
final class QuotaExceededException extends GoogleConsoleFailure
{

    public function __construct(string $message, private readonly ?string $limitType = null, private readonly ?int $retryAfterSeconds = null) {
        parent::__construct($message);
    }

    public function getLimitType(): ?string
    {
        return $this->limitType;
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }

}
