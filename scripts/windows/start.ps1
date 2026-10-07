<#
.SYNOPSIS
    Starts the ERP (backend + queue worker, and optionally the live-reloading frontend) and opens the browser.

.DESCRIPTION
    powershell -ExecutionPolicy Bypass -File .\scripts\windows\start.ps1          # normal
    powershell -ExecutionPolicy Bypass -File .\scripts\windows\start.ps1 -Dev     # also runs the Vite dev server
    Stop everything with Ctrl+C.
#>
param(
    [switch]$Dev,
    [int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$ToolsDir    = Join-Path $env:USERPROFILE 'aureus-erp-tools'

$machine = [Environment]::GetEnvironmentVariable('Path', 'Machine')
$user    = [Environment]::GetEnvironmentVariable('Path', 'User')
$env:Path = "$machine;$user;$ToolsDir"

Set-Location $ProjectRoot

$service = Get-Service -Name 'MariaDB' -ErrorAction SilentlyContinue
if ($service -and $service.Status -ne 'Running') {
    Write-Host 'Starting the MariaDB database...'
    try { Start-Service 'MariaDB' } catch { throw 'Could not start the database. Open PowerShell as administrator and run: Start-Service MariaDB' }
}

if (-not (Test-Path (Join-Path $ProjectRoot 'node_modules\concurrently'))) { & npm ci --no-audit --no-fund }

$commands = @("php artisan serve --host=127.0.0.1 --port=$Port", 'php artisan queue:listen --tries=1 --memory=512')
$names = 'server,queue'
if ($Dev) {
    $commands += 'npm run dev'
    $names += ',vite'
}

$url = "http://127.0.0.1:$Port/admin"
Write-Host "`nStarting the ERP. Your browser will open $url in a few seconds." -ForegroundColor Green
Write-Host 'Press Ctrl+C in this window to stop it.' -ForegroundColor Yellow

Start-Job -ScriptBlock { param($u) Start-Sleep -Seconds 6; Start-Process $u } -ArgumentList $url | Out-Null

& npx concurrently -c '#93c5fd,#c4b5fd,#fdba74' --names $names @commands
