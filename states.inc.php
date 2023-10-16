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
 * states.inc.php
 *
 * TashKalarExpansions game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => clienttranslate("Game setup"),
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 5 )
    ),
    
    5 => array(
        "name" => "randomDecks",
        "description" => '',
        "type" => "game",
        "action" => "stRandomDecks",
        "transitions" => array( "beginGame" => 20, "chooseDecks" => 10, "initialPiecesDuel" => 16, "initialPiecesMelee" => 17 )
    ),
    
    10 => array(
    		"name" => "deckChoice",
    		"description" => clienttranslate('${actplayer} must choose a school'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a school'),
    		"type" => "activeplayer",
		"args" => "argDeckChoice",
    		"possibleactions" => array( "chooseDeck" ),
    		"transitions" => array( "chooseDeck" => 15, "zombiePass" => 99 )
    ),
    
    15 => array(
        "name" => "nextDeck",
        "description" => '',
        "type" => "game",
        "action" => "stNextDeck",
        "transitions" => array( "beginGame" => 20, "nextDeck" => 10, "initialPiecesDuel" => 16, "initialPiecesMelee" => 17 )
    ),
    
    16 => array(
		"name" => "initialPiecesDuel",
		"description" => clienttranslate('${actplayer} must place the initial pieces'),
    		"descriptionmyturn" => clienttranslate('${you} must place one of your pieces on a square marked <div id="DMmark"></div>'),
    		"type" => "activeplayer",
    		"possibleactions" => array( "placeInitialPieces" ),
    		"transitions" => array( "placeInitialPieces" => 19, "zombiePass" => 20 )
		),

    17 => array(
		"name" => "initialPiecesMelee",
		"description" => clienttranslate('${actplayer} must place the initial pieces'),
    		"descriptionmyturn" => clienttranslate('${you} must place a ${color} piece on a square adjacent to a <div id="DMMmark"></div> symbol'),
    		"type" => "activeplayer",
		"args" => "argMeleeInitial",
    		"possibleactions" => array( "placeInitialPieces",
					    "placeLastPiece" ),
    		"transitions" => array( "placeInitialPieces" => 17,
				"placeLastPiece" => 19, "zombiePass" => 20 )
		),

    19 => array(
        "name" => "setFirstPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stSetFirstPlayer",
        "transitions" => array( "" => 20)
    ),

    20 => array(
        "name" => "turnBegin",
        "description" => '',
        "type" => "game",
        "action" => "stTurnBegin",
        "transitions" => array( "firstAction" => 21 )
    ),

    21 => array(
    		"name" => "actionChoice",
    		"description" => clienttranslate('${actplayer} must play a piece or select a card (${actions} actions left)'),
    		"descriptionmyturn" => clienttranslate('${you} must play a piece or select a card (${actions} actions left)'),
    		"type" => "activeplayer",
		"args" => "argActionChoice",
		"updateGameProgression" => true,   
    		"possibleactions" => array( "playPiece", "playCard", "discardCard", "playFlare", "playFrozen", "playWarp", "pieceShortage", "browseHistory" ),
    		"transitions" => array( "playPiece" => 70, "playCard" => 40, "chooseColorImpro" => 36, "chooseColorLegend" => 38, "discardCard" => 70, "chooseColorFlare" => 28, "playFlare" => 30, "playFrozen" => 40, "playWarp" => 40, "pieceShortage" => 22, "browseHistory" => 95, "zombiePass" => 70 )
    ),
    
    22 => array(
    		"name" => "pickPiece",
    		"description" => clienttranslate('${actplayer} has no pieces left and must pick one up'),
    		"descriptionmyturn" => clienttranslate('${you} have no pieces left and must pick one up'),
    		"type" => "activeplayer",
		"args" => "argPickPiece",
    		"possibleactions" => array( "playPiece", "playCard", "cancelPick", "browseHistory" ),
    		"transitions" => array( "playPiece" => 70, "playCard" => 40, "chooseColorLegend" => 38, "cancelPick" => 21, "browseHistory" => 95, "zombiePass" => 70 )
    ),

    /*
    23 => array(
    		"name" => "flareOption",
    		"description" => clienttranslate('${actplayer} may invoke a flare'),
    		"descriptionmyturn" => clienttranslate('${you} may invoke a flare or'),
    		"type" => "activeplayer",
    		"possibleactions" => array( "playFlare", "skip", "browseHistory" ),
    		"transitions" => array( "playFlare" => 30, "skip" => 80, "browseHistory" => 95, "zombiePass" => 90 )
    ),
    */

    28 => array(
    		"name" => "chooseColorFlare",
    		"description" => clienttranslate('${actplayer} must choose against which color the flare is invoked'),
    		"descriptionmyturn" => clienttranslate('${you} must choose against which color the flare is invoked'),
    		"type" => "activeplayer",
		"args" => "argChooseColorFlare",
    		"possibleactions" => array( "colorChosen", "browseHistory" ),
    		"transitions" => array( "colorChosen" => 30, "browseHistory" => 95, "zombiePass" => 50 )
		),

    30 => array(
        "name" => "initFlare",
        "description" => '',
        "type" => "game",
        "action" => "stInitFlare",
        "transitions" => array( "flareInitiated" => 50, "flaresDone" => 21, "flaresTurnDoneHF" => 75, "flaresTurnDoneDM" => 76 )
    ),
    
    36 => array(
    		"name" => "chooseColorImpro",
    		"description" => clienttranslate('${actplayer} must choose a color for improvised summoning'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a color for improvised summoning'),
    		"type" => "activeplayer",
		"args" => "argChooseColorImpro",
    		"possibleactions" => array( "playPiece", "playCard", "cancelPick", "browseHistory" ),
    		"transitions" => array( "playCard" => 40, "chooseColorLegend" => 38, "pieceShortage" => 22, "browseHistory" => 95, "zombiePass" => 70 )
		),

    38 => array(
    		"name" => "chooseColorLegend",
    		"description" => clienttranslate('${actplayer} must choose a color to score the legend in'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a color to score your legend in'),
    		"type" => "activeplayer",
		"args" => "argChooseColorLegend",
    		"possibleactions" => array( "colorChosen", "browseHistory" ),
    		"transitions" => array( "colorChosen" => 40, "autoEffect" => 50, "browseHistory" => 95, "zombiePass" => 70 )
		),

    40 => array(
	"name" => "initEffect",
        "description" => '',
        "type" => "game",
        "action" => "stInitEffect",
        "transitions" => array( "effectsInitiated" => 50 )
	// array( "effectCard" => 41, "effectSquare" => 42, "effectPiece" => 43, "effectMovePiece" => 44, "autoEffect" => 50 )
    ),
    
    41 => array(
    		"name" => "cardChoice",
    		"description" => clienttranslate('${actplayer} must choose a card'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a card'),
    		"type" => "activeplayer",
		"args" => "argCardChoice",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),
    
    42 => array(
    		"name" => "squareChoice",
    		"description" => clienttranslate('${actplayer} must choose a square'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a square'),
    		"type" => "activeplayer",
		"args" => "argPlaceInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),
    
    43 => array(
    		"name" => "pieceChoice",
    		"description" => clienttranslate('${actplayer} must choose a square'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a square'),
    		"type" => "activeplayer",
		"args" => "argEffectInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),
    
    44 => array(
    		"name" => "moveChoice",
    		"description" => clienttranslate('${actplayer} must move a piece'),
    		"descriptionmyturn" => clienttranslate('${you} must move a piece'),
    		"type" => "activeplayer",
		"args" => "argEffectInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),
    
    45 => array(
    		"name" => "directionChoice",
    		"description" => clienttranslate('${actplayer} must choose a direction'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a direction'),
    		"type" => "activeplayer",
		"args" => "argEffectInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),

    46 => array(
    		"name" => "orEffects2",
    		"description" => clienttranslate('${actplayer} must choose an effect'),
    		"descriptionmyturn" => clienttranslate('${you} must choose an effect'),
    		"type" => "activeplayer",
		"args" => "argEffectInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),

    47 => array(
    		"name" => "orEffects3",
    		"description" => clienttranslate('${actplayer} must choose an effect'),
    		"descriptionmyturn" => clienttranslate('${you} must choose an effect'),
    		"type" => "activeplayer",
		"args" => "argEffectInput",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),

    48 => array(
    		"name" => "chooseOption",
    		"description" => clienttranslate('${questionhe}'),
    		"descriptionmyturn" => clienttranslate('${questionyou}'),
    		"type" => "activeplayer",
		"args" => "argOptions",
    		"possibleactions" => array( "effectPlayed", "skip", "browseHistory" ),
    		"transitions" => array( "effectPlayed" => 50, "skip" => 50, "browseHistory" => 95, "zombiePass" => 50 )
    ),
    
    50 => array(
	"name" => "nextEffect",
        "description" => '',
        "type" => "game",
        "action" => "stNextEffect",
        "transitions" => array( "effectCard" => 41, "effectSquare" => 42,
				"effectPiece" => 43, "effectMovePiece" => 44,
				"effectDirection" => 45, "orEffects2" => 46,
				"orEffects3" => 47, "effectChoose" => 48,
				"autoEffect" => 50, "effectFalse" => 50,
				"chooseColorLegend" => 38, 
				"chooseFrozen" => 55,
				"effectsDone" => 70, "nextFlare" => 30 )
    ),
    
    55 => array(
    		"name" => "frozenChoice",
    		"description" => clienttranslate('${actplayer} must choose a frozen effect to put into play'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a frozen effect to put into play'),
    		"type" => "activeplayer",
		"args" => "argFrozenChoice",
    		"possibleactions" => array( "frozenChosen", "browseHistory" ),
    		"transitions" => array( "frozenChosen" => 70, "browseHistory" => 95, "zombiePass" => 70 )
    ),
    
    70 => array(
	"name" => "nextAction",
        "description" => '',
        "type" => "game",
        "action" => "stNextAction",
        "transitions" => array( "nextAction" => 21, "actionsDoneHF" => 75, "actionsDoneDM" => 76 )
    ),

    75 => array(
    		"name" => "turnEndHF",
    		"description" => clienttranslate('${actplayer} may invoke a flare or claim a task'),
    		"descriptionmyturn" => clienttranslate('${you} may invoke a flare, claim a task or'),
    		"type" => "activeplayer",
		"args" => "argTurnEnd",
    		"possibleactions" => array( "playFlare", "playFrozen", "playWarp", "chooseTask", "skip", "browseHistory" ),
    		"transitions" => array( "chooseColorFlare" => 28, "playFlare" => 30, "playFrozen" => 40, "playWarp" => 40, "chooseTask" => 90, "skip" => 90, "browseHistory" => 95, "zombiePass" => 90 )
		),

    76 => array(
    		"name" => "turnEndDM",
    		"description" => clienttranslate('${actplayer} may invoke a flare'),
    		"descriptionmyturn" => clienttranslate('${you} may invoke a flare or'),
    		"type" => "activeplayer",
		"args" => "argTurnEnd",
    		"possibleactions" => array( "playFlare", "playFrozen", "playWarp", "skip", "chooseColor", "browseHistory" ),
    		"transitions" => array( "chooseColorFlare" => 28, "playFlare" => 30, "playFrozen" => 40, "playWarp" => 40, "skip" => 90, "chooseColor" => 88, "browseHistory" => 95, "zombiePass" => 90 )
		),

    /**    
    80 => array(
	"name" => "checkTasks",
        "description" => '',
        "type" => "game",
        "action" => "stCheckTasks",
        "transitions" => array( "tasksDone" => 90, "askTask" => 85 )
    ),

    85 => array(
    		"name" => "taskChoice",
    		"description" => clienttranslate('${actplayer} may claim a task'),
    		"descriptionmyturn" => clienttranslate('${you} may claim a task'),
    		"type" => "activeplayer",
		"args" => "argTaskChoice",
    		"possibleactions" => array( "chooseTask", "skip", "browseHistory" ),
    		"transitions" => array( "chooseTask" => 90, "skip" => 90, "browseHistory" => 95, "zombiePass" => 90 )
    ),
    **/
   
    88 => array(
    		"name" => "chooseColor",
    		"description" => clienttranslate('${actplayer} must choose a color to score the unpaired pieces in'),
    		"descriptionmyturn" => clienttranslate('${you} must choose a color to score your unpaired pieces in'),
    		"type" => "activeplayer",
		"args" => "argChooseColor",
    		"possibleactions" => array( "colorChosen", "browseHistory" ),
    		"transitions" => array( "colorChosen" => 90, "browseHistory" => 95, "zombiePass" => 90 )
		),

    90 => array(
	"name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array( "nextPlayer" => 20, "endGame" => 99 )
    ),

    95 => array(
	"name" => "browseHistory",
	"description" => '',
	"type" => "game",
	"action" => "stBrowseHistory",
	"transitions" => array( "actionChoice" => 21, "pickPiece" => 22,
				"chooseColorFlare" => 28,
				"chooseColorImpro" => 36,
				"chooseColorLegend" => 38,
				"effectCard" => 41, "effectSquare" => 42,
				"effectPiece" => 43, "effectMovePiece" => 44,
				"effectDirection" => 45, "orEffects2" => 46,
				"orEffects3" => 47, "effectChoose" => 48,
				"frozenChoice" => 55,
				"turnEndHF" => 75, "turnEndDM" => 76 )
    ),
    
/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify.
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);


