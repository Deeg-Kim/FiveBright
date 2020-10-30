<?php namespace DGKim\Fivebright;

use Event;
use System\Classes\PluginBase;
use DGKim\Fivebright\Models\Jeomsu;

class Plugin extends PluginBase
{
    public function boot()
    {
        Event::listen('flynsarmy.sociallogin.registerUser', function($provider_details, $user_details) {
            $defaultJeomsu = 10000;

            $jeomsu = new Jeomsu;
            $jeomsu->player_email = $user_details->email;
            $jeomsu->jeomsu = $defaultJeomsu;
            $jeomsu->save();
        });
    }
    
    public function registerComponents()
    {
        return [
            'DGKim\Fivebright\Components\OpenMatchList' => 'openMatchList',
            'DGKim\Fivebright\Components\PlayMatch' => 'playMatch'
        ];
    }

    public function registerSettings()
    {
    }
}
