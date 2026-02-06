<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .otp-box {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            color: #dc3545;
            font-size: 14px;
            margin-top: 15px;
            font-weight: bold;
        }
        .info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info p {
            margin: 5px 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏍️ Motorcycle Management</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                @if($userName)
                    <p>Halo <strong>{{ $userName }}</strong>,</p>
                @else
                    <p>Halo,</p>
                @endif
                
                @if($type === 'password_reset')
                    <p>Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode OTP berikut untuk melanjutkan:</p>
                @elseif($type === 'phone_verification')
                    <p>Gunakan kode OTP berikut untuk memverifikasi nomor telepon Anda:</p>
                @else
                    <p>Terima kasih telah mendaftar! Gunakan kode OTP berikut untuk memverifikasi email Anda:</p>
                @endif
            </div>

            <div class="otp-box">
                <div class="otp-label">Kode OTP Anda:</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry">⏰ Berlaku selama 10 menit</div>
            </div>

            <div class="info">
                <p><strong>⚠️ Penting:</strong></p>
                <p>• Jangan bagikan kode ini kepada siapapun</p>
                <p>• Kode hanya berlaku untuk 1x penggunaan</p>
                <p>• Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini</p>
            </div>

            @if($type === 'password_reset')
                <p style="color: #666; font-size: 14px;">
                    Jika Anda tidak meminta reset password, silakan abaikan email ini. Password Anda tidak akan berubah.
                </p>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Motorcycle Management. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
        </div>
    </div>
</body>
</html>
