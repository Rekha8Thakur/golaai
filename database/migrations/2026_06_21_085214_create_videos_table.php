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
        Schema::create('videos', function (Blueprint $table) {
            $table->string('video_id')->primary();
            $table->string('title');
            $table->string('thumbnail_url')->nullable();
            $table->longText('transcript');
            $table->longText('summary');
            $table->longText('notes');
            $table->longText('qa');
            $table->longText('mcqs');
            $table->longText('action_items');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
