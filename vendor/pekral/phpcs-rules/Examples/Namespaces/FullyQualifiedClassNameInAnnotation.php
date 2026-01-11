<?php

declare(strict_types = 1);

namespace Example\Namespaces;

use DateTime;
use Exception;

// In annotation, only imported class names are used, not fully qualified names
/**
 * @var \DateTime $dt
 * @var \Exception $ex
 */
final class FullyQualifiedClassNameInAnnotation
{

    public function foo(): string
    {
        $dt = new DateTime();
        $ex = new Exception();

        return $dt->format('c') . $ex->getMessage();
    }

} 