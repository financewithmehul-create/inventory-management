# Inventory Management System

A simple system for a small business to run its stock, sales, purchases, invoices and accounts from one place.
It is built for one person to use, with plain words on every screen.

## What it does

| Area | What you can do |
| --- | --- |
| Dashboard | See this month's sales, money to receive, bills to pay and items running low. |
| Sales | Make quotes, turn them into orders, keep a list of customers. |
| Inventory | Add products, receive goods, send goods, count stock. |
| Purchase | Ask suppliers for prices, place orders, keep a list of suppliers. |
| Invoices | Bill customers, record payments, keep track of bills to pay. |
| Accounting | Accounts list, entries and the standard reports (profit and loss, balance sheet). |

## Requirements

- PHP 8.3 with the extensions `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`,
  `pdo_mysql`, `sodium`, `tokenizer`, `xml`, `zip`
- MySQL 8 or MariaDB 10.6+
- Composer 2 (Node 20+ only if you change the styles)

## Install

```bash
git clone <your repository url> inventory
cd inventory
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# set APP_URL and the DB_* values in .env
php artisan erp:install
```

The installer asks for the administrator's name, email and a strong password. When you sign in for the first
time, a short setup asks for your business details, logo and colour.

Guides:

- [Deploy on Hostinger](docs/DEPLOY_HOSTINGER.md)
- [Run it on your own computer](docs/LOCAL_SETUP.md)
- [Windows setup and command list](docs/WINDOWS_CHEATSHEET.md)

## Useful commands

| Command | What it does |
| --- | --- |
| `php artisan erp:doctor --fix` | Checks the database and repairs missing tables or permissions. |
| `php artisan erp:admin:reset you@example.com` | Sets a new password for the administrator. |
| `php artisan erp:backup` | Saves a compressed copy of the database in `storage/app/backups`. |
| `php artisan erp:modules:prune` | Removes modules that are not part of this product (deletes their data). |

## Security

Please report security problems privately to the owner of this repository instead of opening a public issue.

## License

Released under the MIT license. See [LICENSE](LICENSE).
