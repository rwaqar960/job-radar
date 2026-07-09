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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('external_id');
            $table->string('title');
            $table->string('company');
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->string('url');
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
