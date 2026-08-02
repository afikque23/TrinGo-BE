import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print("  HAPUS TIPE MOTOR 'adventure' DARI DATABASE VPS  ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})

    print("\n[+] Menghapus data adventure dari database VPS...")

    # Run artisan tinker to delete adventure-related data
    php_script = """
php -r "
require '/var/www/motorcycle_management/vendor/autoload.php';
\$app = require '/var/www/motorcycle_management/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find the adventure motor type
\$adventureType = DB::table('motor_types')->where('slug', 'adventure')->first();

if (\$adventureType) {
    // Get all component_configs for adventure
    \$componentIds = DB::table('component_configs')
        ->where('motor_type_id', \$adventureType->id)
        ->pluck('id');

    // Delete fuzzy_rules for adventure components
    \$rulesDeleted = DB::table('fuzzy_rules')
        ->whereIn('component_config_id', \$componentIds)
        ->delete();
    echo '[+] Fuzzy rules dihapus: ' . \$rulesDeleted . PHP_EOL;

    // Delete fuzzy_variables for adventure components
    \$varsDeleted = DB::table('fuzzy_variables')
        ->whereIn('component_config_id', \$componentIds)
        ->delete();
    echo '[+] Fuzzy variables dihapus: ' . \$varsDeleted . PHP_EOL;

    // Delete component_configs for adventure
    \$configsDeleted = DB::table('component_configs')
        ->where('motor_type_id', \$adventureType->id)
        ->delete();
    echo '[+] Component configs dihapus: ' . \$configsDeleted . PHP_EOL;

    // Delete the motor type itself
    DB::table('motor_types')->where('slug', 'adventure')->delete();
    echo '[+] Motor type adventure berhasil dihapus!' . PHP_EOL;
} else {
    echo '[i] Motor type adventure tidak ditemukan di database.' . PHP_EOL;
}

// Also clean up any motors with tipe_motor = adventure (set to null/matic)
\$motorsUpdated = DB::table('motors')
    ->where('tipe_motor', 'adventure')
    ->update(['tipe_motor' => 'matic']);
echo '[+] Motors dengan tipe adventure direset ke matic: ' . \$motorsUpdated . PHP_EOL;

// Clean recommendation cache
DB::table('ai_recommendation_caches')->truncate();
echo '[+] AI recommendation cache dibersihkan.' . PHP_EOL;
"
"""
    stdin, stdout, stderr = client.exec_command(php_script)
    output = stdout.read().decode()
    err = stderr.read().decode()
    if output:
        print(output)
    if err:
        print("[Log]:", err[:500])

    print("\n=====================================================")
    print("  🎉 SELESAI! Tipe motor 'adventure' telah        ")
    print("  dihapus dari database VPS.                      ")
    print("=====================================================")
    client.close()
except Exception as e:
    print(f"\n[ERROR] Gagal: {e}")
