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
 * tashkalarexpansions.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in tashkalarexpansions_tashkalarexpansions.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_tashkalarexpansions_tashkalarexpansions extends game_view
  {
    function getGameName() {
        return "tashkalarexpansions";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        $this->page->begin_block( "tashkalarexpansions_tashkalarexpansions", "square" );
        
        $hor_scale = 47.5;
        $ver_scale = 47.6;
        for( $x=1; $x<=9; $x++ )
        {
            for( $y=1; $y<=9; $y++ )
            {
		if ($x+$y>3 && $x+$y<17 && $x-$y<7 && $y-$x<7)
	    	{
		    $this->page->insert_block( "square", array(
                        'X' => $x,
                    	'Y' => $y,
                    	'LEFT' => round( ($x-1)*$hor_scale+20 ),
                    	'TOP' => round( ($y-1)*$ver_scale+20 )
                	) );
		}
            }        
        }

        $this->tpl['END_WARNING'] = self::_("This is the last turn.");
        $this->tpl['AVAILABLE_TASKS'] = self::_("Available tasks");
        $this->tpl['NEXT_TASK'] = self::_("Next task");
        $this->tpl['DESTROYED_PIECES'] = self::_("Enemy pieces destroyed this turn");
        $this->tpl['MY_HAND'] = self::_("My hand");
        $this->tpl['MY_DISCARD'] = self::_("My discard");
	

        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "tashkalarexpansions_tashkalarexpansions", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

