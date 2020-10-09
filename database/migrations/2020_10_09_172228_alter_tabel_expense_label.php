<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTabelExpenseLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('expense_label', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
            $table->dropColumn('expense_id');
            $table->integer('item_id');
            $table->string('item_model');
            $table->unique([
                'item_id',
                'item_model',
                'label_id',
            ]);
        });

        Schema::rename('expense_label', 'item_label');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('item_label', function (Blueprint $table) {
            $table->dropUnique([
                'item_id',
                'item_model',
                'label_id',
            ]);

            $table->dropColumn('item_id');
            $table->dropColumn('item_model');
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
        });

        Schema::rename('item_label', 'expense_label');
    }
}
