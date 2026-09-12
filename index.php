<?php
// index.php — Entry point + router semplice per il gioco RPG
// Mode: gallery | gacha | game | collection

require_once __DIR__ . '/config/cards.php';
require_once __DIR__ . '/game/functions.php';

$mode = $_GET['mode'] ?? 'gallery';
$mode = in_array($mode, ['gallery', 'gacha', 'game', 'collection']) ? $mode : 'gallery';

$authors = loadCards(); // Carica carte base + custom JSON
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card RPG - Ultra Collector</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/main.css">
    <style>
    /* ===== NAV A SCHEDE ===== */
    .tab-nav {
        position: fixed; top: 60px; left: 0; width: 100%;
        display: flex; justify-content: center; gap: 8px;
        z-index: 700; padding: 0 20px; background: rgba(0,0,0,0.3); backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--rpg-gold);
    }
    .tab-nav .tab {
        padding: 10px 20px; border-radius: 8px 8px 0 0;
        background: rgba(255,255,255,0.1); color: #aaa;
        font-family: 'Orbitron'; font-size: 13px; cursor: pointer;
        transition: all 0.3s ease; border: 1px solid transparent;
    }
    .tab-nav .tab.active { background: var(--rpg-gold); color: #000; border-color: var(--rpg-gold); }
    .tab-nav .tab:hover { background: rgba(255,255,255,0.2); color: #fff; }
    </style>
</head>
<body class="gallery-theme body-forest">
    <div class="scanline-overlay"></div>
    <div id="flash-overlay"></div>

    <!-- HUD -->
    <div class="game-hud">
        <div class="hud-left">
            <div class="player-level">LVL <span id="playerLevel">1</span></div>
            <div class="hp-bar-container">
                <span class="hp-bar-label">HP</span>
                <div class="hp-bar-fill" id="playerHpBar" style="width: 100%;"></div>
                <span class="hp-bar-label" id="playerHpText">100/100</span>
            </div>
        </div>
        <div class="hud-right">
            <div class="currency-display"><span class="ff-icon">💎</span> <span id="fFCount">0</span></div>
            <button class="ctrl-btn" id="themeToggle" title="Cambia tema" onclick="toggleTheme()">◐</button>
            <button class="ctrl-btn" id="collectionToggle" title="Collezione" onclick="toggleCollection()">📚</button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-nav">
        <div class="tab" onclick="switchMode('gallery')">🏠 Galleria</div>
        <div class="tab" onclick="switchMode('gacha')">🎰 Gacha</div>
        <div class="tab" onclick="switchMode('game')">⚔️ Combatti</div>
        <div class="tab" onclick="switchMode('collections')">🎒 Collezione</div>
        <div class="tab"><a href="admin.php" style="color:inherit;text-decoration:none;">ℹ️ Admin</a></div>
    </div>

    <!-- Contenuto principale -->
    <div id="modeContent" style="padding-top: 90px; width: 100%; display: flex; justify-content: center;">
        <?php if ($mode === 'gallery'): ?>
            <?php include __DIR__ . '/game/gallery_view.php'; ?>
        <?php elseif ($mode === 'gacha'): ?>
            <?php include __DIR__ . '/game/gacha_view.php'; ?>
        <?php elseif ($mode === 'game'): ?>
            <?php include __DIR__ . '/game/battle_view.php'; ?>
        <?php else: ?>
            <?php include __DIR__ . '/game/collection_view.php'; ?>
        <?php endif; ?>
    </div>

    <!-- Collection Panel (sempre presente) -->
    <div class="collection-panel" id="collectionPanel">
        <h2>⚔ LA TUA COLLEZIONE</h2>
        <div id="collectionList"></div>
        <div style="margin-top:20px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px;">
            <div style="font-family:'Orbitron';color:var(--cyber-cyan);font-size:12px;letter-spacing:2px;margin-bottom:8px;">STATISTICHE</div>
            <div id="collectionStats" style="font-size:12px;color:#aaa;line-height:1.8;"></div>
        </div>
    </div>
    <div id="collectionOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:8999;display:none;" onclick="toggleCollection()"></div>

    <!-- Scripts condivisi -->
    <script>
    // Dati carte in JS (per logica client-side gacha/inventario)
    const ALL_CARDS = <?php echo json_encode($authors); ?>;
    const RARITY_WEIGHTS = <?php echo json_encode($rarityWeights); ?>;

    function switchMode(mode) {
        window.location.href = '?mode=' + mode;
    }

    // Temi
    function toggleTheme() {
        const body = document.body;
        body.classList.toggle('dark-mode');
        body.classList.toggle('light-mode');
        document.getElementById('themeToggle').classList.toggle('active');
    }

    // Collezione
    const COLLECT_KEY = 'cardGameCollection';
    const SAVE_KEY = 'cardGameSave';
    const CURRENCY_KEY = 'gachaCurrency';
    let collection = JSON.parse(localStorage.getItem(COLLECT_KEY) || '{}');

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
            list.innerHTML = '<div style="color:#666;text-align:center;padding:40px 0;font-size:13px;">Nessuna carta nella collezione.<br>Tira dal gacha per raccoglierle!</div>';
            document.getElementById('collectionStats').innerHTML = 'Carte: 0<br>Punti totali: 0';
            return;
        }
        let totalPts = 0;
        list.innerHTML = names.map(name => {
            const c = collection[name];
            const s = c.stats || c.stats_rpg || {};
            const pts = Object.values(s).reduce((a,b)=>a+b,0) || 0;
            totalPts += pts;
            const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
            const lvl = c.level || 1;
            return `<div class="collection-card" onclick="window.location.href='?mode=game&card=${encodeURIComponent(name)}'">
                <img src="./images/${encodeURIComponent(name)}.png" onerror="this.src='https://via.placeholder.com/50'">
                <div>
                    <div class="cc-name">${name} <span style="color:${colors[c.rarity]||'#fff'}">[${lvl}]</span></div>
                    <div class="cc-rarity" style="color:${colors[c.rarity]||'#fff'}">${c.rarity}</div>
                    <div class="collection-stats">⚔${pts}pts</div>
                </div>
            </div>`;
        }).join('');
        document.getElementById('collectionStats').innerHTML = `Carte: ${names.length}<br>Punti totali: ${totalPts}`;
    }

    function toggleCollection() {
        const panel = document.getElementById('collectionPanel');
        const overlay = document.getElementById('collectionOverlay');
        panel.classList.toggle('open');
        overlay.style.display = panel.classList.contains('open') ? 'block' : 'none';
        if (panel.classList.contains('open')) renderCollection();
    }

    // Particelle per rarità
    function spawnParticles(rarity, count) {
        const colors = {
            'comune': ['#aaa','#888'], 'non-comune': ['#27ae60','#2ecc71'],
            'raro': ['#2980b9','#3498db'], 'epico': ['#8e44ad','#e040fb'],
            'leggendario': ['#f1c40f','#f39c12'], 'mitico': ['#e74c3c','#ff7300','#fffb00'],
            'esotico': ['#ff512f','#DD2476'], 'segreto': ['#00f2ff','#ff0055']
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

    // Carica salvataggio
    function loadSave() {
        const save = JSON.parse(localStorage.getItem(SAVE_KEY) || '{}');
        const fF = parseInt(localStorage.getItem(CURRENCY_KEY) || '0');
        return { level: save.level || 1, xp: save.xp || 0, fF: fF, equipped: save.equipped || {} };
    }

    function saveGame() {
        const save = loadSave();
        localStorage.setItem(SAVE_KEY, JSON.stringify({
            level: save.level, xp: save.xp, equipped: save.equipped
        }));
        localStorage.setItem(CURRENCY_KEY, save.fF.toString());
    }

    // Inizializza HUD
    window.addEventListener('DOMContentLoaded', () => {
        const save = loadSave();
        document.getElementById('playerLevel').textContent = save.level;
        document.getElementById('fFCount').textContent = save.fF;
        updateRaritiesCounter();
    });
    </script>
</body>
</html>
