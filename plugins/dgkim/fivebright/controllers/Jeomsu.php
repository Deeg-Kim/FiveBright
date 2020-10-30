<?php namespace DGKim\Fivebright\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Jeomsu extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('DGKim.Fivebright', 'main-menu-item-fb', 'side-menu-item-jeomsu');
    }
}
