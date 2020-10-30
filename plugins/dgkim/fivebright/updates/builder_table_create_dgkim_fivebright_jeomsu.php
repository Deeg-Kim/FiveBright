<?php namespace DGKim\Fivebright\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDgkimFivebrightJeomsu extends Migration
{
    public function up()
    {
        Schema::create('dgkim_fivebright_jeomsu', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('player_id');
            $table->integer('jeomsu');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('dgkim_fivebright_jeomsu');
    }
}
