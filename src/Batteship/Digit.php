<?php

namespace Battleship;

use InvalidArgumentException;

class Digit
{

    public static $from = 1;
    public static $to = 8;

    public static function validate($digit) : string
    {
        if($digit < self::$from || $digit > self::$to){
            throw new InvalidArgumentException("Digit not exist.");
        }

        return $digit;
    }
}