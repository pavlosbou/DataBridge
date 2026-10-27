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
        Schema::create('dataset_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('original_filename',255);
            $table->string('storage_path',255);
            $table->string('status',30)->default('uploaded');
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('valid_rows')->nullable();
            $table->unsignedInteger('invalid_rows')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_imports');
    }
};
