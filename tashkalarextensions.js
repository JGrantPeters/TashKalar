/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TashKalarExpansions implementation : © Benjamin Wack <benjamin.wack@free.fr>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * tashkalarexpansions.js
 *
 * TashKalarExpansions user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    "ebg/zone"
],
function (dojo, declare) {
    return declare("bgagame.tashkalarexpansions", ebg.core.gamegui, {
        constructor: function(){
            // console.log('tashkalarexpansions constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

	    this.args = null;
	    this.from_x = 0;
	    this.from_y = 0;
	    this.player = null;
	    this.basestate = "";
	    this.discarded = "";
	    this.returned = [];
	    this.saved_description = "";
	    this.savepoints = [];
	    this.lastsavepoint = -1;
	    this.undohandle = null;
	    this.redohandle = null;
	    this.playerDiscardHandle = null;
	    this.flareAnim = null;
	    this.turn_counter = 0;
	    this.warp_effects = [1,2,3,4,5,6,8,11,12,14,16];
	    this.pending = -1;
	    this.next_token = 1;
	    this.keep_discard = false;
	    this.discard_weights = {};
	    
	    this.destroyed = 0;
	    this.theDestroyed = new ebg.zone();
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            // console.log( "Starting game setup" );
//            console.log(this.gamedatas);

	    if ( gamedatas.last_player != 0 )
		dojo.style( 'endwarning', 'display', 'block' );
	    dojo.addClass( "board", gamedatas.game_form );

	    // Undo / redo buttons
	    dojo.place( "<span id='my_undoredo_wrap'></span>", "gotonexttable_wrap", "after" );
	    dojo.place( "<div id='my_redo_button' class='redob dimmedbutton'><div></div></div>", "my_undoredo_wrap" );
	    this.addTooltipToClass( "redob", '', _('Redo last undone move') );
	    if (gamedatas.after > 0)
	    {
		dojo.removeClass( 'my_redo_button', 'dimmedbutton' );
		this.redohandle = dojo.connect( $('my_redo_button'), 'onclick', this, 'onRedoStep' );
	    }
	    dojo.place( "<div id='my_undo_button' class='undob dimmedbutton'><div></div></div>", "my_undoredo_wrap" );
	    this.addTooltipToClass( "undob", '', _('Undo last move') );
	    if (gamedatas.before > 0)
	    {
		dojo.removeClass( 'my_undo_button', 'dimmedbutton' );
		this.undohandle = dojo.connect( $('my_undo_button'), 'onclick', this, 'onUndoStep' );
	    }

	    // Common decks
	    /*** DEPRECATED
	    dojo.place( this.format_block( 'jstpl_decks_left', {} ),
			'right-side-first-part', "first" );
	    ***/

            // Setting up player boards
	    this.claimed_tasks = [];

	    // TODO : too small, but where else ? Also attach to id rather than class
	    var points = 0;
	    if ( gamedatas.game_form == 'highform')
		points = 9;
	    else
	    {
		switch ( Object.keys(gamedatas.players).length )
		{
		case 2:
		    points = 18;
		    break;
		case 3:
		    points = 12;
		    break;
		case 4:
		default:
		    points = 10;
		    break;		    
		}
	    }
	    var end_condition = dojo.string.substitute( _('Game will end at ${points} points'), {points:points} );
	    this.addTooltipToClass( 'icon16_point', end_condition, '');

	    dojo.place( this.format_block( 'jstpl_gateway', {} ),
			'tokens', 'after' );
	    this.addTooltip( 'gateway', _('Gateway'), '' ) ;

	    var colors = [ '037cb1', 'dc2515', 'd6b156', '8ec459',
			   'f0f9ff', 'f4913c', '6a548f' ];
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
		var player_board_div = $('player_board_'+player_id);

		dojo.place( this.format_block('jstpl_player_board',
		{ id : player_id ,
		  color: gamedatas.players[player_id]['color'] } ),
	    		player_board_div );

		/***
		if ( player.deck != -2 )
		{
		    this.updateLastAuto( player );
		}
		***/

		if (player_id != this.player_id &&
		    (Object.keys(gamedatas.players).length == 2
		     || !this.isSpectator ))
		{
		    dojo.place( this.format_block('jstpl_flare_diff',
						  { opcolor : player.color } ),
//				player_board_div );//flares_diff') );
				"diffs_placeholder_"+player_id );
		}

		if (Object.keys(gamedatas.players).length > 2)
		{
		    for( var op_id in gamedatas.players )
		    {
			if ( op_id != player_id )
			{
			    dojo.place(
			    this.format_block('jstpl_melee',
						  { id : player_id,
						    opid : op_id } ),
				"impros_placeholder_"+player_id );
			}
		    }
		    for( var op_id in gamedatas.players )
		    {
			if ( op_id != player_id )
			{
			    dojo.place(
			    this.format_block('jstpl_impro',
						  { id : player_id,
						    opid : op_id } ),
				"impros_placeholder_"+player_id );
			}
		    }
		    this.addTooltipToClass( "improicon",
		_('Improvised summoning : this player may use 1 piece of this color to summon a being'), '');
		    this.addTooltipToClass( "melee_container",
					  _('This player\'s score in this color'), '');
		}

		if ( gamedatas.game_form == 'highform' )
		{
		    var title = dojo.string.substitute( _('Tasks claimed by ${name}'), {name:player.name} );
		    dojo.place( this.format_block('jstpl_claimed_tasks',
						  { title: title,
						    id: player_id } ),
				$('claimed_tasks') );
		    this.claimed_tasks[player_id] = new ebg.stock;
		    this.claimed_tasks[player_id].create( this, $('claimed_tasks_'+player_id), 150, 89 );
		    this.claimed_tasks[player_id].setSelectionMode(0);
		}

		if ( gamedatas.players[player_id]['color'] == 'f0f9ff' )
		{
		    this.gamedatas.players[player_id]['color_back'] = 'a3e4ec';
		    dojo.destroy( $('frozen_effect') );
		    dojo.place( this.format_block( 'jstpl_frozen', {} ),
				'pieces_left_'+player_id, 'after' );
		    dojo.style( 'deck_container_'+player_id,
				'marginBottom', '14px' );
		    dojo.style( 'pieces_container_'+player_id,
				'marginBottom', '14px' );
		    dojo.style( 'legendarypieces_container_'+player_id,
				'marginBottom', '14px' );
		    if ( gamedatas.frozen >= 0 )
		    {
			var hoffset = 125 * gamedatas.frozen / 2 + 2;
			dojo.style( 'frozen_card', 'backgroundPosition',
				    '-'+hoffset+'px -12px' );
			dojo.style( 'frozen_card', 'visibility', 'visible' );
			this.addTooltipHtml( 'frozen_effect',
				     '<div class="frozenicon"></div>'
				     +'<i>'+_(gamedatas.frozentext)+'</i>' );
		    }
		    else
			dojo.style( 'frozen_card', 'visibility', 'hidden' );
		}
		if ( gamedatas.players[player_id]['color'] == 'f4913c' )
		{
		    this.gamedatas.players[player_id]['color_back'] = '444444';
		}
		if ( gamedatas.players[player_id]['color'] == '6a548f' )
		{
		    dojo.destroy( $('warp_effect') );
		    dojo.place( this.format_block( 'jstpl_warp', {} ),
				'pieces_left_'+player_id, 'after' );
		    dojo.place( this.format_block( 'jstpl_malus', {} ),
				'player_score_'+player_id, 'after' );
		    this.addTooltip( 'pending_malus', _('Causality penalty'), '' ) ;
		    dojo.style( 'deck_container_'+player_id,
				'marginBottom', '14px' );
		    dojo.style( 'pieces_container_'+player_id,
				'marginBottom', '14px' );
		    dojo.style( 'legendarypieces_container_'+player_id,
				'marginBottom', '14px' );
		    this.pending = gamedatas.pending;
		    if ( gamedatas.pending >= 0 )
		    {
			dojo.style( 'pending_malus', 'display', 'inline' );
			var hoffset = 125 * gamedatas.pending / 2 + 2;
			dojo.style( 'warp_card', 'backgroundPosition',
				    '-'+hoffset+'px -12px' );
//			dojo.style( 'warp_card', 'visibility', 'visible' );
			this.addTooltipHtml( 'warp_effect',
				this.format_block( 'jstpl_warp_tip',
					{ name:_(gamedatas.pendingname),
					  text:_(gamedatas.pendingtext),
					  warptext:_(gamedatas.warptext),
					  deck:'Etherweave',
					  offset:250*gamedatas.pending } ) );
		    }
		    else
			dojo.style( 'warp_card', 'opacity', 0 );
		}
            }

	    if (Object.keys(gamedatas.players).length > 2)
		this.installMeleeIcons( gamedatas.players );

	    this.addTooltipToClass( "deck_container",
			_("Remaining cards in the player's deck"), '' );
	    this.addTooltipToClass( "pieces_container",
			_("Player's remaining ordinary pieces"), '' );
	    this.addTooltipToClass( "legendarypieces_container",
			_("Player's remaining legendary pieces"), '' );
	    this.addTooltipToClass( "upgradeddiff_container",
		_("Your opponent has that many more upgraded pieces than you"), '' );
	    this.addTooltipToClass( "piecesdiff_container",
		_("Your opponent has that many more pieces than you"), '' );
          
	    this.playerHand = new ebg.stock;
	    this.playerHand.create( this, $('hand'), 125, 210 );
	    this.playerHand.setSelectionMode(1);
	    /***
	    this.cardPlayed = new ebg.stock;
	    this.cardPlayed.create( this, $('card_played'), 125, 210 );
	    this.cardPlayed.setSelectionMode(0);
	    ***/
	    dojo.connect( this.playerHand, 'onChangeSelection', this, 'onCardSelect' );
	    // Explain there are 18 images per row in the CSS sprite image
	    // this.playerHand.image_items_per_row = 18;
	    // 1 line, non necessary

	    // Create cards types:
	    var decks = ['Northern', 'Southern', 'Highland', 'Sylvan', 'Everfrost', 'Nethervoid', 'Etherweave', 'Legends'];
	    for( var deck in decks )
	    {
		var max_index = 18;
		if ( decks[deck] == 'Legends' )
		    max_index = 12;
		for( var index = 0 ; index < max_index ; index++ )
		{
		    // Build card type id
		    var card_type_id = deck+"_"+index;
		    this.playerHand.addItemType( card_type_id, deck, g_gamethemeurl+'img/'+decks[deck]+'.jpg', index );
//		    this.cardPlayed.addItemType( card_type_id, deck, g_gamethemeurl+'img/'+decks[deck]+'.jpg', index );
		}
	    }

	    for( var index=0 ; index < 12 ; index++ )
	    {
		// Build card type id
		var card_type_id = "flare_"+index;
		this.playerHand.addItemType( card_type_id, 10, g_gamethemeurl+'img/flares.jpg', index );
//		this.cardPlayed.addItemType( card_type_id, 10, g_gamethemeurl+'img/flares.jpg', index );
	    }

	    // Prepare discard
	    this.playerDiscard = new ebg.stock;
	    this.playerDiscard.create( this, $('discard'), 125, 210 );
	    this.playerDiscard.setSelectionMode(0);
	    this.playerDiscard.autowidth = true;
	    this.playerDiscard.setOverlap(80,0);
//            this.playerDiscard.updateDisplay();
	    if ( !this.isSpectator
		 && gamedatas.players[this.player_id]['color'] != '000000' ) {
		var deck =
		  colors.indexOf(gamedatas.players[this.player_id]['color']);
		for( var index = 0 ; index < 18 ; index++ )
		  {
		      // Build card type id
		      this.playerDiscard.addItemType( index, index,
			g_gamethemeurl+'img/'+decks[deck]+'.jpg', index );
		  }
	      }

	    // Populate player's hand and discard
	    for (var card_i in gamedatas.hand)
		this.installCard( gamedatas.hand[card_i], this.playerHand,
				  'overall_player_board_'+this.player_id );
	    for (var card_i in gamedatas.discard)
		this.addCard( gamedatas.discard[card_i],
			      'overall_player_board_'+this.player_id );
	    this.playerDiscard.changeItemsWeight( this.discard_weights );
	    dojo.addClass( 'discard', 'hidden' );
	    dojo.connect( $('my_discard'), 'onclick', this,
			'toggleDiscard' );

	    if ( gamedatas.game_form == 'highform' )
	    {
		this.dontPreloadImage("board_DM.jpg");
		dojo.style( 'destroyed_pieces', 'display', 'none' );
		this.next_task = new ebg.stock;
		this.next_task.create( this, $('next_task'), 150, 89 );
		this.next_task.setSelectionMode(0);

		this.current_tasks = new ebg.stock;
		this.current_tasks.create( this, $('current_tasks'), 150, 89 );
		this.current_tasks.setSelectionMode(0);

		/***
		this.taskPlayed = new ebg.stock;
		this.taskPlayed.create( this, $('card_played'), 150, 89 );
		this.taskPlayed.setSelectionMode(0);
		***/
		// Create cards types:
		for( var index=0;index<24;index++ )
		{
		    // Build card type id
		    var card_type_id = index;
		    this.current_tasks.addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/tasks.jpg', index );
		    this.next_task.addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/tasks.jpg', index );
		    for( var player_id in gamedatas.players )
			this.claimed_tasks[player_id].addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/tasks.jpg', index );
//		    this.taskPlayed.addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/tasks.jpg', index );
		}

		for ( var i in gamedatas.next_task )
		{
		    this.installCard( gamedatas.next_task[i], this.next_task,
				      null );
		}
		for ( var i in gamedatas.current_tasks )
		{
		    this.installCard( gamedatas.current_tasks[i],
				      this.current_tasks, null );
		}
		for( var player_id in gamedatas.players )
		    for (var card_i in gamedatas.claimed[player_id] )
			this.installCard( gamedatas.claimed[player_id][card_i],
					  this.claimed_tasks[player_id],
					null );
	    }
	    else
	    {
		this.dontPreloadImage("board_HF.jpg");
		this.dontPreloadImage("tasks.jpg");
		dojo.style( 'the_tasks', 'display', 'none' );
		dojo.style( 'claimed_tasks', 'display', 'none' );

		this.theDestroyed.create( this, 'the_destroyed', 25, 45 );
		this.theDestroyed.setPattern( 'grid' );
	    }

            // Set up your game interface here, according to "gamedatas"
            for (var i in gamedatas.board)
	    {
		var token = gamedatas.board[i];
		this.addTokenOnBoard(token.x, token.y, token.player, parseInt(token.rank));
	    }

	    if (gamedatas.gateway_x == 0) {
		dojo.style( 'mini_gateway', 'display' , 'inline-block' );
	    }
	    if (gamedatas.gateway_x <= 0)
	    {
		dojo.style('gateway', 'display', 'none');
		this.placeOnObject( 'gateway', 'player_boards');
	    }
	    else
	    {
		this.placeOnObject( 'gateway',
 		    'square_'+gamedatas.gateway_x+'_'+gamedatas.gateway_y );
		dojo.style('gateway', 'display', 'inline-block');
	    }

	    this.from_x = gamedatas.card_x;
	    this.from_y = gamedatas.card_y;

	    this.turn_counter = gamedatas.turn_counter;

	    if (gamedatas.merchant_player != 0) {
		dojo.addClass( 'captured_piece', [ 
		    this.rankName(gamedatas.merchant_rank),
	'token_'+this.gamedatas.players[gamedatas.merchant_player]['color']
		] );
		dojo.style( 'captured_piece', 'opacity', 1 );
	    }

	    if ( ! this.isSpectator ) {
	      if ( gamedatas.players[this.player_id]['color'] == '6a548f' 
		   && gamedatas.pending >= 0 )
		  dojo.addClass( 'hand_item_6_'+gamedatas.pending, 'pending' );

	      this.displayPiecesDiff( gamedatas.differentials );
	    }
	    else {
		this.displayPiecesDiffSpectator( gamedatas.differentials );
		dojo.style( 'my_discard', 'display', 'none' );
	    }

	    this.installDestroyedZone( {args:gamedatas.destroyed_args} ) ;
 
	    // Hack : we define animations here so they are not garbled by css compression
	    var s = document.createElement( 'style' );
	    var keyframes = '';
	    var keyframepfx = ['', '-webkit-', '-moz-'];
	    for( var i = 0; i < keyframepfx.length; i++ )
	    {
		keyframes += '@' + keyframepfx[i] + 'keyframes myglow { '+
                '50% {box-shadow: 0 0 10px 5px Crimson;}' +
                '}\n';
		keyframes += '@' + keyframepfx[i] + 'keyframes myrot { '+
                '100% {filter: sepia(100%);}' +
                '}\n';
		keyframes += '@' + keyframepfx[i] + 'keyframes myunrot { '+
                '0% {filter: sepia(100%);}' +
                '100% {filter: sepia(0%);}' +
                '}\n';
	    }
	    s.innerHTML = keyframes;
	    document.getElementsByTagName( 'head' )[ 0 ].appendChild( s );

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
	    this.dontPreloadImage("Northern.jpg");
	    this.dontPreloadImage("Southern.jpg");
	    this.dontPreloadImage("Highland.jpg");
	    this.dontPreloadImage("Sylvan.jpg");
	    this.dontPreloadImage("Everfrost.jpg");
	    this.dontPreloadImage("Nethervoid.jpg");
	    this.dontPreloadImage("Etherweave.jpg");
	    this.dontPreloadImage("Northern_big.jpg");
	    this.dontPreloadImage("Southern_big.jpg");
	    this.dontPreloadImage("Highland_big.jpg");
	    this.dontPreloadImage("Sylvan_big.jpg");
	    this.dontPreloadImage("Everfrost_big.jpg");
	    this.dontPreloadImage("Nethervoid_big.jpg");
	    this.dontPreloadImage("Etherweave_big.jpg");
	    this.dontPreloadImage("flares_big.jpg");
	    this.dontPreloadImage("tasks_big.jpg");
	    this.dontPreloadImage("Legends_big.jpg");
            // console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            // console.log( 'Entering state: '+stateName );

	    if (args.args != undefined)
		this.args = args.args;

	    if ( this.isCurrentPlayerActive() )
	    {
		dojo.style( 'my_undo_button', 'display', 'block' );
		dojo.style( 'my_redo_button', 'display', 'block' );
	    }
	    else
	    {
		dojo.style( 'my_undo_button', 'display', 'none' );
		dojo.style( 'my_redo_button', 'display', 'none' );
	    }

	    var player_id = this.getActivePlayerId();
	    // if ( player_id != null )
	    // 	dojo.removeClass( 'last_card_icon_' + player_id, 'dimmedcard' );
            switch( stateName )
            {
	    case 'deckChoice':
		var decks = { Northern:['037cb1',4,4, _('Northern')],
			      Southern:['dc2515',6,4, _('Southern')],
			      Highland:['d6b156',8,4, _('Highland')],
			      Sylvan:['8ec459',2,4, _('Sylvan')],
			      Everfrost:['f0f9ff',3,8, _('Everfrost')],
			      Nethervoid:['f4913c',5,8, _('Nethervoid')],
			      Etherweave:['6a548f',7,8, _('Etherweave')] };
		var color;

		dojo.style('selection_overlay', 'visibility', 'visible');
		
		for (var deck in decks)
		{
		    color = decks[deck][0];
		    if ( args.args.indexOf( color ) == -1 )
		    {
			var x = decks[deck][1];
			var y = decks[deck][2];
			dojo.place( this.format_block( 'jstpl_token', {
			    n: this.next_token,
			    color: color,
			    therank: "common"
			} ) , $('tokens') );
            
			this.placeOnObject( 'token_'+this.next_token,
					    'square_'+x+'_'+y );
			dojo.setAttr( 'square_'+x+'_'+y, 'token',
				      this.next_token );
			this.next_token++;

			if( this.isCurrentPlayerActive() )
			{
			    dojo.addClass('square_'+x+'_'+y, "clickable" );
			    this.addTooltip( 'square_'+x+'_'+y,
					decks[deck][3],
					_('Click to choose this school') ) ;
			}
			else
			    this.addTooltip( 'square_'+x+'_'+y,
					decks[deck][3], '' ) ;
		    }
		}
		this.disconnectAll();
		if( this.isCurrentPlayerActive() )
		    this.connectClass( '.clickable', 'onclick', 'onChooseDeck' );
		break;
	    case 'initialPiecesDuel':
		this.disconnectAll();
		dojo.addClass( 'square_3_5', 'clickable' );
		dojo.addClass( 'square_7_5', 'clickable' );
		if( this.isCurrentPlayerActive() )
		{
		    this.connectClass( '.clickable', 'onclick', 'onPlaceInitialPieces' );		
		    this.addTooltipToClass( 'clickable','', _('Click to place one of the initial pieces here') ) ;
		}
		break;
	    case 'initialPiecesMelee':
		this.disconnectAll();
		for (var x in args.args.empty)
		    for (var y in args.args.empty[x])
		{
		    var square = 'square_'+x+'_'+y;
		    dojo.addClass(square, "clickable");
		}
		if( this.isCurrentPlayerActive() )
		{
		    this.connectClass( '.clickable', 'onclick', 'onPlaceInitialPieces' );		
		    this.addTooltipToClass( 'clickable','', _('Click to place one of the initial pieces here') ) ;
		}
		break;
            case 'actionChoice':
		this.disconnectAll();
		for (var x in args.args.empty)
		    for (var y in args.args.empty[x])
		{
		    var square = 'square_'+x+'_'+y;
		    dojo.addClass(square, "clickable");
		}
		this.turn_counter = args.args.turn_counter;
		
		if( this.isCurrentPlayerActive() )
		{
		    this.addTooltipToClass( 'clickable','', _('Click to place a piece here') ) ;	    
		    this.connectClass( '.clickable', 'onclick', 'onPlayPiece' );
		    if ( args.args.frozen >= 0
		&& this.gamedatas.players[this.player_id]['color'] == 'f0f9ff' )
		    {
			this.connect( $('frozen_effect'), 'onclick', 'onPlayFrozen' );
			dojo.style( 'frozen_effect', 'cursor', 'pointer' );
		    }
		}
//		this.playerHand.setSelectionMode(1);
//		dojo.addClass( 'last_card_icon_' + player_id, 'dimmedcard' );
		break;
	    case 'actionChoice.being':
		this.disconnectAll();
		this.connectClass( '.square', 'onclick', 'onPlayCard' );
		dojo.query( '.square' ).addClass( 'clickable' );
		this.addTooltipToClass( '.clickable','', _('Click to place the being here') ) ;
		//		this.playerHand.setSelectionMode(1);
//		dojo.addClass( 'last_card_icon_' + player_id, 'dimmedcard' );
		break;
		break;
	    case "actionChoice.returnCards" :
// 		this.playerHand.setSelectionMode(1);
//		dojo.addClass( 'last_card_icon_' + player_id, 'dimmedcard' );
		this.disconnectAll();
		break;
	    case 'squareChoice':
		if ( args.args.eligible_players.length > 1)
		    {
			this.setClientState('squareChoice.chooseColor', {
			    descriptionmyturn : _('${you} must choose which piece to place'),
			    description : _('${actplayer} must choose which piece to place')
			} );
			break;
		    }
		// else...
		this.player = args.args.eligible_players[0];
	    case 'squareChoice.doPlace':
		if (args.args.clickable[this.player])
		    this.updateClickable( args.args.clickable[this.player], 
					  args.args.effect );
		else
		    this.updateClickable( args.args.clickable,
					  args.args.effect );
		this.disconnectAll();
		this.connectClickable();
		break;
	    case 'pieceChoice':
	    case 'directionChoice':
		this.from_x = args.args.x;
		this.from_y = args.args.y;
		this.updateClickable( args.args.clickable, args.args.effect );
		this.disconnectAll();
		this.connectClickable();
		break;
	    case 'moveChoice':
		var square = this.singleClickable( args.args.clickable );
		if ( square !== null && typeof args.args.swap === 'undefined' ) {
		    this.setClientState( "moveChoice.destination", {
			x:square[0], y:square[1], single:true,
			"descriptionmyturn":_("${you} must choose where to move this piece") } );
		}
		else {
		    if ( typeof args.args.swap !== 'undefined' ) {
			this.gamedatas.gamestate.descriptionmyturn =
			    _('${you} may swap two pieces');
			this.gamedatas.gamestate.description =
    			    _('${actplayer} may swap two pieces');
			this.updatePageTitle();
		    }
		    this.updateClickable(args.args.clickable, 'movePiece');
		    this.disconnectAll();
		    this.basestate = 'moveChoice';
		    this.saved_description = this.gamedatas.gamestate.descriptionmyturn;
		    this.connectClickable();
		}   
		break;
	    case 'moveChoice.destination':
		var destinations = [this.args.clickable, this.args.clickable1, this.args.clickable2, this.args.clickable3];
		for ( var i in destinations )
		    if (destinations[i] != undefined && destinations[i][args.x][args.y])
			this.updateClickable( destinations[i][args.x][args.y], "moveDest" );
		this.from_x = args.x;
		this.from_y = args.y;
		if ( !args.single ) {
		    dojo.addClass('square_'+args.x+'_'+args.y, "moveUndo" );
		    this.addTooltipToClass( 'moveUndo','', _('Click again to move another piece') ) ;
		}

		this.disconnectAll();
		this.connectClickable();
		break;
	    case 'orEffects3':
		this.updateClickable( args.args.clickable3, args.args.effect3 );
	    case 'orEffects2':
		/* orEffects always place player pieces */
		this.player = this.player_id;
		this.basestate = stateName;
		this.saved_description = args.descriptionmyturn;
		this.updateClickable( args.args.clickable2, args.args.effect2 );
		if( args.args.effect1 == 'performWarp' ) // Ziggurat Sentinel
		{
		  if ( this.isCurrentPlayerActive() ) {
		    if ( Object.keys(args.args.clickable1).length > 0 ) {
			this.revealDiscard( );
			this.playerDiscard.setSelectionMode(1);
			this.dimCards();
			for ( var i in args.args.clickable1 ) {
			    dojo.removeClass(
				'discard_item_'+args.args.clickable1[i].type_arg,
				'dimmedcard' );
			}
			this.playerDiscardHandle = dojo.connect(
			    this.playerDiscard, 'onChangeSelection',
			    this, 'onPutCardOn' );
			this.gamedatas.gamestate.descriptionmyturn =
			    _('${you} may copy a warp effect or move the Ziggurat Sentinel');
		    }
		    else
			this.gamedatas.gamestate.descriptionmyturn =
			    _('${you} may only move the Ziggurat Sentinel');
		    this.updatePageTitle();
		  }
		}
		else if ( args.args.effect1 == 'returnPending' // Warpmaster
			  && (this.pending < 0 || this.pending == 5) ) {
		    // Don't return Merchant of Time (5)
		    this.gamedatas.gamestate.descriptionmyturn =
			_('${you} must move a piece');
		    this.updatePageTitle();
		}
		else
		    this.updateClickable( args.args.clickable1, args.args.effect1 );
		this.disconnectAll();
		this.connectClickable();
		break;
	    case 'cardChoice':
		if ( args.args.location == 'hand' ) {
		    this.gamedatas.gamestate.descriptionmyturn =
			_('${you} may discard any other card from your hand');
		    this.updatePageTitle();
		    break;
		}
	    case 'frozenChoice':
		if( this.isCurrentPlayerActive() ) {
		    this.revealDiscard();
		    this.playerDiscard.setSelectionMode(1);
		    this.dimCards();
		    for ( var i in args.args._private.cards ) {
			if ($('discard_item_'+args.args._private.cards[i].type_arg) == null) {
			    this.addCard(args.args._private.cards[i], 'frozen_effect');
			    this.discard_weights[args.args._private.cards[i].type_arg] = 1000;
			}			
			else
			    dojo.removeClass(
			 'discard_item_'+args.args._private.cards[i].type_arg,
				'dimmedcard' );
		    }
		    // console.log('WEIGHTS', this.discard_weights);
		    this.playerDiscard.changeItemsWeight(this.discard_weights);
		    this.playerDiscardHandle = dojo.connect(
			this.playerDiscard, 'onChangeSelection',
			this, 'onPutCardOn' );
		}
		break;

	    case 'pickPiece':
		this.updateClickable( args.args.clickable, "pickPiece" );
		this.disconnectAll();
		this.connectClickable();
		break;

	    case 'chooseOption':
		this.setClientState( "chooseOption.title", {
		    descriptionmyturn: _(args.args.questionyou),
		    description: _(args.args.questionhe)
		} );
		break;

	    case 'chooseColor' :
		if ( this.isCurrentPlayerActive()
		    && Object.keys(args.args.will_score).length > 0 )
		{
		    var points = '';
		    for (var c in args.args.will_score)
		    {
			points += ' ' + args.args.will_score[c]
			    + ' <div class="meleeicontitle melee_'+c+'"></div>';
		    }
		    var will_score = dojo.string.substitute(
			_('${you} will score ${points}.<br/>'), {points:points, you:'${you}'} );
		    this.gamedatas.gamestate.descriptionmyturn =
			will_score
			+ _(this.gamedatas.gamestate.descriptionmyturn);
		    this.updatePageTitle();
		}
		break;

	    case 'turnEndHF':
		dojo.query( "#current_tasks .stockitem" ).forEach(
		    function (node) { dojo.addClass( node, "claimable"); } );
//		this.addTooltipToClass( 'claimable','', _('Click to claim this task') ) ;	    
	    case 'turnEndDM':
		this.disconnectAll();
		if( this.isCurrentPlayerActive() )
		    this.connectClass(".claimable", "onclick", "onClaimTask");
//		dojo.addClass( 'last_card_icon_' + player_id, 'dimmedcard' );
		if( this.isCurrentPlayerActive() && args.args.frozen >= 0
		&& this.gamedatas.players[this.player_id]['color'] == 'f0f9ff' )
		    {
			this.connect( $('frozen_effect'), 'onclick', 'onPlayFrozen' );
			dojo.style( 'frozen_effect', 'cursor', 'pointer' );
		    }
		break;
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

	/******** TODO ***********/
        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            // console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
	    case 'deckChoice':
		dojo.query( ".token" ).forEach(
		    function(node){ dojo.destroy( node ) } );
		dojo.query('.square').forEach(
		    dojo.hitch( this, function(node) {
			this.removeTooltip( node );
			dojo.setAttr( node, 'token', "" );
		    } ) );
		var over = true;
		for (var player_id in this.gamedatas.players)
		    if (this.gamedatas.players[player_id]['color'] == '000000')
			over = false;
		if (over)
		    this.fadeOutAndDestroy( 'selection_overlay' );
		break;
	    case 'actionChoice':
		dojo.query('#frozen_effect').style( 'cursor', 'default' );
		break;
	    case 'actionChoice.being':
// 		this.playerHand.setSelectionMode(0);
		this.playerHand.unselectAll();
		break;
	    case "actionChoice.returnCards" :
// 		this.playerHand.setSelectionMode(0);
		break;
	    case 'directionChoice':
		dojo.query('.shootPieces').forEach(
		    function(node) { dojo.style( node, 'opacity', 0.2 ); } );
		dojo.query('.chooseDirectionMirror').forEach(
		    function(node) { dojo.style( node, 'opacity', 0.2 ); } );
		break;
	    case 'turnEndHF':
		dojo.query(".claimable").removeClass("claimable");
		break;
	    case 'cardChoice':
	    case 'frozenChoice':
	    case 'orEffects2':
		if ( !this.keep_discard )
 		    dojo.addClass( 'discard', 'hidden' );
		dojo.disconnect( this.playerDiscardHandle );
		dojo.query('#discard .stockitem').removeClass('dimmedcard');
		this.playerDiscard.setSelectionMode(0);
		break;
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               

	    this.cleanClickable();
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            // console.log( 'onUpdateActionButtons: '+stateName );

	    var ga = false;
                      
            if( this.isCurrentPlayerActive() )
            {            
		switch( stateName )
                {
		case "deckChoice" :
		    this.addActionButton( 'randomSchool', _('Choose randomly'), 'onChooseRandomDeck' );
		    break;
		case "actionChoice" :
		    if ( args.frozen >= 0
		&& this.gamedatas.players[this.player_id]['color'] == 'f0f9ff' )
		    {
			this.addActionButton( 'frozenButton',
					      _('Thaw frozen effect'),
					      'onPlayFrozen' );
		    }
		    break;
		case "actionChoice.returnCards" :
		    this.addActionButton( 'undoDiscard', _('Undo'), 'onUndoDiscard' );		
		    this.addActionButton( 'returnsDone', _('Done'), 'onReturnsDone' );		
		    break;
		case 'actionChoice.being':
		    var cards = this.playerHand.getSelectedItems();
		    var card_ids = cards[0].id.split("_");
		    if ( card_ids[0] == '6' && this.pending == -1
			 && this.warp_effects.includes(toint(card_ids[1]))) {
			if (this.turn_counter > 1)
			    this.addActionButton( 'warpButton', _('Play warp effect'), 'onWarp' );
			else
			    this.addActionButton( 'noWarpButton', _('No warp effect on first turn'), function() {}, null, false, 'red' );
		    }
		    if ( this.args.discard_ok && card_ids[0] != 7
		       && this.pending != card_ids[1] )
			this.addActionButton( 'discardButton', _('Discard'), 'onDiscard' );	
		    break;
		case 'orEffects3':
		    if (args.effect3 == 'gainAction')
			ga = true;
		case 'orEffects2':
		    if (ga || args.effect1 == 'gainAction' || args.effect2 == 'gainAction')
			this.addActionButton( 'actionButton', _('Gain an action'), 'onGainAction' );
		    if (args.effect1 == 'returnPending'
			&& this.pending >= 0 && this.pending != 5) // MoT
			this.addActionButton( 'actionButton', _('Return your pending being to your hand'), 'onReturnPending' );
		case 'cardChoice':
		case 'squareChoice':
		case 'pieceChoice':
		case 'directionChoice':
		case 'moveChoice':
		case 'moveChoice.destination':
		    if (! args.mandatory)
			this.addActionButton( 'skipButton', _('Skip'), 'onSkip' );
		    break;
		    
		case 'squareChoice.chooseColor':
		    for (var i in args.eligible_players)
			this.addActionButton(
			    'choiceButton'+args.eligible_players[i],
			    "<div class='pieceschoice common token_"
		+ this.gamedatas.players[args.eligible_players[i]]['color']
				+"'></div>", 'onChooseColorPlace' );
		    break;
		case 'pickPiece':
//		    this.addActionButton( 'cancelButton', _('Cancel'), 'onCancel' );	    
		    break;
		case 'chooseColor':
		    for (var i in args.scorable)
		    {
			this.addActionButton( 'choiceButton'+args.scorable[i],
				"<div class='pieceschoice common token_"
			+ this.gamedatas.players[args.scorable[i]]['color']
			+"'></div>", 'onChooseColor' );
		    }
		    break;
		case 'chooseColorLegend':
		    for (var i in args.scorable)
		    {
			this.addActionButton( 'choiceButton'+args.scorable[i],
				"<div class='pieceschoice common token_"
			+ this.gamedatas.players[args.scorable[i]]['color']
			+"'></div>", 'onChooseColorLegend' );
		    }
		    break;
		case 'chooseColorFlare':
		    for (var i in args.activable)
		    {
			this.addActionButton( 'choiceButton'+args.activable[i],
				"<div class='pieceschoice common token_"
			+ this.gamedatas.players[args.activable[i]]['color']
			+"'></div>", 'onChooseColorFlare' );
		    }
		    break;
		case 'chooseColorImpro':
		    for (var i in args.usable)
		    {
			if ( args.usable[i] == this.player_id )
			    this.addActionButton(
				'choiceButton'+args.usable[i],
				_("No improvised summoning"),
				'onChooseColorImpro' );
			else
			    this.addActionButton(
				'choiceButton'+args.usable[i],
				"<div class='pieceschoice common token_"
			    + this.gamedatas.players[args.usable[i]]['color']
				    +"'></div>", 'onChooseColorImpro' );
		    }
		    break;
		case 'turnEndHF.warp':
		case 'turnEndDM.warp':
		    var cards = this.playerHand.getSelectedItems();
		    var card_ids = cards[0].id.split("_");
		    if ( card_ids[0] == '6' && this.pending == -1
			 && this.warp_effects.includes(toint(card_ids[1]))) {
			if (this.turn_counter > 1)
			    this.addActionButton( 'warpButton', _('Play warp effect'), 'onWarp' );
			else
			    this.addActionButton( 'noWarpButton', _('No warp effect on first turn'), function() {}, null, false, 'red' );
		    }
		case 'turnEndHF':
		case 'turnEndDM':
		    if ( args.claimable == 0 )
			this.addActionButton( 'skipButton', _('End turn'), 'onSkip' );
		    else
			this.addActionButton( 'skipButton', _('End turn'), 'onSkipConfirm' );
		    if ( args.frozen >= 0
		&& this.gamedatas.players[this.player_id]['color'] == 'f0f9ff' )
		    {
			this.addActionButton( 'frozenButton',
					      _('Thaw frozen effect'),
					      'onPlayFrozen' );
		    }
		    break;
		case 'chooseOption.title':
		    this.addActionButton( 'choiceButtonOne', _(args.option1),
					  'onChooseOne' );
		    this.addActionButton( 'choiceButtonTwo', _(args.option2),
					  'onChooseTwo' );
		    if (args.option3 != undefined)
			this.addActionButton( 'choiceButtonThree',
					  _(args.option3), 'onChooseThree' );
		    if (! args.mandatory)
			this.addActionButton( 'skipButton', _('Skip'), 'onSkip' );
		    dojo.query( '.pieceschoice' ).addClass(
			'token_' + 
			    this.gamedatas.players[this.player_id]['color'] );
		break;

/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }

            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

	cardId: function ( card )
	{
	    switch ( card.type )
	    {
	    case 'task':
		card_id = card.type_arg;
		break;
	    case "Flare":
		card_id = "flare_"+card.type_arg;
		break;
	    default:
		card_id = ['Northern', 'Southern', 'Highland', 'Sylvan', 'Everfrost', 'Nethervoid', 'Etherweave', 'Legends'].indexOf(card.type) + "_" + card.type_arg;
		break;
	    }
	    return card_id;
	},

	installMeleeIcons: function ( players )
	{
	    var colors = [ '037cb1', 'dc2515', 'd6b156', '8ec459', 'f0f9ff', 'f4913c', '6a548f' ];
            for( var player_id in players )
            {
                var player = players[player_id];
		var player_board_div = $('player_board_'+player_id);
		
		for( var op_id in players )
		{
		    if ( op_id != player_id )
		    {
			var opcolor = players[op_id]['color'];
			var dx = 32*colors.indexOf(player.color);
			var dy = 32*(1+colors.indexOf(opcolor));
			if (dx != -32 && dy != 0)
			    dojo.removeClass( "impro_"+player_id+"_vs_"+op_id,
					      'token_000000' );
			dojo.style( "impro_"+player_id+"_vs_"+op_id,
					"backgroundPosition",
					'-'+dx+'px -'+dy+'px');
			dojo.addClass( 
			    "meleescore_icon_"+player_id+"_vs_"+op_id,
			    "melee_"+opcolor );
		    }
		}
	    }
	},

	addCardTip: function( element, card )
	{
	    if ( typeof card.frozentext !== 'undefined' )
		this.addTooltipHtml( element,
				     this.format_block( 'jstpl_frozen_tip',
					   { name:_(card.name),
					     text:_(card.text),
					     frozentext:_(card.frozentext),
					     deck:card.type,
					     offset:250*card.type_arg } ) );
	    else if ( typeof card.warptext !== 'undefined' )
		this.addTooltipHtml( element,
				     this.format_block( 'jstpl_warp_tip',
					   { name:_(card.name),
					     text:_(card.text),
					     warptext:_(card.warptext),
					     deck:card.type,
					     offset:250*card.type_arg } ) );
	    else
		this.addTooltipHtml( element,
				     this.format_block( 'jstpl_deck_tip',
					   { name:_(card.name),
					     text:_(card.text),
					     deck:card.type,
					     offset:250*card.type_arg } ) );
	},

	installCard: function ( card, stock, from )
	{
	    var card_id = this.cardId( card );

	    if ( card.type == 'task' )
	    {
		stock.addToStockWithId( card_id, card_id );
		this.addTooltipHtml( stock.control_name+'_item_'+card_id,
				     '<div class="tasktooltip">\n'
				     +'<h3>'+_(card.name)+'</h3>\n'
				     +'<hr>\n'
				     +'<div class="tasks_big" style="background-position: -'+406*card.type_arg+'px 0px;"></div>\n'
				     +'<hr>\n'
				     +_(card.text)+"</div>" );
	    }
	    else
	    {
		if (from !== null)
		    stock.addToStockWithId( card_id, card_id, from );
		else
		    stock.addToStockWithId( card_id, card_id );

		if ( card.type == "Flare" )
		    this.addTooltipHtml( 'hand_item_'+card_id,
			this.format_block( 'jstpl_flare_tip',
					   { upgraded:_(card.upgraded),
					     pieces:_(card.pieces),
					     offset:250*card.type_arg }));
		else
		{
		    this.addCardTip( 'hand_item_'+card_id, card );
		}
	    }
	},

	dimCards: function() {
	    dojo.query('#discard .stockitem').forEach( function (node) {
		// var style = dojo.style( node );
		// console.log("STYLE", style);
		// console.log("OPACITY", style.opacity);
//		style.replace(/opacity\s*:\s*[.0-9]*/, "");
		// style.opacity = "";
		// console.log("NEW OPACITY", style.opacity);
		// dojo.style( node, style);
		dojo.style(node, "opacity", null);
	    } );
	    dojo.query('#discard .stockitem').addClass('dimmedcard');
	},
	
	cleanClickable: function ()
	{
	    var effects = ["clickable", "destroyPiece", "upgradePiece", "downgradePiece", "movePiece", "moveBeing", "moveUndo", "moveDest", "placePiece", "shootPieces", "chooseDirectionMirror", "convertPiece", "pickPiece", "choosePiece", "becomeGateway", "chooseSquare", "capturePiece", "freePiece"];
	    for (var i in effects)
	    {
		var self = this;
		var squares = dojo.query("."+effects[i]);
		squares.forEach( function(node) {
		    self.removeTooltip( node.id );
		    self.setRot( node.id, 0 );
		} );
		squares.removeClass(effects[i]);
	    }
	},


	singleClickable: function ( pieces )
	{
	    var the_x=0, the_y=0;
	    for (var x in pieces)
		for (var y in pieces[x])
		    if (pieces[x][y])
	    {
		if (the_x == 0) {
		    the_x = x;
		    the_y = y;
		}
		else
		    return null;
	    }
	    if (the_x == 0)
		return null;
	    else
		return [the_x, the_y];
	},

	updateClickable: function ( pieces, effect )
	{
	    for (var x in pieces)
		for (var y in pieces[x])
		    if (pieces[x][y])
	    {
		var square = 'square_'+x+'_'+y;
		var n = dojo.getAttr( square, "token" );
		dojo.addClass(square, "clickable");

		if ( effect == 'destroyPiece' && $('token_'+n) == null )
		    dojo.addClass(square, 'moveBeing');
		else
		    dojo.addClass(square, effect);

		if (effect == "shootPieces"
		    || effect == "chooseDirectionMirror")
		{
		    this.setArrow( square );
		}
	    }

	    if( this.isCurrentPlayerActive() )
	    {
		this.addTooltipToClass( 'destroyPiece','', _('Click to destroy this piece') ) ;	    
		this.addTooltipToClass( 'upgradePiece','', _('Click to upgrade this piece') ) ;
		this.addTooltipToClass( 'downgradePiece','', _('Click to downgrade this piece') ) ;
		this.addTooltipToClass( 'moveBeing','', _('Click to move the being here') ) ;
		this.addTooltipToClass( 'movePiece','', _('Click to move this piece') ) ;
		this.addTooltipToClass( 'moveDest','', _('Click to move the piece here') ) ;
		this.addTooltipToClass( 'placePiece','', _('Click to place a piece here') ) ;
		this.addTooltipToClass( 'shootPieces','', _('Click to choose this direction') ) ;
		this.addTooltipToClass( 'chooseDirectionMirror','', _('Click to place pieces in this direction') ) ;
		this.addTooltipToClass( 'convertPiece','', _('Click to convert this piece') ) ;
		this.addTooltipToClass( 'pickPiece','', _('Click to pick up this piece') ) ;
		this.addTooltipToClass( 'choosePiece','', _('Click to choose this piece') ) ;
		this.addTooltipToClass( 'becomeGateway','', _('Click to make this piece become the Gateway') ) ;
		this.addTooltipToClass( 'capturePiece','', _('Click to put this piece on Merchant of Time') ) ;
		this.addTooltipToClass( 'chooseSquare','', _('Click to choose this square'));
		this.addTooltipToClass( 'freePiece','', _('Click to move the piece here') ) ;
	    }
	},

	connectClickable: function( )
	{
	    if( this.isCurrentPlayerActive() )
	    {
		var effects = ["destroyPiece", "upgradePiece", "downgradePiece", "moveBeing", "shootPieces", "chooseDirectionMirror", "convertPiece", "choosePiece", "becomeGateway", "chooseSquare", "capturePiece", "freePiece"];
		for (var i in effects)
		    this.connectClass("."+effects[i], "onclick", "onClickEffect");

		this.connectClass( ".movePiece", "onclick", "onClickMove" );
		this.connectClass( ".placePiece", "onclick", "onClickPlace" );
		this.connectClass( ".moveDest", "onclick", "onClickMoveDest" );
		this.connectClass( ".moveUndo", "onclick", "onClickMoveUndo" );
		this.connectClass( ".pickPiece", "onclick", "onClickPick" );
	    }
	},

	rankName: function( rank )
	{
	    switch (rank)
	    {
	    case 0:
	    case '0':
		return "common";
		break;
	    case 1:
	    case '1':
		return "heroic";
		break;
	    case 2:
	    case '2':
		return "legendary";
		break;
	    default:
		return "common";
		break;
	    }
	},

	addTokenOnBoard: function( x, y, player, rank, delay, gateway )
        {
	    if ( typeof(delay)==='undefined' )
		delay = 0;

	    var therank=this.rankName(rank);
		
            dojo.place( this.format_block( 'jstpl_token', {
                n: this.next_token,
                color: this.gamedatas.players[ player ].color,
		therank: therank
            } ) , $('tokens') );
           
	    this.placeOnObject( 'token_'+this.next_token,
				'overall_player_board_'+player );
	    if (x>0 && y>0)
		this.slideToObject( 'token_'+this.next_token,
				    'square_'+x+'_'+y, 500, delay ).play();
	    else {
		this.placeOnObject( 'token_'+this.next_token, 'warp_card' );
	    }
	    dojo.setAttr( 'square_'+x+'_'+y, 'token',
			  this.next_token );
	    this.next_token++;
        },

	setRot: function( square, direction )
	{
	    var transform;
            dojo.forEach(
		['transform', 'WebkitTransform', 'msTransform',
		 'MozTransform', 'OTransform'],
		function (name) {
                    if (typeof dojo.body().style[name] != 'undefined') {
			transform = name;
                    }
		});
	    
            dojo.style( square, transform,
			'rotate('+direction+'deg)' );
	},

	setArrow: function( square )
	{
	    var dx = square.charAt(7) - this.from_x;
	    var dy = square.charAt(9) - this.from_y;
	    var direction = 180 - 90*dx + dy*dx*45;
	    if (dx == 0 && dy == -1)
		direction = 0;

	    dojo.style( square, 'opacity', 0.8 );
	    this.setRot( square, direction );
	},
	
	revealDiscard: function ( )
	{
//	    this.playerDiscard.removeAll();
	    dojo.removeClass( 'discard', 'hidden' );
//	    this.playerDiscard.updateDisplay();
	    // for (var i in cards)
	    // 	this.addCard( cards[i], school );
	},

	toggleDiscard: function ( )
	{
	    dojo.toggleClass( 'discard', 'hidden' );
	    this.keep_discard = !this.keep_discard;
	},
	
	addCard: function ( card, from ) {
	    var card_id = card.type_arg;
	    // this.playerDiscard.addItemType( card_id, card_id, g_gamethemeurl+'img/'+school+'.jpg', card_id );
	    if ( from !== null )
		this.playerDiscard.addToStockWithId( card_id, card_id, from );
	    else
		this.playerDiscard.addToStockWithId( card_id, card_id );
	    this.addCardTip( 'discard_item_'+card_id, card );
	    this.discard_weights[card_id] = toint(card.location_arg)
		+ (card.location=='discard_buffer'?100:0);
	    // if ( school == 'Sylvan' )
	    // 	tooltip += _('<hr><b>Click to put this card on top of your deck</b>');
	    // else if ( school == 'Everfrost' )
	    // 	tooltip += _('<hr><b>Click to put this frozen effect into play</b>');
	    // else // Etherweave
	    // 	tooltip += _('<hr><b>Click to copy this warp effect</b>');
	},

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

	onChooseRandomDeck: function()
	{
	    var colors = [];
	    dojo.query( '.token' ).forEach( function(node) {
		var pattern = new RegExp("token_[0-9a-f]{6} ");
		var color = pattern.exec(node.className);
		color = color[0].substr(6, 6);
		colors.push(color);
	    } );

	    var acolor = colors[Math.floor(Math.random()*colors.length)]

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseDeck.html', {lock:true, color:acolor}, this, function (result) {} );
	},

	onChooseDeck: function( evt )
	{
	    dojo.stopEvent( evt );
	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);
	    var n = dojo.getAttr( 'square_'+x+'_'+y, 'token' );
	    var token = $('token_'+n);
	    var pattern = new RegExp("token_[0-9a-f]{6} ");
	    var color = pattern.exec(token.className);
	    color = color[0].substr(6, 6);

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseDeck.html', {lock:true, color:color}, this, function (result) {} );
	},
        
	onClickEffect: function( evt )
	{
	    dojo.stopEvent( evt );
	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/clickEffect.html', {lock:true, x:x, y:y}, this, function (result) {} );
	},

	onClickPlace: function( evt )
	{
	    dojo.stopEvent( evt );
	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/clickPlace.html', {lock:true, x:x, y:y, player:this.player}, this, function (result) {} );
	},

	onClickPick: function( evt )
	{
	    dojo.stopEvent( evt );
	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/pickPiece.html', {lock:true, x:x, y:y}, this, function (result) {} );
	},

	onClickMove: function( evt )
	{
	    dojo.stopEvent( evt );	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);
	    this.setClientState( "moveChoice.destination", {
		x:x, y:y, single:false,
		"descriptionmyturn":_("${you} must choose where to move this piece") } );
	},

	onClickMoveDest: function( evt )
	{
	    dojo.stopEvent( evt );	    
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/movePiece.html', {lock:true, from_x:this.from_x, from_y:this.from_y, x:x, y:y}, this, function (result) {} );
	},

	onClickMoveUndo: function( evt )
	{
	    dojo.stopEvent( evt );	    
	    this.setClientState( this.basestate, {
		"descriptionmyturn": this.saved_description } );
		//_("${you} must move a piece or")
	},

	onPlaceInitialPieces: function( evt )
	{
	    dojo.stopEvent( evt );
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    if (this.checkAction('placeInitialPieces', true)
		|| this.checkAction('placeLastPiece'))
	    {
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/placeInitialPieces.html', {lock:true, x:x, y:y}, this, function (result) {} );
	    }
	},

	onPlayPiece: function( evt )
	{
	    dojo.stopEvent( evt );
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);

	    if (this.checkAction('playPiece'))
	    {
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/playPiece.html', {lock:true, x:x, y:y}, this, function (result) {} );
	    }
	},

	onSkip: function()
	{
	    this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/skip.html', {lock:true}, this, function (result) {} );
	},

	onSkipConfirm: function()
	{
	    this.confirmationDialog( _('You have accomplished a task, are you sure you want to end your turn without claiming it ?'), dojo.hitch( this, function() {
		this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/skip.html', {lock:true}, this, function (result) {} );
            } ) );  
	},

	// onTakeBack: function()
	// {
	//     this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/takeBack.html', {lock:true}, this, function (result) {} );
	// },

	onCancel: function()
	{
	    this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/cancel.html', {lock:true}, this, function (result) {} );
	},

	onGainAction: function()
	{
	    this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/gainAction.html', {lock:true}, this, function (result) {} );
	},

	onPlayCard: function( evt )
	{
	    dojo.stopEvent( evt );
	    var x = evt.currentTarget.id.charAt(7);
	    var y = evt.currentTarget.id.charAt(9);
	    var cards = this.playerHand.getSelectedItems();
	    var card = cards[0];

	    if (this.checkAction('playCard'))
	    {
		this.from_x = x;
		this.from_y = y;
		var card_ids = card.id.split("_");
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/playCard.html', {lock:true, x:x, y:y, deck_id:card_ids[0], card_id:card_ids[1]}, this, function (result) {} );
	    }
	},

	onCardSelect: function( control_name )
	{
	    if (this.discarded != "")
		this.onCardReturn( control_name );
	    else
	    {
		var cards = this.playerHand.getSelectedItems();

		/* setClientState is wonderful */
		if (cards.length == 0) {
		    switch ( this.gamedatas.gamestate.name ) {
		    case 'turnEndHF.warp':
			this.setClientState("turnEndHF", {} );
			break;
		    case 'turnEndDM.warp':
			this.setClientState("turnEndDM", {} );
			break;
		    default:
			this.setClientState("actionChoice", { "descriptionmyturn":_("${you} must play a piece or select a card") } );
		    }
		}
		else
		{
		    var card = cards[0];
		    var card_ids = card.id.split("_");

		    if (this.gamedatas.gamestate.name == 'cardChoice') {
			this.playerHand.unselectItem(card.id);
			if (this.checkAction('effectPlayed'))
			{
			    if ( card_ids[0] != 6
				 || card_ids[1] != this.pending
				 && card_ids[1] != 13 )
			    {
				// this.removeTooltip( 'hand_item_'+card.id );
				// this.playerHand.removeFromStockById( card.id );
				if ( card_ids[0] == 'flare' )
				    card_ids[0] = -1;
				this.ajaxcall(
				 '/tashkalarexpansions/tashkalarexpansions/discardSingleCard.html',
				    { lock:true,
				      discarded_deck:card_ids[0],
				      discarded_id:card_ids[1],
				    }, this,
				    function (result) {} );
			    }
			}
		    }
		    else if (card_ids[0] == 'flare')
		    {
			this.playerHand.unselectItem(card.id);
			if (this.checkAction('playFlare'))
			{
			    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/playFlare.html',
					   { lock:true }, this,
					   function (result) {} );
			}
		    }
		    else
		    {
			if ( this.gamedatas.gamestate.name != 'pickPiece'
			     && this.checkAction('playCard', false) )
			{
			  var desc;
			  if ( this.args.discard_ok && card_ids[0] != '7'
			       && this.pending != card_ids[1]
			   || card_ids[0] == '6' && this.pending < 0
			     && this.warp_effects.includes(toint(card_ids[1])) 
			       && this.turn_counter > 1 )
				desc = _("${you} must click on board to place that being or");
			    else
				desc = _("${you} must click on board to place that being");
			    this.setClientState("actionChoice.being", { "descriptionmyturn":desc } );
			}
			else if ( this.checkAction('playWarp', false )
			&& card_ids[0] == '6' && this.pending < 0
			&& this.warp_effects.includes(toint(card_ids[1])) 
				  && this.turn_counter > 1 ) {
			    statename = this.gamedatas.gamestate.name+".warp";
			    this.setClientState( statename, {} );
			}
			else
			    this.playerHand.unselectItem(card.id);
		    }
		}
	    }
	},

	onDiscard: function()
	{
	    var cards = this.playerHand.getSelectedItems();
	    var card = cards[0];
	    this.playerHand.unselectItem(card.id);

	    if (this.checkAction('discardCard'))
	    {
		var card_ids = card.id.split("_");
		if ( card_ids[0] == 7 )
		    this.showMessage( _("You can't discard a legend"), "error" );
		else
		{
		    this.playerDiscard.addToStockWithId( card_ids[1],
							 card_ids[1],
					'hand_item_'+card.id );
		    this.removeTooltip( 'hand_item_'+card.id );
		    this.discard_weights[card_ids[1]] = 1 + Math.max(...Object.values(this.discard_weights));
//		    // console.log('WEIGHTS', this.discard_weights);
		    this.playerDiscard.changeItemsWeight(this.discard_weights);
		    this.playerHand.removeFromStockById( card.id );
		    this.discarded = card.id;
		    this.returned = [];
		    this.setClientState("actionChoice.returnCards", { "descriptionmyturn":_("${you} may return some cards to their decks") } );
		}
	    }
	},

	onReturnPending: function() {
	    this.ajaxcall( 'tashkalarexpansions/tashkalarexpansions/returnPending.html', {lock:true}, this, function (result) {} );
	},

	onWarp: function()
	{
	    var cards = this.playerHand.getSelectedItems();
	    var card = cards[0];
	    this.playerHand.unselectItem(card.id);

	    if (this.checkAction('playWarp'))
	    {
		var card_ids = card.id.split("_");
		if ( card_ids[0] != 6
		     || !this.warp_effects.includes(toint(card_ids[1])) )
		    this.showMessage( _("That being has no warp effect"),
				      "error" );
		else
		{
		    this.pending = toint(card_ids[1]);
		    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/playWarp.html',
				   {lock:true, card_id:card_ids[1]},
				   this, function (result) {} );
		}
	    }
	},

	onCardReturn: function( control_name )
	{
	    var cards = this.playerHand.getSelectedItems();
	    var card = cards[0];
	    this.playerHand.unselectItem(card.id);
	    var card_ids = card.id.split("_");
	    if ( card_ids[0] != 6 || card_ids[1] != this.pending ) {	    
		this.removeTooltip( 'hand_item_'+card.id );
		this.playerHand.removeFromStockById( card.id );
		this.returned.push(card.id);
		if (this.playerHand.count() == 0)
		    this.onReturnsDone();
	    }
	},

	onUndoDiscard: function()
	{
	    var index = this.discarded.split("_");
	    this.playerHand.addToStockWithId( this.discarded, this.discarded,
					'discard_item_'+index[1] );
	    this.playerDiscard.removeFromStockById( index[1] );
	    delete this.discard_weights[index[1]];
	    for (var i in this.returned)
		this.playerHand.addToStockWithId( this.returned[i],
						  this.returned[i] );
	    this.returned = [];
	    this.discarded = "";
	    this.setClientState("actionChoice", { "descriptionmyturn":_("${you} must play a piece or select a card") } );
	},

	onReturnsDone: function()
	{
	    var discarded_id = this.discarded.split("_");
	    this.discarded = "";
	    if ( discarded_id[0] == 'flare' )
		discarded_id[0] = -1;
	    var returned_ids = '';
	    var returned_decks = '';
	    for (var i in this.returned)
	    {
		var returned_id = this.returned[i].split("_");
		if ( returned_id[0] == 'flare' )
		    returned_decks += "-1;";
		else
		    returned_decks += returned_id[0] + ";";
		returned_ids += returned_id[1] + ";";
	    }
	    this.returned = [];
	    
	    this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/discardCard.html',
			   { lock:true,
			     discarded_deck:discarded_id[0],
			     discarded_id:discarded_id[1],
			     returned_decks:returned_decks,
			     returned_ids:returned_ids
			   }, this,
			   function (result) {} );	    
	},

	onClaimTask: function( evt )
	{
	    dojo.stopEvent( evt );

	    if (this.checkAction('chooseTask'))
	    {
		var task_id = evt.target.id.split("_");
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/claimTask.html',
			       { lock:true, task_id:task_id[3] }, this,
			       function (result) {} );
	    }
	},

	onChooseOne: function()
	{
	    if (this.checkAction('effectPlayed'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseOption.html',
			       { lock:true, optnum:1 }, this,
			       function (result) {} );
	},

	onChooseTwo: function()
	{
	    if (this.checkAction('effectPlayed'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseOption.html',
			       { lock:true, optnum:2 }, this,
			       function (result) {} );
	},

	onChooseThree: function()
	{
	    if (this.checkAction('effectPlayed'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseOption.html',
			       { lock:true, optnum:3 }, this,
			       function (result) {} );
	},

	onChooseColor: function(evt)
	{
	    var id = evt.currentTarget.id.substr(12);
	    if (this.checkAction('colorChosen'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseColor.html',
			       { lock:true, id:id }, this,
			       function (result) {} );
	},

	onChooseColorLegend: function(evt)
	{
	    var id = evt.currentTarget.id.substr(12);
	    if (this.checkAction('colorChosen'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseColorLegend.html',
			       { lock:true, id:id }, this,
			       function (result) {} );
	},

	onChooseColorFlare: function(evt)
	{
	    var id = evt.currentTarget.id.substr(12);
	    if (this.checkAction('colorChosen'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseColorFlare.html',
			       { lock:true, id:id }, this,
			       function (result) {} );
	},

	onChooseColorImpro: function(evt)
	{
	    var id = evt.currentTarget.id.substr(12);
	    if (this.checkAction('playCard'))
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/chooseColorImpro.html',
			       { lock:true, id:id }, this,
			       function (result) {} );
	},

	onChooseColorPlace: function(evt)
	{
	    var id = evt.currentTarget.id.substr(12);
	    this.player = id;
	    this.setClientState( 'squareChoice.doPlace', {
		description : _('${actplayer} must choose a square'),
    		descriptionmyturn : _('${you} must choose a square')
	    } );
	},

	onPutCardOn: function( control_name )
	{
	    var cards = this.playerDiscard.getSelectedItems();
	    var card_id = cards[0].id;

	    if ( this.gamedatas.players[this.player_id]['color'] == 'f0f9ff' )
		var school = 'Everfrost';
	    else if (this.gamedatas.players[this.player_id]['color']=='6a548f')
		var school = 'Etherweave';
	    else // Sylvan
		var school = 'Sylvan';

	    this.playerDiscard.unselectItem(card_id);
	    if ( ( this.checkAction('effectPlayed', true)
		   || this.checkAction('frozenChosen') )
		    && !dojo.hasClass('discard_item_'+card_id, 'dimmedcard') )
	    {
		this.removeTooltip( 'discard_item_'+card_id );
		if ( school != 'Etherweave' ) {
		    dojo.place( "<div id='retrieved_card' class='tmp_card "+school+"'></div>", "discard" );
		    var offset = 125 * card_id;
		    dojo.style( 'retrieved_card', 'backgroundPosition',
				'-'+offset+'px 0px' );
		    this.placeOnObject( 'retrieved_card' ,
					'discard_item_'+card_id );
		    this.slideToObjectAndDestroy( 'retrieved_card',
 				  'overall_player_board_'+this.player_id );
		    this.playerDiscard.removeFromStockById( card_id );
		    delete this.discard_weights[card_id];
		}

		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/putCardOn.html', {lock:true, card_id:card_id}, this, function (result) {} );
	    }
	},

	onUndoStep: function( )
	{
	    if ( this.isCurrentPlayerActive()
		 && this.checkAction('browseHistory') )
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/undoStep.html', {lock:true}, this, function (result) {} );
	},

	onRedoStep: function( )
	{
	    if ( this.isCurrentPlayerActive()
		 && this.checkAction('browseHistory') )
		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/redoStep.html', {lock:true}, this, function (result) {} );
	},

	onPlayFrozen: function( evt )
	{
	    dojo.stopEvent( evt );

	    if (this.checkAction('playFrozen'))
	    {
		var cards = this.playerHand.getSelectedItems();
		if ( cards.length > 0 )
		{
		    var card = cards[0];
		    this.playerHand.unselectItem(card.id);
		}

		this.ajaxcall( '/tashkalarexpansions/tashkalarexpansions/playFrozen.html',
			       {lock:true}, this, function (result) {} );
	    }
	},

        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/tashkalarexpansions/tashkalarexpansions/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your tashkalarexpansions.game.php file.
        
        */
        setupNotifications: function()
        {
            // console.log( 'notifications subscriptions setup' );

	    // Main actions
            dojo.subscribe( 'piecePlayed', this, "notif_piecePlayed" );
            this.notifqueue.setSynchronous( 'piecePlayed', 500 );
            dojo.subscribe( 'piecePicked', this, "notif_piecePicked" );
            this.notifqueue.setSynchronous( 'piecePicked', 500 );
            dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            this.notifqueue.setSynchronous( 'cardPlayed', 1200 );
            dojo.subscribe( 'frozenPlayed', this, "notif_frozenPlayed" );
            this.notifqueue.setSynchronous( 'frozenPlayed', 500 );
            dojo.subscribe( 'warpPlayed', this, "notif_warpPlayed" );
            this.notifqueue.setSynchronous( 'warpPlayed', 500 );
            dojo.subscribe( 'frozenInPlay', this, "notif_frozenInPlay" );
            dojo.subscribe( 'flarePlayed', this, "notif_flarePlayed" );
            this.notifqueue.setSynchronous( 'flarePlayed', 500 );
            dojo.subscribe( 'pendingMoved', this, "notif_pendingMoved" );
            this.notifqueue.setSynchronous( 'pendingMoved', 500 );
            dojo.subscribe( 'gatewayChanged', this, "notif_gatewayChanged" );
            this.notifqueue.setSynchronous( 'gatewayChanged', 500 );
            dojo.subscribe( 'cantSummon', this, "notif_cantSummon" );
//            dojo.subscribe( 'savePoint', this, "notif_savePoint" );
            dojo.subscribe( 'nextChunk', this, "notif_nextChunk" );
            dojo.subscribe( 'takeBack', this, "notif_takeBack" );
            this.notifqueue.setSynchronous( 'takeBack', 500 );
            dojo.subscribe( 'takeBackCards', this, "notif_takeBackCards" );
//            this.notifqueue.setSynchronous( 'takeBackCards', 500 );

	    // Visible effects
            dojo.subscribe( 'pieceDestroyed', this, "notif_pieceDestroyed" );
            this.notifqueue.setSynchronous( 'pieceDestroyed', 500 );
            dojo.subscribe( 'pieceUpgraded', this, "notif_pieceUpgraded" );
            this.notifqueue.setSynchronous( 'pieceUpgraded', 500 );
            dojo.subscribe( 'pieceDowngraded', this, "notif_pieceDowngraded" );
            this.notifqueue.setSynchronous( 'pieceDowngraded', 500 );
            dojo.subscribe( 'pieceMoved', this, "notif_pieceMoved" );
            this.notifqueue.setSynchronous( 'pieceMoved', 500 );
            dojo.subscribe( 'pieceConverted', this, "notif_pieceConverted" );
            this.notifqueue.setSynchronous( 'pieceConverted', 700 );
            dojo.subscribe( 'pieceCaptured', this, "notif_pieceCaptured" );
            this.notifqueue.setSynchronous( 'pieceCaptured', 500 );
            dojo.subscribe( 'pieceFreed', this, "notif_pieceFreed" );
            this.notifqueue.setSynchronous( 'pieceFreed', 500 );

	    // Less visible effects or delayed effects (mainly text logs)
            dojo.subscribe( 'actionGained', this, "notif_actionGained" );
            dojo.subscribe( 'actionLost', this, "notif_actionLost" );
            dojo.subscribe( 'turnGained', this, "notif_turnGained" );
            dojo.subscribe( 'effectSkipped', this, "notif_effectSkipped" );
            dojo.subscribe( 'warSummon', this, "notif_warSummon" );
            dojo.subscribe( 'legendSummoned', this, "notif_legendSummoned" );
            dojo.subscribe( 'extraCard', this, "notif_extraCard" );
            dojo.subscribe( 'cardDiscarded', this, "notif_cardDiscarded" );
            dojo.subscribe( 'pieceRemoved', this, "notif_pieceRemoved" );
            dojo.subscribe( 'merchantImmunity', this, "notif_merchantImmunity" );
            dojo.subscribe( 'putCardOnBottom', this, "notif_putCardOnBottom" );
            dojo.subscribe( 'putTopCardOnTop', this, "notif_putTopCardOnTop" );

	    // Game interface updates
	    dojo.subscribe( 'colorsPicked', this, "notif_colorsPicked" );
	    if ( ! this.isSpectator )
		dojo.subscribe( 'piecesDiff', this, "notif_piecesDiff" );
	    else
		dojo.subscribe( 'piecesDiffSpectator', this, "notif_piecesDiffSpectator" );
            dojo.subscribe( 'updateScore', this, "notif_updateScore" );
            dojo.subscribe( 'retrieveTasks', this, "notif_retrieveTasks" );
	    dojo.subscribe( 'cleanDestroyed', this, "notif_cleanDestroyed" );
	    dojo.subscribe( 'improSummon', this, "notif_improSummon" );

	    // Hand display updates, for player only
            dojo.subscribe( 'chooseDeck', this, "notif_chooseDeck" );
            this.notifqueue.setSynchronous( 'chooseDeck', 500 );
            dojo.subscribe( 'retrieveCards', this, "notif_retrieveCards" );
            this.notifqueue.setSynchronous( 'retrieveCards', 500 );
            dojo.subscribe( 'discardCard', this, "notif_discardCard" );
            dojo.subscribe( 'discardFrozen', this, "notif_discardFrozen" );
//            this.notifqueue.setSynchronous( 'discardCard', 500 );
            dojo.subscribe( 'taskClaimed', this, "notif_taskClaimed" );
            this.notifqueue.setSynchronous( 'taskClaimed', 2000 );

            dojo.subscribe( 'lastTurn', this, "notif_lastTurn" );
            dojo.subscribe( 'formChanged', this, "notif_formChanged" );

            // here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // from this point and below, you can write your game notifications handling methods
        
	notif_piecePlayed: function( notif )
        {
            // console.log( 'notif_piecePlayed' );
            // console.log( notif );

            this.addTokenOnBoard(notif.args.x, notif.args.y, notif.args.player, notif.args.rank);
	    if ( notif.args.rank == 2 && notif.args.game_form == 1 )
		this.scoreCtrl[notif.args.player_id].incValue( 1 );
        },

	notif_piecePicked: function( notif )
        {
            // console.log( 'notif_piecePicked' );
            // console.log( notif );
	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    dojo.setAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token', "" );
	    var token = 'token_'+n;

            var slide = this.slideToObject( token,
			'overall_player_board_'+notif.args.player_id );
	    dojo.connect( slide, 'onEnd', this,
			  dojo.hitch( this, function( node ) {
                              dojo.destroy(node);
			  } ) );
	    var anim = [slide];
	    if ( notif.args.gateway ) {
		slide = this.slideToObject( 'gateway', 'player_boards' );
		dojo.connect( slide, 'onEnd', this,
			      dojo.hitch( this, function( node ) {
				  dojo.style(node, 'display', 'none');
			      } ) ); 
		anim.push(slide);
	    }
	    dojo.fx.combine(anim).play();
	    if ( notif.args.rank == 2 && notif.args.game_form == 1 )
		this.scoreCtrl[notif.args.player_id].incValue( -1 );
        },    

	updateDestroyedZone: function( notif )
	{
	    if ( notif.args.game_form == 2
		 && notif.args.color_destroyed != notif.args.player_id )
	    {
		for (var i=0 ; i<=notif.args.additional_destroyed ; i++) {
		    dojo.place( this.format_block( 'jstpl_token', {
			n: '_'+this.destroyed,
			color: this.gamedatas.players[ notif.args.color_destroyed ].color,
			therank: this.rankName(notif.args.rank_destroyed),
		    } ) , $('the_destroyed') );
		    this.theDestroyed.placeInZone( 'token__'+this.destroyed );
		    if (i>0)
			dojo.addClass( 'token__'+this.destroyed,
				       'additional_destroyed' );
		    this.destroyed++;
		}
	    }
	},

	notif_pieceDestroyed: function( notif )
        {
            // console.log( 'notif_pieceDestroyed' );
            // console.log( notif );
	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    dojo.setAttr( 'square_'+notif.args.x+'_'+notif.args.y,
			  'token', "" );
	    var token = 'token_'+n;

            // dojo.fadeOut( { node: token,
            //                 onEnd: function( node ) {
            //                    	dojo.destroy(node);
            //                         } 
            //               } ).play();
	    this.fadeOutAndDestroy( token );

	    if (! notif.args.titan)
		this.updateDestroyedZone( notif );
        },    

	notif_pieceUpgraded: function( notif )
        {
            // console.log( 'notif_pieceUpgraded' );
            // console.log( notif );
	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    var token = 'token_'+n;
	    var therank = this.rankName(notif.args.rank);

	    // dojo.animateProperty( { node:token, properties:{
	    // 	transform: 'scaleX(0)' } } ).play();
	    var dummy = 'dummy_'+this.next_token;
	    this.next_token++;
            dojo.place( '<div id="'+dummy+'" class="above"></div>', $('tokens') );
	    dojo.addClass( dummy, $(token).className );
            this.placeOnObject( dummy,
	    			'square_'+notif.args.x+'_'+notif.args.y );
	    dojo.removeClass( token, [ 'common', 'heroic', 'legendary' ] );
            dojo.addClass( token, this.rankName(notif.args.rank) );
	    this.fadeOutAndDestroy( dummy );
	    
	    if ( notif.args.rank == 2 && notif.args.game_form == 1 )
		this.scoreCtrl[notif.args.id].incValue( 1 );
        },    

	notif_pieceDowngraded: function( notif )
        {
            // console.log( 'notif_pieceDowngraded' );
            // console.log( notif );
	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    var token = 'token_'+n;

	    var dummy = 'dummy_'+this.next_token;
	    this.next_token++;
            dojo.place( '<div id="'+dummy+'" class="above"></div>', $('tokens') );
	    dojo.addClass( dummy, $(token).className );
            this.placeOnObject( dummy,
	    			'square_'+notif.args.x+'_'+notif.args.y );
	    dojo.removeClass( token, [ 'common', 'heroic', 'legendary' ] );
            dojo.addClass( token, this.rankName(notif.args.rank) );
	    this.fadeOutAndDestroy( dummy );

	    if ( notif.args.rank == 1 && notif.args.game_form == 1 )
		this.scoreCtrl[notif.args.id].incValue( -1 );
        },    

	notif_pieceMoved: function( notif )
        {
            // console.log( 'notif_pieceMoved' );
            // console.log( notif );
	    var x = notif.args.x;
	    var y = notif.args.y;
	    var from_x = notif.args.from_x;
	    var from_y = notif.args.from_y;
	    var n = dojo.getAttr( 'square_'+x+'_'+y, 'token' );
	    var token = 'token_'+n;
	    var nfrom = dojo.getAttr( 'square_'+notif.args.from_x+'_'+notif.args.from_y, 'token' );
	    var tokenfrom = 'token_'+nfrom;
	    dojo.setAttr( 'square_'+x+'_'+y, 'token', nfrom );
	    dojo.setAttr('square_'+notif.args.from_x+'_'+notif.args.from_y,
			  'token', "" );

	    var anim = [];

	    if ( $(token) != null && !notif.args.swap )
	    {
		anim.push( dojo.fadeOut( { node: token,
				onEnd: function( node ) {
                               	    dojo.destroy(node);
                                } 
					 } ) );

		this.updateDestroyedZone( notif );
	    }

	    anim.push( this.slideToObject( tokenfrom, 'square_'+x+'_'+y ) );
	    switch ( notif.args.gateway_moves ) {
	    case 'to':
		anim.push( this.slideToObject( 'gateway', 'square_'+x+'_'+y ) );
		break;
	    case 'from':
		anim.push( this.slideToObject( 'gateway', 'square_'+from_x+'_'+from_y ) );
		break;
	    }
	    
	    if ( notif.args.swap ) {
		anim.push( this.slideToObject( token, 'square_'+from_x+'_'+from_y ) );
		dojo.setAttr('square_'+notif.args.from_x+'_'+notif.args.from_y,
			  'token', n );
	    }
	    dojo.fx.combine(anim).play();
        },

	notif_pieceConverted: function( notif )
        {
            // console.log( 'notif_pieceConverted' );
            // console.log( notif );
	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    var token = 'token_'+n;

	    var self = this;
	    dojo.fx.chain( [
		dojo.fadeOut( { node:token,
				onEnd: function(){
		dojo.removeClass( token, [ 'token_037cb1', 'token_dc2515',
					   'token_d6b156', 'token_8ec459',
					   'token_f0f9ff', 'token_f4913c',
					   'token_6a548f' ] );
		dojo.addClass( token,
		'token_'+self.gamedatas.players[notif.args.player_id].color );
		dojo.removeClass( token, [ 'common', 'heroic', 'legendary' ] );
		dojo.addClass( token, self.rankName(notif.args.newrank) );
				} } ),
		dojo.fadeIn( { node:token } )
	    ] ).play();
	    
	    this.updateDestroyedZone( notif );
        },    

	notif_pieceCaptured: function( notif )
        {
            // console.log( 'notif_pieceCaptured' );
            // console.log( notif );
	    var x = notif.args.x;
	    var y = notif.args.y;
	    var n = dojo.getAttr( 'square_'+x+'_'+y, 'token' );
	    var token = 'token_'+n;
	    dojo.setAttr( 'square_'+x+'_'+y, 'token', "" );

	    dojo.removeClass( 'captured_piece' );
	    dojo.addClass( 'captured_piece', dojo.getAttr( token, 'class' ) );
	    dojo.addClass( 'captured_piece', 'piecesicon' );
	    dojo.removeClass( 'captured_piece', 'token' );
            var slide = this.slideToObject( token,
					    'captured_piece' );
	    dojo.connect( slide, 'onEnd', this,
			  dojo.hitch( this, function( node ) {
                              dojo.destroy(node);
			      dojo.style( 'captured_piece', 'opacity', 1 );
			  } ) );
	    var anim = [slide];

	    if (notif.args.gateway)
	    {
		slide = this.slideToObject( 'gateway', 'captured_piece' );
		dojo.connect( slide, 'onEnd', this,
			      dojo.hitch( this, function( node ) {
				  dojo.style( 'gateway', 'display', 'none' );
				  dojo.style( 'mini_gateway', 'display', 'inline-block' );
			      } ) );
		anim.push(slide);
	    }
	    dojo.fx.combine(anim).play();
        },

	notif_pieceFreed: function( notif )
        {
            // console.log( 'notif_pieceFreed' );
            // console.log( notif );
	    var x = notif.args.x;
	    var y = notif.args.y;
	    var n = dojo.getAttr( 'square_'+x+'_'+y, 'token' );
	    var token = 'token_'+n;
	    dojo.style( 'captured_piece', 'opacity', 0 );
	    this.addTokenOnBoard( x, y, notif.args.player_freed, 
				  notif.args.rank_freed, notif.args.gateway );
	    if ( notif.args.gateway ) {
		this.placeOnObject( 'gateway', 'mini_gateway' );
		dojo.style( 'gateway', 'display', 'inline-block' );
		dojo.style( 'mini_gateway', 'display', 'none' );
		this.slideToObject( 'gateway', 'square_'+x+'_'+y ).play();
	    }

	    if ( $(token) != null )
	    {
		dojo.fadeOut( { node: token,
				onEnd: function( node ) {
                               	    dojo.destroy(node);
                                } 
					 } ).play();

		this.updateDestroyedZone( notif );
	    }
        },

	notif_cardPlayed: function( notif )
        {
            // console.log( 'notif_cardPlayed' );
            // console.log( notif );

	    var decks = ['Northern', 'Southern', 'Highland', 'Sylvan', 'Everfrost', 'Nethervoid', 'Etherweave', 'Legends'];
	    if ( notif.args.frozentext !== null )
		this.addTooltipHtml(
		    'last_card_'+notif.args.deck_id+'_'+notif.args.card_id,
		    this.format_block( 'jstpl_frozen_tip',
			{ name:_(notif.args.being), text:_(notif.args.text),
			  frozentext:_(notif.args.frozentext),
			  deck:decks[notif.args.deck_id],
			  offset:250*notif.args.card_id } ), 510 );
	    else if ( notif.args.warptext !== null )
		this.addTooltipHtml(
		    'last_card_'+notif.args.deck_id+'_'+notif.args.card_id,
		    this.format_block( 'jstpl_warp_tip',
			{ name:_(notif.args.being), text:_(notif.args.text),
			  warptext:_(notif.args.warptext),
			  deck:decks[notif.args.deck_id],
			  offset:250*notif.args.card_id } ), 510 );
	    else
		this.addTooltipHtml(
		    'last_card_'+notif.args.deck_id+'_'+notif.args.card_id,
		    this.format_block( 'jstpl_deck_tip',
			{ name:_(notif.args.being), text:_(notif.args.text),
			  deck:decks[notif.args.deck_id],
			  offset:250*notif.args.card_id } ), 510 );

	    /***
	    this.updateLast( notif.args.player_id,
			     decks[notif.args.deck_id], notif.args.card_id,
			     this.format_block( 'jstpl_deck_tip',
		{ name:_(notif.args.being), text:_(notif.args.text),
		  deck:decks[notif.args.deck_id],
		  offset:250*notif.args.card_id } ) );
	    ***/


	    var anim = [];
	    var lightOn = [];
	    var lightOff = [];
	    for (var i in notif.args.used)
	    {
		var square = 'square_'+notif.args.used[i][0]
		    +'_'+notif.args.used[i][1];
		dojo.addClass( square, "summonBeing" );
		anim.push (dojo.fx.chain( [
		    dojo.animateProperty( {node:square,
					   duration:500,
					   properties:{opacity:{start:0.0, end:0.7}} } ),
		    dojo.animateProperty( {node:square,
					   duration:500,
					   properties:{opacity:0.2}
					   } )
		] ) );
		lightOn.push(
		    dojo.animateProperty( {node:square+'_summon',
					   duration:100,
					   properties:{opacity:{start:0.0, end:0.6}} } ) );
		lightOff.push(
		    dojo.animateProperty( {node:square+'_summon',
					   duration:100,
					   properties:{opacity:{start:0.6, end:0.0}} } ) );
	    }
	    lightOn.push( dojo.animateProperty(
		{node:'square_'+notif.args.x+'_'+notif.args.y+'_summon',
		 duration:100,
		 properties:{opacity:{start:0.0, end:0.9}} } ) );
	    lightOff.push( dojo.animateProperty(
		{node:'square_'+notif.args.x+'_'+notif.args.y+'_summon',
		 duration:100,
		 properties:{opacity:{start:0.9, end:0.0}} } ) );

	    var the_anim = dojo.fx.combine(anim);
	    dojo.connect( the_anim, 'onEnd', this,
			  dojo.hitch( this, function( node ) {
		    dojo.query(".summonBeing").removeClass('summonBeing');
			  } ) );
	    the_anim.play();

	    dojo.connect(
		$('last_card_'+notif.args.deck_id+'_'+notif.args.card_id),
		'onmouseover', this, dojo.hitch( this, function( node ) {
		    dojo.fx.combine(lightOn).play();
			  } ) );

	    dojo.connect(
		$('last_card_'+notif.args.deck_id+'_'+notif.args.card_id),
		'onmouseout', this, dojo.hitch( this, function( node ) {
		    dojo.fx.combine(lightOff).play();
			  } ) );

	    var n = dojo.getAttr( 'square_'+notif.args.x+'_'+notif.args.y,
				  'token' );
	    var token = 'token_'+n;
	    if ( $(token) != null )
	    {
		var self=this;
		var anim = dojo.fadeOut( { node: token,
				onPlay: function( node ) {
				    self.addTokenOnBoard(notif.args.x,
				    	notif.args.y, notif.args.player_id,
				    			 notif.args.rank );
                                },
				onEnd: function( node ) {
				    dojo.destroy( node );
				}
					 } );
		anim.play();
		this.updateDestroyedZone( notif );
	    }
	    else
		this.addTokenOnBoard(notif.args.x, notif.args.y, notif.args.player_id, notif.args.rank);

	    this.from_x = notif.args.x;
	    this.from_y = notif.args.y;
	    if ( notif.args.deck_id == 6
		 && notif.args.card_id == this.pending ) {
		this.pending = -1;
		dojo.style( 'pending_malus', 'display', 'none' );
		dojo.fadeOut( {node: 'warp_card', duration: 500} ).play();
		this.removeTooltip( 'warp_effect' );
//		dojo.style( 'warp_card', 'visibility', 'hidden' );
	    }

	    if ( notif.args.rank == 2
		 && Object.keys(this.gamedatas.players).length == 2 )
		this.scoreCtrl[notif.args.player_id].incValue( 1 );
        },    

	notif_frozenPlayed: function( notif )
        {
            // console.log( 'notif_frozenPlayed' );
            // console.log( notif );
	    
	    dojo.style( 'frozen_card', 'visibility', 'hidden' );
	    this.removeTooltip( 'frozen_effect' );

	    this.addTooltipHtml( 'last_frozen_'+notif.args.card_id,
				 '<div class="frozenicon"></div>'
				 +'<i>'+_(notif.args.frozentext)+'</i>' );
        },    

	notif_pendingMoved: function( notif )
        {
            // console.log( 'notif_pendingMoved' );
            // console.log( notif );
	    
	    dojo.fadeOut( {node: 'warp_card', duration: 500} ).play();
	    this.pending = -1;
	    dojo.style( 'pending_malus', 'display', 'none' );
	    dojo.query( '#warp_effect' ).forEach(
		dojo.hitch( this, function(node) {
		    this.removeTooltip( node );
		} ) );
	    dojo.query(".pending").forEach(
	        function (node) {
		    dojo.removeClass(node, "pending");
		    dojo.addClass(node, "unpending");	        }
	    );
        },

	notif_warpPlayed: function( notif )
        {
            // console.log( 'notif_warpPlayed' );
            // console.log( notif );

	    if ( !notif.args.copy ) {
		this.pending = notif.args.card_id;
		if ( !this.isSpectator
		&& this.gamedatas.players[this.player_id]['color']=='6a548f' )
		{
		    dojo.removeClass( 'hand_item_6_'+notif.args.card_id, 'unpending' );
		    dojo.addClass( 'hand_item_6_'+notif.args.card_id, 'pending' );
		}
		dojo.style( 'pending_malus', 'display', 'inline' );
		var hoffset = 125 * notif.args.card_id / 2 + 2;
		dojo.style( 'warp_card', 'backgroundPosition',
			    '-'+hoffset+'px -12px' );
		//	    dojo.style( 'warp_card', 'visibility', 'visible' );
		dojo.fadeIn( {node: 'warp_card', duration: 500} ).play();
		this.addTooltipHtml( 'warp_effect',
				 this.format_block( 'jstpl_warp_tip',
					{ name:_(notif.args.being),
					  text:_(notif.args.text),
					  warptext:_(notif.args.warptext),
					  deck:'Etherweave',
					  offset:250*notif.args.card_id } ) );
	    }
	    
	    this.addTooltipHtml( 'last_warp_'+notif.args.card_id,
				 '<div class="warpicon"></div>'
				 +'<i>'+_(notif.args.warptext)+'</i>' );
        },    

	notif_frozenInPlay: function( notif )
	{
            // console.log( 'notif_frozenPlayed' );
            // console.log( notif );
	    
	    var hoffset = 125 * notif.args.card_id / 2 + 2;
	    dojo.style( 'frozen_card', 'backgroundPosition',
			'-'+hoffset+'px -12px' );
	    dojo.style( 'frozen_card', 'visibility', 'visible' );
	    this.addTooltipHtml( 'frozen_effect',
				 '<div class="frozenicon"></div>'
				 +'<i>'+_(notif.args.frozentext)+'</i>' );
	},

	notif_flarePlayed: function( notif )
        {
            // console.log( 'notif_flarePlayed' );
            // console.log( notif );
	    
	    /***
	    this.updateLast( notif.args.player_id,
			     'Flare', notif.args.flarenum,
			     this.format_block( 'jstpl_flare_tip',
					{ upgraded:_(notif.args.upgraded),
					  pieces:_(notif.args.pieces),
					  offset:250*notif.args.flarenum } ) );
	    ***/

	    this.addTooltipHtml(
		'last_card_flare_'+notif.args.flarenum,
		this.format_block( 'jstpl_flare_tip',
		{ upgraded:_(notif.args.upgraded),
		  pieces:_(notif.args.pieces),
		  offset:250*notif.args.flarenum } ) );

	    if ( notif.args.game_form == 2
		 && Object.keys(this.gamedatas.players).length == 2 )
		this.scoreCtrl[notif.args.against].incValue( 1 );	    
        },    

	changeGateway: function(x,y, delay)
	{
	    if (parseInt(x) == -1)
	    {
		var slide = this.slideToObject( 'gateway', 'player_boards', 500, delay );
		dojo.connect( slide, 'onEnd', this,
			      dojo.hitch( this, function( node ) {
				  dojo.style(node, 'display', 'none');
			      } ) );
		slide.play();
	    }
	    else if (parseInt(x) == 0) {
		slide = this.slideToObject( 'gateway', 'captured_piece' );
		dojo.connect( slide, 'onEnd', this,
			      dojo.hitch( this, function( node ) {
				  dojo.style( 'gateway', 'display', 'none' );
				  dojo.query( '#mini_gateway' ).style( 'display', 'inline-block' );
			      } ) );
		slide.play();
	    }
	    else
	    {
		dojo.query( '#mini_gateway' ).style( 'display', 'none' );
		dojo.style( 'gateway', 'display', 'inline-block');
		this.slideToObject( 'gateway',
			'square_'+x+'_'+y, 500, delay ).play();
	    }
	},

	notif_gatewayChanged: function(notif)
	{
            // console.log( 'notif_gatewayChanged' );
            // console.log( notif );

	    this.changeGateway(notif.args.x,notif.args.y, 0);
	},
	
	notif_cantSummon: function( notif )
        {
            // console.log( 'notif_cantSummon' );
            // console.log( notif );
        },    

	notif_actionGained: function( notif )
        {
            // console.log( 'notif_actionGained' );
            // console.log( notif );
        },    

	notif_turnGained: function( notif )
        {
            // console.log( 'notif_turnGained' );
            // console.log( notif );
	    var message = dojo.string.substitute( _('${name} takes an extra turn'), {name:notif.args.player_name} );
	    this.showMessage( message, "info" );
        },    

	notif_effectSkipped: function( notif )
        {
            // console.log( 'notif_effectSkipped' );
            // console.log( notif );
        },    

	displayPiecesDiff: function( notif )
	{
	    for (var i in notif.args)
	    {
	    	if (i != 'activable' && i != 'flarenum')
	    	    $(i).innerHTML = notif.args[i];
	    }
	    if (notif.args.activable == 1)
		{
		    dojo.query( '#hand_item_flare_'+notif.args.flarenum ).addClass( 'activable' );
		}
	    else
		{
		    dojo.query( '#hand_item_flare_'+notif.args.flarenum ).removeClass( 'activable' );
		}
	},

	displayPiecesDiffSpectator: function( notif )
	{
	    for (var p in notif) {
		for (var i in notif[p].args) {
	    	    if (i != 'activable' && i != 'flarenum')
	    		$(i).innerHTML = notif[p].args[i];
		}
	    }
	},

	notif_colorsPicked: function( notif )
        {
            // console.log( 'notif_colorsPicked' );
            // console.log( notif );
	    
	    this.installMeleeIcons( notif.args.players );
        },    

	notif_piecesDiff: function( notif )
        {
            // console.log( 'notif_piecesDiff' );
            // console.log( notif );

	    this.displayPiecesDiff( notif );
        },    

	notif_piecesDiffSpectator: function( notif )
        {
            // console.log( 'notif_piecesDiffSpectator' );
            // console.log( notif );

	    this.displayPiecesDiffSpectator( notif.args );
        },    

	notif_updateScore: function( notif )
        {
            // console.log( 'notif_updateScore' );
            // console.log( notif );
	    if (typeof notif.args.against === 'undefined')
	    {
		this.scoreCtrl[notif.args.player_id].incValue( notif.args.diff );
	    }
	    else
	    {
		$('meleescore_'+notif.args.player_id
		  +'_vs_'+notif.args.against).innerHTML = notif.args.result;
	    }
	},

	notif_warSummon: function( notif )
        {
            // console.log( 'notif_warSummon' );
            // console.log( notif );
	},

	notif_legendSummoned: function( notif )
        {
            // console.log( 'notif_legendSummoned' );
            // console.log( notif );
	},

	notif_taskClaimed: function( notif )
        {
            // console.log( 'notif_taskClaimed' );
            // console.log( notif );
	    
	    // Show 
	    if ( typeof notif.args.used == 'string' )
	    {
		var msg = dojo.string.substitute(
		    _('${player_name} claimed ${task_name}'),
		    {player_name:notif.args.player_name,
		    task_name:notif.args.task_name} );

		this.showMessage( _(msg), "info" );
	    }
	    else
	    {
		var anim = [];
		for (var x in notif.args.used)
		    for (var y in notif.args.used[x])
			if (notif.args.used[x][y])
		{
		    var square = 'square_'+x+'_'+y;
		    dojo.addClass( square, "claimTask" );
		    anim.push (dojo.fx.chain( [
			dojo.animateProperty( {node:square,
					       duration:400,
					       properties:{opacity:0.7} } ),
			dojo.animateProperty( {node:square,
					       duration:400,
					       properties:{opacity:0.2} } ),
			dojo.animateProperty( {node:square,
					       duration:400,
					       properties:{opacity:0.7} } ),
			dojo.animateProperty( {node:square,
					       duration:400,
					       properties:{opacity:0.2} } )
		    ] ) );
		}
		var the_anim = dojo.fx.combine(anim);
		dojo.connect( the_anim, 'onEnd', this,
			      dojo.hitch( this, function( node ) {
			dojo.query(".claimTask").removeClass('claimTask');
			      } ) );
		the_anim.play();
	    }
	    
	    // Claim
	    var tooltip_html;
	    this.claimed_tasks[notif.args.player_id].addToStockWithId( 
		notif.args.task_id, notif.args.task_id,
		'current_tasks_item_'+notif.args.task_id );
	    // dojo.place( "<div id='claimed_task' class='tmp_task'></div>", "current_tasks" );
	    // var offset = 150 * notif.args.task_id;
	    // dojo.style( 'claimed_task',
	    // 		'backgroundPosition', '-'+offset+'px 0px' );
	    // this.placeOnObject( 'claimed_task' ,
	    // 			'current_tasks_item_'+notif.args.task_id );
	    // this.slideToObjectAndDestroy( 'claimed_task',
 	    // 			'overall_player_board_'+notif.args.player_id );
	    tooltip_html = this.tooltips['current_tasks_item_'+notif.args.task_id].label;
	    this.removeTooltip( 'current_tasks_item_'+notif.args.task_id );
	    this.addTooltipHtml( 'claimed_tasks_'+notif.args.player_id+'_item_'+notif.args.task_id, tooltip_html );
	    this.current_tasks.removeFromStockById( notif.args.task_id );
	    this.scoreCtrl[notif.args.player_id].incValue( notif.args.points );

	    // Make next task available
	    var next = this.next_task.getPresentTypeList();
	    for (var id in next)
	    {
		this.current_tasks.addToStockWithId( id, id,
						     'next_task_item_'+id );
		tooltip_html = this.tooltips['next_task_item_'+id].label;
		this.removeTooltip( 'next_task_item_'+id );
		this.addTooltipHtml( 'current_tasks_item_'+id, tooltip_html );
		this.next_task.removeFromStockById( id );
	    }

	    // Display next task
	    if ( notif.args.new_task != -1 )
		this.installCard( notif.args.new_task, this.next_task, null );
	},

	notif_nextChunk: function( notif )
	{
            // console.log( 'notif_nextChunk' );
            // console.log( notif );

	    dojo.query( '.dead_log' ).forEach(
	        function (node) {
	    	    dojo.destroy( node );
	        }
	    );

	    dojo.disconnect( this.undohandle );
	    if (notif.args.before > 0)
	    {
		dojo.removeClass( 'my_undo_button', 'dimmedbutton' );
		this.undohandle = dojo.connect( $('my_undo_button'), 'onclick', this, 'onUndoStep' );
	    }
	    else
	    {
		dojo.addClass( 'my_undo_button', 'dimmedbutton' );
	    }
	    dojo.addClass( 'my_redo_button', 'dimmedbutton' );
	    dojo.disconnect( this.redohandle );
	},

	notif_savePoint: function( notif )
        {
            // console.log( 'notif_savePoint' );
            // console.log( notif );

	    var first;
	    if ( this.savepoints.length == 0)
	    {
		first = 1;
	    }
	    else
	    {
		first = this.savepoints[this.savepoints.length-1].last + 1;
	    }

	    this.lastsavepoint++;
	    if ( this.lastsavepoint < 0 )
		this.lastsavepoint = 0;
	    while ( this.savepoints.length > this.lastsavepoint )
		this.savepoints.pop();

	    var last = 0;
	    dojo.query( '.log' ).forEach(
		function (node) {
		    var id = toint( node.id.substr(4) );
		    if ( id > last )
			last = id;
		}
	    );
	    this.savepoints.push( {first:first, last:last} );

	    dojo.disconnect( this.undohandle );
	    if (notif.args.before > 0)
	    {
		dojo.removeClass( 'my_undo_button', 'dimmedbutton' );
		this.undohandle = dojo.connect( $('my_undo_button'), 'onclick', this, 'onUndoStep' );
	    }
	    else
	    {
		dojo.addClass( 'my_undo_button', 'dimmedbutton' );
	    }
	    dojo.addClass( 'my_redo_button', 'dimmedbutton' );
	    dojo.disconnect( this.redohandle );
	},

	installDestroyedZone: function( notif )
	{
	    if ( notif.args.game_form == 2 )
	    {
		var active = this.getActivePlayerId();
		var ranks = ['common', 'heroic', 'legendary'];
		this.destroyed = 0;
		this.theDestroyed.removeAll();
		dojo.query( '#the_destroyed .token' ).forEach( function(node)
						{ dojo.destroy(node); } );
		for ( var op in notif.args.scores_dm[active] )
		{
		    for ( var r in ranks )
		    {
			for (var i = 0 ;
			 i < notif.args.scores_dm[active][op][ranks[r]] ; i++)
			{
			    dojo.place( this.format_block( 'jstpl_token', {
				n: '_'+this.destroyed,
				color: this.gamedatas.players[ op ].color,
				therank: ranks[r]
			    } ) , $('the_destroyed') );
			    this.theDestroyed.placeInZone( 'token__'+this.destroyed );
			    this.destroyed++;
			}
		    }
		}

		if ( Object.keys(notif.args.scores_dm).length > 2 )
		{
		    for (var id in notif.args.scores_dm )
		    {
			for (var op in notif.args.scores_dm[id] )
			{
			    $('meleescore_'+id+'_vs_'+op).innerHTML =
				notif.args.scores_dm[id][op]['value'];
			    if ( notif.args.scores_dm[id][op]['impro'] == 0 )
				dojo.style( 'impro_'+id+'_vs_'+op,
					    'opacity', 0 );
			    else
				dojo.style( 'impro_'+id+'_vs_'+op,
					    'opacity', 1 );
			}
		    }
		}
	    }
	},

	notif_takeBack: function( notif )
        {
            // console.log( 'notif_takeBack' );
            // console.log( notif );
	    if (notif.args.type == "undo")
	    {
		dojo.query( '.logchunk_'+notif.args.chunk ).forEach(
		    function (node) {
			dojo.addClass( node, 'dead_log' );
			var fadeout = dojo.fadeOut( {node: node.parentNode.parentNode, duration: 500} );
			dojo.connect( fadeout, 'onEnd', function( node ) {
  			    dojo.style( node, 'display', 'none' ); } );
			fadeout.play();
		    } );
// 		if ( this.lastsavepoint >= 0 )
// 		{
// 		    for ( i = this.savepoints[this.lastsavepoint].first ;
// 			  i <= this.savepoints[this.lastsavepoint].last ; i++ )
// 		    {
// 			var fadeout = dojo.fadeOut( {node: 'log_'+i, duration: 500} );    	
// 			dojo.connect( fadeout, 'onEnd', function( node ) {
//  			    dojo.style( node, 'display', 'none' ); } );
// 			fadeout.play();
// 		    }
// //			dojo.fadeOut( {node:'log_'+i, duration:1000} ).play();
// //			dojo.style( 'log_'+i, 'display', 'none' );   
// 		}
// 		this.lastsavepoint--;

		dojo.removeClass( 'my_redo_button', 'dimmedbutton' );
		dojo.disconnect( this.redohandle );
		this.redohandle = dojo.connect( $('my_redo_button'), 'onclick', this, 'onRedoStep' );
		if (notif.args.remaining == 0)
		{
		    dojo.addClass( 'my_undo_button', 'dimmedbutton' );
		    dojo.disconnect( this.undohandle );
		}
	    }
	    else
	    {
		dojo.query( '.logchunk_'+notif.args.chunk ).forEach(
		    function (node) {
			dojo.removeClass( node, 'dead_log' );
 			dojo.style( node.parentNode.parentNode,
				    'display', 'block' );
 			dojo.fadeIn( {node:node.parentNode.parentNode,
				      duration:500} ).play();
		    } );
// 		// Prevent overflow when one player has reloaded
// 		// in the middle of some undo/redo sequence
// 		if ( this.lastsavepoint < this.savepoints.length - 1 )
// 		    this.lastsavepoint++;
// 		if ( this.lastsavepoint >= 0 )
// 		{
// 		    for ( i = this.savepoints[this.lastsavepoint].first ;
// 			  i <= this.savepoints[this.lastsavepoint].last ; i++ )
// 		    {
// 			dojo.style( 'log_'+i, 'display', 'block' );
// 			dojo.fadeIn( {node:'log_'+i, duration:500} ).play();
// 		    }
// //			dojo.style( 'log_'+i, 'display', 'block' );
// 		}

		dojo.removeClass( 'my_undo_button', 'dimmedbutton' );
		dojo.disconnect( this.undohandle );
		this.undohandle = dojo.connect( $('my_undo_button'), 'onclick', this, 'onUndoStep' );
		if (notif.args.remaining == 0)
		{
		    dojo.addClass( 'my_redo_button', 'dimmedbutton' );
		    dojo.disconnect( this.redohandle );
		}
	    }
	    
	    // this.removeTooltip( 'last_card_icon' );
	    // dojo.fadeOut( { node:'last_card_icon',
	    // 		    onEnd: function(){
	    // 			dojo.removeClass( 'last_card_icon' );
	    // 		    } } ).play();

	    // dojo.query( ".token" ).forEach(
	    // 	function(node) { dojo.destroy( node ); } );
            for (var i in notif.args.board)
	    {
		var token = notif.args.board[i];

		if (token.oldplayer == null)
		    this.addTokenOnBoard( token.x, token.y, token.player, token.rank, 500);
		else
		{
		    var n = dojo.getAttr( 'square_'+token.x+'_'+token.y,
					  'token' );
		    if (token.player == null) {
			this.slideToObjectAndDestroy( 'token_'+n,
			      'overall_player_board_'+token.oldplayer );
			dojo.setAttr( 'square_'+token.x+'_'+token.y,
				      'token', "" );
		    }
		    else
		    {
			var slideTo = this.slideToObject( 'token_'+n,
				'overall_player_board_'+token.oldplayer );
//			var self = this;

			var slideBack = this.slideToObject( 'token_'+n,
					'square_'+token.x+'_'+token.y );

			dojo.fx.chain( [ slideTo, slideBack ] ).play();

//			dojo.connect( slideBack, 'onPlay', function( node ) {
			dojo.removeClass( 'token_'+n );
			dojo.addClass( 'token_'+n,
				       ['token',
					'token_'+this.gamedatas.players[ token.player ].color,
					this.rankName(token.rank) ] );
// 			     } );

		    }
		}

//		    this.slideToObjectAndDestroy( 'token_'+token.x+'_'+token.y, 'overall_player_board_'+token.player );

	    }

	    for (var id in notif.args.scores)
	    {
		this.scoreCtrl[id].setValue( notif.args.scores[id] );
	    }

	    this.installDestroyedZone( notif ) ;
	    
	    if ( notif.args.frozen >= 0 )
	    {
		var hoffset = 125 * notif.args.frozen / 2 + 2;
		dojo.style( 'frozen_card', 'backgroundPosition',
			    '-'+hoffset+'px -12px' );
		dojo.style( 'frozen_card', 'visibility', 'visible' );
		this.addTooltipHtml( 'frozen_effect',
				     '<div class="frozenicon"></div>'
				     +'<i>'+_(notif.args.frozentext)+'</i>' );
	    }
	    else
	    {
		dojo.query( '#frozen_card').style( 'visibility', 'hidden' );
		dojo.query( '#frozen_effect' ).forEach(
		    dojo.hitch( this, function(node) {
			this.removeTooltip( node );
		    } ) );
	    }

	    if ( notif.args.pending >= 0 ) {
		var hoffset = 125 * notif.args.pending / 2 + 2;
		dojo.style( 'warp_card', 'backgroundPosition',
			    '-'+hoffset+'px -12px' );
		dojo.query( '#hand_item_6_'+notif.args.pending).removeClass( "unpending" );
		dojo.query( '#hand_item_6_'+notif.args.pending).addClass( "pending" );
		dojo.fadeIn( {node: 'warp_card', duration: 500} ).play();
		this.pending = notif.args.pending;
		dojo.style( 'pending_malus', 'display', 'inline' );
		this.addTooltipHtml( 'warp_effect',
				     this.format_block( 'jstpl_warp_tip',
					{ name:_(notif.args.pendingname),
					  text:_(notif.args.pendingtext),
					  warptext:_(notif.args.warptext),
					  deck:'Etherweave',
					  offset:250*notif.args.pending } ) );
	    }
	    else {
		dojo.query( '#warp_card').style( 'opacity', 0 );
		this.pending = -1;
		dojo.query( '#pending_malus' ).style( 'display', 'none' );
		dojo.query( '#warp_effect' ).forEach(
		    dojo.hitch( this, function(node) {
			this.removeTooltip( node );
		    } ) );
		dojo.query(".pending").forEach(
	            function (node) {
			dojo.removeClass(node, "pending");
			dojo.addClass(node, "unpending");	        }
		);
	    }

	    if ( notif.args.merchant_player != 0 ) {
		dojo.query( '#captured_piece' ).removeClass();
		dojo.query( '#captured_piece' ).addClass( [ 
		    this.rankName(notif.args.merchant_rank),
		    'piecesicon',
	'token_'+this.gamedatas.players[notif.args.merchant_player]['color']
		] );
		dojo.query( '#captured_piece' ).style( 'opacity', 1 );
	    }
	    else
		dojo.query( '#captured_piece' ).style( 'opacity', 0 );

	    this.changeGateway( notif.args.gateway_x, notif.args.gateway_y,
				500 );
	    
	    /***
	    var lp = notif.args.last_played;
	    if (lp != null)
	    {
		this.updateLastAuto( lp );
	    }
	    ***/
	},

	notif_takeBackCards: function( notif )
	{
            // console.log( 'notif_takeBackCards' );
            // console.log( notif );
	    new_ids = [];
	    for (var i in notif.args.hand)
		new_ids.push( this.cardId(notif.args.hand[i]) );
		
	    old = this.playerHand.getAllItems();
	    old_ids = [];
	    for (var i in old)
	    {
		old_ids.push( old[i].id );
		if (new_ids.indexOf( old[i].id ) == -1)
		{
		    this.removeTooltip( 'hand_item_'+old[i].id );
		    this.playerHand.removeFromStockById( old[i].id );
		}
	    }
	    for (var i in notif.args.hand)
		if (old_ids.indexOf( this.cardId(notif.args.hand[i]) ) == -1)
		    this.installCard( notif.args.hand[i],
				      this.playerHand, null );
	    new_ids = [];
	    for (var i in notif.args.discard)
		new_ids.push( notif.args.discard[i].type_arg );
		
	    old = this.playerDiscard.getAllItems();
	    old_ids = [];
	    for (var i in old)
	    {
		old_ids.push( old[i].id );
		if (new_ids.indexOf( old[i].id ) == -1)
		{
		    this.removeTooltip( 'discard_item_'+old[i].id );
		    this.playerDiscard.removeFromStockById( old[i].id );
		    delete this.discard_weights[old[i].id];
		}
	    }
	    for (var i in notif.args.discard)
		if (old_ids.indexOf( notif.args.discard[i].type_arg ) == -1)
		    this.addCard( notif.args.discard[i] );
	    // console.log('WEIGHTS', this.discard_weights);
	    this.playerDiscard.changeItemsWeight( this.discard_weights );
	},

	notif_cardDiscarded: function( notif )
        {
            // console.log( 'notif_cardDiscarded' );
            // console.log( notif );
	},

	notif_pieceRemoved: function( notif )
        {
            // console.log( 'notif_pieceRemoved' );
            // console.log( notif );
	},

	notif_merchantImmunity: function( notif )
        {
            // console.log( 'notif_merchantImmunity' );
            // console.log( notif );
	},

	notif_putCardOnBottom: function( notif )
        {
            // console.log( 'notif_putCardOnBottom' );
            // console.log( notif );
	},

	notif_putTopCardOnTop: function( notif )
        {
            // console.log( 'notif_putTopCardOnTop' );
            // console.log( notif );
	    if ( this.player_id == notif.args.player_id ) {
		dojo.place( "<div id='retrieved_card' class='tmp_card Etherweave'></div>", "discard" );
		var offset = 125 * notif.args.card_id;
		dojo.style( 'retrieved_card', 'backgroundPosition',
			    '-'+offset+'px 0px' );
		this.placeOnObject( 'retrieved_card' ,
				    'discard_item_'+notif.args.card_id );
		this.slideToObjectAndDestroy( 'retrieved_card',
 				'overall_player_board_'+this.player_id );
		this.playerDiscard.removeFromStockById( notif.args.card_id );
		delete this.discard_weights[notif.args.card_id];
	    }
	},

	notif_actionLost: function( notif )
        {
            // console.log( 'notif_actionLost' );
            // console.log( notif );
	},

	notif_extraCard: function( notif )
        {
            // console.log( 'notif_extraCard' );
            // console.log( notif );
	},

	notif_retrieveCards: function( notif )
        {
            // console.log( 'notif_retrieveCards' );
            // console.log( notif );
	    for (var i in notif.args.retrieve)
		this.installCard( notif.args.retrieve[i], this.playerHand, 
				  'overall_player_board_'+this.player_id );
	},

	notif_retrieveTasks: function( notif )
        {
            // console.log( 'notif_retrieveTasks' );
            // console.log( notif );
	    for (var i in notif.args.current)
		this.installCard( notif.args.current[i],
				  this.current_tasks, null );
	    this.installCard( notif.args.next, this.next_task, null );
	},

	notif_cleanDestroyed: function( notif )
        {
	    this.theDestroyed.removeAll();
	    this.destroyed = 0;
	},

	notif_improSummon: function( notif )
        {
            // console.log( 'notif_improSummon' );
            // console.log( notif );
	    dojo.fadeOut( { node:
		    'impro_'+notif.args.player_id+'_vs_'+notif.args.impro_id,
			  } ).play();
	},

	notif_discardCard: function( notif )
        {
            // console.log( 'notif_discardCard' );
            // console.log( notif );
	    var card_ids = notif.args.card_id.split("_");
	    if ( $('hand_item_'+notif.args.card_id) !== null ) {
		this.removeTooltip( 'hand_item_'+notif.args.card_id );
		if ( card_ids[0] != 'flare' && card_ids[0] != 7
		     && notif.args.to == 'discard' ) {
		    this.playerDiscard.addToStockWithId( card_ids[1],
				card_ids[1], 'hand_item_'+notif.args.card_id );
		    this.discard_weights[card_ids[1]] = 1 + Math.max(...Object.values(this.discard_weights));
		// console.log('WEIGHTS', this.discard_weights);
		    this.playerDiscard.changeItemsWeight(this.discard_weights);
		}
		this.playerHand.removeFromStockById( notif.args.card_id );
	    }
	    if ( card_ids[0] != 'flare' && card_ids[0] != 7
		 && notif.args.to == 'discard'
		 && $('discard_item_'+card_ids[1]) ) {
	    	this.addCardTip( 'discard_item_'+card_ids[1],
				 notif.args.card );
	    }
	},

	notif_discardFrozen: function( notif )
        {
            // console.log( 'notif_discardFrozen' );
            // console.log( notif );
//	    this.removeTooltip( 'discard_item_'+notif.args.card_id );
	    this.playerDiscard.addToStockWithId( notif.args.card_id, notif.args.card_id,
				'overall_player_board_'+this.player_id );
	    this.addCardTip( 'discard_item_'+notif.args.card_id, notif.args.card );
	    this.discard_weights[notif.args.card_id] = 1 + Math.max(...Object.values(this.discard_weights));
	    // console.log('WEIGHTS', this.discard_weights);
	    this.playerDiscard.changeItemsWeight( this.discard_weights );
	},

	notif_lastTurn: function( notif )
        {
            // console.log( 'notif_lastTurn' );
            // console.log( notif );
	    this.showMessage( _('The end of the game has been triggered'), "info" );
	    dojo.style( 'endwarning', 'display', 'block' );
	},

	notif_formChanged: function( notif )
        {
            // console.log( 'notif_formChanged' );
            // console.log( notif );
	    this.showMessage( _('Warning : because there are more than 2 players, the game form has been changed to Deathmatch Melee.'), "error" );
	},

	notif_chooseDeck: function( notif )
	{
            // console.log( 'notif_chooseDeck' );
            // console.log( notif );
	    
	    var self = this;
	    dojo.query( '.token_'+notif.args.color ).forEach(
		function(node) {
		    self.slideToObjectAndDestroy( node.id,
			  'overall_player_board_'+notif.args.player_id );
		    	       } );

	    dojo.query('a', 'player_name_'+notif.args.player_id).style(
			'color', '#'+notif.args.color );
	    dojo.removeClass( 'nonlegendaryicon_'+notif.args.player_id,
			      'token_000000' );
	    dojo.removeClass( 'legendaryicon_'+notif.args.player_id,
			      'token_000000' );

	    dojo.addClass( 'nonlegendaryicon_'+notif.args.player_id,
			   'token_'+notif.args.color );
	    dojo.addClass( 'legendaryicon_'+notif.args.player_id,
			   'token_'+notif.args.color );

	    if (notif.args.player_id != this.player_id )
	    {
		var elements;

		var ids = [//'flare_diff_vs_', 
			   'upgradeddiff_container_',
			   'upgradeddiff_vs_', 'upgradeddifficon_vs_',
			   'piecesdiff_container_', 'piecesdiff_vs_',
			   'piecesdifficon_vs_'];

		for (var id in ids)
		{
		    dojo.query( '#player_board_'+notif.args.player_id
				+' #'+ids[id]+'000000').forEach(
		function(node) { node.id = ids[id]+notif.args.color; } );
		}
	    }
	    else
	    {
		if ( !this.isSpectator ) {
		    var decks = ['Northern', 'Southern', 'Highland', 'Sylvan',
				 'Everfrost', 'Nethervoid', 'Etherweave'];
		    var colors = [ '037cb1', 'dc2515', 'd6b156', '8ec459',
				   'f0f9ff', 'f4913c', '6a548f' ];
		    var deck = colors.indexOf(notif.args.color);
		    for( var index = 0 ; index < 18 ; index++ )
		    {
			// Build card type id
			this.playerDiscard.addItemType( index, index,
			    g_gamethemeurl+'img/'+decks[deck]+'.jpg', index );
		    }
		}
	    }

	    this.gamedatas.players[notif.args.player_id]['color'] =
		notif.args.color;
	    
	    if ( notif.args.color == 'f0f9ff' )
	    {
		this.gamedatas.players[notif.args.player_id]['color_back'] = 'a3e4ec';
		dojo.destroy( $('frozen_effect') );
		dojo.place( this.format_block( 'jstpl_frozen', {} ),
			    'pieces_left_'+notif.args.player_id );
		dojo.style( 'deck_container_'+notif.args.player_id,
			    'marginBottom', '14px' );
		dojo.style( 'pieces_container_'+notif.args.player_id,
			    'marginBottom', '14px' );
		dojo.style( 'legendarypieces_container_'+notif.args.player_id,
			    'marginBottom', '14px' );
		dojo.style( 'frozen_card', 'visibility', 'hidden' );
	    }
	    if ( notif.args.color == 'f4913c' )
	    {
		this.gamedatas.players[notif.args.player_id]['color_back'] = '444444';
	    }
	    if ( notif.args.color == '6a548f' )
	    {
		dojo.destroy( $('warp_effect') );
		dojo.place( this.format_block( 'jstpl_warp', {} ),
			    'pieces_left_'+notif.args.player_id, 'after' );
		dojo.place( this.format_block( 'jstpl_malus', {} ),
			    'player_score_'+notif.args.player_id, 'after' );
		this.addTooltip( 'pending_malus', _('Causality penalty'), '' ) ;
		dojo.style( 'deck_container_'+notif.args.player_id,
			    'marginBottom', '14px' );
		dojo.style( 'pieces_container_'+notif.args.player_id,
			    'marginBottom', '14px' );
		dojo.style( 'legendarypieces_container_'+notif.args.player_id,
				'marginBottom', '14px' );
		dojo.style( 'warp_card', 'opacity', 0 );
	    }
	}

        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // play the card in the user interface.
        },    
        
        */
   });             
});
