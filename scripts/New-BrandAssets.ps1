[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$dir = Join-Path $projectRoot 'release-plugin/patriot-web-solutions/payload/theme/patriot-web-solutions/assets/images'
Add-Type -AssemblyName System.Drawing

$navy = [System.Drawing.Color]::FromArgb(7, 24, 36)
$red = [System.Drawing.Color]::FromArgb(166, 54, 50)
$paper = [System.Drawing.Color]::FromArgb(245, 241, 232)
$blue = [System.Drawing.Color]::FromArgb(43, 100, 124)
$gold = [System.Drawing.Color]::FromArgb(214, 178, 110)
$muted = [System.Drawing.Color]::FromArgb(200, 212, 217)
$white = [System.Drawing.Color]::White

$bmp = New-Object System.Drawing.Bitmap(1200, 630)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = 'AntiAlias'
$g.TextRenderingHint = 'AntiAliasGridFit'
$g.Clear($navy)
$g.FillRectangle((New-Object System.Drawing.SolidBrush($red)), 0, 0, 312, 10)
$g.FillRectangle((New-Object System.Drawing.SolidBrush($paper)), 312, 0, 48, 10)
$g.FillRectangle((New-Object System.Drawing.SolidBrush($blue)), 360, 0, 840, 10)
$fontKicker = New-Object System.Drawing.Font('Segoe UI', 21, [System.Drawing.FontStyle]::Bold)
$g.DrawString('K I L L E E N ,   T E X A S', $fontKicker, (New-Object System.Drawing.SolidBrush($gold)), 96, 150)
$fontTitle = New-Object System.Drawing.Font('Georgia', 68, [System.Drawing.FontStyle]::Regular)
$g.DrawString('Patriot Web Solutions', $fontTitle, (New-Object System.Drawing.SolidBrush($white)), 88, 196)
$fontLine = New-Object System.Drawing.Font('Georgia', 40, [System.Drawing.FontStyle]::Italic)
$g.DrawString('Show the work.', $fontLine, (New-Object System.Drawing.SolidBrush($paper)), 96, 330)
$fontSub = New-Object System.Drawing.Font('Segoe UI', 20)
$g.DrawString('AI learning for military members, veterans, and their families', $fontSub, (New-Object System.Drawing.SolidBrush($muted)), 96, 436)
$g.FillRectangle((New-Object System.Drawing.SolidBrush($gold)), 96, 500, 300, 3)
$g.Dispose()
$bmp.Save((Join-Path $dir 'og-card.png'), [System.Drawing.Imaging.ImageFormat]::Png)
$bmp.Dispose()

$logo = New-Object System.Drawing.Bitmap(512, 512)
$g2 = [System.Drawing.Graphics]::FromImage($logo)
$g2.SmoothingMode = 'AntiAlias'
$g2.TextRenderingHint = 'AntiAliasGridFit'
$g2.Clear($navy)
$g2.DrawRectangle((New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(143, 255, 255, 255), 6)), 86, 86, 340, 340)
$fontMark = New-Object System.Drawing.Font('Segoe UI', 96, [System.Drawing.FontStyle]::Bold)
$g2.DrawString('P', $fontMark, (New-Object System.Drawing.SolidBrush($white)), 118, 176)
$g2.DrawString('W', $fontMark, (New-Object System.Drawing.SolidBrush($white)), 252, 176)
$points = [System.Drawing.Point[]]@(
    (New-Object System.Drawing.Point(262, 140)),
    (New-Object System.Drawing.Point(276, 140)),
    (New-Object System.Drawing.Point(250, 372)),
    (New-Object System.Drawing.Point(236, 372))
)
$g2.FillPolygon((New-Object System.Drawing.SolidBrush($red)), $points)
$g2.Dispose()
$logo.Save((Join-Path $dir 'logo.png'), [System.Drawing.Imaging.ImageFormat]::Png)
$logo.Dispose()

Get-ChildItem -LiteralPath $dir | Select-Object Name, Length
