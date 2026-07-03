$sshUser = "root"
$sshHost = "144.225.8.129"
$sshPass = "Ayaan_123#@!"
$projectDir = "/var/www/onclick2buy"

$secPass = ConvertTo-SecureString $sshPass -AsPlainText -Force
$cred = New-Object System.Management.Automation.PSCredential("$sshUser@$sshHost", $secPass)

# Use plink-style approach via .NET SSH
# Alternative: use ssh command with Process and stdin
$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = "ssh"
$psi.Arguments = "-o StrictHostKeyChecking=no -tt $sshUser@$sshHost"
$psi.UseShellExecute = $false
$psi.RedirectStandardInput = $true
$psi.RedirectStandardOutput = $true
$psi.RedirectStandardError = $true
$psi.CreateNoWindow = $true

$proc = [System.Diagnostics.Process]::Start($psi)
Start-Sleep -Milliseconds 2000

# Send password
$proc.StandardInput.WriteLine($sshPass)
Start-Sleep -Milliseconds 1000

# Send commands
$commands = @(
    "cd $projectDir",
    "git pull origin main 2>&1",
    "composer install --no-dev --optimize-autoloader 2>&1",
    "php artisan migrate --force 2>&1",
    "npm install 2>&1",
    "npm run build 2>&1",
    "php artisan optimize:clear 2>&1",
    "chown -R www-data:www-data storage bootstrap/cache 2>&1",
    "exit"
)

foreach ($cmd in $commands) {
    $proc.StandardInput.WriteLine($cmd)
    Start-Sleep -Milliseconds 500
}

Start-Sleep -Seconds 30
$proc.StandardInput.WriteLine("exit")
$proc.WaitForExit(10000)
$output = $proc.StandardOutput.ReadToEnd()
$error = $proc.StandardError.ReadToEnd()
Write-Output "OUTPUT: $output"
if ($error) { Write-Output "ERROR: $error" }
