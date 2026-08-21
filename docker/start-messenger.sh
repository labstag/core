#!/bin/bash
set -e

echo "🚀 Démarrage Messenger"

# Attente base de données
until symfony console about >/dev/null 2>&1; do
  echo "⏳ Attente DB..."
  sleep 2
done

echo "▶️ Lancement Supervisor"
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf