<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tip->title }} - TringGo Tips</title>

    <!-- Open Graph Meta Tags (Wajib untuk WhatsApp Preview) -->
    <meta property="og:title" content="{{ $tip->title }}" />
    <meta property="og:description" content="{{ Str::limit($tip->description, 150) }}" />
    <!-- Gunakan gambar bawaan logo jika tidak ada cover_image -->
    <meta property="og:image" content="{{ asset('images/logo.png') }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="TringGo" />

    <!-- Tailwind CSS (via CDN untuk kesederhanaan) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden m-4 border border-gray-200">
        <!-- Banner/Logo Area -->
        <div class="bg-blue-600 h-32 flex items-center justify-center p-4">
            <h1 class="text-white text-3xl font-bold tracking-tight">TringGo</h1>
        </div>

        <!-- Content Area -->
        <div class="p-6">
            <!-- Kategori / Hashtags -->
            <div class="flex flex-wrap gap-2 mb-3">
                @if($tip->hashtags)
                    @foreach(is_array($tip->hashtags) ? $tip->hashtags : json_decode($tip->hashtags, true) as $tag)
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">#{{ $tag }}</span>
                    @endforeach
                @else
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">#TipsMotor</span>
                @endif
            </div>

            <!-- Judul -->
            <h2 class="text-2xl font-bold text-gray-800 leading-tight mb-2">
                {{ $tip->title }}
            </h2>

            <!-- Penulis -->
            <div class="flex items-center text-sm text-gray-500 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Oleh <span class="font-semibold text-gray-700 ml-1">{{ $tip->user->name ?? 'Pengguna TringGo' }}</span>
            </div>

            <!-- Deskripsi -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                {{ Str::limit($tip->description, 200) }}
            </p>

            <!-- Call to Action -->
            <div class="mt-6 border-t pt-6">
                <p class="text-sm text-gray-500 text-center mb-4">Baca tips selengkapnya dan rawat motor Anda dengan lebih baik di aplikasi TringGo.</p>
                <a href="https://play.google.com/store/apps/details?id=com.afikque23.tringgo" 
                   target="_blank"
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-3 px-4 rounded-lg shadow-md transition duration-200">
                    Buka di Aplikasi TringGo
                </a>
            </div>
        </div>
    </div>

</body>
</html>
