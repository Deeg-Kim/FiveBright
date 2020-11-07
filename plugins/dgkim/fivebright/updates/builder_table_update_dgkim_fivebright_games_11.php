<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames11 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->string('player_1_ssa_suits', 255)->nullable();
            $table->string('player_2_ssa_suits', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->dropColumn('player_1_ssa_suits');
            $table->dropColumn('player_2_ssa_suits');
        });
    }
}
