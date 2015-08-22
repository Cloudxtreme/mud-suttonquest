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
    private $players; //stores the player location
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

    //find a player from ID
    public function get_player($playerID) {
        foreach($this->players as $player) {
            if($player->get_id() == $playerID) {
                return $player;
            }
        }
    }

    //read players from DB
    public function update_players() {
        $this->players = array();
        $dbcon = mysqli_connect("localhost","suttonquest","Xzrr71^1","suttonquest");
        $query = "SELECT * FROM players";
        if (mysqli_connect_errno()) {
			printf("Failed to connect to MySQL: " . mysqli_connect_error());
		}
        if ($result = mysqli_query($dbcon, $query))
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
		} else {
            printf("Failed to update players");
        }
        mysqli_close($dbcon);
    }

    //check if anybody else is in a room by playerID
    public function other_occupants($playerID) {
        //find playerID location, compare to each player where ID is not the same
        $player = $this->get_player($playerID);
        $playerlocation = $player->get_location();
        $locationX = $playerlocation['x'];
        $locationY = $playerlocation['y'];
        $others_in_room = array();
        foreach($this->players as $other) {
            $o_location = $other->get_location();
            if($o_location['x'] == $locationX && $o_location['y'] == $locationY && $other->get_id() != $playerID) {
                array_push($others_in_room, $other);
            }
        }
        return $others_in_room;
    }
}

//world location/node
class Node
{
    protected $transparent;
    protected $description;
    protected $type;
    protected $descriptions = array(
        "A strange ceiling is the focal point of the room before you. It's honeycombed with hundreds of holes about as wide as your head. They seem to penetrate the ceiling to some height beyond a couple feet, but you can't be sure from your vantage point.",
        "Several round pits lie in the floor of the room before you. Spaced roughly equally apart, each is about 15 feet in diameter and appears about 20 feet deep. A lattice of thick iron bars covers the top of each pit, and each lattice has a door of iron bars that can be lifted open. The pits smell of sweat and offal.",
        "This chamber is clearly a prison. Small barred cells line the walls, leaving a 15-foot-wide pathway for a guard to walk. Channels run down either side of the path next to the cages, probably to allow the prisoners' waste to flow through the grates on the other side of the room. The cells appear empty but your vantage point doesn't allow you to see the full extent of them all.",
        "You peer through the open doorway into a broad, pillared hall. The columns of stone are carved as tree trunks and seem placed at random like trees in a forest. Stone root systems crawl out into the floor and marble branches expand across the ceiling. You even note a few carvings of small birds and squirrels.",
        "Several white marble busts that rest on white pillars dominate this room. Most appear to be male or female humans of middle age, but one clearly bears small horns projecting from its forehead and another is spread across the floor in a thousand pieces, leaving one pillar empty.",
        "In the center of this large room lies a 30-foot-wide round pit, its edges lined with rusting iron spikes. About 5 feet away from the pit's edge stand several stone semicircular benches. The scent of sweat and blood lingers, which makes the pit's resemblance to a fighting pit or gladiatorial arena even stronger.",
        "This room holds six dry circular basins large enough to hold a man and a dry fountain at its center. All possess chipped carvings of merfolk and other sea creatures. It looks like this room once served some group of people as a bath.",
        "This small room contains several pieces of well-polished wood furniture. Eight ornate, high-backed chairs surround a long oval table, and a side table stands next to the far exit. All bear delicate carvings of various shapes. One bears carvings of skulls and bones, another is carved with shields and magic circles, and a third is carved with shapes like flames and lightning strokes.",
        "A large forge squats against the far wall of this room, and coals glow dimly inside. Before the forge stands a wide block of iron with a heavy-looking hammer lying atop it, no doubt for use in pounding out shapes in hot metal. Other forge tools hang in racks nearby, and a barrel of water and bellows rest on the floor nearby.",
        "This small bare chamber holds nothing but a large ironbound chest, which is big enough for a man to fit in and bears a heavy iron lock. The floor has a layer of undisturbed dust upon it.",
        "A chill crawls up your spine and out over your skin as you look upon this room. The carvings on the wall are magnificent, a symphony in stonework, but given the themes represented, it might be better described as a requiem. Scenes of death, both violent and peaceful, appear on every wall framed by grinning skeletons and ghoulish forms in ragged cloaks.",
        "This otherwise bare room has one distinguishing feature. The stone around one of the other doors has been pulled over its edges, as though the rock were as soft as clay and could be moved with fingers. The stone of the door and wall seems hastily molded together."
    );

    //pick from a list of random descriptions here
    public function __construct($_type, $_transparent)
    {
        $this->type = $_type;
        $this->transparent = $_transparent;
        $this->description = $this->get_random_desc();
    }

    public function get_desc() {
        return $this->description;
    }

    public function get_type() {
        return $this->type;
    }

    protected function get_random_desc() {
        $rand = mt_rand(0, count($this->descriptions) - 1);
        return $this->descriptions[$rand];
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
