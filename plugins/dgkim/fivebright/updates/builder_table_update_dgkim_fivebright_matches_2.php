<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightMatches2 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_matches', function($table)
        {
            $table->integer('last_winner')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_matches', function($table)
        {
            $table->dropColumn('last_winner');
        });
    }
}
