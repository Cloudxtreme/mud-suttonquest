<?php
class Room
{
    private $sizeX;
    private $sizeY;

    private $north;
    private $south;
    private $east;
    private $west;
    private $up;
    private $down;

    public function __construct(Room $north, Room $east, Room $south, Room $west, Room $up, Room $down, $sizeX, $sizeY)
    {
        //set rooms here
    }
}

class Item
{
    private $name;
}

class Player
{
    private $name;
    private $inventory;
}
?>
