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
        Schema::create('php_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('gist_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('code');
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->integer('memory_usage_bytes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_successful')->default(false);
            $table->json('metadata')->nullable(); // 额外的元数据
            $table->timestamps();

            // 索引
            $table->index(['user_id', 'created_at']);
            $table->index(['gist_id', 'created_at']);
            $table->index(['is_successful', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('php_execution_logs');
    }
};
