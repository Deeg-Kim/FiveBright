<?php namespace DGKim\Fivebright\Models;

use Model;

/**
 * Model
 */
class Match extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'dgkim_fivebright_matches';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
