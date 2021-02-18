<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrekuensiNgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frekuensi_ngram', function (Blueprint $table) {
            $table->increments('id');

            $table->string('gram_1')->nullable();
            $table->string('gram_2')->nullable();
            $table->string('gram_3')->nullable();
            $table->unsignedInteger('frekuensi')->default(0);

            $table->unique(['gram_1', 'gram_2', 'gram_3']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frekuensi_ngram');
    }
}
