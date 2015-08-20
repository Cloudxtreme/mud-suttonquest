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
	username VARCHAR(50),
	password VARCHAR(50),
	last_update DATETIME,
	CONSTRAINT players_playerID_pk
		PRIMARY KEY (playerID)
);

ENGINE=InnoDB DEFAULT CHARSET=latin1;
