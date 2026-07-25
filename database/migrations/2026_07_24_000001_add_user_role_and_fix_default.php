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
            // role_id === 1 is hardcoded app-wide to mean admin (policies,
            // IsAdmin middleware). On a completely empty roles table this
            // insert would otherwise be the first row and land on id 1,
            // making the non-admin "user" role grant admin access. Reserve
            // id 1 for admin first so "user" can never land there.
            if (DB::table('roles')->count() === 0) {
                DB::table('roles')->insert([
                    'id'         => 1,
                    'name'       => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

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
