<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table de suivi des articles importés depuis les sources externes
 * Semantic Scholar | OpenAlex | arXiv | Unpaywall
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Lien vers la publication UMMISCO (null = pas encore importée)
            $table->foreignUuid('publication_id')
                  ->nullable()
                  ->constrained('publications')
                  ->nullOnDelete();

            // Source d'origine
            $table->string('source', 30);        // semantic_scholar | openalex | arxiv

            // Identifiant unique dans la source externe
            $table->string('external_id', 255);

            // DOI (pivot pour Unpaywall + déduplication)
            $table->string('doi', 255)->nullable();

            // URL PDF gratuit (fourni par Unpaywall ou la source)
            $table->string('pdf_url', 1000)->nullable();

            // Données brutes de l'API (JSONB sous PostgreSQL)
            $table->json('raw_data');

            // Métadonnées normalisées pour affichage rapide
            $table->text('titre')->nullable();
            $table->text('resume')->nullable();
            $table->text('auteurs')->nullable();   // JSON stringified array
            $table->string('journal', 500)->nullable();
            $table->string('annee', 4)->nullable();
            $table->string('type_publication', 50)->nullable();  // article, thesis, preprint...

            // Statut d'import
            $table->string('statut', 20)->default('disponible');
            // disponible   = récupéré de l'API, affiché sur le portail
            // importe      = inséré dans publications (avec publication_id)
            // ignore       = marqué à ignorer

            $table->timestamp('fetched_at')->nullable();  // dernier fetch API
            $table->timestamps();

            // Déduplication : un même article ne peut être importé 2x depuis la même source
            $table->unique(['source', 'external_id']);
            $table->index('doi');
            $table->index('statut');
            $table->index('source');
            $table->index('annee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_publications');
    }
};
