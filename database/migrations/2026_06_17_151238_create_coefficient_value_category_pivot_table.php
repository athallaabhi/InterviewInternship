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
        Schema::create('coefficient_value_category_pivot', function (Blueprint $table) {
            $table->foreignId('coefficient_value_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_value_id')->constrained()->cascadeOnDelete();
            $table->primary(['coefficient_value_id', 'category_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coefficient_value_category_pivot');
    }
};
