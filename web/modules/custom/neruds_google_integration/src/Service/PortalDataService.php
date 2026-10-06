<?php

declare(strict_types=1);

namespace Drupal\neruds_google_integration\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Builds cached public data for NERUDS portal components.
 */
final class PortalDataService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly CacheBackendInterface $cache,
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Returns dashboard metrics with Drupal-first fallbacks.
   */
  public function getDashboard(): array {
    return $this->remember('neruds_google_integration:dashboard', function (): array {
      return [
        'metrics' => [
          [
            'id' => 'researchers',
            'label' => 'Pesquisadores Ativos',
            'value' => $this->countUsers(),
            'href' => '/pesquisadores',
            'color' => '#1E88E5',
            'sparkline' => [8, 9, 11, 11, 12, 12, 13, 14, 14, 14, 14, 14],
          ],
          [
            'id' => 'projects',
            'label' => 'Projetos em Andamento',
            'value' => $this->countNodes(['projeto', 'project']),
            'href' => '/projetos',
            'color' => '#2C5530',
            'sparkline' => [1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 2, 2],
          ],
          [
            'id' => 'publications',
            'label' => 'Publicacoes Indexadas',
            'value' => $this->countNodes(['publicacao', 'publicacao_cientifica', 'reference']),
            'href' => '/publicacoes',
            'color' => '#8B6914',
            'sparkline' => [92, 101, 112, 123, 136, 142, 150, 154, 160, 164, 168, 169],
          ],
          [
            'id' => 'citations',
            'label' => 'Citacoes Totais',
            'value' => $this->getSheetMetric('citations_total', 0),
            'href' => '/publicacoes?sort=citations',
            'color' => '#E74C3C',
            'sparkline' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          ],
          [
            'id' => 'territories',
            'label' => 'Territorios Monitorados',
            'value' => 5,
            'href' => '#territorios',
            'color' => '#4A7C59',
            'sparkline' => [3, 3, 4, 4, 4, 5, 5, 5, 5, 5, 5, 5],
          ],
          [
            'id' => 'ods',
            'label' => 'ODS Alcancados',
            'value' => $this->countTerms(['ods', 'objetivos_de_desenvolvimento_sustentavel']) ?: 8,
            'href' => '/ods',
            'color' => '#9B59B6',
            'sparkline' => [4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 8, 8],
          ],
        ],
        'updated' => gmdate('c'),
        'environment' => $this->selectedEnvironment(),
        'integrations' => [
          'ga4_property_configured' => $this->selectedGa4PropertyId() !== '',
          'google_calendar_configured' => $this->envOrConfig('NERUDS_GOOGLE_CALENDAR_ID', 'google_calendar_id') !== '',
          'search_provider_configured' => $this->hasSearchProvider(),
        ],
      ];
    });
  }

  /**
   * Returns territory GeoJSON enriched with portal counts.
   */
  public function getTerritories(): array {
    return $this->remember('neruds_google_integration:territories', function (): array {
      $path = DRUPAL_ROOT . '/modules/custom/neruds_google_integration/assets/geojson/territories.geojson';
      $geojson = is_readable($path) ? json_decode((string) file_get_contents($path), TRUE) : ['type' => 'FeatureCollection', 'features' => []];
      foreach ($geojson['features'] as &$feature) {
        $slug = $feature['properties']['slug'] ?? '';
        $feature['properties']['counts'] = [
          'projects' => $this->countNodesByTerritory(['projeto', 'project'], $slug),
          'researchers' => $this->countUsers(),
          'publications' => $this->countNodesByTerritory(['publicacao', 'publicacao_cientifica', 'reference'], $slug),
          'events' => $this->countNodesByTerritory($this->eventBundles(), $slug),
        ];
        $feature['properties']['recent'] = $feature['properties']['counts']['publications'] > 0;
      }
      return $geojson;
    });
  }

  /**
   * Returns public calendar events in FullCalendar format.
   */
  public function getCalendarEvents(): array {
    return $this->remember('neruds_google_integration:calendar', function (): array {
      $events = $this->getGoogleCalendarEvents();
      if ($events !== []) {
        return $events;
      }

      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', $this->eventBundles(), 'IN')
        ->sort('created', 'DESC')
        ->range(0, 50);
      $items = [];
      foreach ($storage->loadMultiple($query->execute()) as $node) {
        $items[] = [
          'id' => (string) $node->id(),
          'title' => $node->label(),
          'start' => date('c', (int) $node->getCreatedTime()),
          'url' => $node->toUrl()->toString(),
          'extendedProps' => [
            'territory' => $this->fieldLabel($node, 'field_territorio'),
            'type' => $this->fieldLabel($node, 'field_tipo_evento') ?: 'Evento',
          ],
        ];
      }
      return $items;
    });
  }

  /**
   * Returns cached publication metrics.
   */
  public function getPublicationMetrics(string $query = '', array $filters = []): array {
    $query = trim($query);
    $filters = $this->normalizePublicationFilters($filters);
    $cid = 'neruds_google_integration:publication_metrics:' . hash('sha256', $query . ':' . json_encode($filters));
    return $this->remember($cid, function () use ($query, $filters): array {
      $all = $this->publicationNodes();
      $matched = array_values(array_filter($all, fn (NodeInterface $node): bool => $this->publicationMatchesQuery($node, $query)));
      $filtered = array_values(array_filter($matched, fn (NodeInterface $node): bool => $this->publicationMatchesFilters($node, $filters)));

      return [
        'total' => count($all),
        'matched_total' => count($matched),
        'filtered_total' => count($filtered),
        'open_access' => $this->getSheetMetric('open_access_total', 0),
        'citations_total' => $this->getSheetMetric('citations_total', 0),
        'query' => $query,
        'filters' => $filters,
        'facets' => [
          // Each facet honours every other active filter. Its own selection is
          // deliberately omitted so visitors can see and switch alternatives.
          'territories' => $this->publicationFacet($this->publicationFacetContext($matched, $filters, 'territory'), ['field_territorio']),
          'ods' => $this->publicationFacet($this->publicationFacetContext($matched, $filters, 'ods'), ['field_ods']),
          'types' => $this->publicationFacet($this->publicationFacetContext($matched, $filters, 'type'), ['field_tipo_publicacao', 'field_tipo_producao']),
          'years' => $this->publicationYearFacet($this->publicationFacetContext($matched, $filters, 'year')),
        ],
        'updated' => gmdate('c'),
      ];
    });
  }

  /**
   * Returns metadata for a public Google Drive folder.
   */
  public function getDriveFolder(string $folderInput): array {
    $folderId = $this->extractDriveFolderId($folderInput);
    if ($folderId === '') {
      return [
        'folderId' => '',
        'files' => [],
        'notice' => 'Invalid Google Drive folder id or URL.',
      ];
    }

    $cid = 'neruds_google_integration:drive_folder:' . hash('sha256', $folderId);
    return $this->remember($cid, function () use ($folderId): array {
      $key = $this->envOrConfig('NERUDS_GOOGLE_DRIVE_API_KEY', 'google_drive_api_key');
      if ($key === '') {
        $key = $this->envOrConfig('NERUDS_GOOGLE_CALENDAR_API_KEY', 'google_calendar_api_key');
      }
      $accessToken = $this->driveAccessToken();

      if ($key === '' && $accessToken === '') {
        return [
          'folderId' => $folderId,
          'files' => [],
          'folderUrl' => 'https://drive.google.com/drive/folders/' . rawurlencode($folderId),
          'notice' => 'Google Drive credentials are not configured.',
        ];
      }

      $lastRequestFailed = FALSE;
      try {
        $files = [];
        if ($accessToken !== '') {
          $files = $this->requestDriveFiles($folderId, $accessToken, '', $lastRequestFailed);
        }

        // Fallback for public folders when service-account visibility is empty.
        if ($files === [] && $key !== '') {
          $files = $this->requestDriveFiles($folderId, '', $key, $lastRequestFailed);
        }

        $response = [
          'folderId' => $folderId,
          'folderUrl' => 'https://drive.google.com/drive/folders/' . rawurlencode($folderId),
          'files' => $files,
          'updated' => gmdate('c'),
        ];
        if ($files === []) {
          $response['notice'] = $lastRequestFailed
            ? 'No files were returned. Confirm the folder has files and is shared with the configured service account or API key.'
            : 'No public files were found in this folder yet.';
        }

        return $response;
      }
      catch (\Throwable $exception) {
        $this->logger->warning('Google Drive request failed. Check folder sharing, credentials, API enablement, quota, and referrer restrictions.');
        return [
          'folderId' => $folderId,
          'folderUrl' => 'https://drive.google.com/drive/folders/' . rawurlencode($folderId),
          'files' => [],
          'notice' => 'Google Drive request failed.',
        ];
      }
    });
  }

  /**
   * Returns operational readiness for forum moderation queue.
   */
  public function getForumModerationStatus(): array {
    return $this->remember('neruds_google_integration:forum_moderation', function (): array {
      return [
        'moderation_enforced' => TRUE,
        'queue' => [
          'pending' => $this->countGroupForumCommentsByStatus(0),
          'published' => $this->countGroupForumCommentsByStatus(1),
        ],
        'approval_url' => '/admin/content/comment/approval',
        'updated' => gmdate('c'),
      ];
    });
  }

  /**
   * Compatibility endpoint used by legacy reflection routes.
   */
  public function getLastAiReflection(): array {
    return $this->remember('neruds_google_integration:ai_reflection:last', function (): array {
      try {
        $audit = \Drupal::service('neruds_google_integration.audit');
        $result = $audit->citations([
          'project' => '',
          'sigilo' => '',
          'q' => '',
          'limit' => 1,
          'offset' => 0,
        ]);
        $item = $result['items'][0] ?? [];
        $content = is_array($item['conteudo'] ?? NULL) ? $item['conteudo'] : [];
        $insight = $content['ai_insight'] ?? NULL;
        if (is_array($insight) && $insight !== []) {
          return $insight;
        }
      }
      catch (\Throwable $exception) {
        $this->logger->warning('Unable to read AI reflection snapshot: @message', [
          '@message' => $exception->getMessage(),
        ]);
      }

      return ['message' => 'No AI insights found yet.'];
    });
  }

  /**
   * Returns search-readiness diagnostics for Vertex/CSE operations.
   */
  public function getSearchReadiness(): array {
    $config = $this->configFactory->get('neruds_google_integration.settings');
    $scope = getenv('NERUDS_SITE_SEARCH_SCOPE') ?: ($config->get('site_search_scope') ?: 'neruds.org/publicacoes');

    return $this->remember('neruds_google_integration:search_readiness', function () use ($scope): array {
      $servingConfig = $this->envOrConfig('NERUDS_VERTEX_SEARCH_SERVING_CONFIG', 'vertex_search_serving_config');
      $token = $this->vertexAccessToken();
      $cx = $this->envOrConfig('NERUDS_GOOGLE_SEARCH_CX', 'google_programmable_search_cx');

      $vertexProbe = ['items' => []];
      if ($servingConfig !== '' && $token !== '') {
        $vertexProbe = $this->searchVertexAi($servingConfig, $token, 'territorio', 1);
      }

      $previewCount = count($vertexProbe['items'] ?? []);
      $totalSize = (int) ($vertexProbe['totalSize'] ?? 0);
      $connected = (bool) ($vertexProbe['vertex_connected'] ?? FALSE);

      return [
        'vertex' => [
          'serving_config_present' => $servingConfig !== '',
          'token_present' => $token !== '',
          'connected' => $connected,
          'indexed_results_preview' => $previewCount,
          'total_size' => $totalSize,
        ],
        'google_cse' => [
          'cx_present' => $cx !== '',
        ],
        'site_scope' => $scope,
        'recommended_provider' => ($connected && ($previewCount > 0 || $totalSize > 0)) ? 'vertex_ai_search' : ($cx !== '' ? 'google_cse_widget_with_drupal_metrics' : 'drupal_editorial_fallback'),
        'next_steps' => [
          'Share source data to the Vertex data store and run ingest.',
          'Retest this endpoint until indexed_results_preview is greater than 0.',
          'Keep Drupal metrics/facets as canonical counts for publication discovery.',
        ],
        'updated' => gmdate('c'),
      ];
    });
  }

  /**
   * Queries Google Drive folder files with either bearer token or API key.
   */
  private function requestDriveFiles(string $folderId, string $accessToken, string $apiKey, bool &$requestFailed): array {
    try {
      $query = [
        'q' => "'" . $folderId . "' in parents and trashed = false",
        'fields' => 'files(id,name,mimeType,webViewLink,webContentLink,modifiedTime,size,iconLink)',
        'orderBy' => 'folder,name',
        'pageSize' => 50,
        'supportsAllDrives' => 'true',
        'includeItemsFromAllDrives' => 'true',
      ];

      $headers = $this->googleRequestHeaders();
      if ($accessToken !== '') {
        $headers['Authorization'] = 'Bearer ' . $accessToken;
      }
      elseif ($apiKey !== '') {
        $query['key'] = $apiKey;
      }

      $response = $this->httpClient->request('GET', 'https://www.googleapis.com/drive/v3/files', [
        'headers' => $headers,
        'query' => $query,
        'timeout' => 6,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      return $this->normalizeDriveFiles($data['files'] ?? []);
    }
    catch (\Throwable) {
      $requestFailed = TRUE;
      return [];
    }
  }

  /**
   * Proxies the public discovery layer with Drupal metrics.
   *
   * The legacy Google Custom Search JSON API is closed for new customers and
   * should not be the default path. Programmatic Google search should use
   * Vertex AI Search / Discovery Engine with OAuth or service-account based
   * credentials. The CSE widget remains useful as a public embedded UI.
   */
  public function googleSearch(string $query, int $start = 1, array $filters = []): array {
    $query = trim($query);
    $start = max(1, $start);
    $filters = $this->normalizePublicationFilters($filters);
    $config = $this->configFactory->get('neruds_google_integration.settings');
    $vertexServingConfig = $this->envOrConfig('NERUDS_VERTEX_SEARCH_SERVING_CONFIG', 'vertex_search_serving_config');
    $vertexAccessToken = $this->vertexAccessToken();
    $key = $this->envSecret('NERUDS_GOOGLE_SEARCH_KEY');
    $cx = $this->envOrConfig('NERUDS_GOOGLE_SEARCH_CX', 'google_programmable_search_cx');
    $scope = getenv('NERUDS_SITE_SEARCH_SCOPE') ?: ($config->get('site_search_scope') ?: 'neruds.org/publicacoes');
    $serperKey = $this->envSecret('NERUDS_SERPER_API_KEY');
    $serpapiKey = $this->envSecret('NERUDS_SERPAPI_API_KEY');
    $legacyCseJson = $this->envFlag('NERUDS_ENABLE_LEGACY_CSE_JSON', FALSE);
    $thirdPartyFallback = $this->envFlag('NERUDS_ENABLE_THIRD_PARTY_SEARCH_FALLBACK', FALSE);

    if ($query === '') {
      return [
        'items' => [],
        'metrics' => $this->getPublicationMetrics('', $filters),
        'notice' => 'Search query is empty.',
      ];
    }

    $cid = 'neruds_google_integration:google_search:' . hash('sha256', implode(':', [
      $query,
      (string) $start,
      $vertexServingConfig !== '' && $vertexAccessToken !== '' ? 'vertex' : '',
      $cx,
      $scope,
      $legacyCseJson ? 'legacy-cse-json' : 'no-legacy-cse-json',
      $thirdPartyFallback ? 'third-party' : 'google-widget',
      json_encode($filters),
    ]));
    return $this->remember($cid, function () use ($vertexServingConfig, $vertexAccessToken, $key, $cx, $serperKey, $serpapiKey, $legacyCseJson, $thirdPartyFallback, $query, $start, $scope, $filters): array {
      // Taxonomy facets are applied by Drupal. External providers receive only
      // the text query and therefore cannot reliably return the selected
      // territory, ODS, type, or year. Keep cards and facet counts aligned.
      if ($this->hasPublicationFilters($filters)) {
        $fallback = $this->searchDrupalPublications($query, $filters, $start, 10);
        return $this->withSearchMetadata([
          'items' => $fallback['items'],
          'provider' => 'drupal_editorial_filtered',
          'cse_cx' => $cx,
          'notice' => ($fallback['items'] ?? []) === []
            ? 'Nenhuma publicação editorial corresponde aos filtros selecionados.'
            : 'Resultados filtrados pelo acervo editorial do NERUDS.',
          'searchInformation' => [
            'totalResults' => (string) ($fallback['total'] ?? 0),
          ],
        ], $query, $scope, $filters);
      }

      $providerNotice = '';

      if ($vertexServingConfig !== '' && $vertexAccessToken !== '') {
        $result = $this->searchVertexAi($vertexServingConfig, $vertexAccessToken, $query, $start);
        if (($result['items'] ?? []) !== []) {
          return $this->withSearchMetadata($result + ['provider' => 'vertex_ai_search'], $query, $scope, $filters);
        }
        if (($result['vertex_connected'] ?? FALSE) === TRUE) {
          $providerNotice = $result['notice'] ?? 'Vertex AI Search is connected, but the data store returned no indexed results yet.';
        }
      }

      if ($legacyCseJson && $this->looksLikeGoogleApiKey($key) && $cx !== '') {
        $result = $this->searchGoogleProgrammable($key, $cx, $query, $start, $scope);
        if (($result['items'] ?? []) !== []) {
          return $this->withSearchMetadata($result + ['provider' => 'legacy_google_custom_search_json'], $query, $scope, $filters);
        }
      }

      if ($cx !== '' && !$thirdPartyFallback) {
        $fallback = $this->searchDrupalPublications($query, $filters, $start, 10);
        if (($fallback['items'] ?? []) !== []) {
          $notice = 'Showing Drupal editorial fallback results while Google widget is available.';
          if ($providerNotice !== '') {
            $notice = $providerNotice . ' ' . $notice;
          }
          return $this->withSearchMetadata([
            'items' => $fallback['items'],
            'provider' => 'drupal_editorial_fallback',
            'cse_cx' => $cx,
            'notice' => $notice,
            'searchInformation' => [
              'totalResults' => (string) ($fallback['total'] ?? 0),
            ],
          ], $query, $scope, $filters);
        }

        return $this->withSearchMetadata([
          'items' => [],
          'provider' => 'google_cse_widget',
          'cse_cx' => $cx,
          'notice' => $providerNotice !== '' ? $providerNotice . ' Use the embedded Google Programmable Search widget.' : 'Use the embedded Google Programmable Search widget.',
        ], $query, $scope, $filters);
      }

      if ($serperKey !== '') {
        $result = $this->searchSerper($serperKey, $query, $start, $scope);
        if (($result['items'] ?? []) !== []) {
          return $this->withSearchMetadata($result + ['provider' => 'serper'], $query, $scope, $filters);
        }
      }

      if ($serpapiKey !== '') {
        $result = $this->searchSerpApi($serpapiKey, $query, $start, $scope);
        if (($result['items'] ?? []) !== []) {
          return $this->withSearchMetadata($result + ['provider' => 'serpapi'], $query, $scope, $filters);
        }
      }

      $fallback = $this->searchDrupalPublications($query, $filters, $start, 10);
      if (($fallback['items'] ?? []) !== []) {
        $notice = 'Showing Drupal editorial fallback results.';
        if ($providerNotice !== '') {
          $notice = $providerNotice . ' ' . $notice;
        }
        return $this->withSearchMetadata([
          'items' => $fallback['items'],
          'provider' => 'drupal_editorial_fallback',
          'cse_cx' => $cx,
          'notice' => $notice,
          'searchInformation' => [
            'totalResults' => (string) ($fallback['total'] ?? 0),
          ],
        ], $query, $scope, $filters);
      }

      return $this->withSearchMetadata([
        'items' => [],
        'provider' => $cx !== '' ? 'google_cse_widget' : NULL,
        'cse_cx' => $cx,
        'notice' => $cx !== '' ? 'Use the embedded Google Programmable Search widget.' : 'No search provider is configured or no result was returned.',
      ], $query, $scope, $filters);
    });
  }

  /**
   * Searches scientific literature using Elsevier Scopus API.
   */
  public function scientificSearch(string $query, int $start = 0, int $count = 10): array {
    $query = trim($query);
    $start = max(0, $start);
    $count = min(25, max(1, $count));

    if ($query === '') {
      return [
        'items' => [],
        'provider' => 'elsevier_scopus_api',
        'query' => '',
        'start' => $start,
        'count' => $count,
        'search_total' => 0,
        'notice' => 'Search query is empty.',
      ];
    }

    $apiKey = $this->envSecret('NERUDS_ELSEVIER_API_KEY');
    if ($apiKey === '') {
      return [
        'items' => [],
        'provider' => 'elsevier_scopus_api',
        'query' => $query,
        'start' => $start,
        'count' => $count,
        'search_total' => 0,
        'notice' => 'Elsevier API key is not configured.',
      ];
    }

    $cid = 'neruds_google_integration:scientific_search:' . hash('sha256', implode(':', [
      $query,
      (string) $start,
      (string) $count,
    ]));

    return $this->remember($cid, function () use ($apiKey, $query, $start, $count): array {
      try {
        $response = $this->httpClient->request('GET', 'https://api.elsevier.com/content/search/scopus', [
          'headers' => [
            'X-ELS-APIKey' => $apiKey,
            'Accept' => 'application/json',
          ],
          'query' => [
            'query' => $query,
            'start' => $start,
            'count' => $count,
            'sort' => '-coverDate',
            'view' => 'STANDARD',
          ],
          'timeout' => 8,
        ]);

        $data = json_decode((string) $response->getBody(), TRUE) ?: [];
        $results = $data['search-results'] ?? [];
        $entries = is_array($results['entry'] ?? NULL) ? $results['entry'] : [];
        $items = [];
        foreach ($entries as $entry) {
          if (!is_array($entry)) {
            continue;
          }
          $title = trim((string) ($entry['dc:title'] ?? ''));
          $doi = trim((string) ($entry['prism:doi'] ?? ''));
          $eid = trim((string) ($entry['eid'] ?? ''));
          $authors = trim((string) ($entry['dc:creator'] ?? ''));
          $journal = trim((string) ($entry['prism:publicationName'] ?? ''));
          $publishedAt = trim((string) ($entry['prism:coverDate'] ?? ''));
          $citedBy = (int) ($entry['citedby-count'] ?? 0);

          $landingUrl = '';
          if ($doi !== '') {
            $landingUrl = 'https://doi.org/' . $doi;
          }
          elseif ($eid !== '') {
            $landingUrl = 'https://www.scopus.com/record/display.uri?eid=' . rawurlencode($eid) . '&origin=resultslist';
          }
          else {
            foreach ($entry['link'] ?? [] as $link) {
              if (is_array($link) && ($link['@ref'] ?? '') === 'scopus') {
                $landingUrl = (string) ($link['@href'] ?? '');
                break;
              }
            }
          }

          $snippetParts = array_values(array_filter([
            $authors,
            $journal,
            $publishedAt,
            $citedBy > 0 ? 'Cited by ' . $citedBy : '',
          ]));

          $items[] = [
            'title' => $title !== '' ? $title : 'Scientific result',
            'link' => $landingUrl,
            'snippet' => implode(' | ', $snippetParts),
            'displayLink' => 'scopus.com',
            'doi' => $doi,
            'eid' => $eid,
            'authors' => $authors,
            'journal' => $journal,
            'published_at' => $publishedAt,
            'cited_by' => $citedBy,
          ];
        }

        return [
          'items' => $items,
          'provider' => 'elsevier_scopus_api',
          'query' => $query,
          'start' => $start,
          'count' => $count,
          'search_total' => (int) ($results['opensearch:totalResults'] ?? count($items)),
          'updated' => gmdate('c'),
        ];
      }
      catch (\Throwable) {
        $this->logger->warning('Elsevier Scopus request failed. Check API key, quota, and network access.');
        return [
          'items' => [],
          'provider' => 'elsevier_scopus_api',
          'query' => $query,
          'start' => $start,
          'count' => $count,
          'search_total' => 0,
          'notice' => 'Scientific search request failed.',
        ];
      }
    });
  }

  private function looksLikeGoogleApiKey(string $key): bool {
    return str_starts_with($key, 'AIza') && strlen($key) > 30;
  }

  private function searchVertexAi(string $servingConfig, string $accessToken, string $query, int $start): array {
    try {
      $servingConfig = trim($servingConfig, '/');
      $response = $this->httpClient->request('POST', 'https://discoveryengine.googleapis.com/v1/' . $servingConfig . ':search', [
        'headers' => [
          'Authorization' => 'Bearer ' . $accessToken,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'query' => $query,
          'pageSize' => 10,
          'offset' => max(0, $start - 1),
          'languageCode' => 'pt-BR',
          'userInfo' => [
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'NERUDS Drupal',
          ],
          'params' => [
            'user_country_code' => 'br',
          ],
        ],
        'timeout' => 8,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      return [
        'items' => $this->normalizeVertexItems($data['results'] ?? []),
        'attributionToken' => $data['attributionToken'] ?? NULL,
        'totalSize' => isset($data['totalSize']) ? (int) $data['totalSize'] : NULL,
        'vertex_connected' => TRUE,
      ];
    }
    catch (\Throwable $exception) {
      $this->logger->warning('Vertex AI Search request failed. Check serving config, OAuth token, quota, billing, and site search index status.');
      return ['items' => []];
    }
  }

  private function searchGoogleProgrammable(string $key, string $cx, string $query, int $start, string $scope): array {
    try {
      $response = $this->httpClient->request('GET', 'https://www.googleapis.com/customsearch/v1', [
        'headers' => $this->googleRequestHeaders(),
        'query' => [
          'key' => $key,
          'cx' => $cx,
          'q' => $query,
          'siteSearch' => $scope,
          'siteSearchFilter' => 'i',
          'start' => max(1, $start),
          'hl' => 'pt-BR',
        ],
        'timeout' => 6,
      ]);
      return json_decode((string) $response->getBody(), TRUE) ?: ['items' => []];
    }
    catch (\Throwable $exception) {
      $this->logger->warning('Google Programmable Search request failed. Check API key, CX, quota, and referrer restrictions.');
      return ['items' => []];
    }
  }

  private function searchSerper(string $key, string $query, int $start, string $scope): array {
    try {
      $response = $this->httpClient->request('POST', 'https://google.serper.dev/search', [
        'headers' => [
          'X-API-KEY' => $key,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'q' => 'site:' . $scope . ' ' . $query,
          'gl' => 'br',
          'hl' => 'pt-br',
          'page' => max(1, (int) ceil(max(1, $start) / 10)),
          'num' => 10,
        ],
        'timeout' => 6,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      return [
        'items' => $this->normalizeOrganicItems($data['organic'] ?? []),
        'searchInformation' => [
          'totalResults' => (string) ($data['searchInformation']['totalResults'] ?? 0),
        ],
      ];
    }
    catch (\Throwable $exception) {
      $this->logger->warning('Serper request failed. Check API key, quota, and request policy.');
      return ['items' => []];
    }
  }

  private function searchSerpApi(string $key, string $query, int $start, string $scope): array {
    try {
      $response = $this->httpClient->request('GET', 'https://serpapi.com/search.json', [
        'query' => [
          'engine' => 'google',
          'api_key' => $key,
          'q' => 'site:' . $scope . ' ' . $query,
          'google_domain' => 'google.com.br',
          'hl' => 'pt-br',
          'gl' => 'br',
          'start' => max(0, $start - 1),
          'num' => 10,
        ],
        'timeout' => 6,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      return [
        'items' => $this->normalizeOrganicItems($data['organic_results'] ?? []),
        'searchInformation' => [
          'totalResults' => (string) ($data['search_information']['total_results'] ?? 0),
        ],
      ];
    }
    catch (\Throwable $exception) {
      $this->logger->warning('SerpAPI request failed. Check API key, quota, and request policy.');
      return ['items' => []];
    }
  }

  private function normalizeOrganicItems(array $items): array {
    $normalized = [];
    foreach ($items as $item) {
      $link = $item['link'] ?? '';
      if ($link === '') {
        continue;
      }
      $normalized[] = [
        'title' => $item['title'] ?? $link,
        'link' => $link,
        'snippet' => $item['snippet'] ?? $item['description'] ?? '',
        'displayLink' => parse_url($link, PHP_URL_HOST) ?: '',
      ];
    }
    return $normalized;
  }

  private function normalizeVertexItems(array $results): array {
    $normalized = [];
    foreach ($results as $result) {
      $document = $result['document'] ?? [];
      $derived = is_array($document['derivedStructData'] ?? NULL) ? $document['derivedStructData'] : [];
      $structured = is_array($document['structData'] ?? NULL) ? $document['structData'] : [];
      $link = (string) ($derived['link'] ?? $derived['url'] ?? $structured['link'] ?? $structured['url'] ?? '');
      if ($link === '') {
        continue;
      }
      $title = (string) ($derived['title'] ?? $structured['title'] ?? $link);
      $normalized[] = [
        'title' => $title,
        'link' => $link,
        'snippet' => $this->vertexSnippet($derived),
        'displayLink' => parse_url($link, PHP_URL_HOST) ?: '',
      ];
    }
    return $normalized;
  }

  /**
   * Provides a resilient Drupal-side fallback while external search warms up.
   */
  private function searchDrupalPublications(string $query, array $filters, int $start = 1, int $limit = 10): array {
    $filters = $this->normalizePublicationFilters($filters);
    $nodes = array_values(array_filter($this->publicationNodes(), function (NodeInterface $node) use ($query, $filters): bool {
      return $this->publicationMatchesQuery($node, $query) && $this->publicationMatchesFilters($node, $filters);
    }));

    $total = count($nodes);
    $offset = max(0, $start - 1);
    $slice = array_slice($nodes, $offset, max(1, $limit));
    $items = [];
    foreach ($slice as $node) {
      $link = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
      $snippet = $this->drupalPublicationSnippet($node);
      $items[] = [
        'title' => $node->label(),
        'link' => $link,
        'snippet' => $snippet,
        'displayLink' => parse_url($link, PHP_URL_HOST) ?: '',
      ];
    }

    return [
      'items' => $items,
      'total' => $total,
    ];
  }

  /**
   * Reports whether a request contains a Drupal-owned publication facet.
   */
  private function hasPublicationFilters(array $filters): bool {
    foreach (['territory', 'ods', 'type', 'year'] as $key) {
      if (($filters[$key] ?? '') !== '') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns the matching nodes that provide the count context for one facet.
   */
  private function publicationFacetContext(array $nodes, array $filters, string $facet): array {
    $contextFilters = $filters;
    $contextFilters[$facet] = '';
    return array_values(array_filter($nodes, fn (NodeInterface $node): bool => $this->publicationMatchesFilters($node, $contextFilters)));
  }

  private function drupalPublicationSnippet(NodeInterface $node): string {
    $candidates = ['field_resumo_publicacao', 'body', 'field_descricao'];
    foreach ($candidates as $fieldName) {
      if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
        continue;
      }
      $text = trim(strip_tags($this->fieldValuesAsText($node, [$fieldName])));
      if ($text !== '') {
        return mb_substr($text, 0, 260);
      }
    }

    return 'Publicacao encontrada no acervo editorial do NERUDS.';
  }

  private function withSearchMetadata(array $result, string $query, string $scope, array $filters): array {
    $result['query'] = trim($query);
    $result['scope'] = $scope;
    $result['search_total'] = $this->searchTotal($result);
    $result['metrics'] = $this->getPublicationMetrics($query, $filters);
    return $result;
  }

  private function searchTotal(array $result): ?int {
    if (isset($result['totalSize'])) {
      return (int) $result['totalSize'];
    }
    if (isset($result['searchInformation']['totalResults'])) {
      return (int) $result['searchInformation']['totalResults'];
    }
    if (isset($result['search_information']['total_results'])) {
      return (int) $result['search_information']['total_results'];
    }
    return NULL;
  }

  private function vertexSnippet(array $derived): string {
    foreach (['snippets', 'extractive_answers', 'extractive_segments'] as $key) {
      if (empty($derived[$key]) || !is_array($derived[$key])) {
        continue;
      }
      $first = reset($derived[$key]);
      if (is_array($first)) {
        return (string) ($first['snippet'] ?? $first['content'] ?? $first['pageContent'] ?? '');
      }
      if (is_string($first)) {
        return $first;
      }
    }
    return (string) ($derived['description'] ?? '');
  }

  private function normalizeDriveFiles(array $files): array {
    $normalized = [];
    foreach ($files as $file) {
      $normalized[] = [
        'id' => (string) ($file['id'] ?? ''),
        'name' => (string) ($file['name'] ?? 'Documento'),
        'mimeType' => (string) ($file['mimeType'] ?? ''),
        'url' => (string) ($file['webViewLink'] ?? ''),
        'downloadUrl' => (string) ($file['webContentLink'] ?? ''),
        'modifiedTime' => (string) ($file['modifiedTime'] ?? ''),
        'size' => isset($file['size']) ? (int) $file['size'] : NULL,
        'icon' => (string) ($file['iconLink'] ?? ''),
      ];
    }
    return $normalized;
  }

  private function countGroupForumCommentsByStatus(int $status): int {
    try {
      $groupBundles = $this->existingNodeBundles(['grupo_estudos', 'grupo_estudo']);
      $groupNids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $groupBundles, 'IN')
        ->execute();
      if ($groupNids === []) {
        return 0;
      }

      return (int) $this->entityTypeManager->getStorage('comment')->getQuery()
        ->accessCheck(FALSE)
        ->condition('entity_type', 'node')
        ->condition('field_name', 'comment')
        ->condition('status', $status)
        ->condition('entity_id', array_values($groupNids), 'IN')
        ->count()
        ->execute();
    }
    catch (\Throwable) {
      return 0;
    }
  }

  private function extractDriveFolderId(string $input): string {
    $input = trim($input);
    if ($input === '') {
      return '';
    }

    if (preg_match('/^[A-Za-z0-9_-]{10,}$/', $input)) {
      return $input;
    }

    $parts = parse_url($input);
    if (($parts['host'] ?? '') !== '' && !str_ends_with((string) $parts['host'], 'drive.google.com')) {
      return '';
    }

    if (preg_match('~/folders/([A-Za-z0-9_-]{10,})~', $input, $matches)) {
      return $matches[1];
    }

    parse_str($parts['query'] ?? '', $query);
    if (!empty($query['id']) && is_string($query['id']) && preg_match('/^[A-Za-z0-9_-]{10,}$/', $query['id'])) {
      return $query['id'];
    }

    return '';
  }

  private function remember(string $cid, callable $callback): array {
    if ($cached = $this->cache->get($cid)) {
      return $cached->data;
    }
    $data = $callback();
    $ttl = (int) ($this->configFactory->get('neruds_google_integration.settings')->get('cache_ttl') ?: 300);
    $this->cache->set($cid, $data, time() + $ttl, ['neruds_google_integration']);
    return $data;
  }

  private function countNodes(array $bundles): int {
    try {
      return (int) $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', $bundles, 'IN')
        ->count()
        ->execute();
    }
    catch (\Throwable) {
      return 0;
    }
  }

  private function countNodesByTerritory(array $bundles, string $territorySlug): int {
    if ($territorySlug === '') {
      return 0;
    }
    try {
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', $bundles, 'IN');
      // Field existence varies between staging/prod; use title fallback safely.
      $query->condition('title', str_replace('-', ' ', $territorySlug), 'CONTAINS');
      return (int) $query->count()->execute();
    }
    catch (\Throwable) {
      return 0;
    }
  }

  private function countTerms(array $vocabularies): int {
    try {
      return (int) $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
        ->accessCheck(TRUE)
        ->condition('vid', $vocabularies, 'IN')
        ->count()
        ->execute();
    }
    catch (\Throwable) {
      return 0;
    }
  }

  private function countUsers(): int {
    try {
      return (int) $this->entityTypeManager->getStorage('user')->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', 1)
        ->condition('uid', 0, '>')
        ->count()
        ->execute();
    }
    catch (\Throwable) {
      return 14;
    }
  }

  /**
   * Loads published publication nodes for small cached discovery summaries.
   *
   * The current corpus is small, and this PHP pass is more resilient than
   * brittle SQL while staging/prod field schemas are still being normalized.
   */
  private function publicationNodes(): array {
    $bundles = $this->existingNodeBundles(['publicacao', 'publicacao_cientifica', 'reference']);
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $ids = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', $bundles, 'IN')
        ->sort('created', 'DESC')
        ->execute();
      return array_values($storage->loadMultiple($ids));
    }
    catch (\Throwable) {
      return [];
    }
  }

  private function publicationMatchesQuery(NodeInterface $node, string $query): bool {
    $query = trim($query);
    if ($query === '') {
      return TRUE;
    }
    $haystack = $this->normalizeDiscoveryText($this->publicationSearchText($node));
    foreach (preg_split('/\s+/', $this->normalizeDiscoveryText($query)) ?: [] as $term) {
      if ($term !== '' && !str_contains($haystack, $term)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  private function publicationMatchesFilters(NodeInterface $node, array $filters): bool {
    if ($filters['territory'] !== '' && !$this->publicationHasFacetValue($node, ['field_territorio'], $filters['territory'])) {
      return FALSE;
    }
    if ($filters['ods'] !== '' && !$this->publicationHasFacetValue($node, ['field_ods'], $filters['ods'])) {
      return FALSE;
    }
    if ($filters['type'] !== '' && !$this->publicationHasFacetValue($node, ['field_tipo_publicacao', 'field_tipo_producao'], $filters['type'])) {
      return FALSE;
    }
    if ($filters['year'] !== '' && $this->publicationYear($node) !== $filters['year']) {
      return FALSE;
    }
    return TRUE;
  }

  private function publicationSearchText(NodeInterface $node): string {
    $fields = [
      'field_titulo_publicacao',
      'field_resumo_publicacao',
      'field_autores',
      'field_doi',
      'field_revista_veiculo',
      'field_ano_publicacao',
      'field_territorio',
      'field_ods',
      'field_linha_pesquisa',
      'field_linhas_pesquisa',
      'field_tipo_publicacao',
      'field_tipo_producao',
      'field_palavras_chave',
      'field_tags',
    ];
    return trim($node->label() . ' ' . $this->fieldValuesAsText($node, $fields));
  }

  private function fieldValuesAsText(NodeInterface $node, array $fieldNames): string {
    $values = [];
    foreach ($fieldNames as $fieldName) {
      if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
        continue;
      }
      foreach ($node->get($fieldName) as $item) {
        try {
          if (!empty($item->entity)) {
            $values[] = $item->entity->label();
            continue;
          }
          foreach (['value', 'title', 'uri', 'target_id'] as $property) {
            if (isset($item->{$property}) && (string) $item->{$property} !== '') {
              $values[] = (string) $item->{$property};
              break;
            }
          }
        }
        catch (\Throwable) {
          continue;
        }
      }
    }
    return implode(' ', $values);
  }

  private function publicationFacet(array $nodes, array $fieldNames): array {
    $counts = [];
    $labels = [];
    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }
      foreach (array_unique($this->publicationFacetValues($node, $fieldNames)) as $label) {
        $key = $this->slug($label);
        if ($key === '') {
          continue;
        }
        $labels[$key] = $label;
        $counts[$key] = ($counts[$key] ?? 0) + 1;
      }
    }
    arsort($counts);
    $facet = [];
    foreach (array_slice($counts, 0, 20, TRUE) as $key => $count) {
      $facet[] = [
        'label' => $labels[$key],
        'value' => $key,
        'count' => $count,
      ];
    }
    return $facet;
  }

  private function publicationYearFacet(array $nodes): array {
    $counts = [];
    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }
      $year = $this->publicationYear($node);
      if ($year !== '') {
        $counts[$year] = ($counts[$year] ?? 0) + 1;
      }
    }
    krsort($counts);
    $facet = [];
    foreach ($counts as $year => $count) {
      $facet[] = [
        'label' => $year,
        'value' => $year,
        'count' => $count,
      ];
    }
    return $facet;
  }

  private function publicationFacetValues(NodeInterface $node, array $fieldNames): array {
    $values = [];
    foreach ($fieldNames as $fieldName) {
      if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
        continue;
      }
      foreach ($node->get($fieldName) as $item) {
        try {
          if (!empty($item->entity)) {
            $values[] = $item->entity->label();
          }
          elseif (isset($item->value) && (string) $item->value !== '') {
            $values[] = (string) $item->value;
          }
        }
        catch (\Throwable) {
          continue;
        }
      }
    }
    if (in_array('field_territorio', $fieldNames, TRUE)) {
      $values = array_merge($values, $this->inferredTerritoryValues($node));
    }
    if (in_array('field_ods', $fieldNames, TRUE)) {
      $values = array_merge($values, $this->inferredOdsValues($node));
    }
    if (array_intersect($fieldNames, ['field_tipo_publicacao', 'field_tipo_producao']) !== []) {
      $values = array_merge($values, $this->inferredPublicationTypeValues($node));
    }
    return $values;
  }

  private function inferredTerritoryValues(NodeInterface $node): array {
    $text = $this->slug($this->publicationSearchText($node));
    $territories = [
      'Jalapão' => ['jalapao'],
      'Bico do Papagaio' => ['bico do papagaio', 'bico papagaio'],
      'Cantão' => ['cantao'],
      'Lago de Palmas' => ['lago de palmas'],
      'Chapada dos Parecis' => ['chapada dos parecis'],
      'Cerrado' => ['cerrado'],
      'Amazônia' => ['amazonia', 'amazonica', 'amazonico'],
    ];

    $values = [];
    foreach ($territories as $label => $needles) {
      foreach ($needles as $needle) {
        if (str_contains($text, $this->slug($needle))) {
          $values[] = $label;
          break;
        }
      }
    }
    return $values;
  }

  private function inferredOdsValues(NodeInterface $node): array {
    $text = $this->normalizeDiscoveryText($this->publicationSearchText($node));
    if (!preg_match_all('/\b(?:ods|sdg)\s*0?([1-9]|1[0-7])\b/u', $text, $matches)) {
      return [];
    }

    $values = [];
    foreach (array_unique($matches[1]) as $number) {
      $values[] = 'ODS ' . (int) $number;
    }
    return $values;
  }

  private function inferredPublicationTypeValues(NodeInterface $node): array {
    return match ($node->bundle()) {
      'publicacao_cientifica' => ['Publicação científica'],
      'publicacao' => ['Publicação'],
      default => [],
    };
  }

  private function publicationHasFacetValue(NodeInterface $node, array $fieldNames, string $needle): bool {
    $needle = $this->slug($needle);
    foreach ($this->publicationFacetValues($node, $fieldNames) as $value) {
      $slug = $this->slug($value);
      if ($slug === $needle || str_contains($slug, $needle) || str_contains($needle, $slug)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function publicationYear(NodeInterface $node): string {
    if ($node->hasField('field_ano_publicacao') && !$node->get('field_ano_publicacao')->isEmpty()) {
      return (string) $node->get('field_ano_publicacao')->value;
    }
    return date('Y', (int) $node->getCreatedTime());
  }

  private function normalizePublicationFilters(array $filters): array {
    return [
      'territory' => $this->slug((string) ($filters['territory'] ?? '')),
      'ods' => $this->slug((string) ($filters['ods'] ?? '')),
      'type' => $this->slug((string) ($filters['type'] ?? '')),
      'year' => preg_replace('/[^0-9]/', '', (string) ($filters['year'] ?? '')) ?: '',
    ];
  }

  private function normalizeDiscoveryText(string $value): string {
    $value = mb_strtolower($value);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    return trim($value . ' ' . (is_string($ascii) ? mb_strtolower($ascii) : ''));
  }

  private function slug(string $value): string {
    $value = mb_strtolower($value);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($ascii) && trim($ascii) !== '') {
      $value = mb_strtolower($ascii);
    }
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
    return trim($value, '-');
  }

  private function eventBundles(): array {
    return $this->existingNodeBundles(['evento', 'event', 'evento_cientifico', 'reuniao']);
  }

  private function existingNodeBundles(array $candidateBundles): array {
    $bundleInfo = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $existing = array_values(array_intersect($candidateBundles, array_keys($bundleInfo)));
    return $existing !== [] ? $existing : $candidateBundles;
  }

  private function getGoogleCalendarEvents(): array {
    $key = $this->envOrConfig('NERUDS_GOOGLE_CALENDAR_API_KEY', 'google_calendar_api_key');
    $calendarId = $this->envOrConfig('NERUDS_GOOGLE_CALENDAR_ID', 'google_calendar_id');
    $accessToken = $this->calendarAccessToken();
    if ($calendarId === '' || ($key === '' && $accessToken === '')) {
      return [];
    }
    try {
      $query = [
        'singleEvents' => 'true',
        'orderBy' => 'startTime',
        'timeMin' => gmdate('c', strtotime('-30 days')),
        'maxResults' => 100,
      ];
      $headers = $this->googleRequestHeaders();
      if ($accessToken !== '') {
        $headers['Authorization'] = 'Bearer ' . $accessToken;
      }
      else {
        $query['key'] = $key;
      }
      $response = $this->httpClient->request('GET', 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendarId) . '/events', [
        'headers' => $headers,
        'query' => $query,
        'timeout' => 6,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      $events = [];
      foreach ($data['items'] ?? [] as $item) {
        $events[] = [
          'id' => $item['id'] ?? '',
          'title' => $item['summary'] ?? 'Evento NERUDS',
          'start' => $item['start']['dateTime'] ?? $item['start']['date'] ?? NULL,
          'end' => $item['end']['dateTime'] ?? $item['end']['date'] ?? NULL,
          'url' => $item['htmlLink'] ?? '',
          'extendedProps' => [
            'location' => $item['location'] ?? '',
            'description' => $item['description'] ?? '',
          ],
        ];
      }
      return $events;
    }
    catch (\Throwable $exception) {
      $this->logger->warning('Google Calendar request failed. Check calendar id, public access, API key, quota, and referrer restrictions.');
      return [];
    }
  }

  private function calendarAccessToken(): string {
    $path = $this->serviceCredentialsPath([
      'NERUDS_GOOGLE_CALENDAR_APPLICATION_CREDENTIALS',
      'NERUDS_GOOGLE_CALENDAR_CREDENTIALS',
    ], '/opt/drupal/private/calendar-key-v2.json');
    return $this->serviceAccountAccessToken($path, 'https://www.googleapis.com/auth/calendar.readonly');
  }

  private function driveAccessToken(): string {
    $path = $this->serviceCredentialsPath([
      'NERUDS_GOOGLE_DRIVE_APPLICATION_CREDENTIALS',
      'NERUDS_GOOGLE_DRIVE_CREDENTIALS',
      'NERUDS_GOOGLE_APPLICATION_CREDENTIALS',
      'GOOGLE_APPLICATION_CREDENTIALS',
    ], '/opt/drupal/private/chatbot-key-v2.json');
    return $this->serviceAccountAccessToken($path, 'https://www.googleapis.com/auth/drive.metadata.readonly');
  }

  private function getSheetMetric(string $key, int $fallback): int {
    // Reserved for a curated public Sheets feed. Keep deterministic until configured.
    return $fallback;
  }

  private function googleRequestHeaders(): array {
    $referer = getenv('NERUDS_GOOGLE_API_REFERER');
    if (is_string($referer) && $referer !== '') {
      return ['Referer' => $referer];
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '' && $host !== '127.0.0.1' && $host !== 'localhost') {
      return ['Referer' => 'https://' . $host];
    }

    return [];
  }

  private function envOrConfig(string $envName, string $configName): string {
    $value = getenv($envName);
    if (is_string($value) && $value !== '') {
      return $value;
    }
    return (string) ($this->configFactory->get('neruds_google_integration.settings')->get($configName) ?: '');
  }

  private function envSecret(string $envName): string {
    $value = getenv($envName);
    if (!is_string($value)) {
      return '';
    }
    return trim($value);
  }

  private function envFlag(string $envName, bool $default = FALSE): bool {
    $value = getenv($envName);
    if (!is_string($value) || trim($value) === '') {
      return $default;
    }
    return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], TRUE);
  }

  private function selectedEnvironment(): string {
    $explicit = getenv('NERUDS_ENVIRONMENT');
    if (is_string($explicit) && in_array($explicit, ['prod', 'production', 'stag', 'staging', 'homolog'], TRUE)) {
      return in_array($explicit, ['prod', 'production'], TRUE) ? 'prod' : 'stag';
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (str_contains($host, 'homolog') || str_contains($host, 'staging')) {
      return 'stag';
    }
    return 'prod';
  }

  private function selectedGa4PropertyId(): string {
    if ($this->selectedEnvironment() === 'stag') {
      $value = $this->envOrConfig('NERUDS_GA4_PROPERTY_ID_STAG', 'ga4_property_id_stag');
      if ($value !== '') {
        return $value;
      }
    }

    $prod = $this->envOrConfig('NERUDS_GA4_PROPERTY_ID_PROD', 'ga4_property_id_prod');
    if ($prod !== '') {
      return $prod;
    }
    return $this->envOrConfig('NERUDS_GA4_PROPERTY_ID', 'ga4_property_id');
  }

  private function hasSearchProvider(): bool {
    return ($this->envOrConfig('NERUDS_VERTEX_SEARCH_SERVING_CONFIG', 'vertex_search_serving_config') !== '' && $this->vertexAccessToken() !== '')
      || $this->envOrConfig('NERUDS_GOOGLE_SEARCH_CX', 'google_programmable_search_cx') !== ''
      || ($this->envFlag('NERUDS_ENABLE_LEGACY_CSE_JSON', FALSE) && $this->envOrConfig('NERUDS_GOOGLE_SEARCH_KEY', 'google_programmable_search_key') !== '')
      || $this->envOrConfig('NERUDS_SERPER_API_KEY', 'serper_api_key') !== ''
      || $this->envOrConfig('NERUDS_SERPAPI_API_KEY', 'serpapi_api_key') !== '';
  }

  private function vertexAccessToken(): string {
    $value = getenv('NERUDS_VERTEX_SEARCH_ACCESS_TOKEN');
    if (is_string($value) && trim($value) !== '') {
      return trim($value);
    }

    return $this->serviceAccountAccessToken($this->serviceAccountCredentialsPath());
  }

  private function serviceAccountCredentialsPath(): string {
    return $this->serviceCredentialsPath([
      'NERUDS_GOOGLE_APPLICATION_CREDENTIALS',
      'GOOGLE_APPLICATION_CREDENTIALS',
    ], '/opt/drupal/private/chatbot-key-v2.json');
  }

  private function serviceCredentialsPath(array $envNames, string $default): string {
    foreach ($envNames as $envName) {
      $value = getenv($envName);
      if (is_string($value) && trim($value) !== '') {
        return trim($value);
      }
    }

    return is_readable($default) ? $default : '';
  }

  private function serviceAccountAccessToken(string $credentialsPath, string $scope = 'https://www.googleapis.com/auth/cloud-platform'): string {
    if ($credentialsPath === '' || !is_readable($credentialsPath)) {
      return '';
    }

    $mtime = @filemtime($credentialsPath) ?: 0;
    $cid = 'neruds_google_integration:service_account_token:' . hash('sha256', $credentialsPath . ':' . $mtime . ':' . $scope);
    if ($cached = $this->cache->get($cid)) {
      return is_string($cached->data) ? $cached->data : '';
    }

    try {
      $credentials = json_decode((string) file_get_contents($credentialsPath), TRUE, flags: JSON_THROW_ON_ERROR);
      $clientEmail = (string) ($credentials['client_email'] ?? '');
      $privateKey = (string) ($credentials['private_key'] ?? '');
      $tokenUri = (string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token');
      if ($clientEmail === '' || $privateKey === '' || $tokenUri === '') {
        return '';
      }

      $now = time();
      $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
      $claims = $this->base64UrlEncode(json_encode([
        'iss' => $clientEmail,
        'scope' => $scope,
        'aud' => $tokenUri,
        'iat' => $now,
        'exp' => $now + 3600,
      ], JSON_THROW_ON_ERROR));
      $unsignedJwt = $header . '.' . $claims;

      $signature = '';
      if (!openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        $this->logger->warning('Unable to sign Google service-account JWT for Vertex AI Search.');
        return '';
      }

      $response = $this->httpClient->request('POST', $tokenUri, [
        'form_params' => [
          'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
          'assertion' => $unsignedJwt . '.' . $this->base64UrlEncode($signature),
        ],
        'timeout' => 8,
      ]);
      $data = json_decode((string) $response->getBody(), TRUE, flags: JSON_THROW_ON_ERROR);
      $token = (string) ($data['access_token'] ?? '');
      if ($token === '') {
        return '';
      }

      $expiresIn = max(60, (int) ($data['expires_in'] ?? 3600));
      $this->cache->set($cid, $token, time() + max(60, $expiresIn - 90), ['neruds_google_integration']);
      return $token;
    }
    catch (\Throwable) {
      $this->logger->warning('Google service-account token request failed. Check credential file, IAM roles, and OAuth token endpoint access.');
      return '';
    }
  }

  private function base64UrlEncode(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }

  private function fieldLabel(NodeInterface $node, string $fieldName): string {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }
    $entity = $node->get($fieldName)->entity;
    return $entity ? $entity->label() : (string) $node->get($fieldName)->value;
  }

}
