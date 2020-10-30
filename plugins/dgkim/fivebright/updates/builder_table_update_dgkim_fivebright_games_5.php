<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames5 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->text('mat_cards')->nullable()->change();
            $table->text('player_1_hand')->nullable()->change();
            $table->text('player_2_hand')->nullable()->change();
            $table->text('deck_cards')->nullable()->change();
            $table->text('player_1_cards')->nullable()->change();
            $table->text('player_2_cards')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->text('mat_cards')->nullable(false)->change();
            $table->text('player_1_hand')->nullable(false)->change();
            $table->text('player_2_hand')->nullable(false)->change();
            $table->text('deck_cards')->nullable(false)->change();
            $table->text('player_1_cards')->nullable(false)->change();
            $table->text('player_2_cards')->nullable(false)->change();
        });
    }
}
