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

Set these in GitLab CI/CD settings:
- `VITE_REVERB_APP_KEY` - Reverb public key
- `VITE_REVERB_HOST` - WebSocket host domain

## Supervisord Services

The container runs these processes:
- `nginx` - Web server
- `php-fpm` - PHP processor
- `reverb` - WebSocket server
- `queue-worker` - Processes broadcast events

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
