<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDgkimFivebrightGames extends Migration
{
    public function up()
    {
        Schema::create('dgkim_fivebright_games', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('match_id');
            $table->integer('next_turn')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('dgkim_fivebright_games');
    }
}
