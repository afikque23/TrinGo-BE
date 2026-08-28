import paramiko
import getpass
import json

hostname = "203.194.115.228"
username = "root"

print("Masukkan Password ROOT VPS untuk Analisis Mendalam:")
password = getpass.getpass()

tinker_script = """
try {
    $trip = App\\Models\\Trip::orderByDesc('id')->first();
    $points = App\\Models\\TripPoint::where('trip_id', $trip->id)->orderBy('sequence')->get();
    
    $temps = [];
    $nullCount = 0;
    foreach($points as $p) {
        if ($p->engine_temp_c !== null) {
            $temps[] = $p->engine_temp_c;
        } else {
            $nullCount++;
        }
    }
    
    $result = [
        'TRIP_ID' => $trip->id,
        'TRIP_STATUS' => $trip->status,
        'DB_AVG' => $trip->avg_temperature_c,
        'DB_MAX' => $trip->max_temperature_c,
        'DB_MIN' => $trip->min_temperature_c,
        'POINTS_TOTAL' => $points->count(),
        'POINTS_NULL_TEMP' => $nullCount,
        'POINTS_VALID_TEMP' => count($temps),
        'RAW_TEMPS' => $temps,
        'CALCULATED_MAX' => count($temps) > 0 ? max($temps) : null,
        'CALCULATED_MIN' => count($temps) > 0 ? min($temps) : null,
        'CALCULATED_AVG' => count($temps) > 0 ? round(array_sum($temps) / count($temps), 2) : null,
    ];
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (\\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
"""

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n--- 1. CEK STATUS MIGRASI ---")
    stdin, stdout, stderr = ssh.exec_command("php /var/www/motorcycle_management/artisan migrate:status")
    print(stdout.read().decode())
    
    print("\n--- 2. ANALISIS DATA PERJALANAN TERAKHIR ---")
    # Execute tinker script safely
    stdin, stdout, stderr = ssh.exec_command(f"php /var/www/motorcycle_management/artisan tinker --execute=\"{tinker_script}\"")
    
    output = stdout.read().decode()
    error = stderr.read().decode()
    
    if output:
        print(output)
    if error:
        print("TINKER ERROR:", error)
        
finally:
    ssh.close()
