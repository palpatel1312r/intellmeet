<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // This creates notifiable_type and notifiable_id columns
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }


    

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
