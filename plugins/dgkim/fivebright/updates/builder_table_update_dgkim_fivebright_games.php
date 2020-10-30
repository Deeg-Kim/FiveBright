<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightGames extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_games', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
