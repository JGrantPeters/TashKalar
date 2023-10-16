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
 * material.inc.php
 *
 * TashKalarExpansions game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->schools_colors = array( "037cb1", "dc2515", "d6b156", "8ec459",
			       "f0f9ff", "f4913c", "6a548f" );
/* Must be in the same order of course ; Legends must be last */
$this->decks = array( clienttranslate("Northern"),
		      clienttranslate("Southern"),
		      clienttranslate("Highland"),
		      clienttranslate("Sylvan"),
		      clienttranslate("Everfrost"),
		      clienttranslate("Nethervoid"),
		      clienttranslate("Etherweave"),
		      "Legends" );
$this->decks[-1] = "Flare";
/**
 * -------------------------------------------
 * Card description specification and template
   -------------------------------------------

   The index of the card must be its position in the Deck image.

   "name" is the english name of the being (use clienttranslate)
   "rank" is respectively 0,1,2 for common, heroic, legendary
   "rotations" can be either :
        1 (many symmetry axes, pattern does not need any rotation nor symmetry, e.g. Master of Intrigue)
	2 (two axes, one rotation is enough, e.g. Swordmaster)
	4 (one axe only, all 4 rotations are needed, e.g. Messenger)
	8 (needs all combinations of symmetry and rotations, e.g. Bomb)
	Symmetries of the effect should be taken in consideration too, e.g. Summoner needs 4 rotations, not 2.
				 
   "pattern" is an array of triples array(x,y,r) where :
        (x,y) is the position of a pattern piece *relative to the being*
	r is its rank
	Tip : use indentation to visualize the pattern better.

   "text" is the english text of the card (use clienttranslate)

   "effects" is an array of strings alternating effects and conditions
        Available effects :
	    destroyPiece,
	    upgradePiece,
	    downgradePiece,
	    moveBeing,
	    nothing,
	    gainAction,
	    loseAction (for other players)
	    movePiece,
	    placePiece (see below for specification)
	    shootPieces (see below for specification)
	    convertPiece,
	    orEffects2 (choose between the 2 following effects, see Flares)
	    orEffects3 (choose between the 3 following effects, see Trees ;
	                in this case leave one empty effect after the 3rd)
	Available conditions :
	    ifDestroyed (the being has destroyed at least 1 piece, any player)
	    ifOversummonedEnemy
	    ifNotDestroyed
	    ifNotDestroyedAtAll (including oversummoning)
	    ifDestroyedLegend
	    ifDestroyedCommon
	    ifDestroyedHeroic
	    ifDestroyedTwo
	    always (no condition)
	    ifNotLastAction
	    ifNotMoved
	    ifMoved
	    ifNotSkipped
	    ifUpgradedPiece
	    ifDowngradedPiece
	    ifPlacedPiece
	    ifLastRankCommon
	    ifBeingOnGreenSquare
	    ifBeingOnRedSquare
	    ifPieceOnRedSquare
	    shootDistanceTwo
	    ifColorsLeft (special for Woodland Druid)
	    ifPlaced
	    ifPlacedTwo
   "effecttargets" is an array of specifications for each *effect*
        (no targets for conditions)

	For most effects the specification is a triplet array(who,rank,where) 
	which are three callback functions for selecting potential targets
	Available functions, more can be programmed :
	   who = enemyPiece,
	         playerPiece,
		 anyPiece,
	         anything (i.e. including an empty square),
		 emptySquare,
		 samePlayer (as the last piece played)
		 samePlayerBefore
		 otherEnemy (than last played)
		 otherLastTwoEnemies
	   rank = commonPiece, heroicPiece, legendaryPiece,
	          upgradedPiece, anyrank, nonLegendaryPiece,
		  sameRank (as the last piece played)
		  markedRank (board_marked is 1 for common, 2 for heroic)
	   where = diagonalNeighbour,
	           neighbour (of the being\'s current position),
	           theBeing (i.e. only him can be targeted),
		   anywhere,
		   markedSquare,
		   samePiece (the piece that was the subject of the last effect),
		   sameTwoPieces (ditto with one of the last 2 pieces)
		   anywhereButBeing,
		   anywhereButSamePiece,
		   anywhereButSameTwoPieces,
		   onOrAdjacentToRed,
		   onOrAdjacentToGreen,
		   adjacentToYourPieces,
		   adjacentColored,
		   pieceNeighbour (i.e. to the last piece played),
		   connectedTwo (Leviathan),
		   connectedThree (Leviathan),
		   distance2 (or less),
		   distance3 (or less),
		   leap2 (exactly),
		   nextPieceSameDirection,
		   sameDirection,
		   starMarked (any piece in any of the indicated directions,
		               see Bone Catapult)

	placePiece is always on empty squares, who and rank are used to
	   state the nature of the placed piece.

	convertPiece is always on enemy pieces, who is replaced
	   by the rank of the NEW piece.

	Targets for moveBeing are different : they are a triplet
	array( type, distance, criteria ) where :
	   type = "standard" or "combat" or a callable function
	          (oneCombatMoveOnly, masterOfIntrigueMoves)
	   distance = "move" or "leap"
	              or "charge" (i.e. any number of moves in any direction)
		      or a callable "where" function
	   criteria is a (possibly empty) array of who / rank conditions
	            !! for charge, criteria give the HALTING condition(s)
	
	movePiece requires a quadruplet
	array( piece, type, distance, criteria ) where :
	   piece is an array(who,rank,where) describing which pieces can be moved
	   type, distance, criteria are the same as for moveBeing

	shootPieces requires a triplet
	array( who, rank, criteria )
	   where the criteria give the BLOCKING condition(s)

   * The last four keys are optional. *
   "auto" is an array indicating which effects require no user input,
       e.g. Swordsmaster\'s upgrade.
       (effects are numbered from 0, don\'t list conditions here)
   "mandatory" is an array indicating which effects are mandatory but
       require some user input, e.g. Chronicler\'s upgrade.
   "marked" is a set of squares marked on the pattern that can not be 
       described as "neighbour" (e.g. Summoner).
       Same coding as the "pattern" key but without rank.
   "impro" is set if the player must be proposed every possible pattern,
       even if one is available without using improvisation


   * Template for copy-pasting *

	 => array(
		   "name" => clienttranslate(""),
		   "rank" => ,
		   "rotations" => ,
		   "pattern" => array(),
		   "text" => clienttranslate(""),
		   "effects" => array(),
		   "effecttargets" => array()
		   ),
 */

$this->imperial_contents = 
  array(
	0 => array(
		   "name" => clienttranslate("Swordmaster"),
		   "rank" => 0,
		   "rotations" => 2,
		   "pattern" => array( array(0,-1,0),
				       /* being */
				       array(0,1,0)),
		   "text" => clienttranslate("You may destroy 1 common enemy piece on a diagonally adjacent square. If you do, upgrade the Swordmaster."),
		   "effects" => array( "destroyPiece", "ifDestroyed", "upgradePiece" ),
		   "effecttargets" => array(
					    array( "enemyPiece", "commonPiece", "diagonalNeighbour" ),
					    array( "playerPiece", "commonPiece", "theBeing" )
					    ),
		   "auto" => array(2)
		   ),

	1 => array(
		   "name" => clienttranslate("Messenger"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array(/* being*/
				                array(1,1,0),
				                             array(2,2,0)),
		   "text" => clienttranslate("You may choose 1 of your non-legendary pieces and a direction. That piece may do any number of standard moves in that direction."),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "nonLegendaryPiece", "anywhere" ),
		       "standard", "charge", array( ) )
					    )
		   ),

	2 => array(
		   "name" => clienttranslate("Herald"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array( /* being */
				                   array(1,1,0), array(2,1,0), 
						   array(1,2,0), ),
		   "text" => clienttranslate("Do up to 2 moves, using only your pieces. These moves can only be onto empty squares."),
		   "effects" => array( "movePiece", "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
	     array( array( "playerPiece", "anyrank", "anywhere"),
		    "standard", "move", array( "emptySquare" ) ),
	     array( array( "playerPiece", "anyrank", "anywhere"),
		    "standard", "move", array( "emptySquare" ) ) )
		   ),

	3 => array(
		   "name" => clienttranslate("Bomb"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array(/* being */
				                 array(1,1,0),
				                 array(1,2,0)),
		   "text" => clienttranslate("If summoning the Bomb is your last action this turn, nothing happens ; otherwise, destroy the Bomb and all common pieces adjacent to it."),
		   "effects" => array( "nothing", "ifNotLastAction", "destroyPiece", "ifNotLastAction", "destroyPiece"),
		   "effecttargets" => array( array(),
					     array( "playerPiece", "commonPiece", "theBeing" ),
					     array( "anyPiece", "commonPiece", "neighbour" )
					     ),
		   "auto" => array(2, 4)
		   ),

	4 => array(
		   "name" => clienttranslate("Chronicler"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array( array(0,-1,0),
			array(-1,0,0), /* being */   array(1,0,0),),
		   "text" => clienttranslate("Upgrade 1 of your common pieces other than the Chronicler. Then that piece may do a standard move."),
		   "effects" => array( "upgradePiece", "ifUpgradedPiece", "movePiece" ),
		   "effecttargets" => array(
		    array( "playerPiece", "commonPiece", "anywhereButBeing" ),
		    array( array( "playerPiece", "heroicPiece", "samePiece" ),
				   "standard", "move", array() )
					    ),
		   "mandatory" => array(0)
		   ),

	5 => array(
		   "name" => clienttranslate("Assassin"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-1,-3,0),
		       array(-2,-2,0),
       array(-3,-1,0)                  /* marked */
				                      /* being */ ),
		   "text" => clienttranslate("Destroy any piece on the marked square. If the marked square was empty, the Assassin may move onto it."),
		   /* Twisted way to choose if more than one pattern matches */
		   "effects" => array( "destroyPiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifNotDestroyed")),
				       "moveBeing" ),
		   "effecttargets" => array( array( "anything", "anyrank", "markedSquare" ),
					     array( "standard", "samePiece", array( "emptySquare" ) )),
		   "marked" => array( array(-1,-1) ),
		   "mandatory" => array(0),
		   "auto" => array(2),
		   "impro" => true
		   /** First version : implicit killing if skipped move
		   "effects" => array( "moveBeing", "ifNotMoved", "destroyPiece" ),
		   "effecttargets" => array( array( "standard", "markedSquare", array( "emptySquare" ) ),
					     array( "anyPiece", "anyrank", "markedSquare" )),
		   "marked" => array( array(-1,-1) ),
		   "mandatory" => array(2)
		   **/
		   ),


	6 => array(
		   "name" => clienttranslate("Time Mage"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-1,0,0), /* being */
		       array(-2,1,0),                array(0,1,0),
				       array(-1,2,0),
),
		   "text" => clienttranslate( "Gain an action. If there is an enemy piece on the marked square, destroy it." ),
		   "effects" => array( "gainAction", "always", "destroyPiece" ),
		   "effecttargets" => array( array(),
					     array( "enemyPiece", "anyrank", "markedSquare" ) ),
		   "marked" => array( array(-1,1) ),
		   "mandatory" => array(2),
		   "impro" => true
		   ),

	7 => array(
		   "name" => clienttranslate("Summoner"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(  /* being */
			array(-1,1,0), array(0,1,0), array(1,1,0) ),
		   "text" => clienttranslate("You may place up to 2 common pieces of your color on empty marked squares."),
		   "effects" => array( "placePiece", "ifNotSkipped", "placePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "markedSquare" ),
			array( "playerPiece", "commonPiece", "markedSquare" )
					    ),
		   "marked" => array(
	array(-2,-2,0),               array(0,-2,0),             array(2,-2,0),
		      array(-1,-1,0), array(0,-1,0), array(1,-1,0),
	array(-2,0,0), array(-1,0,0), /* being */ array(1,0,0), array(2,0,0)
				     ),
		   /* Multiple patterns should be covered correctly now */
		   "impro" => true
		   ),

	8 => array(
		   "name" => clienttranslate("Hypnotist"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(     array(1,-1,0),
		array(-1,0,0), /* being */ array(1,0,0),
		array(-1,1,0)
					   ),
		   "text" => clienttranslate("You may choose up to 3 common or up to 2 heroic pieces in one enemy color: Do 1 combat move with each."),
		   "effects" => array( "movePiece",
				       "ifNotSkipped", "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankCommon")),
				       "movePiece" ),
		   "effecttargets" => array(
	array( array( "enemyPiece", "nonLegendaryPiece", "anywhere" ),
	       "combat", "move", array() ),
	array( array( "samePlayer", "sameRank", "anywhereButSamePiece" ),
	       "combat", "move", array() ),
	array( array( "samePlayer", "sameRank", "anywhereButSameTwoPieces" ),
	       "combat", "move", array() )
					    )
		   ),

	9 => array(
		   "name" => clienttranslate("Cannon"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1,0,0), /**/array(0,0,0)/**/,
				       array(-2,1,0) ),
		   "text" => clienttranslate("You may choose 1 of the indicated directions: Destroy all common pieces in that direction."),
		   "effects" => array( "shootPieces" ),
		   "effecttargets" => array( array( "anyPiece", "commonPiece",
						    array() ) ),
		   "marked" => array( array(-1,-1), array(0,-1), array(1,-1),
				                    /* being */  array(1,0),
				      array(-1,1),  array(0,1),  array(1,1) ),
		   "impro" => true
		   ),

	10 => array(
		   "name" => clienttranslate("Champion"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1,0,0), /* being */
				       array(-1,1,0), array(0,1,0), 
				                      array(0,2,0) ),
		   "text" => clienttranslate("You may destroy 1 adjacent enemy piece. If that piece was legendary, you also destroy the Champion and gain an action."),
		   "effects" => array( "destroyPiece", "ifDestroyedLegend", "destroyPiece", "ifDestroyedLegend", "gainAction" ),
		   "effecttargets" => array( array( "enemyPiece", "anyrank", "neighbour" ),
					     array( "playerPiece", "heroicPiece", "theBeing" ),
					     array() ),
		   "auto" => array(2)
		   ),

	11 => array(
		   "name" => clienttranslate("Infantry Captain"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */
           array(-2,1,0), array(-1,1,0),         array(1,1,0), array(2,1,0) ),
		   "text" => clienttranslate("Do up to 2 combat moves, using your pieces other than the Infantry Captain."),
		   "effects" => array( "movePiece", "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
	     array( array( "playerPiece", "anyrank", "anywhereButBeing"),
		    "combat", "move", array( ) ),
	     array( array( "playerPiece", "anyrank", "anywhereButBeing"),
		    "combat", "move", array( ) ) )
		   ),

	12 => array(
		   "name" => clienttranslate("Cavalry Captain"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */
			array(-1,1,0),             array(1,1,0),
			array(-1,2,0),             array(1,2,0)),
		   "text" => clienttranslate("You may choose 1 of your pieces other than the Cavalry Captain: You may do up to 1 combat move and up to 2 standard moves with it (in any order)."),
		   "effects" => array( "movePiece", "ifNotSkipped", "movePiece", "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "anyrank", "anywhereButBeing"),
		       "combat", "move", array() ),
		array( array( "playerPiece", "anyrank", "samePiece"),
		       "oneCombatMoveOnly", "move", array() ),
		array( array( "playerPiece", "anyrank", "samePiece"),
		       "oneCombatMoveOnly", "move", array() )
					    )
		   ),

	13 => array(
		    "name" => clienttranslate("Gryphon Rider"),
		    "rank" => 1,
		    "rotations" => 8,
		    "pattern" => array( array(-1,0,0),/* being */
			   array(-2,1,0),             array(0,1,0),
			                                         array(1,2,0)),
		    "text" => clienttranslate("The Gryphon Rider may do a combat leap. If she does, you may then downgrade her and place 1 common piece of your color on an empty adjacent space."),
		    "effects" => array( "moveBeing", "ifMoved", "downgradePiece", "ifDowngradedPiece", "placePiece" ),
		    "effecttargets" => array( 
			 array( "combat", "leap", array() ),
			 array( "playerPiece", "heroicPiece", "theBeing" ),
			 array( "playerPiece", "commonPiece", "neighbour" ),
					      ),
		    "mandatory" => array(4)
		   ),

	14 => array(
		   "name" => clienttranslate("Knight"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
		       array(-1,1,0), array(0,1,0), array(1,1,0),
		       array(-1,2,0), ),
		   "text" => clienttranslate("The Knight may do up to 3 combat moves. You cannot destroy common pieces with these moves."),
		   "effects" => array( "moveBeing", "ifNotSkipped",
				       "moveBeing", "ifNotSkipped",
				       "moveBeing" ),
		   "effecttargets" => array(
	    				    array( "combat", "move",
	    		 array(call_user_func(array($this, "orcond"),
	    array($this, "heroicPiece"), array($this, "emptySquare") ))),
	    				    array( "combat", "move",
	    		 array(call_user_func(array($this, "orcond"),
	    array($this, "heroicPiece"), array($this, "emptySquare") ))),
	    				    array( "combat", "move",
	    		 array(call_user_func(array($this, "orcond"),
	    array($this, "heroicPiece"), array($this, "emptySquare") )))
					    )
		    ),

	15 => array(
		    "name" => clienttranslate("High Priestess"),
		    "rank" => 1,
		    "rotations" => 4,
		    "pattern" => array(        /**/ array(0,0,0), /**/

				       array(-1,2,0),           array(1,2,0)),
		    "text" => clienttranslate("The High Priestess may do 1 standard move. Whether she moves or not, you may then upgrade 1 common piece adjacent to her."),
		    "effects" => array( "moveBeing", "always", "upgradePiece" ),
		    "effecttargets" => array(
					     array( "standard", "move", array() ),
					     array( "anyPiece", "commonPiece", "neighbour" ),
					     ),
		    ),

	16 => array(
		   "name" => clienttranslate("Master of Intrigue"),
		   "rank" => 1,
		   "rotations" => 1,
		   "pattern" => array( array(-1,-1,0),        array(1,-1,0),
				                    /* being */
				       array(-1,1,0),        array(1,1,0)),
		   "text" => clienttranslate("Do up to 3 moves: standard moves with the Master of Intrigue and/or combat moves using non-legendary pieces that were used to summon him."),
		   "effects" => array( "movePiece", "ifNotSkipped",
			           "movePiece", "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
		array(array( "anyPiece", "nonLegendaryPiece",
			     call_user_func(array($this, "union"),
	    array($this, "theBeing"), array($this, "markedSquare") ) ),
		      "masterOfIntrigueMoves", "move", array() ),
		array(array( "anyPiece", "nonLegendaryPiece",
			     call_user_func(array($this, "union"),
		array($this, "theBeing"), array($this, "markedSquare"),
					    array($this, "samePiece") ) ),
		      "masterOfIntrigueMoves", "move", array() ),
		array(array( "anyPiece", "nonLegendaryPiece",
			     call_user_func(array($this, "union"),
	    array($this, "theBeing"), array($this, "markedSquare"),
					    array($this, "sameTwoPieces") ) ),
		      "masterOfIntrigueMoves", "move", array() ),
					    ),
		   "marked" => array( array(-1,-1,0),        array(1,-1,0),
				                    /* being */
				       array(-1,1,0),        array(1,1,0)),
		    ),

	17 => array(
		   "name" => clienttranslate("Gun Tower"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(/* being */
				                 array(0,1,0), array(1,1,0),
						 array(0,2,0), array(1,2,0)),
		   "text" => clienttranslate("You may choose 1 of the indicated directions: Destroy the first 2 pieces in that direction. A legendary piece cannot be destroyed and stops the shot."),
		   "effects" => array( "shootPieces" ),
		   "effecttargets" => array(
		    array( "anyPiece", "nonLegendaryPiece",
			   array("ifDestroyedTwo", "legendaryPiece") ) ),
		   "marked" => array( array(-1,0), /*being*/ array(1,0) ),
		   "impro" => true
		   )
	);

$this->highland_contents = 
  array(
	0 => array(
		   "name" => clienttranslate("Wild Eagle"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array(   /* being */
		          array(-1, 1, 0),          array(1, 1, 0)
		    ),
		   "text" => clienttranslate("The Wild Eagle may do a combat leap to any square of the board."),
		   "effects" => array( "moveBeing" ),
		   "effecttargets" => array(
						array( "combat", "leap", array() )
			)			
		),

	1 => array(
		   "name" => clienttranslate("Clan Axeman"),
		   "rank" => 0,
		   "rotations" => 2,
		   "pattern" => array(                 array(1, -1, 0),
		                             /* being */
							array(-1, 1, 0)
		    ),
		   "text" => clienttranslate("You may destroy 1 non-legendary piece on an orthogonally adjacent square."),
		   "effects" => array( "destroyPiece" ),
		   "effecttargets" => array(
		array( "anyPiece", "nonLegendaryPiece", "orthogonalNeighbour" )
			)
		),

	2 => array(
		   "name" => clienttranslate("Clan Healer"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array(                array(1,-1,0),
			   array(-1,0,0), /* being */ array(1,0,0) ),
		   "text" => clienttranslate("You may place up to 2 common pieces of your color. Each must be placed on an empty square adjacent to a green square."),
		   "effects" => array( "placePiece", "ifNotSkipped", "placePiece" ),
		   "effecttargets" => array(
		array( "playerPiece", "commonPiece", "adjacentToGreen" ),
		array( "playerPiece", "commonPiece", "adjacentToGreen" ) )
		   ),

	3 => array(
		   "name" => clienttranslate("Dire Wolf"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array(                  /* being */
		                       array(-1, 1, 0), array(0, 1, 0),
					array(-2, 2, 0)
		   ),
		   "text" => clienttranslate("The Dire Wolf may do up to 2 combat moves."),
		   "effects" => array( "moveBeing", "ifNotSkipped", "moveBeing" ),
		   "effecttargets" => array(
						array( "combat", "move", array() ),
				        array( "combat", "move", array() )
			)
		),

	4 => array(
		   "name" => clienttranslate("Ritual Keeper"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array(                 array(0, -1, 0),
		                       array(-1, 0, 0), /* being */
		   ),
		   "text" => clienttranslate("If the Ritual Keeper was summoned on a green square, upgrade one of your common pieces; if on a red square, you may do 1 combat move with 1 of your pieces."),
		   "effects" => array( "nothing", "ifBeingOnGreenSquare", "upgradePiece", "ifBeingOnRedSquare", "movePiece"),
		   "effecttargets" => array(
					    array(),
						array( "playerPiece", "commonPiece", "anywhere" ),
						array( array( "playerPiece", "anyrank", "anywhere" ),
							   "combat", "move", array() )
			),
		   "mandatory" => array(2)
		),

	5 => array(
		   "name" => clienttranslate("Eagle Lord"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(array(-2, 0, 0),      /* being */       array(2, 0, 0),
		                               array(-1, 1, 0),      array(1, 1, 0)
		   ),
		   "text" => clienttranslate("The Eagle Lord may do a combat leap to any square of the board."),
		   "effects" => array( "moveBeing" ),
		   "effecttargets" => array(
						array( "combat", "leap", array() )
			)
		),

	6 => array(
		   "name" => clienttranslate("Wolf Rider"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(/* being */
		                      array(0, 1, 0), array(1, 1, 0), array(2, 1, 0),
					          array(0, 2, 0)
		   ),
		   "text" => clienttranslate("The Wolf Rider may do up to 2 combat moves."),
		   "effects" => array( "moveBeing", "ifNotSkipped", "moveBeing" ),
		   "effecttargets" => array(
						array( "combat", "move", array() ),
				        array( "combat", "move", array() )
			)
		),

	7 => array(
		   "name" => clienttranslate("Blood Shaman"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(                  array(1,-1,0),
					    /* being */ array(1,0,0),
			    array(-1,1,0), array(0,1,0) ),
		   "text" => clienttranslate("Destroy 1 non-legendary piece. If it was on a red square, destroy all common pieces adjacent to the red square."),
		   "effects" => array( "destroyPiece",
				       "ifPieceOnRedSquare", "destroyPiece" ),
		   "effecttargets" => array(
			array( "anyPiece", "nonLegendaryPiece", "anywhere" ),
			array( "anyPiece", "commonPiece", "pieceNeighbour" )
					    ),
		   "mandatory" => array(0),
		   "auto" => array(2)
		   ),

	8 => array(
		   "name" => clienttranslate("War Drummer"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /**/ array(0,0,0), /**/ array(1,0,0),
				            array(0,1,0),      array(1,1,0) ),
		   "text" => clienttranslate("Do either 1 combat move or up to 2 standard moves, using your pieces other than the War Drummer."),
		   "effects" => array( "movePiece",
				       call_user_func(array($this, "andcond"),
	      array($this, "ifNotSkipped"), array($this, "ifNotCombatMoved")),
				       "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "anyrank", "anywhereButBeing"),
		       "combat", "move", array() ),
		array( array( "playerPiece", "anyrank", "anywhereButBeing"),
		       "standard", "move", array() )
					    )
		   ),

	9 => array(
		   "name" => clienttranslate("Hill Giant"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(               /* being */        
		                                     array(0, 1, 0),
							array(-1, 2, 0), array(0, 2, 0), array(1, 2, 0)
		   ),
		   "text" => clienttranslate("Destroy all non-legendary pieces on orthogonally adjacent squares."),
		   "effects" => array( "destroyPiece" ),
		   "effecttargets" => array(array( "anyPiece", "nonLegendaryPiece", "orthogonalNeighbour" )
		   ),
		   "auto" => array(0)
		),

	10 => array(
		   "name" => clienttranslate("Warlord"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */
			array(-1,1,0), array(0,1,0), array(1,1,0), 
	  array(-2,2,0),                                         array(2,2,0)),
		   "text" => clienttranslate("Do up to 3 combat moves, using your pieces. If you do all 3, at least one has to be with the Warlord."),
		   "effects" => array( "movePiece",
				       "ifNotSkipped", "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifMoved")),
				       "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifNotMoved")),
				       "movePiece" ),
		   "effecttargets" => array(
			array( array( "playerPiece", "anyrank", "anywhere" ),
			       "combat", "move", array() ),
			array( array( "playerPiece", "anyrank", "anywhere" ),
			       "combat", "move", array() ),
			array( array( "playerPiece", "anyrank", "anywhere" ),
			       "combat", "move", array() ),
			array( array( "playerPiece", "anyrank", "theBeing" ),
			       "combat", "move", array() ) )
		   ),

	11 => array(
		   "name" => clienttranslate("War Summoner"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-1,0,0), /* being */ array(1,0,0),
				                     array(0,1,0),
				                     array(0,2,0) ),
		   "text" => clienttranslate("Gain an action. For the pattern of the next being you summon this turn, you may use one enemy piece as though it were yours."),
		   "effects" => array( "gainAction", "always", "gainBonusImprovisation" ),
		   "effecttargets" => array( array(), array() )
		   ),

	12 => array(
		   "name" => clienttranslate("Ritual Master"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-1,-1,0),
				                     /* being */
				       array(-1,1,0),           array(1,1,0) ),
		   "text" => clienttranslate("If the Ritual Master was summoned on a green square, gain 2 actions; if on a red square, you may destroy 1 heroic and/or 1 common piece anywhere on the board."),
		   "effects" => array( "nothing",
				       "ifBeingOnGreenSquare", "gainAction",
				       "ifBeingOnGreenSquare", "gainAction",
				       "ifBeingOnRedSquare", "destroyPiece",
				       "ifBeingOnRedSquare", "destroyPiece" ),
		   "effecttargets" => array( array(), array(), array(),
				array( "anyPiece", "heroicPiece", "anywhere" ),
				array( "anyPiece", "commonPiece", "anywhere" ))
		   ),

	13 => array(
		   "name" => clienttranslate("Legend Slayer"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
				      array(0,1,0), array(1,1,0), 
				      array(0,2,0),             array(2,2,0) ),
		   "text" => clienttranslate("You may destroy 1 legendary or up to 2 non-legendary pieces on diagonally adjacent squares."),
		   "effects" => array( "destroyPiece",
				       call_user_func(array($this, "andcond"),
	array($this, "ifNotSkipped"), array($this, "ifNotDestroyedLegend")),
				       "destroyPiece" ),
		   "effecttargets" => array(
		array( "anyPiece", "anyrank", "diagonalNeighbour" ),
		array( "anyPiece", "nonLegendaryPiece", "diagonalNeighbour" )
					    )
		   ),

	14 => array(
		   "name" => clienttranslate("Mountain Troll"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
			array(-1,1,0), array(0,1,0),
			               array(0,2,0), array(1,2,0) ),
		   "text" => clienttranslate("Destroy all common enemy pieces on adjacent squares. If you destroy at least 2 this way, gain an action."),
		   "effects" => array( "destroyPiece", "ifDestroyedTwo",
				       "gainAction" ),
		   "effecttargets" => array(
			array( "enemyPiece", "commonPiece", "neighbour" ),
			array() ),
		   "auto" => array(0)
		   ),

	15 => array(
		   "name" => clienttranslate("Hungry Bear"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */ array(1,0,0),
				       array(0,1,0), array(1,1,0) ),
		   "text" => clienttranslate("The hungry bear may do up to 2 standard moves. If it moves onto an empty square, it stops moving."),
		   "effects" => array( "moveBeing", "ifDestroyed",
				       "moveBeing" ),
		   "effecttargets" => array(
				    array( "standard", "move", array() ),
				    array( "standard", "move", array() ) )
		   ),

	16 => array(
		   "name" => clienttranslate("Werewolf"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-2,-1,0),
			array(-3,0,0),                          /* being */
				       array(-2,1,0) ),
		   "text" => clienttranslate("If the Werewolf was summoned on a non-central red or green square, it may do up to 3 combat moves. Otherwise it may do 1 standard move."),
		   "effects" => array( "moonShine",
				       "ifNotFullmoon", "moveBeing",
				       call_user_func(array($this, "andcond"),
	array($this, "ifNotSkipped"), array($this, "ifFullmoon")),
				       "moveBeing",
				       call_user_func(array($this, "andcond"),
	array($this, "ifNotSkipped"), array($this, "ifFullmoon")),
				       "moveBeing",
				       call_user_func(array($this, "andcond"),
	array($this, "ifNotSkipped"), array($this, "ifFullmoon")),
				       "moveBeing" ),
		   "effecttargets" => array( array(),
				     array( "standard", "move", array() ),
				     array( "combat", "move", array() ),
				     array( "combat", "move", array() ),
				     array( "combat", "move", array() ) )
		   ),

	17 => array(
		   "name" => clienttranslate("Clan Guardian"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(   array(-2, 0, 0),array(-1, 0, 0),/* being */  array(1, 0, 0)
		   ),
		   "text" => clienttranslate("You may upgrade 1 common piece used to summon the Clan Guardian."),
		   "effects" => array("upgradePiece"),
		   "effecttargets" => array(
				array( "anyPiece", "commonPiece", "markedSquare" )
		    ),
		   "marked" => array (
			array(-2, 0, 0),array(-1, 0, 0),/* being */  array(1, 0, 0)
				      ),
		   "impro" => true
		)				
    );

$this->sylvan_contents = 
  array(
	0 => array(
		   "name" => clienttranslate("Sapling"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array( /* being */
				       array(0,1,0),
				       array(0,2,0) ),
		   "text" => clienttranslate("Upgrade the Sapling after summoning it."),
		   "effects" => array( "upgradePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "theBeing" ) ),
		   "auto" => array(0)
		   ),

	1 => array(
		   "name" => clienttranslate("Kiskin Farseeders"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array( /* being */ array(1,0,0),
			array(-1,1,0) ),
		   "text" => clienttranslate("You may place 1 common piece of your color on an empty square adjacent to one of your pieces."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => array(
		array( "playerPiece", "commonPiece", "adjacentToYourPieces" ) )
		   ),

	2 => array(
		   "name" => clienttranslate("Charging Buck"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array( /* being */
			array(-1,1,0), array(0,1,0),
			array(-1,2,0) ),
		   "text" => clienttranslate("The Charging Buck may do up to 2 combat leaps, each of distance exactly 2."),
		   "effects" => array( "moveBeing", "ifNotSkipped",
				       "moveBeing"),
		   "effecttargets" => array(
					array( "combat", "leap2", array() ),
					array( "combat", "leap2", array() ) )
		   ),

	3 => array(
		   "name" => clienttranslate("Forest Wardens"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array(
			array(-2,0,0),            /* being */ array(1,0,0) ),
		   "text" => clienttranslate("Destroy 1 common piece of an opponent with more common pieces than you. Destroy 1 heroic piece of an opponent with more upgraded pieces than you."),
		   "effects" => array( "destroyPiece", "always",
				       "destroyPiece" ),
		   "effecttargets" => array(
		array( "moreCommonOpponent", "commonPiece", "anywhere" ),
		array( "moreUpgradedOpponent", "heroicPiece", "anywhere" ) ),
		   "mandatory" => array(0, 2)
		   ),

	4 => array(
		   "name" => clienttranslate("Naiad"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array( /* being */
	array(-2,1,0), array(-1,1,0), array(0,1,0) ),
		   "text" => clienttranslate("You may choose up to 2 of your pieces adjacent to the Naiad and do 1 standard move with each."),
		   "effects" => array( "movePiece", "ifNotSkipped",
				       "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "anyrank", "markedSquare" ),
		       "standard", "move", array() ),
		array( array( "playerPiece", "anyrank",
			      call_user_func(array($this, "intersection"),
	array($this, "markedSquare"), array($this, "anywhereButSamePiece") )
			      ),
		       "standard", "move", array() ) ),
		   "marked" => array( array(-1,-1), array(0,-1), array(1,-1),
				      array(-1,0),  /* being */  array(1,0),
				      array(-1,1),  array(0,1),  array(1,1) )
		   ),

	5 => array(
		   "name" => clienttranslate("Kiskin Spirit"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-2,0,0),      /* being */
				       array(-2,1,0),     array(0,1,0)),
		   "text" => clienttranslate("You may choose a card from your discard pile and put it on top of your deck. If you do, or if your discard pile was empty, draw 1 extra card from your deck at the end of this turn."),
		   "effects" => array( "putCardOnTop", "ifNotSkipped",
				       "drawExtra" ),
		   "effecttargets" => array( array(),
					     array( 1, 0 ) )
		   ),

	6 => array(
		   "name" => clienttranslate("Dryad"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1, -1, 0),
				                   /**/ array(0, 0, 0), /**/
				                        array(0, 1, 0) ),
		   "text" => clienttranslate("On one of the adjacent squares, you may convert 1 non-legendary enemy piece to your common piece."),
		   "effects" => array( "convertPiece" ),
		   "effecttargets" => array(
		array( "commonPiece", "nonLegendaryPiece", "neighbour" )
					    )
		   ),

	7 => array(
		   "name" => clienttranslate("Centaur Spearman"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
				     array(0,1,0), array(1,1,0), array(2,1,0),
				                                 array(2,2,0)),
		   "text" => clienttranslate("The Centaur Spearman may do 1 combat move. If he does, destroy the next piece in the same direction, unless it is legendary."),
		   "effects" => array( "moveBeing", "ifNotSkipped",
				       "destroyPiece" ),
		   "effecttargets" => array(
	array( "combat", "move", array() ),
	array( "anyPiece", "nonLegendaryPiece", "nextPieceSameDirection" ) ),
		   "auto" => array(2)
		   ),

	8 => array(
		   "name" => clienttranslate("Centaur Chieftain"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(
			array(-2,-1,0),
			              array(-1,0,0), /* being */
				                   array(0,1,0),
			                                       array(1,2,0)),
		   "text" => clienttranslate("The Centaur Chieftain may do 1 combat move. If he does, you may do up to 3 combat moves in the same direction, using other pieces of yours."),
		   "effects" => array( "moveBeing",
				       "ifNotSkipped", "movePiece",
				       "ifNotSkipped", "movePiece",
				       "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
		array( "combat", "move", array() ),
		array( array( "playerPiece", "anyrank", "anywhereButBeing" ),
		       "combat", "sameDirection", array() ),
		array( array( "playerPiece", "anyrank", "anywhereButBeing" ),
		       "combat", "sameDirection", array() ),
		array( array( "playerPiece", "anyrank", "anywhereButBeing" ),
		       "combat", "sameDirection", array() )
					    )
		   ),

	9 => array(
		   "name" => clienttranslate("Unicorn"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1, 0, 0), /* being */
		      array(-2, 1, 0), array(-1, 1, 0),
		      array(-2, 2, 0) ),
		   "text" => clienttranslate("Choose one: Either the Unicorn may do 1 combat move, or you gain 1 action."),
		   "effects" => array( "orEffects2", "moveBeing", "gainAction" ),
		   "effecttargets" => array(
					    array( "combat", "move", array() ),
					    array()
					    )
		   //,		   "mandatory" => array(0)
		   ),

	10 => array(
		   "name" => clienttranslate("Sylvan Queen"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
				      array(0,1,0),             array(2,1,0),
				                   array(1,2,0),
				                   array(1,3,0) ),
		   "text" => clienttranslate("You may convert 1 non-legendary enemy piece on a diagonally adjacent square to your piece of the same rank."),
		   "effects" => array( "convertPiece" ),
		   "effecttargets" => array(
		array( "sameRank", "nonLegendaryPiece", "diagonalNeighbour" ) )
		   ),

	11 => array(
		   "name" => clienttranslate("Sylvan Princess"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
				      array(0,1,0),
				                   array(1,2,0),
				      array(0,3,0) ),
		   "text" => clienttranslate("You may convert 1 common enemy piece on a diagonally adjacent square to your common piece."),
		   "effects" => array( "convertPiece" ),
		   "effecttargets" => array(
		array( "commonPiece", "commonPiece", "diagonalNeighbour" ) )
		   ),

	12 => array(
		   "name" => clienttranslate("Woodland Druid"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(-1,-1,0),        array(1,-1,0),
				                    /* being */
				                   array(0,1,0) ),
		   "text" => clienttranslate("Upgrade 1 common piece of each color."),
		   "effects" => array( "upgradePiece", "always",
				       "upgradePiece", "ifColorsLeft",
				       "upgradePiece", "ifColorsLeft",
				       "upgradePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ),
			array( "enemyPiece", "commonPiece", "anywhere" ),
			array( "otherEnemy", "commonPiece", "anywhere" ),
			array( "otherLastTwoEnemies", "commonPiece", "anywhere" ) ),
		   "mandatory" => array(0,2,4,6)
		   ),

	13 => array(
		   "name" => clienttranslate("Forest Ancient"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(0,-1,0),
			 array(-1,0,0), /* being */ array(1,0,0),
			 array(-1,1,0) ),
		   "text" => clienttranslate("Place 2 enemy common pieces on empty squares up to distance 2. Then destroy 2 common pieces of the same color or colors up to distance 3."),
		   "effects" => array( "placePiece", "always",
				       "placePiece", "ifPlaced",
				       "destroyPiece", "ifPlacedTwo",
				       "destroyPiece" ),
		   "effecttargets" => array(
			array( "enemyPiece", "commonPiece", "distance2" ),
			array( "enemyPiece", "commonPiece", "distance2" ),
		array( "samePlayerBefore", "commonPiece", "distance3" ),
		array( "samePlayerBefore", "commonPiece", "distance3" ),
					    ),
		   "mandatory" => array(0,2,4,6)
		   ),

	14 => array(
		   "name" => clienttranslate("Forest Mystic"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( array(0,-1,0),
			array(-1,0,0), /* being */
				                     array(1,1,0) ),
		   "text" => clienttranslate("At the end of this turn, draw 1 extra card from your deck and 1 extra card from the legendary deck."),
		   "effects" => array( "drawExtra" ),
		   "effecttargets" => array( array( 1, 1 ) )
		   ),

	15 => array(
		   "name" => clienttranslate("Kiskin Leafsplitter"),
		   "rank" => 1,
		   "rotations" => 1,
		   "pattern" => array( array(0,-1,0),
			array(-1,0,0), /* being */  array(1,0,0),
				       array(0,1,0) ),
		   "text" => clienttranslate("Up to 2 times, you may choose a diagonal direction and destroy the first piece in that direction."),
		   "effects" => array( "shootPieces", "ifNotSkipped",
				       "shootPieces" ),
		   "effecttargets" => array(
		array( "anyPiece", "anyrank", array("ifDestroyed") ),
		array( "anyPiece", "anyrank", array("ifDestroyedTwo") ) ),
		   "marked" => array( array(-1,-1),           array(1,-1),
				                   /* being */
				      array(-1,1),            array(1,1) )
		   ),

	16 => array(
		   "name" => clienttranslate("Kiskin Boughrunner"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */
			array(-1,1,0), array(0,1,0), array(1,1,0),
			               array(0,2,0) ),
		   "text" => clienttranslate("The Kiskin Boughrunner may do up to 3 combat moves. Each move must end on a square adjacent to one of your pieces."),
		   "effects" => array( "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing" ),
		   "effecttargets" => array(
		array( "combat", "neighbourAdjacentToYourPieces", array() ),
		array( "combat", "neighbourAdjacentToYourPieces", array() ),
		array( "combat", "neighbourAdjacentToYourPieces", array() ) )
		   ),

	17 => array(
		   "name" => clienttranslate("Tree Shepherd"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(
			array(-1,0,0), /* being */  array(1,0,0),
			              array(0,1,0), array(1,1,0) ),
		   "text" => clienttranslate("Choose 1 of your pieces: If it is common, upgrade it; if heroic, it may do 1 combat move; if legendary, it may do 1 standard move."),
		   "effects" => array( "orEffects3", "upgradePiece", "movePiece", "movePiece", "" ),
		   "effecttargets" => array(
		array( "playerPiece", "commonPiece", "anywhere" ),
		array( array( "playerPiece", "heroicPiece", "anywhere" ),
		       "combat", "move", array() ),
		array( array( "playerPiece", "legendaryPiece", "anywhere" ),
		       "standard", "move", array() )
					    )
		   //		   , "mandatory" => array(0)
		    )
	);

$this->everfrost_contents = array(
	0 => array( /* ok */
		   "name" => clienttranslate("Snow Fox"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array(
			/* being */ array(1,0,0),
				                  array(2,1,0) ),
		   "text" => clienttranslate("The Snow Fox may do up to 2 standard moves."),
		   "effects" => array( "moveBeing", "ifNotSkipped",
		   	     	       "moveBeing" ),
		   "effecttargets" => array(
		   		   array( "standard", "move", array() ),
		   		   array( "standard", "move", array() ) )
		   ),

	1 => array( /* ok */
		   "name" => clienttranslate("Royal Reindeer"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array( /* being */ 
			array(-1,1,0),  array(0,1,0),
			                              array(1,2,0) ),
		   "text" => clienttranslate("The Royal Reindeer may do a combat leap to a distance of exactly 2. If neither the summoning nor the leap destroys a piece, upgrade the Royal Reindeer."),
		   "effects" => array( "moveBeing", "ifNotDestroyedAtAll", "upgradePiece" ),
		   "effecttargets" => array(
		   		   array( "combat", "leap2", array() ),
				   array( "playerPiece", "commonPiece", "theBeing" ) ),
		   "auto" => array(2)
		   ),

	2 => array( /* ok */
		   "name" => clienttranslate("Crystal Mirror"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array( array(0,-1,0),
				       /* being */
				       array(0,1,0) ),
		   /* "marked" => array( array(1,-1), array(2,-1), */
		   /* 	       	      array(1,0), array(2,0), */
		   /* 		      array(1,1), array(2,1)  ), */
		   "marked" => array( array(1,0) ),
		   "text" => clienttranslate("You may choose 1 heroic or up to 2 common pieces on yellow-marked squares: For each, place one of your pieces with the same rank on the mirror-image square, if it is empty."),
		   "effects" => array( "chooseDirectionMirror",
				       "always", "placePiece",
		   	     	       "always", "cleanUpMirror",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankCommon" )),
	       		    	       "placePiece" ),
		   "effecttargets" => array(
		   array(),
		   array( "playerPiece", "markedRank", "markedSquare" ),
		   array(),
		   array( "playerPiece", "commonPiece", "markedSquare" ) ),
		   "mandatory" => array(0),
		   "impro" => true
		   ),

	3 => array(
		   "name" => clienttranslate("Crystal Grower"),
		   "rank" => 0,
		   "rotations" => 8,
		   "pattern" => array( /* being */
			array(-1,1,0), array(0,1,0) ),
		   "text" => clienttranslate("Upgrade 1 common piece of each enemy color."),
		   "effects" => array( "upgradePiece", "ifColorsLeft",
				       "upgradePiece", "ifColorsLeft",
				       "upgradePiece" ),
		   "effecttargets" => array(
			array( "enemyPiece", "commonPiece", "anywhere" ),
			array( "otherEnemy", "commonPiece", "anywhere" ),
			array( "otherLastTwoEnemies", "commonPiece", "anywhere" ) ),
		   "frozentext" => clienttranslate("Upgrade a common piece of your color."),
		   "mandatory" => array(0,2,4)
		   ),

	4 => array(
		   "name" => clienttranslate("Ice Princess"),
		   "rank" => 0,
		   "rotations" => 4,
		   "pattern" => array( /* being */
				      array(0,1,0),
			array(-1,2,0),               array(1,2,0) ),
		   "text" => clienttranslate("You may do 1 combat move with one of your common pieces other than the Ice Princess."),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
	array( array( "playerPiece", "commonPiece", "anywhereButBeing" ),
	       "combat", "move", array() ) ),
		   "frozentext" => clienttranslate("Do 1 standard move with one of your common pieces.")
		   ),

	5 => array(
		   "name" => clienttranslate("Ice Queen"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /**/ array(0,0,0), /**/
				            array(0,1,0),
			     array(-1,2,0),               array(1,2,0) ),
		   "text" => clienttranslate("You may do 1 combat move with one of your heroic pieces other than the Ice Queen."),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
	array( array( "playerPiece", "heroicPiece", "anywhereButBeing" ),
	       "combat", "move", array() ) ),
		   "frozentext" => clienttranslate("Do 1 standard move with one of your heroic pieces.")
		   ),

	6 => array(
		   "name" => clienttranslate("Frostweave Summoner"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1,-1,0),
				                     /* being */ 
				                     array(0,1,0),
				                     array(0,2,0) ),
		   "text" => "",
		   "effects" => array( "nothing" ),
		   "effecttargets" => array( array() ),
		   "frozentext" => clienttranslate("Use just before summoning a being. For the pattern of that being, you may use one enemy piece as though it were yours.")
		   ),

	7 => array(
		   "name" => clienttranslate("Winter Whisperer"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
		      array(-1,1,0), array(0,1,0), array(1,1,0), array(2,1,0)),
		   "text" => "",
		   "effects" => array( "nothing" ),
		   "effecttargets" => array( array() ),
		   "frozentext" => clienttranslate("You may destroy one of your common pieces. You may discard 1 flare. If you do both, gain an action.")
		   ),

	8 => array(
		   "name" => clienttranslate("Frozen Chest"),
		   "rank" => 1,
		   "rotations" => 2,
		   "pattern" => array(
			array(-1,0,0), /**/ array(0,0,0),/**/ array(1,0,0) ),
		   "text" => clienttranslate("You may take one frozen effect from your discard pile and put it directly into play. (The limit of 1 frozen effect in play still applies.)"),
		   "effects" => array( "putFrozenInPlay" ),
		   "effecttargets" => array( array() )
		   ),

	9 => array( /* ok */
		   "name" => clienttranslate("Everfrost Sentinel"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */
				      array(0,1,0),
				      array(0,2,0), array(1,2,0),
				                    array(1,3,0) ),
		   "text" => clienttranslate("If summoning the Everfrost Sentinel destroyed an enemy piece, you may downgrade the Everfrost Sentinel. If you do, each other player or team has 1 less action on their next turn."),
		   "effects" => array( "nothing",
				       "ifOversummonedEnemy", "downgradePiece",
				       "ifDowngradedPiece", "loseAction" ),
		   "effecttargets" => array(
			array(),
			array( "playerPiece", "heroicPiece", "theBeing" ),
			array() )
		   ),

	10 => array( /* ok */
		   "name" => clienttranslate("Glacier Giant"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array( /* being */
				      array(0,1,0),
			array(-1,2,0), array(0,2,0), array(1,2,0) ),
		   "marked" => array ( array(-1,1), array(1,1) ),
		   "text" => clienttranslate("The Glacier Giant may do any number of combat moves in one of the indicated directions. If it moves, it may continue 1 more square to destroy a legendary piece, but this also destroys the Glacier Giant."),
		   "effects" => array( "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifMoved", "destroyPiece",
				       "ifDestroyedLegend", "destroyPiece" ),
		   "effecttargets" => array(
			array( "combat", "markedSquare", array() ),
			array( "combat", "sameDirection", array() ),
			array( "combat", "sameDirection", array() ),
			array( "combat", "sameDirection", array() ),
			array( "combat", "sameDirection", array() ),
			array( "combat", "sameDirection", array() ),
			array( "anyPiece", "legendaryPiece", "sameDirection" ),
			array( "playerPiece", "heroicPiece", "theBeing" )
					    ),
		   "impro" => true,
		   "auto" => array(14)
		   ),

	11 => array( /* ok */
		   "name" => clienttranslate("Polar Bear"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(
			/* being */ array(1,0,0), array(2,0,0),
			            array(1,1,0),               array(3,1,0) ),
		   "text" => clienttranslate("The Polar Bear may do a combat move. If that move destroys a piece, it may do a standard move. If that destroys a piece, it may do a move onto an empty square."),
		   "effects" => array( "moveBeing",
				       "ifDestroyed", "moveBeing",
				       "ifDestroyedTwo", "moveBeing" ),
		   "effecttargets" => array(
			array( "combat", "move", array() ),
			array( "standard", "move", array() ),
			array( "standard", "move", array( "emptySquare" ) ) )
		   ),

	12 => array( /* ok */
		   "name" => clienttranslate("War Sled"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(
				array(-2,0,0),               /* being */ 
				array(-2,1,0), array(-1,1,0), array(0,1,0) ),
		   "marked" => array( array(1,-1), array(1,0), array(1,1) ),
		   "text" => clienttranslate("The War Sled may do up to 3 combat moves, the first in one of the indicated directions and each subsequent move in a direction that differs by no more than 45 degrees from the previous move."),
		   "effects" => array( "moveBeing",
				       "ifNotSkipped", "moveBeing",
				       "ifNotSkipped", "moveBeing" ),
		   "effecttargets" => array(
				array( "combat", "markedSquare", array() ),
				array( "combat", "noMoreThan45", array() ),
				array( "combat", "noMoreThan45", array() )
					    ),
		   "impro" => true
		   ),

	13 => array( /* ok */
		   "name" => clienttranslate("Snow Monster"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array(
			/**/ array(0,0,0), /**/ array(1,0,0),
		array(-1,1,0),                                 array(2,1,0) ),
		   "text" => clienttranslate("Destroy each common enemy piece that is within distance 2 of the Snow Monster and adjacent to at least one of your pieces."),
		   "effects" => array( "destroyPiece" ),
		   "effecttargets" => array( array( "enemyPiece",
			"commonPiece", call_user_func(array($this, "intersection"),
				array($this, "distance2"),
				array($this, "adjacentToYourPieces") ) ) ),
		   "auto" => array(0)
		   ),

	14 => array( /* ok */
		   "name" => clienttranslate("Frost Imp"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1,0,0), /* being */
				                     array(0,1,0),
				                                 array(1,2,0)),
		   "text" => clienttranslate("You may choose an adjacent common piece and a direction: That piece does as many standard moves as it can in that direction."),
		   "effects" => array( "movePiece",
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece", 
				       "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
			array( array("anyPiece", "commonPiece", "neighbour"),
			       "standard", "move", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() ),
			array( array("anyPiece", "commonPiece", "samePiece"),
			       "standard", "sameDirection", array() )
					    ),
		   "mandatory" => array(2,4,6,8,10,12,14)
		   ),

	15 => array(
		   "name" => clienttranslate("Ice Wyvern"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( /* being */ array(1,0,0),
				       array(0,1,0),
				                   array(1,2,0), array(2,2,0)),
		   "text" => clienttranslate("The Ice Wyvern may do a combat leap to anywhere on the board. If the leap destroys a piece, destroy the Ice Wyvern."),
		   "effects" => array( "moveBeing",
				       "ifDestroyed", "destroyPiece" ),
		   "effecttargets" => array(
			array( "combat", "leap", array() ),
			array( "playerPiece", "heroicPiece", "theBeing" ) ),
		   "frozentext" => clienttranslate("Place a common piece of your color on an empty square adjacent to one of your heroic pieces."),
		   "auto" => array(2)
		   ),

	16 => array( /* ok */
		   "name" => clienttranslate("Deathbringer"),
		   "rank" => 1,
		   "rotations" => 8,
		   "pattern" => array( array(-1,0,0), /* being */

			array(-2,2,0), array(-1,2,0), array(0,2,0) ),
		   "text" => clienttranslate("You may destroy an adjacent piece; if heroic, it must be adjacent to at least 1 other piece of yours; if legendary, 3 other pieces of yours. Count it as two destroyed pieces. Remove it from the game."),
		   "effects" => array( "destroyPiece",
				       "ifDestroyed", "countTwice",
				       "ifDestroyed", "removeFromGame" ),
		   "effecttargets" => array(
			array( "anyPiece", "anyRank", "adjacentDeathbringer" ),
			array(), array() )
		   ),

	17 => array(
		   "name" => clienttranslate("Frostweave Illusionist"),
		   "rank" => 1,
		   "rotations" => 4,
		   "pattern" => array(
			array(-1,0,0), /* being */ array(1,0,0),

				      array(0,2,0),),
		   "text" => clienttranslate("Convert the Frostweave Illusionist to a common piece of an enemy color."),
		   "effects" => array( "nothing",
                               "ifOpponentHasPieces", "destroyPiece",
                               "always", "placePiece" ),
		   "effecttargets" => array(
               array(),
               array( "playerPiece", "heroicPiece", "theBeing" ),
               array( "enemyPiece", "commonPiece", "theBeing" ) ),
		   "frozentext" => clienttranslate("Convert any common enemy piece to your common piece."),
		   "mandatory" => array(2,4)
		    ),

	23 => array(
		   "name" => clienttranslate("Crystal Grower"),
		   "effects" => array( "upgradePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ) ),
		   "mandatory" => array(0)
		   ),

	24 => array(
		   "name" => clienttranslate("Ice Princess"),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
	array( array( "playerPiece", "commonPiece", "anywhere" ),
	       "standard", "move", array() ) ),
		   "mandatory" => array(0)
		   ),

	25 => array(
		   "name" => clienttranslate("Ice Queen"),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "heroicPiece", "anywhere" ),
		       "standard", "move", array() ) ),
		   "mandatory" => array(0)
		   ),

	26 => array(
		   "name" => clienttranslate("Frostweave Summoner"),
		   "effects" => array( "gainBonusImprovisation" ),
		   "effecttargets" => array( array() )
		   ),

	27 => array(
		   "name" => clienttranslate("Winter Whisperer"),
		   "effects" => array( "destroyPiece",
				       "always", "chooseOption",
				       "ifChoiceOne", "discardFlare",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifChoiceOne"), array($this, "ifDestroyed")),
				       "gainAction" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ),
			array( clienttranslate('${actplayer} must choose whether to discard a flare'),
			       clienttranslate('${you} must choose whether you wish to discard a flare'),
			       clienttranslate("Yes"),
			       clienttranslate("No")
			       ),
			array(), array() ),
		   "mandatory" => array(2)
		   ),

	35 => array(
		   "name" => clienttranslate("Ice Wyvern"),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => array(
	array( "playerPiece", "commonPiece", "adjacentToYourHeroic" ) ),
		   "mandatory" => array(0)
		   ),

	37 => array(
		   "name" => clienttranslate("Frostweave Illusionist"),
		   "effects" => array( "convertPiece" ),
		   "effecttargets" => array(
			array( "commonPiece", "commonPiece", "anywhere" ) ),
		   "mandatory" => array(0)
		   )
				  );

$this->nethervoid_contents = array(
	0 => array("name" => clienttranslate("Shadow Imp"),
			"rank" => 0,
			"rotations" => 8,
			"pattern" => array( array(-1,-1,0),
								/* being */   array(1,0,0),),
			"text" => clienttranslate("The Shadow Imp may do 1 combat move. If it does and it is not the Gateway, then the Gateway may do 1 combat move in the same direction"),
			"effects" => array( "moveBeing",
								call_user_func(array($this, "andcond"),
												array($this, "ifNotSkipped"),
												array($this, "ifGatewayElsewhere")),
								"movePiece" ),
			"effecttargets" => array(
				array( "combat", "move", array() ),
				array( array( "playerPiece", "anyrank", "theGateway"), "combat", "sameDirection", array() ),
			)
		),
	1 => array("name" => clienttranslate("Flame Imp"),
			"rank" => 0,
			"rotations" => 8,
			"pattern" => array( array(-2,0,0), /* being */
									array(-1,1,0)),
			"text" => clienttranslate("You may destroy a common piece adjacent to the Gateway."),
			"effects" => array( "destroyPiece" ),
			"effecttargets" => array(
				array( "anyPiece", "commonPiece", "adjacentGateway")
			)
		),
	2 => array("name" => clienttranslate("Gate Keeper"),
			"rank" => 0,
			"rotations" => 4,
			"pattern" => array(array(0,-1,0),
				array(-1,0,0), /* being*/ array(1,0,0)),
			"text" => clienttranslate("You may upgrade any one of your common pieces or place a common piece of your color on an empty square.  In either case, that piece becomes the Gateway."),
			"effects" => array( "orEffects2", "placePiece", "upgradePiece", "ifNotSkipped", "becomeGateway"),
			"effecttargets" => array(
				array( "playerPiece", "commonPiece", "anywhere" ),
				array( "playerPiece", "commonPiece", "anywhere" ),
				array( "playerPiece", "anyrank", "samePiece")
			),
			"auto" => array(4)
		),
	3 => array("name" => clienttranslate("Demon of Gluttony"),
			"rank" => 0,
			"rotations" => 4,
			"pattern" => array(  /* being */
				array(-1,1,0), array(0,1,0), array(1,1,0) ),
			"text" => clienttranslate("The Demon of Gluttony may do a combat move. If this destroys a piece, upgrade Gluttony and it may do another combat move.  If this destroys another piece, Gluttony becomes the Gateway."),
			"effects" => array( "moveBeing", "ifDestroyed", "upgradePiece", "ifDestroyed", "moveBeing", "ifDestroyedTwo", "becomeGateway"),
			"effecttargets" => array(
				array( "combat", "move", array() ),
				array( "playerPiece", "commonPiece", "theBeing"),
				array( "combat", "move", array() ),
				array( "playerPiece", "heroicPiece", "theBeing")
			),
			"auto" => array(2,6)
		),
	4 => array("name" => clienttranslate("Demon of Pride"),
			"rank" => 0,
			"rotations" => 8,
			"pattern" => array(/* being */
								array(0,1,0),
								array(0,2,0), array(1,2,0)),
			"text" => clienttranslate("Upgrade the Demon of Pride. It becomes the Gateway. It may do 1 combat move."),
			"effects" => array( "upgradePiece", "always", "becomeGateway", "always", "moveBeing" ),
			"effecttargets" => array(
				array( "anyPiece", "commonPiece", "theBeing" ),
				array( "anyPiece", "heroicPiece", "theBeing" ),
				array( "combat", "move", array() ),
			),
			"auto" => array(0,2)
		),
	5 => array("name" => clienttranslate("Acolyte of Destruction"),
			"rank" => 1,
			"rotations" => 4,
			"pattern" => array( /* being */
				array(-1,1,0),       array(1,1,0),
				
							array(0,3,0), ),
			"text" => clienttranslate("Destroy a common piece. If the Gateway is on a red square, you may also destroy a heroic piece adjacent to the destroyed piece's square."),
			"effects" => array( "destroyPiece",
								call_user_func(array($this, "andcond"),
												array($this, "ifNotSkipped"),
												array($this, "ifGatewayOnRedSquare")),
								"destroyPiece"),
			"effecttargets" => array(
				array("anyPiece", "commonPiece", "anywhere"),
				array("anyPiece", "heroicPiece", "pieceNeighbour")
			),
			"mandatory" => array(0)
		),
	6 => array("name" => clienttranslate("Acolyte of Growth"),
			"rank" => 1,
			"rotations" => 4,
			"pattern" => array( array(-1,-1,0),             array(1,-1,0),
								array(-1,0,0), /* being */ array(1,0,0)),
			"text" => clienttranslate("Place a common piece of your color on an empty square. If the Gateway is on a green square, place a heroic piece of your color on an empty square adjacent to that new common piece."),
			"effects" => array( "placePiece",
				call_user_func(array($this, "andcond"),
					array($this, "ifNotSkipped"),
					array($this,"ifGatewayOnGreenSquare")),
								"placePiece" ),
			"effecttargets" => array(
				array( "playerPiece", "commonPiece", "anywhere" ),
				array( "playerPiece", "heroicPiece", "pieceNeighbour" )
						 ),
		   "mandatory" => array(0, 2)
		),
	7 => array("name" => clienttranslate("Power Seeker"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( /* being */ array(1,0,0),        array(3,0,0),
													array(2,1,0),        array(4,1,0)),
			"text" => clienttranslate("If the Power Seeker is the Gateway, it may do 1 standard move. Otherwise, it may do any number of combat moves towards the Gateway."),
			"effects" => array( "nothing",
									"ifGateway", "moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing",
									call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifGatewayElsewhere")),
									"moveBeing"
			),
			"effecttargets" => array(
				array(),
				array( "standard", "move", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() ),
				array( "combat", "towardsGateway", array() )
			)
		),
	8 => array("name" => clienttranslate("Hell Hound"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( array(-2,0,0), array(-1,0,0),/* being */
				array(-3,1,0),             					array(0,1,0),),
			"text" => clienttranslate("You may destroy an adjacent piece. If you destroy no legendary piece and if your deck is not out of cards, put this card on the bottom of your deck."),
			"effects" => array( "destroyPiece",
								call_user_func(array($this, "andcond"),
												array($this, "ifNotDestroyedLegend"),
												array($this, "ifDeckNonEmpty")),
								"putCardOnBottom" ),
			"effecttargets" => array( 
				array( "anyPiece", "anyrank", "neighbour" ),
				8,
			)
		),
	9 => array("name" => clienttranslate("Vortex Master"),
			"rank" => 1,
			"rotations" => 4,
			"pattern" => array( array(0,-3,0),
				array(-1,-2,0),                array(1,-2,0),
						array(0,-1,0)
						/* being */
			),
			"text" => clienttranslate( "You may do 1 combat move with the Gateway. Whether you do or not, you may then do 1 combat move with one of your pieces adjacent to the Gateway." ),
			"effects" => array( "movePiece", "always", "movePiece" ),
			"effecttargets" => array(
				array( array( "playerPiece", "anyrank", "theGateway"), "combat", "move", array() ),
				array( array( "playerPiece", "anyrank", "adjacentGateway"), "combat", "move", array() ),
			)
		),
	10 => array("name" => clienttranslate("Gate Master"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( array(0,-1,0), array(1,-1,0),
						array(-1,0,0), /* being */ array(1,0,0), array(2,0,0) ),
			"text" => clienttranslate("Upgrade the Gateway. Then choose 1 of your non-legendary pieces to become the Gateway. If upgrading created a legendary piece, then Gate Master's summoning counts as summoning a legend on Gate Master's square."),
			"effects" => array( "upgradePiece", "ifUpgradedPiece", "considerLegendSummoned", "always", "becomeGateway" ),
			"effecttargets" => array(
				array( "playerPiece", "anyrank", "theGateway" ),
				array( "playerPiece", "anyrank", "theBeing" ),
				array( "playerPiece", "nonLegendaryPiece", "anywhere" )
			),
			"mandatory" => array(4),
			"auto" => array(0)
		),
	11 => array("name" => clienttranslate("Hell Rider"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( /**/ array(0,0,0), /**/ array(1,0,0), array(2,0,0),
								array(0,1,0)),
			"text" => clienttranslate("If the Hell Rider is the Gateway, it may do 1 combat move. Otherwise, it may do up to 3 combat moves."),
			"effects" => array( "moveBeing",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotGateway")),
								"moveBeing",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotGateway")),
								"moveBeing"),
			"effecttargets" => array(
				array( "combat", "move", array() ),
				array( "combat", "move", array() ),
				array( "combat", "move", array() ),
			),
		),
	12 => array("name" => clienttranslate("Demon of Wrath"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array(  /* being */ array(1,0,0),
						array(-1,1,0), array(1,1,0),
								array(0,2,0) ),
			"text" => clienttranslate("Destroy all enemy common pieces adjacent to the Gateway. If there are none, the Gateway may do a standard move first."),
			"effects" => array( "destroyPiece", "ifNotDestroyed", "movePiece", "always", "destroyPiece" ),
			"effecttargets" => array(
				array( "enemyPiece", "commonPiece", "adjacentGateway" ),
				array( array( "playerPiece", "anyrank", "theGateway" ), "standard", "move", array() ),
				array( "enemyPiece", "commonPiece", "adjacentGateway" ),
			),
		    "auto" => array(0,4)
		),
	13 => array("name" => clienttranslate("Demon of Greed"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( array(-1,-1,0), array(0,-1,0),
												/* marked being */
								array(-1,1,0),          array(1,1,0)),
			"text" => clienttranslate("Destroy one of your common and one of your heroic non-Gateway pieces. For each destroyed piece, gain an action."),
			"effects" => array( "destroyPiece", "always", "destroyPiece", "ifDestroyed", "gainAction", "ifDestroyedTwo", "gainAction"),
			"effecttargets" => array(
				array( "playerpiece", "commonPiece", "nonGateway" ),
				array( "playerpiece", "heroicPiece", "nonGateway" ),
				array(),
				array()
			),
			"mandatory" => array(0,2)
		),
	14 => array("name" => clienttranslate("Demon of Lust"), // OK
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array( /* being */
								array(0,1,0), array(1,1,0),
											array(1,2,0), array(2,2,0)
			),
			"text" => clienttranslate("Choose up to 2 pieces (not necessarily yours). With each, do 1 combat move toward the Gateway. These moves must not destroy the Gateway."),
			"effects" => array( "movePiece", "ifNotSkipped", "movePiece" ),
			"effecttargets" => array(
				array( array( "anyPiece", "anyrank", "anywhere"), "combat", "towardsGatewayNotOnto", array()),
				array( array( "anyPiece", "anyrank", "anywhereButSamePiece"), "combat", "towardsGatewayNotOnto", array()),
			)
		),
	15 => array("name" => clienttranslate("Demon of Sloth"),
			"rank" => 1,
			"rotations" => 4,
			"pattern" => array(     array(1,-1,0)
							/* being */
				),
			"text" => clienttranslate("Spend an action to do nothing. If you cannot, destroy the Gateway instead."),
			"effects" => array( "loseOwnAction", "ifSkipped", "destroyPiece" ),
			"effecttargets" => array(
				array(),
				array("playerPiece", "anyrank", "theGateway" )
			),
			"mandatory" => array(2),
			"auto" => array(2)
		),
	16 => array("name" => clienttranslate("Demon of Envy"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array(array(-1,-1,0),
									/* */ array(0,0,0) /* */, array(1,0,0)),
			"text" => clienttranslate("Place a common piece of your color on an empty square adjacent to the Gateway. Then repeat this until there is no such empty square or until no opponent has more pieces than you do."),
			"effects" => array( "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece",
								call_user_func(array($this, "andcond"), array($this, "ifNotSkipped"), array($this, "ifNotMaxPieces")), "placePiece"
							),
			"effecttargets" => array(
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" ),
				array( "playerPiece", "commonPiece", "adjacentGateway" )
			),
			"mandatory" => array(0,2,4,6,8,10,12,14)
		),
	17 => array("name" => clienttranslate("Possessed Summoner"),
			"rank" => 1,
			"rotations" => 8,
			"pattern" => array(/* being */ array(1,0,0),
						array(-1,1,0), array(0,1,0), 
										array(0,2,0) ),
			"text" => clienttranslate("Place a common enemy piece on an empty square adjacent to a piece of the same color. For your next summoning this turn, use pieces of that color instead of your own. You may discard your flare to gain an action."),
			"effects" => array( "placePiece", "ifNotSkipped", "useLastEnemyColor", "always", "chooseOption", "ifChoiceOne", "discardFlare", "ifChoiceOne", "gainAction" ),
			"effecttargets" => array(
				array( "enemyPiece", "commonPiece", "adjacentToEnemyPieces" ),
				array( ),
				array( clienttranslate('${actplayer} must choose whether to discard a flare'),
					clienttranslate('${you} must choose whether you wish to discard a flare'),
					clienttranslate("Yes"),
					clienttranslate("No")
				),
				array(),
				array()
			),
			"mandatory" => array(0,4)
		)
	);				  
$this->etherweave_contents = array(
	0 => array("name" => clienttranslate("Doppelganger"), // Works
			"rank" => 0,
			"rotations" => 8,
			"text" => clienttranslate("If you have a pending being, copy its warp effect."),
			"pattern" => array( array(-1,0,0), /* being */
								array(-1,1,0) ),
			"effects" => array( "performWarp" ),
		        "effecttargets" => array( "pendingBeing" ),
		        "mandatory" => array(0)
		),
	1 => array("name" => clienttranslate("Paradox Worm"), // Works
			"rank" => 0,
			"rotations" => 8,
			"warptext" => clienttranslate("Upgrade 1 enemy common piece. You may then discard your pending being."),
			"text" => clienttranslate("You may swap one of your non-legendary pieces with an enemy piece of the same rank."),
			"pattern" => array(                array(-1,0,0), /* being */
				array(-3,1,0), array(-2,1,0) ),
			"effects" => array( "movePiece" ),
			"effecttargets" => array(
		array( array("playerPiece", "nonLegendaryPiece", "anywhere"),
		       "swap", "leap", array("enemyPiece", "sameRankSwap") )
			)
		),
	2 => array("name" => clienttranslate("Lesser Shadow Twin"), // Works
			"rank" => 0,
			"rotations" => 8,
			"warptext" => clienttranslate("Upgrade 1 of your common pieces."),
			"text" => clienttranslate("If Greater Shadow Twin is in your discard pile, choose one of your pieces and destroy up to 2 common pieces adjacent to it."),
			"effects" => array( "nothing",
								"ifTwinInDiscard",
								"destroyPiece",
								"ifDestroyed",
								"destroyPiece"),
			"pattern" => array( /* being */
									array(0,1,0), array(1,1,0), array(2,1,0)),
			"effecttargets" => array(
				array(),
				array( "anyPiece", "commonPiece", "adjacentToYourPieces" ),
				array( "anyPiece", "commonPiece", "adjacentToSamePiece" )
			)									 
		),
	3 => array("name" => clienttranslate("Translocationist"), // Works
			"rank" => 0,
			"rotations" => 4,
			"warptext" => clienttranslate("Gain an action."),
			"text" => clienttranslate("You may swap up to 2 of your heroic pieces with your common pieces."),
		   "effects" => array( "movePiece",
				       "ifNotSkipped", "movePiece" ),
			"pattern" => array(                            array(1,-1,0),
								array(-1,0,0), /* being */
								array(-1,1,0), array(0,1,0) ),
			"effecttargets" => array(
		array( array("playerPiece", "heroicPiece", "anywhere"), 
		       "swap", "leap", array("playerPiece", "commonPiece") ),
		array( array("playerPiece", "heroicPiece", "anywhere"), 
		       "swap", "leap", array("playerPiece", "commonPiece") )
			)
		),
	4 => array("name" => clienttranslate("Antimatter Spirit"), // done!
		"rank" => 0,
		"rotations" => 2,
		"warptext" => clienttranslate("Place a common piece of your color on an empty colorless square. It does a combat move. Place a common piece of another color on the same empty square. It does a combat move in the opposite direction. This is a linked effect."),
		"text" => "",
		"effects" => array( "nothing" ),
		"effecttargets" => array( array() ),
		"pattern" => array( 		array(1,-1,0),
								/* being */
							array(-1,1,0) ),
		),
	5 => array("name" => clienttranslate("Merchant of Time"), // Need "merchant" location for piece
			"rank" => 1,
			"rotations" => 8,
			"warptext" => clienttranslate("Take a piece from a colorless square and put it on this card. This card cannot be copied. If pending, it cannot be discarded or returned to hand."),
			"text" => clienttranslate("If there is a piece on this card, do a standard leap with that piece to any colorless square on the board."),
		   "effects" => array( "nothing", "ifCaptured", "freePiece" ),
			"pattern" => array(                 /* being */
								array(-1,1,0),              array(1,1,0),
					array(-2,2,0),              array(0,2,0) ),
			"effecttargets" => array(
						 array(),
		array( "anything", "standardMerchant", "colorlessSquare" ) ),
		        "mandatory" => array(2)
		),
	6 => array("name" => clienttranslate("Gate of Oblivion"),  // No need for "purplemarked" after all !
			"rank" => 1,
			"rotations" => 4,
			"warptext" => clienttranslate("For the rest of this turn, when you destroy a piece count it as though you also destroyed an additional piece of the same color and rank."),
			"text" => clienttranslate("Destroy any piece on the purple marked square. On another marked square, you may destroy a non-legendary piece."),
		   "effects" => array( "destroyPiece",
				       "always", "cleanUpGateOfOblivion",
				       "always", "destroyPiece" ),
			"pattern" => array( array(-1,0,0), /* being */ array(1,0,0),
								array(-1,1,0),             array(1,1,0) ),
			/* will be converted into marked after 1st effect */
			"marked2" => array(array(-1,-1), array(0,-1), array(1,-1),
					array(-2,0),              /* being */              array(2,0),
					array(-2,1),                                       array(2,1)),
			/* *** the purple marked square *** */
		   "marked" => array( array(0,1) ),
			"effecttargets" => array(
			    array( "anyPiece", "anyrank", "markedSquare" ),
			    array(),
				array( "anyPiece", "nonLegendaryPiece", "markedSquare" )
			),
			"mandatory" => array(0),
		        "impro" => true
		),
	7 => array("name" => clienttranslate("Time Traveler"), // Works
			"rank" => 1,
			"rotations" => 2,
			"text" => clienttranslate("Time Traveler's summoning square can be any colorless empty square. You may destroy an adjacent common piece. If you do, put the top card of your discard pile on top of your deck."),
		   /* No being on the pattern, can summon anywhere : */
		   /* DONE, see foundPatternWrapper */
			"pattern" => array( array(-1,-1,0),
												array(0,0,0),
															array(1,1,0) ),
			"effects" => array( "destroyPiece", "ifNotSkipped", "putTopCardOnTop" ),															
			"effecttargets" => array(
				array( "anyPiece", "commonPiece", "neighbour" ),
				array()
			),
		   "traveler" => true
		),
	8 => array("name" => clienttranslate("Greater Shadow Twin"), // Works
			"rank" => 1,
			"rotations" => 4,
			"warptext" => clienttranslate("Upgrade 1 of your common pieces."),
			"text" => clienttranslate("If Lesser Shadow Twin is in your discard pile, you may destroy one heroic piece adjacent to at least two of your pieces."),
			"effects" => array( "nothing",
								"ifTwinInDiscard",
								"destroyPiece" ),
			"pattern" => array(/* being */ array(1,0,0), array(2,0,0), array(3,0,0) ),
			"effecttargets" => array(
				array(),
				array( "anyPiece", "heroicPiece", "adjacentToTwo" )
			)
		),
	9 => array("name" => clienttranslate("Warpmaster"), // Need "returnPending" effect
			"rank" => 1,
			"rotations" => 8,
			"text" => clienttranslate("You may either return your pending being to your hand or do a standard move with the Warpmaster."),
			"effects" => array( "orEffects2",
					    "returnPending", "moveBeing"),
			"pattern" => array( array(-1,-2,0),
				/* empty line LINE "" "i" "" */
				array(-2,0,0),               /* being */
								array(-1,1,0) ),
			"effecttargets" => array(
				/* array( clienttranslate('${actplayer} must choose how to use the Warpmaster'), */
				/* 		clienttranslate('${you} must choose how to use the Warpmaster'), */
				/* 		clienttranslate("Return Pending Being"), */
				/* 		clienttranslate("Move the Warpmaster") */
				/* ), */
				array(),
				array( "standard", "move", array() )
						 ),
		   //		   "mandatory" => array(0)
		),
	10 => array("name" => clienttranslate("Dark Sphere"), // Works
			"rank" => 1,
			"rotations" => 4,
			"text" => clienttranslate("Choose up to 3 non-legendary pieces, at most 2 of one color. With each, do a combat move. Each move must end at distance 2 from the Dark Sphere."),
			"effects" => array( "movePiece", "ifNotSkipped", "movePiece", "ifNotSkipped", "movePiece" ),
			"pattern" => array( array(-1,0,0), /**/array(0,0,0)/**/,
								array(-1,1,0),     array(0,1,0) ),
			"effecttargets" => array(
				array( array( "anyPiece", "nonLegendaryPiece", "anywhere" ), "combat", "moveToDistance2FromBeing", array( ) ),
				array( array( "anyPiece", "nonLegendaryPiece", "anywhereButSamePiece" ), "combat", "moveToDistance2FromBeing", array( ) ),
				array( array( "darkSphereThirdOpponent", "nonLegendaryPiece", "anywhereButSameTwoPieces" ), "combat", "moveToDistance2FromBeing", array( ) )
			)
		),
	11 => array("name" => clienttranslate("Void Summoner"), // Need "voidSummoning" effect and additions to summoning patterns to use it
			"rank" => 1,
			"rotations" => 8,
			"warptext" => clienttranslate("Do a standard move with one of your non-legendary pieces."),
			"text" => clienttranslate("For your next summoning this turn, you may count up to two empty squares as common pieces of your color."),
			"effects" => array( "voidSummoning" ),
			"pattern" => array( /* being */ array(1,0,0),
								array(0,1,0),
				array(-1,2,0),             array(1,2,0) ),
			"effecttargets" => array(
				array()
			)
		),
	12 => array("name" => clienttranslate("Eternal Emperor"), // should work
			"rank" => 1,
			"rotations" => 8,
			"warptext" => clienttranslate("Either place a common piece of your color on an empty square, or move 1 of your pieces - combat move if common, standard move if upgraded."),
			"text" => clienttranslate("If you have not played Eternal Emperor's warp effect this turn, you may perform it now as a normal effect."),
			"effects" => array( "nothing",
					    "ifNoEmperorYet",
			"orEffects3", "placePiece", "movePiece", "movePiece" ),
			"pattern" => array( array(-1,-1,0),
								array(-1,0,0), /* being */
								array(-1,1,0), array(0,1,0) ),
			"effecttargets" => array(
				array(),
				array( "playerPiece", "commonPiece", "anywhere" ),
		 		array( array( "playerPiece", "commonPiece", "anywhere" ), "combat", "move", array() ),
				array( array( "playerPiece", "upgradedPiece", "anywhere" ), "standard", "move", array() )
			)
		),
	13 => array("name" => clienttranslate("Reality Patch"), // Need "discardPending" effect and "discardSingleCard" effect
			"rank" => 1,
			"rotations" => 1,
			"text" => clienttranslate("In each enemy color, you may destroy 1 heroic piece. You may discard your pending being. You may discard one card of any type from your hand."),
			"effects" => array( "destroyPiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifColorsLeft")),
					    "destroyPiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifColorsLeft")),
					    "destroyPiece",
					    "ifPendingBeingNotEmperor", "chooseOption",
				       "ifChoiceOne", "discardPending",
				       "ifChoiceTwo", "nothing",
					    "always", "discardSingleCard" ),
			"pattern" => array( array(0,-1,0),
					array(-1,0,0), /* being */ array(1,0,0),
								array(0,1,0) ),
			"effecttargets" => array(
				array( "enemyPiece", "heroicPiece", "anywhere" ),
				array( "otherEnemy", "heroicPiece", "anywhere" ),
				array( "otherLastTwoEnemies", "heroicPiece", "anywhere" ),
				array( clienttranslate('${actplayer} must choose whether to discard his pending being'),
				       clienttranslate('${you} must choose whether you wish to discard your pending being'),
				       clienttranslate("Yes"),
				       clienttranslate("No")
				       ),
				array(),
				array(),
				array()
						 ),
		    "mandatory" => array(6)
		),
	14 => array("name" => clienttranslate("Polarity Queen"), // coded, lot of testing to check!
			"rank" => 1,
			"rotations" => 4,
			"warptext" => clienttranslate("Place a common piece of your color on an empty square adjacent to an enemy piece."),
			"text" => clienttranslate("Choose 2 of your non-legendary pieces other than the Queen. Do 1 combat move with each of them in opposite directions. This is a linked effect."),
			"effects" => array( "movePiece", "ifNotSkipped", "movePiece" ),
			"pattern" => array( /* being */
				array(-1,1,0),           array(1,1,0),
								array(0,2,0),
								array(0,3,0) ),
			"effecttargets" => array(
				array( array( "playerPiece", "nonLegendaryPiece", "polarityQueenFirstPiece" ), "combat", "polarityQueenFirstMove", array() ),
				array( array( "playerPiece", "nonLegendaryPiece", "anywhereButBeingOrSamePiece" ), "combat", "oppositeDirection", array( ) )
			),
			"mandatory" => array(0,2)
		),
	15 => array("name" => clienttranslate("Singularity"), // Works
			"rank" => 1,
			"rotations" => 1,
			"text" => clienttranslate("Choose an empty colorless square. All common pieces adjacent to that square must do a combat move onto it in the order of your choosing."),
			"effects" => array( "chooseSquare", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece", "ifAdjacentCommonLeft",
								"movePiece" ),
			"pattern" => array( array(-1,-1,0),            array(1,-1,0),
												/* being */
								array(-1,1,0),             array(1,1,0) ),
			"effecttargets" => array(
				array( "emptyColorlessSquare" ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) ),
				array( array( "anyPiece", "commonPiece", "adjacentSpace" ), "combat", "towardsSingularity", array( ) )
			),
			"mandatory" => array(0,2,4,6,8,10,12,14,16)
		),
	16 => array("name" => clienttranslate("Ziggurat Sentinel"), // Works
			"rank" => 1,
			"rotations" => 8,
			"warptext" => clienttranslate("Do 1 combat move or 2 standard moves with one of your non-legendary pieces."),
			"text" => clienttranslate("You may either copy a warp effect on a card in your discard pile, or do up to 3 combat moves with Ziggurat Sentinel in the marked direction."),
			/* "effects" => array( "chooseOption", */
			/* 					"ifChoiceOne", "performWarp", */
			/* 					"ifChoiceTwo", "moveBeing", */
			/* 					call_user_func(array($this, "andcond"), array($this, "ifChoiceTwo"), array($this, "ifNotSkipped")), "moveBeing", */
			/* 					call_user_func(array($this, "andcond"), array($this, "ifChoiceTwo"), array($this, "ifNotSkipped")), "moveBeing"), */
			"effects" => array( "orEffects2",
					     "performWarp", "moveBeing",
					    "ifMoved", "moveBeing",
					    "ifNotSkipped", "moveBeing" ),
			"pattern" => array(                                /* being */
															array(0,1,0),
												array(-1,2,0), array(0,2,0),
								array(-2,3,0), array(-1,3,0) ),
			"marked" => array(                                 /* being */
												array(-1,1) ),
			/* "effecttargets" => array( */
			/* 	array( clienttranslate('${actplayer} must choose how to use the Ziggurat Sentinel'), */
			/* 			clienttranslate('${you} must choose how to use the Ziggurat Sentinel'), */
			/* 			clienttranslate("Copy Warp effect from discard"), */
			/* 			clienttranslate("Move the Ziggurat Sentinel") */
			/* 	), */
			/* 	array( "discardedWarp" ), */
			/* 	array( "combat", "markedSquare", array( ) ), */
			/* 	array( "combat", "sameDirection", array( ) ), */
			/* 	array( "combat", "sameDirection", array( ) ) */
			/* ) */
			"effecttargets" => array(
				"discardedWarp",
				array( "combat", "markedSquare", array( ) ),
				array( "combat", "sameDirection", array( ) ),
				array( "combat", "sameDirection", array( ) )
				),
		        "impro" => true
		    ),

	17 => array("name" => clienttranslate("Iris of Eternity"), // Need "discardLegends" effect
			"rank" => 1,
			"rotations" => 4,
			"text" => clienttranslate("You may either destroy an adjacent legendary piece or upgrade the Iris. If the Iris becomes legendary, count it as a legend summoned this turn and discard all your legendary cards."),
			"effects" => array( "orEffects2", "destroyPiece", "upgradePiece", "ifUpgradedPiece", "considerLegendSummoned", "ifUpgradedPiece", "discardLegends" ),
			"pattern" => array( array(-2,-1,0), array(-1,-1,0),
				array(-3,0,0),                               /* being */
								array(-2,1,0),  array(-1,1,0) ),
			"effecttargets" => array(
				array( "anyPiece", "legendaryPiece", "neighbour" ),
				array( "playerPiece", "heroicPiece", "theBeing" ),
				array( "playerPiece", "anyrank", "theBeing" ),
				array()
						 ),
		    "mandatory" => array(4,6)
		),
	// Warp effects	  
	21 => array("name" => clienttranslate("Paradox Worm"), // Need to make the discardPending optional, and cause a visual update if you do so
			"warptext" => clienttranslate("Upgrade 1 enemy common piece. You may then discard your pending being."),
			"effects" => array( "upgradePiece",
					    "ifPendingBeingNotEmperor", "chooseOption",
					    "ifChoiceOne", "discardPending", 
					    "ifChoiceTwo", "nothing" ),
			"effecttargets" => array(
				array( "enemyPiece", "commonPiece", "anywhere" ),
				array( clienttranslate('${actplayer} must choose whether to discard his pending being'),
				       clienttranslate('${you} must choose whether you wish to discard your pending being'),
				       clienttranslate("Yes"),
				       clienttranslate("No")
				       ),
				array(),
				array()
						 ),
			"mandatory" => array(0,2)
		),
	22 => array("name" => clienttranslate("Lesser Shadow Twin"), // should just work
			"warptext" => clienttranslate("Upgrade 1 of your common pieces."),
			"effects" => array( "upgradePiece" ),
			"effecttargets" => array( array( "playerPiece", "commonPiece", "anywhere" ) ),
			"mandatory" => array(0)
		),
	23 => array("name" => clienttranslate("Translocationist"), // should just work
			"warptext" => clienttranslate("Gain an action."),
			"effects" => array( "gainAction" ),
			"effecttargets" => array( array( ) )
		),
	24 => array("name" => clienttranslate("Antimatter Spirit"), // works
			"warptext" => clienttranslate("Place a common piece of your color on an empty colorless square. It does a combat move. Place a common piece of another color on the same empty square. It does a combat move in the opposite direction. This is a linked effect."),
		    //* Rules Q: Are you allowed to warp this card
		    // if you cannot perform the effect at all?  I
		    // think yes, but not certain.
		    //* From the rules : If it not possible to perform
		    // all parts of a linked effect, then no part
		    // of the effect is performed.
			"effects" => array( "placePiece", "ifNotSkipped", "movePiece", "ifNotSkipped", "placePiece", "ifNotSkipped", "movePiece" ),
			"effecttargets" => array(
				array( "playerPiece", "commonPiece", "antimatterSquare" ),
				array( array("anyPiece", "commonPiece", "samePiece"), "combat", "antimatterFirstMove", array() ),
				array( "enemyPiece", "commonPiece", "sameSquare" ),
				array( array("anyPiece", "commonPiece", "samePiece"), "combat", "oppositeDirection", array() ),
			),
			"mandatory" => array(0,2,4,6)
		),
	25 => array("name" => clienttranslate("Merchant of Time"), // Need to come up with an effect name for capturing a piece, and implement
			"warptext" => clienttranslate("Take a piece from a colorless square and put it on this card. This card cannot be copied. If pending, it cannot be discarded or returned to hand."),
		    "effects" => array( "capturePiece" ),
		    "effecttargets" => array( array( "anyPiece", "anyrank", "colorlessSquare" ) )
		),
	26 => array("name" => clienttranslate("Gate of Oblivion"), // Bugged in 3p deathmatch (at least)
			"warptext" => clienttranslate("For the rest of this turn, when you destroy a piece count it as though you also destroyed an additional piece of the same color and rank."),
		    "effects" => array( "countAdditionalDestroyed" ),
			"effecttargets" => array( array( ) )
		),
	28 => array("name" => clienttranslate("Greater Shadow Twin"),  // should just work
			"warptext" => clienttranslate("Upgrade 1 of your common pieces."),
			"effects" => array( "upgradePiece" ),
			"effecttargets" => array( array( "playerPiece", "commonPiece", "anywhere" ) ),
			"mandatory" => array(0)
		),
	31 => array("name" => clienttranslate("Void Summoner"), // should just work
			"warptext" => clienttranslate("Make a standard move with one of your non-legendary pieces."),
			"effects" => array( "movePiece" ),
			"effecttargets" => array(
				array( array( "playerPiece", "nonLegendaryPiece", "anywhere" ), "standard", "move", array() ) 
			),
		    "mandatory" => array(0)
		),
	32 => array("name" => clienttranslate("Eternal Emperor"), // works
			"warptext" => clienttranslate("Either place a common piece of your color on an empty square, or move 1 of your pieces - combat move if common, standard move if upgraded."),
			"effects" => array( "orEffects3", "placePiece", "movePiece", "movePiece", "", "always", "eternalEmperorWarped" ),
			"effecttargets" => array(
				array( "playerPiece", "commonPiece", "anywhere" ),
		 		array( array( "playerPiece", "commonPiece", "anywhere" ), "combat", "move", array() ),
				array( array( "playerPiece", "upgradedPiece", "anywhere" ), "standard", "move", array() ),
				array( )
			),
		    "mandatory" => array(0)
		),
	34 => array("name" => clienttranslate("Polarity Queen"), // works for one opponent, untested for more
			"warptext" => clienttranslate("Place a common piece of your color on an empty square adjacent to an enemy piece."),
		    "effects" => array( "placePiece" ),
		    "effecttargets" => array( 
				array( "playerPiece", "commonPiece", "adjacentToAnyEnemyPieces" ),
			),
		    "mandatory" => array(0)
		),
	36 => array("name" => clienttranslate("Ziggurat Sentinel"), // works
		    "warptext" => clienttranslate("Do one combat or two standard moves with one of your non-legendary pieces."),
		    "effects" => array( "movePiece", "ifNotCombatMoved", "movePiece" ),
		    "effecttargets" => array(
				array( array( "playerPiece", "nonLegendaryPiece", "anywhere"), "combat", "move", array() ),
				array( array( "playerPiece", "nonLegendaryPiece", "samePiece"), "standard", "move", array() )
			),
		    "mandatory" => array(0),
		)
  );

$this->legends_contents = array(
	0 => array(
		   "name" => clienttranslate("Fire Dragon"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array( array(-1,-3,1),          array(1,-3,1),
				                     array(0,-2,0),
				                     array(0,-1,1)
				                      /* being */),
		   "text" => clienttranslate("Choose one of the indicated directions: Destroy all non-legendary pieces up to distance 2 in that direction and in both directions at 45 degrees to it."),
		   "effects" => array( "shootPieces", "always",
				       "markSquares", "always",
				       "shootPieces" ),
		   "effecttargets" => array(
				array( "anyPiece", "nonLegendaryPiece",
				       array("shootDistanceTwo") ),
				array( "anything", "anyrank", "around45" ),
				array( "anyPiece", "nonLegendaryPiece",
				       array("shootDistanceTwo") )
					    ),
		   "marked" => array( array(-1,0), /* being */ array(1,0),
				      array(-1,1), array(0,1), array(1,1) ),
		   "mandatory" => array(0),
		   "auto" => array(4),
		   "impro" => true
		   ),

	1 => array(
		   "name" => clienttranslate("Hell Bull"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array(
			array(-1,-1,0),                    array(1,-1,0),
			array(-1,0,1),/**/array(0,0,0),/**/ array(1,0,1),
			                  array(0,1,0) ),
		   "text" => clienttranslate("Choose any direction: The Hell Bull may do any number of combat moves in that direction. If it destroys a legendary piece, it stops."),
		   "effects" => array( "moveBeing" ),
		   "effecttargets" => array(
			array( "combat", "charge", array( "legendaryPiece") ))
		   //		   , "mandatory" => array(0)	   
		   ),

	2 => array(
		   "name" => clienttranslate("Angel of Death"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array( array(-1,-1,0),     array(1,-1,0),
			array(-2,0,0),     /**/array(0,0,1),/**/  array(2,0,0),
				               array(0,1,0)),
		   "text" => clienttranslate("You may choose any 1 piece other than the Angel of Death: Upgrade it and then do a combat leap onto its square with the Angel of Death."),
		   "effects" => array( "choosePiece",
				       "ifNotSkipped", "upgradePiece",
				       "ifNotSkipped", "moveBeing" ),
		   "effecttargets" => array(
			array( "anyPiece", "anyrank", "anywhereButBeing"),
			array( "anyPiece", "anyrank", "samePiece"),
			array( "combat", "samePiece", array() )
					    ),
		   "auto" => array(2, 4)
		   ),

	3 => array(
		   "name" => clienttranslate("The Eldest Tree"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array( array(1,-1,1),
	array(-2,0,0),      /* being */              array(2,0,0),
		   array(-1,1,1) ),
		   "text" => clienttranslate("On up to 3 adjacent squares: if it has an enemy non-legendary piece, destroy it; if it has your common piece, upgrade it; if it is empty, place 1 common piece of your color there."),
		   "effects" => array( "orEffects3", "destroyPiece",
				       "upgradePiece", "placePiece", "",
			"ifNotSkipped", "orEffects3", "destroyPiece",
				       "upgradePiece", "placePiece", "",
			"ifNotSkipped", "orEffects3", "destroyPiece",
				       "upgradePiece", "placePiece", "" ),
		   "effecttargets" => array(
		array( "enemyPiece", "nonLegendaryPiece", "neighbour" ),
		array( "playerPiece", "commonPiece", "neighbour" ),
		array( "playerPiece", "commonPiece", "neighbour" ),
		array( "enemyPiece", "nonLegendaryPiece",
		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSamePiece"))),
		array( "playerPiece", "commonPiece",
 		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSamePiece"))),
		array( "playerPiece", "commonPiece",
 		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSamePiece"))),
		array( "enemyPiece", "nonLegendaryPiece",
		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSameTwoPieces"))),
		array( "playerPiece", "commonPiece",
 		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSameTwoPieces"))),
		array( "playerPiece", "commonPiece",
 		       call_user_func(array($this, "intersection"),
	array($this, "neighbour"), array($this, "anywhereButSameTwoPieces"))),
					    )
		   ),

	4 => array(
		   "name" => clienttranslate("Bone Catapult"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array( array(-1,-1,0),
				                    /* being */
				      array(-1,1,1),array(0,1,1),array(1,1,1)),
		   "text" => clienttranslate("You may choose any 1 piece in any of the indicated directions: Destroy it and all common pieces adjacent to it."),
		   "effects" => array( "destroyPiece", "ifNotSkipped",
				       "destroyPiece" ),
		   "effecttargets" => array(
			array( "anyPiece", "anyrank", "starMarked" ),
			array( "anyPiece", "commonPiece", "pieceNeighbour" )
					    ),
		   "marked" => array(             array(0,-1), array(1,-1),
				      array(-1,0), /* being */ array(1,0) ),
		   "auto" => array(2),
		   "impro" => true
		   ),

	5 => array(
		   "name" => clienttranslate("Fire Elemental"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array( /* being */
			 array(-1,1,1),          array(1,1,0), 
	   array(-2,2,1),             array(0,2,0),          array(2,2,0) ),
		   "text" => clienttranslate("Destroy either all non-legendary enemy pieces on all adjacent squares or all common enemy pieces up to distance 2."),
		   "effects" => array( "chooseOption", "ifChoiceOne", "destroyPiece", "ifChoiceTwo", "destroyPiece" ),
		   "effecttargets" => array(
	    array( clienttranslate('${actplayer} must choose which pieces to destroy'),
		   clienttranslate('${you} must choose which pieces to destroy'),
		   clienttranslate("Adjacent squares"),
		   clienttranslate("Up to distance 2")
		   ),
		array( "enemyPiece", "nonLegendaryPiece", "neighbour" ),
		array( "enemyPiece", "commonPiece", "distance2" )
					    ),
		   "auto" => array(2, 4),
		   "mandatory" => array(0)
		   ),

	6 => array(
		   "name" => clienttranslate("Leviathan"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array(                              /* being */
				      array(-2,1,1),array(-1,1,1),
	array(-4,2,1),array(-3,2,0) ),
		   "text" => clienttranslate("Chose one: Either downgrade a connected group of up to 3 upgraded pieces or destroy a connected group of up to 4 common pieces. (A group may have multiple colors.)"),
		   "effects" => array( "orEffects2",
				       "downgradePiece", "destroyPiece",
				       call_user_func(array($this, "andcond"),
	     array($this, "ifNotSkipped"), array($this, "ifDowngradedPiece")),
				       "downgradePiece",
				       call_user_func(array($this, "andcond"),
	     array($this, "ifNotSkipped"), array($this, "ifDowngradedPiece")),
				       "downgradePiece",
				       call_user_func(array($this, "andcond"),
	     array($this, "ifNotSkipped"), array($this, "ifDestroyed")),
				       "destroyPiece",
				       call_user_func(array($this, "andcond"),
	     array($this, "ifNotSkipped"), array($this, "ifDestroyed")),
				       "destroyPiece",
				       call_user_func(array($this, "andcond"),
	     array($this, "ifNotSkipped"), array($this, "ifDestroyed")),
				       "destroyPiece"
				       ),
		   "effecttargets" => array(
			array( "anyPiece", "upgradedPiece", "anywhere" ),
			array( "anyPiece", "commonPiece", "anywhere" ),
			array( "anyPiece", "upgradedPiece", "pieceNeighbour" ),
			array( "anyPiece", "upgradedPiece", "connectedTwo" ),
			array( "anyPiece", "commonPiece", "pieceNeighbour" ),
			array( "anyPiece", "commonPiece", "connectedTwo" ),
			array( "anyPiece", "commonPiece", "connectedThree" )
					    )
		   ),

	7 => array(
		   "name" => clienttranslate("Two-Headed Dragon"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array( /* being */        array(2,0,1),
				                array(1,1,1),
				       array(0,2,1),      array(2,2,0),
				                                 array(3,3,0)),
		   "text" => clienttranslate("You may upgrade 1 of the pieces used to summon the Two-Headed Dragon. If you do, you may destroy 1 heroic piece adjacent to the upgraded piece."),
		   "effects" => array( "upgradePiece", "ifUpgradedPiece",
				       "destroyPiece" ),
		   "effecttargets" => array(
		array( "anyPiece", "nonLegendaryPiece", "markedSquare" ),
		array( "anyPiece", "heroicPiece", "pieceNeighbour" )
					    ),
		   "marked" => array( /* being */        array(2,0,1),
				                array(1,1,1),
				       array(0,2,1),      array(2,2,0),
				                                 array(3,3,0)),
		   "impro" => true
		   ),

	8 => array(
		   "name" => clienttranslate("Earth Elemental"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array( /* being */   array(1,0,0),
				       array(0,1,0), array(1,1,0),
			  array(-1,2,1),                         array(2,2,1)),
		   "text" => clienttranslate("Destroy a non-legendary piece on or adjacent to a red square. Then upgrade a common piece on or adjacent to a green square. Then the Earth Elemental may do 1 combat move to an adjacent red or green square."),
		   "effects" => array( "destroyPiece", "always",
				       "upgradePiece", "always", "moveBeing"),
		   "effecttargets" => array(
		array( "anyPiece", "nonLegendaryPiece", "onOrAdjacentToRed"),
		array( "anyPiece", "commonPiece", "onOrAdjacentToGreen"),
		array( "combat", "adjacentColored", array() )
					    ),
		   "mandatory" => array(0, 2)
		   ),

	9 => array(
		   "name" => clienttranslate("Time Elemental"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array( /* being */
			 array(-1,1,1),           array(1,1,1),
			              array(0,2,1), 
			 array(-1,3,0),           array(1,3,0) ),
		   "text" => clienttranslate("After this turn, take an extra turn (even if the end of the game has been triggered)."),
		   "effects" => array( "gainTurn" ),
		   "effecttargets" => array( array() )
		   ),

	10 => array(
		   "name" => clienttranslate("Storm Elemental"),
		   "rank" => 2,
		   "rotations" => 8,
		   "pattern" => array( array(-1,-2,1),
				       array(-1,-1,1),          array(1,-1,1),
				                 /**/array(0,0,0)/**/ ),
		   "text" => clienttranslate("You may place 1 piece of your color on an empty square up to distance 3. With it, do up to 1 (if legendary), 3 (if heroic), or 5 (if common) combat moves in one direction. Then destroy it."),
		   "effects" => array( "chooseOption",
				       "ifChoiceOne", "placePiece",
				       "ifChoiceTwo", "placePiece",
				       "ifChoiceThree", "placePiece",
				       //				       "ifNotSkipped", "movePiece",
				       "ifNotSkipped", "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankNonLegendary")),
				       "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankNonLegendary")),
				       "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankCommon")),
				       "movePiece",
				       call_user_func(array($this, "andcond"),
	       array($this, "ifNotSkipped"), array($this, "ifLastRankCommon")),
				       "movePiece",
				       "ifPlacedPiece", "destroyPiece" ),
		   "effecttargets" => array(
	    array( clienttranslate('${actplayer} must choose which piece to play'),
		   clienttranslate('${you} must choose which piece to play'),
		   "<div class='pieceschoice common'></div>",
		   "<div class='pieceschoice heroic'></div>",
		   "<div class='pieceschoice legendary'></div>" ),
	    array( "playerPiece", "commonPiece", "distance3" ),
	    array( "playerPiece", "heroicPiece", "distance3" ),
	    array( "playerPiece", "legendaryPiece", "distance3" ),
	    /* array( array( "playerPiece", "anyrank", "samePiece" ), */
	    /* 	   "combat", "charge", array( "stormDistance" ) ), */
	    array( array( "playerPiece", "anyrank", "samePiece" ),
		   "combat", "move", array( ) ),
	    array( array( "playerPiece", "anyrank", "samePiece" ),
		   "combat", "sameDirection", array( ) ),
	    array( array( "playerPiece", "anyrank", "samePiece" ),
		   "combat", "sameDirection", array( ) ),
	    array( array( "playerPiece", "anyrank", "samePiece" ),
		   "combat", "sameDirection", array( ) ),
	    array( array( "playerPiece", "anyrank", "samePiece" ),
		   "combat", "sameDirection", array( ) ),
	    array( "playerPiece", "anyrank", "samePiece" )
					    ),
		   "auto" => array( 18 ),
		   "mandatory" => array( 2, 4, 6 )
		    ),

	11 => array(
		   "name" => clienttranslate("Titan"),
		   "rank" => 2,
		   "rotations" => 4,
		   "pattern" => array( /* being */
				      array(0,1,1),
				      array(0,2,1),
			array(-1,3,0), array(0,3,0), array(1,3,0) ),
		   "text" => clienttranslate("Destroy all orthogonally adjacent pieces and all non-legendary diagonally adjacent pieces. They do not count as pieces destroyed by you."),
		   "effects" => array( "destroyPieceTitan", "always",
				       "destroyPieceTitan"
				       ),
		   "effecttargets" => array(
			array( "anyPiece", "anyrank", "orthogonalNeighbour"),
		array( "anyPiece", "nonLegendaryPiece", "diagonalNeighbour")
			//, array()
					    ),
		   "auto" => array(0, 2)
		   )
				);

/* Flares effects are mostly mandatory, we only mark the skippable ones.
   0 is for the "upgraded" criterion, 1 for the "pieces" criterion.
   "more" is the required differential.
   The rest is as for beings effects.
*/

$this->flares = array(
	0 => array(
	     0 => array(
		   "more" => 3,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" =>
		   array( array( "playerPiece", "commonPiece", "anywhere" ))),
	     1 => array(
		   "more" => 6,
		   "text" => clienttranslate("Place 1 heroic piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" =>
		   array( array( "playerPiece", "heroicPiece", "anywhere" ) ) )
		   ),
		      
	1 => array(
	     0 => array(
		   "more" => 4,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => 
		   array( array( "playerPiece", "commonPiece", "anywhere" ))),
	     1 => array(
		   "more" => 4,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => 
		   array( array( "playerPiece", "commonPiece", "anywhere" ) ) )
		   ),

	2 => array(
	     0 => array(
		   "more" => 3,
		   "text" => clienttranslate("You may do up to 3 standard moves, using your common pieces."),
		   "effects" => array( "movePiece", "ifNotSkipped",
					       "movePiece", "ifNotSkipped",
					       "movePiece"  ),
		   "effecttargets" => array(
		      array( array( "playerPiece", "commonPiece", "anywhere"),
			     "standard", "move", array() ),
		      array( array( "playerPiece", "commonPiece", "anywhere"),
			     "standard", "move", array() ),
		      array( array( "playerPiece", "commonPiece", "anywhere"),
			     "standard", "move", array() )
						    ),
		   "skippable" => array(0,2,4) ),
	     1 => array(
		   "more" => 5,
		   "text" => clienttranslate("Gain an action."),
		   "effects" => array( "gainAction" ),
		   "effecttargets" => array( array() ) )
		   ),

	3 => array(
	     0 => array(
		   "more" => 4,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" =>
		   array( array( "playerPiece", "commonPiece", "anywhere" ))),
	     1 => array(
		   "more" => 5,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square, or convert 1 common enemy piece to your color."),
		   "effects" => array( "orEffects2", "placePiece", "convertPiece" ),
		   "effecttargets" => array(
		   array( "playerPiece", "commonPiece", "anywhere" ),
		   array( "commonPiece", "commonPiece", "anywhere" ) ) )
		   ),

	4 => array(
	     0 => array(
		   "more" => 3,
		   "text" => clienttranslate("Do 1 standard move with one of your common pieces, or upgrade 1 of your common pieces."),
		   "effects" => array( "chooseOption", "ifChoiceOne", "movePiece", "ifChoiceTwo", "upgradePiece" ),
		   "effecttargets" => array(
	    array( clienttranslate('${actplayer} must choose an effect'),
		   clienttranslate('${you} must choose an effect'),
		   clienttranslate("Move a piece"),
		   clienttranslate("Upgrade a piece")
		   ),
		array( array( "playerPiece", "commonPiece", "anywhere" ),
		       "standard", "move", array() ),
		array( "playerPiece", "commonPiece", "anywhere" ) ) ),
	     1 => array(
		   "more" => 6,
		   "text" => clienttranslate("Place 2 common pieces of your color on empty squares."),
		   "effects" => array( "placePiece", "always", "placePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ),
			array( "playerPiece", "commonPiece", "anywhere" ) ) )
		   ),

	5 => array(
	     0 => array(
		   "more" => 4,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square, or upgrade 1 of your common pieces."),
		   "effects" => array( "orEffects2", "placePiece", "upgradePiece" ),
		   "effecttargets" => array(
		   array( "playerPiece", "commonPiece", "anywhere" ),
		   array( "playerPiece", "commonPiece", "anywhere" ) ) ),
	     1 => array(
		   "more" => 4,
		   "text" => clienttranslate("You may do 1 combat leap with one of your common pieces."),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "commonPiece", "anywhere" ),
		       "combat", "leap", array() ) ),
		   "skippable" => array(0) )
		   ),

	6 => array(
	     0 => array(
		   "more" => 4,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" =>
		   array( array( "playerPiece", "commonPiece", "anywhere" ))),
	     1 => array(
		   "more" => 6,
		   "text" => clienttranslate("Upgrade 1 of your common pieces. Gain an action."),
		   "effects" => array( "upgradePiece", "always", "gainAction" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ),
			array() ) )
		   ),

	7 => array(
	     0 => array(
		   "more" => 4,
		   "text" => clienttranslate("Gain an action."),
		   "effects" => array( "gainAction" ),
		   "effecttargets" => array( array() ) ),
	     1 => array(
		   "more" => 5,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => 
		   array( array( "playerPiece", "commonPiece", "anywhere" ) ) )
		   ),

	8 => array(
	     0 => array(
		   "more" => 5,
		   "text" => clienttranslate("Place 1 heroic piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" =>
		   array( array( "playerPiece", "heroicPiece", "anywhere" ) )),
	     1 => array(
		   "more" => 4,
		   "text" => clienttranslate("You may do 1 standard move and 1 combat move (in either order), using your common pieces."),
		   "effects" => array( "movePiece", "ifNotSkipped", "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "commonPiece", "anywhere"),
		       "combat", "move", array() ),
		array( array( "playerPiece", "commonPiece", "anywhere"),
		       "oneCombatMoveOnly", "move", array() )
					    ),
		   "skippable" => array(0) )
		   ),

	9 => array(
	     0 => array(
		   "more" => 5,
		   "text" => clienttranslate("Place 2 common pieces of your color on empty squares."),
		   "effects" => array( "placePiece", "always", "placePiece" ),
		   "effecttargets" => array(
			array( "playerPiece", "commonPiece", "anywhere" ),
			array( "playerPiece", "commonPiece", "anywhere" ) ) ),
	     1 => array(
		   "more" => 5,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => 
		   array( array( "playerPiece", "commonPiece", "anywhere" ) ) )
		   ),

	10 => array(
	     0 => array(
		   "more" => 3,
		   "text" => clienttranslate("Place 1 common piece of your color on any empty square."),
		   "effects" => array( "placePiece" ),
		   "effecttargets" => 
		   array( array( "playerPiece", "commonPiece", "anywhere" ) )),
	     1 => array(
		   "more" => 4,
		   "text" => clienttranslate("You may do 1 combat move or 2 standard moves, using your non-legendary pieces."),
		   "effects" => array( "movePiece",
				       call_user_func(array($this, "andcond"),
	      array($this, "ifNotSkipped"), array($this, "ifNotCombatMoved")),
				       "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "nonLegendaryPiece", "anywhere"),
		       "combat", "move", array() ),
		array( array( "playerPiece", "nonLegendaryPiece", "anywhere"),
		       "standard", "move", array() )
					    ),
		   "skippable" => array(0,2) )
		   ),

	11 => array(
	     0 => array(
		   "more" => 2,
		   "text" => clienttranslate("You may do 1 standard leap with one of your common pieces."),
		   "effects" => array( "movePiece" ),
		   "effecttargets" => array(
		array( array( "playerPiece", "commonPiece", "anywhere" ),
		       "standard", "leap", array() ) ),
		   "skippable" => array(0) ),
	     1 => array(
		   "more" => 5,
		   "text" => clienttranslate("Gain an action."),
		   "effects" => array( "gainAction" ),
		   "effecttargets" => array( array() ) )
		   )
		      );

$this->card_contents = array(
			     "Northern" => $this->imperial_contents,
			     "Southern" => $this->imperial_contents,
			     "Highland" => $this->highland_contents,
			     "Sylvan" => $this->sylvan_contents,
			     "Everfrost" => $this->everfrost_contents,
		  		 "Nethervoid" => $this->nethervoid_contents,
		 		 "Etherweave" => $this->etherweave_contents,
			     "Legends" => $this->legends_contents,
			     "Flare" => $this->flares
			     );


/* Tasks :
   name and text as on the card, translated
   type is "destruction", "colored", "summoning", "pattern" or "enemy"
   difficulty is "simple" or "advanced"
   criteria is a function representing the task
   critargs is a (possibly empty) array of arguments for this function
   points is the number of points the task grants

	 => array(
		   "name" => clienttranslate(""),
		   "type" => "",
		   "difficulty" => "",
		   "text" => clienttranslate(""),
		   "criteria" => "",
		   "critargs" => array(),
		   "points" => 
		   ),
*/

$this->tasks = array(
	0 => array(
		   "name" => clienttranslate("Destruction"),
		   "type" => "destruction",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You destroyed at least 3 enemy pieces this turn."),
		   "criteria" => "destroyAtLeast",
		   "critargs" => array( 3, 0 ),
		   "points" => 1
		   ),

	1 => array(
		   "name" => clienttranslate("Devastation"),
		   "type" => "destruction",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You destroyed at least 4 enemy pieces this turn."),
		   "criteria" => "destroyAtLeast",
		   "critargs" => array( 4, 0 ),
		   "points" => 2
		   ),

	2 => array(
		   "name" => clienttranslate("Heroic Destruction"),
		   "type" => "destruction",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You destroyed at least 3 enemy pieces this turn, at least 1 of them upgraded."),
		   "criteria" => "destroyAtLeast",
		   "critargs" => array( 3, 1 ),
		   "points" => 2
		   ),

	3 => array(
		   "name" => clienttranslate("Heroic Devastation"),
		   "type" => "destruction",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You destroyed at least 4 enemy pieces this turn, at least 2 of them upgraded."),
		   "criteria" => "destroyAtLeast",
		   "critargs" => array( 4, 2 ),
		   "points" => 3
		   ),

	4 => array(
		   "name" => clienttranslate("End of Legends"),
		   "type" => "destruction",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You destroyed at least 1 legendary enemy piece or 2 heroic enemy pieces this turn."),
		   "criteria" => "endOfLegends",
		   "critargs" => array(),
		   "points" => 2
		   ),

	5 => array(
		   "name" => clienttranslate("Red Conquest"),
		   "type" => "colored",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 3 pieces on red squares, and at least 2 of them are upgraded."),
		   "criteria" => "conquestTask",
		   "critargs" => array( 3, 2, 0, "redSquares" ),
		   "points" => 2
		   ),

	6 => array(
		   "name" => clienttranslate("Green Conquest"),
		   "type" => "colored",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 3 pieces on green squares, and at least 2 of them are upgraded."),
		   "criteria" => "conquestTask",
		   "critargs" => array( 3, 2, 0, "greenSquares" ),
		   "points" => 2
		   ),

	7 => array(
		   "name" => clienttranslate("Color Conquest"),
		   "type" => "colored",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 5 pieces on red and/or green squares, and at least 3 of them are upgraded."),
		   "criteria" => "conquestTask",
		   "critargs" => array( 5, 3, 0, "coloredSquares" ),
		   "points" => 3
		   ),

	8 => array(
		   "name" => clienttranslate("Red Legends"),
		   "type" => "colored",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You have at least 2 upgraded pieces on red squares, and at least 1 of them is legendary."),
		   "criteria" => "conquestTask",
		   "critargs" => array( 0, 2, 1, "redSquares" ),
		   "points" => 3
		   ),

	9 => array(
		   "name" => clienttranslate("Green Legends"),
		   "type" => "colored",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You have at least 2 upgraded pieces on green squares, and at least 1 of them is legendary."),
		   "criteria" => "conquestTask",
		   "critargs" => array( 0, 2, 1, "greenSquares" ),
		   "points" => 3
		   ),

	10 => array(
		   "name" => clienttranslate("Rainbow Dominance"),
		   "type" => "colored",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have more upgraded pieces on red squares and more upgraded pieces on green squares than your opponent does."),
		   "criteria" => "rainbowDominance",
		   "critargs" => array(),
		   "points" => 2
		   ),

	11 => array(
		   "name" => clienttranslate("Red Summoning"),
		   "type" => "summoning",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You summoned at least 2 beings this turn, at least 1 on a red square."),
		   "criteria" => "summoningTask",
		   "critargs" => array( 2, 0, 1, "red" ),
		   "points" => 1
		   ),

	12 => array(
		   "name" => clienttranslate("Green Summoning"),
		   "type" => "summoning",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You summoned at least 2 beings this turn, at least 1 on a green square."),
		   "criteria" => "summoningTask",
		   "critargs" => array( 2, 0, 1, "green" ),
		   "points" => 1
		   ),

	13 => array(
		   "name" => clienttranslate("Colored Summoning"),
		   "type" => "summoning",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You summoned at least 2 beings on red and/or green squares this turn. (Both of them may have been summoned on the same square.)"),
		   "criteria" => "summoningTask",
		   "critargs" => array( 2, 0, 2, "colored" ),
		   "points" => 2
		   ),

	14 => array(
		   "name" => clienttranslate("Legendary Summoning"),
		   "type" => "summoning",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You summoned at least 2 beings this turn, at least one of them legendary."),
		   "criteria" => "summoningTask",
		   "critargs" => array( 2, 1, 0, "red" ),
		   "points" => 2
		   ),

	15 => array(
		   "name" => clienttranslate("Central Dominance"),
		   "type" => "pattern",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 5 pieces on the nine central squares, and at least 2 of them are upgraded."),
		   "criteria" => "dominanceTask",
		   "critargs" => array( 5, 2, 0, "central" ),
		   "points" => 1
		   ),

	16 => array(
		   "name" => clienttranslate("Center Cross"),
		   "type" => "pattern",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 5 pieces on the central squares in a + or x pattern."),
		   "criteria" => "dominanceTask",
		   "critargs" => array( 5, 0, 0, "centerCross" ),
		   "points" => 2
		   ),

	17 => array(
		   "name" => clienttranslate("Line Dominance"),
		   "type" => "pattern",
		   "difficulty" => "simple",
		   "text" => clienttranslate("On one of the orthogonal lines through the central squares, you have 6 more pieces than your opponent does."),
		   "criteria" => "dominanceTask",
		   "critargs" => array( 6, 0, 1, "lines" ),
		   "points" => 2
		   ),

	18 => array(
		   "name" => clienttranslate("Diagonals"),
		   "type" => "pattern",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You have at least 4 pieces on each diagonal, and at least 1 of your pieces on each diagonal is upgraded."),
		   "criteria" => "diagonalsTask",
		   "critargs" => array(),
		   "points" => 3
		   ),

	19 => array(
		   "name" => clienttranslate("Side Chain"),
		   "type" => "pattern",
		   "difficulty" => "simple",
		   "text" => clienttranslate("Squares on two opposite sides of the board are connected by a chain of your pieces."),
		   "criteria" => "sideChain",
		   "critargs" => array(),
		   "points" => 3
		   ),

	20 => array(
		   "name" => clienttranslate("Corner Chain"),
		   "type" => "pattern",
		   "difficulty" => "simple",
		   "text" => clienttranslate("Squares in two opposite corners of the board are connected by a chain of your pieces."),
		   "criteria" => "cornerChain",
		   "critargs" => array(),
		   "points" => 3
		   ),

	21 => array(
		   "name" => clienttranslate("Imprisonment"),
		   "type" => "enemy",
		   "difficulty" => "simple",
		   "text" => clienttranslate("You have at least 6 pieces adjacent to the same enemy piece."),
		   "criteria" => "dominanceTask",
		   "critargs" => array( 6, 0, 0, "aroundEnemy" ),
		   "points" => 1
		   ),

	22 => array(
		   "name" => clienttranslate("Envelopment"),
		   "type" => "enemy",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("You have at least 7 pieces adjacent to the same enemy piece."),
		   "criteria" => "dominanceTask",
		   "critargs" => array( 7, 0, 0, "aroundEnemy" ),
		   "points" => 2
		   ),

	23 => array(
		   "name" => clienttranslate("Isolation"),
		   "type" => "enemy",
		   "difficulty" => "advanced",
		   "text" => clienttranslate("An enemy piece is within 2 squares of another enemy piece, but it cannot get adjacent to any enemy piece in 3 or fewer moves through empty squares."),
		   "criteria" => "isolationTask",
		   "critargs" => array(),
		   "points" => 2
		   )
		     );


/**** TODO ****/
/* discardFlare */
