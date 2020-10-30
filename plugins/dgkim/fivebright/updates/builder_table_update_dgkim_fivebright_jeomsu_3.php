<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightJeomsu3 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
