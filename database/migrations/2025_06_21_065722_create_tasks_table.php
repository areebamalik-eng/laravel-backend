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
    Schema::create('tasks', function (Blueprint $table) {
        
        $table->id();                 // Primary key
        $table->string('name');       // Task name field
        $table->boolean('is_done')->default(false);   // Task complete ya nahi
        $table->date('due_date')->nullable();         // Task ki last date
        $table->time('due_time')->nullable(); 

        $table->timestamps();         // created_at, updated_at
        $table->softDeletes();        // deleted_at (for soft delete)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
