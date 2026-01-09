# LangConnect Deployment

## First-Time Setup

### 1. Deploy Infrastructure

```bash
kubectl apply -f k8s/infrastructure/namespace.yaml
kubectl apply -f k8s/infrastructure/limitrange.yaml
kubectl apply -f k8s/infrastructure/secrets.yaml
kubectl apply -f k8s/infrastructure/mysql-pvc.yaml
kubectl apply -f k8s/infrastructure/mysql-deployment.yaml
```

### 2. Create Registry Secret

```bash
kubectl create secret docker-registry registry-secret \
  --docker-server=registry.yujuism.com \
  --docker-username=gitlab-ci-token \
  --docker-password=YOUR_TOKEN \
  -n langconnect
```

### 3. Deploy App

Push to `main` branch triggers CI/CD, or:

```bash
kubectl apply -f k8s/app/deployment.yaml
kubectl apply -f k8s/app/service.yaml
```

## Cloudflare Tunnel

Add public hostname in Cloudflare Zero Trust > Tunnels:

| Subdomain | Domain | Path | URL |
|-----------|--------|------|-----|
| langconnect | cloudsynth.site | | langconnect.langconnect:80 |
| langconnect | cloudsynth.site | /app/* | langconnect.langconnect:8080 |

Enable WebSocket in hostname settings.

## Commands

```bash
kubectl get pods -n langconnect
kubectl logs -n langconnect deployment/langconnect
kubectl rollout restart -n langconnect deployment/langconnect
```
