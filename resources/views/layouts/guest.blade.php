<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Title එක page එකෙන් page එකට වෙනස් වෙන්න $title slot එකක් දැම්මා -->
    <title>{{ $title ?? 'Lumos Studio - Wedding Invitations' }}</title>
    
    <!-- Fonts & Icons (හැම Auth page එකටම මේවා ඕනේ නිසා මෙතන දැම්මා) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global CSS (හැම page එකටම පොදු දේවල් ටික මෙතන තියෙනවා) -->
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #0f0f1a; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 24px; 
            color: #e8e4dc;
        }
        body::before { 
            content: ''; 
            position: fixed; 
            inset: 0; 
            background: radial-gradient(ellipse 80% 50% at 50% 30%, rgba(201,169,110,0.07), transparent); 
            pointer-events: none; 
        }
        a { text-decoration: none; }
    </style>

    <!-- Page Specific CSS Load කරන්න Slot එකක් හැදුවා -->
    {{ $styles ?? '' }}

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

    <!-- ඔයාගේ අනිත් pages වල කෝඩ් එක (Form එක) මෙතනට තමයි එන්නේ -->
    {{ $slot }}

    <!-- Page Specific JS Load කරන්න Slot එකක් හැදුවා -->
    {{ $scripts ?? '' }}

</body>
</html>