<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'transfer_board_member_attendances',
            function (Blueprint $table) {
                $table->id();

                // Relations
                $table->string('tbm_id', 20);

                // Attendance information
                $table->date('attendance_date');

                $table->enum(
                    'attendance_status',
                    ['present', 'absent', 'late']
                )->default('present');

                $table->text('remarks')->nullable();

                // Status
                $table->boolean('active_status')
                    ->default(true)
                    ->comment('true: Active, false: Inactive');

                // Audit
                $table->string('created_by', 12)->nullable();
                $table->string('updated_by', 12)->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Indexes and Unique Constraints
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'tbm_id',
                    'trbma_tbm_idx'
                );

                $table->index(
                    'attendance_date',
                    'trbma_attendance_date_idx'
                );

                $table->unique(
                    ['tbm_id', 'attendance_date'],
                    'trbma_member_date_unique'
                );

                /*
                |--------------------------------------------------------------------------
                | Foreign Keys
                |--------------------------------------------------------------------------
                */

                $table->foreign(
                    'tbm_id',
                    'trbma_member_fk'
                )
                    ->references('tbm_id')
                    ->on('transfer_board_members')
                    ->cascadeOnDelete();

                $table->foreign(
                    'created_by',
                    'trbma_created_by_fk'
                )
                    ->references('people_id')
                    ->on('people')
                    ->nullOnDelete();

                $table->foreign(
                    'updated_by',
                    'trbma_updated_by_fk'
                )
                    ->references('people_id')
                    ->on('people')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'transfer_board_member_attendances'
        );
    }
};
