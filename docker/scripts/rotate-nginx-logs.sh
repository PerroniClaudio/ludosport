#!/bin/bash

# Script per archiviare i log Nginx su Google Cloud Storage
# con struttura gerarchica temporale: logs/YYYY/MM/DD/HH-MM-SS/

# Timestamp completo (con timezone +2 hours)
YEAR=$(date -d "+2 hours" +%Y)
MONTH=$(date -d "+2 hours" +%m)
DAY=$(date -d "+2 hours" +%d)
HOUR=$(date -d "+2 hours" +%H)
MINUTE=$(date -d "+2 hours" +%M)
SECOND=$(date -d "+2 hours" +%S)

# Cartella locale dei log Nginx
NGINX_LOG_DIR="/server/site/ludosport/docker/logs/nginx"

# Bucket e percorso di destinazione con timestamp gerarchico
BUCKET="ludosport-production"
DEST="logs/$YEAR/$MONTH/$DAY/$HOUR-$MINUTE-$SECOND/nginx/"

# Verifica che la directory log esista
if [ ! -d "$NGINX_LOG_DIR" ]; then
    echo "Error: Nginx log directory not found: $NGINX_LOG_DIR"
    exit 1
fi

# Carica i log Nginx sul bucket nella cartella con timestamp
echo "Uploading Nginx logs to gs://$BUCKET/$DEST"
gsutil -m cp -r "$NGINX_LOG_DIR"/* "gs://$BUCKET/$DEST"

if [ $? -eq 0 ]; then
    echo "Successfully uploaded Nginx logs"
    
    # Opzionale: Tronca i log dopo l'upload (decommentare se necessario)
    # echo "Truncating local Nginx logs..."
    # > "$NGINX_LOG_DIR/access.log"
    # > "$NGINX_LOG_DIR/error.log"
    # echo "Local logs truncated"
else
    echo "Error: Failed to upload Nginx logs"
    exit 1
fi

exit 0
