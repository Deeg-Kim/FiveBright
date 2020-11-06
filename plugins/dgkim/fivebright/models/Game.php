<?php namespace DGKim\Fivebright\Models;

use Model;

/**
 * Model
 */
class Game extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

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

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'dgkim_fivebright_games';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];


    /**
     * Getters
     */
    public function getHandForPlayer($player) {
        $hand = null;
        if ($player == 1) {
            $hand = $this->player_1_hand;
        } else if ($player == 2) {
            $hand = $this->player_2_hand;
        } else {
            throw new Exception('Invalid player number!');
        }

        // The array will be size 1 when exploding a null array
        if ($hand == null) {
            return $hand;
        } else {
            return explode($this->delim, $hand);
        }
    }

    public function getCardsForPlayer($player) {
        $cards = null;
        if ($player == 1) {
            $cards = $this->player_1_cards;
        } else if ($player == 2) {
            $cards = $this->player_2_cards;
        } else {
            throw new Exception('Invalid player number!');
        }

        if ($cards == null) {
            return $cards;
        } else {
            return explode($this->delim, $cards);
        }
    }

    public function getMat() {
        return explode($this->delim, $this->mat_cards);
    }

    public function getDeck() {
        return explode($this->delim, $this->deck_cards);
    }

    /**
     * Setters
     */
    public function setDeck($cards) {
        $this->deck_cards = implode($cards, $this->delim);
    }

    public function setMat($cards) {
        $this->mat_cards = implode($cards, $this->delim);
    }

    public function setPlayerHand($player, $cards) {
        $hand = implode($cards, $this->delim);
        if ($player == 1) {
            $this->player_1_hand = $hand;
        } else if ($player == 2) {
            $this->player_2_hand = $hand;
        } else {
            throw new Exception('Invalid player number!');
        }
    }

    public function setPlayerCards($player, $cardArr) {
        $cards = implode($cardArr, $this->delim);
        if ($player == 1) {
            $this->player_1_cards = $cards;
        } else if ($player == 2) {
            $this->player_2_cards = $cards;
        } else {
            throw new Exception('Invalid player number!');
        }
    }

    /**
     * Multi-part functions
     */
    public function pullCardFromDeck() {
        $deckArray = $this->getDeck();
        $nextCard = array_pop($deckArray);
        $this->setDeck($deckArray);

        return $nextCard;
    }

    public function addCardToMat($card) {
        $matArray = $this->getMat();
        array_push($matArray, $card);
        $this->setMat($matArray);
    }

    public function resetTrackers() {
        $this->recent_card = null;
        $this->recent_flip = null;
        $this->played_selection = null;
        $this->flipped_selection = null;
    }

    public function getSortedCardsByPlayer($player) {
        $gwang = array();
        $yul = array();
        $tti = array();
        $pi = array();
        $captured = array();

        $cards = $this->getCardsForPlayer($player);

        if ($cards == null) {
            $cards = array();
        }

        foreach ($cards as $card) {
            if (in_array($card, $this->gwang)) {
                $gwang[] = $card;
            } else if (in_array($card, $this->yul)) {
                $yul[] = $card;
            } else if (in_array($card, $this->tti)) {
                $tti[] = $card;
            } else if (in_array($card, $this->pi)) {
                $pi[] = $card;
            }
        }

        $captured["gwang"] = $gwang;
        $captured["yul"] = $yul;
        $captured["tti"] = $tti;
        $captured["pi"] = $pi;

        return $captured;
    }

    // Remove card from array
    private function removeCard($card, $arr) {
        $key = array_search($card, $arr);
        array_splice($arr, $key, 1);

        return $arr;
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
}
