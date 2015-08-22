USE suttonquest;

SET NAMES utf8;

DROP TABLE IF EXISTS update_queue;
CREATE TABLE update_queue (
	updateID INT NOT NULL AUTO_INCREMENT,
	playerID INT,
	update_type VARCHAR(10),
	update_body VARCHAR(50),
	CONSTRAINT update_queue_updateID_pk
		PRIMARY KEY (updateID),
	CONSTRAINT update_queue_playerID_fk
		FOREIGN KEY (playerID)
		REFERENCES players(playerID)
		ON DELETE SET NULL
);

DROP TABLE IF EXISTS players;
CREATE TABLE players (
	playerID INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(50),
	active VARCHAR(1),
	last_update INT,
	last_update_time DATETIME,
	locationX INT,
	locationY INT,
	CONSTRAINT players_playerID_pk
		PRIMARY KEY (playerID)
);

INSERT INTO `suttonquest`.`players` (`playerID`, `name`, `active`, `last_update`, `locationX`, `locationY`, `last_update_time`) VALUES
(1, 'Abe', 'N', 0, 1, 9, NULL),
(2, 'Dr. Mario', 'N', 0, 1, 9, NULL),
(3, 'Ronald McDonald', 'N', 0, 1, 9, NULL),
(4, 'Tony Blair', 'N', 0, 1, 9, NULL),
(5, 'Frankenstein', 'N', 0, 1, 9, NULL),
(6, 'Danger Mouse', 'N', 0, 1, 9, NULL),
(7, 'Godzilla', 'N', 0, 1, 9, NULL),
(8, 'The Mummy', 'N', 0, 1, 9, NULL),
(9, 'Yoshimitsu', 'N', 0, 1, 9, NULL),
(10, 'Donkey Kong', 'N', 0, 1, 9, NULL);

ENGINE=InnoDB DEFAULT CHARSET=latin1;
