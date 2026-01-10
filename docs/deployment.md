# LangConnect Deployment

## Architecture

- **Web Server**: Nginx (port 8000) - serves PHP via FastCGI, proxies WebSocket
- **PHP-FPM**: Handles Laravel requests (port 9000)
- **Reverb**: WebSocket server (port 8080) - proxied via nginx at `/app`
- **Queue Worker**: Processes broadcast events for real-time messaging/calls
- **MySQL**: Database with 1GB PVC

All services run in a single pod managed by supervisord.

## First-Time Setup

### 1. Create Secrets

Copy the example and fill in real values:

```bash
cp k8s/infrastructure/secrets.yaml.example k8s/infrastructure/secrets.yaml
# Edit secrets.yaml with actual passwords/keys
```

Generate values:
- `APP_KEY`: `php artisan key:generate --show`
- `REVERB_APP_KEY`: `openssl rand -hex 16`
- `REVERB_APP_SECRET`: `openssl rand -hex 32`
- `MYSQL_*_PASSWORD`: `openssl rand -base64 18`

### 2. Deploy Infrastructure

```bash
kubectl apply -f k8s/infrastructure/namespace.yaml
kubectl apply -f k8s/infrastructure/limitrange.yaml
kubectl apply -f k8s/infrastructure/secrets.yaml
kubectl apply -f k8s/infrastructure/mysql-pvc.yaml
kubectl apply -f k8s/infrastructure/mysql-deployment.yaml
```

### 3. Create Registry Secret

```bash
kubectl create secret docker-registry registry-secret \
  --docker-server=registry.yujuism.com \
  --docker-username=gitlab-ci-token \
  --docker-password=YOUR_PAT_TOKEN \
  -n langconnect
```

### 4. Configure GitLab CI Variables

In GitLab > Settings > CI/CD > Variables, add:

| Variable | Value |
|----------|-------|
| `VITE_REVERB_APP_KEY` | Same as `REVERB_APP_KEY` in secrets.yaml |
| `VITE_REVERB_HOST` | `langconnect.cloudsynth.site` |
| `MINIO_ACCESS_KEY` | MinIO access key |
| `MINIO_SECRET_KEY` | MinIO secret key (masked) |
| `MINIO_BUCKET` | `langconnect-pdfs` |
| `MINIO_ENDPOINT` | `https://s3.cloudsynth.site` |
| `MINIO_URL` | `https://s3.cloudsynth.site` |

### 5. Deploy App

Push to `main` branch triggers CI/CD, or manually:

```bash
kubectl apply -f k8s/app/deployment.yaml
kubectl apply -f k8s/app/service.yaml
```

## Cloudflare Tunnel

Add public hostname in Cloudflare Zero Trust > Tunnels:

| Subdomain | Domain | Type | URL |
|-----------|--------|------|-----|
| langconnect | cloudsynth.site | HTTP | langconnect.langconnect.svc.cluster.local:80 |

WebSocket is automatically handled - nginx proxies `/app/*` to Reverb internally.

## CI/CD Pipeline

The GitLab CI pipeline has 3 stages:

1. **build**: Builds Docker image with Vite env vars baked in
2. **infra**: Creates namespace if first deployment (secrets applied manually)
3. **deploy**: Applies k8s manifests and restarts deployment

### Required CI Variables

Set these in GitLab CI/CD settings (see table in section 4 for full list):
- `VITE_REVERB_APP_KEY` - Reverb public key (for frontend build)
- `VITE_REVERB_HOST` - WebSocket host domain (for frontend build)
- `MINIO_*` - MinIO credentials (for PDF storage)

## Supervisord Services

The container runs these processes:
- `nginx` - Web server
- `php-fpm` - PHP processor
- `reverb` - WebSocket server
- `queue-worker` - Processes broadcast events

## Collaborative Canvas

Practice sessions include a real-time collaborative canvas powered by tldraw v1.29.2 (MIT licensed).

### Features
- Real-time drawing sync between partners (100ms throttle)
- Partner cursor tracking
- Follow mode (follow partner's viewport)
- Auto-save to database (2s debounce)
- Presence indicator (online/offline status)

### How it works
1. Canvas state stored in `practice_sessions.canvas_data` (JSON)
2. Changes broadcast via `CanvasChanged` event on `private-session.{id}` channel
3. Cursor/presence synced via WebSocket whispers (no server storage)

### Frontend
- `resources/js/tldraw-canvas.jsx` - React component
- Uses `@tldraw/tldraw@1.29.2` (MIT license, no commercial license needed)
- Mounted via `window.mountTldrawCanvas()` on session page

### Backend
- `CanvasController` - save/load/broadcast endpoints
- `CanvasChanged` event - broadcasts to session channel

## Collaborative PDF Viewer

Practice sessions also include a collaborative PDF viewer for annotating documents together.

### Features
- Upload and share PDFs during sessions
- Text highlighting with color picker
- Freehand drawing (pen tool) with real-time sync
- Text annotations with real-time typing indicator
- Partner cursor tracking on PDF
- Partner text selection sync (see what partner is selecting)
- Follow mode (follow partner's page/viewport)
- Auto-save highlights and drawings to database

### How it works
1. PDFs stored in MinIO (S3-compatible storage)
2. Highlights stored in `practice_sessions.pdf_highlights` (JSON)
3. Drawings stored in `practice_sessions.pdf_drawings` (JSON)
4. Changes broadcast via WebSocket whispers for real-time sync
5. SVG overlay with viewBox for scale-independent annotations

### Frontend
- `resources/js/pdf-viewer.jsx` - React component using react-pdf v10
- Mounted via `window.mountPdfViewer()` on session page

### Backend
- `PdfController` - upload/save/broadcast endpoints
- `PdfHighlightChanged` event - broadcasts changes to partner

### MinIO Configuration

MinIO is used for S3-compatible PDF storage. Required environment variables:

| Variable | Description | Example |
|----------|-------------|---------|
| `MINIO_ACCESS_KEY` | MinIO access key | `langconnect` |
| `MINIO_SECRET_KEY` | MinIO secret key | `your-secret-key` |
| `MINIO_BUCKET` | Bucket name | `langconnect-pdfs` |
| `MINIO_ENDPOINT` | MinIO API endpoint | `https://s3.cloudsynth.site` |
| `MINIO_URL` | Public URL for PDF access | `https://s3.cloudsynth.site` |

Add these to your k8s secrets and GitLab CI variables.

## Commands

```bash
# Check pods
kubectl get pods -n langconnect

# View logs
kubectl logs -n langconnect deployment/langconnect

# Restart deployment
kubectl rollout restart -n langconnect deployment/langconnect

# Check processes in container
kubectl exec deployment/langconnect -n langconnect -- ps aux

# Run artisan commands
kubectl exec deployment/langconnect -n langconnect -- php artisan migrate
kubectl exec deployment/langconnect -n langconnect -- php artisan db:seed

# Check MySQL
kubectl exec deployment/mysql -n langconnect -- mysql -u langconnect -p langconnect -e "SHOW TABLES;"
```

## Test Users

Seeded test accounts (password: `password`):
- john@example.com (English native, learning Japanese)
- yuki@example.com (Japanese native, learning English)
- maria@example.com (Spanish native, learning English)
- hans@example.com (German native, learning Spanish)
- chen@example.com (Chinese native, learning English)
- pierre@example.com (French native, learning Japanese)
