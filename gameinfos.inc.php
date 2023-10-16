<?php

$gameinfos = array( 


// Game designer (or game designers, separated by commas)
'designer' => 'Vlaada Chvátil',       

// Game artist (or game artists, separated by commas)
'artist' => 'David Cochard',         

// Year of FIRST publication of this game. Can be negative.
'year' => 2013,                 

// Game publisher
'publisher' => 'Czech Games Edition',                     

// Url of game publisher website
'publisher_website' => 'http://czechgames.com/',   

// Board Game Geek ID of the publisher
'publisher_bgg_id' => 7345,

// Board game geek if of the game
'bgg_id' => 146278,

// Game presentation
// Short game presentation text that will appear on the game description page, structured as an array of paragraphs.
// Each paragraph must be wrapped with totranslate() for translation and should not contain html (plain text without formatting).
// A good length for this text is between 100 and 150 words (about 6 to 9 lines on a standard display)
'presentation' => array(
totranslate("Tash-Kalar is the ancient art of magical combat performed in arenas and combat pits throughout the known world."),
totranslate("Tash-Kalar is also the name of the oldest and most famous arena, the place where the game began."),
totranslate("And Tash-Kalar is an exciting board game that confronts players with the same intellectual challenges that dueling mages have faced for centuries."),
totranslate("ou can play the game with 2, 3, or 4 players, in teams or individually. Whether you are playing the High Form or a deathmatch, all games of Tash-Kalar have the same basic rules: Players take turns creating magical stones and placing them in patterns that allow them to summon fantastical beings. Your beings can move around the arena, disrupt opponents' patterns, and form new patterns that allow you to summon more beings."),
totranslate("Each form of Tash-Kalar has its own means of scoring and deciding
on a victor."),
totranslate("Game banner by Rob Robinson aka zombiegod on BGG.")
),

// Players configuration that can be played (ex: 2 to 4 players)
'players' => array( 2, 3, 4 ),    

// Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
'suggest_player_number' => 2,

// Discourage players to play with these numbers of players. Must be null if there is no such advice.
'not_recommend_player_number' => null,
// 'not_recommend_player_number' => array( 2, 3 ),      // <= example: this is not recommended to play this game with 2 or 3 players


// Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
'estimated_duration' => 60,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
'fast_additional_time' => 90,

// Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
'medium_additional_time' => 100,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
'slow_additional_time' => 150,           

// If you are using a tie breaker in your game (using "player_score_aux"), you must describe here
// the formula used to compute "player_score_aux". This description will be used as a tooltip to explain
// the tie breaker to the players.
// Note: if you are NOT using any tie breaker, leave the empty string.
//
// Example: 'tie_breaker_description' => totranslate( "Number of remaining cards in hand" ),
'tie_breaker_description' => totranslate( "Number of upgraded pieces on the board, then total number of pieces on the board" ).totranslate( ". If playing Deathmatch Melee, consider first the scores in other colors, sorted in ascending order." ),

// Game is "beta". A game MUST set is_beta=1 when published on BGA for the first time, and must remains like this until all bugs are fixed.
'is_beta' => 1,                     

// Is this game cooperative (all players wins together or loose together)
'is_coop' => 0, 


// Complexity of the game, from 0 (extremely simple) to 5 (extremely complex)
'complexity' => 3,    

// Luck of the game, from 0 (absolutely no luck in this game) to 5 (totally luck driven)
'luck' => 1,    

// Strategy of the game, from 0 (no strategy can be setup) to 5 (totally based on strategy)
'strategy' => 5,    

// Diplomacy of the game, from 0 (no interaction in this game) to 5 (totally based on interaction and discussion between players)
'diplomacy' => 0,    


// Games categories
//  You can attribute any number of "tags" to your game.
//  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
'tags' => array( 1, 11, 30, 100, 200, 204, 206 )
);
