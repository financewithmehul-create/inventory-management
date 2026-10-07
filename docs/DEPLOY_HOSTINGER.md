# Deploying the ERP on Hostinger (app + MySQL database on one plan)

This guide puts the whole ERP on a single Hostinger **Premium Web Hosting** plan: the PHP app, its MySQL
database, the domain, SSL, backups and scheduled jobs. No Supabase or other paid database is needed.

> Hostinger changes its panel (hPanel) from time to time. If a menu is named slightly differently, look for
> the closest match. Everything below uses standard features included in the Premium plan.

**What you need before starting**

- A Hostinger Premium (or Business) Web Hosting plan, bought by the client, with their domain added.
- The code repository link and access to it (GitHub).
- About 45 minutes.

---

## Quick path (recommended): subdomain + one command

This is how the demo at `inventory.creativebee.app` (and every customer site) is set up. Parts 1 to 8 below
explain each step in detail; the installer script does Parts 4, 6 and 7 for you.

1. **Create the subdomain.** hPanel → **Websites → Add website → Empty PHP/HTML website**, choose **Use a
   subdomain** (or **Domains → Subdomains → Create**), enter `inventory` under `creativebee.app`. Hostinger
   creates `~/domains/inventory.creativebee.app/public_html`.
2. **PHP 8.3 and extensions, SSL**: Part 1 below. Turn on **Force HTTPS**.
3. **Database**: Part 2 below (one new database per site, never shared).
4. **SSH in** (Part 3) and run:

```bash
cd ~/domains/inventory.creativebee.app
git clone https://github.com/financewithmehul-create/inventory-management.git erp
cd erp && git checkout claude/zen-curie-pt2356
bash scripts/hostinger/install.sh
```

   It checks PHP, installs packages, asks for the site address, database details and the administrator, writes a
   locked-down `.env`, installs the ERP and prints the last hPanel steps.
5. **Point the site at the app** and add the two **cron jobs** as printed by the script (Parts 5 and 8).
6. Open `https://inventory.creativebee.app`. You are sent to the **setup wizard** (company, logo, colours,
   light/dark, modules). Settings can be changed later under **Settings → Branding**.

Update later with `bash scripts/hostinger/update.sh` (it makes a database backup first).

---

## Part 1: Prepare the website in hPanel

1. Log in to **hPanel** and open **Websites → Manage** next to the client's domain.
2. **PHP version.** Open **Advanced → PHP Configuration**:
   - Choose **PHP 8.3**.
   - On the **PHP Extensions** tab, make sure these are ticked: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`,
     `gd`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
   - On the **PHP Options** tab raise: `memory_limit` to **512M**, `max_execution_time` to **300**,
     `upload_max_filesize` and `post_max_size` to **64M**.
3. **SSL.** Open **Security → SSL** and install the free SSL for the domain, then turn on **Force HTTPS**.

## Part 2: Create the database

1. Open **Databases → Management**.
2. Under **Create a New MySQL Database And Database User**, enter a database name, a user name and a strong
   password, then click **Create**.
3. Write down the three values. Hostinger adds a prefix, so they look like `u123456789_erp` (database),
   `u123456789_erpuser` (user) and the password you chose. The database host is always `localhost`.
4. Do **not** reuse a database that belongs to another website.

## Part 3: Turn on SSH and connect

1. Open **Advanced → SSH Access** and click **Enable**. Note the **IP**, **port** (Hostinger uses `65002`) and
   **username** (like `u123456789`). Set or reset the SSH password.
2. From your computer, open a terminal (on Windows use PowerShell) and connect:

```bash
ssh -p 65002 u123456789@YOUR.SERVER.IP
```

3. Check the tools are there:

```bash
php -v          # must say PHP 8.3.x. If not, see Troubleshooting.
composer -V
git --version
```

## Part 4: Put the code on the server

The app lives **outside** the public folder, and only its `public` folder is exposed to the internet. In the
commands below replace `example.com` with the client's domain.

```bash
cd ~/domains/example.com
git clone https://github.com/financewithmehul-create/inventory-management.git erp
cd erp
git checkout claude/zen-curie-pt2356    # or the branch you decided to release from
composer install --no-dev --optimize-autoloader --no-interaction
```

If `composer install` stops with a memory error, run it as `php -d memory_limit=-1 $(which composer) install --no-dev -o`.

If the repository is private, GitHub will ask for a username and a personal access token instead of a password.

## Part 5: Point the domain at the app's `public` folder

Hostinger serves the website from `~/domains/example.com/public_html`. Replace that folder with a link to the
app's `public` folder.

1. Make sure `public_html` holds nothing you need (a new site only has a default placeholder page).
2. Run:

```bash
cd ~/domains/example.com
mv public_html public_html_old
ln -s erp/public public_html
```

3. Open `https://example.com/admin/login`. You should see the login page (the ERP is not installed yet, so
   do not log in). If you see a blank page or an error, read Troubleshooting.

## Part 6: Configure the app (`.env`)

```bash
cd ~/domains/example.com/erp
cp .env.example .env
php artisan key:generate
nano .env
```

Set these values (keep everything else as it is):

```ini
APP_NAME="Client Company ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_TIMEZONE=Asia/Kolkata
APP_CURRENCY=INR

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_erp
DB_USERNAME=u123456789_erpuser
DB_PASSWORD=the-password-you-chose

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LOG_LEVEL=warning
```

Save in nano with `Ctrl+O`, `Enter`, then `Ctrl+X`. Never commit this file or share it: it holds the database
password. Protect it with `chmod 600 .env`.

Mail (for password resets and notifications): set `MAIL_MAILER=smtp` and the host, port, username and
password of a mailbox created in hPanel under **Emails**.

## Part 7: Install the ERP

```bash
php artisan erp:install --force --country=IN --currency=INR \
  --admin-name="Client Admin" --admin-email=admin@example.com --admin-password="ChooseAStrongPassword"

for p in accounts accounting barcode blogs contacts employees inventories invoices maintenance \
         manufacturing payments products projects purchases recruitments sales time-off timesheets website; do
  php artisan $p:install --no-interaction
done

php artisan storage:link
php artisan optimize
```

`--force` wipes the database, so use it only on a brand-new database. Install only the modules the client
wants if you prefer: the list above is every module.

Now open `https://example.com/admin` and log in with the admin email and password you chose. **Change the
password** after the first login and give the client their own users under **Settings → Users**.

## Part 8: Scheduled jobs (cron)

The ERP sends notifications and runs background work through Laravel's scheduler and queue.

1. In hPanel open **Advanced → Cron Jobs**.
2. Add a job that runs **every minute** with this command (replace the paths):

```
/usr/bin/php /home/u123456789/domains/example.com/erp/artisan schedule:run >> /dev/null 2>&1
```

3. Add a second job, also every minute, for the queue:

```
/usr/bin/php /home/u123456789/domains/example.com/erp/artisan queue:work --stop-when-empty --max-time=55 --memory=512 >> /dev/null 2>&1
```

Find your exact home path with `pwd` after logging in over SSH.

## Part 9: Backups

- **Hostinger backups:** open **Files → Backups** to see automatic backups and restore them.
- **Database export:** open **Databases → phpMyAdmin → Enter phpMyAdmin**, choose the database, then
  **Export → Quick → SQL**. Do this before every update and store the file somewhere safe.
- **Automatic daily copy:** the app saves a compressed database copy to `storage/app/backups` every night at
  02:30 (needs the cron job in Part 8) and keeps 14 days. Run one now with `php artisan erp:backup`. These files
  are not reachable from the web. Download them over SFTP now and then.
- **Restore:** `gunzip -c storage/app/backups/FILE.sql.gz | mysql -u USER -p DATABASE`.
- Back up `~/domains/example.com/erp/.env` and the `storage/app` folder (uploaded files) too.

## Part 10: Updating the app later

```bash
cd ~/domains/example.com/erp
php artisan down
git pull
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan up
```

Export the database from phpMyAdmin first.

---

## Troubleshooting

| What you see | What to do |
| --- | --- |
| `php -v` shows an old version over SSH | Run `which -a php`, then use the PHP 8.3 path (for example `/opt/alt/php83/usr/bin/php`) in place of `php`, and in the cron lines. |
| Blank page or **500 error** | Run `tail -n 50 storage/logs/laravel.log`. Check `.env` values and run `php artisan optimize:clear`. |
| **403 / 404 on every page except the home page** | The symlink in Part 5 is missing or `public/.htaccess` was deleted. Re-check with `ls -l ~/domains/example.com`. |
| **Class not found / missing extension** | Re-check the extensions in Part 1 (especially `bcmath`, `intl`, `gd`, `zip`) and run `composer install` again. |
| **Database connection refused** | The host must be `localhost`, and the database name and user must include Hostinger's `u123456789_` prefix. |
| **File permission errors** | `chmod -R 775 storage bootstrap/cache` |
| Pages load without styling | `public/build` is missing. It is committed in the repository; run `git status` and `git pull`. |
| Logo or uploads missing | Run `php artisan storage:link` again. |
| Jobs and emails never run | Check the two cron jobs in Part 8 and the paths in them. |

## Licensing: 14-day trial and license keys

Every new install gets a **14-day free trial**. After that the workspace becomes **read-only** (people can sign
in and see everything, nothing can be added or changed, nothing is ever deleted) until a license key is entered
under **Settings → License**. A banner shows the days left.

You (the vendor) create keys. Customers cannot make or extend them.

1. **Once, on your own computer** (not on a customer server): `php artisan license:keygen`. Save the
   **private key** in a password manager. Put the **public key** in `LICENSE_PUBLIC_KEY` in each customer's `.env`
   (the install script asks for it).
2. **For each sale:**

```bash
php artisan license:issue --to="Acme Traders" --domain=acme.com --until=2027-10-31
# add --users=10 to record a user limit; leave out --until for a lifetime key
```

   It asks for your private key (hidden, never stored; or set `LICENSE_PRIVATE_KEY` for the command only) and
   prints a key starting `AUR1.`. Send it to the customer. It only works on that domain (subdomains included).
3. The customer pastes it under **Settings → License → Activate**. `php artisan license:status` shows the state.

To turn licensing off for your own demo, set `LICENSE_ENFORCE=false` in `.env`.

## Selling it to a client

**The client buys:** a domain and a Hostinger **Business** web hosting plan (this gives each client their own
database, so customers are fully separated from each other and cost you nothing to host).

**You do (about 30 minutes per client):**

1. Ask the client to add you to their Hostinger account (or share SSH details), or do it on a call.
2. Follow the **Quick path** above on their domain, using their own database and an administrator email they own.
3. Issue their license key (above) and give it to them after the trial or at handover.
4. Show them the setup wizard: company, logo, colours, modules, then **Settings → Users** to invite staff and
   assign roles (Inventory Manager, Warehouse Staff, Viewer and more, each with its own avatar).

**Per-customer checklist:** PHP 8.3 and extensions · SSL + Force HTTPS · own database · `.env` with
`APP_DEBUG=false` · cron jobs added · admin password changed and two-factor sign-in enabled (profile) ·
license public key set · key issued · first backup taken (`php artisan erp:backup`).

## Security notes

- Production `.env` uses `APP_DEBUG=false`, encrypted sessions, secure cookies and warning-level logs
  (the install script sets these). Keep `.env` at `chmod 600`.
- The app sends security headers (clickjacking, MIME sniffing, referrer policy, HSTS on HTTPS) and limits the
  REST API (120 calls a minute per person, `API_RATE_LIMIT`).
- Passwords: at least 10 characters with upper and lower case letters and a number, and not found in known
  breaches (checked in production).
- Staff can turn on authenticator-app two-factor sign-in from their profile.

## Costs recap

One Hostinger Premium plan covers the PHP app, the MySQL database, SSL, the domain and daily backups. There
is no separate database subscription.
