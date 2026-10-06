# NERUDS Google Integration

Custom Drupal 11 module for the first NERUDS territorial portal cycle.

## What It Adds

- Blocks for the live dashboard, Leaflet territory map, territorial timeline, and hybrid publication discovery.
- Cached public endpoints:
  - `/neruds/api/dashboard`
  - `/neruds/api/territories`
  - `/neruds/api/calendar`
  - `/neruds/api/publication-metrics`
  - `/neruds/api/datasets`
  - `/neruds/api/forum-moderation`
  - `/neruds/api/search-readiness`
  - `/neruds/api/audit/citations`
  - `/neruds/api/audit/schema`
  - `/neruds/api/google-search`
- Lightweight page `/publicacoes-discovery` for Google Programmable Search results scoped to NERUDS publications.
- Lightweight page `/datasets-discovery` for BigQuery-backed dataset discovery.
- Base vocabularies and cross-cutting fields when installed.
- Accessibility baseline for LBI/eMAG/WCAG AA: skip link, persistent visitor preferences, visible focus, reduced-motion support, safer live regions, and optional VLibras widget injection.

## Environment Variables

Keep credentials outside exported Drupal YAML:

```bash
NERUDS_GOOGLE_SEARCH_CX=""
NERUDS_VERTEX_SEARCH_SERVING_CONFIG=""
NERUDS_VERTEX_SEARCH_ACCESS_TOKEN=""
NERUDS_GOOGLE_APPLICATION_CREDENTIALS="/opt/drupal/private/chatbot-key-v2.json"
NERUDS_GOOGLE_CALENDAR_API_KEY=""
NERUDS_GOOGLE_CALENDAR_ID=""
NERUDS_GOOGLE_CALENDAR_APPLICATION_CREDENTIALS="/opt/drupal/private/calendar-key-v2.json"
NERUDS_GOOGLE_DRIVE_API_KEY=""
NERUDS_GOOGLE_DRIVE_APPLICATION_CREDENTIALS="/opt/drupal/private/chatbot-key-v2.json"
NERUDS_BIGQUERY_PROJECT_ID="neruds-staging-2026"
NERUDS_BIGQUERY_AUDIT_DATASET="auditoria_citacoes_us"
NERUDS_BIGQUERY_AUDIT_TABLE="citacoes_auditaveis"
NERUDS_BIGQUERY_APPLICATION_CREDENTIALS="/opt/drupal/private/chatbot-key-v2.json"
NERUDS_GOOGLE_API_REFERER="https://homolog.neruds.org"
NERUDS_GOOGLE_API_GEMINI=""
NERUDS_GOOGLE_SHEETS_API_KEY=""
NERUDS_DASHBOARD_SHEET_ID=""
NERUDS_GA4_PROPERTY_ID=""
NERUDS_GA4_PROPERTY_ID_PROD=""
NERUDS_GA4_PROPERTY_ID_STAG=""
NERUDS_GA4_MEASUREMENT_ID_PROD=""
NERUDS_GA4_MEASUREMENT_ID_STAG=""
NERUDS_ENVIRONMENT="staging"
NERUDS_ENABLE_VLIBRAS="1"
NERUDS_SITE_SEARCH_SCOPE="neruds.org/publicacoes"
NERUDS_SERPER_API_KEY=""
NERUDS_SERPAPI_API_KEY=""
NERUDS_ENABLE_LEGACY_CSE_JSON="0"
NERUDS_ENABLE_THIRD_PARTY_SEARCH_FALLBACK="0"
```

Use `NERUDS_GA4_PROPERTY_ID_STAG` for GA4 reporting/API in `homolog.neruds.org` and `NERUDS_GA4_PROPERTY_ID_PROD` for production. Use `NERUDS_GA4_MEASUREMENT_ID_STAG` and `NERUDS_GA4_MEASUREMENT_ID_PROD` for browser tracking; these are the `G-...` IDs.

Search provider order is Vertex AI Search / Discovery Engine, then the embedded Google CSE widget. Serper and SerpAPI are optional third-party fallbacks and are only tried when `NERUDS_ENABLE_THIRD_PARTY_SEARCH_FALLBACK=1`; this keeps staging/prod logs clean when the public CSE widget is enough. The legacy Custom Search JSON API is closed to new customers and should stay disabled (`NERUDS_ENABLE_LEGACY_CSE_JSON=0`) unless an already-entitled Google Cloud project still has access. Vertex AI Search uses the Google Discovery Engine REST API and should be called with an OAuth access token minted from a Google Cloud service account or Application Default Credentials.

Example Vertex serving config shape:

```bash
NERUDS_VERTEX_SEARCH_SERVING_CONFIG="projects/PROJECT_ID/locations/global/collections/default_collection/dataStores/DATA_STORE_ID/servingConfigs/default_serving_config"
```

For service-account deployments, prefer `NERUDS_GOOGLE_APPLICATION_CREDENTIALS` pointing to a JSON file outside the public docroot. The module mints and caches a short-lived OAuth token with the `https://www.googleapis.com/auth/cloud-platform` scope. `NERUDS_VERTEX_SEARCH_ACCESS_TOKEN` remains available only for temporary debugging or external token injection.

For private/shared Google Calendar reads, use `NERUDS_GOOGLE_CALENDAR_APPLICATION_CREDENTIALS` and share the calendar with the calendar service account. Public calendar reads can still use `NERUDS_GOOGLE_CALENDAR_API_KEY`.

For Google Drive folder reads, prefer `NERUDS_GOOGLE_DRIVE_APPLICATION_CREDENTIALS` with a read-only service account and share each folder with that service account. Public folders can still use `NERUDS_GOOGLE_DRIVE_API_KEY`, but service-account reads are the safer default for group documents and curated research folders.

BigQuery audit endpoints read only `publico` and `anonimizado` citation rows from the configured table. Queries use Standard SQL, named parameters, cache, and a 50 MB maximum billed bytes cap.
The audit layer can read the primary audit table plus the secondary audit table configured by `NERUDS_BIGQUERY_SECONDARY_AUDIT_DATASET` / `NERUDS_BIGQUERY_SECONDARY_AUDIT_TABLE`; staging defaults include both `auditoria_citacoes_us.citacoes_auditaveis` and `auditoria_citacoes.citacoes_auditaveis`.
The dataset discovery endpoint groups public/auditable rows by project, source dataset and source table, and merges active metadata from `NERUDS_BIGQUERY_INVENTORY_DATASET` / `NERUDS_BIGQUERY_INVENTORY_TABLE`; staging defaults point to `cur_inventario_neruds.inventario_datasets`.

Do not paste secrets into chat. Store them in `/root/neruds_staging/.env`, Google Secret Manager, or n8n credentials, then inject only environment variable names into Drupal.

## Accessibility Operations

- Keep `NERUDS_ENABLE_VLIBRAS=1` unless a legal/accessibility review explicitly disables the public widget.
- Active staging modules: `editoria11y`, `ableplayer`, `a11y_form_helpers`, `ai_image_alt_text`, and `gemini_provider`.
- The custom accessibility preference toolbar covers the first-cycle Style Switcher need with high contrast, grayscale, reading spacing, large cursor, skip link, visible focus, and reduced-motion support.
- Editor policy: every image must keep a human-reviewed alternative text. `ai_image_alt_text` is configured as assisted generation only: `autogenerate=false`, no bulk generation, and Gemini is configured through a Key entity backed by `NERUDS_GOOGLE_API_GEMINI`.
- Looker Studio embeds must include a visible text summary before the iframe and alt text/descriptions inside the Looker report for charts and images.
- Manual acceptance: keyboard-only navigation, focus order, screen reader labels, 320px/768px/1440px responsive checks, contrast checks, and reduced-motion mode.

## Staging Deploy

From the workspace:

```powershell
.\deploy\neruds_google_integration_deploy.ps1
```

The script uses the local SSH alias `cleiton-vps`, syncs production data to staging, copies the module, fixes module file permissions, enables it, clears cache, and shows config status.

## Manual Validation

- Visit `/` and confirm dashboard, map, and timeline blocks render.
- Visit `/neruds/api/dashboard`, `/neruds/api/territories`, and `/neruds/api/calendar`.
- Visit `/neruds/api/drive-folder?folder=GOOGLE_DRIVE_FOLDER_ID` after a public Drive folder is linked.
- Visit `/publicacoes-discovery` and test search after `NERUDS_GOOGLE_SEARCH_CX` or Vertex AI Search is configured.
- Visit `/neruds/api/search-readiness` to monitor Vertex ingestion readiness and preview indexed results.
- Visit `/neruds/api/forum-moderation` to track pending forum comments queue.
- Run `drush cim --no --diff` before any production promotion.

## Forum moderation policy

- New comments in `grupo_estudos` forum are forced into moderation queue unless the account has `administer comments` or `bypass neruds forum moderation`.
- Moderation queue URL: `/admin/content/comment/approval`.
