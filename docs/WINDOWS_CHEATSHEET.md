# Windows cheat sheet: install once, then run it any time

Works on Windows 10 and 11. Everything installs on your own computer and nothing is sent anywhere.

## 1. First time only (about 15 minutes)

1. Open **PowerShell as administrator**: press the Windows key, type `PowerShell`, right-click
   **Windows PowerShell** and choose **Run as administrator**.
2. Copy and paste these lines, one block at a time:

```powershell
winget install --id Git.Git -e --accept-package-agreements --accept-source-agreements
```

Close PowerShell and open it again **as administrator** (so it can find Git), then:

```powershell
cd $env:USERPROFILE
git clone https://github.com/financewithmehul-create/inventory-management.git
cd inventory-management
git checkout claude/zen-curie-pt2356
powershell -ExecutionPolicy Bypass -File .\scripts\windows\setup.ps1
```

The setup script installs PHP 8.3, Node.js, MariaDB (the database) and Composer, builds the ERP, and asks you
for three things: an **admin name**, an **admin email** and an **admin password**. Use that email and password
to log in later. At the end it offers to start the ERP.

If the repository is private, Git asks you to sign in to GitHub in a browser window the first time.

If Windows asks to allow PHP or Node through the firewall, choose **Private networks** and **Allow**.

## 2. Every day: start it

Open a normal PowerShell window and run:

```powershell
cd $env:USERPROFILE\inventory-management
powershell -ExecutionPolicy Bypass -File .\scripts\windows\start.ps1
```

Your browser opens **http://127.0.0.1:8000/admin**. Log in with the admin email and password you chose.
Leave the PowerShell window open while you use the ERP. Press **Ctrl+C** in it to stop.

| Want | Command |
| --- | --- |
| Start normally (backend + background jobs) | `.\scripts\windows\start.ps1` |
| Also run the live-reloading frontend (for changing the screens/styles) | `.\scripts\windows\start.ps1 -Dev` |
| Use another port | `.\scripts\windows\start.ps1 -Port 8080` |

If the database is not running, `start.ps1` starts it. If it cannot, open PowerShell as administrator and run
`Start-Service MariaDB`.

## 3. Commands you may need

Run these inside the project folder (`cd $env:USERPROFILE\inventory-management`).

```powershell
# Get the latest code and update everything
git pull
composer install
php artisan migrate --force
npm ci
npm run build
php artisan optimize:clear

# Run the automatic tests (they use the separate erp_test database)
php artisan test --compact
php artisan test --compact --filter=Inventory
vendor\bin\pest --parallel --processes=4

# Check the code style
vendor\bin\pint --dirty

# Look at the error log
Get-Content storage\logs\laravel.log -Tail 50

# Start again from an empty ERP (WIPES ALL DATA in the "erp" database)
powershell -ExecutionPolicy Bypass -File .\scripts\windows\setup.ps1 -Reinstall
```

Your database password is saved in `C:\Users\<you>\aureus-erp-db.txt`. Do not share it.

## 4. What to try first

1. **Inventory → Products** and **Warehouses**: create a product.
2. **Purchase**: create a vendor and a purchase order, confirm it, receive the goods.
3. **Sales**: create a customer and a sales order, confirm it and deliver.
4. **Invoices**: create the invoice and register a payment.
5. **Settings**: switch optional features on or off (lots, packages, routes, variants).

## 5. If something goes wrong

| What you see | What to do |
| --- | --- |
| `running scripts is disabled` | Always start the scripts with `powershell -ExecutionPolicy Bypass -File ...` as shown above. |
| `winget is not recognized` | Install **App Installer** from the Microsoft Store, then try again. |
| `git/php/node was installed but is not visible` | Close PowerShell, open a **new** administrator PowerShell, and run `setup.ps1` again. It continues where it stopped. |
| `Access denied for user root` | The saved password does not match. Delete `aureus-erp-db.txt` from your user folder and run `setup.ps1` again; it asks for the MariaDB root password. |
| Page opens but looks unstyled | Run `npm run build` in the project folder. |
| `port 8000 is already in use` | Use `start.ps1 -Port 8080` and open `http://127.0.0.1:8080/admin`. |
| Anything else | Copy the red error text and send it to me. |
