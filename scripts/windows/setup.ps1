<#
.SYNOPSIS
    One-time setup of the ERP on Windows 10/11: installs the tools, the database and the app.

.DESCRIPTION
    Run from the project folder, in a PowerShell window opened with "Run as administrator":

        powershell -ExecutionPolicy Bypass -File .\scripts\windows\setup.ps1

    It is safe to run again: every step checks first and skips what is already done.
    Add -Reinstall to wipe the ERP database and install it again from scratch.
#>
param(
    [switch]$Reinstall
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$ToolsDir    = Join-Path $env:USERPROFILE 'aureus-erp-tools'
$SecretsFile = Join-Path $env:USERPROFILE 'aureus-erp-db.txt'

$Modules = @(
    'accounts', 'accounting', 'barcode', 'blogs', 'contacts', 'employees', 'inventories', 'invoices',
    'maintenance', 'manufacturing', 'payments', 'products', 'projects', 'purchases', 'recruitments',
    'sales', 'time-off', 'timesheets', 'website'
)

function Write-Step([string]$Text) { Write-Host "`n=== $Text ===" -ForegroundColor Cyan }
function Write-Ok([string]$Text)   { Write-Host "  OK  $Text" -ForegroundColor Green }

function Invoke-Checked {
    param([Parameter(Mandatory)][string]$Command, [string[]]$Arguments = @())
    & $Command @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed (exit code $LASTEXITCODE): $Command $($Arguments -join ' ')"
    }
}

function Update-SessionPath {
    $machine = [Environment]::GetEnvironmentVariable('Path', 'Machine')
    $user    = [Environment]::GetEnvironmentVariable('Path', 'User')
    $env:Path = "$machine;$user;$ToolsDir"
}

function Test-Command([string]$Name) { [bool](Get-Command $Name -ErrorAction SilentlyContinue) }

function Install-WingetPackage([string]$Id, [string]$Label, [string]$Override = '') {
    Write-Host "  Installing $Label (this can take a few minutes)..."
    $wingetArgs = @('install', '--id', $Id, '-e', '--silent', '--accept-package-agreements', '--accept-source-agreements')
    if ($Override) { $wingetArgs += @('--override', $Override) }
    & winget @wingetArgs
    # winget returns a non-zero code when the package is already installed, so only warn.
    if ($LASTEXITCODE -ne 0) { Write-Host "  (winget exit code $LASTEXITCODE for $Label - continuing, it will be verified below)" -ForegroundColor Yellow }
    Update-SessionPath
}

function Set-EnvValue([string]$File, [string]$Key, [string]$Value) {
    $lines = [System.Collections.Generic.List[string]](Get-Content -LiteralPath $File)
    $pattern = '^\s*#?\s*' + [regex]::Escape($Key) + '='
    $index = -1
    for ($i = 0; $i -lt $lines.Count; $i++) { if ($lines[$i] -match $pattern) { $index = $i; break } }
    if ($index -ge 0) { $lines[$index] = "$Key=$Value" } else { $lines.Add("$Key=$Value") }
    [System.IO.File]::WriteAllLines($File, $lines, (New-Object System.Text.UTF8Encoding($false)))
}

function New-RandomPassword { -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 20 | ForEach-Object { [char]$_ }) }

# ---------------------------------------------------------------------------------------------
Write-Step '1/9 Checking your computer'
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
if (-not (New-Object Security.Principal.WindowsPrincipal($identity)).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Please close this window and open PowerShell with "Run as administrator", then run the script again.'
}
if (-not (Test-Command 'winget')) {
    throw 'winget was not found. Install "App Installer" from the Microsoft Store, then run this script again.'
}
if (-not (Test-Path (Join-Path $ProjectRoot 'artisan'))) { throw "This does not look like the project folder: $ProjectRoot" }
New-Item -ItemType Directory -Force -Path $ToolsDir | Out-Null
Update-SessionPath
Write-Ok "Project folder: $ProjectRoot"

# ---------------------------------------------------------------------------------------------
Write-Step '2/9 Installing tools (Git, Node.js, PHP 8.3, MariaDB, Composer)'
if (-not (Test-Command 'git'))  { Install-WingetPackage 'Git.Git' 'Git' }
if (-not (Test-Command 'node')) { Install-WingetPackage 'OpenJS.NodeJS.LTS' 'Node.js' }
if (-not (Test-Command 'php'))  { Install-WingetPackage 'PHP.PHP.8.3' 'PHP 8.3' }

$mariaService = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
if (-not $mariaService) {
    $rootPassword = New-RandomPassword
    Install-WingetPackage 'MariaDB.Server' 'MariaDB database' "/qn SERVICENAME=MariaDB PORT=3306 PASSWORD=$rootPassword UTF8=1"
    Set-Content -LiteralPath $SecretsFile -Value $rootPassword -Encoding ASCII
    Write-Ok "Database password saved to $SecretsFile"
} else {
    Write-Ok 'MariaDB is already installed'
}

foreach ($tool in 'git', 'node', 'php') {
    if (-not (Test-Command $tool)) {
        throw "$tool was installed but is not visible yet. Close this window, open a NEW administrator PowerShell and run the script again."
    }
}

if (-not (Test-Command 'composer')) {
    Write-Host '  Installing Composer...'
    $phar = Join-Path $ToolsDir 'composer.phar'
    Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile $phar -UseBasicParsing
    Set-Content -LiteralPath (Join-Path $ToolsDir 'composer.bat') -Encoding ASCII -Value "@echo off`r`nphp `"%~dp0composer.phar`" %*"
    $userPath = [Environment]::GetEnvironmentVariable('Path', 'User')
    if ($userPath -notlike "*$ToolsDir*") { [Environment]::SetEnvironmentVariable('Path', "$userPath;$ToolsDir", 'User') }
    Update-SessionPath
}
Write-Ok ((& php -v)[0])
Write-Ok ((& node -v))

# ---------------------------------------------------------------------------------------------
Write-Step '3/9 Configuring PHP'
$phpBinary = (& php -r 'echo PHP_BINARY;')
$phpDir    = Split-Path -Parent $phpBinary
$phpIni    = Join-Path $phpDir 'php.ini'
if (-not (Test-Path $phpIni)) {
    $template = Join-Path $phpDir 'php.ini-development'
    if (-not (Test-Path $template)) { throw "Could not find php.ini-development in $phpDir" }
    Copy-Item $template $phpIni
}
$ini = Get-Content -LiteralPath $phpIni -Raw
$ini = $ini -replace '(?m)^[ \t]*;?[ \t]*extension_dir[ \t]*=.*$', ('extension_dir = "' + (Join-Path $phpDir 'ext') + '"')
foreach ($extension in 'curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_mysql', 'zip', 'sodium', 'exif') {
    $ini = $ini -replace "(?m)^[ \t]*;[ \t]*extension=$extension[ \t]*\r?$", "extension=$extension"
}
$ini = $ini -replace '(?m)^[ \t]*;?[ \t]*memory_limit[ \t]*=.*$', 'memory_limit = 512M'
[System.IO.File]::WriteAllText($phpIni, $ini, (New-Object System.Text.UTF8Encoding($false)))

$loaded = (& php -m) -join ' '
$missing = @('curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_mysql', 'zip', 'bcmath') | Where-Object { $loaded -notmatch "(?i)\b$_\b" }
if ($missing) { throw "These PHP extensions are not loaded: $($missing -join ', '). Check $phpIni" }
Write-Ok "PHP extensions loaded (php.ini: $phpIni)"

# ---------------------------------------------------------------------------------------------
Write-Step '4/9 Starting the database and creating the ERP databases'
$service = Get-Service -Name 'MariaDB' -ErrorAction Stop
if ($service.Status -ne 'Running') { Start-Service 'MariaDB' }

$mysql = Get-ChildItem 'C:\Program Files\MariaDB*\bin\mysql.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $mysql) { throw 'Could not find mysql.exe under C:\Program Files\MariaDB*. Was MariaDB installed?' }

if (Test-Path $SecretsFile) {
    $dbPassword = (Get-Content -LiteralPath $SecretsFile -Raw).Trim()
} else {
    $secure = Read-Host 'Enter the MariaDB root password (set when MariaDB was installed)' -AsSecureString
    $dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure))
    Set-Content -LiteralPath $SecretsFile -Value $dbPassword -Encoding ASCII
}

$createSql = 'CREATE DATABASE IF NOT EXISTS erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; ' +
             'CREATE DATABASE IF NOT EXISTS erp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
Invoke-Checked $mysql.FullName @('-uroot', "-p$dbPassword", '-h127.0.0.1', '-e', $createSql)
Write-Ok 'Databases "erp" and "erp_test" are ready'

# ---------------------------------------------------------------------------------------------
Write-Step '5/9 Installing the backend (Composer packages) - this takes a few minutes'
Set-Location $ProjectRoot
Invoke-Checked 'composer' @('install', '--no-interaction')

$envFile = Join-Path $ProjectRoot '.env'
if (-not (Test-Path $envFile)) { Copy-Item (Join-Path $ProjectRoot '.env.example') $envFile }
$settings = [ordered]@{
    APP_URL = 'http://127.0.0.1:8000'; APP_TIMEZONE = 'Asia/Kolkata'; APP_CURRENCY = 'INR'
    DB_CONNECTION = 'mysql'; DB_HOST = '127.0.0.1'; DB_PORT = '3306'; DB_DATABASE = 'erp'
    DB_USERNAME = 'root'; DB_PASSWORD = $dbPassword
}
foreach ($key in $settings.Keys) { Set-EnvValue $envFile $key $settings[$key] }
if ((Get-Content -LiteralPath $envFile -Raw) -notmatch '(?m)^APP_KEY=base64:') { Invoke-Checked 'php' @('artisan', 'key:generate', '--force') }

$testEnv = Join-Path $ProjectRoot '.env.testing'
Copy-Item $envFile $testEnv -Force
Set-EnvValue $testEnv 'APP_ENV' 'testing'
Set-EnvValue $testEnv 'APP_TIMEZONE' 'UTC'
Set-EnvValue $testEnv 'DB_DATABASE' 'erp_test'
Invoke-Checked 'php' @('artisan', 'config:clear')
Write-Ok 'Backend configured (.env and .env.testing)'

# ---------------------------------------------------------------------------------------------
Write-Step '6/9 Installing the ERP (tables, starter data, admin user)'
$installedMarker = Join-Path $ProjectRoot 'storage\installed'
if ((Test-Path $installedMarker) -and -not $Reinstall) {
    Write-Ok 'ERP is already installed (run with -Reinstall to wipe it and start again)'
} else {
    $adminName  = Read-Host 'Admin name (for example: Your Name)'
    $adminEmail = Read-Host 'Admin email (this is your login)'
    $securePass = Read-Host 'Admin password (choose a strong one)' -AsSecureString
    $adminPass  = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
    if (-not $adminName -or -not $adminEmail -or $adminPass.Length -lt 8) { throw 'Name and email are required, and the password needs at least 8 characters.' }

    Invoke-Checked 'php' @('artisan', 'erp:install', '--force', '--no-interaction', '--country=IN', '--currency=INR',
        "--admin-name=$adminName", "--admin-email=$adminEmail", "--admin-password=$adminPass")

    foreach ($module in $Modules) {
        Write-Host "  Installing module: $module"
        Invoke-Checked 'php' @('artisan', "${module}:install", '--no-interaction')
    }
    Invoke-Checked 'php' @('artisan', 'storage:link', '--force')
    Write-Ok 'ERP installed'
}

# ---------------------------------------------------------------------------------------------
Write-Step '7/9 Installing the frontend (Node packages and styles)'
Invoke-Checked 'npm' @('ci', '--no-audit', '--no-fund')
Invoke-Checked 'npm' @('run', 'build')
Write-Ok 'Frontend built'

# ---------------------------------------------------------------------------------------------
Write-Step '8/9 Final check'
Invoke-Checked 'php' @('artisan', 'about', '--only=environment')

Write-Step '9/9 Done'
Write-Host @"

  The ERP is installed.

  START IT:   powershell -ExecutionPolicy Bypass -File .\scripts\windows\start.ps1
  OPEN IT:    http://127.0.0.1:8000/admin   (log in with the email and password you just chose)

  Database password is saved in: $SecretsFile
  All commands:                  docs\WINDOWS_CHEATSHEET.md

"@ -ForegroundColor Green

$answer = Read-Host 'Start the ERP now? (Y/N)'
if ($answer -match '^[Yy]') { & (Join-Path $PSScriptRoot 'start.ps1') }
