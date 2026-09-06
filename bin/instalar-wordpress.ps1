# Instalador reproducible del sitio CDN Santiago para colaboradores.
# Reproduce: descarga WordPress es_ES, crea BD, genera wp-config, instala y siembra contenido.
#
# Uso (desde la raiz del repo clonado en C:\xampp\htdocs\sercotec-cdn):
#   powershell -ExecutionPolicy Bypass -File .\bin\instalar-wordpress.ps1

param(
    [string]$RutaSitio = (Split-Path -Parent $PSScriptRoot),
    [string]$UrlSitio  = "http://localhost/sercotec-cdn",
    [string]$NombreBD  = "sercotec_cdn"
)

$ErrorActionPreference = "Stop"
$Php     = "C:\xampp\php\php.exe"
$Mysql   = "C:\xampp\mysql\bin\mysql.exe"
$Temp    = Join-Path $env:TEMP "opencode\wp-inst"
$Zip     = Join-Path $Temp "latest-es_ES.zip"
$Admin   = "admin"
$AdminPass = "CN-Santiago!" + ([System.Guid]::NewGuid().ToString("N").Substring(0,10))

Write-Host "== Instalador CDN Santiago =="
Write-Host "Sitio : $RutaSitio"
Write-Host "URL   : $UrlSitio"
Write-Host "BD    : $NombreBD"

if (-not (Test-Path $Php))   { throw "No se encuentra PHP en $Php" }
if (-not (Test-Path $Mysql)) { throw "No se encuentra MySQL en $Mysql" }

# 1) Descargar y extraer WordPress es_ES
if (-not (Test-Path $Zip)) {
    New-Item -ItemType Directory -Path $Temp -Force | Out-Null
    Write-Host "Descargando WordPress es_ES..."
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri "https://es.wordpress.org/latest-es_ES.zip" -OutFile $Zip -UseBasicParsing
}
if (-not (Test-Path (Join-Path $Temp "wordpress"))) {
    Expand-Archive -LiteralPath $Zip -DestinationPath $Temp -Force
}
Write-Host "Copiando WordPress a $RutaSitio ..."
Copy-Item -Path (Join-Path $Temp "wordpress\*") -Destination $RutaSitio -Recurse -Force

# 2) wp-config.php
$wpConfig = Join-Path $RutaSitio "wp-config.php"
if (-not (Test-Path $wpConfig)) {
    Write-Host "Generando wp-config.php..."
    $sample = Get-Content -LiteralPath (Join-Path $RutaSitio "wp-config-sample.php") -Raw
    foreach ($k in @("AUTH_KEY","SECURE_AUTH_KEY","LOGGED_IN_KEY","NONCE_KEY","AUTH_SALT","SECURE_AUTH_SALT","LOGGED_IN_SALT","NONCE_SALT")) {
        $rnd = -join ((48..57)+(65..90)+(97..122) | Get-Random -Count 60 | ForEach-Object {[char]$_})
        $sample = $sample -replace ("define\( '$k',\s*'[^']*' \);"), ("define( '$k', '$rnd' );")
    }
    $sample = $sample.Replace("database_name_here","$NombreBD").Replace("username_here","root").Replace("password_here","").Replace("localhost","localhost")
    $extra = "`n/* Ajustes locales del proyecto. */`ndefine( 'FS_METHOD', 'direct' );`ndefine( 'WP_ENVIRONMENT_TYPE', 'local' );`ndefine( 'DISALLOW_FILE_EDIT', true );`n"
    $pos = $sample.IndexOf("/* That's all, stop editing!")
    $sample = $sample.Substring(0,$pos) + $extra + $sample.Substring($pos)
    [System.IO.File]::WriteAllText($wpConfig, $sample, (New-Object System.Text.UTF8Encoding($false)))
}

# 3) Crear base de datos
Write-Host "Creando base de datos $NombreBD ..."
& $Mysql -u root -e "CREATE DATABASE IF NOT EXISTS $NombreBD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($LASTEXITCODE -ne 0) { throw "No se pudo crear la BD (¿root tiene password?). Crea $NombreBD en phpMyAdmin." }

# 4) Instalar WordPress por HTTP
Write-Host "Instalando WordPress (puede tardar unos segundos)..."
$form = "weblog_title=" + [uri]::EscapeDataString("Centro de Desarrollo de Negocios Sercotec Santiago") +
        "&user_name=$Admin&admin_password=$AdminPass&admin_password2=$AdminPass&admin_email=admin@example.com&Submit=" +
        [uri]::EscapeDataString("Instalar WordPress") + "&language="
$null = curl.exe -s -o NUL -X POST "$UrlSitio/wp-admin/install.php?step=2" --data $form
Start-Sleep -Seconds 2

# 5) Semilla (activa theme/plugin, contenido, app password)
Write-Host "Sembrando contenido (bin/seed.php)..."
& $Php (Join-Path $PSScriptRoot "seed.php")

Write-Host ""
Write-Host "=== Instalacion lista ==="
Write-Host "Sitio : $UrlSitio"
Write-Host "Admin : $Admin"
Write-Host "Clave : $AdminPass   (cambiala tras la primera entrada)"
Write-Host ""
Write-Host "IMPORTANTE: guarda esta clave; no se vuelve a mostrar."
