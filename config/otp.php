<?php

return [
    // OTP berlaku (menit)
    'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 10),

    // Batas percobaan OTP per kode
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),

    // Untuk development saja: kalau true, backend akan menyertakan OTP di response JSON.
    // Default: false (opt-in).
    'expose_in_response' => (bool) env('OTP_EXPOSE_IN_RESPONSE', false),
];
