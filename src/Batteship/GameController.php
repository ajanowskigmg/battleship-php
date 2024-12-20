<?php

namespace Battleship;

use InvalidArgumentException;

class GameController
{

    public static function checkIsHit(array &$fleet, $shot)
    {
        if ($fleet == null) {
            throw new InvalidArgumentException("ships is null");
        }

        if ($shot == null) {
            throw new InvalidArgumentException("shot is null");
        }

        foreach ($fleet as $ship) {
            foreach ($ship->getPositions() as $position) {
                if ($position == $shot) {
                    $index = array_search($position, $ship->getPositions());
                    unset($ship->getPositions()[$index]);
                    if (self::checkIfShipSunk($ship)) {
                        self::printInfoShipSunk($ship);
                        self::printAllShipsCounts($fleet);
                    }
                    return true;
                }
            }
        }

        return false;
    }

    public static function checkIsGameOver(array $fleet)
    {
        foreach ($fleet as $ship) {
            if (count($ship->getPositions()) > 0) {
                return false;
            }
        }

        return true;
    }

    public static function initializeShips()
    {
        return Array(
            new Ship("Aircraft Carrier", 5, Color::CADET_BLUE),
            new Ship("Battleship", 4, Color::RED),
            new Ship("Submarine", 3, Color::CHARTREUSE),
            new Ship("Destroyer", 3, Color::YELLOW),
            new Ship("Patrol Boat", 2, Color::ORANGE));
    }

    public static function isShipValid($ship)
    {
        return count($ship->getPositions()) == $ship->getSize();
    }

    public static function getRandomPosition()
    {
        $rows = 8;
        $lines = 8;

        $letter = Letter::value(random_int(0, $lines - 1));
        $number = random_int(0, $rows - 1);

        return new Position($letter, $number);
    }

    /**
     * Sprawdza czy statek zatonął, jeśli zadonął, wypisuje jaki to statek
     *
     * @param Ship $ship
     * @param Position $hitPosition
     * @return bool
     */
    public static function checkIfShipSunk(Ship $ship)
    {
        if (count($ship->getPositions()) == 0) {
            return true;
        }

        return false;
    }

    public static function printAllShipsCounts(array $fleet)
    {
        foreach ($fleet as $ship) {
            echo $ship->getName() . " Active positions: " . count($ship->getPositions()) . "/" .  $ship->getSize() . "\n";
        }
    }

    private static function printInfoShipSunk(Ship $ship)
    {
        echo "\033[31m";

        echo "SHIP DESTROYED: " . $ship->getName() . " Size: " . $ship->getSize() . "\n";
        echo "             |    |    |               \n";
        echo "            )_)  )_)  )_)              \n";
        echo "           )___))___))___)\\           \n";
        echo "          )____)____)_____)\\\\         \n";
        echo "        _____|____|____|____\\\\__      \n";
        echo "--------\\                   /-------- \n";
        echo "  ~~~~~~^~~~~~~~~~~~~~~~~~~~^~~~~~~~  \n";
        echo "     ~~~^~                         ~~~\n";
        echo "       RIP " . $ship->getName() . "         \n";

        echo "\033[0m";
    }
}
