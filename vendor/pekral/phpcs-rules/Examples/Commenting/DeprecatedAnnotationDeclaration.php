<?php

declare(strict_types = 1);

namespace Example\Commenting;

final class DeprecatedAnnotationDeclaration
{

    /**
     * @deprecated Use newMethod() instead
     */
    public function oldMethod(): string
    {
        return 'deprecated';
    }

    /**
     * @deprecated Will be removed in next version
     */
    public function deprecatedMethod(): void
    {
        // This method is deprecated and will be removed
    }

    public function newMethod(): string
    {
        return 'new';
    }

}
