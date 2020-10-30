<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDgkimFivebrightMatches extends Migration
{
    public function up()
    {
        Schema::create('dgkim_fivebright_matches', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('player_1_id');
            $table->integer('player_2_id')->nullable();
            $table->integer('starting_score');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('dgkim_fivebright_matches');
    }
}
