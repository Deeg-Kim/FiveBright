<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames7 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->string('recent_card')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->dropColumn('recent_card');
        });
    }
}
