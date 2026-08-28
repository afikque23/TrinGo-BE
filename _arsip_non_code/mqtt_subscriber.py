import json
import paho.mqtt.client as mqtt
from datetime import datetime

# --- Konfigurasi MQTT ---
MQTT_BROKER = "tringgo.site"
MQTT_PORT = 1883
MQTT_USERNAME = "tringgo_mqtt"
MQTT_PASSWORD = "erenvsreiner"
MQTT_TOPIC = "vehicle/+/telemetry"

# Callback ketika berhasil terhubung ke broker
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"✅ Terhubung ke MQTT Broker ({MQTT_BROKER}:{MQTT_PORT})")
        # Subscribe ke topik dengan wildcard
        client.subscribe(MQTT_TOPIC)
        print(f"📡 Mendengarkan topik: {MQTT_TOPIC}\n")
    else:
        print(f"❌ Gagal terhubung ke MQTT Broker. Return code: {rc}")

# Callback ketika menerima pesan (data) dari topik yang di-subscribe
def on_message(client, userdata, msg):
    try:
        # Decode payload JSON dari byte ke string
        payload = msg.payload.decode('utf-8')
        
        # Parsing string JSON menjadi dictionary Python
        data = json.loads(payload)
        
        # --- Ekstrak data ---
        device_id = data.get("device_id", "Unknown")
        timestamp = data.get("timestamp", 0)
        lat = data.get("lat", 0.0)
        lng = data.get("lng", 0.0)
        speed = data.get("speed_kmh", 0.0)
        address = data.get("address", "Tidak diketahui")
        maps_url = data.get("maps_url", "-")
        
        # --- Cetak ke Console / Log ---
        print(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] 📥 Data masuk pada topik: {msg.topic}")
        print("-" * 55)
        print(f"🏍️  Device ID  : {device_id}")
        print(f"📍 Koordinat  : {lat}, {lng}")
        print(f"🗺️  Alamat     : {address}")
        print(f"💨 Kecepatan  : {speed} km/jam")
        print(f"🔗 Google Maps: {maps_url}")
        print("-" * 55 + "\n")
        
        # =========================================================
        # TODO: Tambahkan kode untuk menyimpan 'data' ke database di sini
        # Contoh (jika menggunakan SQLAlchemy atau pymysql):
        # insert_to_db(device_id, lat, lng, speed, timestamp, ...)
        # =========================================================
        
    except json.JSONDecodeError:
        print(f"⚠️ Peringatan: Menerima payload yang bukan JSON yang valid di topik {msg.topic}")
        print(f"Payload: {msg.payload.decode('utf-8')}")
    except Exception as e:
        print(f"❌ Terjadi kesalahan saat memproses pesan: {e}")

if __name__ == "__main__":
    print("Memulai MQTT Telemetry Subscriber...")
    
    # Inisialisasi MQTT Client (Gunakan Callback API v1)
    client = mqtt.Client()
    
    # Set username dan password
    client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
    
    # Pasang fungsi callback
    client.on_connect = on_connect
    client.on_message = on_message
    
    try:
        # Mulai koneksi ke broker
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        
        # Jalankan loop tak terbatas untuk terus mendengarkan data masuk
        client.loop_forever()
        
    except KeyboardInterrupt:
        print("\n🛑 Dihentikan oleh user (Ctrl+C). Keluar...")
        client.disconnect()
    except Exception as e:
        print(f"❌ Gagal menjalankan klien MQTT: {e}")
