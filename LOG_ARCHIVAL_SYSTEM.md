# Sistema di Archiviazione Log con Aggregazione Mensile

Il sistema archivia i log giornalmente e li aggrega mensilmente per ottimizzare ricerche e analisi a lungo termine.

## 📦 Struttura di Archiviazione

### 1. Archiviazione Giornaliera (Automatica)
```bash
# Struttura: logs/YYYY/MM/DD/laravel/channel.log
logs/2026/04/14/laravel/user.log     # Log user del 14 aprile
logs/2026/04/15/laravel/user.log     # Log user del 15 aprile
```
- **Vantaggi**: Files giornalieri di dimensione gestibile
- **Uso**: Analisi giornaliere e troubleshooting recente

### 2. Aggregazione Mensile (Schedulata)
```bash
# Struttura: logs/YYYY/MM/laravel/monthly/channel.log
logs/2026/04/laravel/monthly/user.log    # Tutti i log user di aprile
logs/2026/04/laravel/monthly/role.log    # Tutti i log role di aprile
```
- **Vantaggi**: 1 file per canale/mese, ricerche ultra-rapide
- **Uso**: Analytics mensili, audit trail, ricerche storiche

## 🔧 Configurazione

Aggiungi al `.env`:
```bash
# Bucket (allineato con nginx logs)
GOOGLE_CLOUD_STORAGE_BUCKET=ludosport-production

# Aggregazione mensile (raccomandato)
LOG_ARCHIVE_AGGREGATE_MONTHLY=true

# ⚠️ ATTENZIONE: Elimina i files giornalieri dopo aggregazione (SCONSIGLIATO)
# LOG_ARCHIVE_REMOVE_DAILY_AFTER_MONTHLY=false  # Default: conserva i files originali
```

**Comportamento sicuro per default**: I files giornalieri vengono **sempre conservati** dopo l'aggregazione mensile.

## 🚀 Comandi Disponibili

### Archiviazione Giornaliera
```bash
# Archiviazione normale
docker exec -t ludosport-app-1 php artisan logs:archive-daily

# Con debug per troubleshooting
docker exec -t ludosport-app-1 php artisan logs:archive-daily --force --debug
```

### Aggregazione Mensile
```bash
# Aggrega il mese scorso
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly

# Aggrega un mese specifico
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly 2026-03

# Canale specifico
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly --channel=user

# Dry run (mostra cosa farebbe)
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly --dry-run
```

## 📅 Schedulazione Automatica

```php
// Archiviazione giornaliera alle 00:10
Schedule::command('logs:archive-daily')->dailyAt('00:10')

// Aggregazione mensile il 1° di ogni mese alle 02:00
Schedule::command('logs:aggregate-monthly')->monthlyOn(1, '02:00')
```

## 🔍 Struttura Completa nel Bucket

```
ludosport-production/
└── logs/
    └── 2026/
        └── 04/
            ├── 01/
            │   └── laravel/
            │       ├── laravel.log     (logs del 1 aprile)
            │       ├── user.log        (logs user del 1 aprile)
            │       └── role.log        (logs role del 1 aprile)
            ├── 02/
            │   └── laravel/
            │       └── [files del 2 aprile...]
            ├── ...
            └── laravel/
                └── monthly/
                    ├── laravel.log     (TUTTI i log di aprile)
                    ├── user.log        (TUTTI i log user di aprile)  
                    └── role.log        (TUTTI i log role di aprile)
```

## 💡 Strategie di Ricerca Ottimizzate

### 🚀 Ultra-Veloce: Ricerca Mensile
```bash
# Download di 1 solo file per tutto il mese
gsutil cp gs://ludosport-production/logs/2026/04/laravel/monthly/user.log ./

# Ricerca locale super-veloce
grep "user_id:12345" user.log
grep "modified.*role" *.log
```

### 📊 Analytics Mensile
```bash
# Download tutti i canali del mese
gsutil -m cp gs://ludosport-production/logs/2026/04/laravel/monthly/* ./monthly/

# Analisi completa con tools esterni
cat monthly/user.log | grep "created" | wc -l      # Conta utenti creati
cat monthly/role.log | grep "admin" | wc -l        # Conta azioni admin
```

### 🎯 Ricerca Specifica Giornaliera
```bash
# Per troubleshooting di un giorno specifico
gsutil cp gs://ludosport-production/logs/2026/04/15/laravel/user.log ./

# Analisi giorno per giorno
grep "error" user.log
```

## ⚡ Vantaggi dell'Aggregazione Mensile

### Prestazioni
- **1 download vs 30+**: Scarichi 1 file invece di 30+ files giornalieri
- **Ricerche 30x più veloci**: Grep/ricerca in un singolo file
- **Meno API calls**: Riduce costi Google Cloud Storage

### Analisi
- **Trend mensili**: Pattern di utilizzo, picchi di attività  
- **Audit completo**: Tutta la storia di un utente in un file
- **Reports automatici**: Facile integrazione con analytics tools

### Gestione
- **Meno clutter**: Bucket più organizzato con meno files
- **Backup semplificato**: Archiviazione long-term più efficiente
- **Retention policy**: Facile implementare pulizia automatica

## 🔄 Migrazione Files Esistenti

```bash
# Aggrega tutti i log di marzo 2026
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly 2026-03

# Aggrega febbraio con dry-run per vedere cosa farebbe
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly 2026-02 --dry-run

# Aggrega solo canale specifico
docker exec -t ludosport-app-1 php artisan logs:aggregate-monthly 2026-01 --channel=user
```

## ⚠️ Note Importanti

1. **Files Conservati**: I files giornalieri vengono **SEMPRE CONSERVATI** per default
2. **Doppio Backup**: Hai sia backup giornalieri che mensili per massima sicurezza  
3. **Files con Separatori**: I log mensili includono separatori per giorno (`=== Day 15 ===`)
4. **Timezone**: Sistema usa +2 ore (Europe/Rome) allineato con nginx
5. **Schedulazione**: Aggregazione mensile automatica il 1° di ogni mese
6. **Compatibilità**: Mantiene struttura esistente, aggiunge solo cartella `/monthly/`
7. **Eliminazione**: Solo se esplicitamente configurato `REMOVE_DAILY_AFTER_MONTHLY=true` (SCONSIGLIATO)