docker compose --env-file ./docker/.env.prod -f docker-compose.prod.yml build
docker tag ludosport-app:latest europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest
docker push europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest                     
# gcloud run deploy ludosport-service --image europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest --platform managed --region europe-west8 --allow-unauthenticated