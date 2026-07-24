<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            // drop de 3 CASCADE lookups
            $table->dropForeign(['country_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['nominal_value_id']);

            // recreate als RESTRICT/NO ACTION
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->noActionOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->noActionOnDelete();

            $table->foreign('nominal_value_id')
                ->references('id')
                ->on('nominal_values')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            // rollback naar CASCADE zoals het nu in je schema zit
            $table->dropForeign(['country_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['nominal_value_id']);

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->cascadeOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->cascadeOnDelete();

            $table->foreign('nominal_value_id')
                ->references('id')
                ->on('nominal_values')
                ->cascadeOnDelete();
        });
    }
};
