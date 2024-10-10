docker buildx build --platform linux/amd64 -t ludosport/ludosport-app:latest --load .
docker tag ludosport-app:latest europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest
docker push europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest                     
gcloud run deploy ludosport-service --image europe-west8-docker.pkg.dev/ludosport-2024/ludosport-repo/ludosport-app:latest --platform managed --region europe-west8 --allow-unauthenticated