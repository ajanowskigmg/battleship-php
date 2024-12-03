<?php

//use Battleship\Color;

class Console
{
    function resetForegroundColor()
    {
        echo(Battleship\Color::DEFAULT_GREY);
    }

    function setForegroundColor($color)
    {
        echo($color);
    }

    function println($line = "")
    {
        echo "$line\n";
    }

    function printColoredLn($line = "", $color = Battleship\Color::DEFAULT_GREY)
    {
        self::setForegroundColor($color);
        self::println($line);
        self::resetForegroundColor();
    }

}