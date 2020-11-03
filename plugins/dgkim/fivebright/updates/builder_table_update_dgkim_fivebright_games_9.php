<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames9 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->string('played_selection', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->dropColumn('played_selection');
        });
    }
}
