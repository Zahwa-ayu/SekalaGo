<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? config('app.name') . ' - Reservasi Hotel & Sewa Kendaraan Terpercaya' }}</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="{{ asset('images/Untitled_design-removebg-preview.ico') }}">

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Inter', 'sans-serif'] },
                colors: {
                    sagara: {
                        dark: '#031E38', navy: '#0b3c5d', blue: '#0077b6',
                        teal: '#00b4d8', sky: '#caf0f8', gold: '#f4a261', coral: '#e76f51',
                    }
                }
            }
        }
    }
</script>
<style>
    .hero-bg { background: linear-gradient(180deg, #031e38 0%, #084c77 55%, #0077b6 85%, #e0f2fe 100%); }
    .glass-card { background: rgba(255,255,255,.95); backdrop-filter: blur(12px); }
</style>