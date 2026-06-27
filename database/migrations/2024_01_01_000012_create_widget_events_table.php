<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Eventos brutos de analytics do widget.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changelog_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reader_id', 64)->nullable();
            $table->enum('type', ['open', 'view', 'reaction', 'comment']);
            $table->timestamp('created_at')->nullable();

            $table->index(['account_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_events');
    }
};
