param(
    [string]$Root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
)

$ErrorActionPreference = 'SilentlyContinue'
$patterns = 'eval\s*\(|gzdecode\s*\(|gzinflate\s*\(|base64_decode\s*\(|str_rot13\s*\(|assert\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\(|system\s*\('
$excludePath = '\\wp-includes\\|\\wp-admin\\includes\\class-pclzip\.php$|\\vendor\\|\\node_modules\\'

Write-Host "== WordPress Security Scan =="
Write-Host "Root: $Root"
Write-Host ""

Write-Host "== Suspicious PHP functions outside core/vendor =="
Get-ChildItem -LiteralPath $Root -Recurse -File -Filter *.php |
    Where-Object { $_.FullName -notmatch $excludePath } |
    Select-String -Pattern $patterns -AllMatches |
    Select-Object Path, LineNumber, Line |
    Format-Table -AutoSize

Write-Host ""
Write-Host "== Executable PHP under uploads =="
$uploads = Join-Path $Root 'wp-content\uploads'
if (Test-Path -LiteralPath $uploads) {
    Get-ChildItem -LiteralPath $uploads -Recurse -File -Include *.php,*.phtml,*.phar,*.php5 |
        Select-Object FullName, Length, LastWriteTime |
        Format-Table -AutoSize
} else {
    Write-Host "No uploads directory found."
}

Write-Host ""
Write-Host "== Known suspicious file names =="
Get-ChildItem -LiteralPath $Root -Recurse -File |
    Where-Object {
        $_.Name -match '^(saiga|ammika|wp-vcd|class\.plugin-modules|wp-info|wp-admin-user|radio|about)\.php$' -or
        $_.Extension -match '^\.(phtml|phar)$'
    } |
    Select-Object FullName, Length, LastWriteTime |
    Format-Table -AutoSize

Write-Host ""
Write-Host "== PHP files modified in the last 14 days =="
Get-ChildItem -LiteralPath $Root -Recurse -File -Filter *.php |
    Where-Object { $_.LastWriteTime -gt (Get-Date).AddDays(-14) } |
    Select-Object FullName, Length, LastWriteTime |
    Sort-Object LastWriteTime -Descending |
    Format-Table -AutoSize

Write-Host ""
Write-Host "== Sensitive files tracked by Git =="
if (Test-Path -LiteralPath (Join-Path $Root '.git')) {
    git -C $Root ls-files wp-config.php webhook.php deploy.sh .env .env.production 2>$null
} else {
    Write-Host "No .git directory found."
}

Write-Host ""
Write-Host "== High-risk plugins present =="
$pluginRoot = Join-Path $Root 'wp-content\plugins'
if (Test-Path -LiteralPath $pluginRoot) {
    Get-ChildItem -LiteralPath $pluginRoot -Directory |
        Where-Object { $_.Name -match 'file-manager|code-snippets|insert-php|exec|shell' } |
        Select-Object Name, FullName, LastWriteTime |
        Format-Table -AutoSize
}
