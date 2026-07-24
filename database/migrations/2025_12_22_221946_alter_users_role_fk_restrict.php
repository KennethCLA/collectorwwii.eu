<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop bestaande FK
            $table->dropForeign(['role_id']);

            // Recreate met RESTRICT (in Laravel: noActionOnDelete)
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Terug naar CASCADE zoals het nu is
            $table->dropForeign(['role_id']);

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }
};
