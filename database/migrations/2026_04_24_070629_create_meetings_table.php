<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('meeting_code')->unique(); // ✅ ADD THIS

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->string('recording_url')->nullable();
            $table->text('transcript')->nullable();
            $table->text('summary')->nullable();
            $table->json('settings')->nullable();

            $table->string('status')->default('scheduled');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
