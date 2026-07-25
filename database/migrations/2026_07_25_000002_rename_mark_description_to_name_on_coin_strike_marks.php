<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coin_strike_marks', function (Blueprint $table) {
            $table->renameColumn('mark_description', 'name');
        });
    }

    public function down(): void
    {
        Schema::table('coin_strike_marks', function (Blueprint $table) {
            $table->renameColumn('name', 'mark_description');
        });
    }
};
