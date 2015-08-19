USE suttonquest;

SET NAMES utf8;

DROP TABLE IF EXISTS update_queue;
CREATE TABLE update_queue (
	updateID INT NOT NULL AUTO_INCREMENT,
	playerID INT NOT NULL,
	time_queued DATETIME NOT NULL,
	update_type VARCHAR(10),
	update_body VARCHAR(50),
	CONSTRAINT update_queue_updateID_pk
		PRIMARY KEY (updateID),
	CONSTRAINT update_queue_playerID_fk
		REFERENCES players(playerID)
		ON DELETE SET NULL
);

DROP TABLE IF EXISTS players;
CREATE TABLE players (
	playerID INT NOT NULL AUTO_INCREMENT,
	username VARCHAR(50),
	password VARCHAR(50),
	CONSTRAINT players_playerID_pk
		PRIMARY KEY (playerID)
);

DROP TABLE IF EXISTS inventories;
CREATE TABLE inventories (
	inventoryID INT NOT NULL AUTO_INCREMENT,
	playerID INT NOT NULL,
	CONSTRAINT inventories_inventoryID_pk
		PRIMARY KEY (inventoryID),
	CONSTRAINT inventories_playerID_fk
		REFERENCES players(playerID)
		ON DELETE CASCADE
);

DROP TABLE IF EXISTS items;
CREATE TABLE items (
	itemID INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(50),
	description VARCHAR(50),
	CONSTRAINT items_itemID_pk
		PRIMARY KEY (itemID),
);

ENGINE=InnoDB DEFAULT CHARSET=latin1;
