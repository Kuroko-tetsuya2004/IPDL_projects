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
        Schema::create('membres_externes_ummisco', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('statut')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->string('url_profil_ummisco', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('centres_ummisco', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('url_externe', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centres_ummisco');
        Schema::dropIfExists('membres_externes_ummisco');
    }
};
