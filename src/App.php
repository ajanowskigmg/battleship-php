<?php

use Battleship\GameController;
use Battleship\Position;
use Battleship\Letter;
use Battleship\Color;

class App
{
    private static $myFleet = array();
    private static $enemyFleet = array();

    private static $fleetNumber;

    private static $console;

    static function run()
    {
        self::$console = new Console();
        self::$console->setForegroundColor(Color::MAGENTA);

        self::$console->println("                                     |__");
        self::$console->println("                                     |\\/");
        self::$console->println("                                     ---");
        self::$console->println("                                     / | [");
        self::$console->println("                              !      | |||");
        self::$console->println("                            _/|     _/|-++'");
        self::$console->println("                        +  +--|    |--|--|_ |-");
        self::$console->println("                     { /|__|  |/\\__|  |--- |||__/");
        self::$console->println("                    +---------------___[}-_===_.'____                 /\\");
        self::$console->println("                ____`-' ||___-{]_| _[}-  |     |_[___\\==--            \\/   _");
        self::$console->println(" __..._____--==/___]_|__|_____________________________[___\\==--____,------' .7");
        self::$console->println("|                        Welcome to Battleship                         BB-61/");
        self::$console->println(" \\_________________________________________________________________________|");
        self::$console->println();
        self::$console->resetForegroundColor();
        self::InitializeGame();
        self::StartGame();
    }

    public static function InitializeEnemyFleet(int $fleetNumber)
    {
        self::$enemyFleet = GameController::initializeShips();

        switch ($fleetNumber) {
            case 1:
                self::initializeFleet1();
                break;
            case 2:
                self::initializeFleet2();
                break;
            case 3:
                self::initializeFleet3();
                break;
            case 4:
                self::initializeFleet4();
                break;
            case 5:
                self::initializeFleet5();
                break;
            default:
                throw new Exception("Invalid fleet number");
        }
    }

    public static function getRandomPosition()
    {
        $rows = 8;
        $lines = 8;

        $letter = Letter::value(random_int(0, $lines - 1));
        $number = random_int(0, $rows - 1);

        return new Position($letter, $number);
    }

    public static function InitializeMyFleet()
    {
        self::$myFleet = GameController::initializeShips();

        self::$console->printColoredLn("Please position your fleet (Game board has size from A to H and 1 to 8) :", Color::YELLOW);

        foreach (self::$myFleet as $ship) {

            self::$console->println();
            self::$console->printColoredLn(sprintf("Please enter the positions for the %s (size: %s)", $ship->getName(), $ship->getSize()), Color::YELLOW);

            for ($i = 1; $i <= $ship->getSize(); $i++) {
                printf("\nEnter position %s of %s (i.e A3):", $i, $ship->getSize());
                $input = readline("");
                $ship->addPosition($input);
            }
        }
    }

    public static function beep()
    {
        echo "\007";
    }

    public static function InitializeGame()
    {
        self::$fleetNumber = random_int(1, 5);

        self::InitializeMyFleet();
        self::InitializeEnemyFleet(self::$fleetNumber);
    }

    public static function StartGame()
    {
        self::$console->setForegroundColor(Color::YELLOW);
        self::$console->println("\033[2J\033[;H");
        self::$console->println("                  __");
        self::$console->println("                 /  \\");
        self::$console->println("           .-.  |    |");
        self::$console->println("   *    _.-'  \\  \\__/");
        self::$console->println("    \\.-'       \\");
        self::$console->println("   /          _/");
        self::$console->println("  |      _  /\" \"");
        self::$console->println("  |     /_\'");
        self::$console->println("   \\    \\_/");
        self::$console->println("    \" \"\" \"\" \"\" \"");
        self::$console->resetForegroundColor();

        while (true) {
            self::groupVisualy("Player turn");
            self::$console->printColoredLn("Player, it's your turn", Color::YELLOW);
            self::$console->println("Enter coordinates for your shot :");
            $position = readline("");

            if (strtolower(trim($position)) === "map#") {
                self::$console->printColoredln("MAP: " . self::$fleetNumber, Color::YELLOW);
                continue;
            }

            $isHit = GameController::checkIsHit(self::$enemyFleet, self::parsePosition($position));
            if (GameController::checkIsGameOver(self::$enemyFleet)) {
                self::$console->printColoredln("You are the winner!", Color::YELLOW);
                exit();
            }

            if ($isHit) {
                self::$console->setForegroundColor(Color::RED);
                self::beep();
                self::$console->println("                \\         .  ./");
                self::$console->println("              \\      .:\" \";'.:..\" \"   /");
                self::$console->println("                  (M^^.^~~:.'\" \").");
                self::$console->println("            -   (/  .    . . \\ \\)  -");
                self::$console->println("               ((| :. ~ ^  :. .|))");
                self::$console->println("            -   (\\- |  \\ /  |  /)  -");
                self::$console->println("                 -\\  \\     /  /-");
                self::$console->println("                   \\  \\   /  /");
                self::$console->println("Yeah ! Nice hit !");
            } else {
                self::$console->setForegroundColor(Color::DARK_CYAN);
            }
            self::$console->resetForegroundColor();

            $position = self::getRandomPosition();
            $isHit = GameController::checkIsHit(self::$myFleet, $position);

            if (GameController::checkIsGameOver(self::$enemyFleet)) {
                self::$console->println("You lost");
                exit();
            }

            self::groupVisualy("Computer turn");
            if ($isHit) {
                self::$console->setForegroundColor(Color::RED);
                self::$console->println(sprintf("Computer shoot in %s%s and hit your ship !", $position->getColumn(), $position->getRow()));
                self::beep();

                self::$console->println("                \\         .  ./");
                self::$console->println("              \\      .:\" \";'.:..\" \"   /");
                self::$console->println("                  (M^^.^~~:.'\" \").");
                self::$console->println("            -   (/  .    . . \\ \\)  -");
                self::$console->println("               ((| :. ~ ^  :. .|))");
                self::$console->println("            -   (\\- |  \\ /  |  /)  -");
                self::$console->println("                 -\\  \\     /  /-");
                self::$console->println("                   \\  \\   /  /");

            } else {
                self::$console->setForegroundColor(Color::DARK_CYAN);
                self::$console->println(sprintf("Computer shoot in %s%s and miss", $position->getColumn(), $position->getRow()));
            }

            self::$console->resetForegroundColor();
        }
    }

    private static function groupVisualy($text = "")
    {
        $length = 70 - strlen($text) - 2;

        $length1 = ceil($length / 2);
        $length2 = $length - $length1;
        self::$console->println();
        self::$console->println(str_repeat('-', $length1) . ' ' . $text . ' ' . str_repeat('-', $length2));
        self::$console->println();
    }
    public static function parsePosition($input)
    {
        $letter = substr($input, 0, 1);
        $number = substr($input, 1, 1);

        if(!is_numeric($number)) {
            throw new Exception("Not a number: $number");
        }

        return new Position($letter, $number);
    }

    private static function initializeFleet1()
    {
        array_push(self::$enemyFleet[0]->getPositions(), new Position('A', 1));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('A', 2));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('A', 3));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('A', 4));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('A', 5));

        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 3));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 4));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 5));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 6));

        array_push(self::$enemyFleet[2]->getPositions(), new Position('E', 1));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('F', 1));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('G', 1));

        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 4));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 5));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 6));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 8));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('G', 8));
    }

    private static function initializeFleet2()
    {
        array_push(self::$enemyFleet[0]->getPositions(), new Position('B', 2));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('B', 3));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('B', 4));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('B', 5));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('B', 6));

        array_push(self::$enemyFleet[1]->getPositions(), new Position('D', 7));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('E', 7));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('F', 7));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('G', 7));

        array_push(self::$enemyFleet[2]->getPositions(), new Position('A', 8));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('B', 8));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('C', 8));

        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 3));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 4));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 5));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 2));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 3));

    }

    private static function initializeFleet3()
    {
        array_push(self::$enemyFleet[0]->getPositions(), new Position('C', 1));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 1));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('E', 1));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('F', 1));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 1));

        array_push(self::$enemyFleet[1]->getPositions(), new Position('A', 4));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('B', 4));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 4));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('D', 4));

        array_push(self::$enemyFleet[2]->getPositions(), new Position('F', 5));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('F', 6));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('F', 7));

        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 6));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 7));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('H', 8));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('B', 7));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('C', 7));


    }

    private static function initializeFleet4()
    {
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 3));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 4));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 5));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 6));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('D', 7));

        array_push(self::$enemyFleet[1]->getPositions(), new Position('F', 2));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('F', 3));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('F', 4));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('F', 5));

        array_push(self::$enemyFleet[2]->getPositions(), new Position('B', 8));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('C', 8));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('D', 8));

        array_push(self::$enemyFleet[3]->getPositions(), new Position('A', 6));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('A', 7));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('A', 8));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('H', 1));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('H', 2));

    }

    private static function initializeFleet5()
    {
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 4));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 5));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 6));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 7));
        array_push(self::$enemyFleet[0]->getPositions(), new Position('G', 8));

        array_push(self::$enemyFleet[1]->getPositions(), new Position('B', 2));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('C', 2));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('D', 2));
        array_push(self::$enemyFleet[1]->getPositions(), new Position('E', 2));

        array_push(self::$enemyFleet[2]->getPositions(), new Position('A', 1));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('A', 2));
        array_push(self::$enemyFleet[2]->getPositions(), new Position('A', 3));

        array_push(self::$enemyFleet[3]->getPositions(), new Position('C', 7));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('C', 8));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('D', 8));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 5));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 6));
    }
}