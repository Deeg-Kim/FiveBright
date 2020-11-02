<?php namespace DGKim\Fivebright\Components;

use Auth;
use Input;
use Validator;
use Redirect;
use Flash;
use Session;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use DGKim\Fivebright\Models\Game;
use DGKim\Fivebright\Models\Jeomsu;
use DGKim\Fivebright\Models\Match;
use RainLab\User\Models\User;

class PlayMatch extends ComponentBase
{

    private $decks = 12;
    private $cards = 4;
    private $deck = array();
    private $handCount = 10;
    private $matCardCount = 8;

    public function onRefresh()
    {
        // Get basic details
        $id = $this->param('id');
        $match = Match::where('id', $id)->first();
        $recentGame = Game::where('match_id', $match->id)->orderBy('id', 'desc')->first();

        $user = Auth::getUser();

        // Build player and jeomsu data
        $playerOne = User::where('id', $match->player_1_id)->first();
        $playerTwo = User::where('id', $match->player_2_id)->first();
        $oneJeomsu = Jeomsu::where('player_email', $playerOne->email)->first();

        $gameStarted = ($playerTwo != null);
        $deckCount = $this->decks * $this->cards;
        $hand = null;
        $mat = null;

        if ($gameStarted) {
            // Which player am I?
            $player = 0;

            if ($user->id == $playerOne->id) {
                $player = 1;
            } else if ($user->id == $playerTwo->id) {
                $player = 2;
            }

            // Handle game logic only if the game has started
            $twoJeomsu = Jeomsu::where('player_email', $playerTwo->email)->first();

            // Handle cards
            if ($recentGame->next_turn == 0) {
                // This case means that it's time to shuffle
                $recentGame->next_turn = $match->last_winner;

                $this->cleanBuildDeck();
                shuffle($this->deck);

                // Build player hands
                $playerOneHand = array();
                $playerTwoHand = array();
                for ($i = 0; $i < $this->handCount; $i++) {
                    $cardOne = array_shift($this->deck);
                    $playerOneHand[] = $cardOne;
                    $cardTwo = array_shift($this->deck);
                    $playerTwoHand[] = $cardTwo;
                }

                sort($playerOneHand);
                sort($playerTwoHand);

                // Build mat
                $matCards = array();
                for ($i = 0; $i < $this->matCardCount; $i++) {
                    $card = array_shift($this->deck);
                    $matCards[] = $card;
                }

                $recentGame->player_1_hand = implode($playerOneHand, ",");
                $recentGame->player_2_hand = implode($playerTwoHand, ",");
                $recentGame->mat_cards = implode($matCards, ",");
                $recentGame->deck_cards = implode($this->deck, ",");
                $recentGame->player_1_cards = implode(array(), ",");
                $recentGame->player_2_cards = implode(array(), ",");

                $recentGame->save();
            } else {

            }

            // Build up visuals
            if ($player == 1) {
                $hand = $recentGame->player_1_hand;
            } else if ($player == 2) {
                $hand = $recentGame->player_2_hand;
            }

            $mat = $recentGame->mat_cards;
            $deckCount = count(explode(",", $recentGame->deck_cards));
        } else {
            $twoJeomsu = 0;
        }

        // The array will be size 1 when exploding a null array
        if ($hand == null) {
            $displayHand = null;
        } else {
            $displayHand = explode(",", $hand);
        }

        if ($mat == null) {
            $displayMat = null;
            $displayMatJokers = null;
        } else {
            $preDisplayMat = explode(",", $mat);
            sort($preDisplayMat);
            $displayMat = array();
            $displayMatJokers = array();

            $displayMat["joker"] = null;
            for ($i = 1; $i <= $this->decks; $i++) {
                $displayMat[] = null;
            }

            for ($i = 0; $i < count($preDisplayMat); $i++) {
                $deckId = $this->getDeck($preDisplayMat[$i]);
                $displayMat[$deckId][] = $preDisplayMat[$i];
            }

            $displayMatJokers = $displayMat["joker"];
        }

        // Return to view
        return [
            "#game" => $this->renderPartial('playMatch::game', [
                    "player1" => $playerOne,
                    "player2" => $playerTwo,
                    "oneJeomsu" => $oneJeomsu,
                    "twoJeomsu" => $twoJeomsu,
                    "hand" => $displayHand,
                    "mat" => $displayMat,
                    "matJokers" => $displayMatJokers,
                    "deckCount" => $deckCount
            ])
        ];
    }

    public function onPlaceCard() {
        // Get basic details
        $id = $this->param('id');
        $match = Match::where('id', $id)->first();
        $recentGame = Game::where('match_id', $match->id)->orderBy('id', 'desc')->first();

        $user = Auth::getUser();
        $playerOne = User::where('id', $match->player_1_id)->first();
        $playerTwo = User::where('id', $match->player_2_id)->first();

        // Which player am I?
        $player = 0;

        if ($user->id == $playerOne->id) {
            $player = 1;
        } else if ($user->id == $playerTwo->id) {
            $player = 2;
        }

        // It has to be your turn to play!
        if ($user->id == $recentGame->next_turn) {
            $cardPlayed = post("card");
            $hand = null;

            if ($player == 1) {
                $hand = $recentGame->player_1_hand;
            } else if ($player == 2) {
                $hand = $recentGame->player_2_hand;
            }
            $hand = explode(",", $hand);

            // Remove card from the hand
            $key = array_search($cardPlayed, $hand);
            array_splice($hand, $key, 1);

            // Place on mat
            $recentGame->recent_card = $cardPlayed;
            if ($player == 1) {
                $recentGame->player_1_hand = implode($hand, ",");
            } else if ($player == 2) {
                $recentGame->player_2_hand = implode($hand, ",");
            }

            $mat = $recentGame->mat_cards;
            $mat = explode(",", $mat);
            array_push($mat, $cardPlayed);
            $mat = implode($mat, ",");

            $recentGame->mat_cards = $mat;

            $recentGame->save();
        }
    }

    public function componentDetails()
    {
        return [
            'name'        => 'Play Match',
            'description' => 'Handles the Go-Stop logic.'
        ];
    }

    public function onRun()
    {
        $user = Auth::getUser();
        $id = $this->param('id');
        $match = Match::where('id', $id)->first();
        $playerOne = User::where('id', $match->player_1_id)->first();

        if ($match->player_2_id == null && $match->player_1_id != $user->id) {
            $match->player_2_id = $user->id;
            $match->save();

            $game = new Game;
            $game->match_id = $match->id;
            $game->save();
        }
    }

    // Utility functions
    function cleanBuildDeck() {
        for ($i = 1; $i <= $this->decks; $i++) {
            for ($j = 1; $j <= $this->cards; $j++) {
                $this->deck[] = $i . "_" . $j;
            }
        }

        $this->deck[] = "joker_2";
        $this->deck[] = "joker_3";
    }

    // Get deck from string
    function getDeck($str) {
        $vars = explode("_", $str);
        return $vars[0];
    }
}
