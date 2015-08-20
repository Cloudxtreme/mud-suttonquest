<?php

class World
{
    $world = array();

    public function __construct() {
        for($int i = 0; i < 50; i++) {
            for($int j = 0; j < 50; j++) {
                $world[i][j] = new Room();
            }
        }
    }
}

class Room
{

    private $transparent;
    protected $description;
    protected $contents;
    protected $team;
    private $is_node;

    //pick from a list of random descriptions here
    public function __construct()
    {
        //set rooms here
    }
}

class Node extends Room
{
    private $hp;
    private $power;

    public function __construct();
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
?>
