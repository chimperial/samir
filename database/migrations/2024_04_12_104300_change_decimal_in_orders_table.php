<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 30, 12)->change();
            $table->decimal('avg_price', 30, 12)->change();
            $table->decimal('orig_qty', 30, 12)->change();
            $table->decimal('executed_qty', 30, 12)->change();
            $table->decimal('cum_qty', 30, 12)->change();
            $table->decimal('cum_quote', 30, 12)->change();
            $table->decimal('stop_price', 30, 12)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('price')->change();
            $table->float('avg_price')->change();
            $table->float('orig_qty')->change();
            $table->float('executed_qty')->change();
            $table->float('cum_qty')->change();
            $table->float('cum_quote')->change();
            $table->float('stop_price')->change();
        });
    }
}; 