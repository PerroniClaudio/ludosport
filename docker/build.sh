# Carica le variabili d'ambiente dal file .env.prod
export $(grep -v '^#' ./.env.prod | xargs)

# Esegui il comando docker buildx build con le variabili d'ambiente caricate
docker buildx build --platform linux/amd64 -t ludosport/ludosport-app:latest -f docker-compose.prod.yml .