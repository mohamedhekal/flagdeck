<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedTinyInteger('percentage')->nullable();
            $table->json('environments')->nullable();
            $table->json('include_user_ids')->nullable();
            $table->json('exclude_user_ids')->nullable();
            $table->json('include_tenant_ids')->nullable();
            $table->json('exclude_tenant_ids')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('feature_flag_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_flag_id')->nullable()->constrained('feature_flags')->nullOnDelete();
            $table->string('flag_key');
            $table->string('action', 32);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->nullableMorphs('actor');
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['flag_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flag_audits');
        Schema::dropIfExists('feature_flags');
    }
};
