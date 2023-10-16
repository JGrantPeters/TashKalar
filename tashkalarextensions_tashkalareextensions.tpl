{OVERALL_GAME_HEADER}

<div id="my_discard" class="whiteblock">
       <h3 id="discard_title" class="bgabutton bgabutton_gray">{MY_DISCARD}</h3>
       <div id="discard">
       </div>
</div>

<div id="endwarning" class="endwarning">
     {END_WARNING}
</div>

<div id="board_tasks">
     <div id="board">
     	  <div id="tokens">
	  </div>
	      <!-- BEGIN square -->
    	      <div id="square_{X}_{Y}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    	      <div id="square_{X}_{Y}_summon" class="square_summon" style="left: {LEFT}px; top: {TOP}px;" token=""></div>
	      <!-- END square -->
     	  <div id="selection_overlay"></div>
     </div>

     <div id='the_tasks' class='whiteblock'>
     	  <h3>{AVAILABLE_TASKS}</h3>
	  <div id='current_tasks'>
	  </div>
	  <h3>{NEXT_TASK}</h3>
	  <div id='next_task'>
	  </div>
     </div>

     <div id='claimed_tasks' class='whiteblock'>
     </div>

     <div id='destroyed_pieces' class='whiteblock'>
     	  <h3>{DESTROYED_PIECES}</h3>
	  <div id='the_destroyed'>
	  </div>
     </div>
</div>

<div id="played_hand">
     <div id="my_hand" class="whiteblock">
       <h3>{MY_HAND}</h3>
       <div id="hand">
       </div>
     </div>
</div>


<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${id}"></div>';

*/

var jstpl_token = '<div class="${therank} token_${color} token" id="token_${n}"></div>';

var jstpl_deck_tip = '<div class="cardtooltip">\
    <span class="cardtext">\
    	  <h3>${name}</h3>\
	  <hr>\
	  <div class="bigcard ${deck}_big" style="background-position: -${offset}px 0px;"></div>\
	  <hr>\
    	  ${text}\
    </span>\
</div>';

var jstpl_frozen_tip = '<div class="cardtooltip">\
    <span class="cardtext">\
    	  <h3>${name}</h3>\
	  <hr>\
	  <div class="bigcard ${deck}_big" style="background-position: -${offset}px 0px;"></div>\
	  <hr>\
    	  ${text}\
	  <hr>\
	  <div class="frozenicon"></div>\
    	  <i>${frozentext}</i>\
    </span>\
</div>';

var jstpl_warp_tip = '<div class="cardtooltip">\
    <span class="cardtext">\
    	  <h3>${name}</h3>\
	  <hr>\
	  <div class="bigcard ${deck}_big" style="background-position: -${offset}px 0px;"></div>\
	  <hr>\
	  <div class="warpicon"></div>\
    	  <i>${warptext}</i>\
	  <hr>\
    	  ${text}\
    </span>\
</div>';

var jstpl_flare_tip = '<div class="cardtooltip">\
    <span class="cardtext">\
    	  ${upgraded}\
	  <hr>\
	  <div class="bigcard Flare_big" style="background-position: -${offset}px 0px;"></div>\
	  <hr>\
    	  ${pieces}\
    </span>\
</div>';


/*** Player boards :
- score(s for Deathmatch)
- flare differentials wrt every other player
- remaining/used pieces (and actions ?)
***/

var jstpl_decks_left = '<div id="card_infos" class="roundedbox player_board">\
<div class="centered-block-outer">\
 <div class="centered-block-middle">\
  <div class="centered-block-inner">\
<div id="last_card">\
     <h3>{LAST CARD}</h3>\
     <div id="last_card_icon"></div>\
</div>\
<div id="decks_left" >\
    <h3>{REMAINING CARDS}</h3>\
    <div id="decks_container">\
        <div id="legends_container" class="legends_container shared_container">\
    	    <span id="cards_legends" class="cardsnum">12</span>\
    	    <div id="cardsicon_legends" class="cardsicon"></div>\
	</div>\
    	<div id="flares_container" class="flares_container shared_container">\
             <span id="cards_flares" class="cardsnum">12</span>\
    	     <div id="cardsicon_flares" class="cardsicon"></div>\
    	</div>\
    </div>\
</div>\
  </div>\
 </div>\
</div>\
</div>';

/***
    <div class="last_card">\
      <div id="last_card_icon_${id}" class="last_card_icon dimmedcard"><div></div></div>\
    </div>\
***/

var jstpl_frozen = '<div id="frozen_effect"><div id="frozen_card" class="last_card_icon Everfrost"></div><div id="ice"></div></div>';

var jstpl_gateway = '<div id="gateway"></div>';

var jstpl_warp = '<div id="warp_effect"><div id="warp_card" class="last_card_icon Etherweave"></div><div id="warp"><div id="captured_piece" class="piecesicon"><div id="mini_gateway"></div></div></div></div>';

var jstpl_malus = '<span id="pending_malus" class="player_score_value"> (-2)</span>';

var jstpl_player_board = '<div class="cp_board" id="ttt">\
<div class="centered-block-outer">\
 <div class="centered-block-middle">\
  <div class="centered-block-inner">\
        <div id="deck_container_${id}" class="deck_container">\
             <span id="cards_${id}" class="cardsnum">18</span>\
    	     <div class="cardsicon cardsicon_deck"></div>\
    	</div>\
    <div id="pieces_left_${id}" class="pieces_left">\
        <div id="pieces_container_${id}" class="pieces_container container">\
             <span id="nonlegendary_${id}" class="piecesnum">17</span>\
    	     <div id="nonlegendaryicon_${id}" class="token_${color} common piecesicon"></div>\
    	</div>\
        <div id="legendarypieces_container_${id}" class="legendarypieces_container container">\
             <span id="legendary_${id}" class="piecesnum">3</span>\
    	     <div id="legendaryicon_${id}" class="token_${color} legendary piecesicon"></div>\
    	</div>\
    </div>\
  </div>\
 </div>\
</div>\
<div class="centered-block-outer">\
 <div class="centered-block-middle">\
  <div id="diffs_placeholder_${id}" class="centered-block-inner">\
  </div>\
 </div>\
</div>\
<div class="centered-block-outer">\
 <div class="centered-block-middle">\
  <div id="impros_placeholder_${id}" class="centered-block-inner">\
  </div>\
 </div>\
</div>\
</div>';

//<div class="cp_board">\
//<div class="centered-block-outer">\
// <div class="centered-block-middle">\
//  <div class="centered-block-inner">\
//<div id="flare_diff_vs_${opcolor}" class="flare_diff">\
var jstpl_flare_diff = '<div id="upgradeddiff_container_${opcolor}" class="upgradeddiff_container diff_container">\
    	 <span id="upgradeddiff_vs_${opcolor}" class="diffnum">0</span>\
    	 <div id="upgradeddifficon_vs_${opcolor}" class="difficon upgradeddiff"></div>\
    </div>\
    <div id="piecesdiff_container_${opcolor}" class="piecesdiff_container diff_container">\
    	 <span id="piecesdiff_vs_${opcolor}" class="diffnum">0</span>\
    	 <div id="piecesdifficon_vs_${opcolor}" class="difficon piecesdiff"></div>\
</div>';
//    </div>\
//</div>\
//  </div>\
// </div>\
//</div>\

var jstpl_melee = '<div id="meleescore_container_${id}_vs_${opid}" class="melee_container">\
    	 <span id="meleescore_${id}_vs_${opid}" class="diffnum">0</span>\
    	 <div id="meleescore_icon_${id}_vs_${opid}" class="meleeicon"></div>\
    </div>';
var jstpl_impro = '<div id="impro_${id}_vs_${opid}" class="improicon token_000000"></div>';

var jstpl_claimed_tasks = '<h3>${title}</h3>\
<div id="claimed_tasks_${id}">\
</div>';

</script>  

{OVERALL_GAME_FOOTER}
