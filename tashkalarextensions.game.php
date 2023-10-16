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
  * tashkalarexpansions.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

/* Functions that require tweaking wrt frozen effects : */
/* enrichCard, argActionChoice, stNextEffect */
/* OK if frozen are stored as phony cards : */
/* clickEffect, movePiece, clickPlace, skip, getCurrentEffect, */
/* argEffectInput, argPlaceInput, */

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class TashKalarExpansions extends Table
{
	function __construct( )
	{ 


        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();self::initGameStateLabels( array( 
	      /* General info about the turn state */
	       "deck_played" => 10, /* -1 for flares */
	       "card_played" => 11,
	       "effect_number" => 12,
	       "card_x" => 13,
	       "card_y" => 14,
	       "remaining_actions" => 15,
	       /* to remember last piece used */
	       "piece_x" => 16,
	       "piece_y" => 17,
	       "piece_rank" => 18,
	       "piece_player" => 19,
	       /* and the piece before that */
	       "piece_before_x" => 20,
	       "piece_before_y" => 21,
	       "piece_before_player" => 22,
	       /* For last direction being moved or shot */
	       "last_dx" => 23,
	       "last_dy" => 24,
	       "flare_upgraded" => 25,
	       "flare_pieces" => 26,
	       "flare_rank" => 27,
	       "option_chosen" => 28,
	       "turn_discarded" => 29,
	       "extra_turn" => 30,
	       "extra_deck" => 31,
	       "extra_legends" => 32,
	       "card_put_on_top" => 33,
	       "last_player" => 34,
	       "last_impro" => 35,
		   "frozen_effect" => 36,
		   "gateway_x" => 37,
		   "gateway_y" => 38,
	           "pending_being" => 39,

	       /* What the being currently summoned has done, useful for effects triggers */
	       "being_destroyed" => 40,
	       "being_moved" => 41,
	       "being_destroyed_legendary" => 42,
	       "has_skipped" => 43,
	       "being_upgraded_piece" => 44,
	       "being_placed_piece" => 45,
	       "combat_moves" => 46,
	       "bonus_improvisation" => 47,
	       "being_destroyed_common" => 48,
	       "being_destroyed_heroic" => 49,
	       "being_downgraded_piece" => 50,
	       "fullmoon" => 51,
	       "flare_discarded" => 52,
	       "action_malus" => 53,
	       "oversummoned" => 54,
	       "piece_removed" => 55,
		   "legend_removed" => 56,
		   "summoning_color" => 57,

		   /* for recording a chosen square */
		   "square_x" => 58,
		   "square_y" => 59,

	       /* What the player has achieved during his turn, useful for tasks claims and stats */
	       "turn_destroyed_common" => 60,
	       "turn_destroyed_heroic" => 61,
	       "turn_destroyed_legendary" => 62,
	       "turn_summoned_beings" => 63,
	       "turn_summoned_legends" => 64,
	       "turn_summoned_red" => 65,
	       "turn_summoned_green" => 66,
	       "turn_summoned_common" => 67,
	       "turn_placed" => 68,
	       "turn_moved" => 69,
	       "turn_upgraded" => 70,
		   "log_chunk" => 71,
		   "additional_destroyed" => 72,
		   "eternal_emperor_warped" => 73,
	       "merchant_rank" => 74,
	       "merchant_player" => 75,
	       "void_summoning" => 76,
	       "to_be_discarded" => 77,
	       
	           "turn_counter" => 97,
	       "maxstep" => 98,
	       "step" => 99,
	       "game_form" => 100,
	       "deck_selection" => 101,
           "Everfrost_set" => 102,
           "Nethervoid_set" => 103,
           "Etherweave_set" => 104,
	       
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );
        
	$this->cards = self::getNew( "module.common.deck" );
	$this->cards->init( "card" );

	/* $this->flaresdeck = self::getNew( "module.common.deck" ); */
	/* $this->flaresdeck->init( "flare" ); */

	}
	
    protected function getGameName( )
    {
        return "tashkalarexpansions";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        $sql = "DELETE FROM player WHERE 1 ";
        self::DbQuery( $sql ); 

        // Create players (no colors at the beginning)
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_pieces_left, player_legends_left) VALUES ";

        $values = array( );

	$available_pieces = self::pieceCount( $players );
        foreach( $players as $player_id => $player )
        {
            $values[] = "('".$player_id."','000000','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',".$available_pieces.",3)";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reloadPlayersBasicInfos();

	// Init scores (for deathmatch)
	$sql = "INSERT INTO score (score_player_id, score_against, score_value, score_common, score_heroic, score_legendary, impro) VALUES ";

	$values = array( );

	foreach( $players as $player_id => $player )
	  {
	    foreach( $players as $op_id => $op )
	      {
		if ( $op_id != $player_id )
		  {
		    if ( count($players) == 2 )
		      $values[] = "('".$player_id."','".$op_id."',0,0,0,0,0)";
		    else
		      $values[] = "('".$player_id."','".$op_id."',0,0,0,0,1)";
		  }
	      }
	  }
	$sql .= implode( $values, ',' );
	self::DbQuery( $sql );
        
        // Create cards (player decks, legends and flares)
        foreach( $this->decks as $deck ) // 
	  {
	    $cards = array();
	    $max_index = min( 18, count($this->card_contents[$deck]) );
	    for( $index=0 ; $index<$max_index ; $index++ )
	      {
                $cards[] = array( 'type' => $deck, 'type_arg' => $index, 'nbr' => 1);
	      }
	    $this->cards->createCards( $cards, $deck.'Deck' );
	    $this->cards->shuffle( $deck.'Deck' );
	  }

	if ( self::getGameStateValue( 'game_form' ) == 1 )
	  {
	    $cards = array();
	    for( $index = 0 ; $index < 24 ; $index++ )
	      $cards[] = array( 'type' => "task", 'type_arg' => $index, 'nbr' => 1);
	    $this->cards->createCards( $cards, 'TasksDeck' );
	    $this->cards->shuffle( 'TasksDeck' );
	  }

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
	self::setGameStateInitialValue( "deck_played", -2 );
	self::setGameStateInitialValue( "card_played", -1 );
	self::setGameStateInitialValue( "effect_number", -2 );
	self::setGameStateInitialValue( "card_x", 0 );
	self::setGameStateInitialValue( "card_y", 0 );
	self::setGameStateInitialValue( "remaining_actions", 1 );
	self::setGameStateInitialValue( "piece_x", 0 );
	self::setGameStateInitialValue( "piece_y", 0 );
	self::setGameStateInitialValue( "piece_rank", 0 );
	self::setGameStateInitialValue( "piece_player", 0 );
	self::setGameStateInitialValue( "piece_before_x", 0 );
	self::setGameStateInitialValue( "piece_before_y", 0 );
	self::setGameStateInitialValue( "piece_before_player", 0 );
	self::setGameStateInitialValue( "last_dx", 0 );
	self::setGameStateInitialValue( "last_dy", 0 );
	self::setGameStateInitialValue( "flare_upgraded", 0 );
	self::setGameStateInitialValue( "flare_pieces", 0 );
	self::setGameStateInitialValue( "flare_rank", 0 );
	self::setGameStateInitialValue( "option_chosen", 0 );
	self::setGameStateInitialValue( "turn_discarded", 0 );
	self::setGameStateInitialValue( "extra_turn", 0 );
	self::setGameStateInitialValue( "extra_deck", 0 );
	self::setGameStateInitialValue( "extra_legends", 0 );
	self::setGameStateInitialValue( "card_put_on_top", 0 );
	self::setGameStateInitialValue( "last_player", 0 );
	self::setGameStateInitialValue( "last_impro", 0 );
	self::setGameStateInitialValue( "frozen_effect", -1 );
	self::setGameStateInitialValue( "gateway_x", -1 );
	self::setGameStateInitialValue( "gateway_y", -1 );
	self::setGameStateInitialValue( "pending_being", -1 );

	self::setGameStateInitialValue( "being_destroyed", 0 );
	self::setGameStateInitialValue( "being_destroyed_legendary", 0 );
	self::setGameStateInitialValue( "being_destroyed_common", 0 );
	self::setGameStateInitialValue( "being_destroyed_heroic", 0 );
	self::setGameStateInitialValue( "being_moved", 0 );
	self::setGameStateInitialValue( "has_skipped", 0 );
	self::setGameStateInitialValue( "being_upgraded_piece", 0 );
	self::setGameStateInitialValue( "being_downgraded_piece", 0 );
	self::setGameStateInitialValue( "being_placed_piece", 0 );
	self::setGameStateInitialValue( "combat_moves", 0 );
	self::setGameStateInitialValue( "fullmoon", 0 );
	self::setGameStateInitialValue( "bonus_improvisation", 0 );
	self::setGameStateInitialValue( "flare_discarded", 0 );
	self::setGameStateInitialValue( "action_malus", 0 );
	self::setGameStateInitialValue( "oversummoned", 0 );
	self::setGameStateInitialValue( "piece_removed", 0 );
	self::setGameStateInitialValue( "legend_removed", 0 );
	self::SetGameStateInitialValue( "summoning_color", 0 );
	self::SetGameStateInitialValue( "square_x", 0 );
	self::SetGameStateInitialValue( "square_y", 0 );

	self::setGameStateInitialValue( "turn_destroyed_common", 0 );
	self::setGameStateInitialValue( "turn_destroyed_heroic", 0 );
	self::setGameStateInitialValue( "turn_destroyed_legendary", 0 );
	self::setGameStateInitialValue( "turn_summoned_beings", 0 );
	self::setGameStateInitialValue( "turn_summoned_legends", 0 );
	self::setGameStateInitialValue( "turn_summoned_red", 0 );
	self::setGameStateInitialValue( "turn_summoned_green", 0 );
	self::setGameStateInitialValue( "turn_summoned_common", 0 );
	self::setGameStateInitialValue( "turn_placed", 0 );
	self::setGameStateInitialValue( "turn_moved", 0 );
	self::setGameStateInitialValue( "turn_upgraded", 0 );

	self::setGameStateInitialValue( "turn_counter", 0 );
	self::setGameStateInitialValue( "log_chunk", 0 );
	self::setGameStateInitialValue( "additional_destroyed", 0 );
	self::setGameStateInitialValue( "eternal_emperor_warped", 0);
	self::setGameStateInitialValue( "merchant_rank", 0);
	self::setGameStateInitialValue( "merchant_player", 0);
	self::setGameStateInitialValue( "void_summoning", 0 );
	self::setGameStateInitialValue( "to_be_discarded", -1 );
	self::setGameStateInitialValue( "step", 0 );
	self::setGameStateInitialValue( "maxstep", 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat( 'table', 'turns_number', 0 );
        self::initStat( 'table', 'northern_choice', 0 );
        self::initStat( 'table', 'southern_choice', 0 );
        self::initStat( 'table', 'highland_choice', 0 );
        self::initStat( 'table', 'sylvan_choice', 0 );
        self::initStat( 'table', 'everfrost_choice', 0 );
        self::initStat( 'player', 'common_summoned', 0 );
        self::initStat( 'player', 'heroic_summoned', 0 );
        self::initStat( 'player', 'legendary_summoned', 0 );
        self::initStat( 'player', 'flares_invoked', 0 );
        self::initStat( 'player', 'tasks_claimed', 0 );
        self::initStat( 'player', 'cards_discarded', 0 );
        self::initStat( 'player', 'pieces_destroyed', 0 );
        self::initStat( 'player', 'pieces_placed', 0 );
        self::initStat( 'player', 'pieces_moved', 0 );
        self::initStat( 'player', 'pieces_upgraded', 0 );
        self::initStat( 'player', 'improvised_summonings', 0 );
        self::initStat( 'player', 'imperial_played', false );
        self::initStat( 'player', 'highland_played', false );
        self::initStat( 'player', 'sylvan_played', false );
		self::initStat( 'player', 'everfrost_played', false );
		self::initStat( 'player', 'nethervoid_played', false );
		self::initStat( 'player', 'etherweave_played', false );

        // Init the board
        $sql = "INSERT INTO board (board_x,board_y,board_player) VALUES ";
        $sql_values = array();
        for( $x=1; $x<=9; $x++ )
        {
            for( $y=1; $y<=9; $y++ )
            {
	      if ( self::onBoard( $x, $y ) )
		{
		  $token_value = "NULL";
		  $sql_values[] = "('$x','$y',$token_value)";
		  $sql .= "($x,$y,$token_value),";
		}
            }
        }
	$sql = rtrim($sql, ",");
        self::DbQuery( $sql );
	  
        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
      ATTENTION: en prod les tables sont dupliquées 4 fois (avec les préfixes "zz_replay1_", "zz_replay2_", "zz_replay3_" et "zz_savepoint_") pour les besoin du framework BGA, et il faut donc faire 4 fois le travail. Voici un exemple de alter table:

        self::DbQuery( "ALTER TABLE  `vardb` CHANGE  `value`  `value` LONGBLOB NOT NULL ;" );
        self::DbQuery( "ALTER TABLE  `zz_replay1_vardb` CHANGE  `value`  `value` LONGBLOB NOT NULL ;" );
        self::DbQuery( "ALTER TABLE  `zz_replay2_vardb` CHANGE  `value`  `value` LONGBLOB NOT NULL ;" );
        self::DbQuery( "ALTER TABLE  `zz_replay3_vardb` CHANGE  `value`  `value` LONGBLOB NOT NULL ;" );
        self::DbQuery( "ALTER TABLE  `zz_savepoint_vardb` CHANGE  `value`  `value` LONGBLOB NOT NULL ;" );
    */
    
    function upgradeTableDb( $from_version )
    {
      $players = self::loadPlayersBasicInfos();
      if( $from_version <= 1502101956 )
        {
	  foreach (array( "", "zz_replay1_", "zz_replay2_", "zz_replay3_", "zz_savepoint_" ) as $prefix)
	    {
	      // New tables
	      self::DbQuery( "CREATE TABLE IF NOT EXISTS `".$prefix."score` ( `score_player_id` int(10) unsigned not null, `score_against` int(10) unsigned not null, `score_value` INT(10) NOT NULL DEFAULT 0, PRIMARY KEY (`score_player_id`,`score_against`) ) ENGINE=InnoDB;" );
	      self::DbQuery( "CREATE TABLE IF NOT EXISTS `".$prefix."score_saved` ( `score_player_id` int(10) unsigned not null, `score_against` int(10) unsigned not null, `score_value` INT(10) NOT NULL DEFAULT 0, `step` smallint(5) unsigned NOT NULL, PRIMARY KEY (`score_player_id`,`score_against`,`step`) ) ENGINE=InnoDB;" );

	      // New fields in existing tables

	      self::DbQuery( "ALTER TABLE `".$prefix."score` ADD (`score_common` INT(10) NOT NULL DEFAULT 0, `score_heroic` INT(10) NOT NULL DEFAULT 0, `score_legendary` INT(10) NOT NULL DEFAULT 0, `impro` tinyint(1) NOT NULL DEFAULT 1)" );

	      self::DbQuery( "ALTER TABLE `".$prefix."score_saved` ADD (`score_common` INT(10) NOT NULL DEFAULT 0, `score_heroic` INT(10) NOT NULL DEFAULT 0, `score_legendary` INT(10) NOT NULL DEFAULT 0, `impro` tinyint(1) NOT NULL DEFAULT 1)" );

	    }

	      // Populate new tables
	      $sql = "INSERT INTO score (score_player_id, score_against, score_value, score_common, score_heroic, score_legendary, impro) VALUES ";

	      $values = array( );

	      foreach( $players as $player_id => $player )
		{
		  foreach( $players as $op_id => $op )
		    {
		      if ( $op_id != $player_id )
			$values[] =
			  "('".$player_id."','".$op_id."',0,0,0,0,0)";
		    }
		}
	      $sql .= implode( $values, ',' );
	      self::DbQuery( $sql );
	      
	  // New globals
	  self::setGameStateValue( "last_impro", 0 );
	  self::setGameStateValue( "frozen_effect", -1 );
	  self::setGameStateValue( "flare_discarded", 0 );
	  self::setGameStateValue( "action_malus", 0 );

	  // New stats
	  foreach ($players as $id => $player)
	    {
	      switch ($player['player_color']) {
	      case '037cb1':
	      case 'dc2515':
		self::setStat( true, 'imperial_played', $id );
		break;
	      case 'd6b156':
		self::setStat( true, 'highland_played', $id );
		break;
	      case '8ec459':
		self::setStat( true, 'sylvan_played', $id );
		break;
	      }
	    }
        }
      if( $from_version <= 1506101454 )
        {
	  self::setGameStateValue( "oversummoned", 0 );
	  self::setGameStateValue( "piece_removed", 0 );
	  self::setGameStateValue( "legend_removed", 0 );
	  foreach ($players as $id => $player)
	    {
	      if ($player['player_color'] == 'f0f9ff')
		{
		  self::setStat( true, 'everfrost_played', $id );
		}
	    }
	}
      //* SCC NEW
      if( $from_version <= 1905092105 )
	{
	    foreach ($players as $id => $player)
	    {
	      if ($player['player_color'] == 'f4913c')
		self::setStat( true, 'nethervoid_played', $id );
	      if ($player['player_color'] == '6a548f')
		self::setStat( true, 'etherweave_played', $id );
	    }
	  self::setGameStateValue( "gateway_x", -1 );
	  self::setGameStateValue( "gateway_y", -1 );
	  self::setGameStateValue( "summoning_color", 0 );
	  self::setGameStateValue( "turn_counter", 0 );
	  self::setGameStateValue( "pending_being", -1 );
	  self::setGameStateValue( "square_x", 0 );
	  self::setGameStateValue( "square_y", 0 );
	  self::setGameStateValue( "eternal_emperor_warped", 0 );
	  self::setGameStateValue( "merchant_rank", 0);
	  self::setGameStateValue( "merchant_player", 0);
	  self::setGameStateValue( "void_summoning", 0);
	  self::setGameStateValue( "to_be_discarded", -1);
	  self::setGameStateValue( "additional_destroyed", 0 );
	  $frozen = self::getGameStateValue( 'frozen_effect' );
	  if ( $frozen >= 0 ) {
	      $thecard = self::getUniqueCardOfType( $this->cards,
						       "Everfrost", $frozen );
	      $this->cards->insertCardOnExtremePosition( $thecard['id'],
							 "frozen", true );
	  }
	}
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array( 'players' => array() );
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, player_color color, player_last_deck deck, player_last_card card FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
	foreach ( $result['players'] as $id => $player )
	  if ( $player['deck'] != -2 )
	    $result['players'][$id] = self::enrichCard( $player );
	
        // Gather all information about current game situation (visible by player $current_player_id).
	if ( self::getGameStateValue( 'game_form' ) == 1 )
	  $result['game_form'] = 'highform';
	else
	  $result['game_form'] = 'deathmatch';

	$result['last_player'] = self::getGameStateValue( "last_player" );

  	$sql = "SELECT board_x x, board_y y, board_player player, board_rank rank FROM board WHERE board_player IS NOT NULL";
	$result['board'] = self::getObjectListFromDb( $sql );

	$result['hand'] = self::getEnrichedCardsInLocation( $this->cards, 'hand', $current_player_id , "card_type");
	if ( isset( $result['players'][$current_player_id] ) ) {
	  $color = $result['players'][$current_player_id]['color'];
	  if ( $color != '000000' ) {
	    $school = $this->decks[
			     array_search($color,
					  $this->schools_colors)];
	    $result['discard'] = self::enrichCards( array_merge(
		self::getCardsOfTypeInLocation( $school, "discard" ),
		self::getCardsOfTypeInLocation( $school, "discard_buffer")));
	  }
	}
	
	if ( self::getGameStateValue( 'game_form' ) == 1 )
	  {
	    $current_tasks = $this->cards->getCardsInLocation( 'current_tasks' );
	    $result['current_tasks'] = self::enrichTasks( $current_tasks );

	    $next_task = $this->cards->getCardsInLocation( 'next_task' );
	    $result['next_task'] = self::enrichTasks( $next_task );

	    $result['claimed'] = array();
	    foreach ( $result['players'] as $id => $player)
	      $result['claimed'][$id] = self::enrichTasks( $this->cards->getCardsInLocation( 'claimed', $id ) );
	  }

	$result['card_x'] = self::getGameStateValue('card_x');
	$result['card_y'] = self::getGameStateValue('card_y');

	$p = self::computePiecesDifferentials();
	if ( isset($p[$current_player_id]) )
	  $result['differentials'] = $p[$current_player_id];
	else
	{
          foreach ($p as $id=>$player) {
  	    unset($p[$id]['args']['flarenum']);
	    // Too much of a mess to display with more than 2 players
	    if (count($p) > 2) {
	      foreach ($this->schools_colors as $c) {
		unset($p[$id]['args']['piecesdiff_vs_'.$c]);
		unset($p[$id]['args']['upgradeddiff_vs_'.$c]);
	      }
	      unset($p[$id]['args']['piecesdiff_vs_000000']);
	      unset($p[$id]['args']['upgradeddiff_vs_000000']);
            }
  	  }
	  $result['differentials'] = $p;
	}

	$maxstep = self::getGameStateValue("maxstep");
	$step = self::getGameStateValue("step");
	$result['before'] = $step;
	$result['after'] = $maxstep - $step;

	/* $player_id = self::getActivePlayerId(); */
	/* foreach ( $result['players'] as $id => $player ) */
	/*   { */
	/*     if ( $id != $player_id ) */
	/*       $enemy = $id; */
	/*   } */
	$result['destroyed_args']['game_form'] = self::getGameStateValue( 'game_form' );
	$result['destroyed_args']['scores_dm'] = self::getDoubleKeyCollectionFromDb( "SELECT score_player_id id, score_against against, score_value value, score_common common, score_heroic heroic, score_legendary legendary, impro impro FROM score" );

	$result['frozen'] = self::getGameStateValue( 'frozen_effect' );
	if ( $result['frozen'] >= 0 )
	  $result['frozentext'] = $this->everfrost_contents[$result['frozen']]['frozentext'];
	$result['pending'] = self::getGameStateValue( 'pending_being' );
	if ( $result['pending'] >= 0 ) {
	  $result['pendingname'] = 
	    $this->etherweave_contents[$result['pending']]['name'];
	  $result['pendingtext'] = 
	    $this->etherweave_contents[$result['pending']]['text'];
	  $result['warptext'] =
	    $this->etherweave_contents[$result['pending']]['warptext'];
	}

	$result['gateway_x'] = self::getGameStateValue( 'gateway_x' );
	$result['gateway_y'] = self::getGameStateValue( 'gateway_y' );
	$result['turn_counter'] = self::getGameStateValue( 'turn_counter' );
	$result['merchant_player'] = self::getGameStateValue( 'merchant_player' );
	$result['merchant_rank'] = self::getGameStateValue( 'merchant_rank' );

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
      $players = self::getCollectionFromDB( "SELECT player_id, player_score, player_color FROM player" );
      $scores = self::getCollectionFromDB( "SELECT score_player_id, MAX(score_value) FROM score GROUP BY score_player_id", true );
      $scoremax = 0;
      $cards = $this->cards->countCardsInLocations();
      $cardsmin = 15;
      foreach ( $players as $id => $player )
	{
	  if ( count($players) == 2 && $player['player_score'] > $scoremax )
	    $scoremax = $player['player_score'];
	  if ( count($players) > 2 && $scores[$id] > $scoremax )
	    $scoremax = $scores[$id];
	  $school = $this->decks[
			     array_search($player['player_color'],
					  $this->schools_colors)];
	  if ( isset( $cards[$school.'Deck'] ) )
	    {
	      if ( $cards[$school.'Deck'] < $cardsmin )
		$cardsmin = $cards[$school.'Deck'];
	    }
	  else
	    $cardsmin = 0;
	}

      $end_score = self::endScore( $players );
      $scoreprogression = min( 100,
			       max( $scoremax * 100 / $end_score,
				    (15 - $cardsmin) * 100 / 15 ) );

      return $scoreprogression;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    /************* For testing only ************/
    /*
    function wipe()
    { 
      $sql = "UPDATE board SET board_player=NULL";
      self::DbQuery($sql);
    }
 
    function giveMeCards()
    {
      $player_id = self::getCurrentPlayerId();
      $color = self::getCurrentPlayerColor();
      $school = $this->decks[array_search($color, $this->schools_colors)];

      $cards = $this->cards->getCardsOfType( $school );
      $this->cards->moveCards( array_keys($cards), 'hand', $player_id );
      $cards = self::getCardsOfTypeInLocation( "Legends", "discard" );
      $this->cards->moveCards( array_keys($cards), 'hand', $player_id );
    }
    
    function giveDeckCards()
    {
      $player_id = self::getCurrentPlayerId();
      $color = self::getCurrentPlayerColor();
      $school = $this->decks[array_search($color, $this->schools_colors)];

      $cards = self::getCardsOfTypeInLocation( $school, "discard" );
      $this->cards->moveCards( array_keys($cards), $school.'Deck' );
      $this->cards->shuffle( $school.'Deck' );
    }
    */
    /************* For testing only ************/

    function myNotifyAllPlayers( $type, $log, $args )
    {
      if ($log != '')
	{
	  self::notifyAllPlayers( $type, 
		'<span class="logchunk logchunk_${chunk}">${orig}</span>',
		array_merge( $args,
			array(
			   'chunk' => self::getGameStateValue( "log_chunk" ),
			   'orig' => array( 'log' => $log, 'args' => $args )
			      ) ) );
	}
      else
	self::notifyAllPlayers( $type, '', $args );
    }
    
    function myNotifyPlayer( $player, $type, $log, $args )
    {
      if ($log != '')
	{
	  self::notifyPlayer( $player, $type, 
		'<span class="logchunk logchunk_${chunk}">${orig}</span>',
		array_merge( $args,
			array(
			   'chunk' => self::getGameStateValue( "log_chunk" ),
			   'orig' => array( 'log' => $log, 'args' => $args )
			      ) ) );
	}
      else
	self::notifyPlayer( $player, $type, '', $args );
    }

    function saveGameState ( $state )
    {
      $step = self::getGameStateValue("step") + 1;
      self::IncGameStateValue("log_chunk", 1);

      self::DbQuery( "DELETE FROM board_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO board_saved SELECT board_x, board_y, board_player, board_rank, board_marked, board_used, $step FROM board" );
      
      self::DbQuery( "DELETE FROM card_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO card_saved SELECT card_id, card_type, card_type_arg, card_location, card_location_arg, $step FROM card" );

      self::DbQuery( "DELETE FROM player_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO player_saved SELECT player_id, player_pieces_left, player_legends_left, player_score, player_last_deck, player_last_card, $step FROM player" );

      self::DbQuery( "DELETE FROM score_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO score_saved (score_player_id, score_against, score_value, score_common, score_heroic, score_legendary, impro, step) SELECT score_player_id, score_against, score_value, score_common, score_heroic, score_legendary, impro, $step FROM score" );

      self::DbQuery( "DELETE FROM global_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO global_saved SELECT global_id, global_value, $step FROM global WHERE global_id >= 10 AND global_id <= 80" );

      self::DbQuery( "DELETE FROM state_saved WHERE step=$step" );
      self::DbQuery( "INSERT INTO state_saved VALUES ('$state', $step)" );

      self::IncGameStateValue("step", 1);
      self::setGameStateValue("maxstep", $step );

      self::myNotifyAllPlayers( "nextChunk", '', array(
			'before' => $step,
			'chunk' => self::getGameStateValue("log_chunk")
			    ) );
    }

    /* May be dirty but I don't see a clean way of mixing callables as strings and as closures */
    function mycall_user_func_array ( $f, $a )
    {
      if (is_string($f))
	return call_user_func_array( array($this, $f), $a );
      else
	return call_user_func_array( $f, $a );
    } 

    /* Complementary functions for Deck */

    function flareNum( $player_id )
    {
      $num = self::getUniqueValueFromDB( "SELECT card_type_arg FROM card WHERE card_type='Flare' AND card_location='hand' AND card_location_arg='$player_id'" );
      if ( $num === null )
	return -1;
      else
	return $num;
    }

    function enrichTasks( $cards )
    {
      foreach ($cards as $card_id => $card)
	{
	  $card_num = $card['type_arg'];
	  $cards[$card_id]['name'] = $this->tasks[$card_num]['name'];
	  $cards[$card_id]['text'] = $this->tasks[$card_num]['text'];
	}
      return $cards;
    }

    function enrichCards( $cards )
    {
      foreach ($cards as $card_id => $card)
	{
	  $deck = $card['type'];
	  $card_num = $card['type_arg'];
	  if ( $deck == "Flare" )
	    {
	      $cards[$card_id]['upgraded'] =
		$this->flares[$card_num][0]['text'];
	      $cards[$card_id]['pieces'] =
		$this->flares[$card_num][1]['text'];
	    }
	  else
	    {
	      $cards[$card_id]['name'] = $this->card_contents[$deck][$card_num]['name'];
	      $cards[$card_id]['text'] = $this->card_contents[$deck][$card_num]['text'];
	      foreach ( array('frozentext', 'warptext') as $specialtext )
		if (isset($this->card_contents[$deck][$card_num][$specialtext]))
		  $cards[$card_id][$specialtext] =
		    $this->card_contents[$deck][$card_num][$specialtext];
	    }
	}
      return $cards;
    }

    function getEnrichedCardsInLocation( $thedeck, $location,
					 $location_arg=null, $order_by=null )
    {
      $cards = $thedeck->getCardsInLocation( $location, $location_arg,
					     $order_by);
      return self::enrichCards( $cards );
    }

    function getCardsOfTypeInLocation ( $type, $location, $location_arg=null, $order_by=null )
    {
      $sql = "SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_type='$type' AND card_location='$location'";
      if ( $location_arg !== null )
	$sql .= " AND card_location_arg=$location_arg";
      if ( $order_by !== null )
	$sql .= " ORDER BY $order_by";
      
      return self::getCollectionFromDB( $sql );
    }

    function getUniqueCardOfType ( $deck, $type, $type_arg )
    {
      $cards = $deck->getCardsOfType( $type, $type_arg );
      if ( count( $cards ) != 1 )
	throw new BgaVisibleSystemException ( "There should be exactly one $type $type_arg card" );
      else
	return array_pop($cards);
    }

    function pickCardReshuffle ( $deck, $location, $player_id )
    {
        $card = $deck->pickCard( $location, $player_id );
        if ($card === null)
            throw new BgaVisibleSystemException ("No cards left in ".$location);
            
        if ( $deck->countCardInLocation( $location ) == 0 )
        {
            $cards = self::getCardsOfTypeInLocation( substr( $location, 0, -4 ),
                                                     'discard' );
            $deck->moveCards( array_keys($cards), $location );
            $deck->shuffle( $location );
        }
        return $card;
    }

    /* General utility functions */

    function pieceCount( $players )
    {
      if ( self::getGameStateValue( 'game_form' ) == 1 )
	return 17;
      else
	return 18 - count($players) ;
    }

    function endScore( $players )
    {
      if ( self::getGameStateValue( 'game_form' ) == 1 )
	return 9;
      else
	switch( count($players) )
	  {
	  case 2:
	    return 18;
	    break;
	  case 3:
	    return 12;
	    break;
	  case 4:
	    return 10;
	    break;
	  }
    }

    function countPerformable( $clickable )
    {
      $performable = 0;
      $lastx = 0;
      $lasty = 0;

      foreach ( $clickable as $x => $clickcolumn )
	foreach ( $clickcolumn as $y => $clickxy )
	if ($clickxy)
	  {
	    $lastx = $x;
	    $lasty = $y;
	    $performable++;
	  }
      return array( $performable, $lastx, $lasty );
    }

    function sign( $i )
    {
      if ($i<0)
	return -1;
      elseif ($i>0)
	return 1;
      else
	return 0;
    }

    function getBoard()
    {
      return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player, board_rank rank FROM board", false );
    }

    // Account for the "round" corners
    function onBoard( $x, $y )
    {
      return ($x>0 && $x<10 && $y>0 && $y<10 &&
	      $x+$y>3 && $x+$y<17 && $x-$y<7 && $y-$x<7);
    }

    function foundPatternWrapper( $x, $y, $card, $board, $player ) {
      $voids = self::getGameStateValue( "void_summoning" );
      if ( !isset( $card['traveler'] ) )
	return self::foundPattern( $x, $y, $card, $board, $player, $voids );
      else {
	$players = self::loadPlayersBasicInfos();
	$used = array();
	$marked = array();
	foreach ($players as $id => $p)
	  {
	    $used[$id] = array();
	    $marked[$id] = array();
	  }
	if ( in_array( array($x, $y), self::emptyColorlessSquare() ) )
	  for ( $x=2 ; $x<=8 ; $x++)
	    for ( $y=2 ; $y<=8 ; $y++) {
	      $pat = self::foundPattern( $x, $y, $card, $board, $player, $voids );
	      foreach ($players as $id => $p)
		$used[$id] = array_merge( $used[$id], $pat['used'][$id] );
	    }

	return array('used'=>$used, 'marked'=>$marked);
      }
    }

    /* returns the set of pieces potentially used to summon, and if applicable, the set of marked squares for every valid orientation of the card.
Actually, returns such a set for every player id of a piece that may be used in the given pattern ($player if no enemy piece used) */
    function foundPattern( $x, $y, $card, $board, $player, $allowed_void )
    {
      $active_player = self::getActivePlayerId();
      $players = self::loadPlayersBasicInfos();
      $bonus = self::getGameStateValue( "bonus_improvisation" );
      
      /* coefficients for the 8 possible "rotations" */
      $a = array( 1, 0,-1, 0,-1,0, 1, 0 );
      $b = array( 0,-1, 0, 1, 0,1, 0,-1 );
      $c = array( 0, 1, 0,-1, 0,1, 0,-1 );
      $d = array( 1, 0,-1, 0, 1,0,-1, 0 );

      $used = array();
      $marked = array();
      foreach ($players as $id => $p)
	{
	  $used[$id] = array();
	  $marked[$id] = array();
	}
      $min_enemy_num = 3;
      for ( $i=0 ; $i<$card["rotations"] ; $i++ )
	{
	  $it_matches = true;
	  $void_pieces = 0;
	  $used_here = array();
	  //	  $enemy_id = 0;
	  $enemy_id = array();
	  $enemy_num = 0;
	  foreach ($card['pattern'] as $pattern_piece)
	    {
	      $dx = $a[$i] * $pattern_piece[0] + $b[$i] * $pattern_piece[1];
	      $dy = $c[$i] * $pattern_piece[0] + $d[$i] * $pattern_piece[1];
	      if (self::onBoard( $x+$dx, $y+$dy ))
		{
		  $used_here[] = array($x+$dx, $y+$dy);
		  $piece_on_board = $board[$x+$dx][$y+$dy];
		  if ( $piece_on_board['player'] === null
		       && $pattern_piece[2] == 0 )
		    $void_pieces++;
		  else if ( $piece_on_board['rank'] < $pattern_piece[2]
		       || $piece_on_board['player'] === null
			    && $pattern_piece[2] > 0
		       || $player != $active_player // Possessed summoning
		          && $piece_on_board['player'] == $active_player )
		    $it_matches = false;
		  else if ( $piece_on_board['player'] != $player )
		      {
			if ( ! in_array($piece_on_board['player'], $enemy_id))
			  $enemy_id[] = $piece_on_board['player'];
			$enemy_num++;
		      }
		}
	      else
		$it_matches = false;
	    }
	  $it_matches = $it_matches && ($void_pieces <= $allowed_void);

	  // No impro needed
	  if ( $it_matches && $enemy_num <= $bonus )
	    {
	      $min_enemy_num = min( $enemy_num, $min_enemy_num );
	      // $used[$player] = array_merge( $used[$player], $used_here );
	      // List, do not merge
	      $used[$player][] = $used_here;
	      /* Compute and merge marked squares */
	      if ( isset( $card['marked'] ))
		{
		  foreach ($card['marked'] as $marked_square)
		    {
		      $dx = $a[$i] * $marked_square[0] + $b[$i] * $marked_square[1];
		      $dy = $c[$i] * $marked_square[0] + $d[$i] * $marked_square[1];
		      if (self::onBoard( $x+$dx, $y+$dy ))
			{
			  $marked[$player][] = array($x+$dx, $y+$dy);
			}
		    }
		}
	    }

	  // Impro required
	  if ( $it_matches && $enemy_num == $bonus+1 )
	    {
	      $min_enemy_num = min( $enemy_num, $min_enemy_num );
	      foreach ($enemy_id as $eid)
		{ // Potentially two because of war summoning
		  // $used[$eid] = array_merge( $used[$eid], $used_here );
		  // List, do not merge
		  $used[$eid][] = $used_here;
		  /* Compute and merge marked squares */
		  if ( isset( $card['marked'] ))
		    {
		      foreach ($card['marked'] as $marked_square)
			{
			  $dx = $a[$i] * $marked_square[0] + $b[$i] * $marked_square[1];
			  $dy = $c[$i] * $marked_square[0] + $d[$i] * $marked_square[1];
			  if (self::onBoard( $x+$dx, $y+$dy ))
			    {
			      $marked[$eid][] = array($x+$dx, $y+$dy);
			    }
			}
		    }
		}
	    }

	}
      if ( $min_enemy_num == 1 && count( $used[$player] ) > 0
	   || $min_enemy_num == 2 )
	{
	  if ( $players[$player]['player_color'] == $this->schools_colors[2] )
	    self::myNotifyAllPlayers( "warSummon", clienttranslate( '${player_name} did a war summoning' ), array(
			'player_id' => $player,
			'player_name' => self::getActivePlayerName()
			    ) );
	  else
	    self::myNotifyAllPlayers( "warSummon", clienttranslate( '${player_name} did a frostweave summoning' ), array(
			'player_id' => $player,
			'player_name' => self::getActivePlayerName()
			    ) );
	}
      return array('used'=>$used, 'marked'=>$marked);
    }

    function updateDestroyed( $board, $x, $y, $player_id, $titan=false )
    { // Called by various effects (shoot, destroy, move, convert...)
	  $rank = $board[$x][$y]['rank'];
	  $id = $board[$x][$y]['player'];
	  if ( (self::getGameStateValue( "gateway_x" ) == $x) && (self::getGameStateValue( "gateway_y" ) == $y)) {
		  	//* SCC TODO improve log?  Rewrite/reposition?
			self::MyNotifyAllPlayers( "gatewayChanged", clienttranslate( 'The Gateway at (${from_x},${from_y}) was destroyed' ), array(
			        'player_id' => $player_id,
				'from_x' => $x,
				'from_y' => $y,
				'x' => -1,
				'y' => -1 ) );
		  self::setGameStateValue( "gateway_x", -1 );
		  self::setGameStateValue( "gateway_y", -1 );
	  }
      if ( $id != $player_id )
	{
	  $name = self::rankName( $rank );
	  self::IncGameStateValue( "being_destroyed_".$name, 1 );

	  if (!$titan)
	    {
	      $counted = 1+self::getGameStateValue( "additional_destroyed" );
	      self::IncGameStateValue( "turn_destroyed_".$name, $counted );
	      self::DbQuery("UPDATE score SET score_".$name."=score_".$name."+".$counted." WHERE score_player_id=".$player_id." AND score_against=".$id);
	    }
	}

      if ( $rank == 2 && self::getGameStateValue( "game_form" ) == 1 )
	{
	  self::DbQuery( "UPDATE player SET player_score=player_score-1 WHERE player_id=$id" );      
	  self::myNotifyAllPlayers( "updateScore", "", array("player_id" => $id,
							     "diff" => -1) );
	}
    }

    /* The actual effects */

    function effectType( $effect ) //, $effectTarget )
    { // Determines the transition to use in the state machine
      switch ( $effect )
	{
	case 'destroyPiece':
	case 'upgradePiece':
	case 'downgradePiece':
	case 'moveBeing':
	case 'convertPiece':
	case 'choosePiece':
	case 'becomeGateway':
	case 'capturePiece':
	  return 'effectPiece';
	case 'placePiece':
	case 'chooseSquare':
	case 'freePiece':
	  return 'effectSquare';
	case 'movePiece':
	  return 'effectMovePiece';
	case 'shootPieces':
	case 'chooseDirectionMirror':
	  return 'effectDirection';
	case 'putCardOnTop':
	case 'putFrozenInPlay':
	case 'performWarp':
	case 'discardSingleCard':
	  return 'effectCard';
	case 'orEffects2':
	case 'orEffects3':
	  return $effect;
	case 'chooseOption':
	  return 'effectChoose';
	case 'considerLegendSummoned':
	  //	  return 'chooseColorLegend';
	case 'nothing':
	case 'gainAction':
	case 'loseAction':
	case 'loseOwnAction':
	case 'discardFlare':
	case 'discardLegends':
	case 'discardPending':
	case 'gainTurn':
	case 'gainBonusImprovisation':
	case 'voidSummoning':
	case 'drawExtra':
	case 'markSquares':
	case 'doNotCountDestroyed':
	case 'moonShine':
	case 'destroyPieceTitan':
	case 'cleanUpMirror':
	case 'cleanUpGateOfOblivion':
	case 'countTwice':
	case 'removeFromGame':
	case 'useEnemyColor':
	case 'putCardOnBottom':
	case 'putTopCardOnTop':
	default:
	  return 'autoEffect';
	}
    }

    function doDestroyPiece( $board, $x, $y, $player_id, $titan=false )
    { // Called by various effects (shoot, destroy...)
      $sql = "UPDATE board SET board_player=NULL WHERE board_x=$x AND board_y=$y";
      self::DbQuery($sql);

      self::IncGameStateValue( "being_destroyed", 1 );

      self::updateDestroyed( $board, $x, $y, $player_id, $titan );
      
      self::myNotifyAllPlayers( "pieceDestroyed", clienttranslate( '${player_name} destroyed a ${therank} piece' ), array(
		'i18n' => array( 'therank' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'therank' => self::rankName( $board[$x][$y]['rank'] ),
		'game_form' => self::getGameStateValue( 'game_form' ),
		'rank_destroyed' => $board[$x][$y]['rank'],
		'color_destroyed' => $board[$x][$y]['player'],
		'additional_destroyed' => self::getGameStateValue( "additional_destroyed" ),
		'x' => $x,
		'y' => $y,
		'titan' => $titan ) );

      self::setGameStateValue("piece_rank",
			      $board[$x][$y]['rank']);
      self::setGameStateValue("piece_before_player",
			self::getGameStateValue("piece_player"));
      self::setGameStateValue("piece_player", $board[$x][$y]['player']);
    }

    function destroyPiece( $board, $x, $y, $player_id, $dummy, $titan=false )
    {
      if ( $board[$x][$y]['player'] !== null ) /* Hack for Assassin */
	{
	  self::doDestroyPiece( $board, $x, $y, $player_id, $titan );
	}

      /* Needs to be memorized for Assassin's hack */
      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_x", $x);
      self::setGameStateValue("piece_y", $y);
    }

    function destroyPieceTitan( $board, $x, $y, $player_id )
    {
      self::destroyPiece( $board, $x, $y, $player_id, null, true );
    }

    function choosePiece( $board, $x, $y )
    {
      /* No effect, just mark a piece */
      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_x", $x);
      self::setGameStateValue("piece_y", $y);
    }

	function chooseSquare( $board, $x, $y )
	{
		/* No effect, just mark a square */
		self::setGameStateValue("square_x", $x);
		self::setGameStateValue("square_y", $y);
	}

    function upgradePiece( $board, $x, $y, $player_id )
    {
      /* Phony upgrade, for Death Angel */
      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_x", $x);
      self::setGameStateValue("piece_y", $y);
      if ( $board[$x][$y]['rank'] < 2 )
	{
	  $rank = $board[$x][$y]['rank'] + 1;
	  $id = $board[$x][$y]['player'];
	  $sql = "UPDATE board SET board_rank=$rank WHERE board_x=$x AND board_y=$y";
	  self::DbQuery($sql);
	  self::IncGameStateValue( "being_upgraded_piece", 1 );
	  self::IncGameStateValue( 'turn_upgraded', 1 );
	
	  $stop_gateway = "";
	  if ((self::getGameStateValue( "gateway_x" ) == $x) &&
			(self::getGameStateValue( "gateway_y" ) == $y) &&
			($rank == 2))
	  { // Gateway just upgraded to Legendary; no longer Gateway
		$stop_gateway = clienttranslate(" (stopping it from being the Gateway)");
		self::setGameStateValue( "gateway_x", -1 );
		self::setGameStateValue( "gateway_y", -1 );
	        self::MyNotifyAllPlayers( "gatewayChanged", '', array(
			        'player_id' => $player_id,
				'x' => -1,
				'y' => -1 ) );
	  }

	  self::myNotifyAllPlayers( "pieceUpgraded", clienttranslate( '${player_name} upgraded a piece${stop_gateway}' ), array(
            'i18n' => array( "stop_gateway" ),
	    'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
	    'x' => $x,
	    'y' => $y,
	    'rank' => $rank,
	    'game_form' => self::getGameStateValue( 'game_form' ),
		'id' => $id,
		'stop_gateway' => $stop_gateway ) );
	  self::setGameStateValue("piece_before_player",
				  self::getGameStateValue("piece_player"));
	  self::setGameStateValue("piece_rank", $board[$x][$y]['rank']);
	  self::setGameStateValue("piece_player", $id );

	  if ( $rank == 2 && self::getGameStateValue( "game_form" ) == 1 )
	    self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$id" );
	}
      /* Phony upgrade must be possible, for Death Angel */
      //      else
      //	throw new BgaVisibleSystemException ( "You can't upgrade a legendary piece" );
    }

    function downgradePiece( $board, $x, $y, $player_id )
    {
      if ( $board[$x][$y]['rank'] > 0 )
	{
	  $rank = $board[$x][$y]['rank'] - 1;
	  $id = $board[$x][$y]['player'];
	  $sql = "UPDATE board SET board_rank=$rank WHERE board_x=$x AND board_y=$y";
	  self::DbQuery($sql);
	  self::setGameStateValue("piece_before_x",
				  self::getGameStateValue("piece_x"));
	  self::setGameStateValue("piece_before_y",
				  self::getGameStateValue("piece_y"));
	  self::setGameStateValue("piece_before_player",
				  self::getGameStateValue("piece_player"));
	  self::setGameStateValue("piece_x", $x);
	  self::setGameStateValue("piece_y", $y);
	  self::setGameStateValue("piece_rank", $board[$x][$y]['rank']);
	  self::setGameStateValue("piece_player", $id);
	  self::IncGameStateValue( "being_downgraded_piece", 1 );
	
	  self::myNotifyAllPlayers( "pieceDowngraded", clienttranslate( '${player_name} downgraded a piece' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
	    'x' => $x,
	    'y' => $y,
	    'rank' => $rank,
	    'game_form' => self::getGameStateValue( 'game_form' ),
	    'id' => $id ) );

	  if ( $rank == 1  && self::getGameStateValue( "game_form" ) == 1 )
	    self::DbQuery( "UPDATE player SET player_score=player_score-1 WHERE player_id=$id" );
	}
      else
	throw new BgaVisibleSystemException ( "You can't downgrade a common piece" );
	}

    function capturePiece( $board, $x, $y, $player_id ) {
      $id = $board[$x][$y]['player'];
      $rank = $board[$x][$y]['rank'];
      $sql = "UPDATE board SET board_player=NULL WHERE board_x=$x AND board_y=$y";
      self::DbQuery($sql);
      self::setGameStateValue( "merchant_rank", $rank );
      self::setGameStateValue( "merchant_player", $id );
      $players = self::loadPlayersBasicInfos();
      if ( $id == $player_id )
	$sentence = clienttranslate( '${player_name} put their own ${rank_name} piece in stasis' );
      else
	$sentence = clienttranslate( '${player_name} put ${captured_name}\'s ${rank_name} piece in stasis' );
      if ( self::getGameStateValue( "gateway_x" ) == $x 
	   && self::getGameStateValue( "gateway_y" ) == $y) {
	$gateway = true;
	self::setGameStateValue( "gateway_x", 0 );
	self::setGameStateValue( "gateway_y", 0 );
      }
      else
	$gateway = false;
      self::myNotifyAllPlayers( "pieceCaptured", $sentence, array(
				 'player_id' => $player_id,
				 'player_name' => self::getActivePlayerName(),
				 "i18n" => array( "rank_name" ),
				 "rank_name" => self::rankName($rank),
				 "captured_name" => $players[$id]['player_name'],
				 'gateway' => $gateway,
				 'x' => $x,
				 'y' => $y ) );
      if ( $rank == 2 && self::getGameStateValue( "game_form" ) == 1 )
	{
	  self::DbQuery( "UPDATE player SET player_score=player_score-1 WHERE player_id=$id" );      
	  self::myNotifyAllPlayers( "updateScore", "", array("player_id" => $id,
							     "diff" => -1) );
	}
    }
	
    function freePiece( $board, $x, $y, $player_id ) {
      self::IncGameStateValue( 'turn_moved', 1 );
      $rank_freed = self::getGameStateValue( "merchant_rank" );
      $player_freed = self::getGameStateValue( "merchant_player" );
      if ( $player_freed == 0 )
	throw new BgaVisibleSystemException ( "You have no piece to free" );
      $and_destroyed = "";
      $destroyed_args = array();
	  
      if ( $board[$x][$y]['player'] !== null )
	{
	  self::updateDestroyed( $board, $x, $y, $player_id );
	  $and_destroyed = clienttranslate(' and destroyed a ${rank_string} piece');
	  $destroyed_args = array(
				  "i18n" => array( "rank_string" ),
				  "rank_string" =>
				  self::rankName($board[$x][$y]['rank']) );
	}
      self::setGameStateValue( "merchant_player", 0 );
	  
      $sql = "UPDATE board SET board_player=$player_freed, board_rank=$rank_freed WHERE board_x=$x AND board_y=$y";
      self::DbQuery($sql);

      $players = self::loadPlayersBasicInfos();
      if ( $player_freed == $player_id )
	$sentence = clienttranslate( '${player_name} released their ${rank_name} piece from stasis${and_destroyed}' );
      else
	$sentence = clienttranslate( '${player_name} released ${captured_name}\'s ${rank_name} piece from stasis${and_destroyed}' );
      if ( self::getGameStateValue( "gateway_x" ) == 0 
	   && self::getGameStateValue( "gateway_y" ) == 0) {
	$gateway = true;
	self::setGameStateValue( "gateway_x", $x );
	self::setGameStateValue( "gateway_y", $y );
      }
      else
	$gateway = false;
	  
      self::myNotifyAllPlayers( "pieceFreed", $sentence, array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'and_destroyed' => array(
						'log' => $and_destroyed,
						'args' => $destroyed_args ),
			'game_form' => self::getGameStateValue( 'game_form' ),
			'rank_destroyed' => $board[$x][$y]['rank'],
			'color_destroyed' => $board[$x][$y]['player'],
			'additional_destroyed' => self::getGameStateValue( "additional_destroyed" ),
			'rank_freed' => $rank_freed,
			'player_freed' => $player_freed,
			'gateway' => $gateway,
			"i18n" => array( "rank_name" ),
			"rank_name" => self::rankName($rank_freed),
			"captured_name" => $players[$player_freed]['player_name'],
			'x' => $x,
			'y' => $y ) );
      if ( $rank_freed == 2 && self::getGameStateValue( "game_form" ) == 1 )
	{
	  self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$player_freed" );      
	  self::myNotifyAllPlayers( "updateScore", "", array("player_id" => $player_freed,
							     "diff" => 1) );
	}
    }
	
    function becomeGateway( $board, $x, $y, $player_id )
    {
		if ( $board[$x][$y]['player'] != $player_id) {
			throw new BgaVisibleSystemException ( "Only Nethervoid pieces can become the Gateway" );
		}
		else if ( $board[$x][$y]['rank'] == 2 )	{
			throw new BgaVisibleSystemException ( "Legendary pieces can't become the Gateway" );
		} else if ( self::getGameStateValue( "gateway_x" ) != $x
			    || self::getGameStateValue( "gateway_y" ) != $y ) {
			//* SCC TODO improve log?
			self::MyNotifyAllPlayers( "gatewayChanged", clienttranslate( 'The piece at (${x},${y}) became the Gateway'), array(
			        'player_id' => $player_id,
				'x' => $x,
				'y' => $y ) );
			self::setGameStateValue("gateway_x", $x);
			self::setGameStateValue("gateway_y", $y);
		}
    }

	function considerLegendSummoned()
	{
	  $player_id = self::getActivePlayerId();
	  self::IncGameStateValue( "turn_summoned_legends", 1 );
	  if ( self::getPlayersNumber() == 2
	       && self::getGameStateValue( 'game_form' ) == 2 )
	    {
	      self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$player_id" );
	      self::myNotifyAllPlayers( "updateScore", "",
					array("player_id" => $player_id,
					      "diff" => 1) );
	    }
	  self::myNotifyAllPlayers( "legendSummoned", clienttranslate( '${player_name}\'s last upgrade counts as summoning a legend' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName()
			    ) );
	}

	function countAdditionalDestroyed()
	{
	  $player_id = self::getActivePlayerId();
	  // May warp the Gate of Oblivion twice or thrice on the same turn
	  self::IncGameStateValue( "additional_destroyed", 1);
	  self::myNotifyAllPlayers( "pieceRemoved", clienttranslate( 'For the rest of this turn, every piece destroyed by ${player_name} will count as ${n} of them' ), array(
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'n' => 1 + self::getGameStateValue( "additional_destroyed" )
							) );
	}

	function useLastEnemyColor( $player_id )
	{
	  $enemy_id = self::getGameStateValue("piece_player");
	  self::setGameStateValue( "summoning_color", $enemy_id );
	  $players = self::loadPlayersBasicInfos();
	  self::myNotifyAllPlayers( "possessedSummoning", clienttranslate( '${player_name} will use ${enemy_name}\'s pieces for their next summoning this turn' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'enemy_name' => $players[$enemy_id]['player_name']
			) );
	}

	function performWarp( $card_id )
	{
	  $player_id = self::getActivePlayerId();
	  $thecard = self::getUniqueCardOfType( $this->cards, "Etherweave",
						$card_id );
	  $card_played = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");
	  $card = $this->card_contents['Etherweave'][$card_played];

	  if ( $card['effecttargets'][$effect_number/2] == 'discardedWarp'
	      && $thecard['location'] != 'discard'
	       && $thecard['location'] != 'discard_buffer' )
	    throw new BgaVisibleSystemException ( "You don't have that card in your discard" );
	  if ( !isset($this->etherweave_contents[$card_id]['warptext']) )
	    throw new BgaVisibleSystemException ( "That being has no warp effect" );
	  if ( $card_id == 5 ) // Merchant of Time
	    throw new BgaVisibleSystemException ( "That being cannot be copied" );

      self::setGameStateValue( 'to_be_discarded', $card_played );

	  // Eternal Emperor subtleties
	  if ($card_id == 12)
	    self::IncGameStateValue( "eternal_emperor_warped", -1 );
	  
	  self::setGameStateValue( "card_played", $card_id+20 );
	  self::setGameStateValue( "deck_played", 6 ); // Etherweave
	  self::setGameStateValue( "effect_number", -2 );
	  $hoffset = 125 * $card_id / 2 + 2;
	  $icon = '<div id="last_warp_'.$card_id.'" class="last_card_icon log_inlined Etherweave" style="background-position:-'.$hoffset.'px -12px"></div>';

	  self::myNotifyAllPlayers( "warpPlayed", clienttranslate( '${icon}${player_name} copied ${being}\'s warp effect' ), array(
		'i18n' => array( 'being' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'being' => $this->etherweave_contents[$card_id]['name'],
		'text' => $this->etherweave_contents[$card_id]['text'],
		'card_id' => $card_id,
		'icon' => $icon,
		'copy' => true,
		'warptext' => 
		    $this->etherweave_contents[$card_id]['warptext'] ) );
	}

	function pendingBeing( ) {
	  $pending = self::getGameStateValue( "pending_being" );
	  if ( $pending == 5 )
          self::myNotifyAllPlayers( "merchantImmunity", clienttranslate('Doppelganger could not copy Merchant of Time'), array() );
	  if ( $pending < 0 || $pending == 5 ) // Merchant of Time
	    return array();
	  else
	    return array( self::getUniqueCardOfType( $this->cards,
					"Etherweave", $pending ) );
	}

	function discardedWarp( ) {
	  $cards = array_merge(
		self::getCardsOfTypeInLocation( "Etherweave", "discard" ),
		self::getCardsOfTypeInLocation( "Etherweave", "discard_buffer" ) );
	  foreach ( $cards as $card_id => $card )
	    {
	      if ( ! isset(
		$this->etherweave_contents[$card['type_arg']]['warptext'] )
		   || $card['type_arg'] == 5 ) // Merchant of Time
		unset( $cards[$card_id] );
	    }
	  return $cards;
	}

	function discardLegends()
	{
	  $player_id = self::getActivePlayerId();
	  $cards = $this->cards->getCardsOfTypeInLocation( 'Legends', null,
							   'hand',
						   self::getActivePlayerId() );
	  foreach ( $cards as $card ) {
	    $this->cards->insertCardOnExtremePosition( $card['id'], "discard_buffer", true );
	    
	    self::myNotifyAllPlayers( "flareDiscarded", clienttranslate( '${player_name} discarded a legend' ), array(
															   'player_id' => $player_id,
															   'player_name' => self::getActivePlayerName()
															   ) );
	    self::myNotifyPlayer( $player_id, "discardCard", "", array(
									     "card_id" => "7_".$card['type_arg'] ) );
	  }
	}

	function discardPending( $player_id )
	{
	  $pending_index = self::getGameStateValue("pending_being");
	  if ($pending_index != -1 && $pending_index != 5) { // MerchantOfTime
		$discarded = self::getUniqueCardOfType( $this->cards, 'Etherweave', self::getGameStateValue("pending_being"));
		$this->cards->insertCardOnExtremePosition( $discarded['id'], "discard_buffer", true );
		self::setGameStateValue( "pending_being", -1 );
		self::myNotifyAllPlayers( "pendingMoved", clienttranslate( '${player_name} discarded their pending being' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName()
		) );
        $discarded = self::enrichCards( array( $discarded['id'] => $discarded ) );
        $discarded = array_pop($discarded);
	 	self::myNotifyPlayer( $player_id, "discardCard", "", array(
		    "card_id" => "6_".$pending_index,
		    "to" => "discard",
            "card" => $discarded ) );
	  } else {
		  throw new BgaVisibleSystemException ( "You don't have a pending being to discard" );
	  }

		// $player_id = self::getActivePlayerId();
		// $deck_id = self::getGameStateValue( "deck_played" );

		// $type = $this->decks[$deck_id];
		// $discarded = self::getUniqueCardOfType( $this->cards, $type, self::getGameStateValue( "pending_being" ) );
		// $this->cards->moveCard( $discarded['id'], "discard_buffer" );
		// self::myNotifyAllPlayers( "cardDiscarded", clienttranslate( '${player_name} discarded their pending being' ), array(
		// 	  'player_name' => self::getActivePlayerName()) );
		// self::setGameStateValue( "pending_being", -1 );
		// $this->gamestate->nextState( 'discardCard' );
	}

	function returnPending( ) {
	  self::checkAction( 'effectPlayed' );
	  $player_id = self::getActivePlayerId();
	  $deck = $this->decks[self::getGameStateValue("deck_played")];
	  $card_id = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");
	  $effect = $this->card_contents[$deck][$card_id]['effects'][$effect_number];
	  $pending = self::getGameStateValue( 'pending_being' );

	  if ( $pending>=0 && $pending!=5 &&
	       substr( $effect, 0, 9) == 'orEffects' )
	    {
	      $neffects = intval( substr( $effect, 9, 1) );
	      self::IncGameStateValue( "effect_number", 2 * ($neffects-1) );
	      
	      for ( $i = 1 ; $i <= $neffects ; $i++ )
		{
		  if ( $this->card_contents[$deck][$card_id]['effects'][$effect_number+$i] == 'returnPending')
		    {
		      self::setGameStateValue( "pending_being", -1 );
		      self::myNotifyAllPlayers( "pendingMoved", clienttranslate( '${player_name} returned their pending being to their hand' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName()
			) );
		      $this->gamestate->nextState( 'effectPlayed' );
		      return;
		    }
		}
	    }
	  throw new BgaVisibleSystemException ( "You can't return a pending being now" );
	}

	function eternalEmperorWarped()
	{
	  // Subtleties about copying its warp effect is not a warp effect
	  self::IncGameStateValue( "eternal_emperor_warped", 1 );
	}

    function discardSingleCard( $discarded_deck_id, $discarded_id )
    { 
      self::checkAction( 'effectPlayed' );
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      if ( $deck == -1 )
	throw new BgaVisibleSystemException ( "You can't discard now" );
      $deck = $this->decks[$deck];
      $card = $this->card_contents[$deck][$card_id];
      $effect = $card['effects'][$effect_number];
      if ( $effect != 'discardSingleCard' )
	throw new BgaVisibleSystemException ( "You can't discard now" );
      $player_id = self::getActivePlayerId();
      $type = $this->decks[$discarded_deck_id];
      $discarded = self::getUniqueCardOfType( $this->cards, $type, $discarded_id );
      if ( $discarded['location'] != 'hand'
	   || $discarded['location_arg'] != $player_id )
	throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
      if ( $discarded_deck_id == 6
	   && $discarded_id == self::getGameStateValue( "pending_being" ) )
	throw new BgaVisibleSystemException ( "You cannot discard your pending being" );
      $this->cards->insertCardOnExtremePosition( $discarded['id'], 
						 "discard_buffer", true );
      self::myNotifyAllPlayers( "flareDiscarded", clienttranslate( '${player_name} discarded a ${deck} card' ), array(
				'i18n' => array( 'deck' ),
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'deck' => $type
						) );
      if ( $discarded_deck_id == -1 )
          $discarded_deck_id = 'flare';
      $discarded = self::enrichCards( array( $discarded['id'] => $discarded ) );
      $discarded = array_pop($discarded);
      self::myNotifyPlayer( $player_id, "discardCard", "",
			    array( "card_id" =>
				   $discarded_deck_id."_".$discarded_id,
                       "to" => "discard",
                "card" => $discarded ) );
      $this->gamestate->nextState( 'effectPlayed' );
    }

	function doMovePiece( $board, $from_x, $from_y, $to_x, $to_y, $distance, $card_x, $card_y, $player_id, $type )
    {
      switch ( $distance )
	{
	case 'charge':
	  $dx = self::sign($to_x-$from_x);
	  $dy = self::sign($to_y-$from_y);
	  break;
	case 'leap':
	case 'move':	
	default:
	  $dx = $to_x-$from_x;
	  $dy = $to_y-$from_y;
	  break;
	}
      
      if ($to_x == $card_x && $to_y == $card_y)
	{ // being destroyed
	  self::setGameStateValue("card_x", 0);
	  self::setGameStateValue("card_y", 0);
	}
      if ($from_x == $card_x && $from_y == $card_y)
	{ // being moves
	  self::setGameStateValue("card_x", $to_x);
	  self::setGameStateValue("card_y", $to_y);
	  /* Charging beings do not care about having moved or destroyed */
	  self::IncGameStateValue( "being_moved", 1 );
	  self::IncGameStateValue( 'turn_moved', 1 );
	  if ( $board[$to_x][$to_y]['player'] !== null && $type != "swap" )
	    self::IncGameStateValue( "being_destroyed", 1 );
	}

      $rank_from = $board[$from_x][$from_y]['rank'];
      $player_from = $board[$from_x][$from_y]['player'];

      // This loop executes once except for charges
      for ( ; $from_x != $to_x || $from_y != $to_y ;
	    $from_x += $dx, $from_y += $dy )
	{
	  $next_x = $from_x+$dx;
	  $next_y = $from_y+$dy;
	  $and_destroyed = "";
	  $destroyed_args = array();
	  
	  if ( $board[$next_x][$next_y]['player'] !== null && $type != "swap" )
	    {
	      self::updateDestroyed( $board, $next_x, $next_y, $player_id );
	      $and_destroyed = clienttranslate(' and destroyed a ${rank_string} piece');
	      $destroyed_args = array(
		      "i18n" => array( "rank_string" ),
			"rank_string" => self::rankName($board[$next_x][$next_y]['rank']) );

	      if ($board[$next_x][$next_y]['rank'] == $rank_from)
		self::IncGameStateValue( "combat_moves", 1 );
	    }
	  
	  if ( $type == "swap" ) {
	    $sql = "UPDATE board SET board_player=".$board[$next_x][$next_y]['player'].", board_rank=".$board[$next_x][$next_y]['rank']." WHERE board_x=$from_x AND board_y=$from_y";
	    $and_destroyed = clienttranslate(' and swapped it with another piece');
	  }
	  else
	    $sql = "UPDATE board SET board_player=NULL WHERE board_x=$from_x AND board_y=$from_y";
	  self::DbQuery($sql);
	  
	  $sql = "UPDATE board SET board_player=$player_from, board_rank=$rank_from WHERE board_x=$next_x AND board_y=$next_y";
	  self::DbQuery($sql);
	  
	  // Update the gateway after checking the destroyed piece to avoid thinking we've destroyed the gateway
	  $gateway_moves = '';
  	  if ($from_x == self::getGameStateValue( 'gateway_x' ) &&
	      $from_y == self::getGameStateValue( 'gateway_y' ))
	   { // Gateway moves
	  	self::setGameStateValue( 'gateway_x', $next_x );
		self::setGameStateValue( 'gateway_y', $next_y );
		$gateway_moves = 'to';
		// self::MyNotifyAllPlayers( "gatewayChanged", '', array(
		// 	        'player_id' => $player_id,
		// 		'x' => $next_x,
		// 		'y' => $next_y ) );
	   }
  	  else if ( $type == 'swap'
		    && $next_x == self::getGameStateValue( 'gateway_x' )
		    && $next_y == self::getGameStateValue( 'gateway_y' ) )
	   { // Gateway moves
	  	self::setGameStateValue( 'gateway_x', $from_x );
		self::setGameStateValue( 'gateway_y', $from_y );
		$gateway_moves = 'from';
		// self::MyNotifyAllPlayers( "gatewayChanged", '', array(
		// 	        'player_id' => $player_id,
		// 		'x' => $from_x,
		// 		'y' => $from_y ) );
	   }

	  self::myNotifyAllPlayers( "pieceMoved", clienttranslate( '${player_name} moved a piece${and_destroyed}' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'and_destroyed' => array(
						'log' => $and_destroyed,
						'args' => $destroyed_args ),
			'game_form' => self::getGameStateValue( 'game_form' ),
			'rank_destroyed' => $board[$next_x][$next_y]['rank'],
			'color_destroyed' => $board[$next_x][$next_y]['player'],
			'additional_destroyed' => self::getGameStateValue( "additional_destroyed" ),
			'swap' => ($type=="swap"),
			'gateway_moves' => $gateway_moves,
			'from_x' => $from_x,
			'from_y' => $from_y,	    
			'x' => $next_x,
			'y' => $next_y ) );

	}

      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_before_player",
			      self::getGameStateValue("piece_player"));
      self::setGameStateValue("piece_x", $to_x);
      self::setGameStateValue("piece_y", $to_y);
      self::setGameStateValue("piece_rank", $rank_from);
      self::setGameStateValue("piece_player", $player_from);

      self::setGameStateValue("last_dx", $dx);
      self::setGameStateValue("last_dy", $dy);
    }

    function moveBeing( $board, $x, $y, $player_id, $targets )
    {
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");

      self::doMovePiece( $board, $card_x, $card_y, $x, $y,
			 $targets[1], $card_x, $card_y,
			 $player_id, $targets[0] );
    }

    function nothing( $played_id )
    {
    }

    function gainAction( $player_id )
    {
      self::IncGameStateValue( "remaining_actions", 1 );
      self::myNotifyAllPlayers( "actionGained", clienttranslate( '${player_name} gained an action' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ) );
    }

    function loseAction( $player_id )
    {
      self::setGameStateValue( "action_malus", $player_id );
      self::myNotifyAllPlayers( "actionLost", clienttranslate( 'All players but ${player_name} have 1 less action on their next turn' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ) );
	}
	
	function loseOwnAction( $player_id )
	{
		$remaining_actions = self::getGameStateValue( "remaining_actions" );
		if ($remaining_actions > 1) {
			self::IncGameStateValue( "remaining_actions", -1 );
			// Text for Demon of Sloth
			self::myNotifyAllPlayers( "actionLost", clienttranslate( '${player_name} lazed away an action' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName()
			) );
		}
		else {
			self::IncGameStateValue( "has_skipped", 1 );
			self::myNotifyAllPlayers( "effectSkipped", clienttranslate( '${player_name} couldn\'t spend an action to do nothing' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName() 
			) );
		}
	}

    function discardFlare( $player_id )
    {
      $flare = self::flareNum( $player_id );
      if ( $flare >= 0 )
	{
	  $discarded = self::getUniqueCardOfType( $this->cards, 'Flare', $flare );
	  if ( $discarded['location'] != 'hand'
	       || $discarded['location_arg'] != $player_id )
	    throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
	  else
	    {
	      // TODO pas fini
	      $this->cards->insertCardOnExtremePosition( $discarded['id'], "discard_buffer", true );
	      self::setGameStateValue( "flare_discarded", 1 );

	      self::myNotifyAllPlayers( "flareDiscarded", clienttranslate( '${player_name} discarded a flare' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName()
								       ) );
	      self::myNotifyPlayer( $player_id, "discardCard", "", array(
				"card_id" => "flare_".$flare ) );
	    }
	}
      else
	throw new BgaVisibleSystemException ( "You don't have a flare to discard" );
    }

    function putCardOnBottom( $player_id, $card_id )
    { // Hellhound
      $card = self::getUniqueCardOfType( $this->cards, "Nethervoid", $card_id );
      $this->cards->InsertCardOnExtremePosition( $card['id'],
						 "return_buffer", true );
      //      $this->cards->InsertCardOnExtremePosition( $card['id'], "NethervoidDeck", true );

      self::myNotifyAllPlayers( "putCardOnBottom", clienttranslate( '${player_name} returned Hell Hound to the bottom of their deck' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName()
								       ) );
    }

    function putTopCardOnTop( $player_id ) {
      $cards = self::getCardsOfTypeInLocation ( 'Etherweave', 'discard_buffer',
						null, 'location_arg' );
      if ( count($cards) == 0 )
	$cards = self::getCardsOfTypeInLocation ( 'Etherweave', 'discard',
						  null, 'location_arg' );
      if ( count($cards) > 0 ) {
	$card = array_pop($cards);
	$this->cards->InsertCardOnExtremePosition( $card['id'],
						   'EtherweaveDeck', true );
	self::myNotifyAllPlayers( "putTopCardOnTop", clienttranslate( '${player_name} put the top card of their discard pile on top of their deck' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
                'card_id' => $card['type_arg']
								       ) );
      }
      else
	self::myNotifyAllPlayers( "effectSkipped", clienttranslate( '${player_name} has no card in their discard' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName() ) );
    }

    function gainTurn( $player_id )
    {
      self::setGameStateValue( "extra_turn", 1 );
    }

    function gainBonusImprovisation( $player_id )
    {
      self::setGameStateValue( "bonus_improvisation", 1 );
    }

    function voidSummoning( $player_id )
    {
      self::myNotifyAllPlayers( "warSummon", clienttranslate( 'For ${player_name}\'s next summoning, up to two empty squares can be counted as common pieces' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName()
			    ) );
      self::setGameStateValue( "void_summoning", 2 );
    }

    function moonShine( )
    { // Memorize where Werewolf was summoned
      $x = self::getGameStateValue( "card_x" );
      $y = self::getGameStateValue( "card_y" );
      self::setGameStateValue( "fullmoon",
	intval( self::greenSquare($x, $y)
		|| self::redSquare($x, $y) && self::nonCentral($x, $y) ) );
    }

    function cleanUpMirror( )
    { // Just unmark "heroic places"
      self::DbQuery( "UPDATE board SET board_marked=0 WHERE board_marked=2" );
    }

    /*** OBSOLETE
    function cleanUpMirror( )
    { // Just unmark "heroic places"
      $card_x = self::getGameStateValue( "card_x" );
      $card_y = self::getGameStateValue( "card_y" );
      $dx = self::getGameStateValue( "last_dx" );
      $dy = self::getGameStateValue( "last_dy" );
      $board = self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_rank rank, board_marked marked FROM board", false );
      $unmark = array();

      if ( $dx == 0 )
	{
	  for ( $i=$x-1 ; $i<=$x+1 ; $i++ )
	    for ( $k=1 ; $k<=2 ; $k++ )
	      {
		$j = $card_y + $k * $dy;
		if ( self::onBoard($i, $j) && $board[$i][$jj]['marked'] == 1 )
		  {
		    $jj = 2*$card_y - $j;
		    if ( $board[$i][$jj]['rank'] == 1 )
		      $unmark[] = array( $i, $j );
		  }
	      }
	}
      else
	{
	  for ( $j=$y-1 ; $j<=$y+1 ; $j++ )
	    for ( $k=1 ; $k<=2 ; $k++ )
	      {
		$i = $card_x + $k * $dx;
		if ( self::onBoard($i, $j) && $board[$i][$jj]['marked'] == 1 )
		  {
		    $ii = 2*$card_x - $i;
		    if ( $board[$ii][$j]['rank'] == 1 )
		      $unmark[] = array( $i, $j );
		  }
	      }
	}

      if ( count($unmark)>0 )
	{
	  $sql = "UPDATE board SET board_marked=0 WHERE ";
	  foreach ( $unmark as $um )
	    {
	      $sql .= "(board_x=".$um[0]." AND board_y=".$um[1].") OR ";
	    }
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	} 
    }
    ***/

    function cleanUpGateOfOblivion( )
    { 
      $card_x = self::getGameStateValue( "card_x" );
      $card_y = self::getGameStateValue( "card_y" );
      $piece_x = self::getGameStateValue( "piece_x" );
      $piece_y = self::getGameStateValue( "piece_y" );
      $purplemarked = array();
      if ( $piece_x != 0 )
	  $purplemarked[] = array($piece_x, $piece_y);
      else {
	  // Gather empty purple squares
	  foreach ( self::getObjectListFromDB( "SELECT board_x x, board_y y
    FROM board WHERE board_marked<>0 AND board_player is NULL" ) as $square )
	      $purplemarked[] = array( intval($square['x']), intval($square['y']) );
      }
      self::DbQuery( "UPDATE board SET board_marked=0");
      // Compute rotations
      /* $a = array( 1, 0,-1, 0); */
      /* $b = array( 0,-1, 0, 1); */
      /* $c = array( 0, 1, 0,-1); */
      /* $d = array( 1, 0,-1, 0); */

      // deduce new marked
      $marked = array();
      foreach ( $purplemarked as $pm ) {
	list( $px, $py ) = $pm;
	foreach ($this->card_contents['Etherweave'][6]['marked2'] as $marked_square)
	  {
	    $b = $px - $card_x;
	    $d = $py - $card_y;
	    if ( $b==1 | $b==-1 ) {
	      $a = 0;
	      $c = -$b;
	    }
	    if ( $d==1 | $d==-1 ) {
	      $c = 0;
	      $a = $d;
	    }
	    $dx = $a * $marked_square[0] + $b * $marked_square[1];
	    $dy = $c * $marked_square[0] + $d * $marked_square[1];
	    if (self::onBoard( $card_x+$dx, $card_y+$dy ))
	      {
		$marked[] = array($card_x+$dx, $card_y+$dy);
	      }
	  }
      }
      $sql = "UPDATE board SET board_marked=1 WHERE ";
      foreach ( $marked as $m )
	{
	  $sql .= "(board_x=".$m[0]." AND board_y=".$m[1].") OR ";
	}
      $sql = substr( $sql, 0, -4 );
      self::DbQuery($sql);
    }
    
    function countTwice( $player_id )
    {
      $rank = self::rankName( self::getGameStateValue( "piece_rank" ) );
      $id = self::getGameStateValue( "piece_player" );
      if ( $id != $player_id )
	{
	  self::IncGameStateValue( "turn_destroyed_".$rank, 1 );
	  self::DbQuery("UPDATE score SET score_".$rank."=score_".$rank."+1 WHERE score_player_id=".$player_id." AND score_against=".$id);
	}
    }

    function removeFromGame( )
    {
      $rank = self::getGameStateValue( "piece_rank" );
      $player = self::getGameStateValue( "piece_player" );
      if ( $rank == 0 || $rank == 1 )
	self::setGameStateValue( "piece_removed", $player );
      else
	self::setGameStateValue( "legend_removed", $player );
      self::myNotifyAllPlayers( "pieceRemoved", clienttranslate( 'That piece counts twice and is removed permanently from the game' ), array( ) );
    }

    function placePiece( $x, $y, $player_id, $player, $rank )
    { // "Place piece" effect (not the standard action)
      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_before_player",
			      self::getGameStateValue("piece_player"));
      self::setGameStateValue("piece_x", $x);
	  self::setGameStateValue("piece_y", $y);
	  //* SCC TODO may move this if adding a "chooseSquare" function
	  self::setGameStateValue("square_x", $x);
	  self::setGameStateValue("square_y", $y);
      self::setGameStateValue("piece_rank", $rank);
      self::setGameStateValue("piece_player", $player);
      self::IncGameStateValue("being_placed_piece", 1);
      self::IncGameStateValue( 'turn_placed', 1 );

      $sql = "UPDATE board SET board_rank=$rank, board_player='$player' WHERE board_x=$x AND board_y=$y";
      self::DbQuery($sql);

      $therank = self::rankName($rank);
      // Notify all players about the piece played
      self::myNotifyAllPlayers( "piecePlayed", clienttranslate( '${player_name} placed a ${therank} piece' ), array(
	    'i18n' => array( 'therank' ),
	    'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
	    'player' => $player,
	    'therank' => $therank,
	    'rank' => $rank,
	    'game_form' => self::getGameStateValue( 'game_form' ),
	    'x' => $x,
	    'y' => $y ) );

      if ( $rank == 2 && self::getGameStateValue( "game_form" ) == 1 )
	self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$player_id" );
	}

    function shootPieces( $board, $x, $y, $player_id, $targets )
    {
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      
      $dx = $x - $card_x;
      $dy = $y - $card_y;
      self::setGameStateValue("last_dx", $dx);
      self::setGameStateValue("last_dy", $dy);
      list( $who, $rank, $criteria ) = $targets;

      $k = 1;
      while ( self::onBoard( $x, $y ) )
	{
	  foreach ( $criteria as $crit )
	    if ( self::mycall_user_func_array( $crit,
				array( $board[$x][$y], $player_id, $k ) ) )
	      break 2;

	  if ( self::mycall_user_func_array( $who,
				array( $board[$x][$y], $player_id ) )
	       && self::mycall_user_func_array( $rank,
						array( $board[$x][$y] ) ) )
	    {

	      if ( $board[$x][$y]['player'] !== null )
		{
		  self::doDestroyPiece( $board, $x, $y, $player_id );

		  self::setGameStateValue("piece_before_x",
					  self::getGameStateValue("piece_x"));
		  self::setGameStateValue("piece_before_y",
					  self::getGameStateValue("piece_y"));
		  self::setGameStateValue("piece_x", $x);
		  self::setGameStateValue("piece_y", $y);
		  $board[$x][$y]['player'] = null;
		}
	    }
	  $x += $dx;
	  $y += $dy;
	  $k++;
	}
    }

    function chooseDirectionMirror( $board, $x, $y )
    { // (x,y) indicates the direction where new pieces will be placed,
      // only the ones eligible for Crystal Mirror are marked
      $card_x = self::getGameStateValue( "card_x" );
      $card_y = self::getGameStateValue( "card_y" );
      $dx = $x - $card_x;
      $dy = $y - $card_y;
      self::setGameStateValue("last_dx", $dx);
      self::setGameStateValue("last_dy", $dy);
      self::DbQuery( "UPDATE board SET board_marked=0");
      $common = array();
      $heroic = array();

      if ( $x == $card_x )
	{
	  for ( $i=$x-1 ; $i<=$x+1 ; $i++ )
	    for ( $k=1 ; $k<=2 ; $k++ )
	      {
		$j = $card_y + $k * $dy;
		if ( self::onBoard($i, $j) )
		  {
		    $jj = 2*$card_y - $j;
		    if ( self::onBoard($i, $jj)
			 && $board[$i][$jj]['player'] != null )
		      {
			if ( $board[$i][$jj]['rank'] == 0 )
			  $common[] = array( $i, $j );
			if ( $board[$i][$jj]['rank'] == 1 )
			  $heroic[] = array( $i, $j );		    
		      }
		  }
	      }
	}
      else
	{
	  for ( $j=$y-1 ; $j<=$y+1 ; $j++ )
	    for ( $k=1 ; $k<=2 ; $k++ )
	      {
		$i = $card_x + $k * $dx;
		if ( self::onBoard($i, $j) )
		  {
		    $ii = 2*$card_x - $i;
		    if ( self::onBoard($ii, $j)
			 && $board[$ii][$j]['player'] != null )
		      {
			if ( $board[$ii][$j]['rank'] == 0 )
			  $common[] = array( $i, $j );
			if ( $board[$ii][$j]['rank'] == 1 )
			  $heroic[] = array( $i, $j );		    
		      }
		  }
	      }
	}
      
      if ( count($common)>0 )
	{
	  $sql = "UPDATE board SET board_marked=1 WHERE ";
	  foreach ( $common as $m )
	    {
	      $sql .= "(board_x=".$m[0]." AND board_y=".$m[1].") OR ";
	    }
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	}
      if ( count($heroic)>0 )
	{
	  $sql = "UPDATE board SET board_marked=2 WHERE ";
	  foreach ( $heroic as $m )
	    {
	      $sql .= "(board_x=".$m[0]." AND board_y=".$m[1].") OR ";
	    }
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	} 
    }
    
    function convertPiece( $board, $x, $y, $player_id, $targets )
    {
      if ( $targets[0] == "sameRank" )
	$newrank = $board[$x][$y]['rank'];
      else
	$newrank = self::pieceToRank( $targets[0] );

      $sql = "UPDATE board SET board_player=$player_id, board_rank=$newrank WHERE board_x=$x AND board_y=$y";
      self::DbQuery($sql);

      self::updateDestroyed( $board, $x, $y, $player_id );

      self::setGameStateValue("piece_before_x",
			      self::getGameStateValue("piece_x"));
      self::setGameStateValue("piece_before_y",
			      self::getGameStateValue("piece_y"));
      self::setGameStateValue("piece_before_player",
			      self::getGameStateValue("piece_player"));
      self::setGameStateValue("piece_x", $x);
      self::setGameStateValue("piece_y", $y);
      self::setGameStateValue("piece_rank", $board[$x][$y]['rank']);
      self::setGameStateValue("piece_player", $board[$x][$y]['player']);
	
      self::myNotifyAllPlayers( "pieceConverted", clienttranslate( '${player_name} converted a ${therank} piece' ), array(
	    'i18n' => array( 'therank' ),
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
	    'therank' => self::rankName( $board[$x][$y]['rank'] ),
	    'game_form' => self::getGameStateValue( 'game_form' ),
	    'rank_destroyed' => $board[$x][$y]['rank'],
	    'color_destroyed' => $board[$x][$y]['player'],
	    'additional_destroyed' => self::getGameStateValue( "additional_destroyed" ),
	    'x' => $x,
	    'y' => $y,
	    'newrank' => $newrank
        ) );
    }

    function drawExtra( $player_id, $targets )
    {
      list( $deck, $legends ) = $targets;
      self::IncGameStateValue( "extra_deck", $deck );
      self::IncGameStateValue( "extra_legends", $legends );
    }

    function markSquares( $player_id, $targets )
    {
      $sql = "UPDATE board SET board_marked=0";
      self::DbQuery($sql);

      $clickable = self::selectPieces( $targets, "markSquares" );
      $m = 0;
      $sql = "UPDATE board SET board_marked=1 WHERE ";
      foreach ( $clickable as $x => $clickcolumn )
	foreach ( $clickcolumn as $y => $clickxy )
	if ($clickxy)
	  {
	    $sql .= "(board_x=$x AND board_y=$y) OR ";
	    $m++;
	  }

      if ( $m > 0 )
	{
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	}
    }
    
    /*** where conditions ***/

    function diagonalNeighbour( $x, $y )
    {
      $places = array();
      for ($dx = -1 ; $dx <= 1 ; $dx += 2)
	for ($dy = -1 ; $dy <= 1 ; $dy += 2)
	  if ( self::onBoard($x+$dx, $y+$dy) )
	    $places [] = array($x+$dx, $y+$dy);
      return $places;
    }

    function orthogonalNeighbour( $x, $y )
    {
      $places = array();
      for ($d = -1 ; $d <= 1 ; $d += 2)
	{
	  if ( self::onBoard($x+$d, $y) )
	    $places [] = array($x+$d, $y);
	  if ( self::onBoard($x, $y+$d) )
	    $places [] = array($x, $y+$d);
	}
      return $places;
    }

    function neighbour( $x, $y )
    {
      $places = array();
      for ($dx = -1 ; $dx <= 1 ; $dx ++)
	for ($dy = -1 ; $dy <= 1 ; $dy ++)
	  if ( self::onBoard($x+$dx, $y+$dy) && ($dx != 0 || $dy != 0) )
	    $places [] = array($x+$dx, $y+$dy);
      return $places;
	}

    function theBeing( $x, $y )
    {
      if ( self::onBoard($x, $y) )
	return array( array( intval($x), intval($y) ));
      else
	return array();
    }

    function anywhere( $x, $y )
    {
      $places = array();
      for ($i = 1 ; $i <= 9 ; $i ++)
	for ($j = 1 ; $j <= 9 ; $j ++)
	  if ( self::onBoard($i, $j) )
	    $places [] = array($i, $j);
      return $places;
    }

    function anywhereButBeing( $x, $y )
    {
      $places = array();
      for ($i = 1 ; $i <= 9 ; $i ++)
	for ($j = 1 ; $j <= 9 ; $j ++)
	  if ( self::onBoard($i, $j) && ($x != $i || $y != $j) )
	    $places [] = array($i, $j);
      return $places;
    }

	function polarityQueenFirstPiece( $x, $y )
	{ // Polarity Queen linking checks
		
		// Get set of candidate pieces (all player's pieces that are non-legendary, non-queen)
		$board = self::getBoard();
		$player_id = self::getActivePlayerId();
		$candidates = array();
		for ($i = 1; $i <= 9; $i++) {
			for ($j = 1; $j <= 9; $j++) {
				if (self::onBoard($i, $j) &&
					self::playerPiece($board[$i][$j], $player_id) &&
					self::nonLegendaryPiece($board[$i][$j], $player_id) &&
					(($x != $i) || ($y != $j)))
					$candidates[] = array($i, $j);
			}
		}	
	
		// Trial each piece as a candidate
		$places = array();
		foreach ($candidates as $candidate) {
			if (self::validPolarityQueenFirstPiece($board, $candidate, $candidates))
				$places[] = $candidate;
		}
		return $places;
	}

	function validPolarityQueenFirstPiece($board, $candidate, $second_candidates )
	{
		list( $candidate_x, $candidate_y ) = $candidate;

		// Consider each dx,dy option.
		for ( $dx = -1; $dx <= 1; $dx++ ) {
			for ( $dy = -1; $dy <= 1; $dy++ ) {
				// Must be on the board
				// Must be a legal move
				if (self::validPolarityQueenSecondMove($board, $candidate_x, $candidate_y, $dx, $dy, $second_candidates))
					return true;
			}
		}

		// We've tried every dx,dy pair and not returned true for any, so it's an invalid candidate
		return false;
	}
	
	function polarityQueenFirstMove( $x, $y )
	{ // Polarity Queen linking checks
		
		// Get set of candidate second pieces (all player's pieces that are non-legendary, non-queen)
		$board = self::getBoard();
		$player_id = self::getActivePlayerId();
		$candidates = array();
		for ($i = 1; $i <= 9; $i++) {
			for ($j = 1; $j <= 9; $j++) {
				if (self::onBoard($i, $j) && // On the board
					self::playerPiece($board[$i][$j], $player_id) && // Our piece
					self::nonLegendaryPiece($board[$i][$j], $player_id) && // Non-legendary
					(($i != self::getGameStateValue('card_x')) || ($j != self::getGameStateValue('card_y'))) && // Non-Queen
					(($x != $i) || ($y != $j))) // Non-first-piece
					$candidates[] = array($i, $j);
			}
		}

		// Test each possible move for the possibility of a second piece to do a different move.
		$destinations = array();
		// Consider each dx,dy option.
		for ( $dx = -1; $dx <= 1; $dx++ )
			for ( $dy = -1; $dy <= 1; $dy++ )
				if (self::validPolarityQueenSecondMove($board, $x, $y, $dx, $dy, $candidates))
					$destinations[] = array($x+$dx, $y+$dy);

		return $destinations;
	}

	function validPolarityQueenSecondMove($board, $candidate_x, $candidate_y, $dx, $dy, $second_candidates)
	{
		if (($dx != 0 || $dy != 0) &&
			self::onBoard($candidate_x + $dx, $candidate_y + $dy) &&
			self::allowedMove( 'combat',
								$board[$candidate_x][$candidate_y]['rank'],
								$board[$candidate_x + $dx][$candidate_y + $dy]['player'],
								$board[$candidate_x + $dx][$candidate_y + $dy]['rank'] )) {
		// Must have a partner piece that can make the opposite move afterwards, noting:
		// - it cannot be the same piece as the candidate
		// - it cannot be on the space the candidate is moving to
		// - it must be able to make the opposite move (on the board, legal destination)
		// - if the destination is the same as the candidate's destination, must be able to do a combat move onto the candidate
			foreach ($second_candidates as $partner) {
				list( $partner_x, $partner_y ) = $partner;
				if ((($partner_x != $candidate_x) || ($partner_y != $candidate_y)) &&
					(($partner_x != $candidate_x + $dx) || ($partner_y != $candidate_y + $dy)) &&
					self::onBoard($partner_x - $dx, $partner_y - $dy) &&
					self::allowedMove( 'combat',
										$board[$partner_x][$partner_y]['rank'],
										$board[$partner_x - $dx][$partner_y - $dy]['player'],
										$board[$partner_x - $dx][$partner_y - $dy]['rank']) &&
					(($partner_x - $dx != $candidate_x + $dx) || ($partner_y - $dy != $candidate_y + $dy) ||
					self::allowedMove( 'combat',
										$board[$partner_x][$partner_y]['rank'],
										$board[$candidate_x][$candidate_y]['player'],
										$board[$candidate_x][$candidate_y]['rank']))) {
					return true;
				}
			}
		}
		return false;
	}

	function anywhereButBeingOrSamePiece( $x, $y )
	{
		$places = array();
		for ($i = 1 ; $i <= 9 ; $i ++)
			for ($j = 1 ; $j <= 9 ; $j ++)
				if ( self::onBoard($i, $j) &&
					(($i != $x) || ($j != $y)) &&
					($i != self::getGameStateValue("piece_x") || $j != self::getGameStateValue("piece_y")) )
						$places [] = array($i, $j);
		return $places;
	}

    function anywhereButSamePiece( $x, $y )
    {
      $places = array();
      for ($i = 1 ; $i <= 9 ; $i ++)
	for ($j = 1 ; $j <= 9 ; $j ++)
	  if ( self::onBoard($i, $j)
	       && ($i != self::getGameStateValue("piece_x")
		   || $j != self::getGameStateValue("piece_y")) )
	    $places [] = array($i, $j);
      return $places;
    }

    function anywhereButSameTwoPieces( $x, $y )
    {
      $places = array();
      for ($i = 1 ; $i <= 9 ; $i ++)
	for ($j = 1 ; $j <= 9 ; $j ++)
	  if ( self::onBoard($i, $j)
	       && ($i != self::getGameStateValue("piece_x")
		   || $j != self::getGameStateValue("piece_y"))
	       && ($i != self::getGameStateValue("piece_before_x")
		   || $j != self::getGameStateValue("piece_before_y")) )
	    $places [] = array($i, $j);
      return $places;
    }

    function markedSquare( )
    {
      $marked = array();
      foreach ( self::getObjectListFromDB( "SELECT board_x x, board_y y FROM board WHERE board_marked<>0" ) as $square )
	$marked[] = array( intval($square['x']), intval($square['y']) );
      return $marked;      
    }

    function unusedPieces( )
    {
      $unused = array();
      foreach ( self::getObjectListFromDB( "SELECT board_x x, board_y y FROM board WHERE board_used=0" ) as $square )
	$unused[] = array( $square['x'], $square['y'] );
      return $unused;      
    }

    function samePiece( $x, $y )
    {
      return array( array( self::getGameStateValue("piece_x"),
			   self::getGameStateValue("piece_y") ) );
    }

    function sameSquare( $x, $y )
    {
      return array( array( self::getGameStateValue("square_x"),
			   self::getGameStateValue("square_y") ) );
    }

    function sameTwoPieces( $x, $y )
    {
      return array( array( self::getGameStateValue("piece_x"),
			   self::getGameStateValue("piece_y") ),
		    array( self::getGameStateValue("piece_before_x"),
			   self::getGameStateValue("piece_before_y") ) );
    }

    function charge( $x, $y, $type, $criteria, $board, $player_id )
    {
      $places = array();
      for ($i=1 ; $i<=9 ; $i++)
      	$places[$i] = array();

      /* for every direction */
      for ($dx = -1 ; $dx <= 1 ; $dx ++)
	for ($dy = -1 ; $dy <= 1 ; $dy ++)
	  if ($dx != 0 || $dy != 0) 
	      /* board is 8 moves across at most */
	      for ($k=1 ; $k<=8 && self::onBoard($x+$k*$dx, $y+$k*$dy) ; $k++)
		{
		  if (! self::allowedMove( $type, $board[$x][$y]['rank'],
				$board[$x+$k*$dx][$y+$k*$dy]['player'],
				$board[$x+$k*$dx][$y+$k*$dy]['rank'] ) )
		    break;
		  $places[$x+$k*$dx][$y+$k*$dy] = true;
		  /* Piece stops AFTER halting condition is met */
		  foreach ( $criteria as $crit )
		    if ( self::mycall_user_func_array( $crit, array($board[$x+$k*$dx][$y+$k*$dy], $player_id, $k) ))
		      break 2;
		}
      return $places;
    }
    
    function redSquares( )
    {
      return array( array(3,8), array(4,2), array(5,5), array(8,6) );
    }

    function redSquare( $x, $y )
    {
      foreach ( self::redSquares() as $square )
	{
	  list( $i, $j ) = $square;
	  if ( $i == $x && $j == $y )
	    return true;
	}
      return false;
    }
    
    function greenSquares( )
    {
      return array( array(1,5), array(3,3), array(6,8), array(7,3),
		    array(9,4) );
    }

    function greenSquare( $x, $y )
    {
      foreach ( self::greenSquares() as $square )
	{
	  list( $i, $j ) = $square;
	  if ( $i == $x && $j == $y )
	    return true;
	}
      return false;
    }

    function coloredSquares( )
    {
      return array_merge( self::greenSquares(), self::redSquares() );
    }

	function colorlessSquare( )
	{
		$places = array();
		foreach (self::anywhere(0,0) as $s) {
			list( $i, $j ) = $s;
			if (! (self::greenSquare($i, $j) || self::redSquare($i, $j)))
				$places[] = array ($i, $j);
		}
		return $places;
	}

	function emptyColorlessSquare( )
	{
		$places = array();
		$board = self::getBoard();
		foreach (self::colorlessSquare( ) as $s) {
			list ( $i, $j ) = $s;
			if (self::emptySquare( $board[$i][$j] ))
				$places[] = array ($i, $j);
		}
		return $places;
	}

	function antimatterSquare( )
	{  // Places the Antimatter Spirit can use for its warp centre-point
		$places = array();
		$board = self::getBoard();

		$active_player = self::getActivePlayerId();
		$players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces, player_legends_left legends FROM player" );
		$enemy_available = false;
		// Shortage : there is an enemy shortage if ALL enemies have 0 common pieces left.
		foreach ($players as $player_id => $player) {
			if (($player_id != $active_player) && ($player['pieces'] > 0)) {
				$enemy_available = true;
			}
		}

		foreach (self::emptyColorlessSquare( ) as $s) {
			list ( $i, $j ) = $s;
			// Check that there exists a suitable opposite-move option
			if (self::validAntimatterMoves($board, $i, $j, $enemy_available))
				$places[] = array ($i, $j);
		}
		return $places;
	}

	function validAntimatterMoves( $board, $i, $j, $enemy_available )
	{
		$dy = 1;
		for ($dx = -1; $dx <= 1; $dx++) {
			if (self::onBoard($i + $dx, $j + $dy) && self::onBoard($i - $dx, $j - $dy) &&
				!self::upgradedPiece($board[$i + $dx][$j + $dy]) &&
				!self::upgradedPiece($board[$i - $dx][$j - $dy]) && 
				($enemy_available || self::enemyCommon($board[$i + $dx][$j + $dy]) || self::enemyCommon($board[$i - $dx][$j - $dy])))
				return true;
		}

		$dy = 0;
		$dx = 1;
		if (self::onBoard($i + $dx, $j + $dy) && self::onBoard($i - $dx, $j - $dy) &&
			!self::upgradedPiece($board[$i + $dx][$j + $dy]) &&
			!self::upgradedPiece($board[$i - $dx][$j - $dy]) &&
			($enemy_available || self::enemyCommon($board[$i + $dx][$j + $dy]) || self::enemyCommon($board[$i - $dx][$j - $dy])))
			return true;

		return false;
	}

	function antimatterFirstMove( $x, $y )
	{ // For the first move of the Antimatter Spirit
	  $board = self::getBoard();
	  $places = array();

	  $active_player = self::getActivePlayerId();
	  $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces, player_legends_left legends FROM player" );
	  $enemy_available = false;
	  // Shortage : there is an enemy shortage if ALL enemies have 0 common pieces left.
	  foreach ($players as $player_id => $player) {
		  if (($player_id != $active_player) && ($player['pieces'] > 0)) {
			  $enemy_available = true;
		  }
	  }

	  for ($dx = -1; $dx <= 1; $dx++) {
		  for ($dy = -1; $dy <= 1; $dy++) {
			if (($dx != 0 || $dy != 0) &&
				self::onBoard($x + $dx, $y + $dy) && self::onBoard($x - $dx, $y - $dy) &&
				!self::upgradedPiece($board[$x + $dx][$y + $dy]) &&
				!self::upgradedPiece($board[$x - $dx][$y - $dy]) &&
				($enemy_available || self::enemyCommon($board[$x + $dx][$y + $dy])))
					$places[] = array( $x+$dx, $y+$dy);
		  }
	  }
	  return $places;
	}

	function theGateway( )
	{
	  if (self::getGameStateValue( "gateway_x" ) == 0)
	    throw new BgaVisibleSystemException ( "The gateway should not be in stasis at that point" );
	  if (self::getGameStateValue( "gateway_x" ) != -1) {
	    return array( array(self::getGameStateValue( "gateway_x" ), self::getGameStateValue( "gateway_y" )) );
	  }
	  else {
	    return array( );
	  }
	}

    function adjacentTo( $set )
    {
      $places = array();
      
      foreach ($set as $s)
	{
	  list($x, $y) = $s;
	  for ( $dx = -1 ; $dx <= 1 ; $dx++ )
	    for ( $dy = -1 ; $dy <= 1 ; $dy++ )
	      if (($dx != 0 || $dy != 0) && self::onBoard($x+$dx, $y+$dy))
		$places[] = array($x+$dx, $y+$dy);
	}
      return $places;
    }

    function onOrAdjacentToRed( )
    {
      $red = self::redSquares();
      return array_merge( self::adjacentTo( $red ), $red );
    }

    function adjacentToGreen( )
    {
      /* Some squares are adjacent to 2 greens, but this is
 converted into a bidimensional array of booleans anyway */
      $green = self::greenSquares();
      return self::adjacentTo( $green );
    }

    function onOrAdjacentToGreen( )
    {
      /* Some squares are adjacent to 2 greens, but this is
 converted into a bidimensional array of booleans anyway */
      $green = self::greenSquares();
      return array_merge( self::adjacentTo( $green ), $green );
    }

    function adjacentToYourPieces( )
    {
      $player_id = self::getActivePlayerId();
      $pieces = self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$player_id", false );
      return self::adjacentTo( array_map( "array_values", $pieces) );
    }
	
	function adjacentToSamePiece( )
	{ // Lesser Shadow Twin - finds the set of pieces adjacent to a player piece which itself was adjacent to last piece destroyed
		$player_id = self::getActivePlayerId();
		$playerPieces = self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$player_id", false );		
		$adjacentToFirstDestroyed = self::adjacentTo( array( array(self::getGameStateValue('piece_x'), self::getGameStateValue('piece_y'))) );
		$board = self::getBoard();

		$playerAdjToFirst = array();
		foreach ($adjacentToFirstDestroyed as $neighbour) {
			list($i, $j) = $neighbour;
			if ($board[$i][$j]['player'] == $player_id) {
				$playerAdjToFirst[ ] = $neighbour;
			}
		}
		return self::adjacentTo( $playerAdjToFirst );
	}

    function adjacentToEnemyPieces( ) // Used for when placing an enemy piece next to its own color
    {
      $player_id = self::getActivePlayerId();
      $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces FROM player" );
      $pieces = array();
      foreach ($players as $id => $player)
	{
	  if ($id != $player_id && $player['pieces'] > 0)
	    $pieces[$id] = self::adjacentTo( array_map( "array_values",
			self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$id", false ) ) );
	}
      return $pieces;
    }

    function adjacentToAnyEnemyPieces( ) // Used for when placing our own piece next to enemy piece
    {
      $player_id = self::getActivePlayerId();
      $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces FROM player" );
      $pieces = array();
      foreach ($players as $id => $player) {
		if ($id != $player_id)
			$pieces = array_merge( $pieces,
									array_map( "array_values", 
									self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$id", false )));
	  }
      return self::adjacentTo($pieces);
    }

    function adjacentToYourHeroic( )
    {
      $player_id = self::getActivePlayerId();
      $pieces = self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$player_id AND board_rank=1", false );
      return self::adjacentTo( array_map( "array_values", $pieces) );
    }

    function neighbourAdjacentToYourPieces( $x, $y )
    { // For Kiskin Boughrunner
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      $places = array();
      for ($dx = -1 ; $dx <= 1 ; $dx ++)
	for ($dy = -1 ; $dy <= 1 ; $dy ++)
	  {
	    $i = $x+$dx;
	    $j = $y+$dy;
	    if ( self::onBoard($i, $j) && ($dx != 0 || $dy != 0) )
	      {
		$adjacent = 0;
		for ($di = -1 ; $di <= 1 ; $di ++)
		  for ($dj = -1 ; $dj <= 1 ; $dj ++)
		    if ( self::onBoard($i+$di, $j+$dj)
			 && ($di != 0 || $dj != 0)
			 && $board[$i+$di][$j+$dj]['player'] == $player_id )
		      $adjacent++;
		if ( $adjacent > 1 ) // Account for the moving piece itself
		  $places[] = array($i, $j);
	      }
	  }
      return $places;
    }

    function adjacentDeathbringer( $card_x, $card_y )
    {
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();

      $places = array();

      for ($x=$card_x-1 ; $x<=$card_x+1 ; $x++)
	for ($y=$card_y-1 ; $y<=$card_y+1 ; $y++)
	  if ( self::onBoard( $x, $y ) && $board[$x][$y]['player'] != null
	       && ($x != $card_x || $y != $card_y ) )
	    {
	      $neighbour = 0;
	      for ( $dx = -1 ; $dx <= 1 ; $dx++ )
		for ( $dy = -1 ; $dy <= 1 ; $dy++ )
		  if ( ($dx != 0 || $dy != 0)
		       && self::onBoard($x+$dx, $y+$dy)
		       && $board[$x+$dx][$y+$dy]['player'] == $player_id
		       && ($x+$dx != $card_x || $y+$dy != $card_y ) )
		    $neighbour++;

	      if ( $neighbour >= 2*$board[$x][$y]['rank'] - 1 )
		$places[] = array( $x, $y );
	    }
      return $places;
	}
	
    function adjacentToTwo( )
    {
	  $player_id = self::getActivePlayerId();
	  $playerPieces = self::getObjectListFromDB( "SELECT board_x, board_y FROM board WHERE board_player=$player_id", false );
	  $adjacentToOne = self::adjacentTo( array_map( "array_values", $playerPieces) );
	  
	  $board = self::getBoard();
	  $pieces = array();
	  foreach ($adjacentToOne as $piece) {
		list($x, $y) = $piece;
		$neighbour_count = 0;
		for ( $dx = -1 ; $dx <= 1 ; $dx++ ) {
			for ( $dy = -1; $dy <= 1; $dy ++ ) {
				if (($dx != 0 || $dy != 0) &&
					self::onBoard($x+$dx, $y+$dy) &&
					$board[$x+$dx][$y+$dy]['player'] == $player_id)
				{
					$neighbour_count++;
				}
			}
		}
		if ($neighbour_count >= 2) {
			$pieces[ ] = $piece;
		}
	  }
      return $pieces;
	}

	function adjacentGateway( )
	{
		$gateway = self::theGateway();
		return self::adjacentTo( $gateway );
	}
	
	function adjacentSpace( )
	{
		$given_space = array( array(self::getGameStateValue( "square_x" ), self::getGameStateValue( "square_y" )) );
		return self::adjacentTo( $given_space );
	}

	function nonGateway( )
	{
		for ( $x = 1 ; $x <= 9 ; $x++ ) {
			for ( $y = 1 ; $y <= 9 ; $y++ ) {
				if (self::onBoard($x, $y) && 
					((self::getGameStateValue( "gateway_x" ) != $x) || (self::getGameStateValue( "gateway_y" ) != $y))) {
						$places[] = array($x, $y);
					}
			}
		}
      	return $places;
	}

    function nonCentral( $x, $y )
    {
      return ( $x != 5 || $y != 5 );
    }

    function centralSquares( )
    {
      $a = array();
      for ( $x = 4 ; $x <= 6 ; $x++)
	for ( $y = 4 ; $y <= 6 ; $y++)
	  $a[] = array( $x, $y );
      return $a;
    }

    function centralPlus( )
    {
      $a = array();
      for ( $i = 4 ; $i <= 6 ; $i++)
	{
	  $a[] = array( 5, $i );
	  $a[] = array( $i, 5 );
	}
      return $a;
    }

    function centralTimes( )
    {
      $a = array();
      for ( $i = 4 ; $i <= 6 ; $i++)
	{
	  $a[] = array( $i, $i );
	  $a[] = array( $i, 10-$i );
	}
      return $a;
    }

    /* returns the nearest colored squares, for Earth Elemental */
    function adjacentColored( $x, $y )
    {
      $places = array();
      for ( $dx = -1 ; $dx <= 1 ; $dx++ )
	for ( $dy = -1 ; $dy <= 1 ; $dy++ )
	  if ( ($dx != 0 || $dy != 0)
	       && ( self::redSquare($x+$dx, $y+$dy)
		    || self::greenSquare($x+$dx, $y+$dy) ) )
	    $places[] = array($x+$dx, $y+$dy);
      return $places;
    }

    function pieceNeighbour( )
    {
      return self::neighbour( self::getGameStateValue('piece_x'),
			      self::getGameStateValue('piece_y') );
    }
    
    function connectedTwo( $x, $y ) /* for Leviathan */
    {
      $piece_x = self::getGameStateValue('piece_x');
      $piece_y = self::getGameStateValue('piece_y');
      $piece_before_x = self::getGameStateValue('piece_before_x');
      $piece_before_y = self::getGameStateValue('piece_before_y');

      $first_two_neighbours = array_merge(
		 self::neighbour( $piece_x, $piece_y ),
		 self::neighbour( $piece_before_x,
				  $piece_before_y ) );

      $sql = "UPDATE board SET board_marked=0";
      self::DbQuery($sql);
      $sql = "UPDATE board SET board_marked=1 WHERE ";
      foreach ( $first_two_neighbours as $key => $marked )
	{
	  // Do not downgrade twice
	  if ( ($marked[0] == $piece_x && $marked[1] == $piece_y)
	       || ($marked[0] == $piece_before_x
		   && $marked[1] == $piece_before_y) )
	    unset( $first_two_neighbours[$key] );
	  else
	    $sql .= "(board_x=".$marked[0]." AND board_y=".$marked[1].") OR ";
	}
      $sql = substr( $sql, 0, -4 );
      self::DbQuery($sql);

      return $first_two_neighbours;
    }
    /* Hack : we mark the neighbours of the first two pieces to avoid
       memorizing the coordinates of the first one. */
    function connectedThree( $x, $y ) /* for Leviathan */
    {
      return array_merge( self::pieceNeighbour(),
			  self::markedSquare() );
      // Technically the last 3 pieces shouldn't be in here, but this
      // function is used only for a connected group of destroyed pieces,
      // so the effect cannot be applied twice anymay.
    }

    function distance_n( $n, $x, $y ) /* n or less */
    {
      $places = array();
      for ($dx = -$n ; $dx <= $n ; $dx ++)
	for ($dy = -$n ; $dy <= $n ; $dy ++)
	  if ( ($dx != 0 || $dy != 0) && self::onBoard($x+$dx, $y+$dy) )
	    $places [] = array($x+$dx, $y+$dy);
      return $places;
    }

    function distance2( $x, $y ) /* or less */
    {
      return self::distance_n( 2, $x, $y );
	} 

    function distance3( $x, $y ) /* or less */
    {
      return self::distance_n( 3, $x, $y );
    }

    function leap2( $x, $y ) /* distance 2 exactly */
    {
      $places = array();
      for ($d = -2 ; $d <= 2 ; $d++)
	for ($e = -2 ; $e <= 2 ; $e += 4)
	  {
	    if ( self::onBoard($x+$d, $y+$e) )
	      $places [] = array($x+$d, $y+$e);
	    if ( self::onBoard($x+$e, $y+$d) )
	      $places [] = array($x+$e, $y+$d);
	  }
      return $places;
    }

	function moveToDistance2FromBeing( $x, $y ) /* distance 2 exactly */
	{
		$end_positions = self::leap2( self::getGameStateValue('card_x'), self::getGameStateValue('card_y') );
		$places = array();
		foreach ($end_positions as $square) {
			list($i, $j) = $square;
			if ((abs($i - $x) <= 1) && (abs($j - $y) <= 1) && (($i != $x) || ($j != $y)))
				$places [] = array($i, $j);
		}
		return $places;
	}

    function nextPieceSameDirection( $x, $y )
    {
      $board = self::getBoard();
      $dx = self::getGameStateValue('last_dx');
      $dy = self::getGameStateValue('last_dy');
      $to_x = $x + $dx;
      $to_y = $y + $dy;

      while ( self::onBoard( $to_x, $to_y ) )
	{
	  if ( $board[$to_x][$to_y]['player'] !== null )
	    {
	      return array( array( $to_x, $to_y ) );
	    }
	  $to_x += $dx;
	  $to_y += $dy;
	}
      return array();
    }

    function sameDirection( $x, $y )
    {
      $to_x = $x + self::getGameStateValue('last_dx');
      $to_y = $y + self::getGameStateValue('last_dy');
      if ( self::onBoard( $to_x, $to_y ) )
	return array( array( $to_x, $to_y ) );
      else
	return array();
    }

    function oppositeDirection( $x, $y )
    {
      $to_x = $x - self::getGameStateValue('last_dx');
      $to_y = $y - self::getGameStateValue('last_dy');
      if ( self::onBoard( $to_x, $to_y ) )
	return array( array( $to_x, $to_y ) );
      else
	return array();
    }

	function distToGateway( $x, $y )
	{
		$gate_x = self::getGameStateValue( 'gateway_x' );
		$gate_y = self::getGameStateValue( 'gateway_y' );
		return (max(abs($x - $gate_x), abs($y - $gate_y)));
	}

	function towardsGateway( $x, $y, $allows_onto=true )
	{
		$result = array();
		$current_distance = self::distToGateway($x, $y);
		for ($dx = -1; $dx <= 1; $dx++) {
			for ($dy = -1; $dy <= 1; $dy++) {
				// Don't need to filter out dx,dy==0,0 since that won't reduce distance
				if ( self::onBoard( $x + $dx, $y + $dy ) &&
					(self::distToGateway($x + $dx, $y + $dy) < $current_distance) &&
					($allows_onto || self::distToGateway($x + $dx, $y + $dy) > 0)) {
						$result[] = array( $x+$dx, $y+$dy );
					}
			}
		} 
		return $result;
	}

	function towardsGatewayNotOnto( $x, $y )
	{
		return self::towardsGateway($x, $y, false);
	}

	function towardsSingularity( $x, $y )
	{
		$result = array();
		$singularity_x = self::getGameStateValue( 'square_x' );
		$singularity_y = self::getGameStateValue( 'square_y' );
		for ($dx = -1; $dx <= 1; $dx++) {
			for ($dy = -1; $dy <= 1; $dy++) {
				if (($x + $dx == $singularity_x) &&
					($y + $dy == $singularity_y)) {
						$result[] = array( $x+$dx, $y+$dy );
					}
			}
		} 
		return $result;
	}

    function noMoreThan45( $x, $y )
    { // For War Sled
      $result = self::around45( $x, $y );
      $dx = self::getGameStateValue('last_dx');
      $dy = self::getGameStateValue('last_dy');
      if ( self::onBoard( $x+$dx, $y+$dy ) )
	$result[] = array( $x+$dx, $y+$dy );
      return $result;
    }

    function around45( $x, $y )
    { // For Fire Dragon
      $dx = self::getGameStateValue('last_dx');
      $dy = self::getGameStateValue('last_dy');

      // 45 degrees to the left
      $di = $dx+$dy;
      $dj = $dy-$dx;
      if (abs($di) == 2)
	$di /= 2;
      if (abs($dj) == 2)
	$dj /= 2;

      $result = array();
      if ( self::onBoard( $x+$di, $y+$dj ) )
	$result[] = array($x+$di, $y+$dj);
      if ( self::onBoard( $x-$dj, $y+$di ) )
	$result[] = array($x-$dj, $y+$di);

      return $result;
    }

    function starMarked( $x, $y )
    { // For Bone Catapult
      $selected = array();
      foreach ( self::getObjectListFromDB( "SELECT board_x x, board_y y FROM board WHERE board_marked=1" ) as $marked )
	{
	  $xx = $marked['x'];
	  $yy = $marked['y'];
	  $dx = $xx - $x;
	  $dy = $yy - $y;
	  while( self::onBoard( $xx, $yy ) )
	    {
	      $selected[] = array( $xx, $yy );
	      $xx += $dx;
	      $yy += $dy;
	    }
	}
      return $selected;      
    }

    function stormDistance( $piece, $player_id, $k )
    {
      return ( $k >= 5 - 2 * self::getGameStateValue( "piece_rank" ) );
    }

    /*** rank conditions ***/
    
    function commonPiece( $piece )
    {
      return ( $piece['player'] !== null && $piece['rank'] == 0);
	}
	
	function enemyCommon( $piece )
	{
		$active_player = self::getActivePlayerId();
		return ( $piece['player'] !== null && $piece['player'] !== $active_player && $piece['rank'] == 0);
	}

    function heroicPiece( $piece )
    {
      return ( $piece['player'] !== null && $piece['rank'] == 1);
    }

    function legendaryPiece( $piece )
    {
      return ( $piece['player'] !== null && $piece['rank'] == 2 );
    }

    function upgradedPiece( $piece )
    {
      return ( $piece['player'] !== null && $piece['rank'] > 0);
    }

    function nonLegendaryPiece( $piece )
    {
      return ( $piece['player'] !== null && $piece['rank'] < 2);
    }

    function sameRank( $piece )
    {
      return ( $piece['player'] !== null &&
	$piece['rank'] == self::getGameStateValue("piece_rank") );
    }

    function sameRankSwap( $piece, $id, $rank )
    {
      return ( $piece['player'] !== null && $piece['rank'] == $rank );
    }

    function anyrank( $piece )
    {
      return true;
    }

    function rankName( $rank )
    {
      switch ($rank)
	{
	case 1:
	  return clienttranslate("heroic");
	case 2:
	  return clienttranslate("legendary");
	case 0:
	  return clienttranslate("common");
	default:
	  throw new BgaVisibleSystemException ( "Wrong rank number" );
	}
    }

    function standardMerchant( $piece )
    {
      return ($piece['player'] == null
	   || ($piece['rank'] < self::getGameStateValue( "merchant_rank" ) ) );
    }

    /*** who conditions ***/
    
    /* Truly anything, whether there is a piece or not */
    function anything( $piece, $id )
    {
      return true;
    }
    
    /* Belongs to any player, but there must be a piece */
    function anyPiece( $piece, $id )
    {
      return ( $piece['player'] !== null );
    }
    
    function enemyPiece( $piece, $id )
    {
      $owner = $piece['player'];
      return ( $owner != $id && $owner !== null );
    }
    
    function playerPiece( $piece, $id )
    {
      return ( $piece['player'] == $id );
    }
    
    function emptySquare( $piece )
    {
      return ( $piece['player'] == null );
    }

    function samePlayer( $piece, $id )
    {
      return ( $piece['player'] == self::getGameStateValue("piece_player") );
    }

    function samePlayerBefore( $piece, $id )
    {
      $pbp = self::getGameStateValue("piece_before_player");
      if ( $pbp != 0 )
	return ( $piece['player'] == $pbp );
      else /* Special case if Forest Ancient can place only one piece */
	return ($piece['player'] == self::getGameStateValue("piece_player"));
    }

    function otherEnemy( $piece, $id )
    {
      return ( $piece['player'] !== null
	       && $piece['player'] != $id
	       && $piece['player'] != self::getGameStateValue("piece_player"));
    }

    function otherLastTwoEnemies( $piece, $id )
    {
      return ( $piece['player'] !== null
	       && $piece['player'] != $id
	       && $piece['player'] != self::getGameStateValue("piece_player")
	       && $piece['player'] !=
	       self::getGameStateValue("piece_before_player")
	       );
    }

    function moreCommonOpponent( $piece, $id )
    { // Forest Wardens
      $players = self::computePiecesNumbers();
      return ( $piece['player'] !== null &&
	       $players[$piece['player']]['pieces']
	       - $players[$piece['player']]['upgraded']
	         > $players[$id]['pieces'] - $players[$id]['upgraded'] );
    }

    function moreUpgradedOpponent( $piece, $id )
    { // Forest Wardens
      $players = self::computePiecesNumbers();
      return ( $piece['player'] !== null &&
	       $players[$piece['player']]['upgraded']
	       > $players[$id]['upgraded'] );
    }

	function darkSphereThirdOpponent( $piece, $id )
	{ // Dark Sphere
	   $first_piece = self::getGameStateValue("piece_before_player");
	   $second_piece = self::getGameStateValue("piece_player");
	   if ($first_piece != $second_piece) {
		   return self::anyPiece($piece, $id);
	   } else {
		   return (($piece['player'] !== null) && ($piece['player'] != $first_piece));
	   }
	}

    /*** Higher-order conditions and sets combinators ***/
    function orcond( $f, $g )
    {
      return function ( ) use ($f,$g) {
	$a = func_get_args();
	return ( call_user_func_array($f, $a)
		 || call_user_func_array($g, $a) );
      };
    }

    function andcond( $f, $g )
    {
      return function ( ) use ($f,$g) {
	$a = func_get_args();
	return ( call_user_func_array($f, $a)
		 && call_user_func_array($g, $a) );
      };
    }

    function union( )
    {
      $fs = func_get_args();
      return function ( ) use ($fs) {
	$a = func_get_args();
	$r = array();
	foreach ($fs as $f)
	  $r = array_merge( $r, call_user_func_array($f, $a) );
	return $r;
      };
    }

    function intersection( )
    {
      $fs = func_get_args();
      return function ( ) use ($fs) {
	$a = func_get_args();
	$initr = false;
	foreach ($fs as $f)
	  {
	    $fr = call_user_func_array($f, $a);
	    foreach ($fr as $k => $fre )
	      $fr[$k] = serialize($fre);
	    if ($initr)
	      {
		$r = array_intersect( $r, $fr );
	      }
	    else
	      {
		$r = $fr;
		$initr = true;
	      }
	  }
	return array_map("unserialize", $r);
      };
    }

    function orgrid( )
    {
      $result = self::prepareSelected();
      foreach ( func_get_args() as $g )
	foreach ( $g as $x => $gcolumn )
	foreach ( $gcolumn as $y => $gxy )
	if ($gxy)
	  $result[$x][$y] = true;
      return $result;
    }

    /*** Generic pieces selectors ***/

    function prepareSelected()
    {
      $selected = array();
      for ($i=1 ; $i<=9 ; $i++)
      	$selected[$i] = array();
      return $selected;
    }

    function selectPieces( $criteria, $effect, $board = NULL )
    {
      if (count($criteria) == 0)
	return array();
      $player_id = self::getActivePlayerId();
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      $selected = self::prepareSelected();

      $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces, player_legends_left legends FROM player" );

      // Shortage : convert is always to your non-legendary piece
      if ( $effect == "convertPiece" && $players[$player_id]['pieces'] == 0 )
	return $selected;

      if ( $board === NULL )
	$board = self::getBoard();
      list( $who, $rank, $where ) = $criteria ;

      if ( $where == "adjacentToEnemyPieces")
	foreach ( self::mycall_user_func_array( $where, array($card_x, $card_y) ) as $id => $places )
	  {
	    $selected[$id] = self::prepareSelected();
	    foreach ( $places as $place )
	      {
		list( $i, $j ) = $place;
		if (self::mycall_user_func_array( $who, array( $board[$i][$j], $player_id ) )
		    && ( $board[$i][$j]['player'] == null
			 || self::mycall_user_func_array($rank, array($board[$i][$j]))) )
		  {
		    $selected[$id][$i][$j] = true;
		  }
	      }
	  }
      else
	foreach ( self::mycall_user_func_array( $where, array($card_x, $card_y) ) as $place )
	  {
	    list( $i, $j ) = $place;
	    if (self::mycall_user_func_array( $who, array( $board[$i][$j], $player_id ) )
		&& ( $board[$i][$j]['player'] == null
		|| self::mycall_user_func_array($rank, array($board[$i][$j])) ) )
	      {
		// Shortage : upgrade to and downgrade from legend
		if ( ! ( $effect == "upgradePiece"
			 && $board[$i][$j]['rank'] == 1
			 && $players[$board[$i][$j]['player']]['legends'] == 0
			 || $effect == "downgradePiece"
			 && $board[$i][$j]['rank'] == 2
			 && $players[$board[$i][$j]['player']]['pieces'] == 0 ) )
		  $selected[$i][$j] = true;
	      }
	  }
      return $selected;
    }

    // Special moves types
    function oneCombatMoveOnly()
    {
      if (self::getGameStateValue( "combat_moves" ) > 0)
	return "standard";
      else
	return "combat";
    }

    function masterOfIntrigueMoves( $x, $y )
    {
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      if ($x == $card_x && $y == $card_y)
	return "standard";
      else
	return "combat";
    }

    function allowedMove( $type, $movingrank, $targetplayer, $targetrank)
    {
      return ($targetplayer == null || $type == 'swap'
	      || ($type == 'standard' && $targetrank < $movingrank)
	      || ($type == 'combat' && $targetrank <= $movingrank) );
    }

    function selectPiecesForMove( $x, $y, $moveinfo )
    { // $x and $y are the location of the piece that moves
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      list( $type, $distance, $criteria ) = $moveinfo;

      if ($type != "standard" && $type != "combat" && $type != "swap")
	$type = self::mycall_user_func_array( $type, array($x,$y) );

      switch ( $distance )
	{
	case 'charge':
	  /* We interrupt the selection for this case */
	  return self::charge( $x, $y, $type, $criteria, $board, $player_id );
	  break;
	case 'leap':
	  $places = self::anywhereButBeing( $x, $y );
	  break;
	case 'move':
	  $places = self::neighbour( $x, $y );
	  break;
	default:
	  $places = self::mycall_user_func_array( $distance, array( $x, $y ) );
	  break;
	}

      $selected = array();
      for ($i=1 ; $i<=9 ; $i++)
      	$selected[$i] = array();

      foreach ( $places as $place )
	{
	  list( $i, $j ) = $place;
	  $selected[$i][$j] = self::allowedMove( $type, $board[$x][$y]['rank'],
			$board[$i][$j]['player'], $board[$i][$j]['rank'] );

	  foreach ( $criteria as $crit )
	    if ( ! self::mycall_user_func_array( $crit, array($board[$i][$j], $player_id, $board[$x][$y]['rank'] ) ))
	      $selected[$i][$j] = false;
	}
      return $selected;
    }

    function selectPiecesForMovePiece( $moveinfo )
    {
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      list( $movablecrit, $type, $distance, $criteria ) = $moveinfo;
      // First select the pieces that can move
      $movable = self::selectPieces( $movablecrit, "movePiece", $board );
      // Then turn $movable into a double array of double arrays representing possible moves.
      foreach ( $movable as $mx => $movablecolumn )
	foreach ( $movablecolumn as $my => $movablexy )
	{
	  $movable[$mx][$my] = self::selectPiecesForMove ( $mx, $my,
				  array($type, $distance, $criteria) );
	  list($perf, $x, $y) = self::countPerformable( $movable[$mx][$my] );
	  if ($perf == 0)
	    unset($movable[$mx][$my]);	  
	}

      return $movable;
    }

    function clickable_merge( $clickable1, $clickable2 )
    {
      $clickable = $clickable1;
      foreach ( $clickable2 as $x => $clickcolumn )
	foreach ( $clickcolumn as $y => $clickxy )
	if ($clickxy)
	  {
	    $clickable[$x][$y] = true;
	  }
      return $clickable;
    }

    function selectPiecesForEffect( $effect, $x, $y, $target )
    {
      switch ($effect)
	{
	case "moveBeing":
	  return self::selectPiecesForMove( $x, $y, $target );
	case "movePiece":
	  return self::selectPiecesForMovePiece( $target );
	case "becomeGateway":
	  return self::selectPieces( array( "playerPiece", "nonLegendaryPiece", $target[2] ), "becomeGateway" );
	case "placePiece":
	  // Shortage : place is always on an empty square
	  $player_id = self::getActivePlayerId();
	  $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces, player_legends_left legends FROM player" );
	  if ( $target[1] == 'legendaryPiece' )
	    $rank = 'legends';
	  else
	    $rank = 'pieces';	    
	  if ( $target[0] == "playerPiece" )
	    $available = $players[$player_id][$rank];
	  else
	    {
	      $available = 0;
	      foreach ($players as $id => $player)
		if ( $id != $player_id )
		  $available += $player[$rank];
	    }
	  if ( $available == 0 )
	    return self::prepareSelected();
	  else
	    return self::selectPieces(
		array( "emptySquare", "anyrank", $target[2] ), "placePiece" );
	case "chooseSquare":
	  return self::selectPieces( array("anything", "anyrank", $target[0]), "chooseSquare" );
	case "convertPiece":
	  return self::selectPieces(
		array( "enemyPiece", $target[1], $target[2] ), "convertPiece");
	case "shootPieces":
	  return self::selectPieces(
		array( "anything", "anyrank", "markedSquare" ), "shootPieces");
	case "chooseDirectionMirror":
	  return self::selectPieces(
		array( "anything", "anyrank", "markedSquare" ), "chooseDirectionMirror");
	case 'gainAction':
	case 'loseAction':
	case 'gainBonusImprovisation':
	case 'voidSummoning':
	  return array();
	case 'discardPending':
	  $pending = self::getGameStateValue( "pending_being" );
	  if ( $pending >= 0 && $pending != 5 )
	    return array( 1 => array( 1 => 1 ) );
	  else
	    return array();
	case 'discardFlare':
	  if ( self::flareNum( self::getActivePlayerId() ) >= 0 )
	    return array( 1 => array( 1 => 1 ) );
	  else
	    return array();
	case 'discardLegends':
	  if ( count( $this->cards->getCardsOfTypeInLocation( 'Legends', null,
				'hand', self::getActivePlayerId() ) ) >= 0 )
	    return array( 1 => array( 1 => 1 ) );
	  else
	    return array();	  
	case 'chooseOption':
	  $deck = self::getGameStateValue("deck_played");
	  $card_id = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");
	  if ( $deck != -1 )
	    {
	      $deck = $this->decks[$deck];
	      $card = $this->card_contents[$deck][$card_id];
	    }
	  else
	    {
	      $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	    }
	  $clickable1 = self::selectPiecesForEffect( 
			$card['effects'][$effect_number+2], $x, $y,
			$card['effecttargets'][$effect_number/2+1] );
	  $clickable2 = self::selectPiecesForEffect( 
			$card['effects'][$effect_number+4], $x, $y,
			$card['effecttargets'][$effect_number/2+2] );
	  $clickable = self::clickable_merge( $clickable1, $clickable2 );
	  if ( isset( $card['effecttargets'][$effect_number/2][4] ) )
	    {
	      $clickable3 = self::selectPiecesForEffect( 
			$card['effects'][$effect_number+6], $x, $y,
			$card['effecttargets'][$effect_number/2+3] );
	      $clickable = self::clickable_merge( $clickable, $clickable3 );
	    }
	  return $clickable;
	case 'putCardOnTop':
	  return array_merge(
		self::getCardsOfTypeInLocation( "Sylvan", "discard" ),
		self::getCardsOfTypeInLocation( "Sylvan", "discard_buffer" ) );

	case 'putFrozenInPlay':
	  $cards = array_merge(
		self::getCardsOfTypeInLocation( "Everfrost", "discard" ),
		self::getCardsOfTypeInLocation( "Everfrost", "discard_buffer" ) );
	  $frozen = self::getGameStateValue( 'frozen_effect' );
	  foreach ( $cards as $card_id => $card )
	    {
	      if ( $card['type_arg'] == $frozen
		|| ! isset(
		$this->everfrost_contents[$card['type_arg']]['frozentext'] ) )
		unset( $cards[$card_id] );
	    }
	  return $cards;
	case 'discardSingleCard':
	  $cards = $this->cards->getCardsInLocation( 'hand',
						   self::getActivePlayerId() );
	  $pending = self::getGameStateValue( 'pending_being' );
	  foreach ( $cards as $card_id => $card )
	      if ( $card['type_arg'] == $pending
		   && $card['type']=='Etherweave' )
		unset( $cards[$card_id] );
	  return $cards;
	case 'performWarp':
	  $card_id = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");
	  $card = $this->card_contents['Etherweave'][$card_id];
	  return call_user_func(array($this,
			      $card['effecttargets'][$effect_number/2]));
	default:
	  return self::selectPieces( $target, $effect );
	}
    }

    /*** Effect triggering conditions ***/

    function ifDestroyed ()
    {
      return (self::getGameStateValue('being_destroyed') > 0);
    }

    function ifOversummonedEnemy ()
    {
      $o = self::getGameStateValue('oversummoned');
      return ( $o != 0 && $o != self::getActivePlayerId() );
    }

    function ifNotDestroyed ()
    {
      return (self::getGameStateValue('being_destroyed') == 0);
    }

    function ifNotDestroyedAtAll ()
    {
      return (self::getGameStateValue('oversummoned') == 0 && 
	      + self::getGameStateValue('being_destroyed') == 0 );
    }

    function ifNotDestroyedLegend ()
    { /* Special for Legend Slayer : includes self-destructed pieces */
	return (self::getGameStateValue('being_destroyed') == 0
	      || self::getGameStateValue('piece_rank') != 2);
    }

    function ifDestroyedLegend ()
    {
      // counts only enemy pieces
      return (self::getGameStateValue('being_destroyed_legendary') > 0 );
    }

    function ifNotLastAction ()
    {
      return (self::getGameStateValue('remaining_actions') > 1);
    }

    function ifNotMoved ()
    {
      return (self::getGameStateValue('being_moved') == 0);
    }

    function ifMoved ()
    {
      return (self::getGameStateValue('being_moved') > 0);
    }

    function ifNotCombatMoved ()
    {
      return (self::getGameStateValue( "combat_moves" ) == 0);
    }

    function ifNotSkipped ()
    {
      return (self::getGameStateValue('has_skipped') == 0);
	}
	
	function ifSkipped ()
    {
      return (self::getGameStateValue('has_skipped') == 1);
    }

    //* BW NEW
    function ifDeckNonEmpty ()
    {
      $players = self::loadPlayersBasicInfos();
      $color = $players[self::getActivePlayerId()]['player_color'];
      $school = $this->decks[array_search($color, $this->schools_colors)];
      return ($this->cards->countCardInLocation($school.'Deck')
	      + count(self::getCardsOfTypeInLocation( $school,"return_buffer"))
	      != 0);
    }

    function ifUpgradedPiece ()
    {
      return (self::getGameStateValue('being_upgraded_piece') > 0);
    }

    function ifDowngradedPiece ()
    {
      return (self::getGameStateValue('being_downgraded_piece') > 0);
    }

    function ifPlacedPiece ()
    {
      return (self::getGameStateValue('being_placed_piece') > 0);
    }

    function ifLastRankCommon ()
    {
      return (self::getGameStateValue('piece_rank') == 0);
    }

    function ifLastRankNonLegendary ()
    {
      return (self::getGameStateValue('piece_rank') < 2);
    }

    function ifLastRankLegendary ()
    {
      return (self::getGameStateValue('piece_rank') == 2);
    }

    function always ()
    {
      return true;
    }

    function ifBeingOnGreenSquare()
    {
      return ( self::greenSquare( self::getGameStateValue('card_x'),
				  self::getGameStateValue('card_y') ) );
    }
    
    function ifBeingOnRedSquare()
    {
      return ( self::redSquare( self::getGameStateValue('card_x'),
				self::getGameStateValue('card_y') ) );
    }

    function ifPieceOnRedSquare()
    {
      return ( self::redSquare( self::getGameStateValue('piece_x'),
				self::getGameStateValue('piece_y') ) );
    }
	
    function ifGatewayOnGreenSquare()
    {
      return ( self::greenSquare( self::getGameStateValue('gateway_x'),
				  self::getGameStateValue('gateway_y') ) );
    }
    
    function ifGatewayOnRedSquare()
    {
      return ( self::redSquare( self::getGameStateValue('gateway_x'),
				self::getGameStateValue('gateway_y') ) );
	}
	
	function ifGateway()
	{
		return ( self::getGameStateValue('gateway_x') == self::getGameStateValue('card_x') &&
				self::getGameStateValue('gateway_y') == self::getGameStateValue('card_y') );
	}

	function ifNotGateway()
	{
		return ( !self::ifGateway() );	
	}

	function ifGatewayElsewhere()
	{
		return ( self::ifNotGateway() &&
				self::getGameStateValue('gateway_x') != -1);
	}

	function ifNotMaxPieces()
	{
		$players = self::computePiecesNumbers();
		$player = $players[self::getActivePlayerId()];
		foreach( $players as $player_count )
		{
			if( $player_count['pieces'] > $player['pieces'] ) {
				return true;
			}
		}
		return false;
	}

	function ifOpponentHasPieces()
	{
        $players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces FROM player" );
		$player_id = self::getActivePlayerId();
		foreach( $players as $id => $player_count )
		{
			if( $id != $player_id && $player_count['pieces'] > 0 ) {
				return true;
			}
		}
		return false;
	}

    function ifDestroyedCommon ()
    {
      return (self::getGameStateValue('being_destroyed_common') > 0);
    }
    
    function ifDestroyedHeroic ()
    {
      return (self::getGameStateValue('being_destroyed_heroic') > 0);
    }

    function ifDestroyedTwo ()
    {
      return (self::getGameStateValue('being_destroyed') >= 2);
    }

    function ifColorsLeft ()
    { // Woodland Druid
      $players = self::loadPlayersBasicInfos();
      $n = self::getPlayersNumber();
      $player_id = self::getActivePlayerId();
      foreach ( $players as $id => $player )
	{
	  if ( $id == $player_id
	       || $id == self::getGameStateValue('piece_player')
	       || $id == self::getGameStateValue('piece_before_player') )
	    $n--;
	}
      return ($n > 0);
    }

    function ifPlaced ()
    {
      return (self::getGameStateValue('being_placed_piece') > 0);
    }

    function ifPlacedTwo ()
    {
      return (self::getGameStateValue('being_placed_piece') >= 2);
    }

    function ifChoiceOne ()
    { // Must be used after chooseOption effect
      return (self::getGameStateValue('option_chosen') == 1);
    }

    function ifChoiceTwo ()
    {
      return (self::getGameStateValue('option_chosen') == 2);
    }

    function ifChoiceThree ()
    {
      return (self::getGameStateValue('option_chosen') == 3);
    }

    function shootDistanceTwo( $square, $player_id, $k )
    { // shot stopping criterion
      return ( $k > 2 );
    }

    function ifFullmoon ()
    { // Werewolf
      return ( self::getGameStateValue( "fullmoon" ) == 1 );
    }

    function ifNotFullmoon ()
    {
      return ( self::getGameStateValue( "fullmoon" ) == 0 );
	}
	
	function ifNoEmperorYet ()
	{
		return (self::getGameStateValue( "eternal_emperor_warped") == 0);
	}

	function ifTwinInDiscard ()
	{
		$card_played = self::getGameStateValue("card_played");
		if ($card_played == 2) {
			$twin = self::getUniqueCardOfType( $this->cards, "Etherweave", 8);
		} elseif ($card_played == 8) {
			$twin = self::getUniqueCardOfType( $this->cards, "Etherweave", 2);
		} else {
			throw new BgaVisibleSystemException ( "Looking for a twin of a non-twin card" );
		}

		return (($twin['location'] == 'discard') || ($twin['location'] == 'discard_buffer'));
	}

	function ifPendingBeingNotEmperor ()
	{
	    $pending = self::getGameStateValue("pending_being");
	    if ( $pending == 5 ) {
            $card_id = self::getGameStateValue( "card_played" );
            switch ($card_id) {
            case 13:
                self::myNotifyAllPlayers( "merchantImmunity", clienttranslate('Reality Patch could not discard Merchant of Time'), array() );
                break;
            case 21:
                self::myNotifyAllPlayers( "merchantImmunity", clienttranslate('Paradow Worm could not discard Merchant of Time'), array() );
                break;            
            }
        }
        return ( $pending >= 0 && $pending != 5 );
	}

    function ifCaptured ()
    {
      return (self::getGameStateValue('merchant_player') != 0);
    }

    function ifAdjacentCommonLeft () {
      $adjacent = self::adjacentSpace();
      $board = self::getBoard();
      foreach ( $adjacent as $square ) {
	list( $x, $y ) = $square;
	if ( $board[$x][$y]['player'] !== null && $board[$x][$y]['rank'] ==0 )
	  return true;
      }
      return false;
    }

    /* Tasks criteria */

    function destroyAtLeast( $pieces, $upgraded )
    {
      $common = self::getGameStateValue( "turn_destroyed_common" );
      $heroic = self::getGameStateValue( "turn_destroyed_heroic" );
      $legendary = self::getGameStateValue( "turn_destroyed_legendary" );
      return array( ($common + $heroic + $legendary >= $pieces
		&& $heroic + $legendary >= $upgraded),
	       null );
    }

    function endOfLegends( )
    {
      $heroic = self::getGameStateValue( "turn_destroyed_heroic" );
      $legendary = self::getGameStateValue( "turn_destroyed_legendary" );
      return array( ($legendary >= 1 || $heroic >= 2),
	       null );
    }

    function summoningTask( $beings, $legends, $colored, $which_color )
    {
      $b = self::getGameStateValue( "turn_summoned_beings" );
      $l = self::getGameStateValue( "turn_summoned_legends" );

      if ( $which_color == 'colored' )
	$c = self::getGameStateValue( "turn_summoned_red" )
	     + self::getGameStateValue( "turn_summoned_green" );
      else
	$c = self::getGameStateValue( "turn_summoned_".$which_color );

      return array( ($b >= $beings && $l >= $legends && $c >= $colored),
	       null );
    }

    function countTask( $who, $rank, $where, $board )
    {
      $grid = self::selectPieces( array( $who, $rank, $where ),
				 'task', $board );
      $c = self::countPerformable( $grid );
      return array( $c[0], $grid );
    }

    function conquestTask( $pieces, $upgraded, $legendary, $which_color )
    {
      $board = self::getBoard();
      list( $p, $pgrid) = self::countTask( "playerPiece", "anyrank",
					  $which_color, $board );
      list( $u, $ugrid) = self::countTask( "playerPiece", "upgradedPiece", 
					  $which_color, $board );
      list( $l, $lgrid) = self::countTask( "playerPiece", "legendaryPiece", 
					  $which_color, $board );
      if ( $pieces > 0 )
	$used = $pgrid;
      else
	$used = $ugrid;
      return array( ($p >= $pieces && $u >= $upgraded && $l >= $legendary),
	       $used );
    }
    
    function rainbowDominance( )
    {
      $board = self::getBoard();
      list( $mine_red, $mrgrid ) = self::countTask( "playerPiece", 
						    "upgradedPiece",
						    "redSquares", $board );
      list( $theirs_red, $trgrid ) = self::countTask( "enemyPiece",
						      "upgradedPiece",
						      "redSquares", $board );
      list( $mine_green, $mggrid ) = self::countTask( "playerPiece",
						      "upgradedPiece",
						      "greenSquares", $board );
      list( $theirs_green, $tggrid ) = self::countTask( "enemyPiece",
						"upgradedPiece",
						"greenSquares", $board );
      return array( ($mine_red > $theirs_red && $mine_green > $theirs_green),
		    self::orgrid( $mrgrid, $mggrid ) );
    }

    function line( $dir, $num )
    { /* returns a callable suitable as a "where" function */
      switch ( $dir )
	{
	case 'row':
	  $x = 0;
	  $dx = 1;
	  $y = $num;
	  $dy = 0;
	  break;
	case 'col':
	  $x = $num;
	  $dx = 0;
	  $y = 0;
	  $dy = 1;
	  break;	  
	default:
	  throw new BgaVisibleSystemException ( "Wrong line direction" );
	}
      return function () use ($x, $dx, $y, $dy) {
	$a = array();
	for ( $i = 1 ; $i <= 9 ; $i++ )
	  $a[] = array( $x + $i*$dx, $y + $i*$dy );
	return $a;
      };
    }

    function dominanceTask( $pieces, $upgraded, $enemy_penalty, $loc )
    { // Decode location
      $board = self::getBoard();
      switch ( $loc )
	{
	case 'central' :
	  $locations = array( "centralSquares" );
	  break;
	case 'centerCross' :
	  $locations = array( "centralPlus", "centralTimes" );
	  break;
	case 'lines' :
	  $locations = array();
	  for ( $i = 4 ; $i <= 6 ; $i++ )
	    {
	      $locations[] = self::line( 'row', $i );
	      $locations[] = self::line( 'col', $i );
	    }
	  break;
	case 'aroundEnemy':
	  $locations = array();
	  $enemy_pieces = self::selectPieces(
				array("enemyPiece", "anyrank", "anywhere"),
				'task', $board );
	  foreach ( $enemy_pieces as $x => $enemy_column )
	    foreach ( $enemy_column as $y => $enemy_piece )
	    if ($enemy_piece)
	      {
		$neighbours = self::neighbour( $x, $y );
		$locations[] = function () use ( $neighbours ) {
		  return $neighbours;
		};
	      }
	  break;
	default:
	  throw new BgaVisibleSystemException ( "Wrong dominance location" );
	}
      
      // Dominance must occur in at least one of the locations
      foreach ( $locations as $location )
	{
	  list( $p, $pgrid ) = self::countTask( "playerPiece", "anyrank",
						$location, $board );
	  list( $e, $egrid ) = self::countTask( "enemyPiece", "anyrank", 
						$location, $board );
	  list( $u, $ugrid ) = self::countTask( "playerPiece","upgradedPiece",
						$location, $board );
	  if ($p - $e * $enemy_penalty >= $pieces && $u >= $upgraded )
	    return array( true, $pgrid );
	}

      return array( false, null );
    }

    function diagonalsTask( )
    {
      $locations = array();
      $locations[] = function () {
	$a = array();
	for ( $i = 1 ; $i <= 7 ; $i++ )
	  $a[] = array( 1 + $i, 1 + $i );
	return $a;
      };
      $locations[] = function () {
	$a = array();
	for ( $i = 1 ; $i <= 7 ; $i++ )
	  $a[] = array( 1 + $i, 9 - $i );
	return $a;
      };
      
      $board = self::getBoard();
      $used = self::prepareSelected();
      foreach ( $locations as $location )
	{
	  list( $p, $pgrid ) = self::countTask( "playerPiece", "anyrank",
						$location, $board );
	  list( $u, $ugrid ) = self::countTask( "playerPiece","upgradedPiece",
						$location, $board );
	  if ($p < 4 || $u < 1 )
	    return array( false, null );
	  $used = self::orgrid( $used, $pgrid );
	}
      return array( true, $used );
    }

    function reachable( $from, $to, $by, $board, $max_moves=80 )
    { // Generic reachability algorithm
      $player_id = self::getActivePlayerId();
      for ( $x = 1 ; $x <= 9 ; $x++ )
	for ( $y = 1 ; $y <= 9 ; $y++ )
	  if ( self::onBoard( $x, $y ) )
	    $board[$x][$y]['dist'] = -1;

      $queue = array();
      foreach ( $from as $from_piece )
	{
	  list( $x, $y ) = $from_piece;
	  $board[$x][$y]['dist'] = 0;
	  $board[$x][$y]['from'] = null;
	  $queue[] = $from_piece;
	}

      while ( $queue != array() )
	{
	  list( $x, $y ) = array_shift( $queue );
	  foreach ( self::neighbour( $x, $y ) as $n )
	    {
	      list( $nx, $ny ) = $n;
	      // Prevent from returning to the starting piece (isolation)
	      if ( in_array( $n, $to ) && ! in_array( $n, $from ) )
		{
		  $grid = self::prepareSelected();
		  $grid[$nx][$ny] = true;
		  $grid[$x][$y] = true;
		  while ( $board[$x][$y]['from'] !== null )
		    {
		      list( $x, $y ) = $board[$x][$y]['from'];
		      $grid[$x][$y] = true;
		    }
		  return array( true, $grid );
		}
	      if ( $board[$x][$y]['dist'] < $max_moves
		   && $board[$nx][$ny]['dist'] < 0
		   && self::mycall_user_func_array( $by,
				array( $board[$nx][$ny], $player_id ) ) )
		{
		  $board[$nx][$ny]['dist'] = $board[$x][$y]['dist'] + 1;
		  array_push( $queue, $n );
		  $board[$nx][$ny]['from'] = array( $x, $y );
		}
	    }
	}
      return array( false, null );
    }

    function sideChain( )
    {
      $board = self::getBoard();
      $player_id = self::getActivePlayerId();
      $left = array();
      $right = array();
      $top = array();
      $down = array();
      for ( $i = 3 ; $i <= 7 ; $i++ )
	{
	  if ( $board[1][$i]['player'] == $player_id )
	    $left[] = array( 1, $i );
	  if ( $board[9][$i]['player'] == $player_id )
	    $right[] = array( 9, $i );
	  if ( $board[$i][1]['player'] == $player_id )
	    $top[] = array( $i, 1 );
	  if ( $board[$i][9]['player'] == $player_id )
	    $down[] = array( $i, 9 );
	}
      list( $ok, $path ) = self::reachable( $left, $right, "playerPiece",
					    $board );
      if ( $ok )
	return array( true, $path );
      list( $ok, $path ) = self::reachable( $top, $down, "playerPiece",
					    $board );
      if ( $ok )
	return array( true, $path );
      return array( false, null );
    }

    function cornerChain( )
    {
      $board = self::getBoard();
      $player_id = self::getActivePlayerId();
      $NW = array();
      $NE = array();
      $SW = array();
      $SE = array();
      for ( $i = 1 ; $i <= 3 ; $i++ )
	{
	  if ( $board[$i][4-$i]['player'] == $player_id )
	    $NW[] = array( $i, 4-$i );
	  if ( $board[6+$i][$i]['player'] == $player_id )
	    $NE[] = array( 6+$i, $i );
	  if ( $board[$i][6+$i]['player'] == $player_id )
	    $SW[] = array( $i, 6+$i );
	  if ( $board[6+$i][10-$i]['player'] == $player_id )
	    $SE[] = array( 6+$i, 10-$i );
	}
      list( $ok, $path ) = self::reachable( $NW, $SE, "playerPiece", $board );
      if ( $ok )
	return array( true, $path );
      list( $ok, $path ) = self::reachable( $NE, $SW, "playerPiece", $board );
      if ( $ok )
	return array( true, $path );
      return array( false, null );
    }

    function isolationTask()
    {
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();

      $enemy_set = array();
      $enemy_close = array();
      foreach ( $board as $x => $boardcol )
	foreach ($boardcol as $y => $piece )
	if ( self::enemyPiece( $piece, $player_id ) )
	  {
	    $enemy_set[] = array( $x, $y );      
	    // No need to test neighbours, they aren't isolated
	    $close = self::leap2( $x, $y );
	    list( $cnum, $cgrid ) = self::countTask( "enemyPiece", "anyrank",
			function() use ($close) {return $close;}, $board );
	    if ( $cnum > 0 )
	      $enemy_close[] = array( 'piece' => array( $x, $y ),
				      'close' => $cgrid );
	  }
      foreach ( $enemy_close as $piece )
	{
	  list( $ok, $path ) = self::reachable( array($piece['piece']),
						$enemy_set, "emptySquare",
						$board, 3 );
	  if (! $ok )
	    {
	      list( $x, $y ) = $piece['piece'];
	      $piece['close'][$x][$y] = true;
	      return array( true, $piece['close'] );
	    }
	}
      return array( false, null );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in tashkalarexpansions.action.php)
    */

    function chooseDeck( $color )
    {
      $this->checkAction( 'chooseDeck' );
      $school = $this->decks[array_search( $color, $this->schools_colors )];
      $already_picked = self::getObjectListFromDB( "SELECT player_color FROM player", true );
      if ( ! in_array( $color, $already_picked ) )
	{
	  $player_id = self::getActivePlayerId();
	  self::DbQuery( "UPDATE player SET player_color='$color' WHERE player_id='$player_id'" );

	  $rank = count( array_unique( $already_picked ) );
	  switch ($color) {
	  case '037cb1':
	    self::setStat( $rank, 'northern_choice' );
	    break;
	  case 'dc2515':
	    self::setStat( $rank, 'southern_choice' );
	    break;
	  case 'd6b156':
	    self::setStat( $rank, 'highland_choice' );
	    break;
	  case '8ec459':
	    self::setStat( $rank, 'sylvan_choice' );
	    break;
	  case 'f0f9ff':
	    self::setStat( $rank, 'everfrost_choice' );
		break;
	  case 'f4913c':
		self::setStat( $rank, 'nethervoid_choice' );
		break;
	  case '6a548f':
		self::setStat( $rank, 'etherweave_choice' );
		break;	
	  }

	  self::reloadPlayersBasicInfos();
	  self::myNotifyAllPlayers( "chooseDeck",
		clienttranslate('${player_name} chose the ${school} deck'),
			array( "i18n" => array( 'school' ),
			       "player_id" => $player_id,
			       'player_name' => self::getActivePlayerName(),
			       "school" => $school,
			       "color" => $color ) );

	  // Init the hand
	  $drawn = $this->cards->pickCards(3, $school.'Deck', $player_id );
	  $drawn = array_merge( $drawn,
			$this->cards->pickCards(2, 'LegendsDeck', $player_id));
	  $drawn[] = $this->cards->pickCard( 'FlareDeck', $player_id );
	  self::myNotifyPlayer( $player_id, "retrieveCards", "",
			  array( 'retrieve' => self::enrichCards($drawn) ) );
	  self::notifyPiecesDifferentials();

	  $this->gamestate->nextState( 'chooseDeck' );
	}
      else
	throw new BgaVisibleSystemException ( "This school has already been picked by another player" );
    }

    function clickOrEffects( $card, $effect_number, $effect, $x, $y )
    { // Actually called by other functions to find the suitable effect
      $board = self::getBoard();
      $player_id = self::getActivePlayerId();
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");

      if (func_num_args() == 6)
	$player = func_get_arg(5);
      elseif (func_num_args() == 7)
	{
	  $to_x = func_get_arg(5);
	  $to_y = func_get_arg(6);
	}

      $neffects = intval( substr( $effect, 9, 1) );
      self::IncGameStateValue( "effect_number", 2 * ($neffects-1) );
	      
      // Browse through the clickables to find the effect
      for ( $i = 1 ; $i <= $neffects ; $i++ )
	{
	  $effect = $card['effects'][$effect_number+$i];
	  if ( $effect != 'performWarp' )
	    $clickable = self::selectPiecesForEffect( $effect, $card_x,
		$card_y, $card['effecttargets'][$effect_number/2+$i-1] );
	  if ( $effect == 'movePiece' && isset($clickable[$x][$y])
		   && $clickable[$x][$y][$to_x][$to_y] )
	    {
	      self::doMovePiece( $board, $x, $y, $to_x, $to_y,
		$card['effecttargets'][$effect_number/2+$i-1][2],
				 $card_x, $card_y, $player_id, 
		$card['effecttargets'][$effect_number/2+$i-1][1] );

	      $this->gamestate->nextState( 'effectPlayed' );
	      return;
	    }
	  elseif ( isset($clickable[$x][$y]) && $clickable[$x][$y] )
	    {
	      if ( $effect == 'placePiece' )
		{
		  $rank = self::pieceToRank(
		$card['effecttargets'][$effect_number/2+$i-1][1], $x, $y );
		  $to_place = $card['effecttargets'][$effect_number/2+$i-1][0];
		  if ( $to_place == "playerPiece" && $player != $player_id
		       || $to_place == "enemyPiece" && $player == $player_id )
		    throw new BgaVisibleSystemException ( "You can't place a piece of that color" );
		  else
		    self::placePiece( $x, $y, $player_id, $player, $rank );
		}
	      else
		{
		  self::mycall_user_func_array( $effect,
				array($board, $x, $y, $player_id,
			$card['effecttargets'][$effect_number/2+$i-1] ) );
		}
		
	      $this->gamestate->nextState( 'effectPlayed' );
	      return;
	    }
	}
      throw new BgaVisibleSystemException ( "You can't do that here" );
    }

    function clickEffect( $x, $y )
    { // Basic effects that only require a square
      self::checkAction( 'effectPlayed' );
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      $effect = $card['effects'][$effect_number];

      if ( substr( $effect, 0, 9) == 'orEffects' )
	{
	  self::clickOrEffects( $card, $effect_number, $effect,
				$x, $y );
	  return;
	}

      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      $clickable = self::selectPiecesForEffect( $effect, $card_x, $card_y, $card['effecttargets'][$effect_number/2] );
            
      if ( isset($clickable[$x][$y]) && $clickable[$x][$y] )
	{
	  self::mycall_user_func_array( $effect, array($board, $x, $y, $player_id, $card['effecttargets'][$effect_number/2] ) );
	  $this->gamestate->nextState( 'effectPlayed' );
	}
      else
	//	throw new feException( "Impossible move" );
	throw new BgaVisibleSystemException ( "You can't do that here" );
    }

    function movePiece( $from_x, $from_y, $x, $y )
    {
      self::checkAction( 'effectPlayed' );
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      $effect = $card['effects'][$effect_number];

      if ( substr( $effect, 0, 9) == 'orEffects' )
	{
	  self::clickOrEffects( $card, $effect_number, $effect,
				$from_x, $from_y, $x, $y );
	  return;
	}

      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      $movable = self::selectPiecesForEffect( $effect, $card_x, $card_y, $card['effecttargets'][$effect_number/2] );

      if ($effect == 'movePiece' && isset($movable[$from_x][$from_y])
	  && $movable[$from_x][$from_y][$x][$y] )
	{
	  self::doMovePiece( $board, $from_x, $from_y, $x, $y,
		$card['effecttargets'][$effect_number/2][2],
			     $card_x, $card_y, $player_id,
		$card['effecttargets'][$effect_number/2][1] );

	  $this->gamestate->nextState( 'effectPlayed' );
	}
      else
	throw new BgaVisibleSystemException ( "You can't move that piece here" );
    }

    function placeInitialPieces( $x, $y )
    { // Deathmatch initial config
      if (! self::checkAction( 'placeInitialPieces', false ) )
	self::checkAction( 'placeLastPiece' );
      $player_id = self::getActivePlayerId();
      $players = self::computePiecesNumbers();

      if ( count($players) == 2 )
	{ /* Deathmatch duel */
    	  foreach ($players as $id => $player)
	    if ($id != $player_id)
	      $opponent_id = $id;

	  if ( $y == 5 && ( $x == 3 || $x == 7 ) )
	    {
	      $sql = "UPDATE board SET board_rank=0, board_player='$player_id' WHERE board_x=$x AND board_y=$y";
	      self::DbQuery($sql);      

	      self::myNotifyAllPlayers( "piecePlayed", clienttranslate( '${player_name} placed a ${therank} piece' ), array(
		'i18n' => array( 'therank' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'player' => $player_id,
		'therank' => clienttranslate('common'),
		'rank' => 0,
		'x' => $x,
		'y' => $y
	       ) );

	      /* Automatically place opponent's piece */
	      $x = 10 - $x;
	      $sql = "UPDATE board SET board_rank=0, board_player='$opponent_id' WHERE board_x=$x AND board_y=$y";
	      self::DbQuery($sql);      

	      self::myNotifyAllPlayers( "piecePlayed", clienttranslate( '${player_name} placed a ${therank} piece' ), array(
		'i18n' => array( 'therank' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'player' => $opponent_id,
		'therank' => clienttranslate('common'),
		'rank' => 0,
		'x' => $x,
		'y' => $y
	       ) );

	      $this->gamestate->nextState( 'placeInitialPieces' );
	    }
	  else
	    throw new BgaVisibleSystemException ( "You can't place a piece here" );
	}
      else
	{ /* Deathmatch Melee */
	  if ( $y == 2 && $x != 6 && $x != 7 ||
	       $y == 6 && $x != 7 ||
	       $y == 7 && $x != 2 && $x != 3 && $x != 7 ||
	       $y == 1 || $y >= 3 && $y <= 5 || $y >= 8 )
	    throw new BgaVisibleSystemException ( "You can't place a piece here" );

	  /* Check that pair of squares has not already been used */
	  $board = self::getBoard();
	  $playable = ($board[$x][$y]['player'] === null);
	  foreach (self::orthogonalNeighbour($x, $y) as $n)
	    {
	      list( $nx, $ny ) = $n;
	      $playable = $playable && ($board[$nx][$ny]['player'] === null);
	    }
	  if ( ! $playable )
	    throw new BgaVisibleSystemException ( "You can't place a piece here" );
	  
	  /* Which color was being placed */
	  $table = $this->getNextPlayerTable();
	  $player = $table[$table[$table[0]]];
	  $i = 0;
	  while( $players[$player]['pieces'] > 0 )
	    {
	      $player = $table[$player];
	      $i++;
	    }
	  
	  $sql = "UPDATE board SET board_rank=0, board_player='$player' WHERE board_x=$x AND board_y=$y";
	  self::DbQuery($sql);      

	  self::myNotifyAllPlayers( "piecePlayed", clienttranslate( '${player_name} placed a ${therank} piece' ), array(
		'i18n' => array( 'therank' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'player' => $player,
		'therank' => clienttranslate('common'),
		'rank' => 0,
		'x' => $x,
		'y' => $y
	       ) );
    
	  if ( $i < 2 )
	      $this->gamestate->nextState( 'placeInitialPieces' );
	  else
	      $this->gamestate->nextState( 'placeLastPiece' );	    
	}
    }

    function playPiece( $x, $y )
    { // Standard "place piece" action (not the effect)
      self::checkAction( 'playPiece' );
      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      
      /***** For testing: upgrade on click *****
      if ($board[$x][$y]['player'] === null)
	$rank = 0;
      else
	$rank = ($board[$x][$y]['rank'] + 1) % 3;
	***** For testing: upgrade on click *****/

      if ($board[$x][$y]['player'] === null)
	{
	  self::setGameStateValue("piece_x", $x);
	  self::setGameStateValue("piece_y", $y);

	  $pieces_left = self::getUniqueValueFromDb( "SELECT player_pieces_left FROM player WHERE player_id=$player_id" );
	  if ( $pieces_left == 0 )
	    {
	      self::setGameStateValue( "piece_rank", 0);
	      self::setGameStateValue( "card_played", -1 );
	      self::memorizeUsed( array() );
	      self::saveGameState( "pickPiece" );
	      $this->gamestate->nextState( 'pieceShortage' );
	    }	
	  else
	    {
	      $sql = "UPDATE board SET board_rank=0, board_player='$player_id' WHERE board_x=$x AND board_y=$y";
	      self::DbQuery($sql);

	      self::IncGameStateValue( 'turn_placed', 1 );

	      // $therank = self::rankName($rank);
	      // Notify all players about the piece played
	      self::myNotifyAllPlayers( "piecePlayed", clienttranslate( '${player_name} placed a ${therank} piece' ), array(
			'i18n' => array( 'therank' ),
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'player' => $player_id,
			'therank' => clienttranslate('common'),
			'rank' => 0,
			'x' => $x,
			'y' => $y ) );
        
	      $this->gamestate->nextState( 'playPiece' );
	    }
	}
      else
	throw new BgaVisibleSystemException ( "You can't place a piece here" );	
    }

    function pickPiece( $x, $y )
    { // Called in case of piece shortage
      if (! self::checkAction( 'playPiece', false ) )
	self::checkAction( 'playCard' );

      // Check that it applies
      $player_id = self::getActivePlayerId();
      $rank = self::getGameStateValue( "piece_rank" );
      if ($rank < 2)
	$left = self::getUniqueValueFromDb( "SELECT player_pieces_left FROM player WHERE player_id=$player_id" );
      else
	$left = self::getUniqueValueFromDb( "SELECT player_legends_left FROM player WHERE player_id=$player_id" );

      $piece = self::getObjectFromDb( "SELECT board_player player, board_rank rank, board_used used FROM board WHERE board_x=$x AND board_y=$y" );
      if ( $left == 0 && ( $rank < 2 || $piece['rank'] == $rank )
	   && $piece['player'] == $player_id && $piece['used'] == 0 )
	{
	  $sql = "UPDATE board SET board_player=NULL WHERE board_x=$x AND board_y=$y";
	  self::DbQuery($sql);
	  self::notifyPiecesDifferentials();

	  if ( self::getGameStateValue( "gateway_x" ) == $x
	       && self::getGameStateValue( "gateway_y" ) == $y) {
          // self::MyNotifyAllPlayers( "gatewayChanged", '', array(
          // 	        'player_id' => $player_id,
          // 		'from_x' => $x,
          // 		'from_y' => $y,
          // 		'x' => -1,
          // 		'y' => -1 ) );
          self::setGameStateValue( "gateway_x", -1 );
          self::setGameStateValue( "gateway_y", -1 );
          $gateway = true;
	  }
      else
          $gateway = false;
	  
	  self::myNotifyAllPlayers( "piecePicked", clienttranslate( '${player_name} picked up a piece' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'x' => $x,
            'y' => $y,
            'rank' => $rank,
            'gateway' => $gateway,
            'game_form' => self::getGameStateValue( 'game_form' )
           ) );
	  
	  if ( $rank == 2  && self::getGameStateValue( "game_form" ) == 1 )
	    self::DbQuery( "UPDATE player SET player_score=player_score-1 WHERE player_id=$player_id" );
	  
	  if ( self::getGameStateValue( "card_played" ) == -1 )
	    self::playPiece( self::getGameStateValue( "piece_x" ),
			     self::getGameStateValue( "piece_y" ) );
	  else
	    self::playCard( self::getGameStateValue( "card_x" ),
			    self::getGameStateValue( "card_y" ),
			    self::getGameStateValue( "deck_played" ),
			    self::getGameStateValue( "card_played" ) );
	  /* Control is returned to the playPiece or playCard action
	     initiated, with saved values. */
	}
      else
	throw new BgaVisibleSystemException ( "You can't pick up a piece here" );
    }

    /* If multiple patterns overlap, the first piece summoned
       by Imperial Summoner may constrain the second one. */
    function summonerCleanup( $x, $y, $cx, $cy )
    {
      $used = self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_used used FROM board", true );
      $unmark = array();

      $dx = $x - $cx;
      $dy = $y - $cy;
      if ( abs($dx) < 2 && abs($dy) < 2 )
	return;
      
      if ( $dx == 0 )
	{
        if ( self::onBoard($cx+$dy/2, $cy) && ! $used[$cx+$dy/2][$cy] )
	    $unmark[] = array( $cx-$dy, $cy-$dy );
	  if ( self::onBoard($cx-$dy/2, $cy) && ! $used[$cx-$dy/2][$cy] )
	    $unmark[] = array( $cx+$dy, $cy-$dy );
	  if ( self::onBoard($cx+$dy/2, $cy) && self::onBoard($cx-$dy/2, $cy)
           && ! $used[$cx+$dy/2][$cy] && ! $used[$cx-$dy/2][$cy] )
	    $unmark[] = array( $cx, $cy-$dy );
	  if ( self::onBoard($cx+$dy/2, $cy) && self::onBoard($cx, $cy-$dy/2)
           && ! $used[$cx+$dy/2][$cy] && ! $used[$cx][$cy-$dy/2] )
	    $unmark[] = array( $cx-$dy, $cy );
	  if ( self::onBoard($cx-$dy/2, $cy) && self::onBoard($cx, $cy-$dy/2)
           && ! $used[$cx-$dy/2][$cy] && ! $used[$cx][$cy-$dy/2] )
	    $unmark[] = array( $cx+$dy, $cy );	  
	}
      elseif ( $dy == 0 )
	{
	  if ( self::onBoard($cx, $cy+$dx/2) && ! $used[$cx][$cy+$dx/2] )
	    $unmark[] = array( $cx-$dx, $cy-$dx );
	  if ( self::onBoard($cx, $cy-$dx/2) && ! $used[$cx][$cy-$dx/2] )
	    $unmark[] = array( $cx-$dx, $cy+$dx );
	  if ( self::onBoard($cx, $cy+$dx/2) && self::onBoard($cx, $cy-$dx/2)
           && ! $used[$cx][$cy+$dx/2] && ! $used[$cx][$cy-$dx/2] )
	    $unmark[] = array( $cx-$dx, $cy );
	  if ( self::onBoard($cx, $cy+$dx/2) && self::onBoard($cx-$dx/2, $cy)
           && ! $used[$cx][$cy+$dx/2] && ! $used[$cx-$dx/2][$cy] )
	    $unmark[] = array( $cx, $cy-$dx );
	  if ( self::onBoard($cx, $cy-$dx/2) && self::onBoard($cx-$dx/2, $cy)
           && ! $used[$cx][$cy-$dx/2] && ! $used[$cx-$dx/2][$cy] )
	    $unmark[] = array( $cx, $cy+$dx );	  
	}
      else
	{
	  $unmark[] = array( $cx-$dx, $cy-$dy );
	  if ( self::onBoard($cx, $cy-$dy/2) && ! $used[$cx][$cy-$dy/2] )
	    {
	      $unmark[] = array( $cx-$dx, $cy );
	      $unmark[] = array( $cx-$dx, $cy+$dy );
	    }
	  if ( self::onBoard($cx-$dx/2, $cy) && ! $used[$cx-$dx/2][$cy] )
	    {
	      $unmark[] = array( $cx, $cy-$dy );
	      $unmark[] = array( $cx+$dx, $cy-$dy );
	    }
	}

      if ( count($unmark)>0 )
	{
	  $sql = "UPDATE board SET board_marked=0 WHERE ";
	  foreach ( $unmark as $um )
	    {
	      $sql .= "(board_x=".$um[0]." AND board_y=".$um[1].") OR ";
	    }
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	} 
    }

    function pieceToRank( $piece, $x=0, $y=0 )
    {
      /* There should be a better way */
      switch( $piece )
	{
	case 'heroicPiece':
	  return 1;
	case 'legendaryPiece':
	  return 2;
	case 'commonPiece':
	  return 0;
	case 'markedRank':
	  return self::getUniqueValueFromDb( "SELECT board_marked FROM board WHERE board_x=$x AND board_y=$y" ) - 1;
	default:
	  throw new BgaVisibleSystemException ( "Wrong piece rank" );
	}
    }

    function clickPlace( $x, $y, $player )
    { // Require the player too, as one can sometimes place enemy pieces
      self::checkAction( 'effectPlayed' );
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");

      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      $effect = $card['effects'][$effect_number];

      if ( substr( $effect, 0, 9) == 'orEffects' )
	{
	  self::clickOrEffects( $card, $effect_number, $effect,
				$x, $y, $player );
	  return;
	}

      $player_id = self::getActivePlayerId();
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      $clickable = self::selectPiecesForEffect( $effect, $card_x, $card_y,
				$card['effecttargets'][$effect_number/2] );
            
      if ( $effect == 'placePiece' && (
		isset($clickable[$x][$y]) && $clickable[$x][$y]
	 || isset($clickable[$player][$x][$y]) && $clickable[$player][$x][$y]
				       ) )
	{
	  $rank = self::pieceToRank(
			$card['effecttargets'][$effect_number/2][1], $x, $y );
	  $to_place = $card['effecttargets'][$effect_number/2][0];
	  // Useless, computed per player in selectPiecesForEffect
	  /* $neighbour = true; */
	  /* if ( $deck == 'Nethervoid' && $card_id == 17 ) */
	  /*   { /\* Possessed Summoner *\/ */
	  /*     $neighbour = false; */
	  /*     $board = self::getBoard(); */
	  /*     foreach (self::neighbour( $x, $y ) as $n) */
	  /* 	{ */
	  /* 	  list( $nx, $ny ) = $n; */
	  /* 	  if ($board[$nx][$ny]['player'] == $player) */
	  /* 	    $neighbour = true; */
	  /* 	} */
	  /*   } */
	  if ( $to_place == "playerPiece" && $player != $player_id
	       || $to_place == "enemyPiece" && $player == $player_id )
	    /* || !$neighbour ) */
	    throw new BgaVisibleSystemException ( "You can't place a piece of that color" );
	  else
	    self::placePiece( $x, $y, $player_id, $player, $rank );

	  if ( ($deck == 'Northern' || $deck == 'Southern') && $card_id == 7 )
	    self::summonerCleanup( $x, $y, $card_x, $card_y );

	  $this->gamestate->nextState( 'effectPlayed' );
	}
      else
	throw new BgaVisibleSystemException ( "You can't place a piece here" );
    }

    function skip()
    {
      if ( !self::checkAction( 'skip', false ) )
	self::checkAction( 'chooseColor' );
      self::IncGameStateValue( 'has_skipped', 1 );
      $deck = self::getGameStateValue("deck_played");
      if ( $deck != -2 ) // is used for turn end too, nothing to do then
	{
	  $card_id = self::getGameStateValue("card_played");
	  $effect_number = self::getGameStateValue("effect_number");
	  if ( $deck != -1 )
	    {
	      $deck = $this->decks[$deck];
	      $card = $this->card_contents[$deck][$card_id];
	    }
	  else
	    {
	      $card =
		$this->flares[$card_id][self::getGameStateValue("flare_rank")];
	    }
	  $effect = $card['effects'][$effect_number];
	  
	  if ( substr( $effect, 0, 9) == 'orEffects' )
	    {
	      $neffects = intval( substr( $effect, 9, 1) );
	      self::IncGameStateValue( "effect_number", 2 * ($neffects-1) );
	    }
	  $this->gamestate->nextState( 'skip' );
	}
      else
	{ // ... except maybe choose in which color score a Melee pair
	  $player_id = self::getActivePlayerId();
	  $common_destroyed = self::getCollectionFromDb("SELECT score_against, score_common FROM score WHERE score_player_id=$player_id", true);
	  $leftovers = array();
	  foreach ( $common_destroyed as $against => $destroyed )
	    {
	      if ( $destroyed % 2 == 1 )
		$leftovers[] = $against;
	    }
	  if ( count($leftovers) > 1 )
	    $this->gamestate->nextState( 'chooseColor' );
	  else
	    $this->gamestate->nextState( 'skip' );
	}
    }

    function cancel()
    { // Available when offered to pick up a piece (in case of shortage)
      self::checkAction( 'cancelPick' );
      self::setGameStateValue( "deck_played", -2 );
      self::setGameStateValue( "card_played", -1 );
      self::setGameStateValue( "last_impro", 0 );
      self::saveGameState("actionChoice");
      $this->gamestate->nextState( 'cancelPick' );
    }

    function gainActionButton()
    { // Unicorn
      self::checkAction( 'effectPlayed' );
      $deck = $this->decks[self::getGameStateValue("deck_played")];
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      $effect = $this->card_contents[$deck][$card_id]['effects'][$effect_number];

      if ( substr( $effect, 0, 9) == 'orEffects' )
	{
	  $neffects = intval( substr( $effect, 9, 1) );
	  self::IncGameStateValue( "effect_number", 2 * ($neffects-1) );
	      
	  for ( $i = 1 ; $i <= $neffects ; $i++ )
	    {
	      if ( $this->card_contents[$deck][$card_id]['effects'][$effect_number+$i] == 'gainAction')
		{
		  self::gainAction( self::getActivePlayerId() );
		  $this->gamestate->nextState( 'effectPlayed' );
		  return;
		}
	    }
	}
      throw new BgaVisibleSystemException ( "You can't gain an action now" );
    }

    function memorizeUsed( $used_set )
    {
      $sql = "UPDATE board SET board_used=0";
      self::DbQuery($sql);
      if ( count( $used_set ) > 0 )
	{
	  $sql = "UPDATE board SET board_used=1 WHERE ";
	  foreach ( $used_set as $used )
	    {
	      $sql .= "(board_x=".$used[0]." AND board_y=".$used[1].") OR ";
	    }
	  $sql = substr( $sql, 0, -4 );
	  self::DbQuery($sql);
	}
    }

    function playCard( $x, $y, $deck_id, $card_id )
    {
	  self::checkAction( 'playCard' );
	  $player_id = self::getActivePlayerId();
	  $summoning_color = self::getGameStateValue( "summoning_color" );
	  if ($summoning_color != 0){
		$pattern_color = $summoning_color;
	  } else {
		$pattern_color = $player_id;
	  }
      $board = self::getBoard();
      $deck = $this->decks[$deck_id];
      $card = $this->card_contents[$deck][$card_id];
      $impro_choice = self::getGameStateValue( "last_impro" );

      $thecard = self::getUniqueCardOfType( $this->cards, $deck, $card_id );
      if ( $thecard['location'] != 'hand'
	   || $thecard['location_arg'] != $player_id )
	throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
      else
	{
	  self::setGameStateValue( "deck_played", $deck_id );
	  self::setGameStateValue( "card_played", $card_id );
	  self::DbQuery( "UPDATE player SET player_last_deck=$deck_id, player_last_card=$card_id WHERE player_id=$player_id" );
	  self::setGameStateValue( "card_x", $x );
	  self::setGameStateValue( "card_y", $y );
	  if ( $deck_id == 6 && $card_id == self::getGameStateValue( "pending_being" ) )
	    self::setGameStateValue( "pending_being", -1 );

	  $found = self::foundPatternWrapper( $x, $y, $card, $board, $pattern_color );
	  if ($impro_choice == 0)
	    {
	      $impros = self::getObjectListFromDb( "SELECT score_against FROM score WHERE impro=1 AND score_player_id=$player_id", true);
	      foreach ( $impros as $i => $impro )
		if ( count($found['used'][$impro]) == 0
		     || $impro == $pattern_color )
		  unset($impros[$i]);
	      $impros = array_values($impros);
	    }
	  else
	    $impros = array( 0 => $impro_choice );

	  if ( $board[$x][$y]['player'] == null
	       || $board[$x][$y]['rank'] <= $card['rank'] )
	    {
	      if ( count($found['used'][$pattern_color]) > 0
		   && ( count($impros) == 0
			|| !isset($card['impro'])
			|| $impro_choice == $pattern_color ) )
		{
		  // Base case : you can use you own pieces
		  // and there is no point in using others
		  // or it's even impossible
		  $used = $found['used'][$pattern_color];
		  $marked = $found['marked'][$pattern_color];
		  self::setGameStateValue( "last_impro", $player_id );
		}
	      elseif ( count($impros) == 1
		       && count($found['used'][$pattern_color]) == 0
		       || $impro_choice != 0 )
		{ // Only an impro is possible, and in only one color
		  $players = self::loadPlayersBasicInfos();
		  $used = $found['used'][$impros[0]];
		  $marked = $found['marked'][$impros[0]];
		  self::DbQuery( "UPDATE score SET impro=0 WHERE score_player_id=$player_id AND score_against=$impros[0]" );
		  self::myNotifyAllPlayers( "improSummon", clienttranslate( '${player_name} used ${impro_name}\'s piece for improvised summoning' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'impro_id' => $impros[0],
				'impro_name' => $players[$impros[0]]['player_name'] ) );
		  self::setGameStateValue( "last_impro", $impros[0] );
		  self::incStat( 1, 'improvised_summonings', $player_id );
		}
	      elseif ( count($impros) > 1
		       || count($impros) == 1
		          && count($found['used'][$pattern_color]) > 0 )
		{
		  self::saveGameState( 'chooseColorImpro' );
		  $this->gamestate->nextState( 'chooseColorImpro' );
		  return;
		}
	      else
		throw new BgaUserException ( self::_("You can't summon that being here") );

	      ////// Now we do the actual summoning //////

	      // Check if shortage applies
	      if ($card['rank'] < 2)
		$left = self::getUniqueValueFromDb( "SELECT player_pieces_left FROM player WHERE player_id=$player_id" );
	      else
		$left = self::getUniqueValueFromDb( "SELECT player_legends_left FROM player WHERE player_id=$player_id" );
	      if ( $left == 0
		   && ! ( $board[$x][$y]['player'] == $player_id
			  && ( $card['rank'] < 2
			       || $board[$x][$y]['rank'] == $card['rank'] ) ) )
		{ // Shortage
		  self::setGameStateValue( "piece_rank", $card['rank'] );
		  // exclude pieces common to every pattern
		  if ( count($used) > 1 ) {
		    $sused = array();
		    foreach ($used as $u)
		      $sused[] = array_map( "serialize", $u);
		    $inter = array_map( "unserialize", call_user_func_array( "array_intersect", $sused ) );
		  }
		  else
		    $inter = $used[0];
		  self::memorizeUsed( $inter );
		  self::saveGameState( "pickPiece" );
		  $this->gamestate->nextState( 'pieceShortage' );
		}
	      else
		{ // Actually play the card
		  $sql = "UPDATE board SET board_rank=".$card['rank'].", board_player='$player_id' WHERE board_x=$x AND board_y=$y";
		  self::DbQuery($sql);

		  self::IncGameStateValue( "turn_summoned_beings", 1 );
		  if ( $card['rank'] == 0 )
		    self::IncGameStateValue( "turn_summoned_common", 1 );
		  $point_gained = "";
		  if ( $card['rank'] == 2 )
		    {
		      self::IncGameStateValue( "turn_summoned_legends", 1 );
		      if ( self::getPlayersNumber() == 2 )
			{
			  self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$player_id" );
			  if (self::getGameStateValue( 'game_form' ) == 2)
			    $point_gained = clienttranslate(" (1 point gained)");
			}
		    }
		  if ( self::redSquare( $x, $y ) )
		    self::IncGameStateValue( "turn_summoned_red", 1 );
		  if ( self::greenSquare( $x, $y ) )
		    self::IncGameStateValue( "turn_summoned_green", 1 );
		  $and_destroyed = "";
		  $destroyed_args = array();
		  if ( $board[$x][$y]['player'] !== null )
		    {
		      self::setGameStateValue( "oversummoned",
					       $board[$x][$y]['player'] );
		      self::updateDestroyed( $board, $x, $y, $player_id );
		      $and_destroyed = clienttranslate(' and destroyed a ${rank_string} piece');
		      $destroyed_args = array(
					      "i18n" => array( "rank_string"),
					      "rank_string" => self::rankName($board[$x][$y]['rank']) );
		    }
		  
			// Note - do this after the Oversummoning to ensure we don't accidently oversummon our gateway and end up without one
			$becoming_gateway = "";
		  if ( ($deck == 'Nethervoid') &&
		       ((self::getGameStateValue( 'gateway_x' ) == -1) || 
			(self::getGameStateValue( 'gateway_x' ) == 0) ||
 				(($board[self::getGameStateValue( 'gateway_x' )][self::getGameStateValue( 'gateway_y' )]['rank'] == 0) 
					&& ($card['rank'] == 1))) )
			{
				// * SCC TODO log good enough?
				$becoming_gateway = clienttranslate(' (becoming the Gateway) ');
				self::setGameStateValue( 'gateway_x', $x );
				self::setGameStateValue( 'gateway_y', $y );
			}	

		  $sql = "UPDATE board SET board_marked=0";
		  self::DbQuery($sql);
		  if (count($marked) > 0)
		    {
		      $sql = "UPDATE board SET board_marked=1 WHERE ";
		      foreach ( $marked as $md )
			{
			  $sql .= "(board_x=".$md[0]." AND board_y=".$md[1].") OR ";
			}
		      $sql = substr( $sql, 0, -4 );
		      self::DbQuery($sql);
		    }

		  $used = call_user_func_array( "array_merge", $used );
		  // Imperial Summoner/Gate of Oblivion for eventual cleanup
		  if ( ( ($deck == 'Northern' || $deck == 'Southern')
			 && $card_id == 7 )
		       || $deck == 'Etherweave' && $card_id == 6 )
		    self::memorizeUsed( $used );

		  self::setGameStateValue( "effect_number", -2 );

		  $hoffset = 125 * $card_id / 2 + 2;
		  $icon = '<div id="last_card_'.$deck_id.'_'.$card_id.'" class="last_card_icon log_inlined '.$deck.'" style="background-position:-'.$hoffset.'px -12px"></div>';

		  if ( isset( $card['frozentext'] ) )
		    $frozentext = $card['frozentext'];
		  else
		    $frozentext = null;
		  if ( isset( $card['warptext'] ) )
		    $warptext = $card['warptext'];
		  else
		    $warptext = null;
		  
		  // Unset weird summoning effects
		  self::setGameStateValue( "summoning_color", 0 );
		  self::setGameStateValue( "void_summoning", 0 );

		  // Notify all players about the piece played
		  self::myNotifyAllPlayers( "cardPlayed", clienttranslate( '${icon}${player_name} summoned a ${being}${becoming_gateway}${point_gained}${and_destroyed}' ), array(
				'i18n' => array( 'being',
						 'point_gained',
						 'becoming_gateway' ),
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'becoming_gateway' => $becoming_gateway,
				'point_gained' => $point_gained,
				'and_destroyed' => array(
						 'log' => $and_destroyed,
						 'args' => $destroyed_args ),
				'game_form' => self::getGameStateValue( 'game_form' ),
				'rank_destroyed' => $board[$x][$y]['rank'],
				'color_destroyed' => $board[$x][$y]['player'],
				'additional_destroyed' => self::getGameStateValue( "additional_destroyed" ),
				'deck_id' => $deck_id,
				'card_id' => $card_id,
				'being' => $card['name'],
				'icon' => $icon,
				'text' => $card['text'],
				'frozentext' => $frozentext,
				'warptext' => $warptext,
				'rank' => $card['rank'],
				'used' => $used,
				'x' => $x,
				'y' => $y ) );
		  if ($becoming_gateway != '')
		    {
		      self::MyNotifyAllPlayers("gatewayChanged", '',
				array( 'player_id' => $player_id,
				       'x' => $x,
				       'y' => $y ) );
		    }

		  if ( $card['rank'] == 2 && self::getPlayersNumber() > 2 )
		    {
		      self::saveGameState("chooseColorLegend");
		      $this->gamestate->nextState( 'chooseColorLegend' );
		    }
		  else
		    $this->gamestate->nextState( 'playCard' );
		}
	    }
	  else
	    throw new BgaUserException ( self::_("You can't summon that being here") );
	}
    }
    
    function discardCard( $discarded_deck_id, $discarded_id,
			  $returned_decks_ids, $returned_ids )
    { // Many "cheating" cases here...
      self::checkAction( 'discardCard' );
      if ( self::getGameStateValue( "turn_discarded" ) > 0 )
	throw new BgaVisibleSystemException ( 'You can\'t do more than one "discard" action per turn' );

      $player_id = self::getActivePlayerId();

      $type = $this->decks[$discarded_deck_id];
	  $discarded = self::getUniqueCardOfType( $this->cards, $type, $discarded_id );
      if ( $discarded['location'] != 'hand'
	   || $discarded['location_arg'] != $player_id )
	throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
      elseif ( $discarded['type'] == "Flare"
	       || $discarded['type'] == "Legend" )
	throw new BgaVisibleSystemException ( "You can only discard a card from your deck" );
      elseif ( $discarded_deck_id == 6
	       && $discarded_id == self::getGameStateValue( "pending_being" ) )
	throw new BgaVisibleSystemException ( "You cannot discard your pending being" );
      else
	$this->cards->insertCardOnExtremePosition( $discarded['id'], "discard_buffer", true );

      // Cards are discarded and returned in a buffer to ease takebacks

      foreach ( $returned_decks_ids as $key => $deck_id )
	{
	  $type = $this->decks[$deck_id];
	  $returned = self::getUniqueCardOfType( $this->cards, $type, $returned_ids[$key] );
	  if ( $returned['location'] != 'hand'
	       || $returned['location_arg'] != $player_id )
	    throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
	  elseif ( $deck_id == 6
	 && $returned_ids[$key] == self::getGameStateValue( "pending_being" ) )
	    throw new BgaVisibleSystemException ( "You cannot get rid of your pending being" );
	  else
	  {
	    $this->cards->InsertCardOnExtremePosition(
	        $returned['id'], "return_buffer", true );
	    if ( $type == "Flare" )
	      self::setGameStateValue( "flare_discarded", 1 );
        self::myNotifyPlayer( $player_id, "discardCard", "",
                  array( "card_id" => $deck_id."_".$returned_ids[$key],
                         "to" => 'deck' ) );
	  }
	}

      self::setGameStateValue( "turn_discarded", 1 );

      self::myNotifyAllPlayers( "cardDiscarded", clienttranslate( '${player_name} discarded a card and returned ${num} other(s) to their decks' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
	    'num' => count( $returned_decks_ids )
			    ) );
      $discarded = self::enrichCards( array( $discarded['id'] => $discarded ) );
      $discarded = array_pop($discarded);
      self::myNotifyPlayer( $player_id, "discardCard", "",
                  array( "card_id" => $discarded_deck_id."_".$discarded_id,
                         "to" => 'discard',
                         "card" => $discarded ) );
      $this->gamestate->nextState( 'discardCard' );
    }

    function computeAndMemorizeActivable( $flarenum, $player_id, $against )
    { // Check which flare criteria apply and memorize them
      $players = self::computePiecesNumbers();
      $player = $players[$player_id];
      $opponent = $players[$against];

      $activable = array( 'upgraded' => 0, 'pieces' => 0 );
      foreach ( array(0 => 'upgraded', 1 => 'pieces') as $num => $rank )
	{
	  if ( $opponent[$rank] - $player[$rank]
	       >= $this->flares[$flarenum][$num]['more'] )
	    $activable[$rank] = 1;
	  self::setGameStateValue( "flare_".$rank, $activable[$rank] );
	}
      return $activable;
    }

    function doPlayFlare( $flarenum, $player_id, $against )
    {
      $activable = self::computeAndMemorizeActivable( $flarenum, $player_id, $against );
      self::setGameStateValue( "card_played", $flarenum );
      self::setGameStateValue( "deck_played", -1 );
      self::DbQuery( "UPDATE player SET player_last_deck=-1, player_last_card=$flarenum WHERE player_id=$player_id" );
      $players = self::loadPlayersBasicInfos();;
      $againstwho='';

      // Deathmatch scoring
      $game_form = self::getGameStateValue( 'game_form' );
      if ( $game_form == 2 )
	{
	  if ( count($players) == 2 )
	    { // Duel
	      self::DbQuery( "UPDATE player SET player_score=player_score+1 WHERE player_id=$against" );
	    }
	  else
	    { // Melee
	      $againstwho = array(
		'log' => clienttranslate( ' against ${opponent}' ),
		'args' => array( "i18n" => array("opponent"),
				 "opponent" =>
				 $players[$against]['player_name']) );

	      self::DbQuery( "UPDATE score SET score_value=score_value+1 WHERE score_player_id=$against AND score_against=$player_id" );
	      self::myNotifyAllPlayers( 'updateScore', '', 
					array("player_id" => $against,
					      "against" => $player_id,
					      "result" => self::getUniqueValueFromDb( "SELECT score_value FROM score WHERE score_player_id=$against AND score_against=$player_id" ) ) );

	      $min = self::getUniqueValueFromDb( "SELECT MIN(score_value) FROM score WHERE score_player_id=$against" );
	      if ( $min > self::getUniqueValueFromDb( "SELECT player_score FROM player WHERE player_id=$against" ) )
		{
		  self::DbQuery("UPDATE player SET player_score=$min WHERE player_id=$against");
		  self::myNotifyAllPlayers( 'updateScore', '', 
					    array("player_id" => $against,
						  "diff" => 1 ) );
		}
	    }
	}
      
      $hoffset = 125 * $flarenum / 2 + 2;
      $icon = '<div id="last_card_flare_'.$flarenum.'" class="last_card_icon log_inlined Flare" style="background-position:-'.$hoffset.'px -6px"></div>';

      if ( $activable['upgraded'] && $activable['pieces'] )
	$parts = clienttranslate( " (both effects)" );
      elseif ( $activable['upgraded'] )
	$parts = clienttranslate( " (upper effect)" );
      else
	$parts = clienttranslate( " (lower effect)" );		

      self::myNotifyAllPlayers( "flarePlayed", clienttranslate( '${icon}${player_name} invoked a flare${againstwho}${parts}' ), array(
				'i18n' => array( 'againstwho', 'parts' ),
				'player_id' => $player_id,
				'player_name' => 
				$players[$player_id]['player_name'],
				'againstwho' => $againstwho,
				'icon' => $icon,
				'flarenum' => $flarenum,
				'upgraded' =>
				$this->flares[$flarenum][0]['text'],
				'pieces' => 
				$this->flares[$flarenum][1]['text'],
				'parts' => $parts,
				'game_form' => $game_form,
				'against' => $against ) );
    }

    function playFlare( )
    {
      self::checkAction( 'playFlare' );
      $player_id = self::getActivePlayerId();

      $against = self::argChooseColorFlare(); // checks $flarenum != -1
      $against = $against['activable'];
      
      if ( count($against) == 1 )
	{
	  $against = $against[0];
	  self::doPlayFlare( self::flareNum( $player_id ),
			     $player_id, $against );
	  $this->gamestate->nextState( 'playFlare' );
	}
      elseif ( count($against) > 1 )
	{
	  self::saveGameState("chooseColorFlare");
	  $this->gamestate->nextState( 'chooseColorFlare' );
	}
      else
	throw new BgaUserException ( self::_("You don't meet either flare criterion") );
    }

    function claimTask( $task_id )
    {
      self::checkAction( 'chooseTask' );
      $task = self::getUniqueCardOfType( $this->cards, "task", $task_id );
      list( $completed, $used ) = self::mycall_user_func_array(
				$this->tasks[$task['type_arg']]['criteria'], 
				$this->tasks[$task['type_arg']]['critargs'] );
      if ( $task['location'] == 'current_tasks' && $completed )
	{
	  $player_id = self::getActivePlayerId();
	  if ( $this->tasks[$task_id]['type'] == 'summoning'
	       || $this->tasks[$task_id]['type'] == 'destruction' )
	    $used = sprintf( clienttranslate( '%s claimed %s' ),
			     self::getActivePlayerName(),
			     $this->tasks[$task_id]['name'] );
	  // Claim
	  $this->cards->moveCard( $task['id'], 'claimed', $player_id );
	  $points = $this->tasks[$task_id]['points'];
	  self::DbQuery( "UPDATE player SET player_score=player_score+$points WHERE player_id=$player_id" );
	  self::incStat( 1, 'tasks_claimed', $player_id );

	  // Make next task available
	  foreach ($this->cards->getCardsInLocation('next_task') as $next)
	    $this->cards->moveCard( $next['id'], 'current_tasks' );

	  // Draw next task
	  $next = $this->cards->pickCardForLocation( 'TasksDeck', 'next_task' );
	  $current = $this->cards->getCardsInLocation( 'current_tasks' );
	  $types = array( "destruction" => 0, "colored" => 0,
			  "summoning" => 0, "pattern" => 0, "enemy" => 0 );
	  foreach ( $current as $card )
	    $types[ $this->tasks[$card['type_arg']]['type'] ]++;
	  $conflicting_type = array_search( 2, $types );
	  while ( $this->tasks[$next['type_arg']]['type']
		  == $conflicting_type )
	    {
	      $this->cards->InsertCardOnExtremePosition( $next['id'], "TasksDeck", false );
	      $next = $this->cards->pickCardForLocation( "TasksDeck",
							   "next_task" );
	    }	
	  
	  if ( $next !== null )
	    {
	      $new_task = self::enrichTasks( array($next) );
	      $new_task = $new_task[0];
	    }
	  else
	    $new_task = -1;

	  self::myNotifyAllPlayers( "taskClaimed", clienttranslate( '${player_name} claimed ${task_name}' ), array(
				'i18n' => array( 'task_name' ),
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'task_id' => $task_id,
				'task_name' => $this->tasks[$task_id]['name'],
				'points' => $points,
				'used' => $used,
				'new_task' => $new_task ) );	      
	  $this->gamestate->nextState( 'chooseTask' );
	  return;
	}
      throw new BgaUserException ( self::_("You can't claim that task") );
    }

    /*** DEPRECATED, superseded by undoStep and redoStep
    function takeBack( )
    {
      self::checkAction( 'takeBack' );

      // Recreate the situation at the beginning of the turn
      self::DbQuery( "UPDATE board SET board_player=board_saved_player, board_rank=board_saved_rank" );
      self::setGameStateValue( "remaining_actions", 2 );
      $player_id = self::getActivePlayerId();
      $players = self::notifyPiecesDifferentials();
      self::DbQuery( "UPDATE player SET player_score=player_saved_score" );
      $scores = self::getCollectionFromDb( "SELECT player_id id, player_score score FROM player", true );

      // Deal back the cards as they were
      $retrieve = self::getEnrichedCardsInLocation( $this->cards,
						    "discard_buffer" );
      $retrieve = array_merge( $retrieve,
		self::getEnrichedCardsInLocation( $this->cards,
						  "return_buffer" ) );

      $this->cards->moveAllCardsInLocation( "discard_buffer", "hand", null, $player_id );
      $this->cards->moveAllCardsInLocation( "return_buffer", "hand", null, $player_id );
      if ( self::getGameStateValue( "card_put_on_top" ) == 1 )
	{
	  $card = $this->cards->getCardOnTop( 'SylvanDeck' );
	  $this->cards->moveCard( $card['id'], 'discard' );
	}
      else if ( self::getGameStateValue( "card_put_on_top" ) == -1 )
	{
	  $card = $this->cards->getCardOnTop( 'SylvanDeck' );
	  $this->cards->moveCard( $card['id'], 'hand', $player_id );
	  $retrieve = array_merge( $retrieve,
				   self::enrichCards( array($card) ) );
	}	

      $board = self::getObjectListFromDb( "SELECT board_x x, board_y y, board_player player, board_rank rank FROM board WHERE board_player IS NOT NULL" );

      self::myNotifyPlayer( $player_id, "retrieveCards", "",
			  array( 'retrieve' => $retrieve ) );
	
      $this->gamestate->nextState( 'takeBack' );
    }
    ***/

    function getCurrentEffect( )
    {
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}
      return $card['effects'][$effect_number];
    }
    
    function chooseOption( $optnum )
    {
      self::checkAction( 'effectPlayed' );
      $effect = self::getCurrentEffect( );
      if ( $effect == "chooseOption" )
	{
	  self::setGameStateValue( "option_chosen", intval($optnum) );
	  $this->gamestate->nextState( 'effectPlayed' );
	}
      else
	throw new BgaVisibleSystemException( "You can't do that now" );
    }

    function chooseColor( $id )
    {
      self::checkAction( 'colorChosen' );

      $valid = self::argChooseColor();

      if ( in_array($id, $valid['scorable']) )
	{
	  /* foreach ($valid['scorable'] as $against ) */
	  /*   { */
	  /*     self::DbQuery("UPDATE score SET score_common=score_common-1 WHERE score_player_id=".self::getActivePlayerId()." AND score_against=$against"); */
	  /*   } */
	  self::DbQuery("UPDATE score SET score_common=score_common+1 WHERE score_player_id=".self::getActivePlayerId()." AND score_against=$id");
	  
	  $this->gamestate->nextState( 'colorChosen' );
	}
      else
	throw new BgaVisibleSystemException( "You can't score in that color" );
    }

    function chooseColorLegend( $id )
    {
      self::checkAction( 'colorChosen' );

      $player_id = self::getActivePlayerId();
      if ( $player_id != $id )
	{
	  $players = self::getCollectionFromDb("SELECT player_id, player_score, player_name FROM player");
	  $scores = self::getCollectionFromDb("SELECT score_against against, score_value value FROM score WHERE score_player_id=$player_id", true);

	  $scores[$id]++;
	  self::DbQuery("UPDATE score SET score_value=score_value+1 WHERE score_player_id=$player_id AND score_against=$id");

	  self::myNotifyAllPlayers( 'updateScore', clienttranslate( '${player_name} scores ${diff} point(s) against ${a_name}' ), 
			array("player_id" => $player_id,
			      "player_name" => $players[$player_id]['player_name'],
			      "diff" => 1,
			      "against" => $id,
			      "a_name" => $players[$id]['player_name'],
			      "result" => $scores[$id] ) );

	  $min = 100;
	  foreach ($scores as $val)
	    if ($val < $min)
	      $min = $val;
	  if ( $min > $players[$player_id]['player_score'] )
	    {
	      self::DbQuery("UPDATE player SET player_score=$min WHERE player_id=$player_id");
	      self::myNotifyAllPlayers( 'updateScore', '', 
					array("player_id" => $player_id,
					      "diff" => 1 ) );
	    }

	  $this->gamestate->nextState( 'colorChosen' );
	}
      else
	throw new BgaVisibleSystemException( "You can't score in that color" );
    }

    function chooseColorFlare( $against )
    {
      self::checkAction( 'colorChosen' );
      $activable = self::argChooseColorFlare();
      if ( in_array( $against, $activable['activable'] ) )
	{
	  $player_id = self::getActivePlayerId();
	  self::doPlayFlare( self::flareNum( $player_id ), $player_id, $against );
	  $this->gamestate->nextState( 'colorChosen' );
	}
      else
	throw new BgaVisibleSystemException( "You can't invoke a flare against that color" );
    }

    function chooseColorImpro( $id )
    {
      $usable = self::argChooseColorImpro();
      if ( in_array( $id, $usable['usable'] ) )
	{
	  self::setGameStateValue( "last_impro", $id );
	  self::playCard( self::getGameStateValue( "card_x" ),
			  self::getGameStateValue( "card_y" ),
			  self::getGameStateValue( "deck_played" ),
			  self::getGameStateValue( "card_played" ) );
	}
      else
	throw new BgaVisibleSystemException ( "You can't do an improvised summoning in this color here" );
    }

    function putCardOn( $card_id )
    {
	$player_id = self::getActivePlayerId();
	if ( self::checkAction( 'frozenChosen', false ) )
	{
	  if ( self::getGameStateValue( 'frozen_effect' ) == $card_id )
	    {
		// Frozen effect remains the same
		// Rejected frozen is already in the discard
		$this->gamestate->nextState( 'frozenChosen' );
	    }
	  elseif ( self::getGameStateValue( 'deck_played' ) == 4
		   && self::getGameStateValue( 'card_played' ) == $card_id )
	  {
	      $new_frozen = self::getUniqueCardOfType( $this->cards,
						       "Everfrost",
						       $card_id );
	      $frozen_num = self::getGameStateValue( 'frozen_effect' );
	      $discarded_frozen = self::getUniqueCardOfType( $this->cards,
							     "Everfrost",
							     $frozen_num );

	      $this->cards->InsertCardOnExtremePosition( $new_frozen['id'],
							 "frozen", true);
	      $this->cards->InsertCardOnExtremePosition( $discarded_frozen['id'],
							 "discard_buffer", true);
	      self::setGameStateValue( "frozen_effect", $card_id );
	      // self::myNotifyPlayer( $player_id, "discardFrozen", "",
	      // 			    array( "card_id" => $frozen_num ) );
	      $being = $this->everfrost_contents[$card_id];
	      self::myNotifyAllPlayers( 'frozenInPlay', clienttranslate( '${player_name} puts ${being}\'s frozen effect into play' ), array(
				"i18n" => array( "frozentext", "being" ),
				"card_id" => $card_id,
				"frozentext" => $being['frozentext'],
				"player_name" => self::getActivePlayerName(),
				"being" => $being['name']
									) );
	      $this->gamestate->nextState( 'frozenChosen' );
	    }
	  else
	    throw new BgaVisibleSystemException ( "You don't have that frozen effect" );
	}
      else
	{
	  self::checkAction( 'effectPlayed' );
	  $effect = self::getCurrentEffect( );
	  if ( $effect == "putCardOnTop" )
	    { // Kiskin Spirit
	      $card = self::getUniqueCardOfType( $this->cards, "Sylvan",
						 $card_id );
	      if ( $card['location'] == 'discard'
		   || $card['location'] == 'discard_buffer' )
		{
		  $this->cards->InsertCardOnExtremePosition( $card['id'],
						     "SylvanDeck", true );
		  if ( $card['location'] == 'discard' )
		    self::setGameStateValue( "card_put_on_top", 1 );
		  else
		    self::setGameStateValue( "card_put_on_top", -1 );
		  $this->gamestate->nextState( 'effectPlayed' );
		}
	      else
		throw new BgaVisibleSystemException ( "That card is not in your discard" );
	    }
	  elseif ( $effect == "putFrozenInPlay" )
	    { // Frozen Chest
	      $card = self::getUniqueCardOfType( $this->cards, "Everfrost",
						 $card_id );
	      if ( $card['location'] == 'discard'
		   || $card['location'] == 'discard_buffer' )
		{
		    $this->cards->InsertCardOnExtremePosition( $card['id'],
							       "frozen", true);
		    $frozen_num = self::getGameStateValue( 'frozen_effect' );
		    if ( $frozen_num >= 0 ) {
			$discarded_frozen = self::getUniqueCardOfType(
			    $this->cards, "Everfrost", $frozen_num );
			$this->cards->InsertCardOnExtremePosition(
			    $discarded_frozen['id'], "discard_buffer", true);
            $discarded_frozen = self::enrichCards( array( $discarded_frozen['id'] => $discarded_frozen ) );
            $discarded_frozen = array_pop($discarded_frozen);

			self::myNotifyPlayer( $player_id, "discardFrozen", "",
                                  array( "card_id" => $frozen_num,
                                  "card" => $discarded_frozen ) );
		    }
		  $being = $this->everfrost_contents[$card_id];
		  self::setGameStateValue( "frozen_effect", $card_id );
		  self::myNotifyAllPlayers( 'frozenInPlay', clienttranslate( '${player_name} puts ${being}\'s frozen effect into play' ), array(
				"i18n" => array( "frozentext", "being" ),
				"card_id" => $card_id,
				"frozentext" => $being['frozentext'],
				"player_name" => self::getActivePlayerName(),
				"being" => $being['name']
									) );
		  $this->gamestate->nextState( 'effectPlayed' );
		}
	      else
		throw new BgaVisibleSystemException ( "That card is not in your discard" );
		}
	  elseif ( $effect == "performWarp" || $effect == "orEffects2" ) {
	    self::checkAction( 'effectPlayed' );
	    self::performWarp( $card_id );
	    $this->gamestate->nextState( 'effectPlayed' );
	  }
	  else
	    throw new BgaVisibleSystemException( "You can't do that now" );
	}
    }

    function playFrozen( )
    {
      self::checkAction( 'playFrozen' );
      $player_id = self::getActivePlayerId();
      $players = self::loadPlayersBasicInfos();
      $frozen = self::getGameStateValue( 'frozen_effect' );
      $school_ind = array_search($players[$player_id]['player_color'],
				 $this->schools_colors);

      if ( $this->decks[$school_ind] == 'Everfrost' && $frozen >= 0 )
	{
      //	  self::saveGameState(""); ?
	  self::setGameStateValue( "card_played", $frozen+20 );
	  self::setGameStateValue( "deck_played", $school_ind );
	  self::setGameStateValue( "effect_number", -2 );
	  $hoffset = 125 * $frozen / 2 + 2;
	  $icon = '<div id="last_frozen_'.$frozen.'" class="last_card_icon log_inlined Everfrost" style="background-position:-'.$hoffset.'px -12px"></div>';

	  self::myNotifyAllPlayers( "frozenPlayed", clienttranslate( '${icon}${player_name} thawed ${being}\'s frozen effect' ), array(
				'i18n' => array( 'being' ),
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'being' =>
				    $this->everfrost_contents[$frozen]['name'],
				'card_id' => $frozen,
				'icon' => $icon,
				'frozentext' => 
			$this->everfrost_contents[$frozen]['frozentext'] ) );
      //	  self::saveGameState(""); ?
	  $this->gamestate->nextState( 'playFrozen' );
	}
      else
	throw new BgaVisibleSystemException ("You don't have a Frozen effect");
    }

    function playWarp( $card_id )
    {
      self::checkAction( 'playWarp' );
      $player_id = self::getActivePlayerId();
      $thecard = self::getUniqueCardOfType( $this->cards, "Etherweave",
					    $card_id );
      if ( self::getGameStateValue( "turn_counter" ) == 1 )
	throw new BgaVisibleSystemException ( "You cannot play a warp effect on your first turn" );
      if ( $thecard['location'] != 'hand' || $thecard['location_arg'] != $player_id )
	throw new BgaVisibleSystemException ( "You don't have that card in your hand" );
      if ( !isset($this->etherweave_contents[$card_id]['warptext']) )
	throw new BgaVisibleSystemException ( "That being has no warp effect" );
      if ( self::getGameStateValue( "pending_being" ) >= 0 )
	throw new BgaVisibleSystemException ( "You already have a pending being" );
      //	  self::saveGameState(""); ?
      self::setGameStateValue( "pending_being", $card_id );
      self::setGameStateValue( "card_played", $card_id+20 );
      self::setGameStateValue( "deck_played", 6 ); // Etherweave
      self::setGameStateValue( "effect_number", -2 );
      $hoffset = 125 * $card_id / 2 + 2;
      $icon = '<div id="last_warp_'.$card_id.'" class="last_card_icon log_inlined Etherweave" style="background-position:-'.$hoffset.'px -12px"></div>';

      self::myNotifyAllPlayers( "warpPlayed", clienttranslate( '${icon}${player_name} played ${being}\'s warp effect' ), array(
		'i18n' => array( 'being' ),
		'player_id' => $player_id,
		'player_name' => self::getActivePlayerName(),
		'being' => $this->etherweave_contents[$card_id]['name'],
		'text' => $this->etherweave_contents[$card_id]['text'],
		'card_id' => $card_id,
		'icon' => $icon,
		'copy' => false,
		'warptext' => 
		    $this->etherweave_contents[$card_id]['warptext'] ) );
      //	  self::saveGameState(""); ?
      $this->gamestate->nextState( 'playWarp' );
    }

    function updateGlobals( $step )
    {
      $global_ids = array_flip( array(
	       "deck_played" => 10,
	       "card_played" => 11,
	       "effect_number" => 12,
	       "card_x" => 13,
	       "card_y" => 14,
	       "remaining_actions" => 15,
	       "piece_x" => 16,
	       "piece_y" => 17,
	       "piece_rank" => 18,
	       "piece_player" => 19,
	       "piece_before_x" => 20,
	       "piece_before_y" => 21,
	       "piece_before_player" => 22,
	       "last_dx" => 23,
	       "last_dy" => 24,
	       "flare_upgraded" => 25,
	       "flare_pieces" => 26,
	       "flare_rank" => 27,
	       "option_chosen" => 28,
	       "turn_discarded" => 29,
	       "extra_turn" => 30,
	       "extra_deck" => 31,
	       "extra_legends" => 32,
	       "card_put_on_top" => 33,
	       "last_player" => 34,
	       "last_impro" => 35,
		   "frozen_effect" => 36,
		   "gateway_x" => 37,
		   "gateway_y" => 38,
	           "pending_being" => 39,
	       "being_destroyed" => 40,
	       "being_moved" => 41,
	       "being_destroyed_legendary" => 42,
	       "has_skipped" => 43,
	       "being_upgraded_piece" => 44,
	       "being_placed_piece" => 45,
	       "combat_moves" => 46,
	       "bonus_improvisation" => 47,
	       "being_destroyed_common" => 48,
	       "being_destroyed_heroic" => 49,
	       "being_downgraded_piece" => 50,
	       "fullmoon" => 51,
	       "flare_discarded" => 52,
	       "action_malus" => 53,
	       "oversummoned" => 54,
	       "piece_removed" => 55,
		   "legend_removed" => 56,
		   "summoning_color" => 57,
		   "square_x" => 58,
		   "square_y" => 59,
	       "turn_destroyed_common" => 60,
	       "turn_destroyed_heroic" => 61,
	       "turn_destroyed_legendary" => 62,
	       "turn_summoned_beings" => 63,
	       "turn_summoned_legends" => 64,
	       "turn_summoned_red" => 65,
	       "turn_summoned_green" => 66,
	       "turn_summoned_common" => 67,
	       "turn_placed" => 68,
	       "turn_moved" => 69,
	       "turn_upgraded" => 70,
		   "log_chunk" => 71,
		   "additional_destroyed" => 72,
		   "eternal_emperor_warped" => 73,
	       "merchant_rank" => 74,
	       "merchant_player" => 75,
	       "void_summoning" => 76,
           "to_be_discarded" => 77,
	       "turn_counter" => 97,
				      ) );

      $changed = self::getCollectionFromDB( "SELECT g.global_id, gs.global_value
FROM global g, global_saved gs
WHERE g.global_id = gs.global_id
 AND g.global_value != gs.global_value
 AND gs.step = $step", true );

      foreach ( $changed as $id => $value )
	{
	  self::setGameStateValue( $global_ids[$id], $value );
	}

    }

    function enrichCard( $card )
    {
      if ( $card['deck'] == -1 )
	{
	  $card['upgraded'] =
	    $this->flares[$card['card']][0]['text'];
	  $card['pieces'] =
	    $this->flares[$card['card']][1]['text'];
	}
      else
	{
	  $deck = $this->decks[$card['deck']];
	  $card['cardname'] = $this->card_contents[$deck][$card['card']]['name'];
	  $card['text'] = $this->card_contents[$deck][$card['card']]['text'];
	  foreach ( array('frozentext', 'warptext') as $specialtext )
	    if (isset($this->card_contents[$deck][$card['card']][$specialtext]))
	      $card[$specialtext] =
		$this->card_contents[$deck][$card['card']][$specialtext];
	}
      return $card;
    }

    function undoStep()
    {
      self::checkAction( 'browseHistory' );
      $step = self::getGameStateValue("step");
      if ($step > 0)
	{
	  $step--;

	  $changed = self::getObjectListFromDB( "SELECT b.board_x as x, b.board_y as y, bs.board_player as player, bs.board_rank as rank, b.board_player as oldplayer FROM board b JOIN board_saved bs ON b.board_x = bs.board_x AND b.board_y = bs.board_y AND bs.step=$step WHERE (b.board_player IS NOT NULL OR bs.board_player IS NOT NULL) AND NOT (b.board_player <=> bs.board_player AND b.board_rank <=> bs.board_rank)" );

	  $last_played = self::getObjectFromDB( "SELECT p.player_id as id, ps.player_last_deck as deck, ps.player_last_card as card FROM player p JOIN player_saved ps ON p.player_id = ps.player_id AND ps.step=$step WHERE (p.player_last_deck <> ps.player_last_deck OR p.player_last_card <> ps.player_last_card) AND ps.player_last_deck <> -2" );
	  if ( $last_played !== null )
	    $last_played = self::enrichCard( $last_played );

	  self::DbQuery( "UPDATE board b, board_saved bs
SET b.board_player = bs.board_player,
 b.board_rank = bs.board_rank,
 b.board_marked = bs.board_marked,
 b.board_used = bs.board_used
WHERE b.board_x = bs.board_x
 AND b.board_y = bs.board_y
 AND bs.step=$step" );

	  self::DbQuery( "UPDATE card c, card_saved cs
SET c.card_type = cs.card_type,
 c.card_type_arg = cs.card_type_arg,
 c.card_location = cs.card_location,
 c.card_location_arg = cs.card_location_arg
WHERE c.card_id = cs.card_id
 AND cs.step = $step" );

	  self::DbQuery( "UPDATE player p, player_saved ps
SET p.player_pieces_left = ps.player_pieces_left,
 p.player_legends_left = ps.player_legends_left,
 p.player_score = ps.player_score,
 p.player_last_deck = ps.player_last_deck,
 p.player_last_card = ps.player_last_card
WHERE p.player_id = ps.player_id
 AND ps.step = $step" );

	  self::DbQuery( "UPDATE score s, score_saved ss
SET s.score_value = ss.score_value,
 s.score_common = ss.score_common,
 s.score_heroic = ss.score_heroic,
 s.score_legendary = ss.score_legendary,
 s.impro = ss.impro
WHERE s.score_player_id = ss.score_player_id
 AND s.score_against = ss.score_against
 AND ss.step = $step" );

	  self::updateGlobals( $step );
	  
	  $chunk = self::getGameStateValue( 'log_chunk' );

	  self::IncGameStateValue("step", -1);
      
	  // Notify
	  $scores = self::getCollectionFromDb( "SELECT player_id id, player_score score FROM player", true );
	  //	  $board = self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player, board_rank rank FROM board WHERE board_player IS NOT NULL", false );
	  // self::getObjectListFromDb( "SELECT b.board_x x, b.board_y y, bs.board_player player, bs.board_rank rank FROM board b, board_saved bs WHERE b.board_player IS NOT NULL" );

	  $player_id = self::getActivePlayerId();
	  $hand = self::getEnrichedCardsInLocation( $this->cards, 'hand', $player_id , "card_type");
	  $players = self::loadPlayersBasicInfos();
	  $color = $players[$player_id]['player_color'];
	  $school = $this->decks[ array_search($color,
					       $this->schools_colors) ];
	  $discard = self::enrichCards( array_merge(
		self::getCardsOfTypeInLocation( $school, "discard" ),
		self::getCardsOfTypeInLocation( $school, "discard_buffer")));

	  self::myNotifyPlayer( $player_id, "takeBackCards", '', array(
	//			'player_id' => $player_id,
				'hand' => $hand,
				'discard' => $discard ) );

	  $scores_dm = self::getDoubleKeyCollectionFromDb( "SELECT score_player_id id, score_against against, score_value value, score_common common, score_heroic heroic, score_legendary legendary, impro impro FROM score" );

	  $frozen = self::getGameStateValue( 'frozen_effect' );
	  if ( $frozen >= 0 )
	    $frozentext = $this->everfrost_contents[$frozen]['frozentext'];
	  else
	    $frozentext = '';
	  $pending = self::getGameStateValue( 'pending_being' );
	  if ( $pending >= 0 )
	    {
	      $pendingname = $this->etherweave_contents[$pending]['name'];
	      $pendingtext = $this->etherweave_contents[$pending]['text'];
	      $warptext = $this->etherweave_contents[$pending]['warptext'];
	    }
	  else
	    {
	      $pendingname = '';
	      $pendingtext = '';
	      $warptext = '';
	    }

	  self::myNotifyAllPlayers( "takeBack", '', array(
							  //			'player_id' => $player_id,
			'i18n' => array( 'frozentext' ),
			'type' => "undo",
                        'player_name' => self::getActivePlayerName(),
			'board' => $changed,
			'last_played' => $last_played,
			'scores' => $scores,
			'game_form' => self::getGameStateValue( 'game_form' ),
			'scores_dm' => $scores_dm,
			'frozen' => $frozen,
			'frozentext' => $frozentext,
			'pending' => $pending,
			'pendingname' => $pendingname,
			'pendingtext' => $pendingtext,
			'merchant_player' => self::getGameStateValue( 'merchant_player' ),
			'merchant_rank' => self::getGameStateValue( 'merchant_rank' ),
			'warptext' => $warptext,
			'gateway_x' => self::getGameStateValue( 'gateway_x' ),
			'gateway_y' => self::getGameStateValue( 'gateway_y' ),
			/* 'common_destroyed' => self::getGameStateValue( "turn_destroyed_common" ), */
			/* 'heroic_destroyed' => self::getGameStateValue( "turn_destroyed_heroic" ), */
			/* 'legendary_destroyed' => self::getGameStateValue( "turn_destroyed_legendary" ), */
			/* 'color_destroyed' => $enemy, */
			'chunk' => $chunk,
			'remaining' => $step ) );

	  self::notifyPiecesDifferentials();

	  $this->gamestate->nextState( 'browseHistory' );	  
	}
      else
	throw new BgaVisibleSystemException( "You can't undo now" );
    }

    function redoStep()
    {
      self::checkAction( 'browseHistory' );
      $maxstep = self::getGameStateValue("maxstep");
      $step = self::getGameStateValue("step");

      if ($step < $maxstep)
	{
	  $step++;
	  $changed = self::getObjectListFromDB( "SELECT b.board_x as x, b.board_y as y, bs.board_player as player, bs.board_rank as rank, b.board_player as oldplayer FROM board b JOIN board_saved bs ON b.board_x = bs.board_x AND b.board_y = bs.board_y AND bs.step=$step WHERE (b.board_player IS NOT NULL OR bs.board_player IS NOT NULL) AND NOT (b.board_player <=> bs.board_player AND b.board_rank <=> bs.board_rank)" );

	  $last_played = self::getObjectFromDB( "SELECT p.player_id as id, ps.player_last_deck as deck, ps.player_last_card as card FROM player p JOIN player_saved ps ON p.player_id = ps.player_id AND ps.step=$step WHERE (p.player_last_deck <> ps.player_last_deck OR p.player_last_card <> ps.player_last_card)  AND ps.player_last_deck <> -2" );
	  if ( $last_played !== null )
	    $last_played = self::enrichCard( $last_played );

	  $chunk = self::getGameStateValue( 'log_chunk' );

	  self::DbQuery( "UPDATE board b, board_saved bs SET
 b.board_player=bs.board_player,
 b.board_rank=bs.board_rank,
 b.board_marked=bs.board_marked,
 b.board_used=bs.board_used
WHERE b.board_x = bs.board_x AND b.board_y = bs.board_y
AND bs.step=$step" );

	  self::DbQuery( "UPDATE card c, card_saved cs
SET c.card_type = cs.card_type,
 c.card_type_arg = cs.card_type_arg,
 c.card_location = cs.card_location,
 c.card_location_arg = cs.card_location_arg
WHERE c.card_id = cs.card_id
 AND cs.step = $step" );

	  self::DbQuery( "UPDATE player p, player_saved ps
SET p.player_pieces_left = ps.player_pieces_left,
 p.player_legends_left = ps.player_legends_left,
 p.player_score = ps.player_score,
 p.player_last_deck = ps.player_last_deck,
 p.player_last_card = ps.player_last_card
WHERE p.player_id = ps.player_id
 AND ps.step = $step" );

	  self::DbQuery( "UPDATE score s, score_saved ss
SET s.score_value = ss.score_value,
 s.score_common = ss.score_common,
 s.score_heroic = ss.score_heroic,
 s.score_legendary = ss.score_legendary,
 s.impro = ss.impro
WHERE s.score_player_id = ss.score_player_id
 AND s.score_against = ss.score_against
 AND ss.step = $step" );

	  self::updateGlobals( $step );

	  self::IncGameStateValue("step", 1);
      
	  // Notify
	  $scores = self::getCollectionFromDb( "SELECT player_id id, player_score score FROM player", true );
	  $board = self::getObjectListFromDb( "SELECT board_x x, board_y y, board_player player, board_rank rank FROM board WHERE board_player IS NOT NULL" );

	  $player_id = self::getActivePlayerId();
	  $hand = self::getEnrichedCardsInLocation( $this->cards, 'hand', $player_id , "card_type");
	  $players = self::loadPlayersBasicInfos();
	  $color = $players[$player_id]['player_color'];
	  $school = $this->decks[ array_search($color,
					       $this->schools_colors) ];
	  $discard = self::enrichCards( array_merge(
		self::getCardsOfTypeInLocation( $school, "discard" ),
		self::getCardsOfTypeInLocation( $school, "discard_buffer")));

	  self::myNotifyPlayer( $player_id, "takeBackCards", '', array(
	//			'player_id' => $player_id,
				       'hand' => $hand,
				       'discard' => $discard ) );

	  $scores_dm = self::getDoubleKeyCollectionFromDb( "SELECT score_player_id id, score_against against, score_value value, score_common common, score_heroic heroic, score_legendary legendary, impro impro FROM score" );

	  $frozen = self::getGameStateValue( 'frozen_effect' );
	  if ( $frozen >= 0 )
	    $frozentext = $this->everfrost_contents[$frozen]['frozentext'];
	  else
	    $frozentext = '';
	  $pending = self::getGameStateValue( 'pending_being' );
	  if ( $pending >= 0 )
	    {
	      $pendingname = $this->etherweave_contents[$pending]['name'];
	      $pendingtext = $this->etherweave_contents[$pending]['text'];
	      $warptext = $this->etherweave_contents[$pending]['warptext'];
	    }
	  else
	    {
	      $pendingname = '';
	      $pendingtext = '';
	      $warptext = '';
	    }

	  self::myNotifyAllPlayers( "takeBack", '', array(
							  //			'player_id' => $player_id,
			'i18n' => array( 'frozentext' ),
			'type' => "redo",
			'player_name' => self::getActivePlayerName(),
			'board' => $changed,
			'last_played' => $last_played,
			'scores' => $scores,
			'game_form' => self::getGameStateValue( 'game_form' ),
			'scores_dm' => $scores_dm,
			'frozen' => $frozen,
			'frozentext' => $frozentext,
			'pending' => $pending,
			'pendingname' => $pendingname,
			'pendingtext' => $pendingtext,
			'merchant_player' => self::getGameStateValue( 'merchant_player' ),
			'merchant_rank' => self::getGameStateValue( 'merchant_rank' ),
			'warptext' => $warptext,
			'gateway_x' => self::getGameStateValue( 'gateway_x' ),
			'gateway_y' => self::getGameStateValue( 'gateway_y' ),
			/* 'common_destroyed' => self::getGameStateValue( "turn_destroyed_common" ), */
			/* 'heroic_destroyed' => self::getGameStateValue( "turn_destroyed_heroic" ), */
			/* 'legendary_destroyed' => self::getGameStateValue( "turn_destroyed_legendary" ), */
			/* 'color_destroyed' => $enemy, */
			'chunk' => $chunk,
			'remaining' => $maxstep - $step ) );

	  self::notifyPiecesDifferentials();

	  $this->gamestate->nextState( 'browseHistory' );	  
	}
      else
	throw new BgaVisibleSystemException( "There is nothing to redo now" );
    }


    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argDeckChoice()
    {
        $unavailable_colors = self::getObjectListFromDB( "SELECT player_color FROM player", true );
        foreach( ['Everfrost', 'Nethervoid', 'Etherweave'] as $i => $school) {
            if ( self::getGameStateValue( $school.'_set' ) == 0 )
                $unavailable_colors[] = $this->schools_colors[4+$i];
        }
        return $unavailable_colors;
    }

    function argMeleeInitial()
    {
      $table = $this->getNextPlayerTable();
      $player = $table[$table[$table[0]]];
      $players = self::computePiecesNumbers();
      /* for( $i=1; $i<3 && $players[$player]['pieces'] > 0; $i++ ) */
      /* 	{ */
      /* 	  $player = $table[$player]; */
      /* 	} */
      while( $players[$player]['pieces'] > 0 )
	{
	  $player = $table[$player];
	}
      $color = $players[$player]['player_color'];

      $board = self::getBoard();
      $empty = array( 2 => array(), 3 => array(), 6 => array(), 7 => array() );
      if ( $board[6][2]['player'] === null && $board[7][2]['player'] === null )
	{
	  $empty[6][2] = true;
	  $empty[7][2] = true;
	}
      if ( $board[2][7]['player'] === null && $board[3][7]['player'] === null )
	{
	  $empty[2][7] = true;
	  $empty[3][7] = true;
	}
      if ( $board[7][6]['player'] === null && $board[7][7]['player'] === null )
	{
	  $empty[7][6] = true;
	  $empty[7][7] = true;
	}

      return array( "color" => "<div class='token_$color common pieceschoice'></div>",
		    "empty" => $empty );
    }

    function argActionChoice()
    {
      return array( "discard_ok" =>
		    ( self::getGameStateValue( "turn_discarded" ) == 0 ),
		    "empty" => self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player FROM board WHERE board_player IS NULL", true ),
		    "actions" => self::getGameStateValue("remaining_actions"),
		    "frozen" => self::getGameStateValue( "frozen_effect" ),
		    "pending" => self::getGameStateValue( "pending_being" ),
		    "turn_counter" => self::getGameStateValue( "turn_counter" )
		    );
    }

    function argEffectInput()
    {
      $deck_id = self::getGameStateValue("deck_played");
      $deck = $this->decks[$deck_id];
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      if ( $deck_id != -1 )
	{
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      $effect = $card['effects'][$effect_number];
      $mandatory = self::isMandatory( $deck_id, $deck, $card_id, $card, $effect_number );
      
      $result = array (
		    'mandatory' => $mandatory
		    );

      if ( substr( $effect, 0, 9) == "orEffects" )
	{
	  $neffects = intval( substr( $effect, 9, 1) );
	  for ( $i = 1 ; $i <= $neffects ; $i++ )
	    {
	      $result['effect'.$i] = $card['effects'][$effect_number+$i];
	      $result['clickable'.$i] = self::selectPiecesForEffect( $result['effect'.$i], $card_x, $card_y, $card['effecttargets'][$effect_number/2+$i-1] );
	      if ( $result['effect'.$i] == "performWarp" )
		$result['clickable'.$i] = self::enrichCards(
						$result['clickable'.$i] );
	    }
	}
      else
	{
	  $result['effect'] = $effect;
	  $result['clickable'] = self::selectPiecesForEffect( $effect, $card_x, $card_y, $card['effecttargets'][$effect_number/2] );	  
	}
      $result['x'] = $card_x;
      $result['y'] = $card_y;
      if ( $effect == "movePiece" &&
	   $card['effecttargets'][$effect_number/2][1] == "swap" )
	  $result['swap'] = true;
      
      return $result;
    }

    function argPlaceInput()
    {
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      $args = self::argEffectInput();
      $player_id = self::getActivePlayerId();
      if ( $card['effects'][$effect_number] == 'freePiece'
	   || $card['effects'][$effect_number] == 'chooseSquare' )
	$args['eligible_players'] = array( );
      else if ( $card['effecttargets'][$effect_number/2][0] == "playerPiece" )
	$args['eligible_players'] = array( $player_id );
      else {
	$args['eligible_players'] = array();
	$players = self::getCollectionFromDb( "SELECT player_id id, player_pieces_left pieces, player_legends_left legends FROM player" );
	foreach ($players as $id => $player)
	  if ( $id != $player_id && $player['pieces'] != 0 )
	    $args['eligible_players'][] = $id;
      }
      
      return $args;
    }

    function argPickPiece()
    {
      $args = array();
      if ( self::getGameStateValue( "piece_rank" ) < 2)
	$args['clickable'] = self::selectPieces( array( "playerPiece", "nonLegendaryPiece", "unusedPieces" ), "" );
      else
	$args['clickable'] = self::selectPieces( array( "playerPiece", "legendaryPiece", "unusedPieces" ), "" );

      return $args;
    }

    function argTurnEnd()
    {
      $claimable = 0; // array();
      foreach ( $this->cards->getCardsInLocation( "current_tasks" ) as $task )
	{
	  list( $completed, $used ) = self::mycall_user_func_array(
			$this->tasks[$task['type_arg']]['criteria'], 
			$this->tasks[$task['type_arg']]['critargs'] );
	  if ( $completed )
	    $claimable = 1; //$task['type_arg'];
	}
      return array( 'claimable' => $claimable,
		    'frozen' => self::getGameStateValue( "frozen_effect" ) );
    }

    function argChooseColor()
    {
      $player_id = self::getActivePlayerId();

      $points = array();
      $leftovers = array();
      $scores = self::getCollectionFromDb("SELECT score_against against, score_common common, score_heroic heroic, score_legendary legendary FROM score WHERE score_player_id=".$player_id);
      $players = self::loadPlayersBasicInfos();
      foreach ( $scores as $against => $destroyed)
	{
	  $p = 2 * $destroyed['legendary'] + $destroyed['heroic']
	    + intval( $destroyed['common'] / 2 );
	  if ($p > 0)
	      $points[$players[$against]['player_color']] = $p;
	  if ( $destroyed['common'] % 2 == 1 )
	    $leftovers[] = $against;
	}
      
      return array( 'will_score' => $points, 'scorable' => $leftovers );
    }

    function argChooseColorLegend()
    {
      $player_id = self::getActivePlayerId();
      $scorable = self::getObjectListFromDb("SELECT player_id FROM player WHERE player_id<>$player_id", true);
      return array( 'scorable' => $scorable );
    }

    function argChooseColorFlare()
    {
      $player_id = self::getActivePlayerId();
      $flarenum = self::flareNum( $player_id );

      $against = array();
      if ( $flarenum != -1 )
	{
	  $players = self::computePiecesNumbers();

	  $player = $players[$player_id];
	  foreach ( array(0 => 'upgraded', 1 => 'pieces') as $num => $rank )
	    {
	      foreach ( $players as $opid => $opponent )
		{
		  if ( $opid != $player_id )
		    if ( $opponent[$rank] - $player[$rank]
			 >= $this->flares[$flarenum][$num]['more'] )
		      {
			if ( ! in_array( $opid, $against ) )
			  $against[] = $opid;
		      }
		}
	    }
	  return array( 'activable' => $against );
	}
      else
	throw new BgaVisibleSystemException ( "You don't have any flare left" );
    }

    function argChooseColorImpro()
    {
      $deck_id = self::getGameStateValue( "deck_played" );
      $card_id = self::getGameStateValue( "card_played" );
      $x = self::getGameStateValue( "card_x" );
      $y = self::getGameStateValue( "card_y" );

      $player_id = self::getActivePlayerId();
      $board = self::getBoard();
      $deck = $this->decks[$deck_id];
      $card = $this->card_contents[$deck][$card_id];

      $summoning_color = self::getGameStateValue( "summoning_color" );
      if ($summoning_color != 0){
	$pattern_color = $summoning_color;
      } else {
	$pattern_color = $player_id;
      }
      $found = self::foundPatternWrapper( $x, $y, $card, $board, $pattern_color );
      $impros = self::getObjectListFromDb( "SELECT score_against FROM score WHERE impro=1 AND score_player_id=$player_id", true);
      foreach ( $impros as $i => $impro )
	if ( count($found['used'][$impro]) == 0 || $impro == $pattern_color )
	  unset($impros[$i]);

      if ( count($found['used'][$player_id]) > 0 )
	$impros[] = $player_id;
      return array( 'usable' => $impros );
    }

    function argOptions()
    {
      $deck = self::getGameStateValue("deck_played");
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      $args = array();
      if ( $deck != -1 )
	{
	  $deck = $this->decks[$deck];
	  $card = $this->card_contents[$deck][$card_id];
	  $args['mandatory'] = isset( $card['mandatory'] )
	    && in_array($effect_number, $card['mandatory'] );
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	  $args['mandatory'] = ! ( isset( $card['skippable'] )
			   && in_array($effect_number,
				       $card['skippable'] ) );
	}

      //      $args['i18n'] = array( 'questionhe', 'questionyou' );
      $args['questionhe'] = $card['effecttargets'][$effect_number/2][0];
      $args['questionyou'] = $card['effecttargets'][$effect_number/2][1];
      $args['option1'] = $card['effecttargets'][$effect_number/2][2];
      $args['option2'] = $card['effecttargets'][$effect_number/2][3];
      if ( isset($card['effecttargets'][$effect_number/2][4]) )
	$args['option3'] = $card['effecttargets'][$effect_number/2][4];

      // Piece shortage for storm Elemental
      if ( $deck == "Legends" && $card_id == 10 )
	{
	  $player_id = self::getActivePlayerId();
	  $left = self::getObjectFromDb( "SELECT player_pieces_left pieces, player_legends_left legends FROM player WHERE player_id=$player_id" );
	  if ( $left['legends'] == 0 )
	    unset( $args['option3'] );
	}

      return $args;
    }

    function argCardChoice()
    {
      $deck = $this->decks[self::getGameStateValue("deck_played")];
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      $card = $this->card_contents[$deck][$card_id];
      $args = array( 'mandatory' => isset( $card['mandatory'] )
		     && in_array($effect_number, $card['mandatory'] ) );
      $args['_private'] = array( 'active' => array( 
	'cards' => self::enrichCards( self::selectPiecesForEffect(
			$card['effects'][$effect_number], 0, 0, null ) )
						    ) );
      if ( $card['effects'][$effect_number] == 'discardSingleCard' )
	$args['location'] = 'hand';
      else
	$args['location'] = 'discard';
      $args['school'] = $deck;
      return $args;
    }

    function argFrozenChoice()
    {
      $card_id = self::getGameStateValue("card_played");
      $frozen = self::getGameStateValue("frozen_effect");
      
      $args = array ( 'school' => 'Everfrost' );
      $args['_private'] = array( 'active' => array( 
	'cards' => self::enrichCards( array(
						$card_id =>
		self::getUniqueCardOfType($this->cards, 'Everfrost', $card_id),
						$frozen =>
		self::getUniqueCardOfType( $this->cards, 'Everfrost', $frozen )
					    ) ) ) );
      return $args;
    }

    function computePiecesNumbers()
    {
      $players = self::loadPlayersBasicInfos();
      $board = self::getBoard();
      foreach ( $players as $id => $player )
	{
	  $players[$id]['upgraded'] = 0;
	  $players[$id]['pieces'] = 0;
	  $players[$id]['legendary'] = 0;
	}

      for( $x=1; $x<=9; $x++ )
	for( $y=1; $y<=9; $y++ )
	  if ( self::onBoard( $x, $y )
	       && $board[$x][$y]['player'] !== null )
	    {
	      $players[$board[$x][$y]['player']]['pieces']++;
	      if ( $board[$x][$y]['rank'] > 0 )
		$players[$board[$x][$y]['player']]['upgraded']++;
	      if ( $board[$x][$y]['rank'] == 2 )
		$players[$board[$x][$y]['player']]['legendary']++;
	    }
      return $players;
    }

    function computePiecesDifferentials()
    { // and cards left
      $players = self::computePiecesNumbers();
      $cards = $this->cards->countCardsInLocations();
      $available_pieces = self::pieceCount( $players );
      $piece_removed = self::getGameStateValue( 'piece_removed' );
      $legend_removed = self::getGameStateValue( 'legend_removed' );
      $rank_captured = self::getGameStateValue( 'merchant_rank' );
      $player_captured = self::getGameStateValue( 'merchant_player' );

      foreach ( $players as $id => $player )
	{
	  $flarenum = self::flareNum( $id );
	  $activable = false;

	  $args = array();
	  /*** DEPRECATED
	  if ( isset( $cards['LegendsDeck'] ) )
	    $args["cards_legends"] = $cards['LegendsDeck'];
	  else
	    $args["cards_legends"] = 0;
	  if ( isset( $cards['FlareDeck'] ) )
	    $args["cards_flares"] = $cards['FlareDeck'];
	  else
	    $args["cards_flares"] = 0;
	  ***/
	  foreach ( $players as $opid => $opponent )
	    {
	      $pieces = $available_pieces - $opponent['pieces']
		+ $opponent['legendary'];
	      if ( $piece_removed == $opid )
		$pieces--;
	      if ( $rank_captured <= 1 && $player_captured == $opid )
		$pieces--;
	      $legends = 3 - $opponent['legendary'];
	      if ( $legend_removed == $opid )
		$legends--;
	      if ( $rank_captured == 2 && $player_captured == $opid )
		$legends--;

	      $args["nonlegendary_$opid"] = $pieces;
	      $args["legendary_$opid"] = $legends;
	      $school = $this->decks[
			     array_search($players[$opid]['player_color'],
					  $this->schools_colors)];
	      if ( isset( $cards[$school.'Deck'] ) )
		$args["cards_$opid"] = $cards[$school.'Deck'];
	      else
		$args["cards_$opid"] = 0;
	      if ( $opid != $id )
		{
		  foreach ( array( 0 => 'upgraded', 1 => 'pieces')
			    as $num => $rank )
		    {
		      $args[$rank.'diff_vs_'.$opponent['player_color']] = $opponent[$rank] - $player[$rank];

		      if ( $flarenum != -1 &&
			   $opponent[$rank] - $player[$rank]
			   >= $this->flares[$flarenum][$num]['more'] )
			$activable = true;
		    }
		}
	      else
		{
		  $sql = "UPDATE player SET player_pieces_left=$pieces, player_legends_left=$legends WHERE player_id=$id";
		  self::DbQuery( $sql );
		}
	    }
	  if (isset($cards['return_buffer']))
	    {
	      $playerid = self::getActivePlayerId();
	      $args["cards_$playerid"] += $cards['return_buffer'];
	    }

	  $args['activable'] = $activable;
	  $args['flarenum'] = $flarenum;
	  $players[$id]['args'] = $args;
	}
      return $players;
    }

    function notifyPiecesDifferentials()
    {
      $players = self::computePiecesDifferentials();
      foreach ( $players as $id => $player )
	{
	  self::myNotifyPlayer( $id, "piecesDiff", "", $player['args'] );
	  unset($players[$id]['args']['flarenum']);
	  // Too much of a mess to display with more than 2 players
	  if (count($players) > 2) {
	    foreach ($this->schools_colors as $c) {
	      unset($players[$id]['args']['piecesdiff_vs_'.$c]);
	      unset($players[$id]['args']['upgradeddiff_vs_'.$c]);
	    }
	    unset($players[$id]['args']['piecesdiff_vs_000000']);
	    unset($players[$id]['args']['upgradeddiff_vs_000000']);
	  }
	}
      self::myNotifyAllPlayers( "piecesDiffSpectator", "", $players );


      return $players;
    }

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function afterDeckChoice()
    {
      $players = self::loadPlayersBasicInfos();
      foreach ($players as $id => $player)
	{
	  switch ($player['player_color']) {
	  case '037cb1':
	  case 'dc2515':
	    self::setStat( true, 'imperial_played', $id );
	    break;
	  case 'd6b156':
	    self::setStat( true, 'highland_played', $id );
	    break;
	  case '8ec459':
	    self::setStat( true, 'sylvan_played', $id );
	    break;
	  case 'f0f9ff':
	    self::setStat( true, 'everfrost_played', $id );
		break;
	  case 'f4913c':
		self::setStat( true, 'nethervoid_played', $id );
		break;
	  case '6a548f':
		self::setStat( true, 'etherweave_played', $id );
		break;		
	  }
	}

      if ( self::getGameStateValue( 'game_form' ) == 1 )
	{
	  // Test specific tasks
	  /*******
		  for ( $i = 11 ; $i <= 19 ; $i++ )
		  {
		  $task = $this->cards->getCardsOfType( 'task', $i );
		  $this->cards->moveCards( array_keys($task), "current_tasks" );
		  }
	  ********/
	  // Three non-advanced tasks
	  $tasks = array();
	  for ( $i = 1 ; $i <= 3 ; $i++ )
	    {
	      $task = $this->cards->pickCardForLocation( "TasksDeck",
							 "current_tasks" );
	      while ( $this->tasks[$task['type_arg']]['difficulty']
		      == "advanced" )
		{
		  $this->cards->playCard( $task['id'] );
		  $task = $this->cards->pickCardForLocation( "TasksDeck",
							     "current_tasks" );
		}
	      $tasks[$i] = $this->tasks[$task['type_arg']];
	      $tasks[$i]['type_arg'] = $task['type_arg'];
	    }
	  // The last one must have a different type
	  while ( $this->tasks[$task['type_arg']]['difficulty'] == "advanced"
		  ||   $this->tasks[$task['type_arg']]['type'] == $tasks[1]['type']
		  && $this->tasks[$task['type_arg']]['type'] == $tasks[2]['type'])
	    {
	      $this->cards->playCard( $task['id'] );
	      $task = $this->cards->pickCardForLocation( "TasksDeck",
							 "current_tasks" );
	    }
	  $tasks[3] = $this->tasks[$task['type_arg']];
	  $tasks[3]['type_arg'] = $task['type_arg'];
	  
	  $discarded_tasks = self::getCardsOfTypeInLocation( "task",
							     "discard" );
	  $this->cards->moveCards( array_keys($discarded_tasks), 'TasksDeck' );
	  $this->cards->shuffle( 'TasksDeck' );
	  
	  // Next task must not have the same type as 2 others
	  $task = $this->cards->pickCardForLocation( "TasksDeck",
						     "next_task" );
	  
	  $types = array( "destruction" => 0, "colored" => 0, "summoning" => 0,
			  "pattern" => 0, "enemy" => 0 );
	  foreach ( $tasks as $card )
	    $types[ $card['type'] ]++;
	  $conflicting_type = array_search( 2, $types );
	  while ( $this->tasks[$task['type_arg']]['type'] == $conflicting_type )
	    {
	      $this->cards->InsertCardOnExtremePosition( $task['id'], "TasksDeck", false );
	      $task = $this->cards->pickCardForLocation( "TasksDeck",
							 "next_task" );
	    }
	  
	  // Enrich the cards with their description
	  for ( $i = 1 ; $i <= 3 ; $i++ )
	    $tasks[$i]['type'] = 'task';
	  $task['name'] = $this->tasks[$task['type_arg']]['name'];
	  $task['text'] = $this->tasks[$task['type_arg']]['text'];
	  self::myNotifyAllPlayers( "retrieveTasks", "",
				    array( 'current' => $tasks,
					   'next' => $task ) );
	  
	  $this->activeNextPlayer();
	  $this->gamestate->nextState( 'beginGame' );
	}
      else
	{
	  if (count(self::loadPlayersBasicInfos()) == 2)
	    $this->gamestate->nextState( 'initialPiecesDuel' );
	  else
	    $this->gamestate->nextState( 'initialPiecesMelee' );
	}
    }

    function stRandomDecks()
    {
      if ( self::getGameStateValue( "deck_selection" ) == 1 )
	$this->gamestate->nextState( 'chooseDecks' );
      else
	{
	  $players = self::loadPlayersBasicInfos();
      $available_colors = $this->schools_colors;
      foreach( ['Everfrost', 'Nethervoid', 'Etherweave'] as $i => $school) {
          if ( self::getGameStateValue( $school.'_set' ) == 0 )
              unset($available_colors[4+$i]);
      }
	  $colors = array_rand( $available_colors, count($players) );
	  $i = 0;
	  foreach( $players as $id => $player )
	    {
	      $color = $this->schools_colors[$colors[$i]];
	      $school = $this->decks[$colors[$i]];
	      self::DbQuery( "UPDATE player SET player_color='$color' WHERE player_id='$id'" );
	      self::reloadPlayersBasicInfos();
	      self::myNotifyAllPlayers( "chooseDeck",
		clienttranslate('${player_name} chose the ${school} deck'),
			array( "i18n" => array( 'school' ),
			       "player_id" => $id,
			       'player_name' => $player['player_name'],
			       "school" => $school,
			       "color" => $color ) );
	      
	      // Init the hand
	      $drawn = $this->cards->pickCards(3, $school.'Deck', $id );
	      $drawn = array_merge( $drawn,
			$this->cards->pickCards(2, 'LegendsDeck', $id));
	      $drawn[] = $this->cards->pickCard( 'FlareDeck', $id );
	      self::myNotifyPlayer( $id, "retrieveCards", "",
			    array( 'retrieve' => self::enrichCards($drawn) ) );
	      self::notifyPiecesDifferentials();
	      if ($i != 0)
		$this->activeNextPlayer();
	      $i++;
	    }

	  self::afterDeckChoice();
	}
    }

    function stNextDeck()
    { // Initial tasks are drawn here, when the last player has chosen a deck
      if ( count( self::getObjectListFromDB( "SELECT player_color FROM player WHERE player_color='000000'", true ) ) == 0 )
	{
	  $players = self::getCollectionFromDb(
		"SELECT player_id id, player_color color FROM player " );
	  if (count($players) > 2)
	    self::notifyAllPlayers( "colorsPicked", '',
				    array( 'players' => $players ) );
	  self::afterDeckChoice();
	}
      else
	{
	  $this->activeNextPlayer();
	  $this->gamestate->nextState( 'nextDeck' );
	}
    }

    function stSetFirstPlayer()
    {
	  $this->activeNextPlayer();
	  $this->gamestate->nextState( );
    }

    function stTurnBegin()
    {
      self::setGameStateValue( "deck_played", -2 );
      self::setGameStateValue( "card_played", -1 );
      self::setGameStateValue( "bonus_improvisation", 0 );
      self::setGameStateValue( "void_summoning", 0 );
      self::setGameStateValue( "extra_turn", 0 );
      self::setGameStateValue( "extra_deck", 0 );
      self::setGameStateValue( "extra_legends", 0 );
      self::setGameStateValue( "card_put_on_top", 0 );
      self::setGameStateValue( "turn_discarded", 0 );
      self::setGameStateValue( "turn_destroyed_common", 0 );
      self::setGameStateValue( "turn_destroyed_heroic", 0 );
      self::setGameStateValue( "turn_destroyed_legendary", 0 );
      self::setGameStateValue( "turn_summoned_beings", 0 );
      self::setGameStateValue( "turn_summoned_legends", 0 );
      self::setGameStateValue( "turn_summoned_red", 0 );
      self::setGameStateValue( "turn_summoned_green", 0 );
      self::setGameStateValue( "turn_summoned_common", 0 );
      self::setGameStateValue( "turn_placed", 0 );
      self::setGameStateValue( "turn_moved", 0 );
	  self::setGameStateValue( "turn_upgraded", 0 );
	  self::setGameStateValue( "additional_destroyed", 0 );
	  self::setGameStateValue( "eternal_emperor_warped", 0);
      self::setGameStateValue( "last_impro", 0 );
	  self::setGameStateValue( "flare_discarded", 0 );
	  self::setGameStateValue( "summoning_color", 0);
	  $id = self::getActivePlayerId();
	  $players = self::loadPlayersBasicInfos();
	  if (self::getNextPlayerTable()[0] == $id)
	    self::IncGameStateValue( "turn_counter", 1 );

      self::DbQuery("UPDATE score SET score_common=0, score_heroic=0, score_legendary=0");

      self::setGameStateValue( "step", -1 );
      self::DbQuery( "DELETE FROM player_saved" );
      self::DbQuery( "DELETE FROM board_saved" );
      self::DbQuery( "DELETE FROM card_saved" );
      self::DbQuery( "DELETE FROM score_saved" );
      self::DbQuery( "DELETE FROM global_saved" );
      self::DbQuery( "DELETE FROM state_saved" );

      self::DbQuery( "UPDATE board SET board_saved_player=board_player, board_saved_rank=board_rank" );
      self::DbQuery( "UPDATE player SET player_saved_score=player_score" );
      if ( self::getGameStateValue( 'pending_being' ) >= 0 )
	foreach ( $players as $id => $player )
	  if ( $player['player_color'] == '6a548f' )
	    self::DbQuery( "UPDATE player SET player_saved_score=player_score-2 WHERE player_id=$id" );

      self::notifyPiecesDifferentials();
      self::saveGameState("actionChoice");
      $this->gamestate->nextState( 'firstAction' );
    }
    
    function stNextAction()
    {
      $player_id = self::getActivePlayerId();
      $players = self::notifyPiecesDifferentials();
      self::IncGameStateValue( "remaining_actions", -1 );
      self::setGameStateValue( "deck_played", -2 );
      self::setGameStateValue( "card_played", -1 );
      self::setGameStateValue( "last_impro", 0 );
      self::setGameStateValue( "oversummoned", 0 );
      self::setGameStateValue( "to_be_discarded", -1 );

      if (self::getGameStateValue( "remaining_actions" ) > 0)
	{
	  self::saveGameState("actionChoice");
	  $this->gamestate->nextState( 'nextAction' );
	}
      else
	{
	  /*** DEPRECATED
	  if ( $players[$player_id]['args']['activable'] )
	    $this->gamestate->nextState( 'flareAvailable' );
	  else
	  ***/
	  if ( self::getGameStateValue( 'game_form' ) == 1 )
	    {
	      self::saveGameState("turnEndHF");
	      $this->gamestate->nextState( 'actionsDoneHF' );
	    }
	  else
	    {
	      self::saveGameState("turnEndDM");
	      $this->gamestate->nextState( 'actionsDoneDM' );
	    }
	}
    }

    /*** DEPRECATED
    function stCheckTasks()
    {
      if ( self::getGameStateValue( "game_form" ) == 1 )
	{
	  $claimable = self::argTaskChoice();

	  if ( $claimable == array() )
	    $this->gamestate->nextState( 'tasksDone' );
	  else
	    $this->gamestate->nextState( 'askTask' );
	}
      else
	$this->gamestate->nextState( 'tasksDone' );	
    }
    ***/ 

    function stNextPlayer()
    {
      $player_id = self::getActivePlayerId();

      // Update scores and stats
      self::incStat( 1, 'turns_number' );

      $common = self::getGameStateValue( 'turn_summoned_common' );
      $legendary = self::getGameStateValue( 'turn_summoned_legends' );
      $heroic = self::getGameStateValue( 'turn_summoned_beings' ) - $common - $legendary;
      self::incStat( $common, 'common_summoned', $player_id );
      self::incStat( $heroic, 'heroic_summoned', $player_id );
      self::incStat( $legendary, 'legendary_summoned', $player_id );

      self::incStat( self::getGameStateValue('turn_discarded'),
		     'cards_discarded', $player_id );

      $common = self::getGameStateValue( 'turn_destroyed_common' );
      $heroic = self::getGameStateValue( 'turn_destroyed_heroic' );
      $legendary = self::getGameStateValue( 'turn_destroyed_legendary' );
      self::incStat( $common + $heroic + $legendary,
		     'pieces_destroyed', $player_id );

      self::incStat( self::getGameStateValue('turn_placed'),
		     'pieces_placed', $player_id );      
      self::incStat( self::getGameStateValue('turn_moved'),
		     'pieces_moved', $player_id );      
      self::incStat( self::getGameStateValue('turn_upgraded'),
		     'pieces_upgraded', $player_id );

      if ( self::getGameStateValue( "game_form" ) == 2 )
	{
	  $scores = self::getCollectionFromDb("SELECT score_against against, score_common common, score_heroic heroic, score_legendary legendary, score_value value FROM score WHERE score_player_id=".$player_id);
	  if ( count($scores) == 1 )
	    {
	      $points = 2 * $legendary + $heroic + intval( $common / 2 );
	      if ($points > 0)
		{
		  self::DbQuery( "UPDATE player SET player_score=player_score+$points WHERE player_id=$player_id" );
		  self::myNotifyAllPlayers( 'updateScore', clienttranslate( '${player_name} scores ${diff} point(s)' ), 
			array("player_id" => $player_id,
			      "player_name" =>  self::getActivePlayerName(),
			      "diff" => $points ) );
		}
	    }
	  else
	    {
	      $players = self::getCollectionFromDB( "SELECT player_id, player_score, player_name FROM player" );
	      $min = 100;
	      foreach ( $scores as $against => $destroyed)
		{
		  $points = 2 * $destroyed['legendary'] + $destroyed['heroic']
		    + intval( $destroyed['common'] / 2 );
		  $result = $destroyed['value'] + $points;
		  if ($result < $min)
		    $min = $result;
		  if ($points > 0)
		    {
		      self::DbQuery( "UPDATE score SET score_value=score_value+$points WHERE score_player_id=$player_id AND score_against=$against" );
		      self::myNotifyAllPlayers( 'updateScore', clienttranslate( '${player_name} scores ${diff} point(s) against ${a_name}' ), 
			array("player_id" => $player_id,
			      "player_name" =>  self::getActivePlayerName(),
			      "diff" => $points,
			      "against" => $against,
			      "a_name" => $players[$against]['player_name'],
			      "result" => $result) );
		    }
		}

	      self::DbQuery( "UPDATE player SET player_score=$min WHERE player_id=$player_id" );
	      self::myNotifyAllPlayers( 'updateScore', "", 
		array("player_id" => $player_id,
		      "diff" => $min - $players[$player_id]['player_score'] ));
	    }

	  self::myNotifyAllPlayers( 'cleanDestroyed', '', array( ) );	  
	}

      // Flush the discard and return buffers
      foreach ( $this->cards->getCardsInLocation( "discard_buffer", null, 'card_location_arg' ) as $id => $card )
	$this->cards->InsertCardOnExtremePosition( $card['id'],
						   'discard',
						   true );
      foreach ( $this->cards->getCardsInLocation( "return_buffer", null, 'card_location_arg' ) as $id => $card )
	$this->cards->InsertCardOnExtremePosition( $card['id'],
						   $card['type']."Deck",
						   false );

      // Draw up to 3-2-1 cards in hands, plus bonus
      $drawn = array();
      if ( count( self::getCardsOfTypeInLocation( 'Flare', 'hand',
						  $player_id ) ) < 1 )
	{
  	  if ( self::getGameStateValue("flare_discarded") == 0 )
		self::incStat( 1, 'flares_invoked', $player_id );
	  $drawn[] = self::pickCardReshuffle( $this->cards, 'FlareDeck',
					      $player_id );
	}

      $l = count( self::getCardsOfTypeInLocation( 'Legends', 'hand',
						  $player_id ) );
      for ( $i=2 ; $i>$l ; $i-- )
	$drawn[] = self::pickCardReshuffle( $this->cards, 'LegendsDeck',
					    $player_id );
      for ( $i = self::getGameStateValue( "extra_legends" ) ; $i>0 ; $i-- )
	{
	  $drawn[] = self::pickCardReshuffle( $this->cards, 'LegendsDeck',
					      $player_id );
	  self::myNotifyAllPlayers( "extraCard", clienttranslate( '${player_name} drew 1 extra card from the legendary deck' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
			    ) );
	}
      
      $players = self::loadPlayersBasicInfos();
      $color = $players[$player_id]['player_color'];
      $school = $this->decks[array_search($color, $this->schools_colors)];
      $d = count( self::getCardsOfTypeInLocation( $school, 'hand',
						  $player_id ) );
      // DO NOT reshuffle your deck
      if ( $d < 3 )
	$drawn = array_merge( $drawn,
			      $this->cards->pickCards( 3-$d,
						       $school.'Deck',
						       $player_id ) );

      $deck_picked = $this->cards->pickCards(
				self::getGameStateValue( "extra_deck" ),
				$school.'Deck',
				$player_id );
      $drawn = array_merge( $drawn, $deck_picked );
      for ( $i=count($deck_picked) ; $i>0 ; $i-- )
	self::myNotifyAllPlayers( "extraCard", clienttranslate( '${player_name} drew 1 extra card from his deck' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName() ) );

      self::myNotifyPlayer( $player_id, "retrieveCards", "",
			  array( 'retrieve' => self::enrichCards($drawn) ) );

      // Time elemental shortcuts turn order
      if ( self::getGameStateValue( "extra_turn" ) > 0 )
	self::myNotifyAllPlayers( "turnGained", clienttranslate( '${player_name} takes an extra turn !' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ) );
      else
	{
	  if ( self::getGameStateValue( "last_player" ) == $player_id )
	    { // This was the really last turn
	      $players = self::computePiecesNumbers();
	      foreach ($players as $id => $player)
		{
		  // Finally apply causality penalty
		  if ( $player['player_color'] == '6a548f'
		       && self::getGameStateValue( "pending_being" ) >= 0 ) {
		    self::DbQuery( "UPDATE score SET score_value=score_value-2 WHERE score_player_id=$id" );
		    self::DbQuery( "UPDATE player SET player_score=player_score-2 WHERE player_id=$id" );
		  }
		  if ( count($players) == 2 )
		    $tiebreaker = 1000*$player['upgraded'] + $player['pieces'];
		  else
		    {
		      $scores = self::getObjectListFromDb("SELECT score_value FROM score WHERE score_player_id=$id ORDER BY score_value DESC", true);
		      $tiebreaker = 100000*$scores[0]		      
		      + 1000*$player['upgraded']
		      + $player['pieces'];
		      if ( count($scores) > 2 )
			$tiebreaker = $tiebreaker + 10000000*$scores[1];
		    }
		  self::DbQuery( "UPDATE player SET player_score_aux=$tiebreaker WHERE player_id='$id'" );
		}
	      $this->gamestate->nextState( 'endGame' );
	      return;
	    }
	  else
	    {
	      if ( self::getGameStateValue( "last_player" ) == 0 )
		{ // If the end has not been triggered yet, check it
		  $end_score = self::endScore( $players );
		  if ( count($players) == 2 )
		    $max = self::getCollectionFromDB( "SELECT player_id id, player_score score FROM player", true );
		  else
		    $max = self::getCollectionFromDB( "SELECT score_player_id id, MAX(score_value) score FROM score GROUP BY score_player_id", true );
		  // Causality penalty
		  if ( self::getGameStateValue( "pending_being" ) >= 0 )
		    foreach ( $max as $id => $score )
		      if ( $players[$id]['player_color'] == '6a548f')
			$max[$id] = $score-2;
		  $max = max($max);
		  /* saved_score is for the unlikely case of a player summoning
		     Time elemental and then lowering his own score */
		  $saved_max = self::getUniqueValueFromDB(
				"SELECT MAX(player_saved_score) FROM player" );
 		  if ( $this->cards->countCardInLocation($school.'Deck') == 0
		       || $max >= $end_score || $saved_max >= $end_score )
		    {
		      self::setGameStateValue( "last_player", $player_id );
		      self::myNotifyAllPlayers( "lastTurn", clienttranslate( 'The end of the game has been triggered. ${player_name} will play the last turn.' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName()
						) );
		    }
		}
	      $player_id = self::activeNextPlayer();
	    }
	}

      self::giveExtraTime( $player_id );
      self::setGameStateValue( "remaining_actions", 2 );
      if ( self::getGameStateValue( "extra_turn" ) == 0 )
	{
	  $action_malus = self::getGameStateValue( "action_malus" );
	  if ( $action_malus != 0 && $action_malus != $player_id )
	    self::setGameStateValue( "remaining_actions", 1 );
	  else
	    self::setGameStateValue( "action_malus", 0 );
	}
      $this->gamestate->nextState( 'nextPlayer' );
    }

    function stInitFlare()
    { // Actually also used to trigger the second effect if both apply
      self::setGameStateValue( "has_skipped", 0 );
      self::setGameStateValue( "combat_moves", 0 );
      
      if ( self::getGameStateValue( "flare_upgraded" ) )
	{
	  self::setGameStateValue( "flare_upgraded", 0 );
	  self::setGameStateValue( "flare_rank", 0 );
	  self::setGameStateValue( "effect_number", -2 );
	  $this->gamestate->nextState( 'flareInitiated' );	  
	}
      elseif ( self::getGameStateValue( "flare_pieces" ) )
	{
	  self::setGameStateValue( "flare_pieces", 0 );
	  self::setGameStateValue( "flare_rank", 1 );
	  self::setGameStateValue( "effect_number", -2 );
	  $this->gamestate->nextState( 'flareInitiated' );	  
	}
      else
	{ // Flares over, discard the card
	  $player_id = self::getActivePlayerId();
	  $flares = self::getCardsOfTypeInLocation( "Flare", "hand", $player_id );
	  foreach ( $flares as $flare )
	    {
	      $this->cards->insertCardOnExtremePosition( $flare['id'], "discard_buffer", true );
	      self::myNotifyPlayer( $player_id, "discardCard", "", array( "card_id" => "flare_".$flare['type_arg'] ) );
	    }

	  if (self::getGameStateValue( "remaining_actions" ) > 0)
	    {
	      self::setGameStateValue( "deck_played", -2 );
	      self::setGameStateValue( "card_played", -1 );
	      self::saveGameState("actionChoice");
	      $this->gamestate->nextState( 'flaresDone' );
	    }
	  else
	    {
	      if ( self::getGameStateValue( 'game_form' ) == 1 )
		{
		  self::saveGameState("turnEndHF");
		  $this->gamestate->nextState( 'flaresTurnDoneHF' );
		}
	      else
		{
		  self::saveGameState("turnEndDM");
		  $this->gamestate->nextState( 'flaresTurnDoneDM' );
		}
	    }
	}
    }

    function stInitEffect()
    {
      self::setGameStateValue( "being_destroyed", 0 );
      self::setGameStateValue( "being_destroyed_legendary", 0 );
      self::setGameStateValue( "being_destroyed_common", 0 );
      self::setGameStateValue( "being_destroyed_heroic", 0 );
      self::setGameStateValue( "being_moved", 0 );
      self::setGameStateValue( "has_skipped", 0 );
      self::setGameStateValue( "being_upgraded_piece", 0 );
      self::setGameStateValue( "being_downgraded_piece", 0 );
      self::setGameStateValue( "being_placed_piece", 0 );
      self::setGameStateValue( "combat_moves", 0 );
      self::setGameStateValue( "piece_x", 0 );
      self::setGameStateValue( "piece_y", 0 );
      self::setGameStateValue( "piece_player", 0 );
      self::setGameStateValue( "piece_before_x", 0 );
      self::setGameStateValue( "piece_before_y", 0 );
      self::setGameStateValue( "piece_before_player", 0 );
      self::setGameStateValue( "bonus_improvisation", 0 );
      self::setGameStateValue( "option_chosen", 0 );
	  self::setGameStateValue( "last_impro", 0 );
	  self::setGameStateValue( "square_x", 0 );
	  self::setGameStateValue( "square_y", 0 );
      $this->gamestate->nextState( 'effectsInitiated' );
    }

    function isMandatory( $deck_id, $deck, $card_id, $card, $effect_number )
    {
      if ($deck_id != -1)
	{ /* Being : default is optional */
	  $mandatory = isset( $card['mandatory'] )
	    && in_array($effect_number, $card['mandatory'] );
	  
	  if ( ($deck == 'Northern' || $deck == 'Southern')
	       && $card_id == 5 ) /* Assassin */
	    {
	      $board = self::getBoard();
	      foreach ( self::markedSquare() as $marked )
		{
		  if ( self::emptySquare(
					 $board[$marked[0]][$marked[1]] ) )
		    $mandatory = false;
		}
	    }
	  if ( ($deck == 'Northern' || $deck == 'Southern')
	       && $card_id == 6 ) /* Time Mage */
	    {
	      $board = self::getBoard();
	      $player_id = self::getActivePlayerId();
	      foreach ( self::markedSquare() as $marked )
		{
		  if ( self::emptySquare($board[$marked[0]][$marked[1]]) ||
		       self::playerPiece($board[$marked[0]][$marked[1]],
		                        $player_id) )
		    $mandatory = false;
		}
	    }
	  if ( $deck == 'Etherweave' && $card_id == 6 ) /* Gate of Oblivion */
	    {
	      $board = self::getBoard();
	      $player_id = self::getActivePlayerId();
	      foreach ( self::markedSquare() as $marked )
		{
		  if ( self::emptySquare($board[$marked[0]][$marked[1]]) )
		    $mandatory = false;
		}
	    }
	}
      else
	{ /* Flare : default is mandatory */
	  $mandatory = ! ( isset( $card['skippable'] )
			   && in_array($effect_number,
				       $card['skippable'] ) );
	}
      return $mandatory;
    }

    function automateEffect( $effect, $card, $effect_number, $lastx, $lasty,
			     $player_id, $clickable, $card_x, $card_y )
    {
      switch ($effect)
	{
	case 'placePiece':
	  $rank = self::pieceToRank(
				$card['effecttargets'][$effect_number/2][1],
				$lastx, $lasty );
	  if ( $card['effecttargets'][$effect_number/2][0] == "playerPiece" )
	    {
	      self::placePiece( $lastx, $lasty, $player_id, $player_id, $rank);
	      return true;
	    }
	  $players = self::loadPlayersBasicInfos();
	  if ( $card['effecttargets'][$effect_number/2][0] == "enemyPiece"
	       && count($players) == 2 )
	    {
	      foreach ( $players as $id => $player )
		if ( $id != $player_id )
		  self::placePiece( $lastx, $lasty, $player_id, $id, $rank);
	      return true;
	    }
	  break;
	case 'movePiece':
	  list($p, $dx, $dy) = self::countPerformable( 
						$clickable[$lastx][$lasty] );
	  if ( $p == 1 )
	    {
	      $board = self::getBoard();
	      self::doMovePiece( $board, $lastx, $lasty, $dx, $dy,
				 $card['effecttargets'][$effect_number/2][2],
				 $card_x, $card_y, $player_id,
				 $card['effecttargets'][$effect_number/2][1] );
	      return true;
	    }
	  break;
	case 'performWarp':
	  $card_performed = array_pop($clickable);
	  self::performWarp( $card_performed['type_arg'] );
	  return true;
	default:
	  $board = self::getBoard();
	  self::mycall_user_func_array( $effect, array($board, $lastx, $lasty, $player_id, $card['effecttargets'][$effect_number/2] ) );
	  return true;
	}
      return false;
    }
    
    function stNextEffect()
    { // called for each effect of a card, including flares
      self::notifyPiecesDifferentials();
      $player_id = self::getActivePlayerId();
      $deck_id = self::getGameStateValue("deck_played");
      $deck = $this->decks[$deck_id];
      $card_id = self::getGameStateValue("card_played");
      $effect_number = self::getGameStateValue("effect_number");
      $card_x = self::getGameStateValue("card_x");
      $card_y = self::getGameStateValue("card_y");
      if ( $deck_id != -1 )
	{
	  $card = $this->card_contents[$deck][$card_id];
	}
      else
	{
	  $card = $this->flares[$card_id][self::getGameStateValue("flare_rank")];
	}

      if (count($card['effects']) <= $effect_number+1)
	{ /* Last effect */
	  if ($deck_id != -1)
	    {
	      if ( $card_id < 20 ) // Was an actual card
		{
		  $thecard = self::getUniqueCardOfType( $this->cards,
							$deck, $card_id );
          $thecard = self::enrichCards( array( $thecard['id'] => $thecard ) );
          $thecard = array_pop($thecard);
          
		  if ($thecard['location'] == 'hand')
		  {
		    // Even if it's frozen we put it in the discard for a while
		      $this->cards->insertCardOnExtremePosition(
			  $thecard['id'], "discard_buffer", true );
		      $to='discard';
		      if ( isset($card['frozentext']) )
		      {
			  if ( self::getGameStateValue( 'frozen_effect' ) < 0 )
			  {
			  // And then we change our mind
			  $this->cards->insertCardOnExtremePosition( $thecard['id'], "frozen", true );
		          $to='deck';
			  self::setGameStateValue( 'frozen_effect', $card_id );
			  self::notifyAllPlayers( 'frozenInPlay', '', array(
			      "i18n" => array( "frozentext" ),
			      "card_id" => $card_id,
			      "frozentext" => $card['frozentext']
			  ) );
			  }
			  else
			  {
			      self::myNotifyPlayer( $player_id, "discardCard",
						    "",
				array( "card_id" => $deck_id."_".$card_id,
				       "to" => 'discard',
                       "card" => $thecard ) );
			      self::saveGameState("frozenChoice");
			      $this->gamestate->nextState( 'chooseFrozen' );
			      return;
			  }
		      }
		  }
		  else // Special Hell Hound case
		      $to='deck';
		  self::myNotifyPlayer( $player_id, "discardCard", "",
				array( "card_id" => $deck_id."_".$card_id,
				       "to" => $to,
                       "card" => $thecard ) );
		}
	      else // Was a frozen or a warp effect
		{
		  if ( $deck == 'Everfrost' ) {
		    self::setGameStateValue( 'frozen_effect', -1 );
		    $thecard = self::getUniqueCardOfType( $this->cards,
							$deck, $card_id-20 );
		    $this->cards->InsertCardOnExtremePosition( $thecard['id'],
							       'discard_buffer',
							       true );
            $thecard = self::enrichCards( array( $thecard['id'] => $thecard ) );
            $thecard = array_pop($thecard);
		    self::myNotifyPlayer( $player_id, "discardFrozen", "",
                                  array( "card_id" => $card_id-20,
                                         "card" => $thecard ) );
		  }

          $thecardplayedid = self::getGameStateValue( 'to_be_discarded' );
          if ( $thecardplayedid >= 0 ) {
              // Was actually a copied warp effect, actual card needs discard
              $thecardplayed = self::getUniqueCardOfType( $this->cards,
                                              "Etherweave", $thecardplayedid );
              $this->cards->insertCardOnExtremePosition( $thecardplayed['id'],
                                                      "discard_buffer", true );
              $thecardplayed = self::enrichCards( array( $thecardplayed['id'] => $thecardplayed ) );
              $thecardplayed = array_pop($thecardplayed);
              self::myNotifyPlayer( $player_id, "discardCard", "",
                                    array( "card_id" => "6_".$thecardplayedid,
                                           "to" => "discard",
                                           "card" => $thecardplayed ) );
          }
          else // Warping and Thawing don't consume an action
              self::IncGameStateValue( 'remaining_actions', 1 );
		}
	      
	      $this->gamestate->nextState( 'effectsDone' );
	    }
	  else
	    $this->gamestate->nextState( 'nextFlare' );
	}
      elseif ($effect_number >= 0
	      && ! self::mycall_user_func_array(
				$card['effects'][$effect_number+1], array() ))
	{ /* Skip effect (condition not met) */
	  self::IncGameStateValue( "effect_number", 2 );
	  $effect = $card['effects'][$effect_number+2];
	  if ( substr( $effect, 0, 9) == 'orEffects' )
	    {
	      $neffects = intval( substr( $effect, 9, 1) );
	      self::IncGameStateValue( "effect_number", 2 * ($neffects-1) );
	    }
	  $this->gamestate->nextState( 'effectFalse' );	  
	}
      else
	{ /* Normal effect */
	  self::IncGameStateValue( "effect_number", 2 );
	  $effect_number += 2;
	  $effect = $card['effects'][$effect_number];

	  if ( $effect == 'orEffects2' || $effect == 'orEffects3' )
	    {
	      /* In this case we don't bother computing sets of targets :
		 - no auto
		 - some mandatory but not worth automating
		 - there's always some possible move
		 - piece shortage is treated in selectPieces
	      */
	      self::saveGameState( $effect );
	      $this->gamestate->nextState( $effect );
	      return;
	    }

	  if (array_key_exists("auto", $card)
	      && in_array($effect_number, $card["auto"]))
	    { /* Standard effects explicitly labeled as auto */
	      $type = 'autoEffect';
	      $board = self::getBoard();
	      $clickable = self::selectPiecesForEffect( $effect, $card_x, $card_y, $card['effecttargets'][$effect_number/2] );
	      
	      foreach ( $clickable as $x => $clickcolumn )
		foreach ( $clickcolumn as $y => $clickxy )
		  if ($clickxy)
		    self::mycall_user_func_array( $effect, array($board, $x, $y, $player_id, $card['effecttargets'][$effect_number/2] ) );
	    }
	  else
	    {
	      $type = self::effectType( $effect );
	      if ($type == 'autoEffect')
		/* Special auto effects */
		self::mycall_user_func_array( $effect, array($player_id,
				$card['effecttargets'][$effect_number/2] ) );
	      else
		{ /* Standard effect : check if it can be performed */
		  $effect = $card['effects'][$effect_number];
		  $clickable = self::selectPiecesForEffect( $effect, $card_x, $card_y, $card['effecttargets'][$effect_number/2] );
		  
		  if ( self::effectType($effect) == 'effectCard' ) {
		    $performable = count($clickable);
		    $lastx = 0; $lasty = 0;
		  }
		  else
		    list( $performable, $lastx, $lasty ) =
		      self::countPerformable( $clickable );
		  if ($performable == 0)
		    {
		      $type = 'autoEffect';
		      if ( $effect != 'putCardOnTop' )
			self::IncGameStateValue( "has_skipped", 1 );
		      self::myNotifyAllPlayers( "effectSkipped", clienttranslate( '${player_name} couldn\'t perform an effect' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName() ) );
		    }

		  $mandatory = self::isMandatory( $deck_id, $deck, $card_id,
						  $card, $effect_number );

		  // Most mandatory effects with one target are automated
		  if ( $performable == 1 && $mandatory
		       && $effect != 'chooseOption' )
		    {
		      if ( self::automateEffect( $effect, $card,
				$effect_number, $lastx, $lasty, $player_id,
				$clickable, $card_x, $card_y ) )
			$type = 'autoEffect';
		    }

		  // Piece shortage for storm Elemental
		  if ( $deck == "Legends" && $card_id == 10
		       && $effect == "chooseOption" )
		    {
		      $left = self::getObjectFromDb( "SELECT player_pieces_left pieces, player_legends_left legends FROM player WHERE player_id=$player_id" );
		      if ( $left['pieces'] == 0 )
			{
			  $type = 'autoEffect';
			  self::setGameStateValue( "option_chosen", 3 );
			  if ( $left['legends'] == 0 )
			    {
			      self::setGameStateValue( "option_chosen", 0 );
			      self::IncGameStateValue( "has_skipped", 1 );
			      self::myNotifyAllPlayers( "effectSkipped", clienttranslate( '${player_name} couldn\'t perform an effect' ), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName() ) );
			    }
			}
		    }
		}	      
	    }

	  if ( $type != 'autoEffect' )
	    self::saveGameState( $type );
	  // Dirty but no other place to put it
	  if ( $effect=="considerLegendSummoned"
	       && self::getPlayersNumber() > 2 )
	    {
	      self::saveGameState("chooseColorLegend");
	      $this->gamestate->nextState( 'chooseColorLegend' );
	    }
	  else
	    $this->gamestate->nextState( $type );
	}
    }    

    function stBrowseHistory()
    {
      // Just fetch the state from an appropriate "_saved" table and transition to it
      $step = self::getGameStateValue("step");
      $state = self::getUniqueValueFromDB( "SELECT state FROM state_saved WHERE step=$step" );
      $this->gamestate->nextState( $state );
    }

    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
      // Nothing to do here, but with the new "inactive player" policy,
      // it's better to place a few random chits... or maybe not...
    	$statename = $state['name'];
    	
        if ($state['type'] == "activeplayer") {
	  switch ($statename) {
	    /**** Random piece placement ???

	  case 'actionChoice':
	    // Place a piece on a random square
	    $board = self::getBoard();
	    $x = 0;
	    $y = 0;
	    while ( ! self::onBoard($x, $y) || $board[$x][$y]['player'] !== null )
	      {
		$x = bga_rand(1,9);
		$y = bga_rand(1,9);
	      }
	    self::playPiece( $x, $y );
	    break;

	  case 'pickPiece':
	    // Pick a random piece (common if available)
	    $board = self::getBoard();
	    $p = self::computePiecesDifferentials();
	    if ( $p[$active_player]['pieces'] > $p[$active_player]['upgraded'] )
	      $rank = 0;
	    else
	      $rank = 1;
	    $x = 0;
	    $y = 0;
	    while ( ! self::onBoard($x, $y) || $board[$x][$y]['player'] !== $active_player || $board[$x][$y]['rank'] > $rank )
	      {
		$x = bga_rand(1,9);
		$y = bga_rand(1,9);
	      }
	    self::pickPiece( $x, $y );
	    break;
	    ******/

	  default:
	    $this->gamestate->nextState( "zombiePass" );
	    break;
	  }
	  
	  return;
        }

        if ($state['type'] == "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $sql = "
                UPDATE  player
                SET     player_is_multiactive = 0
                WHERE   player_id = $active_player
            ";
            self::DbQuery( $sql );

            $this->gamestate->updateMultiactiveOrNextState( '' );
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
}
