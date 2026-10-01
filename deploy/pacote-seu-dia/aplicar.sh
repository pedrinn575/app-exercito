#!/bin/bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
DEST=/var/www/escalas
sudo cp -a "$ROOT/app/." "$DEST/app/"
sudo chown -R www-data:www-data \
  "$DEST/app/Views/calendario/index.php" \
  "$DEST/app/Controllers/CalendarioController.php" \
  "$DEST/app/Services/EscalaService.php" \
  "$DEST/app/Repositories/EscalaRepository.php"
php -l "$DEST/app/Views/calendario/index.php"
php -l "$DEST/app/Controllers/CalendarioController.php"
php -l "$DEST/app/Services/EscalaService.php"
php -l "$DEST/app/Repositories/EscalaRepository.php"
echo "Pacote aplicado em $DEST"