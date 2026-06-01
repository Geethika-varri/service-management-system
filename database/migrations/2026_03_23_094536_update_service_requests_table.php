<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {

            $table->renameColumn('technician_id', 'assigned_to');

            $table->enum('priority', ['low', 'medium', 'high'])
                  ->default('medium')
                  ->after('description');

            $table->timestamp('assigned_at')->nullable()->after('assigned_to');

            $table->integer('sla_hours')->nullable()->after('completed_at');

            $table->boolean('is_sla_breached')->default(false)->after('sla_hours');

            // FOREIGN KEY
            $table->foreign('assigned_to')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // INDEX
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {

            $table->renameColumn('assigned_to', 'technician_id');

            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);

            $table->dropColumn([
                'priority',
                'assigned_at',
                'sla_hours',
                'is_sla_breached'
            ]);
        });
    }
};