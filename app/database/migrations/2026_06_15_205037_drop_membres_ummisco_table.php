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
        Schema::dropIfExists('membres_ummisco');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('membres_ummisco', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('url_profil')->nullable();
            $table->foreignId('centre_id')->nullable()->constrained('centres')->nullOnDelete();
            $table->timestamps();
        });
    }
};
