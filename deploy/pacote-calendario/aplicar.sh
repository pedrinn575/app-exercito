#!/bin/bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
DEST=/var/www/escalas
sudo cp -a "$ROOT/app/." "$DEST/app/"
sudo chown -R www-data:www-data \
  "$DEST/app/Controllers/EscalaController.php" \
  "$DEST/app/Controllers/DashboardController.php" \
  "$DEST/app/Views/calendario/index.php" \
  "$DEST/app/Views/escalas/lista.php" \
  "$DEST/app/Views/dashboard/index.php"
php -l "$DEST/app/Controllers/EscalaController.php"
php -l "$DEST/app/Controllers/DashboardController.php"
php -l "$DEST/app/Views/calendario/index.php"
php -l "$DEST/app/Views/escalas/lista.php"
php -l "$DEST/app/Views/dashboard/index.php"
echo "Pacote aplicado em $DEST"