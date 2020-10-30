<?php namespace DGKim\Fivebright\Components;

use Auth;
use Input;
use Validator;
use Redirect;
use Flash;
use Session;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use DGKim\Fivebright\Models\Match;
use RainLab\User\Models\User;

class PlayMatch extends ComponentBase
{

    public function onRefresh()
    {
        $id = $this->param('id');
        $match = Match::where('id', $id)->first();

        $playerOne = User::where('id', $match->player_1_id)->first();
        $playerTwo = User::where('id', $match->player_2_id)->first();

        return [
            "#game" => $this->renderPartial('playMatch::game', [
                    "player1" => $playerOne,
                    "player2" => $playerTwo
            ])
        ];
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
        }
    }
}
