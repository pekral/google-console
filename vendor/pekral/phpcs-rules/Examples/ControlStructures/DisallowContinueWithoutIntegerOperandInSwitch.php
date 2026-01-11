<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class DisallowContinueWithoutIntegerOperandInSwitch
{

    public function foo(): void
    {
        for ($i = 0; $i < 3; $i += 1) {
            switch ($i) {
                case 1:
                    continue 1;

                case 2:
                    break;
            }
        }
    }

} 