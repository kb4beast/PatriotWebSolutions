[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$archive = Join-Path $projectRoot 'dist/patriot-web-solutions-release-1.0.0.zip'

if (-not (Test-Path -LiteralPath $archive)) {
    throw 'Release archive is missing. Run npm run build first.'
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Get-StreamSha256([System.IO.Stream] $Stream) {
    $algorithm = [System.Security.Cryptography.SHA256]::Create()
    try {
        return ([System.BitConverter]::ToString($algorithm.ComputeHash($Stream))).Replace('-', '').ToLowerInvariant()
    } finally {
        $algorithm.Dispose()
    }
}

$zip = [System.IO.Compression.ZipFile]::OpenRead($archive)
try {
    $names = @($zip.Entries | ForEach-Object { $_.FullName })
    if (@($names | Select-Object -Unique).Count -ne $names.Count) {
        throw 'Archive contains duplicate entry names.'
    }
    foreach ($name in $names) {
        if ($name.Contains('\') -or -not $name.StartsWith('patriot-web-solutions/')) {
            throw "Unsafe or invalid archive entry: $name"
        }
        if (@($name.Split('/') | Where-Object { $_ -eq '..' }).Count -gt 0) {
            throw "Path traversal segment in archive entry: $name"
        }
    }

    $manifestEntry = $zip.GetEntry('patriot-web-solutions/release-manifest.json')
    if ($null -eq $manifestEntry) {
        throw 'Archive manifest entry is missing.'
    }
    $reader = New-Object System.IO.StreamReader($manifestEntry.Open())
    try {
        $manifest = $reader.ReadToEnd() | ConvertFrom-Json
    } finally {
        $reader.Dispose()
    }

    if ($names.Count -ne (@($manifest.files).Count + 1)) {
        throw "Archive entry count does not match its manifest: $($names.Count) entries."
    }
    foreach ($file in $manifest.files) {
        $entryName = 'patriot-web-solutions/' + [string] $file.path
        $entry = $zip.GetEntry($entryName)
        if ($null -eq $entry) {
            throw "Manifest file missing from archive: $entryName"
        }
        if ($entry.Length -ne [long] $file.bytes) {
            throw "Manifest byte count mismatch: $entryName"
        }
        $stream = $entry.Open()
        try {
            $digest = Get-StreamSha256 $stream
        } finally {
            $stream.Dispose()
        }
        if ($digest -ne [string] $file.sha256) {
            throw "Manifest hash mismatch: $entryName"
        }
    }

    [ordered]@{
        status = 'PASS'
        archive_entries = $names.Count
        verified_payload_files = @($manifest.files).Count
        top_level_directory = 'patriot-web-solutions/'
        path_safety = 'PASS'
        manifest_hashes = 'PASS'
    } | ConvertTo-Json
} finally {
    $zip.Dispose()
}
