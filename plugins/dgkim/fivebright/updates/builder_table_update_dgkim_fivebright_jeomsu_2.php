<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDgkimFivebrightJeomsu2 extends Migration
{
    public function up()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->string('player_email', 255)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('dgkim_fivebright_jeomsu', function($table)
        {
            $table->text('player_email')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
