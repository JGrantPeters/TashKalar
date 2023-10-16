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
 * tashkalarexpansions.action.php
 *
 * TashKalarExpansions main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/tashkalarexpansions/tashkalarexpansions/myAction.html", ...)
 *
 */
  
  
  class action_tashkalarexpansions extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "tashkalarexpansions_tashkalarexpansions";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

	/* Are both really needed ? At least one for putting a chit outside summoning ? */
	public function chooseDeck()
	{
	  self::setAjaxMode();     

	  $color = self::getArg( "color", AT_alphanum, true );

	  $this->game->chooseDeck( $color );
	  
	  self::ajaxResponse( );
	}

	public function placeInitialPieces()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->placeInitialPieces( $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function playPiece()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->playPiece( $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function pickPiece()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->pickPiece( $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function clickPlace()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );
	  $player = self::getArg( "player", AT_posint, true );

	  $this->game->clickPlace( $x, $y, $player );
	  
	  self::ajaxResponse( );
	}

	/* Is there any use to this ? */
	public function playSquare()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->playSquare( $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function clickEffect()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->clickEffect( $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function movePiece()
	{
	  self::setAjaxMode();     

	  $from_x = self::getArg( "from_x", AT_posint, true );
	  $from_y = self::getArg( "from_y", AT_posint, true );
	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );

	  $this->game->movePiece( $from_x, $from_y, $x, $y );
	  
	  self::ajaxResponse( );
	}

	public function skip()
	{
	  self::setAjaxMode();
	  $this->game->skip();
	  self::ajaxResponse();
	}

	/*** DEPRECATED
	public function takeBack()
	{
	  self::setAjaxMode();

	  $this->game->takeBack();

	  self::ajaxResponse();
	}
	***/

	public function cancel()
	{
	  self::setAjaxMode();
	  $this->game->cancel();
	  self::ajaxResponse();
	}

	public function gainAction()
	{
	  self::setAjaxMode();
	  $this->game->gainActionButton();
	  self::ajaxResponse();
	}

	public function returnPending()
	{
	  self::setAjaxMode();
	  $this->game->returnPending();
	  self::ajaxResponse();
	}

	public function playCard()
	{
	  self::setAjaxMode();     

	  $x = self::getArg( "x", AT_posint, true );
	  $y = self::getArg( "y", AT_posint, true );
	  $deck_id = self::getArg( "deck_id", AT_posint, true );
	  $card_id = self::getArg( "card_id", AT_posint, true );

	  $this->game->playCard( $x, $y, $deck_id, $card_id );
	  
	  self::ajaxResponse( );
	}

	public function discardSingleCard()
	{
	  self::setAjaxMode();
	  $discarded_deck = self::getArg( "discarded_deck", AT_int, true );
	  $discarded_id = self::getArg( "discarded_id", AT_posint, true );
	  $this->game->discardSingleCard( $discarded_deck, $discarded_id );
	  self::ajaxResponse( );
	}

	public function discardCard()
	{
	  self::setAjaxMode();

	  $discarded_deck = self::getArg( "discarded_deck", AT_int, true );
	  $discarded_id = self::getArg( "discarded_id", AT_posint, true );
	  $returned_decks_raw = self::getArg( "returned_decks", AT_numberlist, true );
	  if( substr( $returned_decks_raw, -1 ) == ';' )
            $returned_decks_raw = substr( $returned_decks_raw, 0, -1 );
	  if( $returned_decks_raw == '' )
            $returned_decks = array();
	  else
            $returned_decks = explode( ';', $returned_decks_raw );
	  $returned_ids_raw = self::getArg( "returned_ids", AT_numberlist, true );
	  if( substr( $returned_ids_raw, -1 ) == ';' )
	    $returned_ids_raw = substr( $returned_ids_raw, 0, -1 );
	  if( $returned_ids_raw == '' )
            $returned_ids = array();
	  else
            $returned_ids = explode( ';', $returned_ids_raw );

	  $this->game->discardCard( $discarded_deck, $discarded_id,
				    $returned_decks, $returned_ids );
	  
	  self::ajaxResponse( );
	}

	public function playFlare()
	{
	  self::setAjaxMode();     
	  $this->game->playFlare( );
	  self::ajaxResponse( );
	}

	public function claimTask()
	{
	  self::setAjaxMode();     

	  $task_id = self::getArg( "task_id", AT_posint, true );

	  $this->game->claimTask( $task_id );
	  
	  self::ajaxResponse( );
	}

	public function chooseOption()
	{
	  self::setAjaxMode();     

	  $optnum = self::getArg( "optnum", AT_posint, true );

	  $this->game->chooseOption( $optnum );
	  
	  self::ajaxResponse( );
	}

	public function chooseColor()
	{
	  self::setAjaxMode();     

	  $id = self::getArg( "id", AT_posint, true );

	  $this->game->chooseColor( $id );
	  
	  self::ajaxResponse( );
	}

	public function chooseColorLegend()
	{
	  self::setAjaxMode();     

	  $id = self::getArg( "id", AT_posint, true );

	  $this->game->chooseColorLegend( $id );
	  
	  self::ajaxResponse( );
	}

	public function chooseColorFlare()
	{
	  self::setAjaxMode();     

	  $id = self::getArg( "id", AT_posint, true );

	  $this->game->chooseColorFlare( $id );
	  
	  self::ajaxResponse( );
	}

	public function chooseColorImpro()
	{
	  self::setAjaxMode();     

	  $id = self::getArg( "id", AT_posint, true );

	  $this->game->chooseColorImpro( $id );
	  
	  self::ajaxResponse( );
	}

	public function putCardOn()
	{
	  self::setAjaxMode();     

	  $card_id = self::getArg( "card_id", AT_posint, true );

	  $this->game->putCardOn( $card_id );
	  
	  self::ajaxResponse( );
	}

	public function undoStep()
	{
	  self::setAjaxMode();
	  $this->game->undoStep();
	  self::ajaxResponse();
	}

	public function redoStep()
	{
	  self::setAjaxMode();
	  $this->game->redoStep();
	  self::ajaxResponse();
	}

	public function playFrozen()
	{
	  self::setAjaxMode();     
	  $this->game->playFrozen( );
	  self::ajaxResponse( );
	}

	public function playWarp()
	{
	  self::setAjaxMode();  
	  $card_id = self::getArg( "card_id", AT_posint, true );
	  $this->game->playWarp( $card_id );
	  self::ajaxResponse( );
	}

    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

  }
  

