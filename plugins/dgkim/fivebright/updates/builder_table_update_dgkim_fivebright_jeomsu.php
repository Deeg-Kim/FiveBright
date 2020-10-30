<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightJeomsu extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->text('player_email');
            $table->dropColumn('player_id');
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->dropColumn('player_email');
            $table->integer('player_id');
        });
    }
}
