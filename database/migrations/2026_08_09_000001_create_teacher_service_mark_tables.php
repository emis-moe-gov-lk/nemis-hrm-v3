<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('province_service_marks', function (Blueprint $table) {
            $table->id();
            $table->string('province_id');
            $table->decimal('mark_per_year', 10, 4);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('active_status')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('province_id')->references('province_id')->on('provinces_lists')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['province_id', 'active_status', 'effective_from'], 'psm_lookup_idx');
        });

        Schema::create('school_service_marks', function (Blueprint $table) {
            $table->id();
            $table->string('workplace_id');
            $table->decimal('mark_per_year', 10, 4);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('active_status')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('workplace_id')->references('workplace_id')->on('institutions')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['workplace_id', 'active_status', 'effective_from'], 'ssm_lookup_idx');
        });

        Schema::create('teacher_service_mark_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('processing_province_id');
            $table->date('calculation_date');
            $table->unsignedInteger('outside_province_months')->default(0);
            $table->unsignedInteger('within_province_months')->default(0);
            $table->decimal('outside_province_score', 14, 4)->default(0);
            $table->decimal('within_province_score', 14, 4)->default(0);
            $table->decimal('total_score', 14, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('people_id')->on('people')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('processing_province_id')->references('province_id')->on('provinces_lists')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['employee_id', 'calculation_date'], 'tsmc_employee_date_idx');
        });

        Schema::create('teacher_service_mark_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculation_id')->constrained('teacher_service_mark_calculations')->cascadeOnDelete();
            $table->foreignId('workplace_history_id')->nullable()->constrained('employer_appointment_workplace_histories')->nullOnDelete();
            $table->string('appointment_id')->nullable();
            $table->string('workplace_id');
            $table->string('workplace_name');
            $table->string('province_id');
            $table->string('province_name');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('completed_months');
            $table->enum('mark_type', ['province', 'school']);
            $table->unsignedBigInteger('mark_source_id');
            $table->decimal('mark_snapshot', 10, 4);
            $table->decimal('score', 14, 4);
            $table->timestamps();
            $table->index(['calculation_id', 'mark_type'], 'tsml_calc_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_service_mark_lines');
        Schema::dropIfExists('teacher_service_mark_calculations');
        Schema::dropIfExists('school_service_marks');
        Schema::dropIfExists('province_service_marks');
    }
};

