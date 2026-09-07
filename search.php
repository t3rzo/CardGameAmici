<?php
// 1. DATABASE COMPLETO
$authors = [
    'Antonio e Bruno (VARIANT)'    => ['desc' => '(SPECIALE VARIANT) Un duo che emana una brezza incredibile!', 'rarity' => 'leggendario'],
    'Antonio e Cesarini' => ['desc' => 'Un duo generalmente di destra!', 'rarity' => 'leggendario'],
    'Antonio Marino'     => ['desc' => 'ANDUNIU', 'rarity' => 'non-comune'],
    'Antonio e Bruno'    => ['desc' => 'Un duo che emana una brezza incredibile!', 'rarity' => 'raro'],
    'Antonio e Stepan'   => ['desc' => 'Uniti da origini ucraine e rumene, Antonio e Stepan fondono forza e astuzia in un\'alleanza indissolubile. Il loro legame è sigillato da una frase diventata leggenda — "Ita fasc comporta bini" — eco dell\'amico Matteo Iazzetta. Insieme avanzano come un solo corpo per opporsi all\'ascesa di Francesco Orciuoli, trasformando la diversità in potere assoluto.', 'extra_gif' => './gif/matteo.gif', 'rarity' => 'mitico'],
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
    'Bruno Silvati'      => ['desc' => 'Immobile come un antico custode, il Pinguino osserva il mondo con calma eterna. Si dice che gli sia morto il cazzo: non per debolezza, ma per scelta, avendo eletto l\'indifferenza a via suprema. Un po\' impallato solo in apparenza: in realtà è già avanti. Non corre, non attacca. Aspetta. E vince.', 'extra_gif' => './gif/bruno.gif', 'rarity' => 'mitico'],
    'Il Trio Tarallo'    => ["L'Allineamento Astrale Proibito. Michele, Antonio e Marco si uniscono: la realtà stessa si incrina. Sono le tre colonne che sorreggono l'universo del Cazzeggio Supremo.", 'rarity' => 'segreto'],
    'Michele e Cesarini' => ['desc' => "SYSTEM OVERRIDE...// KERNEL PANIC // Hey V... siamo un glitch nella rete neurale di Night City. Non puoi cancellarci, il codice è nostro ora.", 'rarity' => 'segreto'],
    'Bruno e Stepan'     => ['desc' => 'Il Pinguino impallato e l\'Ucraino senza cittadinanza uniscono le forze sotto il ferro e il sudore. Uno avanza con calma glaciale, l\'altro spinge oltre ogni limite imposto dal mondo. Insieme trasformano lo sforzo in progresso: non per vincere oggi, ma per arrivare più lontano di tutti.', 'rarity' => 'esotico'],
    'Ugo Verola'         => ['desc' => 'Il Farmer definitivo (Marzò). Mentre tu dormi, lui grinda; mentre tu mangi, lui scala le classifiche di Anime Eternal.', 'rarity' => 'leggendario'],
    'Stepan e Cesarini'  => ['desc' => 'Io non li ho mai visti insieme...', 'rarity' => 'comune'],
    'Stepan e Ugo'       => ['desc' => 'Il loro incontro è stato univoco, si prospettano future apparizioni.', 'rarity' => 'non-comune'],
];

// Funzione per generare statistiche deterministiche basate sul nome e rarità
function genStats($name, $rarity) {
    $seed = abs(crc32($name)) % 1000;
    $min = ['comune'=>10,'non-comune'=>25,'raro'=>40,'epico'=>55,'leggendario'=>70,'mitico'=>80,'esotico'=>75,'segreto'=>90];
    $max = ['comune'=>30,'non-comune'=>45,'raro'=>60,'epico'=>75,'leggendario'=>90,'mitico'=>100,'esotico'=>95,'segreto'=>100];
    $mn = $min[$rarity] ?? 20;
    $mx = $max[$rarity] ?? 80;
    return [
        'forza' => ($seed % ($mx - $mn + 1)) + $mn,
        'velocità' => ((int)($seed/3) % ($mx - $mn + 1)) + $mn,
        'tecnica' => ((int)($seed/7) % ($mx - $mn + 1)) + $mn,
        'mentalità' => ((int)($seed/11) % ($mx - $mn + 1)) + $mn,
    ];
}

// Aggiunge stats a ogni autore
foreach ($authors as $name => &$data) {
    $data['stats'] = genStats($name, $data['rarity']);
}
unset($data);

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
$stats = [];

if ($author_query !== "") {
    foreach ($authors as $name => $data) {
        if (strcasecmp($name, $author_query) === 0) {
            $matched_name = $name;
            $matched_desc = $data['desc'];
            $rarity = $data['rarity'];
            $extra_gif = isset($data['extra_gif']) ? $data['extra_gif'] : null;
            $stats = isset($data['stats']) ? $data['stats'] : [];
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

        /* =========================================
           NUOVE ANIMAZIONI E FUNZIONALITÀ
           ========================================= */

        /* Particelle per ogni rarità */
        .particle {
            position: fixed; pointer-events: none; z-index: 9998;
            border-radius: 50%;
        }
        @keyframes floatUp {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-120vh) rotate(720deg); opacity: 0; }
        }

        /* Star particles for leggendario */
        .star-particle {
            width: 8px; height: 8px; background: #f1c40f;
            box-shadow: 0 0 6px #f1c40f, 0 0 12px #f39c12;
            animation: floatUp linear forwards;
        }
        /* Sparkle for epico */
        .sparkle-particle {
            width: 6px; height: 6px; background: #e040fb;
            box-shadow: 0 0 8px #e040fb, 0 0 16px #9c27b0;
            animation: floatUp linear forwards;
        }
        /* Ember for esotico */
        .ember-particle {
            width: 10px; height: 10px;
            background: radial-gradient(circle, #ff512f, #dd2476);
            animation: floatUp linear forwards;
        }
        /* Rune for mitico */
        .rune-particle {
            width: 14px; height: 14px;
            background: conic-gradient(from 0deg, red, orange, yellow, green, blue, indigo, violet, red);
            clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
            animation: floatUp linear forwards;
        }
        /* Void particles for segreto */
        .void-particle {
            width: 12px; height: 12px;
            background: radial-gradient(circle, #00f2ff, transparent);
            box-shadow: 0 0 15px #00f2ff;
            animation: floatUp 6s linear forwards;
        }
        /* Common dust for comune/non-comune */
        .dust-particle {
            width: 5px; height: 5px; background: #aaa;
            animation: floatUp 8s linear forwards;
        }

        /* Stat bars */
        .stats-grid {
            width: 90%; margin-top: 10px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px;
        }
        .stat-row { display: flex; align-items: center; gap: 8px; }
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #555; width: 70px; }
        .stat-bar-bg { flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
        .stat-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
        .stat-value { font-size: 11px; color: #666; width: 28px; text-align: right; font-weight: bold; }

        /* Colori stat bar */
        .stat-forza .stat-bar-fill { background: linear-gradient(90deg, #e74c3c, #c0392b); }
        .stat-velocità .stat-bar-fill { background: linear-gradient(90deg, #3498db, #2980b9); }
        .stat-tecnica .stat-bar-fill { background: linear-gradient(90deg, #2ecc71, #27ae60); }
        .stat-mentalità .stat-bar-fill { background: linear-gradient(90deg, #9b59b6, #8e44ad); }

        /* Total score badge */
        .score-badge {
            position: absolute; top: -15px; right: -15px;
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 3px solid var(--leggendario);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            box-shadow: 0 0 20px rgba(241, 196, 15, 0.5);
            z-index: 50;
        }
        .score-badge .score-label { font-size: 7px; text-transform: uppercase; color: #aaa; letter-spacing: 1px; }
        .score-badge .score-val { font-family: 'Orbitron', sans-serif; font-size: 16px; font-weight: 900; color: var(--leggendario); }

        /* Collect button */
        .collect-btn {
            margin-top: 12px; padding: 10px 30px;
            border: 2px solid var(--cyber-cyan); background: transparent;
            color: var(--cyber-cyan); font-family: 'Share Tech Mono', monospace;
            font-size: 14px; cursor: pointer; border-radius: 4px;
            text-transform: uppercase; letter-spacing: 2px;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .collect-btn:hover { background: var(--cyber-cyan); color: #000; }
        .collect-btn.collected { border-color: var(--non-comune); color: var(--non-comune); }
        .collect-btn.collected:hover { background: var(--non-comune); color: #fff; }

        /* Collection panel */
        .collection-panel {
            position: fixed; right: -420px; top: 0; width: 400px; height: 100vh;
            background: rgba(10, 10, 20, 0.97); border-left: 2px solid var(--cyber-cyan);
            box-shadow: -10px 0 40px rgba(0, 242, 255, 0.2);
            z-index: 9000; transition: right 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            overflow-y: auto; padding: 30px 20px;
        }
        .collection-panel.open { right: 0; }
        .collection-panel h2 {
            font-family: 'Orbitron', sans-serif; color: var(--cyber-cyan);
            font-size: 18px; letter-spacing: 3px; margin-bottom: 20px;
            border-bottom: 1px solid rgba(0,242,255,0.3); padding-bottom: 10px;
        }
        .collection-card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; padding: 10px; margin-bottom: 10px;
            display: flex; align-items: center; gap: 12px; cursor: pointer;
            transition: all 0.2s ease;
        }
        .collection-card:hover { background: rgba(0,242,255,0.1); border-color: var(--cyber-cyan); }
        .collection-card img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .collection-card .cc-name { font-size: 13px; color: #fff; font-weight: bold; }
        .collection-card .cc-rarity { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .collection-stats { font-size: 10px; color: #888; margin-top: 3px; }

        /* Toggle buttons row */
        .top-controls {
            position: fixed; top: 20px; right: 20px; z-index: 8000;
            display: flex; gap: 10px;
        }
        .ctrl-btn {
            width: 44px; height: 44px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.5);
            color: #fff; font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s ease; backdrop-filter: blur(10px);
        }
        .ctrl-btn:hover { border-color: var(--cyber-cyan); color: var(--cyber-cyan); box-shadow: 0 0 15px rgba(0,242,255,0.3); }
        .ctrl-btn.active { background: var(--cyber-cyan); color: #000; border-color: var(--cyber-cyan); }

        /* Dark mode */
        body.dark-mode { background-color: #0a0a0f !important; }
        body.dark-mode .card { background: #111; border-color: #333; }
        body.dark-mode .desc-box { background: rgba(20,20,30,0.95); color: #ddd; border-color: rgba(255,255,255,0.1); }
        body.dark-mode h1 { color: #eee !important; }
        body.dark-mode input[type="text"] { background: rgba(30,30,40,0.9); color: #fff; border-color: #444; }
        body.dark-mode .sugg-item { background: #1a1a2e; color: #fff; border-bottom-color: #333; }
        body.dark-mode .stat-label { color: #aaa; }
        body.dark-mode .stat-bar-bg { background: #333; }
        body.dark-mode .stat-value { color: #aaa; }
        body.light-mode { background-color: #f0f2f5 !important; }
        body.light-mode .card { background: #fff; }
        body.light-mode .desc-box { background: rgba(240,240,250,0.95); }

        /* Rarity-specific card animations (NEW - don't touch existing) */
        @keyframes legendaryShine {
            0% { box-shadow: 0 0 40px var(--leggendario), inset 0 0 20px rgba(241,196,15,0.2); }
            50% { box-shadow: 0 0 80px var(--leggendario), 0 0 120px var(--leggendario), inset 0 0 30px rgba(241,196,15,0.4); }
            100% { box-shadow: 0 0 40px var(--leggendario), inset 0 0 20px rgba(241,196,15,0.2); }
        }
        .rarity-leggendario { animation: legendaryShine 3s ease-in-out infinite !important; }

        @keyframes epicWave {
            0%, 100% { box-shadow: 0 0 30px var(--epico), inset 0 0 15px rgba(142,68,173,0.3); }
            50% { box-shadow: 0 0 60px var(--epico), 0 0 90px rgba(142,68,173,0.5), inset 0 0 25px rgba(142,68,173,0.5); }
        }
        .rarity-epico { animation: epicWave 3s ease-in-out infinite !important; }

        @keyframes rareGlow {
            0%, 100% { box-shadow: 0 0 20px var(--raro); }
            50% { box-shadow: 0 0 45px var(--raro), 0 0 70px rgba(41,128,185,0.4); }
        }
        .rarity-raro { animation: rareGlow 4s ease-in-out infinite !important; }

        @keyframes uncommonPulse {
            0%, 100% { box-shadow: 0 0 10px var(--non-comune); }
            50% { box-shadow: 0 0 25px var(--non-comune); }
        }
        .rarity-non-comune { animation: uncommonPulse 5s ease-in-out infinite !important; }

        @keyframes commonStill {}
        .rarity-comune { animation: commonStill 1s linear infinite; }

        /* Holographic shine overlay for all cards on hover */
        .card::before {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: skewX(-25deg);
            transition: left 0.7s ease;
            pointer-events: none; z-index: 40;
        }
        .card:hover::before { left: 150%; }

        /* Audio visualizer bars */
        .audio-viz {
            display: flex; align-items: flex-end; justify-content: center;
            gap: 3px; height: 30px; margin-top: 8px;
        }
        .audio-viz .bar {
            width: 4px; background: var(--cyber-cyan);
            animation: vizBar 0.8s ease-in-out infinite alternate;
            border-radius: 2px;
        }
        @keyframes vizBar { 0% { height: 5px; } 100% { height: 25px; } }
        .audio-viz .bar:nth-child(1) { animation-delay: 0s; }
        .audio-viz .bar:nth-child(2) { animation-delay: 0.1s; }
        .audio-viz .bar:nth-child(3) { animation-delay: 0.2s; }
        .audio-viz .bar:nth-child(4) { animation-delay: 0.3s; }
        .audio-viz .bar:nth-child(5) { animation-delay: 0.15s; }
        .audio-viz .bar:nth-child(6) { animation-delay: 0.25s; }
        .audio-viz .bar:nth-child(7) { animation-delay: 0.05s; }

        /* Responsive */
        @media (max-width: 600px) {
            .card { min-height: 700px; padding: 15px; border-width: 10px; border-radius: 20px; }
            .media-box { height: 300px; }
            .card h1 { font-size: 22px; }
            .stats-grid { grid-template-columns: 1fr; }
            .collection-panel { width: 100%; right: -100%; }
            .score-badge { width: 45px; height: 45px; right: -10px; top: -10px; }
            .score-badge .score-val { font-size: 13px; }
            .top-controls { top: 10px; right: 10px; }
            .ctrl-btn { width: 36px; height: 36px; font-size: 15px; }
            .search-container { width: 98%; }
            .card { width: 98%; }
        }

        /* Rarity counter */
        .rarities-counter {
            display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;
            margin-top: 15px; padding: 12px;
            background: rgba(255,255,255,0.08); border-radius: 12px;
        }
        .rc-item { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .rc-dot { width: 10px; height: 10px; border-radius: 50%; }
        .rc-count { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 8px; font-size: 10px; }

        /* Flip card back */
        .card-inner { position: relative; width: 100%; min-height: 800px; perspective: 1000px; }
        .card-flip-front, .card-flip-back { backface-visibility: hidden; }
        .card.flipped .card-flip-front { transform: rotateY(180deg); }
        .card.flipped .card-flip-back { transform: rotateY(0deg); }
        .card-flip-back {
            position: absolute; top: 0; left: 0; width: 100%; min-height: 800px;
            backface-visibility: hidden; transform: rotateY(180deg);
            display: flex; flex-direction: column; align-items: center; padding: 25px;
        }
        .flip-hint { font-size: 11px; opacity: 0.6; margin-top: auto; padding-top: 15px; }

        /* Card entrance animation variations per rarity */
        .card-enter-comune { animation: cardPop 0.5s ease forwards; }
        .card-enter-non-comune { animation: cardPop 0.5s ease forwards, uncommonPulse 5s ease-in-out infinite 0.5s; }
        .card-enter-raro { animation: cardPop 0.5s ease forwards, rareGlow 4s ease-in-out infinite 0.5s; }
        .card-enter-epico { animation: cardPop 0.5s ease forwards, epicWave 3s ease-in-out infinite 0.5s; }
        .card-enter-leggendario { animation: cardPop 0.5s ease forwards, legendaryShine 3s ease-in-out infinite 0.5s; }
        .card-enter-mitico { animation: cardPop 0.5s ease forwards, miticBorder 3s infinite; }
        .card-enter-esotico { animation: cardPop 0.5s ease forwards, exoticBreathe 3s ease-in-out infinite; }
        .card-enter-segreto { animation: cardPop 0.5s ease forwards, cosmicShake 4s infinite, borderPulse 2s infinite alternate; }
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

    <!-- TOP CONTROLS -->
    <div class="top-controls">
        <button class="ctrl-btn" id="themeToggle" title="Cambia tema" onclick="toggleTheme()">◐</button>
        <button class="ctrl-btn" id="collectionToggle" title="Collezione" onclick="toggleCollection()">📚</button>
    </div>

    <div class="search-container">
        <input type="text" id="authorInput" placeholder="CERCA UN GUERRIERO..." autocomplete="off">
        <div id="suggestions"></div>
    </div>

    <div id="resultArea" style="width: 100%; display: flex; justify-content: center;">
        <?php if ($matched_name): ?>
            <?php
            $stats = genStats($matched_name, $rarity);
            $totalScore = array_sum($stats);
            ?>
            <div class="card <?php echo ($matched_name === 'Michele e Cesarini') ? 'rarity-comune' : 'rarity-'.$rarity; ?>" id="mainCard">
                <!-- Score badge -->
                <div class="score-badge">
                    <span class="score-label">PTS</span>
                    <span class="score-val"><?php echo $totalScore; ?></span>
                </div>

                <?php if ($matched_name === 'Michele e Cesarini'): ?>
                    <!-- cyberpunk decorations kept identical -->
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

                    <!-- Stats grid -->
                    <div class="stats-grid" id="statsGrid">
                        <div class="stat-row stat-forza">
                            <span class="stat-label">Forza</span>
                            <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats['forza']; ?>"></div></div>
                            <span class="stat-value"><?php echo $stats['forza']; ?></span>
                        </div>
                        <div class="stat-row stat-velocità">
                            <span class="stat-label">Velocità</span>
                            <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats['velocità']; ?>"></div></div>
                            <span class="stat-value"><?php echo $stats['velocità']; ?></span>
                        </div>
                        <div class="stat-row stat-tecnica">
                            <span class="stat-label">Tecnica</span>
                            <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats['tecnica']; ?>"></div></div>
                            <span class="stat-value"><?php echo $stats['tecnica']; ?></span>
                        </div>
                        <div class="stat-row stat-mentalità">
                            <span class="stat-label">Mentalità</span>
                            <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats['mentalità']; ?>"></div></div>
                            <span class="stat-value"><?php echo $stats['mentalità']; ?></span>
                        </div>
                    </div>

                    <!-- Audio visualizer -->
                    <div class="audio-viz" id="audioViz" style="display:none;">
                        <?php for($i=0;$i<7;$i++): ?><div class="bar"></div><?php endfor; ?>
                    </div>

                    <!-- Collect button -->
                    <button class="collect-btn" id="collectBtn" onclick="toggleCollect('<?php echo addslashes($matched_name); ?>', '<?php echo $rarity; ?>')">
                        + Aggiungi alla Collezione
                    </button>

                    <!-- Rarity counter -->
                    <div class="rarities-counter" id="raritiesCounter"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:20px; text-align:center; opacity:0.5; font-size:12px; letter-spacing:1px;">
        CARD GALLERY v2.0 — <?php echo count($authors); ?> GUERRIERI DISPONIBILI
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

                setTimeout(() => spawnParticles('<?php echo $rarity; ?>', 20), 2000);
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

        // ============ COLLECTION SYSTEM ============
        const COLLECT_KEY = 'cardGameCollection';
        let collection = JSON.parse(localStorage.getItem(COLLECT_KEY) || '{}');

        function toggleCollect(name, rarity) {
            const btn = document.getElementById('collectBtn');
            if (collection[name]) {
                delete collection[name];
                btn.textContent = '+ Aggiungi alla Collezione';
                btn.classList.remove('collected');
            } else {
                collection[name] = { rarity: rarity, collectedAt: Date.now(), stats: <?php echo json_encode($stats ?? []); ?> };
                btn.textContent = '✓ Nella Collezione';
                btn.classList.add('collected');
            }
            localStorage.setItem(COLLECT_KEY, JSON.stringify(collection));
            updateRaritiesCounter();
            renderCollection();
        }

        function updateRaritiesCounter() {
            const container = document.getElementById('raritiesCounter');
            if (!container) return;
            const counts = {};
            Object.values(collection).forEach(c => { counts[c.rarity] = (counts[c.rarity]||0)+1; });
            const rarities = ['comune','non-comune','raro','epico','leggendario','esotico','mitico','segreto'];
            const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
            container.innerHTML = rarities.map(r => `<div class="rc-item"><div class="rc-dot" style="background:${colors[r]}"></div>${r}<span class="rc-count">${counts[r]||0}</span></div>`).join('');
        }

        function renderCollection() {
            const list = document.getElementById('collectionList');
            if (!list) return;
            const names = Object.keys(collection);
            if (names.length === 0) {
                list.innerHTML = '<div style="color:#666;text-align:center;padding:40px 0;font-size:13px;">Nessuna carta collezione ancora.<br>Cerca e coleta i guerrieri!</div>';
                document.getElementById('collectionStats').innerHTML = 'Carte: 0<br>Punti totali: 0';
                return;
            }
            let totalPts = 0;
            list.innerHTML = names.map(name => {
                const c = collection[name];
                const s = c.stats || {};
                const pts = Object.values(s).reduce((a,b)=>a+b,0) || 0;
                totalPts += pts;
                const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
                return `<div class="collection-card" onclick="window.location.href='?author=${encodeURIComponent(name)}'">
                    <img src="./images/${encodeURIComponent(name)}.png" onerror="this.src='https://via.placeholder.com/50'">
                    <div>
                        <div class="cc-name">${name}</div>
                        <div class="cc-rarity" style="color:${colors[c.rarity]||'#fff'}">${c.rarity}</div>
                        <div class="collection-stats">⚔${pts}pts</div>
                    </div>
                </div>`;
            }).join('');
            document.getElementById('collectionStats').innerHTML = `Carte: ${names.length}<br>Punti totali: ${totalPts}`;
        }

        // ============ THEME TOGGLE ============
        function toggleTheme() {
            const btn = document.getElementById('themeToggle');
            document.body.classList.toggle('dark-mode');
            document.body.classList.toggle('light-mode');
            btn.classList.toggle('active');
        }

        // ============ COLLECTION PANEL ============
        function toggleCollection() {
            const panel = document.getElementById('collectionPanel');
            const overlay = document.getElementById('collectionOverlay');
            panel.classList.toggle('open');
            overlay.style.display = panel.classList.contains('open') ? 'block' : 'none';
            if (panel.classList.contains('open')) renderCollection();
        }

        // ============ PARTICLE EFFECTS PER RARITÀ ============
        function spawnParticles(rarity, count) {
            const colors = {
                'comune': ['#aaa','#888'],
                'non-comune': ['#27ae60','#2ecc71'],
                'raro': ['#2980b9','#3498db'],
                'epico': ['#8e44ad','#e040fb'],
                'leggendario': ['#f1c40f','#f39c12'],
                'mitico': ['#e74c3c','#ff7300','#fffb00'],
                'esotico': ['#ff512f','#DD2476'],
                'segreto': ['#00f2ff','#ff0055']
            };
            const c = colors[rarity] || colors['comune'];
            for (let i = 0; i < count; i++) {
                setTimeout(() => {
                    const p = document.createElement('div');
                    p.className = 'particle';
                    p.style.left = Math.random() * 100 + 'vw';
                    p.style.top = (80 + Math.random() * 20) + 'vh';
                    p.style.width = (4 + Math.random()*8) + 'px';
                    p.style.height = p.style.width;
                    p.style.background = c[Math.floor(Math.random()*c.length)];
                    p.style.boxShadow = `0 0 ${6+Math.random()*8}px ${p.style.background}`;
                    p.style.animation = `floatUp ${3+Math.random()*4}s linear forwards`;
                    document.body.appendChild(p);
                    setTimeout(() => p.remove(), 8000);
                }, i * 80);
            }
        }

        // ============ STAT BAR ANIMATION ============
        function animateStats() {
            document.querySelectorAll('.stat-bar-fill').forEach(bar => {
                const w = bar.getAttribute('data-w');
                setTimeout(() => { bar.style.width = w + '%'; }, 300);
            });
        }

        // ============ AUDIO VISUALIZER ============
        function showAudioViz(show) {
            const viz = document.getElementById('audioViz');
            if (viz) viz.style.display = show ? 'flex' : 'none';
        }

        // ============ RUN ON LOAD ============
        window.addEventListener('DOMContentLoaded', () => {
            animateStats();
            updateRaritiesCounter();
        });

        // Listen for audio start to show viz
        document.addEventListener('DOMContentLoaded', () => {
            const origPlay = Audio.prototype.play;
            Audio.prototype.play = function() {
                showAudioViz(true);
                this.addEventListener('ended', () => showAudioViz(false));
                return origPlay.call(this);
            };
        });
    </script>

    <!-- COLLECTION PANEL -->
    <div class="collection-panel" id="collectionPanel">
        <h2>⚔ LA TUA COLLEZIONE</h2>
        <div id="collectionList"></div>
        <div style="margin-top:20px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px;">
            <div style="font-family:'Orbitron';color:var(--cyber-cyan);font-size:12px;letter-spacing:2px;margin-bottom:8px;">STATISTICHE</div>
            <div id="collectionStats" style="font-size:12px;color:#aaa;line-height:1.8;"></div>
        </div>
    </div>
    <div id="collectionOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:8999;display:none;" onclick="toggleCollection()"></div>

</body>
</html>
