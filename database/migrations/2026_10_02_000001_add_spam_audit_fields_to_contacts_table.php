<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedTinyInteger('spam_score')->default(0);
            $table->json('spam_reasons')->nullable();
            $table->timestamp('mail_suppressed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['spam_score', 'spam_reasons', 'mail_suppressed_at']);
        });
    }
};
