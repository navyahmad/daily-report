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
        Schema::create('hrd_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->foreignId('created_by')->nullable()->constrained('admin_hrd_users')->nullOnDelete();
            $table->boolean('include_employee')->default(false);
            $table->boolean('include_office')->default(false);
            $table->json('employee_entries')->nullable();
            $table->json('office_findings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrd_reports');
    }
};
