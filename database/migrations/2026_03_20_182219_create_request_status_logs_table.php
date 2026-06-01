<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('request_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete()
                ->index();

            $table->enum('old_status', ['pending','assigned','in_progress','completed']);
            $table->enum('new_status', ['pending','assigned','in_progress','completed']);

            $table->foreignId('changed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status_logs');
    }
};
