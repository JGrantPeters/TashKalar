<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TashKalarExpansions implementation : © Benjamin Wack <benjamin.wack@free.fr>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * TashKalarExpansions game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice ("Your game configuration" section):
    http://en.studio.boardgamearena.com/admin/studio
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),

	"northern_choice" => array("id" => 11,
				   "name" => totranslate("Northern imperial school: choice order"),
				   "type" => "int" ),

	"southern_choice" => array("id" => 12,
				   "name" => totranslate("Southern imperial school: choice order"),
				   "type" => "int" ),

	"highland_choice" => array("id" => 13,
				   "name" => totranslate("Highland school: choice order"),
				   "type" => "int" ),

	"sylvan_choice" => array("id" => 14,
				   "name" => totranslate("Sylvan school: choice order"),
				 "type" => "int" ),

	"everfrost_choice" => array("id" => 15,
				   "name" => totranslate("Everfrost school: choice order"),
				   "type" => "int" ),
	"nethervoid_choice" => array("id" => 16,
				   "name" => totranslate("Nethervoid school: choice order"),
				   "type" => "int" ),
	"etherweave_choice" => array("id" => 17,
				   "name" => totranslate("Etherweave school: choice order"),
				   "type" => "int" )



/*
        Examples:


        "table_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("table test stat 1"), 
                                "type" => "int" ),
                                
        "table_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("table test stat 2"), 
                                "type" => "float" )
*/  
    ),
    
    // Statistics existing for each player
    "player" => array(

        "common_summoned" => array("id"=> 10,
                    "name" => totranslate("Common beings summoned"),
                    "type" => "int" ),
    
        "heroic_summoned" => array("id"=> 11,
                    "name" => totranslate("Heroic beings summoned"),
                    "type" => "int" ),
    
        "legendary_summoned" => array("id"=> 12,
                    "name" => totranslate("Legendary beings summoned"),
                    "type" => "int" ),
    
        "flares_invoked" => array("id"=> 13,
                    "name" => totranslate("Flares invoked"),
                    "type" => "int" ),
    
        "tasks_claimed" => array("id"=> 14,
                    "name" => totranslate("Tasks claimed"),
                    "type" => "int" ),
    
        "cards_discarded" => array("id"=> 15,
                    "name" => totranslate("Cards discarded"),
                    "type" => "int" ),
    
        "pieces_destroyed" => array("id"=> 16,
                    "name" => totranslate("Enemy pieces destroyed"),
                    "type" => "int" ),
    
        "pieces_placed" => array("id"=> 17,
                    "name" => totranslate("Pieces placed"),
                    "type" => "int" ),
    
        "pieces_moved" => array("id"=> 18,
                    "name" => totranslate("Pieces moved"),
                    "type" => "int" ),
    
        "pieces_upgraded" => array("id"=> 19,
                    "name" => totranslate("Pieces upgraded"),
                    "type" => "int" ),
    
        "improvised_summonings" => array("id"=> 20,
                    "name" => totranslate("Improvised Summonings"),
                    "type" => "int" ),

        "imperial_played" => array("id"=> 21,
                    "name" => totranslate("Played with the Imperial school"),
                    "type" => "bool" ),

        "highland_played" => array("id"=> 22,
                    "name" => totranslate("Played with the Highland school"),
                    "type" => "bool" ),

        "sylvan_played" => array("id"=> 23,
                    "name" => totranslate("Played with the Sylvan school"),
                    "type" => "bool" ),

        "everfrost_played" => array("id"=> 24,
                    "name" => totranslate("Played with the Everfrost school"),
                    "type" => "bool" ),

        "nethervoid_played" => array("id"=> 25,
                    "name" => totranslate("Played with the Nethervoid school"),
                    "type" => "bool" ),

        "etherweave_played" => array("id"=> 26,
                    "name" => totranslate("Played with the Etherweave school"),
                    "type" => "bool" ),

/*
        Examples:    
        
        
        "player_teststat1" => array(   "id"=> 10,
                                "name" => totranslate("player test stat 1"), 
                                "type" => "int" ),
                                
        "player_teststat2" => array(   "id"=> 11,
                                "name" => totranslate("player test stat 2"), 
                                "type" => "float" )

*/    
    )

);
