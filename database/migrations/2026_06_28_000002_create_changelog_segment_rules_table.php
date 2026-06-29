<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_segment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
            $table->string('attribute', 100);
            $table->enum('operator', [
                'equals', 'not_equals',
                'contains', 'starts_with', 'ends_with',
                'greater_than', 'less_than',
                'before', 'after',
                'in', 'not_in',
                'exists', 'not_exists',
            ]);
            $table->json('value')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index('changelog_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_segment_rules');
    }
};
