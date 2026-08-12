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
        Schema::create('transfer_board_subjects', function (Blueprint $table) {
            $table->id();

            $table->string('board_id', 20);
            $table->string('subject_id', 10);

            $table->boolean('active_status')
                ->default(true)
                ->comment('true: Active, false: Inactive');

            $table->string('created_by', 12)->nullable();
            $table->string('updated_by', 12)->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes and Unique Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['board_id', 'subject_id'],
                'trbs_board_subject_unique'
            );

            $table->index(
                ['board_id', 'active_status'],
                'trbs_board_status_index'
            );

            $table->index(
                'subject_id',
                'trbs_subject_index'
            );

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('board_id', 'trbs_board_fk')
                ->references('board_id')
                ->on('transfer_boards')
                ->cascadeOnDelete();

            $table->foreign('subject_id', 'trbs_subject_fk')
                ->references('subject_id')
                ->on('subject_lists');

            $table->foreign('created_by', 'trbs_created_by_fk')
                ->references('people_id')
                ->on('people')
                ->nullOnDelete();

            $table->foreign('updated_by', 'trbs_updated_by_fk')
                ->references('people_id')
                ->on('people')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_board_subjects');
    }
};
