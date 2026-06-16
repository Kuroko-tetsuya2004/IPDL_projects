<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('failed_jobs')) {
    Schema::create('failed_jobs', function (Blueprint $table) {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });
    echo "Table failed_jobs créée.\n";
}

if (!Schema::hasTable('documents_administratifs')) {
    Schema::create('documents_administratifs', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
        $table->string('type_document');
        $table->string('reference')->unique();
        $table->jsonb('donnees')->nullable();
        $table->string('file_path')->nullable();
        $table->timestamps();
    });
    echo "Table documents_administratifs créée.\n";
}

echo "Correction de la base de données terminée.\n";
