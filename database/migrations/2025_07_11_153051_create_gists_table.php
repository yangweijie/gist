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
        Schema::create('gists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('github_gist_id')->nullable()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content');
            $table->string('language')->default('text');
            $table->string('filename')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_synced')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
            $table->timestamps();

            // 添加索引
            $table->index('user_id');
            $table->index('github_gist_id');
            $table->index('language');
            $table->index('is_public');
            $table->index('is_synced');
            $table->index('views_count');
            $table->index('likes_count');
            $table->index('created_at');
            $table->index(['user_id', 'is_public']);
            $table->index(['language', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gists');
    }
};
