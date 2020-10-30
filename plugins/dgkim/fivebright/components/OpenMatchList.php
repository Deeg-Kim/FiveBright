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

class OpenMatchList extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Open Match List',
            'description' => 'Listing of open matches.'
        ];
    }

    public function onRun()
    {
        $matches = Match::where("player_2_id", null)
            ->join("users", "dgkim_fivebright_matches.player_1_id", "=", "users.id")
            ->join("dgkim_fivebright_jeomsu", "users.email", "=", "dgkim_fivebright_jeomsu.player_email")
            ->get();

        $this->page['matches'] = $matches;
    }
}
