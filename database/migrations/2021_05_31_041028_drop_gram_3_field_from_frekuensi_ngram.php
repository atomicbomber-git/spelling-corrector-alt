<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropGram3FieldFromFrekuensiNgram extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frekuensi_ngram', function (Blueprint $table) {
            $table->dropUnique(['gram_1', 'gram_2', 'gram_3']);
            $table->dropColumn(['gram_3']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frekuensi_ngram', function (Blueprint $table) {
            $table->string('gram_3')->nullable();
            $table->unique(['gram_1', 'gram_2', 'gram_3']);
        });
    }
}
