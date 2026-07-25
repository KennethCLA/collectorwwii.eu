<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every lookup table managed by the generic LookupIndexController is
     * expected to have a "name" column (per its config map, and per every
     * controller/view that queries these tables). These six were created
     * with a different column name and were unreachable through both the
     * admin coin form and the generic lookup admin page as a result.
     */
    private const RENAMES = [
        'coin_front_images' => 'image_path',
        'coin_reverse_images' => 'image_path',
        'coin_front_texts' => 'text',
        'coin_reverse_texts' => 'text',
        'coin_rim_texts' => 'text',
        'coin_rims' => 'rim_type',
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->renameColumn($column, 'name');
            });
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->renameColumn('name', $column);
            });
        }
    }
};
