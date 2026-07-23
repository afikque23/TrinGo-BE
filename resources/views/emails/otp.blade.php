<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP - TringGo</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #0A0A0A;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #111111;
            border: 1px solid #1E2939;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .header {
            background: linear-gradient(135deg, #6B7C4F 0%, #556338 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #1E2939;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
            background: #111111;
        }
        .greeting {
            font-size: 16px;
            color: #FFFFFF;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .greeting strong {
            color: #6B7C4F;
        }
        .otp-box {
            background: #0A0A0A;
            border: 2px solid #6B7C4F;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            color: #99A1AF;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            color: #6B7C4F;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
            text-shadow: 0 0 10px rgba(107, 124, 79, 0.3);
        }
        .expiry {
            color: #99A1AF;
            font-size: 13px;
            margin-top: 15px;
            font-weight: normal;
        }
        .info {
            background: #1A1A1A;
            border: 1px solid #364153;
            border-left: 4px solid #6B7C4F;
            padding: 20px;
            margin: 25px 0;
            border-radius: 10px;
        }
        .info p {
            margin: 8px 0;
            color: #99A1AF;
            font-size: 14px;
            line-height: 1.6;
        }
        .info strong {
            color: #FFFFFF;
        }
        .footer {
            background: #0A0A0A;
            border-top: 1px solid #1E2939;
            padding: 25px;
            text-align: center;
            color: #6A7282;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        .note {
            color: #99A1AF;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏍️ TringGo</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                @if($userName)
                    <p>Halo <strong>{{ $userName }}</strong>,</p>
                @else
                    <p>Halo,</p>
                @endif
                
                @if($type === 'password_reset')
                    <p>Kami menerima permintaan untuk mereset password akun TringGo Anda. Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>
                @elseif($type === 'phone_verification')
                    <p>Gunakan kode OTP berikut untuk memverifikasi nomor telepon Anda di aplikasi TringGo:</p>
                @else
                    <p>Terima kasih telah mendaftar di TringGo! Gunakan kode OTP berikut untuk memverifikasi email Anda dan mengaktifkan akun:</p>
                @endif
            </div>

            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi OTP</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry">⏱️ Berlaku selama 10 menit</div>
            </div>

            <div class="info">
                <p><strong>⚠️ Informasi Keamanan:</strong></p>
                <p>• Jangan bagikan kode ini kepada siapapun termasuk staff TringGo</p>
                <p>• Kode OTP hanya berlaku untuk sekali penggunaan</p>
                <p>• Kode akan kadaluarsa dalam 10 menit</p>
                <p>• Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini</p>
            </div>

            @if($type === 'password_reset')
                <p class="note">
                    Jika Anda tidak meminta reset password, silakan abaikan email ini dan password Anda akan tetap aman. Untuk keamanan tambahan, kami sarankan untuk mengubah password secara berkala.
                </p>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} TringGo - Motorcycle Management System</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
