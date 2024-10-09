docker compose --env-file /docker/.env.dev -f docker-compose.dev.yml up -d --build
docker compose --env-file /docker/.env.prod -f docker-compose.prod.yml up -d --build