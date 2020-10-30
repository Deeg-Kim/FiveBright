<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames4 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->text('player_2_hand');
            $table->text('deck_cards');
            $table->text('player_1_cards');
            $table->text('player_2_cards');
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->dropColumn('player_2_hand');
            $table->dropColumn('deck_cards');
            $table->dropColumn('player_1_cards');
            $table->dropColumn('player_2_cards');
        });
    }
}
