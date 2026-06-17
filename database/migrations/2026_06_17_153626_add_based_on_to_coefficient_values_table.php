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
        Schema::table('coefficient_values', function (Blueprint $table) {
            $table->string('based_on')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('coefficient_values', function (Blueprint $table) {
            $table->dropColumn('based_on');
        });
    }
};
