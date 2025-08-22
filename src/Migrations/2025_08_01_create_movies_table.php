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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('external_service');
            $table->unsignedBigInteger('external_id');
            $table->string('title');
            $table->string('original_title');
            $table->string('original_language')->nullable();
            $table->text('overview')->nullable();
            $table->date('release_date')->nullable();
            $table->string('poster_url')->nullable();
            $table->timestamps();

            $table->unique(['external_service', 'external_id'], 'service_id_unique');
            $table->index('title', 'movies_title_index');
            $table->index('original_title', 'movies_original_title_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
