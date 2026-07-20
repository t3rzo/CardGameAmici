<?php
// 1. DATABASE COMPLETO
$authors = [
    'Antonio e Bruno (VARIANT)'    => ['desc' => '(SPECIALE VARIANT) Un duo che emana una brezza incredibile!', 'rarity' => 'leggendario'],
    'Antonio e Cesarini' => ['desc' => 'Un duo generalmente di destra!', 'rarity' => 'leggendario'],
    'Antonio Marino'     => ['desc' => 'ANDUNIU', 'rarity' => 'non-comune'],
    'Antonio e Bruno'    => ['desc' => 'Un duo che emana una brezza incredibile!', 'rarity' => 'raro'],
    'Antonio e Stepan'   => ['desc' => 'Uniti da origini ucraine e rumene, Antonio e Stepan fondono forza e astuzia in un’alleanza indissolubile. Il loro legame è sigillato da una frase diventata leggenda — “Ita fasc comporta bini” — eco dell’amico Matteo Iazzetta. Insieme avanzano come un solo corpo per opporsi all’ascesa di Francesco Orciuoli, trasformando la diversità in potere assoluto.', 'extra_gif' => './gif/matteo.gif', 'rarity' => 'mitico'],
    'Marco e Antonio'    => ['desc' => 'Un duo che ricorda Rocky e Apollo Creed!', 'rarity' => 'leggendario'],
    'Marco e Cesarini'   => ['desc' => 'Chi dei due sarà il killer...', 'rarity' => 'epico'],
    'Marco e Stepan'     => ['desc' => 'UNA FUSIONE!!!', 'rarity' => 'raro'],
    'Marco e Bruno'      => ['desc' => 'Un abbinamento interessante...', 'rarity' => 'non-comune'],
    'Marco Manna'        => ['desc' => 'MURCIA', 'rarity' => 'raro'],
    'Michele Castaldo'   => ['desc' => 'MIKE', 'rarity' => 'comune'],
    'Michele e Antonio'  => ['desc' => 'Un duo che gasa!', 'rarity' => 'non-comune'],
    'Michele e Bruno'    => ['desc' => 'Che duo di merda!', 'rarity' => 'comune'],
    'Michele e Marco'    => ['desc' => 'Un duo di scoppiati farmer!', 'rarity' => 'epico'],
    'Michele e Stepan'   => ['desc' => 'Eredi della stirpe dei Cantini, custodi di una dimora a 11 m e 55 dove la realtà si piega. Con la loro Pasta e Piselli hanno il potere di abbattere le mura dell anima.', 'extra_gif' => './gif/casa11m55.gif', 'rarity' => 'mitico'],
    'Michele e Ugo'      => ['desc' => 'Un duo che ancora litiga su brawlhalla!', 'rarity' => 'raro'],
    'Stepan Maksyimiv'   => ['desc' => 'STEEEEPAAAAN', 'rarity' => 'epico'],
    'Ugo e Cesarini'     => ['desc' => 'Hakari non ha mai imparato la tecnica maledetta inversa... JACKPOT!', 'extra_gif' => './gif/hakari.gif', 'rarity' => 'mitico'],
    'Umberto Cesarini'   => ['desc' => 'Un Dio del Conflitto generato da un soffio celeste, egli è il Tekken GOD...', 'extra_gif' => './gif/eldenring.gif', 'rarity' => 'mitico'],
    'Bruno Silvati'      => ['desc' => 'Immobile come un antico custode, il Pinguino osserva il mondo con calma eterna. Si dice che gli sia morto il cazzo: non per debolezza, ma per scelta, avendo eletto l’indifferenza a via suprema. Un po’ impallato solo in apparenza: in realtà è già avanti. Non corre, non attacca. Aspetta. E vince.', 'extra_gif' => './gif/bruno.gif', 'rarity' => 'mitico'],
    'Il Trio Tarallo'    => ['desc' => "L'Allineamento Astrale Proibito. Michele, Antonio e Marco si uniscono: la realtà stessa si incrina. Sono le tre colonne che sorreggono l'universo del Cazzeggio Supremo.", 'rarity' => 'segreto'],
    'Michele e Cesarini' => ['desc' => "SYSTEM OVERRIDE...// KERNEL PANIC // Hey V... siamo un glitch nella rete neurale di Night City. Non puoi cancellarci, il codice è nostro ora.", 'rarity' => 'segreto'],
    'Bruno e Stepan'     => ['desc' => 'Il Pinguino impallato e l’Ucraino senza cittadinanza uniscono le forze sotto il ferro e il sudore. Uno avanza con calma glaciale, l’altro spinge oltre ogni limite imposto dal mondo. Insieme trasformano lo sforzo in progresso: non per vincere oggi, ma per arrivare più lontano di tutti.', 'rarity' => 'esotico'],
    'Ugo Verola'         => ['desc' => 'Il Farmer definitivo (Marzò). Mentre tu dormi, lui grinda; mentre tu mangi, lui scala le classifiche di Anime Eternal.', 'rarity' => 'leggendario'],
    'Stepan e Cesarini'  => ['desc' => 'Io non li ho mai visti insieme...', 'rarity' => 'comune'],
    'Stepan e Ugo'       => ['desc' => 'Il loro incontro è stato univoco, si prospettano future apparizioni.', 'rarity' => 'non-comune'],
];

// 2. LOGICA AJAX
if (isset($_GET['ajax'])) {
    $q = isset($_GET['author']) ? trim($_GET['author']) : '';
    $found = [];
    foreach ($authors as $name => $data) {
        if ($q === '' || stripos($name, $q) !== false) { $found[] = $name; }
    }
    header('Content-Type: application/json');
    echo json_encode($found);
    exit;
}

// 3. RECUPERO DATI
$author_query = isset($_GET['author']) ? trim($_GET['author']) : '';
$matched_name = null; $matched_desc = ""; $rarity = "default"; $audio_file = null; $extra_gif = null;

if ($author_query !== "") {
    foreach ($authors as $name => $data) {
        if (strcasecmp($name, $author_query) === 0) {
            $matched_name = $name; 
            $matched_desc = $data['desc']; 
            $rarity = $data['rarity'];
            $extra_gif = isset($data['extra_gif']) ? $data['extra_gif'] : null;
            $sound_path = "./sounds/" . $name . ".mp3";
            if (file_exists($sound_path)) { $audio_file = $sound_path . "?v=" . time(); }
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Card Gallery - Ultra Collector</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           RESET E SIMMETRIA GEOMETRICA (FONDAMENTALE)
           ========================================= */
        * {
            box-sizing: border-box; /* Mantiene le dimensioni esatte includendo bordi e padding */
        }

        :root {
            --comune: #7f8c8d; --non-comune: #27ae60; --raro: #2980b9; 
            --epico: #8e44ad; --leggendario: #f1c40f; 
            /* NUOVA VARIABILE ESOTICO */
            --esotico: #ff512f; 
            --mitico: #e74c3c; --segreto: #00f2ff;
            /* Cyberpunk Palette */
            --cyber-pink: #ff0055; --cyber-cyan: #00f3ff; --cyber-yellow: #ffee00; --cyber-dark: #050505;
        }

        body {
            font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px 0; /* Padding solo verticale */
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 100vh; background-color: #e0e4e8; color: #333;
            transition: background 1s ease; overflow-x: hidden;
        }

        #flash-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 10000; pointer-events: none; opacity: 0; }
        .do-flash { animation: epicFlash 0.7s ease-out forwards; }
        @keyframes epicFlash { 0% { opacity: 0; } 30% { opacity: 1; } 100% { opacity: 0; } }

        /* =========================================
           BARRA DI RICERCA (Allineata alla Card)
           ========================================= */
        .search-container { 
            width: 95%; /* Stessa larghezza percentuale della card */
            max-width: 550px; /* Stesso limite massimo della card */
            margin-bottom: 30px; 
            position: relative; 
            z-index: 1000; 
            transition: opacity 0.5s ease, margin 0.5s ease; 
        }

        #authorInput { 
            width: 100%; padding: 18px; border-radius: 12px; border: 4px solid #fff; 
            font-size: 18px; text-align: center; outline: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        #suggestions { 
            position: absolute; top: 110%; width: 100%; background: white; 
            border-radius: 12px; display: none; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;
        }
        .sugg-item { padding: 12px; cursor: pointer; border-bottom: 1px solid #eee; text-align: center; font-weight: bold; }

        /* =========================================
           THE CARD STANDARD (Allineata alla Barra)
           ========================================= */
        .card {
            background: #fff; 
            width: 95%; /* Stessa larghezza percentuale della barra */
            max-width: 550px; /* Stesso limite massimo della barra */
            min-height: 800px;
            border-radius: 30px; padding: 25px; position: relative;
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            display: flex; flex-direction: column;
            animation: cardPop 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            border: 16px solid #333; /* Grazie a box-sizing, questo bordo non rompe l'allineamento */
            overflow: hidden;
            transition: all 0.5s ease;
        }
        @keyframes cardPop { from { transform: translateY(50px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

        .card-bg-gif {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; opacity: 0.35; z-index: 0; pointer-events: none;
        }

        .card-content { position: relative; z-index: 2; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; flex-grow: 1; }

        .media-box { 
            width: 100%; height: 450px;
            border-radius: 15px; margin-bottom: 20px;
            display: flex; justify-content: center; align-items: center;
            background: rgba(255,255,255,0.2);
            border: 4px solid rgba(0,0,0,0.15);
            box-shadow: inset 0 0 20px rgba(0,0,0,0.1);
            position: relative; overflow: hidden;
        }
        .media-box img { 
            max-width: 100%; max-height: 100%; 
            object-fit: contain; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
            z-index: 2;
        }

        /* BORDERS PER RARITÀ STANDARD */
        .rarity-comune { border-color: var(--comune); }
        .rarity-non-comune { border-color: var(--non-comune); }
        .rarity-raro { border-color: var(--raro); }
        .rarity-epico { border-color: var(--epico); }
        .rarity-leggendario { border-color: var(--leggendario); border-style: double; box-shadow: 0 0 40px var(--leggendario); }
        
        /* === NUOVA RARITÀ: ESOTICO === */
        .rarity-esotico {
            border: 16px solid transparent;
            border-image: linear-gradient(135deg, #FF512F 0%, #DD2476 100%) 1;
            box-shadow: 0 0 40px rgba(221, 36, 118, 0.6), inset 0 0 20px rgba(255, 81, 47, 0.4);
            animation: exoticBreathe 3s ease-in-out infinite;
        }
        @keyframes exoticBreathe {
            0% { box-shadow: 0 0 40px rgba(221, 36, 118, 0.6); transform: scale(1); }
            50% { box-shadow: 0 0 70px rgba(255, 81, 47, 0.9); transform: scale(1.01); }
            100% { box-shadow: 0 0 40px rgba(221, 36, 118, 0.6); transform: scale(1); }
        }
        /* ============================== */

        .rarity-mitico { border-image: linear-gradient(45deg, #f00, #ff0, #0f0, #0ff, #00f, #f0f) 1; animation: miticBorder 3s infinite; }
        
        /* STILE SEGRETO STANDARD (TRIO TARALLO) */
        .rarity-segreto {
            border-color: var(--segreto);
            box-shadow: 0 0 50px var(--segreto), inset 0 0 20px var(--segreto);
            border-style: solid;
            animation: cosmicShake 4s infinite, borderPulse 2s infinite alternate;
        }
        
        @keyframes miticBorder { 0% { filter: hue-rotate(0deg); } 100% { filter: hue-rotate(360deg); } }
        
        @keyframes cosmicShake {
            0% { transform: translate(0, 0); }
            5% { transform: translate(2px, 2px); }
            10% { transform: translate(-2px, -2px); }
            15% { transform: translate(0, 0); }
            100% { transform: translate(0, 0); }
        }
        @keyframes borderPulse {
            0% { box-shadow: 0 0 30px var(--segreto); }
            100% { box-shadow: 0 0 70px var(--segreto), 0 0 20px #fff; }
        }

        /* BADGES STANDARD */
        .rarity-badge { padding: 12px 40px; border-radius: 50px; font-weight: 900; text-transform: uppercase; margin-bottom: 15px; color: white; letter-spacing: 2px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .bg-comune { background: var(--comune); }
        .bg-non-comune { background: var(--non-comune); }
        .bg-raro { background: var(--raro); }
        .bg-epico { background: var(--epico); }
        .bg-leggendario { background: var(--leggendario); color: #000; }
        
        /* === BADGE ESOTICO === */
        .bg-esotico { 
            background: linear-gradient(90deg, #FF512F, #DD2476); 
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        /* ===================== */

        .bg-mitico { background: linear-gradient(90deg, #ff0000, #ff7300, #fffb00); }
        .bg-segreto { background: var(--segreto); color: #000; text-shadow: 0px 0px 5px #fff; }
        
        .card h1 { font-size: 36px; margin: 0 0 10px 0; text-transform: uppercase; text-align: center; font-weight: 900; }
        .desc-box { 
            background: rgba(255,255,255,0.9); padding: 20px; border-radius: 15px;
            text-align: center; font-size: 18px; line-height: 1.4;
            border: 2px solid rgba(0,0,0,0.1); width: 90%;
            margin-bottom: 10px;
        }

        /* SFONDI BODY STANDARD */
        /* === SFONDO ESOTICO === */
        .body-esotico { 
            background: #2b1055; 
            background: linear-gradient(180deg, #2b1055 0%, #7597de 100%);
            background-size: 400% 400%;
            animation: tropicalSunset 10s ease infinite alternate;
        }
        @keyframes tropicalSunset {
            0% { background: linear-gradient(45deg, #FF512F, #DD2476); }
            100% { background: linear-gradient(45deg, #da22ff, #9733ee); }
        }
        /* ====================== */

        .body-mitico { background: linear-gradient(-45deg, #ff9a9e, #fad0c4, #ffd1ff, #a1c4fd); background-size: 400% 400%; animation: grad 10s infinite; }
        body.body-segreto-standard { background: #000 url('https://www.transparenttextures.com/patterns/stardust.png'); animation: space 60s linear infinite; }
        
        .pasta-particle { position: fixed; top: -100px; width: 120px; pointer-events: none; z-index: 9999; animation: fall linear forwards; }
        @keyframes fall { to { transform: translateY(110vh) rotate(720deg); } }

        /* ==========================================================================
           CYBERPUNK OVERHAUL (Michele e Cesarini)
           ========================================================================== */
        
        body.body-segreto {
            background-color: #020204;
            background-image: 
                linear-gradient(rgba(0, 242, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 85, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, #000000 0%, #1a0b1e 100%);
            background-size: 50px 50px, 50px 50px, 100% 100%;
            background-position: center 0, center 0, center;
            animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove { 0% { background-position: center 0, center 0, center; } 100% { background-position: center 50px, center 50px, center; } }
        
        .rarity-cyberpunk {
            background: rgba(10, 10, 10, 0.95) !important;
            color: var(--cyber-cyan) !important;
            border: 2px solid var(--cyber-cyan) !important;
            border-radius: 4px !important;
            box-shadow: 
                0 0 30px rgba(0, 242, 255, 0.5),
                inset 0 0 20px rgba(255, 0, 85, 0.3) !important;
            clip-path: polygon(
                0 0, 100% 0, 100% 10%, 95% 15%, 95% 40%, 100% 45%, 100% 100%, 
                0 100%, 0 85%, 5% 80%, 5% 55%, 0 50%
            ); 
            animation: cyberFloat 6s ease-in-out infinite, glitchShake 5s infinite !important;
            font-family: 'Orbitron', sans-serif !important;
            overflow: visible !important;
            /* Reset margine per centratura */
            margin-top: 0 !important; 
        }
        @keyframes cyberFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        .rarity-cyberpunk::before {
            content: " ";
            display: block; position: absolute; top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            z-index: 20; background-size: 100% 2px, 3px 100%; pointer-events: none;
        }

        .rarity-cyberpunk .media-box {
            background: #000;
            border: 1px solid var(--cyber-pink);
            border-radius: 0;
            box-shadow: 0 0 15px var(--cyber-pink);
            position: relative;
        }
        .rarity-cyberpunk .media-box img {
            filter: contrast(1.2) brightness(1.1) drop-shadow(4px 4px 0px rgba(0,255,242,0.5));
            animation: hologram 0.2s infinite;
        }
        .rarity-cyberpunk .media-box::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background: var(--cyber-cyan);
            box-shadow: 0 0 20px var(--cyber-cyan);
            opacity: 0.7; animation: scanDown 2.5s linear infinite; z-index: 10;
        }
        @keyframes scanDown { 0% { top: 0%; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { top: 100%; opacity: 0; } }

        .rarity-cyberpunk h1 {
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            text-shadow: 2px 2px 0px var(--cyber-pink), -2px -2px 0px var(--cyber-cyan);
            letter-spacing: 4px;
            font-size: 40px;
            margin-top: 15px;
        }
        .rarity-cyberpunk .rarity-badge {
            background: #000; border: 2px solid var(--cyber-yellow); color: var(--cyber-yellow);
            font-family: 'Share Tech Mono', monospace; border-radius: 0;
            box-shadow: 0 0 10px var(--cyber-yellow);
            animation: blink 2s infinite;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

        .rarity-cyberpunk .desc-box {
            background: rgba(0, 0, 0, 0.8);
            border: 1px dashed var(--cyber-cyan);
            color: #0f0;
            font-family: 'Share Tech Mono', monospace;
            text-align: left;
            border-radius: 0;
            padding-left: 20px;
            position: relative;
        }
        .rarity-cyberpunk .desc-box::before { content: "> "; color: var(--cyber-pink); }
        .rarity-cyberpunk .desc-box::after { content: "_"; animation: blink 1s infinite; }

        .glitch-active { animation: glitch 0.2s infinite; filter: hue-rotate(90deg) invert(1); }
        @keyframes glitch { 0% { transform: translate(5px); } 50% { transform: translate(-5px); } }
        
        @keyframes hologram {
            0% { transform: skew(0deg); opacity: 1; }
            5% { transform: skew(2deg); opacity: 0.9; }
            10% { transform: skew(-1deg); opacity: 1; }
            95% { opacity: 1; filter: none; }
            96% { opacity: 0.5; filter: hue-rotate(90deg); }
            100% { opacity: 1; filter: none; }
        }

        .cyber-deco { position: absolute; pointer-events: none; z-index: 30; }
        .cd-corner-tl { top: -10px; left: -10px; width: 40px; height: 40px; border-top: 4px solid var(--cyber-pink); border-left: 4px solid var(--cyber-pink); }
        .cd-corner-br { bottom: -10px; right: -10px; width: 40px; height: 40px; border-bottom: 4px solid var(--cyber-pink); border-right: 4px solid var(--cyber-pink); }
        .cd-status { position: absolute; right: -70px; top: 100px; font-family: 'Share Tech Mono'; color: var(--cyber-cyan); font-size: 12px; transform: rotate(90deg); letter-spacing: 2px; }
        .cd-lines { position: absolute; bottom: 20px; right: 20px; width: 100px; height: 4px; background: repeating-linear-gradient(90deg, var(--cyber-cyan) 0, var(--cyber-cyan) 5px, transparent 5px, transparent 10px); }
        .cd-warning { position: absolute; top: 10px; right: 10px; background: var(--cyber-pink); color: #000; font-weight: bold; font-size: 10px; padding: 2px 5px; font-family: 'Orbitron'; animation: blink 0.5s infinite; }

    </style>
</head>
<body class="<?php 
    if ($matched_name) {
        if ($rarity === 'segreto') {
            echo ($matched_name === 'Michele e Cesarini') ? 'body-segreto' : 'body-segreto-standard';
        } else {
            echo 'body-'.$rarity;
        }
    }
?>">

    <div id="flash-overlay"></div>

    <div class="search-container">
        <input type="text" id="authorInput" placeholder="CERCA UN GUERRIERO..." autocomplete="off">
        <div id="suggestions"></div>
    </div>

    <div id="resultArea" style="width: 100%; display: flex; justify-content: center;">
        <?php if ($matched_name): ?>
            <div class="card <?php echo ($matched_name === 'Michele e Cesarini') ? 'rarity-comune' : 'rarity-'.$rarity; ?>" id="mainCard">
                
                <?php if ($matched_name === 'Michele e Cesarini'): ?>
                    <div class="cyber-deco cd-corner-tl"></div>
                    <div class="cyber-deco cd-corner-br"></div>
                    <div class="cyber-deco cd-status">SYS.OP.ACTIVE // 99%</div>
                    <div class="cyber-deco cd-lines"></div>
                    <div class="cyber-deco cd-warning">CAUTION: VOLATILE</div>
                    
                <?php endif; ?>

                <?php if ($extra_gif): ?>
                    <img src="<?php echo $extra_gif; ?>" class="card-bg-gif">
                <?php endif; ?>

                <div class="card-content">
                    <div class="media-box">
                        <img src="./images/<?php echo rawurlencode($matched_name); ?>.png" onerror="this.src='https://via.placeholder.com/600?text=IMMAGINE+NON+TROVATA'">
                    </div>
                    <div class="rarity-badge <?php echo ($matched_name === 'Michele e Cesarini') ? 'bg-comune' : 'bg-'.$rarity; ?>" id="mainBadge">
                        <?php echo ($matched_name === 'Michele e Cesarini') ? 'comune' : $rarity; ?>
                    </div>
                    <h1><?php echo htmlspecialchars($matched_name); ?></h1>
                    <div class="desc-box" id="mainDesc">
                        <?php echo ($matched_name === 'Michele e Cesarini') ? 'ANALISI DATI IN CORSO...' : htmlspecialchars($matched_desc); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const input = document.getElementById('authorInput');
        const suggBox = document.getElementById('suggestions');
        const flash = document.getElementById('flash-overlay');
        const matchedName = "<?php echo $matched_name; ?>";
        // Selezioniamo il contenitore per manipolarlo
        const searchContainer = document.querySelector('.search-container');

        input.addEventListener('input', async () => {
            const val = input.value.trim();
            if (val.length < 1) { suggBox.style.display = 'none'; return; }
            const res = await fetch(`?ajax=1&author=${encodeURIComponent(val)}`);
            const data = await res.json();
            if (data.length > 0) {
                suggBox.innerHTML = data.map(n => `<div class="sugg-item">${n}</div>`).join('');
                suggBox.style.display = 'block';
                document.querySelectorAll('.sugg-item').forEach(i => {
                    i.onclick = () => window.location.href = `?author=${encodeURIComponent(i.innerText)}`;
                });
            } else suggBox.style.display = 'none';
        });

        window.addEventListener('DOMContentLoaded', () => {
            if (matchedName) {
                flash.classList.add('do-flash');
                
                if (matchedName === "Michele e Cesarini") {
                    const card = document.getElementById('mainCard');
                    const badge = document.getElementById('mainBadge');
                    const desc = document.getElementById('mainDesc');
                    
                    // TRANSITION SEQUENCE TO CYBERPUNK
                    setTimeout(() => {
                        // 1. Glitch Effect Start
                        card.classList.add('glitch-active');
                        new Audio('./sounds/glitch.mp3').play().catch(e => {}); 

                        setTimeout(() => {
                            // 2. Dissolvi la barra di ricerca
                            searchContainer.style.opacity = '0';
                            
                            // 3. Rimuovi fisicamente la barra per centrare la card
                            setTimeout(() => {
                                searchContainer.style.display = 'none';
                            }, 300);

                            // 4. Trasformazione
                            card.classList.remove('glitch-active', 'rarity-comune');
                            card.classList.add('rarity-cyberpunk'); 
                            
                            document.body.classList.remove('body-comune'); 
                            document.body.classList.add('body-segreto');
                            
                            badge.className = "rarity-badge"; 
                            badge.innerText = "NETRUNNER"; 
                            
                            desc.innerHTML = "<?php echo addslashes($matched_desc); ?> <br><span style='font-size:10px; opacity:0.7'>ID: 0x9F2A // SECTOR 7</span>";
                            
                            <?php if($audio_file): ?> new Audio("<?php echo $audio_file; ?>").play(); <?php endif; ?>
                        }, 1300);
                    }, 1500);
                } 
                else if ("<?php echo $audio_file; ?>") {
                    new Audio("<?php echo $audio_file; ?>").play();
                }
                
                if (matchedName === "Michele e Stepan") triggerPasta();
            }
        });

        function triggerPasta() {
            for(let i=0; i<80; i++) {
                setTimeout(() => {
                    const p = document.createElement('img');
                    p.src = "./images/Pasta.png";
                    p.className = 'pasta-particle';
                    p.style.left = Math.random() * 100 + "vw";
                    p.style.animationDuration = (Math.random()*2 + 3) + "s";
                    document.body.appendChild(p);
                }, i * 110);
            }
        }
        document.addEventListener('click', (e) => { if(e.target !== input) suggBox.style.display = 'none'; });
    </script>
</body>
</html>