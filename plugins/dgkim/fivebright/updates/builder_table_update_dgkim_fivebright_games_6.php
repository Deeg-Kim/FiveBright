<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames6 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->string('mat_cards', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('player_1_hand', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('player_2_hand', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('deck_cards', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('player_1_cards', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->string('player_2_cards', 255)->nullable()->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->text('mat_cards')->nullable()->unsigned(false)->default(null)->change();
            $table->text('player_1_hand')->nullable()->unsigned(false)->default(null)->change();
            $table->text('player_2_hand')->nullable()->unsigned(false)->default(null)->change();
            $table->text('deck_cards')->nullable()->unsigned(false)->default(null)->change();
            $table->text('player_1_cards')->nullable()->unsigned(false)->default(null)->change();
            $table->text('player_2_cards')->nullable()->unsigned(false)->default(null)->change();
        });
    }
}
