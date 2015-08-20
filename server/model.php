<?php

abstract class Teams
{
    const neutral = 0;
    const humans = 1;
    const monsters = 2;
}

class World
{
    public $nodes = array();
    public $player = array(); //stores the player location
    public $worldstr = '';

    public function __construct($world_file) {
        //read file
        $world = fopen($world_file, 'r');
        $x = 0;
        $y = 0;

        $player_test = new Player();

        $this->players[] = $player_test;

        while(!feof($world)) {
            $line = fgets($world);
            printf($line);
            $this->worldstr .= $line;
            //create character array
            $chars = str_split($line);
            foreach($chars as $char) {
                switch($char) {
                    case 'T':
                        $this->nodes[$x][$y] = new Room('Transparent Room', true);
                        break;
                    case '-':
                        $this->nodes[$x][$y] = new Node('Opaque Room', false);
                        break;
                    case 'M':
                        $this->nodes[$x][$y] = new Spawn('Megabeast Spawn', true);
                        break;
                    case 'O':
                        $this->nodes[$x][$y] = new Objective('Capturable Objective', true);
                        break;
                    case 'S':
                        $this->nodes[$x][$y] = new Spawn('Player Spawn', true);
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
        printf("worldstr:");
        printf($this->worldstr);
        fclose($world);
    }

    public function get_node($x, $y) {
        return $this->nodes[$x][$y];
    }
}

//world location/node
class Node
{
    protected $transparent;
    protected $description;
    protected $type;

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

//transparent rooms
class Room extends Node
{
    private $contents = array();

    public function __construct($type) {
        parent::__construct($type, true);
    }
}

//capturable objectives
class Objective extends Node
{
    private $team;
    private $hp;
    private $power;

    public function __construct($type) {
        parent::__construct($type, true);
    }
}

//megabeast/player spawn nodes
class Spawn extends Node
{
    private $team;

    public function __construct($type) {
        parent::__construct($type, true);
    }
}

class Player
{
    public $name;
    private $ID;
    private $inventory;
    public $active;
    public $location = array();

    public function __construct() {
        $this->name = "jack";
        $this->ID = 1;
        $this->location = array('x' => 0, 'y' => 0);
        $this->active = false;
    }
}
