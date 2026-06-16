<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('url')->nullable();
            $table->text('description_courte')->nullable();
            $table->timestamps();
        });

        Schema::create('membres_ummisco', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('role')->nullable();
            $table->string('url_profil')->nullable();
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres_ummisco');
        Schema::dropIfExists('centres');
    }
};
