<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['books', 'items', 'banknotes', 'coins', 'magazines', 'newspapers', 'postcards', 'stamps'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->index('for_sale');
                $t->index('created_at');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex("{$table}_for_sale_index");
                $t->dropIndex("{$table}_created_at_index");
            });
        }
    }
};
