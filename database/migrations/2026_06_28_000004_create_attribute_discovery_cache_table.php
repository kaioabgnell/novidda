<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_discovery_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_path', 100);
            $table->string('detected_type', 20);
            $table->unsignedInteger('coverage_count')->default(0);
            $table->json('sample_values')->nullable();
            $table->timestamp('updated_at');

            $table->unique(['account_id', 'attribute_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_discovery_cache');
    }
};
