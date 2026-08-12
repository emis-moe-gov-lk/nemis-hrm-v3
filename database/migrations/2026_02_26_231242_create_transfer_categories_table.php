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
        Schema::create('transfer_categories', function (Blueprint $table) {
            $table->id();

            $table->string('transfer_category_id', 20);
            $table->string('policy_id', 20);

            $table->char('office_level_id', 10);
            $table->string('transfer_owner_workplace_id', 10);

            $table->string('transfer_category_name');
            $table->text('description')->nullable();

            $table->boolean('active_status')
                ->default(true)
                ->comment('true: Active, false: Inactive');

            $table->timestamps();

            $table->unique(
                'transfer_category_id',
                'transfer_categories_id_unique'
            );

            $table->foreign(
                'policy_id',
                'transfer_categories_policy_fk'
            )
                ->references('policy_id')
                ->on('transfer_policies')
                ->cascadeOnDelete();

            $table->foreign(
                'office_level_id',
                'transfer_categories_office_level_fk'
            )
                ->references('office_level_id')
                ->on('office_levels');

            $table->foreign(
                'transfer_owner_workplace_id',
                'transfer_categories_owner_workplace_fk'
            )
                ->references('workplace_id')
                ->on('workplaces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_categories');
    }
};
