<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kata', function (Blueprint $table) {
            $table->string('isi');
            $table->primary('isi');
        });

        DB::unprepared("CREATE INDEX IF NOT EXISTS kata_isi_gin ON kata USING gin(isi gin_trgm_ops)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kata');
    }
}
