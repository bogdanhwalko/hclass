<#
.SYNOPSIS
  One-command release for HClass.
  Builds frontend assets, commits, pushes, and (optionally) runs deploy.sh on the host.

.EXAMPLE
  .\release.ps1 "fix: calendar modal"
      Build + commit + push only.

.EXAMPLE
  .\release.ps1 "fix: calendar modal" -Ssh user@host -Path /home/user/hclass
      Also SSH into the host and run deploy.sh there.

  Tip: set defaults once so you can just run  .\release.ps1 "msg"
      $env:HCLASS_SSH  = "user@host"
      $env:HCLASS_PATH = "/home/user/hclass"
#>
param(
    [Parameter(Mandatory = $true, Position = 0)]
    [string]$Message,

    [string]$Ssh  = $env:HCLASS_SSH,
    [string]$Path = $env:HCLASS_PATH
)

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "==> Building frontend assets..." -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) { throw "npm run build failed" }

Write-Host "==> Committing & pushing..." -ForegroundColor Cyan
git add -A
# Commit only if there is something staged.
git diff --cached --quiet
if ($LASTEXITCODE -ne 0) {
    git commit -m $Message
    if ($LASTEXITCODE -ne 0) { throw "git commit failed" }
} else {
    Write-Host "    (nothing to commit, pushing existing HEAD)" -ForegroundColor DarkGray
}
git push origin main
if ($LASTEXITCODE -ne 0) { throw "git push failed" }

if ($Ssh -and $Path) {
    Write-Host "==> Deploying on $Ssh ..." -ForegroundColor Cyan
    ssh $Ssh "cd '$Path' && bash deploy.sh"
    if ($LASTEXITCODE -ne 0) { throw "remote deploy failed" }
    Write-Host "[OK] Released to $Ssh" -ForegroundColor Green
} else {
    Write-Host "[OK] Pushed. Now run on the server:  cd <path> && bash deploy.sh" -ForegroundColor Green
    Write-Host "     (set `$env:HCLASS_SSH and `$env:HCLASS_PATH to auto-deploy next time)" -ForegroundColor DarkGray
}
