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
 * gameoptions.inc.php
 *
 * TashKalarExpansions game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in tashkalarexpansions.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    100 => array(
                'name' => totranslate('Form of Tash-Kalar'),    
                'values' => array(
                            1 => array( 'name' => totranslate('High Form'),
				'tmdisplay' => totranslate('High Form') ),

                            2 => array( 'name' => totranslate('Deathmatch'),
				'tmdisplay' => totranslate('Deathmatch') )
				  ),
		'startcondition' => array(
			1 => array( array( 'type' => 'maxplayers', 'value' => 2, 'message' => totranslate( 'High Form is only available for 2 players, please use Deathmatch for 3 or 4.' ) ) ),
			2 => array( )
					  )
		 ),

    101 => array(
                'name' => totranslate('Deck selection'),    
                'values' => array(
                            1 => array( 'name' => totranslate('Player selection'),
				'tmdisplay' => totranslate('Player selection') ),

                            2 => array( 'name' => totranslate('Random selection'),
				'tmdisplay' => totranslate('Random selection') ),

                            3 => array( 'name' => totranslate('Random selection after bans'),
				'tmdisplay' => totranslate('Random selection after bans'),
				'beta' => true ),

                            4 => array( 'name' => totranslate('Mutual selection'),
				'tmdisplay' => totranslate('Mutual selection') ),
				'beta' => true )
                        )
    ),

    102 => array(
        'name' => totranslate('Everfrost'),
        'values' => array(
            1 => array( 'name' => totranslate('On'),
                        'tmdisplay' => totranslate('Everfrost'),
                        'nobeginner' => true ),
            0 => array( 'name' => totranslate('Off'),
                        'tmdisplay' => totranslate('') ),
        ),
        'default' => 1
    ),
        
    103 => array(
        'name' => totranslate('Nethervoid'),
        'values' => array(
            1 => array( 'name' => totranslate('On'),
                        'tmdisplay' => totranslate('Nethervoid'),
                        'nobeginner' => true,
                        'beta' => true ),
            0 => array( 'name' => totranslate('Off'),
                        'tmdisplay' => totranslate('') ),
        ),
        'default' => 1
    ),
        
    104 => array(
        'name' => totranslate('Etherweave'),
        'values' => array(
            1 => array( 'name' => totranslate('On'),
                        'tmdisplay' => totranslate('Etherweave'),
                        'nobeginner' => true,
                        'beta' => true ),
            0 => array( 'name' => totranslate('Off'),
                        'tmdisplay' => totranslate('') ),
        ),
        'default' => 1
    ),
        


    /* Example of game variant:
    
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'),  'beta' => true, 'nobeginner' => true ),) )
                        )
            )

    */

);


