<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 👤 Notification receiver
            $table->string('type'); // e.g., 'task' or 'expense'
            $table->unsignedBigInteger('resource_id'); // task_id or expense_id
            $table->string('message'); // 📨 Notification message
            $table->boolean('is_read')->default(false); // ✅ Unread/read status
            $table->timestamps();

            // Optional: Add foreign key constraint if needed
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
