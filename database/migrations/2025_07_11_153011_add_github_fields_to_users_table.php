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
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_id')->nullable()->unique()->after('email');
            $table->string('github_username')->nullable()->after('github_id');
            $table->text('github_token')->nullable()->after('github_username');
            $table->string('avatar_url')->nullable()->after('github_token');
            $table->text('bio')->nullable()->after('avatar_url');
            $table->string('location')->nullable()->after('bio');
            $table->string('website')->nullable()->after('location');
            $table->boolean('is_active')->default(true)->after('website');

            // 添加索引（github_id 已经是 unique，不需要额外索引）
            $table->index('github_username');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 删除索引（注意 github_id 是 unique 索引）
            $table->dropUnique(['github_id']);
            $table->dropIndex(['github_username']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'github_id',
                'github_username',
                'github_token',
                'avatar_url',
                'bio',
                'location',
                'website',
                'is_active'
            ]);
        });
    }
};
