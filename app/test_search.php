<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/publications', 'GET', ['q' => 'quantum physics']);
$response = $kernel->handle($request);
if ($response->getStatusCode() === 200) {
    echo "Status 200 OK\n";
    $html = $response->getContent();
    if (strpos($html, 'quantum') !== false || strpos($html, 'publication(s) trouvée(s)') !== false || strpos($html, 'trouvé') !== false) {
        echo "Valid HTML output generated.\n";
    } else {
        echo "Unexpected output.\n";
    }
} else {
    echo "Status code: " . $response->getStatusCode() . "\n";
    echo $response->getContent();
}
