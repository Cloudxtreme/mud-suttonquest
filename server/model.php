<?php

abstract class Teams
{
    const neutral = 0;
    const humans = 1;
    const monsters = 2;
}

class World
{
    public $rooms = array();
    public function __construct($world_file) {
        //read file
        $world = fopen($world_file, 'r');
        $x = 0;
        $y = 0;

        while(!feof($world)) {
            $line = fgets($world);
            printf($line);
            //create character array
            $chars = str_split($line);
            foreach($chars as $char) {
                switch($char) {
                    case 'T':
                        $this->rooms[$x][$y] = new Room($char, true);
                        break;
                    case '-':
                        $this->rooms[$x][$y] = new Room($char, true);
                        break;
                    case 'M':
                        $this->rooms[$x][$y] = new Room($char, true);
                        break;
                    case 'S':
                        $this->rooms[$x][$y] = new Room($char, true);
                        break;
                    default:
                        break;
                }
                $y++;
            }
            //increment x and reset y
            $x++;
            $y = 0;
        }
        fclose($world);
    }

    public function get_room($x, $y) {
        return $this->rooms[$x][$y];
    }
}

class Room
{
    private $transparent;
    protected $description;
    protected $type;
    protected $contents;
    protected $team;
    private $is_spawn;

    //pick from a list of random descriptions here
    public function __construct($_type, $_transparent)
    {
        $this->type = $_type;
        $this->transparent = $_transparent;
        $this->description = $this->random_desc();
    }

    //generate a random description for a room
    public function random_desc() {
        return 'blah';
    }

    public function get_desc() {
        return $this->description;
    }

    public function get_type() {
        return $this->type;
    }
}

class Node extends Room
{
    private $hp;
    private $power;

    public function __construct() {
        parent::__construct(true);
    }
}

class Item
{
    private $name;
    private $description;
}

class Player
{
    private $name;
    private $inventory;
}

class inventory
{
    private $items;

}
