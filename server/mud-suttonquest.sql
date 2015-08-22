USE suttonquest;

SET NAMES utf8;

DROP TABLE IF EXISTS update_queue;
CREATE TABLE update_queue (
	updateID INT NOT NULL AUTO_INCREMENT,
	playerID INT,
	time_queued DATETIME NOT NULL,
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
	last_update DATETIME,
	locationX INT,
	locationY INT,
	CONSTRAINT players_playerID_pk
		PRIMARY KEY (playerID)
);

INSERT INTO `suttonquest`.`players` (`playerID`, `name`, `active`, `last_update`, `locationX`, `locationY`) VALUES
(1, 'Abe', 'N', NULL, 1, 9),
(2, 'Dr. Mario', 'N', NULL, 1, 9),
(3, 'Ronald McDonald', 'N', NULL, 1, 9),
(4, 'Tony Blair', 'N', NULL, 1, 9),
(5, 'Frankenstein', 'N', NULL, 1, 9),
(6, 'Danger Mouse', 'N', NULL, 1, 9),
(7, 'Godzilla', 'N', NULL, 1, 9),
(8, 'The Mummy', 'N', NULL, 1, 9),
(9, 'Yoshimitsu', 'N', NULL, 1, 9),
(10, 'Donkey Kong', 'N', NULL, 1, 9);

ENGINE=InnoDB DEFAULT CHARSET=latin1;
