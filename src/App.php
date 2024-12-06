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
    private static $shots = [];
    public static function getRandomPosition()
    {
        $rows = 8;
        $lines = 8;

        // Generowanie losowej pozycji
        do {
            $letter = Letter::value(random_int(0, $lines - 1));
            $number = random_int(0, $rows - 1);
            $position = new Position($letter, $number);
        } while (in_array($position, self::$shots));

        // Dodajemy nową pozycję do tablicy strzałów
        self::$shots[] = $position;

        return $position;
    }

    public static function InitializeMyFleet()
    {
        self::$myFleet = GameController::initializeShips();

        self::$console->printColoredLn("Set up your fleet (board size is from A to H and 1 to 8):", Color::YELLOW);
        self::$console->printColoredLn("Directions: R - Right, D - Down", Color::YELLOW);

        foreach (self::$myFleet as $ship) {
            while (true) {
                try {
                    self::drawMap(self::$myFleet);
                    self::$console->println();
                    self::$console->printColoredLn(
                        sprintf("Enter positions for %s (size: %s)", $ship->getName(), $ship->getSize()),
                        Color::YELLOW
                    );

                    self::$console->println("Enter starting position (e.g. A3):");
                    $start = readline("");
                    $startPos = self::parsePosition($start);

                    // Calculate all ship positions
                    $shipPositions = self::calculateShipPositions($startPos, 'R', 1);

                    // Validate collisions with other ships
                    foreach ($shipPositions as $position) {
                        $pos = self::parsePosition($position);
                        if (self::isCollisionWithOtherShips($pos, self::$myFleet)) {
                            throw new Exception("Ships cannot be placed adjacent to each other!");
                        }
                    }

                    self::$console->println("Enter direction (R/D):");
                    $direction = strtoupper(readline(""));

                    if (!in_array($direction, ['R', 'D'])) {
                        throw new Exception("Invalid direction! Use R (Right) or D (Down).");
                    }

                    // Early validation for ship bounds
                    if ($direction === 'R') {
                        $endColIndex = array_search($startPos->getColumn(), Letter::$letters) + ($ship->getSize() - 1);
                        if ($endColIndex >= count(Letter::$letters)) {
                            throw new Exception("Ship cannot be placed here - it would go off the board to the right!");
                        }
                    } else { // direction is 'D'
                        $endRow = $startPos->getRow() + ($ship->getSize() - 1);
                        if ($endRow > 8) {
                            throw new Exception("Ship cannot be placed here - it would go off the board downwards!");
                        }
                    }

                    // Calculate all ship positions
                    $shipPositions = self::calculateShipPositions($startPos, $direction, $ship->getSize());

                    // Validate collisions with other ships
                    foreach ($shipPositions as $position) {
                        $pos = self::parsePosition($position);

                        if (self::isCollisionWithOtherShips($pos, self::$myFleet)) {
                            throw new Exception("Ships cannot overlap each other!");
                        }
                    }

                    // Add positions to ship
                    foreach ($shipPositions as $position) {
                        $ship->addPosition(self::parsePosition($position));
                    }

                    break; // Exit loop if successful

                } catch (Exception $e) {
                    self::$console->printColoredLn("Error: " . $e->getMessage(), Color::RED);
                    self::$console->println("Please try again.");
                }
            }
        }
    }

    private static function isCollisionWithOtherShips(Position $position, array $fleet): bool
    {
        foreach ($fleet as $ship) {
            foreach ($ship->getPositions() as $shipPosition) {
                // Sprawdź bezpośrednią kolizję
                if ($position->getColumn() === $shipPosition->getColumn() &&
                    $position->getRow() === $shipPosition->getRow()) {
                    return true;
                }

            }
        }
        return false;
    }

    private static function isPositionValid(Position $position): bool
    {
        $col = array_search($position->getColumn(), Letter::$letters);
        $row = $position->getRow();

        return $col >= 0 && $col < 8 && $row >= 1 && $row <= 8;
    }

    private static function calculateShipPositions(Position $start, string $direction, int $size): array
    {
        $positions = [];
        $startCol = array_search($start->getColumn(), Letter::$letters);
        $startRow = $start->getRow();

        for ($i = 0; $i < $size; $i++) {
            switch ($direction) {
                case 'D':
                    $positions[] = $start->getColumn() . ($startRow + $i);
                    break;
                case 'R':
                    $positions[] = Letter::$letters[$startCol + $i] . $startRow;
                    break;
            }
        }

        return $positions;
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
                echo "My fleet: \n";
                self::drawMap(self::$myFleet);
                echo "Enemy fleet: \n";
                self::drawMap(self::$enemyFleet);
                continue;
            }

            $isHit = GameController::checkIsHit(self::$enemyFleet, self::parsePosition($position));
            if (GameController::checkIsGameOver(self::$enemyFleet)) {
                self::$console->printColoredln("You are the winner!", Color::YELLOW);
                self::$console->println("\nPress Enter to quit game...");
                readline();
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

            if (GameController::checkIsGameOver(self::$enemyFleet)) {
                self::$console->println("You are the winner!");
                exit();
            }


            $position = self::getRandomPosition();
            $isHit = GameController::checkIsHit(self::$myFleet, $position);

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

            if (GameController::checkIsGameOver(self::$myFleet)) {
                self::$console->println("You lost");
                self::$console->println("\nPress Enter to quit game...");
                exit();
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

        array_push(self::$enemyFleet[3]->getPositions(), new Position('C', 6));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('C', 7));
        array_push(self::$enemyFleet[3]->getPositions(), new Position('C', 8));

        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 5));
        array_push(self::$enemyFleet[4]->getPositions(), new Position('F', 6));
    }

    private static function drawMap($fleet)
    {
        $rows = 8;
        $lines = 8;

        self::$console->printColoredLn("   A B C D E F G H", Color::YELLOW);
        self::$console->printColoredLn("  +----------------+", Color::YELLOW);

        for ($i = 1; $i <= $rows; $i++) {
            $line = $i . " |";
            for ($j = 1; $j <= $lines; $j++) {
                $position = new Position(Letter::value($j - 1), $i);
                $isShip = false;
                foreach ($fleet as $ship) {
                    if (in_array($position, $ship->getPositions())) {
                        $isShip = true;
                        break;
                    }
                }
                $line .= $isShip ? "X" : " ";
                $line .= " ";
            }
            $line .= "|";
            self::$console->printColoredLn($line, Color::YELLOW);
        }

        self::$console->printColoredLn("  +----------------+", Color::YELLOW);
    }

    /**
     *
     */
    private static function drawImpresiveMap($fleet)
    {
        $rows = 8;
        $lines = 8;

        self::$console->printColoredLn("   A B C D E F G H", Color::YELLOW);
        self::$console->printColoredLn("  +----------------+", Color::YELLOW);

        for ($i = 1; $i <= $rows; $i++) {
            $line = $i . " |";
            for ($j = 1; $j <= $lines; $j++) {
                $position = new Position(Letter::value($j - 1), $i);
                $isShip = false;
                foreach ($fleet as $ship) {
                    if (in_array($position, $ship->getPositions())) {
                        $isShip = true;
                        break;
                    }
                }
                $line .= $isShip ? "X" : " ";
                $line .= " ";
            }
            $line .= "|";
            self::$console->printColoredLn($line, Color::YELLOW);
        }

        self::$console->printColoredLn("  +----------------+", Color::YELLOW);
    }
}