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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->date('report_date')->index();
            $table->string('email');
            $table->string('employee_name_snapshot');
            $table->string('division_name_snapshot');
            $table->string('division_code_snapshot');
            $table->unsignedSmallInteger('form_version')->default(1);
            $table->string('status')->default('active')->index(); // active, cancelled
            $table->json('form_data');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['employee_id', 'report_date']);
            $table->index(['report_date', 'division_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
