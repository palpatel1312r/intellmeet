<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team_members')) {
            Schema::create('team_members', function (Blueprint $table) {
                $table->id();

                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                $table->string('role')->default('member');
                $table->string('position')->nullable();
                $table->timestamp('joined_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

                $table->timestamps();

                $table->unique(['team_id', 'user_id']);
            });
        } else {
            // Table exists - add any missing columns
            Schema::table('team_members', function (Blueprint $table) {
                if (!Schema::hasColumn('team_members', 'position')) {
                    $table->string('position')->nullable();
                }
                if (!Schema::hasColumn('team_members', 'joined_at')) {
                    $table->timestamp('joined_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};