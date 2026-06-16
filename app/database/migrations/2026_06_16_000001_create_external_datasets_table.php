<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table external_datasets
 *
 * Stocke les métadonnées de datasets scientifiques récupérées depuis DataCite
 * (et autres sources futures). Équivalent de external_publications pour les datasets.
 *
 * Déduplication par DOI (unique).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_datasets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            // Source de données
            $table->string('source', 30)->default('datacite');    // datacite | zenodo | dryad | figshare
            $table->string('external_id', 500)->nullable();       // ID interne à la source
            $table->string('doi', 255)->nullable()->unique();     // DOI — clé de déduplication principale

            // Métadonnées descriptives
            $table->text('titre')->nullable();
            $table->text('resume')->nullable();
            $table->text('auteurs')->nullable();                   // JSON encodé
            $table->string('annee', 4)->nullable();
            $table->string('type_dataset', 50)->nullable();        // dataset | software | image | ...
            $table->string('licence', 100)->nullable();            // CC BY 4.0, CC0, ...
            $table->bigInteger('taille_octets')->nullable();
            $table->jsonb('formats')->nullable();                  // ['CSV','NetCDF',...]
            $table->string('lien_acces', 1000)->nullable();        // URL d'accès au dataset
            $table->string('editeur', 500)->nullable();            // ex: Zenodo, Dryad
            $table->string('version', 50)->nullable();

            // Données brutes complètes (pour ré-import futur)
            $table->jsonb('raw_data')->nullable();

            // Gestion
            $table->string('statut', 20)->default('disponible');   // disponible | archive | supprime
            $table->timestamp('fetched_at')->nullable();

            $table->timestamps();

            // Index
            $table->index('source');
            $table->index('annee');
            $table->index('statut');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_datasets');
    }
};
