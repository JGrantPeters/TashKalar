
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- TashKalarExpansions implementation : © Benjamin Wack <benjamin.wack@free.fr>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `player` ADD (
  `player_pieces_left` INT UNSIGNED NOT NULL DEFAULT 17,
  `player_legends_left` INT UNSIGNED NOT NULL DEFAULT 3,
  `player_saved_score` INT(10) NOT NULL DEFAULT 0,
  `player_last_deck` INT(10) NOT NULL DEFAULT -2,
  `player_last_card` INT(10) NOT NULL DEFAULT -1
);

CREATE TABLE IF NOT EXISTS `board` (
  `board_x` smallint(5) unsigned NOT NULL,
  `board_y` smallint(5) unsigned NOT NULL,
  `board_player` int(10) unsigned DEFAULT NULL,
  `board_rank` smallint(3) unsigned NOT NULL DEFAULT 0,
  `board_saved_player` int(10) unsigned DEFAULT NULL,
  `board_saved_rank` smallint(3) unsigned NOT NULL DEFAULT 0,
  `board_marked` tinyint(1) NOT NULL DEFAULT 0,
  `board_used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`board_x`,`board_y`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `score` (
  `score_player_id` int(10) unsigned not null,
  `score_against` int(10) unsigned not null,
  `score_value` INT(10) NOT NULL DEFAULT 0,
  `score_common` INT(10) NOT NULL DEFAULT 0,
  `score_heroic` INT(10) NOT NULL DEFAULT 0,
  `score_legendary` INT(10) NOT NULL DEFAULT 0,
  `impro` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`score_player_id`,`score_against`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `player_saved` (
  `player_id` int(10) unsigned not null,
  `player_pieces_left` INT UNSIGNED NOT NULL DEFAULT 17,
  `player_legends_left` INT UNSIGNED NOT NULL DEFAULT 3,
  `player_score` INT(10) NOT NULL DEFAULT 0,
  `player_last_deck` INT(10) NOT NULL DEFAULT -2,
  `player_last_card` INT(10) NOT NULL DEFAULT -1,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`player_id`,`step`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `board_saved` (
  `board_x` smallint(5) unsigned NOT NULL,
  `board_y` smallint(5) unsigned NOT NULL,
  `board_player` int(10) unsigned DEFAULT NULL,
  `board_rank` smallint(3) unsigned NOT NULL DEFAULT 0,
  `board_marked` tinyint(1) NOT NULL DEFAULT 0,
  `board_used` tinyint(1) NOT NULL DEFAULT 0,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`board_x`,`board_y`, `step`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `card_saved` (
  `card_id` int(10) unsigned NOT NULL,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`card_id`, `step`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `score_saved` (
  `score_player_id` int(10) unsigned not null,
  `score_against` int(10) unsigned not null,
  `score_value` INT(10) NOT NULL DEFAULT 0,
  `score_common` INT(10) NOT NULL DEFAULT 0,
  `score_heroic` INT(10) NOT NULL DEFAULT 0,
  `score_legendary` INT(10) NOT NULL DEFAULT 0,
  `impro` tinyint(1) NOT NULL DEFAULT 1,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`score_player_id`,`score_against`,`step`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `global_saved` (
  `global_id` int(10) unsigned NOT NULL,
  `global_value` int(11) NOT NULL,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`global_id`, `step`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `state_saved` (
  `state` varchar(20) NOT NULL,
  `step` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`step`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
