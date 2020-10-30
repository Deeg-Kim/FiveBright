<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightMatches extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_matches', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
            $table->renameColumn('starting_score', 'num_games');
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_matches', function($table)
        {
            $table->dropColumn('deleted_at');
            $table->renameColumn('num_games', 'starting_score');
        });
    }
}
