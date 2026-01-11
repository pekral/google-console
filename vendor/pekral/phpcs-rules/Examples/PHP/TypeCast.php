<?php

declare(strict_types = 1);

namespace Example\PHP;

final class TypeCast
{

    public function example(): void
    {
        $string = '42';
        $int = (int) $string;
        
        $float = 3.14;
        $intFromFloat = (int) $float;
        
        $bool = true;
        $intFromBool = (int) $bool;
        
        $array = [1, 2, 3];
        $stringFromArray = (string) $array;
        
        echo $int;
        echo $intFromFloat;
        echo $intFromBool;
        echo $stringFromArray;
    }

}
