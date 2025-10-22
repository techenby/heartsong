<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->renameColumn('name', 'grade');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->renameColumn('grade', 'name');
        });
    }
};
