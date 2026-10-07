#!/usr/bin/env bash
# One-command installer for Hostinger (run over SSH from the folder that holds the app, for example
#   ~/domains/inventory.creativebee.app/erp
# ). It writes .env, installs the database and prints the remaining hPanel steps.
#
#   bash scripts/hostinger/install.sh
set -euo pipefail

cd "$(dirname "$0")/../.."

say()  { printf '\n\033[1;36m==> %s\033[0m\n' "$1"; }
fail() { printf '\033[1;31mERROR: %s\033[0m\n' "$1" >&2; exit 1; }
ask()  { local reply; read -r -p "$1 " reply; printf '%s' "${reply:-${2:-}}"; }
askhidden() { local reply; read -r -s -p "$1 " reply; echo >&2; printf '%s' "$reply"; }

say "Checking the server"
command -v php >/dev/null || fail "php not found"
php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
  || fail "PHP 8.3 is required (found $(php -r 'echo PHP_VERSION;')). In hPanel: Advanced > PHP Configuration > PHP 8.3."
modules=$(php -m)
for ext in bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip sodium; do
  printf '%s\n' "$modules" | grep -qix "$ext" || fail "PHP extension '$ext' is missing. Tick it in hPanel > PHP Configuration > PHP Extensions."
done
command -v composer >/dev/null || fail "composer not found"

say "Installing PHP packages"
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f .env ] && grep -q '^APP_KEY=base64' .env; then
  say ".env already exists, keeping it"
else
  say "Creating .env"
  url=$(ask "Site address (for example https://inventory.creativebee.app):")
  [ -n "$url" ] || fail "The site address is required"
  dbname=$(ask "Database name (from hPanel, like u123456789_erp):")
  dbuser=$(ask "Database user:")
  dbpass=$(askhidden "Database password (hidden):")
  appname=$(ask "Product / company name shown in the app [ERP]:" "ERP")
  tz=$(ask "Time zone [Asia/Kolkata]:" "Asia/Kolkata")
  pubkey=$(ask "License PUBLIC key (leave empty to skip for now):")

  cp .env.example .env
  set_env() { # key value
    local value; value=$(printf '%s' "$2" | sed -e 's/[\/&|]/\\&/g')
    if grep -q "^$1=" .env; then sed -i "s|^$1=.*|$1=\"$value\"|" .env; else printf '%s="%s"\n' "$1" "$2" >> .env; fi
  }
  set_env APP_NAME "$appname"
  set_env APP_ENV production
  set_env APP_DEBUG false
  set_env APP_URL "$url"
  set_env APP_TIMEZONE "$tz"
  set_env DB_CONNECTION mysql
  set_env DB_HOST localhost
  set_env DB_PORT 3306
  set_env DB_DATABASE "$dbname"
  set_env DB_USERNAME "$dbuser"
  set_env DB_PASSWORD "$dbpass"
  set_env SESSION_DRIVER database
  set_env SESSION_SECURE_COOKIE true
  set_env SESSION_ENCRYPT true
  set_env CACHE_STORE database
  set_env QUEUE_CONNECTION database
  set_env FILESYSTEM_DISK public
  set_env LOG_LEVEL warning
  set_env LICENSE_ENFORCE true
  [ -z "$pubkey" ] || set_env LICENSE_PUBLIC_KEY "$pubkey"
  chmod 600 .env
  php artisan key:generate --force
fi

say "Checking the database connection"
php artisan db:show >/dev/null 2>&1 \
  || fail "Cannot connect to the database. Open .env (nano .env) and set APP_URL, DB_HOST=localhost, DB_DATABASE, DB_USERNAME and DB_PASSWORD from hPanel > Databases, then run this script again."

[ -e public/storage ] || ln -s ../storage/app/public public/storage

say "Installing the ERP (this wipes an empty database and builds all tables)"
adminname=$(ask "Administrator name [Admin]:" "Admin")
adminemail=$(ask "Administrator email:")
[ -n "$adminemail" ] || fail "Administrator email is required"
adminpass=$(askhidden "Administrator password (min 10 characters, hidden):")
[ ${#adminpass} -ge 10 ] || fail "Choose a password of at least 10 characters"

php artisan erp:install --force --no-interaction --country=IN --currency=INR \
  --admin-name="$adminname" --admin-email="$adminemail" --admin-password="$adminpass"

php artisan optimize
chmod -R 775 storage bootstrap/cache

root="$(pwd)"
cat <<DONE

Done. Next steps in hPanel:
  1. Make sure the (sub)domain's document root points at:  $root/public
     (or run:  rm -rf ../public_html && ln -s "$root/public" ../public_html )
  2. Turn on SSL and Force HTTPS (Security > SSL).
  3. Advanced > Cron Jobs, add these two, every minute:
       $(command -v php) $root/artisan schedule:run >> /dev/null 2>&1
       $(command -v php) $root/artisan queue:work --stop-when-empty --max-time=55 --memory=512 >> /dev/null 2>&1
  4. Open the site and sign in. The setup wizard (company, logo, colours, modules) starts automatically.
     The free trial runs 14 days from now; add a license key under Settings > License.

DONE
