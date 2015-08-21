<?php

abstract class Teams
{
    const neutral = 0;
    const humans = 1;
    const monsters = 2;
}

class World
{
    private $nodes = array();
    private $players = array(); //stores the player location
    private $worldstr = '';

    public function __construct($world_file) {
        //read file
        $world = fopen($world_file, 'r');
        $x = 0; $y = 0;

        //world gen
        while(!feof($world)) {
            $line = fgets($world);
            $this->worldstr .= $line;
            $chars = str_split($line); //create character array
            foreach($chars as $char) {
                switch($char) {
                    case 'T': $this->nodes[$x][$y] = new Room('Transparent Room', true); break;
                    case '-': $this->nodes[$x][$y] = new Node('Opaque Room', false); break;
                    case 'M': $this->nodes[$x][$y] = new Spawn('Megabeast Spawn', true); break;
                    case 'O': $this->nodes[$x][$y] = new Objective('Capturable Objective', true); break;
                    case 'S': $this->nodes[$x][$y] = new Spawn('Player Spawn', true); break;
                    default: break;
                }
                $y++;
            }
            $x++;
            $y = 0;
        }

        //update players
        $this->update_players();

        //write players to DB?
        printf($this->worldstr);
        fclose($world);
    }

    public function get_node($x, $y) {
        return $this->nodes[$x][$y];
    }

    public function get_worldstr() {
        return $this->worldstr;
    }

    public function get_player($id) {
        return $this->players[$id];
    }

    //read players from DB
    public function update_players() {
        $this->_dbcon = mysqli_connect("localhost","suttonquest","Xzrr71^1","suttonquest");
        $query = "SELECT * FROM players WHERE active='N'";
        if (mysqli_connect_errno()) {
			printf("Failed to connect to MySQL: " . mysqli_connect_error());
		}
        if ($result = mysqli_query($this->_dbcon, $query))
		{
			$tempArray = array();
			//loop through each row in the result set
			while($row = $result->fetch_object())
			{
                $temp = new Player($row->name, $row->playerID, $row->locationX, $row->locationY);
                if($row->active == 'Y') {
                    $temp->set_active(true);
                } else {
                    $temp->set_active(false);
                }
                array_push($this->players, $temp);
			}
            //print_r($tempArray[0]->playerID);
            print_r($this->players);
		} else {
            printf("Failed to initialise players");
        }
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

class Creature {
    protected $hp;
    protected $name;
    protected $location = array();

    public function __construct($_name, $_x, $_y) {
        $this->name = $_name;
        $this->location = array('x' => $_x, 'y' => $_y);
    }

    public function get_location() {
        return $this->location;
    }

    public function set_location($_x, $_y) {
        $this->location['x'] = $_x;
        $this->location['y'] = $_y;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_hp() {
        return $this->hp;
    }
}
/*
class Megabeast {
    public function __construct($_name, $_id, $_x, $_y) {
        parent::__construct($_name, $_x, $_y);
    }
}*/

class Player extends Creature
{
    private $id;
    private $active;

    public function __construct($_name, $_id, $_x, $_y) {
        parent::__construct($_name, $_x, $_y);
        $this->id = $_id;
        $this->active = false;
    }

    public function get_id() {
        return $this->id;
    }

    public function is_active() {
        return $this->active;
    }

    public function set_active($_active) {
        $this->active = $_active;
    }
}
