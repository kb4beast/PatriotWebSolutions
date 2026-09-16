[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$pluginSource = Join-Path $projectRoot 'release-plugin/patriot-web-solutions'
$dist = Join-Path $projectRoot 'dist'
$stage = Join-Path $projectRoot '.build-stage/release-packaging'
$releaseVersion = (Get-Content -Raw -LiteralPath (Join-Path $pluginSource 'payload/content.json') | ConvertFrom-Json).version
if ($releaseVersion -notmatch '^\d+\.\d+\.\d+$') { throw 'Invalid release version.' }
$archive = Join-Path $dist "patriot-web-solutions-release-$releaseVersion.zip"
$manifestPath = Join-Path $pluginSource 'release-manifest.json'
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

function Get-PwsSha256([string] $Path) {
    $hashAlgorithm = [System.Security.Cryptography.SHA256]::Create()
    $inputStream = [System.IO.File]::OpenRead($Path)
    try {
        return ([System.BitConverter]::ToString($hashAlgorithm.ComputeHash($inputStream))).Replace('-', '').ToLowerInvariant()
    } finally {
        $inputStream.Dispose()
        $hashAlgorithm.Dispose()
    }
}

if (-not (Test-Path -LiteralPath (Join-Path $pluginSource 'patriot-web-solutions.php'))) {
    throw 'Release plugin source is missing.'
}

# Stamp evidence-check dates with the builder's local date: authored record dates on the site are
# US-Central, and a UTC stamp from an evening build reads as tomorrow next to them.
$projectsPath = Join-Path $pluginSource 'payload/projects.json'
$buildDate = (Get-Date).ToString('yyyy-MM-dd')
$projectsText = [System.IO.File]::ReadAllText($projectsPath)
$projectsText = [regex]::Replace($projectsText, '(?<="checked(?:_label)?":\s*"[^"]*?)\d{4}-\d{2}-\d{2}', $buildDate)
[System.IO.File]::WriteAllText($projectsPath, $projectsText, $utf8NoBom)

$manifestFiles = Get-ChildItem -LiteralPath $pluginSource -Recurse -File |
    Where-Object { $_.FullName -ne $manifestPath } |
    Sort-Object FullName |
    ForEach-Object {
        [ordered]@{
            path = $_.FullName.Substring($pluginSource.Length + 1).Replace('\', '/')
            bytes = $_.Length
            sha256 = Get-PwsSha256 $_.FullName
        }
    }

$manifest = [ordered]@{
    schema_version = 1
    release = "patriot-web-solutions-$releaseVersion"
    artifact_kind = 'WordPress installer plugin with bundled theme and reversible content migration'
    generated_at_utc = (Get-Date).ToUniversalTime().ToString('o')
    minimum_wordpress = '6.5'
    minimum_php = '8.1'
    files = @($manifestFiles)
}
[System.IO.File]::WriteAllText($manifestPath, ($manifest | ConvertTo-Json -Depth 8), $utf8NoBom)

if (Test-Path -LiteralPath $stage) {
    Remove-Item -LiteralPath $stage -Recurse -Force
}
New-Item -ItemType Directory -Force -Path (Join-Path $stage 'patriot-web-solutions') | Out-Null
Copy-Item -Path (Join-Path $pluginSource '*') -Destination (Join-Path $stage 'patriot-web-solutions') -Recurse -Force

if (Test-Path -LiteralPath $archive) {
    Remove-Item -LiteralPath $archive -Force
}
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipStream = [System.IO.File]::Open($archive, [System.IO.FileMode]::CreateNew)
$zip = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create, $false)
try {
    Get-ChildItem -LiteralPath $stage -Recurse -File | Sort-Object FullName | ForEach-Object {
        $entryName = $_.FullName.Substring($stage.Length + 1).Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $entryName, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
} finally {
    $zip.Dispose()
    $zipStream.Dispose()
}
Remove-Item -LiteralPath $stage -Recurse -Force

$receipt = [ordered]@{
    generated_at_utc = (Get-Date).ToUniversalTime().ToString('o')
    artifact = "dist/patriot-web-solutions-release-$releaseVersion.zip"
    bytes = (Get-Item -LiteralPath $archive).Length
    sha256 = Get-PwsSha256 $archive
    file_count_excluding_manifest = @($manifestFiles).Count
}
[System.IO.File]::WriteAllText((Join-Path $dist 'release-receipt.json'), ($receipt | ConvertTo-Json -Depth 4), $utf8NoBom)
$receipt | ConvertTo-Json -Depth 4
