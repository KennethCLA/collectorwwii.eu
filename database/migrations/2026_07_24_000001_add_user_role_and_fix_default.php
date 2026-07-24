<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');

        if (! $userRoleId) {
            $userRoleId = DB::table('roles')->insertGetId([
                'name'       => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::statement("ALTER TABLE users ALTER COLUMN role_id SET DEFAULT {$userRoleId}");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN role_id SET DEFAULT 1');

        DB::table('roles')->where('name', 'user')->delete();
    }
};
