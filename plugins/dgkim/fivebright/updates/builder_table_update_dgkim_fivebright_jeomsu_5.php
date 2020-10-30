<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightJeomsu5 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->dropColumn('next_turn');
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->integer('next_turn')->default(0);
        });
    }
}
