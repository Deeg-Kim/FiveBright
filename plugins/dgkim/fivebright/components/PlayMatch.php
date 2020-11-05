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

    private $suits = 12;
    private $cards = 4;
    private $deck = array();
    private $handCount = 10;
    private $matCardCount = 8;
    private $maxPlayers = 2;
    private $delim = ",";
    private $playerDelim = ":";

    private $gwang = array("1_1", "3_1", "8_1", "11_1", "12_1");
    private $yul = array("2_1", "4_1", "5_1", "6_1", "7_1", "8_2", "9_1", "10_1",
                         "11_2");
    private $tti = array("1_2", "2_2", "3_2", "4_2", "5_2", "6_2", "7_2", "9_2",
                         "10_2", "11_3");
    private $pi = array("1_3", "1_4", "2_3", "2_4", "3_3", "3_4", "4_3", "4_4",
                        "5_3", "5_4", "6_3", "6_4", "7_3", "7_4", "8_3", "8_4",
                        "9_3", "9_4", "10_3", "10_4", "11_4", "12_2", "12_3",
                        "12_4", "joker_2", "joker_3");
    private $onePointPi = array("1_3", "1_4", "2_3", "2_4", "3_3", "3_4", "4_3",
                                "4_4", "5_3", "5_4", "6_3", "6_4", "7_3", "7_4",
                                "8_3", "8_4", "9_3", "9_4", "10_3", "10_4",
                                "12_3", "12_4");
    private $twoPointPi = array("11_4", "12_2", "joker_2");
    private $threePointPi = array("joker_3");

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

        $player = 0;
        $gameStarted = ($playerTwo != null);
        $deckCount = $this->suits * $this->cards;
        $hand = null;
        $mat = null;
        $cards = null;
        $myGwang = null;
        $myYul = null;
        $myTti = null;
        $myPi = null;
        $nextTurn = 0;
        $askPlayerPlayed = 0;
        $askPlayedCards = null;
        $askPlayerFlipped = 0;
        $askFlippedCards = null;

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

                $recentGame->player_1_hand = implode($playerOneHand, $this->delim);
                $recentGame->player_2_hand = implode($playerTwoHand, $this->delim);
                $recentGame->player_1_cards = null;
                $recentGame->player_2_cards = null;
                $recentGame->setMat($matCards);
                $recentGame->setDeck($this->deck);

                $recentGame->save();
            } else {
                if ($recentGame->recent_card != null && $recentGame->recent_flip == null) {
                    // Player has played a card, need to pull from the deck
                    // Popping from the array for convenience: don't get confused by this later!
                    $playedPlayer = $this->getPlayer($recentGame->recent_card);

                    $nextCard = $recentGame->pullCardFromDeck();
                    $recentGame->addCardToMat($nextCard);

                    // Keep track of the most recent flip
                    $recentGame->recent_flip = $player . $this->playerDelim . $nextCard;

                    $recentGame->save();
                } else if ($recentGame->recent_card != null && $recentGame->recent_flip != null) {
                    // Card has been flipped, need to handle pulling cards to player's pile
                    $currentMat = explode($this->delim, $recentGame->mat_cards);
                    $mappedCards = $this->mapSuits($currentMat);

                    $playedFullCards = explode($this->delim, $recentGame->recent_card);

                    $playedPlayer = $this->getPlayer($recentGame->recent_card);
                    $playedSuit = $this->getSuit($this->getFullCard($playedFullCards[0]));
                    $flippedSuit = $this->getSuit($this->getFullCard($recentGame->recent_flip));

                    $playerCaptured = null;
                    if ($playedPlayer == 1) {
                        $playerCaptured = $recentGame->player_1_cards;
                    } else if ($playedPlayer == 2) {
                        $playerCaptured = $recentGame->player_2_cards;
                    }
                    if ($playerCaptured != null) {
                        $playerCaptured = explode($this->delim, $playerCaptured);
                    } else {
                        $playerCaptured = array();
                    }

                    if ($playedSuit == $flippedSuit) {
                        // Going to be ssa, ttadak or chok

                    } else {
                        // The played and flipped decks are different, so we can handle separately

                        // Handle the played card
                        if (count($playedFullCards) == 1) {
                            // Only one card was played, need to handle a bunch of cases
                            $playedClosable = false;
                            switch (count($mappedCards[$playedSuit])) {
                                case 1:
                                    // There's no match, bad luck!
                                    $playedClosable = true;
                                    break;
                                case 2:
                                    // Simple case, the card is captured and both arrive in player's hand
                                    $captured = $mappedCards[$playedSuit];
                                    for ($i = 0; $i < 2; $i++) {
                                        $currentMat = $this->removeCard($captured[$i], $currentMat);
                                        array_push($playerCaptured, $captured[$i]);
                                    }
                                    $playedClosable = true;
                                    break;
                                case 3:
                                    // Allowed to chose which card from the mat to bring back

                                    // Haven't selected yet
                                    if (post("playedSelection") == null && $recentGame->played_selection == null) {
                                        $askPlayerPlayed = $playedPlayer;
                                        $capturableCards = $mappedCards[$playedSuit];
                                        $askPlayedCards = $this->removeCard($this->getFullCard($recentGame->recent_card), $capturableCards);

                                        // Not closable, as we need to ask the player which to capture
                                        $playedClosable = false;
                                    } else {
                                        // Update selection
                                        $recentGame->played_selection = $playedPlayer . ":" . post("playedSelection");
                                        $recentGame->save();

                                        array_push($playerCaptured, $this->getFullCard($recentGame->recent_card));
                                        $currentMat = $this->removeCard($this->getFullCard($recentGame->recent_card), $currentMat);
                                        array_push($playerCaptured, post("playedSelection"));
                                        $currentMat = $this->removeCard(post("playedSelection"), $currentMat);

                                        $playedClosable = true;
                                    }
                                    break;
                                case 4:
                                    // This would be a bomb case, but we'll handle here anyway for convenience
                                    $playedClosable = true;
                                    break;
                            }
                        } else {
                            // If there were multiple cards played, means we bombed
                            $playedClosable = true;

                            foreach ($mappedCards[$playedSuit] as $c) {
                                array_push($playerCaptured, $c);
                                $currentMat = $this->removeCard($c, $currentMat);
                            }
                        }

                        // Handle the flipped card
                        $flippedClosable = false;
                        switch (count($mappedCards[$flippedSuit])) {
                            case 1:
                                // There's no match, bad luck!
                                $flippedClosable = true;
                                break;
                            case 2:
                                // Simple case, the card is captured and both arrive in player's hand
                                $captured = $mappedCards[$flippedSuit];
                                for ($i = 0; $i < 2; $i++) {
                                    $currentMat = $this->removeCard($captured[$i], $currentMat);
                                    array_push($playerCaptured, $captured[$i]);
                                }
                                $flippedClosable = true;
                                break;
                            case 3:
                                // Allowed to chose which card from the mat to bring back

                                // Haven't selected yet
                                if (post("flippedSelection") == null && $recentGame->flipped_selection == null) {
                                    $askPlayerFlipped = $playedPlayer;
                                    $capturableCards = $mappedCards[$flippedSuit];
                                    $askFlippedCards = $this->removeCard($this->getFullCard($recentGame->recent_flip), $capturableCards);

                                    // Not closable, as we need to ask the player which to capture
                                    $flippedClosable = false;
                                } else {
                                    // Update selection
                                    $recentGame->played_selection = $playedPlayer . ":" . post("flippedSelection");
                                    $recentGame->save();

                                    array_push($playerCaptured, $this->getFullCard($recentGame->recent_flip));
                                    $currentMat = $this->removeCard($this->getFullCard($recentGame->recent_flip), $currentMat);
                                    array_push($playerCaptured, post("flippedSelection"));
                                    $currentMat = $this->removeCard(post("flippedSelection"), $currentMat);

                                    $flippedClosable = true;
                                }
                                break;
                            case 4:
                                // Bring back all the cards
                                $flippedClosable = true;
                                break;
                        }

                        // TODO: Implement sseul

                        $closable = $playedClosable && $flippedClosable;
                        // Close out turn
                        if ($closable) {
                            sort($playerCaptured);
                            if ($playedPlayer == 1) {
                                $recentGame->player_1_cards = implode($playerCaptured, $this->delim);
                            } else if ($playedPlayer == 2) {
                                $recentGame->player_2_cards = implode($playerCaptured, $this->delim);
                            }
                            $recentGame->mat_cards = implode($currentMat, $this->delim);

                            // All actions have been taken care of
                            $nextPlayer = $recentGame->next_turn + 1;

                            if ($nextPlayer > $this->maxPlayers) {
                                $nextPlayer = 1;
                            }

                            $recentGame->next_turn = $nextPlayer;
                            $recentGame->resetTrackers();

                            $recentGame->save();
                        }
                    }
                }
            }

            // Build up visuals
            $hand = $recentGame->getHandForPlayer($player);
            $cards = $recentGame->getCardsForPlayer($player);

            $myGwang = array();
            $myYul = array();
            $myTti = array();
            $myPi = array();

            if ($cards == null) {
                $cards = array();
            }

            foreach ($cards as $card) {
                if (in_array($card, $this->gwang)) {
                    $myGwang[] = $card;
                } else if (in_array($card, $this->yul)) {
                    $myYul[] = $card;
                } else if (in_array($card, $this->tti)) {
                    $myTti[] = $card;
                } else if (in_array($card, $this->pi)) {
                    $myPi[] = $card;
                }
            }

            $mat = $recentGame->mat_cards;
            $deckCount = count(explode($this->delim, $recentGame->deck_cards));
            $nextTurn = $recentGame->next_turn;
        } else {
            // The game hasn't started, but we need to set up some basics
            $player = 1;
            $twoJeomsu = 0;
        }

        if ($mat == null) {
            $displayMat = null;
            $displayMatJokers = null;
        } else {
            $preDisplayMat = explode($this->delim, $mat);
            sort($preDisplayMat);
            $displayMat = array();
            $displayMatJokers = array();

            $displayMat["joker"] = null;
            for ($i = 1; $i <= $this->suits; $i++) {
                $displayMat[] = null;
            }

            for ($i = 0; $i < count($preDisplayMat); $i++) {
                $deckId = $this->getSuit($preDisplayMat[$i]);
                $displayMat[$deckId][] = $preDisplayMat[$i];
            }

            $displayMatJokers = $displayMat["joker"];
        }

        // Return to view
        return [
            "#game" => $this->renderPartial('playMatch::game', [
                    "player" => $player,
                    "player1" => $playerOne,
                    "player2" => $playerTwo,
                    "oneJeomsu" => $oneJeomsu,
                    "twoJeomsu" => $twoJeomsu,
                    "hand" => $hand,
                    "mat" => $displayMat,
                    "matJokers" => $displayMatJokers,
                    "deckCount" => $deckCount,
                    "myGwang" => $myGwang,
                    "myYul" => $myYul,
                    "myTti" => $myTti,
                    "myPi" => $myPi,
                    "nextTurn" => $nextTurn,
                    "askPlayerPlayed" => $askPlayerPlayed,
                    "askPlayedCards" => $askPlayedCards,
                    "askPlayerFlipped" => $askPlayerFlipped,
                    "askFlippedCards" => $askFlippedCards
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
        if (($user->id == $recentGame->next_turn) && $recentGame->recent_card == null) {
            $cardPlayed = post("card");
            $playedSuit = $this->getSuit($cardPlayed);

            $hand = $recentGame->getHandForPlayer($player);
            $mat = $recentGame->getMat();

            $handMap = $this->mapSuits($hand);
            $matMap = $this->mapSuits($mat);

            // Bomb all the cards in the suit
            if ((count($handMap[$playedSuit]) + count($matMap[$playedSuit])) == 4) {
                $playedCards = array();

                // Remove all the relevant cards from the hand
                for ($i = 0; $i < count($handMap[$playedSuit]); $i++) {
                    $cardPlayedIt = $handMap[$playedSuit][$i];

                    $hand = $this->removeCard($cardPlayedIt, $hand);
                    $playedCards[] = $player . $this->playerDelim . $cardPlayedIt;

                    $recentGame->addCardToMat($cardPlayedIt);
                }

                $recentGame->recent_card = implode($playedCards, $this->delim);
                $recentGame->setPlayerHand($player, $hand);
            } else {
                // Remove card from the hand
                $hand = $this->removeCard($cardPlayed, $hand);

                // Place on mat
                $recentGame->recent_card = $player . $this->playerDelim . $cardPlayed;
                $recentGame->setPlayerHand($player, $hand);
                $recentGame->addCardToMat($cardPlayed);
            }
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
        for ($i = 1; $i <= $this->suits; $i++) {
            for ($j = 1; $j <= $this->cards; $j++) {
                $this->deck[] = $i . "_" . $j;
            }
        }

        $this->deck[] = "joker_2";
        $this->deck[] = "joker_3";
    }

    // Build up sorted mapping of deck number to cards
    function mapSuits($cards) {
        $mapping = array();
        for ($i = 1; $i <= $this->suits; $i++) {
            $mapping[$i] = array();
        }

        foreach ($cards as $card) {
            $deck = $this->getSuit($card);
            $cardId = $this->getCard($card);

            $mapping[$deck][] = $card;
        }

        for ($i = 1; $i <= $this->suits; $i++) {
            sort($mapping[$i]);
        }

        return $mapping;
    }

    // Pick a card to steal
    function cardToSteal($cards) {
        $pi = array_intersect($cards, $this->pi);

        // No cards to steal!
        if (count($pi) == 0) {
            return null;
        }

        $onePointCards = array_intersect($pi, $onePointPi);

        if (count($onePointCards) > 0) {
            return $onePointCards[0];
        }

        $twoPointCards = array_intersect($pi, $twoPointPi);

        if (count($twoPointCards) > 0) {
            return $twoPointCards[0];
        }

        $threePointCards = array_intersect($pi, $threePointPi);

        if (count($threePointCards) > 0) {
            return $threePointCards[0];
        }
    }

    // Remove card from array
    function removeCard($card, $arr) {
        $key = array_search($card, $arr);
        array_splice($arr, $key, 1);

        return $arr;
    }

    // Get suit from string
    function getSuit($str) {
        $vars = explode("_", $str);
        return $vars[0];
    }

    // Get card from string
    function getCard($str) {
        $vars = explode("_", $str);
        return $vars[1];
    }

    // Get player from long string
    function getPlayer($str) {
        $vars = explode(":", $str);
        return $vars[0];
    }

    // Get card from long string
    function getFullCard($str) {
        $vars = explode(":", $str);
        return $vars[1];
    }
}
