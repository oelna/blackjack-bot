<?php
// Made by: Arno Richter
// Year:    2020
// Source:  https://github.com/oelna/blackjack-bot

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

DEFINE('BR', "\n");
DEFINE('MATCHES', __DIR__.DIRECTORY_SEPARATOR.'blackjack_matches');
DEFINE('WINVALUE', 21);

if(!file_exists(MATCHES)) {
	mkdir(MATCHES);
}

	$deck = [
  ['c','A'],['c','2'],['c','3'],['c','4'],['c','5'],['c','6'],['c','7'],['c','8'],['c','9'],['c','10'],['c','B'],['c','D'],['c','K'],
  ['s','A'],['s','2'],['s','3'],['s','4'],['s','5'],['s','6'],['s','7'],['s','8'],['s','9'],['s','10'],['s','B'],['s','D'],['s','K'],
  ['h','A'],['h','2'],['h','3'],['h','4'],['h','5'],['h','6'],['h','7'],['h','8'],['h','9'],['h','10'],['h','B'],['h','D'],['h','K'],
  ['d','A'],['d','2'],['d','3'],['d','4'],['d','5'],['d','6'],['d','7'],['d','8'],['d','9'],['d','10'],['d','B'],['d','D'],['d','K']
];

	$deck_keys = array_keys($deck);

	function clean_name($string) {
		$username = mb_strtolower(trim($string));
		$username = ltrim($username, '@');

		return $username;
	}

	function draw() {
		global $deck_keys;

		$random_key = rand(0, sizeof($deck_keys)-1);
		$card_key = $deck_keys[$random_key];
		unset($deck_keys[$random_key]);
		$deck_keys = array_values($deck_keys);

		return $card_key;
	}

	function card_name($index) {
		global $deck;

		// https://gist.github.com/oliveratgithub/0bf11a9aff0d6da7b46f1490f86a71eb
		$suit = '';
		switch ($deck[$index][0]) {
			case 'c':
				$suit = 'Clubs ♣';
				break;
			case 's':
				$suit = 'Spades ♠';
				break;
			case 'd':
				$suit = 'Diamonds ♦';
				break;
			default:
				$suit = 'Hearts ♥';
				break;
		}

		$value = '';
		switch ($deck[$index][1]) {
			case 'B':
				$value = 'Jack';
				break;
			case 'D':
				$value = 'Queen';
				break;
			case 'K':
				$value = 'King';
				break;
			case 'A':
				$value = 'Ace';
				break;
			default:
				$value = $deck[$index][1];
				break;
		}

		return $value.' of '.$suit;
	}

	function card_sum($arr) {
		global $deck;

		$value = 0;
		foreach ($arr as $index) {

			$extra = array('B', 'D', 'K');
			$aces = 0;
			
			if(in_array($deck[$index][1], $extra)) {
				$value += 10;
			} elseif($deck[$index][1] == 'A') {
				$aces += 1;
			} else {
				$value += (int) $deck[$index][1];
			}

			if($aces > 1) {
				$value += 1;
			} elseif($aces == 1) {
				$value += 1;
			}
		}

		return $value;
	}

	if(!empty($_GET['user1']) && !empty($_GET['user2']) && !empty($_GET['command'])) {
		$players = array(clean_name($_GET['user1']), clean_name($_GET['user2']));
		sort($players);
		$filename = mb_strtolower(implode('-', $players)).'.json';
	} else {
		echo('Syntax: !blackjack <username> [new, status, hit, stand, help]'.BR);
		die();
	};

	if(mb_strtolower($_GET['command']) == 'new') {
		// start a new round

		$player_cards = array(
			$_GET['user1'] => array(),
			$_GET['user2'] => array()
		);

		// draw 2 for each player
		$json = array(
			'nextPlayer' => 'selimhex',
			'matchStarted' => time(),
			'matchActive' => time(),
			'matchEnded' => false,
			'stand' => array(),
			'players' => $player_cards
		);

		// draw 2 cards for each player
		for ($i=0; $i < sizeof($players); $i++) {
			foreach ($players as $player) {
				$card_index = draw();
				$card_name = card_name($card_index);
				echo($player.' drew '.$card_name.', '.BR);
				$json['players'][$player][] = $card_index;
			}
		}

		foreach ($players as $player) {
			echo($player.' has: '.card_sum($json['players'][$player]).', '.BR);
		}

		$json['nextPlayer'] = array_rand($players);

		echo('It\'s '.$players[$json['nextPlayer']].'\'s turn!');

		file_put_contents(MATCHES.DIRECTORY_SEPARATOR.$filename, json_encode($json, JSON_PRETTY_PRINT));
	} elseif(mb_strtolower($_GET['command']) == 'status') {
		if(!file_exists(MATCHES.DIRECTORY_SEPARATOR.$filename)) {
			die('This match is not currently running. Start a new one?');
		}

		$json = file_get_contents(MATCHES.DIRECTORY_SEPARATOR.$filename);

		$data = json_decode($json, true);

		if($data['matchEnded'] === true) {
			die('This match has ended. Start a new one?');
		}

		$player1 = $players[0];
		$player2 = $players[1];
		$sum_player1 = card_sum($data['players'][$player1]);
		$sum_player2 = card_sum($data['players'][$player2]);

		echo($player1.' has '.$sum_player1.' '.BR);
		echo($player2.' has '.$sum_player2.' '.BR);
		echo('It\'s '.$players[$data['nextPlayer']].'\'s turn.');
		die();

	} elseif(mb_strtolower($_GET['command']) == 'help') {
		// show usage help

		echo('Syntax: !blackjack <username> [new, status, hit, stand, help]'.BR);
		die();

	} elseif(mb_strtolower($_GET['command']) == 'stand') {
		// existing match, stand.
		
		if(!file_exists(MATCHES.DIRECTORY_SEPARATOR.$filename)) {
			die('This match is not currently running. Start a new one?');
		}

		$json = file_get_contents(MATCHES.DIRECTORY_SEPARATOR.$filename);

		$data = json_decode($json, true);

		if($players[$data['nextPlayer']] == clean_name($_GET['user1'])) {

			if(!in_array(clean_name($_GET['user1']), $data['stand'], true)) {
				array_push($data['stand'], clean_name($_GET['user1']));
			}

			if(sizeof($data['stand']) == sizeof($players)) {
				// all players have stood, calculate results
				echo('All stand. '.BR);

				$data['matchEnded'] = true;

				file_put_contents(MATCHES.DIRECTORY_SEPARATOR.$filename, json_encode($data, JSON_PRETTY_PRINT));

				$player1 = $players[0];
				$player2 = $players[1];
				$sum_player1 = card_sum($data['players'][$player1]);
				$sum_player2 = card_sum($data['players'][$player2]);

				echo(' '.$player1.' has '.$sum_player1.', '.$player2.' has '.$sum_player2.'. ');
				
				if($sum_player1 > $sum_player2) {
					echo($player1.' won!');
				} elseif($sum_player1 < $sum_player2) {
					echo($player2.' won!');
				} else {
					// it's a draw!
					echo('It\'s a tie!');
				}

				@unlink(MATCHES.DIRECTORY_SEPARATOR.$filename);

				die();
			} else {
				$opponent = $data['nextPlayer'];
				$opponent_sum = card_sum($data['players'][$players[$opponent]]);

				$data['nextPlayer'] = ($data['nextPlayer'] + 1) % sizeof($players);
				$next_player_sum = card_sum($data['players'][$players[$data['nextPlayer']]]);
				$data['matchActive'] = time();

				echo($players[$opponent].' stands at '.$opponent_sum.'. It\'s '.$players[$data['nextPlayer']].'\'s turn with '.$next_player_sum.'.');

				file_put_contents(MATCHES.DIRECTORY_SEPARATOR.$filename, json_encode($data, JSON_PRETTY_PRINT));
			}

		} else {
			echo('It\'s not your turn yet!');
		}
	} else {
		// existing match, hit!
		
		if(!file_exists(MATCHES.DIRECTORY_SEPARATOR.$filename)) {
			die('This match is not currently running. Start a new one?');
		}

		$json = file_get_contents(MATCHES.DIRECTORY_SEPARATOR.$filename);

		$data = json_decode($json, true);

		$drawn_cards = array();
		foreach ($players as $player) {
			$drawn_cards = array_merge($data['players'][$player], $drawn_cards);
		}

		sort($drawn_cards);

		$remaining_cards = array_diff($deck_keys, $drawn_cards);
		$remaining_cards = array_values($remaining_cards);

		$deck_keys = $remaining_cards;
		
		if($players[$data['nextPlayer']] == mb_strtolower($_GET['user1'])) {
			// drawing for user1

			if(in_array($players[$data['nextPlayer']], $data['stand'])) {
				// this player has quit drawing, skip!
				echo($players[$data['nextPlayer']].' is standing. '.BR);
			} else {
				// draw a card
				$card_index = draw();
				$card_name = card_name($card_index);

				echo('Player '.$_GET['user1'].' drew '.$card_name.', '.BR);
				$data['players'][$_GET['user1']][] = $card_index;

				// detect win condition
				$sum = card_sum($data['players'][$_GET['user1']]);
				if($sum > WINVALUE) {
					unlink(MATCHES.DIRECTORY_SEPARATOR.$filename);
					die($_GET['user1'].' lost with '.$sum.'!');
				}
				
				echo('Sum: '.card_sum($data['players'][$_GET['user1']]).' '.BR);
			}

			$data['nextPlayer'] = ($data['nextPlayer'] + 1) % sizeof($players);
			if(in_array($players[$data['nextPlayer']], $data['stand'])) {
				// rotate to the next player, if this one already stands
				$data['nextPlayer'] = ($data['nextPlayer'] + 1) % sizeof($players);
			}
			$data['matchActive'] = time();

			echo('It\'s your turn, '.$players[$data['nextPlayer']].'!');

			file_put_contents(MATCHES.DIRECTORY_SEPARATOR.$filename, json_encode($data, JSON_PRETTY_PRINT));

		} else {
			echo('It\'s not your turn yet!');
		}
		
	}
?>
