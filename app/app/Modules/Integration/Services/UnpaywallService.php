<?php

namespace App\Modules\Integration\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * UnpaywallService
 * API gratuite — https://api.unpaywall.org/v2/{doi}
 * Récupère le lien PDF gratuit pour un DOI donné
 * Limite : 100k req/jour
 */
class UnpaywallService
{
    private const BASE_URL = 'https://api.unpaywall.org/v2';

    private string $email;

    public function __construct()
    {
        // L'email est obligatoire pour l'API Unpaywall
        $this->email = config('services.unpaywall.email', 'contact@ummisco.sn');
    }

    /**
     * Récupère l'URL du PDF gratuit pour un DOI
     *
     * @param  string  $doi  DOI de l'article (ex: 10.1126/science.1157996)
     * @return string|null   URL du PDF en accès libre, ou null
     */
    public function getPdfUrl(string $doi): ?string
    {
        if (empty($doi)) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL . '/' . urlencode($doi), [
                    'email' => $this->email,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            // Meilleure source OA selon Unpaywall
            $bestOa = $data['best_oa_location'] ?? null;
            if ($bestOa && !empty($bestOa['url_for_pdf'])) {
                return $bestOa['url_for_pdf'];
            }

            // Cherche dans toutes les locations OA
            foreach ($data['oa_locations'] ?? [] as $location) {
                if (!empty($location['url_for_pdf'])) {
                    return $location['url_for_pdf'];
                }
                if (!empty($location['url'])) {
                    return $location['url'];
                }
            }

            return null;

        } catch (ConnectionException $e) {
            Log::warning('[Unpaywall] Impossible de contacter l\'API', [
                'doi'   => $doi,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Enrichit une liste d'articles avec les URLs PDF via Unpaywall
     * Respecte un délai entre les requêtes pour éviter le rate limiting
     *
     * @param  array  $articles  Articles avec champ 'doi'
     * @return array             Articles enrichis avec champ 'pdf_url'
     */
    public function enrichWithPdfUrls(array $articles): array
    {
        foreach ($articles as &$article) {
            // Déjà un PDF direct
            if (!empty($article['pdf_url'])) {
                continue;
            }

            // Pas de DOI = pas de PDF via Unpaywall
            if (empty($article['doi'])) {
                continue;
            }

            $pdfUrl = $this->getPdfUrl($article['doi']);
            if ($pdfUrl) {
                $article['pdf_url'] = $pdfUrl;
            }

            // Pause légère pour respecter le rate limit Unpaywall
            usleep(100000); // 100ms
        }

        return $articles;
    }
}
