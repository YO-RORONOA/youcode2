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
        Schema::table('presentiel_tests', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('staff_id')->constrained('test_groups')->nullOnDelete();
            $table->enum('test_type', ['cme', 'technical', 'administrative'])->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
