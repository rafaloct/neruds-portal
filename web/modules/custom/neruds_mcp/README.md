# NERUDS MCP — AI Content Management API

Drupal Model Context Protocol (MCP) integration for NERUDS — enables AI systems to autonomously manage content (News, Events, Projects) with full audit logging and security.

## Features

- ✅ **REST API** for CRUD operations on News, Events, Projects
- ✅ **Taxonomy Integration** — ODS (17 terms) + Research Categories
- ✅ **Full Audit Logging** — Every operation logged to Drupal watchdog
- ✅ **Permission-based Access Control**
- ✅ **JSON Request/Response** — Easy integration with AI systems
- ✅ **Rate Limiting Ready** — Can be enabled via Drupal contrib modules

## Installation

### 1. Enable the module
```bash
drush module:install neruds_mcp
```

### 2. Grant permissions (if not auto-assigned)
```bash
# Via UI: /admin/people/permissions
# Or via Drush:
drush role:add-perm authenticated "access mcp api"
drush role:add-perm authenticated "create mcp nodes"
```

### 3. Verify API is accessible
```bash
curl http://your-site/api/mcp/taxonomy/ods
```

## API Endpoints

### List ODS Terms
```bash
GET /api/mcp/taxonomy/ods
```

**Response:**
```json
{
  "vocabulary": "ods",
  "count": 17,
  "terms": [
    {
      "id": 1,
      "name": "ODS 1 - Erradicar a Pobreza",
      "color": "#E5243B"
    },
    ...
  ]
}
```

### List Research Categories
```bash
GET /api/mcp/taxonomy/categories
```

**Response:**
```json
{
  "vocabulary": "research_categories",
  "count": 5,
  "terms": [
    {
      "id": 1,
      "name": "Sistemas Socioecológicos"
    },
    ...
  ]
}
```

### Create News Node
```bash
POST /api/mcp/nodes
Content-Type: application/json

{
  "type": "news",
  "title": "New NERUDS Research Published",
  "body": "Full article HTML content here...",
  "field_ods": [1, 2, 3],
  "field_research_categories": [1, 2],
  "status": 1
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "nid": 123,
  "type": "news",
  "title": "New NERUDS Research Published",
  "url": "http://your-site/node/123"
}
```

### Create Event Node
```bash
POST /api/mcp/nodes
Content-Type: application/json

{
  "type": "event",
  "title": "NERUDS Annual Conference 2026",
  "body": "Event details and schedule...",
  "field_ods": [4, 5],
  "field_research_categories": [1],
  "status": 1
}
```

### Create Project Node
```bash
POST /api/mcp/nodes
Content-Type: application/json

{
  "type": "project",
  "title": "Biodiversity Conservation in Tocantins",
  "body": "Project description and methods...",
  "field_ods": [13, 15],
  "field_research_categories": [3],
  "status": 1
}
```

### List News (with filters)
```bash
GET /api/mcp/nodes?type=news&limit=20&ods=1&category=2
```

**Query Parameters:**
- `type` — 'news', 'event', or 'project'
- `limit` — Max 20-100 (default 20)
- `ods` — Filter by ODS term ID
- `category` — Filter by category term ID

**Response:**
```json
{
  "type": "news",
  "count": 5,
  "items": [
    {
      "nid": 123,
      "type": "news",
      "title": "Article Title",
      "body": "Article content...",
      "status": 1,
      "created": 1711270000,
      "updated": 1711270000,
      "author": "admin",
      "field_ods": [
        {"id": 1, "name": "ODS 1 - Erradicar a Pobreza"}
      ],
      "field_research_categories": [
        {"id": 1, "name": "Sistemas Socioecológicos"}
      ],
      "url": "http://your-site/node/123"
    }
  ]
}
```

### Get Single Node
```bash
GET /api/mcp/nodes/123
```

### Update Node
```bash
PATCH /api/mcp/nodes/123
Content-Type: application/json

{
  "title": "Updated Title",
  "body": "Updated content...",
  "status": 0
}
```

**Response:**
```json
{
  "success": true,
  "nid": 123,
  "message": "Node updated successfully"
}
```

### Delete Node
```bash
DELETE /api/mcp/nodes/123
```

**Response:**
```json
{
  "success": true,
  "message": "Node deleted successfully"
}
```

## Usage with Claude AI

### Example: Claude Creating News via MCP

```python
import requests

BASE_URL = "http://your-neruds-site"
HEADERS = {"Content-Type": "application/json"}

# 1. Get available ODS
response = requests.get(f"{BASE_URL}/api/mcp/taxonomy/ods")
ods_terms = {t['id']: t['name'] for t in response.json()['terms']}

# 2. Create news article
news_data = {
    "type": "news",
    "title": "Climate Action Initiative Launched by NERUDS",
    "body": "<p>NERUDS announces new climate research partnership...</p>",
    "field_ods": [13],  # ODS 13 - Ação Climática
    "field_research_categories": [3],  # Biodiversity
    "status": 1
}

response = requests.post(f"{BASE_URL}/api/mcp/nodes", json=news_data, headers=HEADERS)
if response.status_code == 201:
    print(f"✅ News created: {response.json()['url']}")
```

## Security Considerations

### Current Implementation
- ✅ Permission checks on every endpoint
- ✅ Full audit logging of all operations
- ✅ JSON validation
- ✅ Drupal's built-in CSRF protection
- ✅ User ownership tracking

### Recommended Enhancements

1. **JWT Authentication** (optional)
   ```bash
   drush module:install jwt
   # Configure token validation in settings
   ```

2. **Rate Limiting** (optional)
   ```bash
   drush module:install rate_limiter
   ```

3. **HTTPS Only** (production)
   - Configure SSL certificate
   - Add `$settings['https_only'] = TRUE;` to settings.php

4. **IP Whitelisting** (optional)
   - Configure via nginx/Apache rewrite rules

## Logging & Monitoring

All MCP operations are logged to the `neruds_mcp` channel:

```bash
# View logs
drush watchdog:tail neruds_mcp

# Example log entry
[2026-03-24 12:34:56] MCP: Node created successfully. NID: 123, Type: news
```

## Testing

### Quick Test via curl
```bash
# 1. Get ODS
curl -s http://localhost/api/mcp/taxonomy/ods | jq .

# 2. Create news
curl -X POST http://localhost/api/mcp/nodes \
  -H "Content-Type: application/json" \
  -d '{
    "type": "news",
    "title": "Test Article",
    "body": "Test content",
    "field_ods": [1],
    "status": 1
  }'

# 3. List news
curl http://localhost/api/mcp/nodes?type=news

# 4. Get single
curl http://localhost/api/mcp/nodes/123

# 5. Delete
curl -X DELETE http://localhost/api/mcp/nodes/123
```

## Troubleshooting

### 403 Forbidden
- Verify user has "access mcp api" permission
- Check role settings: `/admin/people/roles`

### 404 Not Found
- Verify module is enabled: `drush module:list | grep neruds_mcp`
- Clear cache: `drush cache:rebuild`

### Node not created
- Check logs: `drush watchdog:tail neruds_mcp`
- Verify JSON format is valid
- Ensure ODS/Category term IDs exist: `/api/mcp/taxonomy/ods`

## Configuration

Edit `/web/modules/custom/neruds_mcp/neruds_mcp.services.yml` to customize:
- Logger channel name
- Entity type manager behavior
- Config factory settings

## Contributing

To extend MCP functionality:

1. Add new methods to `MCPService`
2. Add new controller methods to `MCPController`
3. Add new routes in `neruds_mcp.routing.yml`
4. Update this README

## License

Part of NERUDS project — All Rights Reserved

## Contact

NERUDS (Núcleo de Estudos Rurais, Desigualdades e Sistemas Socioecológicos)
UFT — Universidade Federal do Tocantins
