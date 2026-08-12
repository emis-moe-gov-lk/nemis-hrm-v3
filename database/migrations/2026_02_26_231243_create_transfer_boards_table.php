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
        Schema::create('transfer_boards', function (Blueprint $table) {
            $table->id();

            // Unique board ID
            $table->string('board_id', 20);
            $table->unique('board_id', 'trb_board_id_unique');

            // Relations
            $table->string('policy_id', 20);
            $table->string('transfer_category_id', 20);

            $table->string('bo_office_level_id', 12)
                ->comment('Board Owner Office Level ID');

            $table->string('bo_workplace_id', 12)
                ->comment('Board Owner Workplace ID');

            // Board details
            $table->string('board_name');
            $table->date('start_date');
            $table->date('end_date');

            // Board roles
            $table->string('chairman_id', 12);
            $table->string('secretary_id', 12);

            // Audit
            $table->string('created_by', 12)->nullable();
            $table->string('updated_by', 12)->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('policy_id', 'trb_policy_fk')
                ->references('policy_id')
                ->on('transfer_policies')
                ->cascadeOnDelete();

            $table->foreign(
                'transfer_category_id',
                'trb_category_fk'
            )
                ->references('transfer_category_id')
                ->on('transfer_categories');

            $table->foreign(
                'bo_office_level_id',
                'trb_office_level_fk'
            )
                ->references('office_level_id')
                ->on('office_levels');

            $table->foreign(
                'bo_workplace_id',
                'trb_workplace_fk'
            )
                ->references('workplace_id')
                ->on('workplaces');

            $table->foreign('chairman_id', 'trb_chairman_fk')
                ->references('people_id')
                ->on('people');

            $table->foreign('secretary_id', 'trb_secretary_fk')
                ->references('people_id')
                ->on('people');

            $table->foreign('created_by', 'trb_created_by_fk')
                ->references('people_id')
                ->on('people')
                ->nullOnDelete();

            $table->foreign('updated_by', 'trb_updated_by_fk')
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
        Schema::dropIfExists('transfer_boards');
    }
};
