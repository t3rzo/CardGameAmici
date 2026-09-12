<?php
// gallery_view.php — Visualizzazione singola carta con dettagli (legacy compat)
$author_query = isset($_GET['author']) ? trim($_GET['author']) : '';
$matched_name = null; $matched_desc = ""; $rarity = "default"; $audio_file = null; $extra_gif = null;
$stats = []; $stats_rpg = [];

if ($author_query !== "") {
    foreach ($authors as $name => $data) {
        if (strcasecmp($name, $author_query) === 0) {
            $matched_name = $name;
            $matched_desc = $data['desc'] ?? '';
            $rarity = $data['rarity'];
            $extra_gif = isset($data['extra_gif']) ? $data['extra_gif'] : null;
            // Stats legacy e RPG
            $stats = $data['stats'] ?? genStatsLegacy($name, $rarity);
            $stats_rpg = [
                'attacco'  => $stats['forza'] ?? 50,
                'vita'     => $stats['mentalità'] ?? 50,
                'difesa'   => $stats['tecnica'] ?? 50,
                'velocità' => $stats['velocità'] ?? 50,
            ];
            $sound_path = "./sounds/" . $name . ".mp3";
            if (file_exists($sound_path)) { $audio_file = $sound_path . "?v=" . time(); }
            break;
        }
    }
}
?>

<div class="search-container">
    <input type="text" id="authorInput" placeholder="CERCA UN GUERRIERO..." autocomplete="off"
           value="<?php echo htmlspecialchars($author_query); ?>">
    <div id="suggestions"></div>
</div>

<div id="resultArea" style="width: 100%; display: flex; justify-content: center;">
    <?php if ($matched_name): ?>
        <?php
        $totalScore = array_sum($stats);
        // Aggiungi pulsante admin carte
        ?>
        <div class="card <?php echo ($matched_name === 'Michele e Cesarini') ? 'rarity-comune' : 'rarity-'.$rarity; ?>" id="mainCard">
            <div class="score-badge">
                <span class="score-label">PTS</span>
                <span class="score-val"><?php echo $totalScore; ?></span>
            </div>

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
                    <img src="./images/<?php echo rawurlencode($matched_name); ?>.png"
                         onerror="this.src='https://via.placeholder.com/600?text=IMMAGINE+NON+TROVATA'">
                </div>
                <div class="rarity-badge <?php echo ($matched_name === 'Michele e Cesarini') ? 'bg-comune' : 'bg-'.$rarity; ?>" id="mainBadge">
                    <?php echo ($matched_name === 'Michele e Cesarini') ? 'comune' : $rarity; ?>
                </div>
                <h1><?php echo htmlspecialchars($matched_name); ?></h1>
                <div class="desc-box" id="mainDesc">
                    <?php echo ($matched_name === 'Michele e Cesarini') ? 'ANALISI DATI IN CORSO...' : htmlspecialchars($matched_desc); ?>
                </div>

                <!-- Stats RPG (etichette nuove) -->
                <div class="stats-grid" id="statsGrid">
                    <div class="stat-row stat-forza">
                        <span class="stat-label">Attacco</span>
                        <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats_rpg['attacco']; ?>"></div></div>
                        <span class="stat-value"><?php echo $stats_rpg['attacco']; ?></span>
                    </div>
                    <div class="stat-row stat-mentalità">
                        <span class="stat-label">Vita</span>
                        <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats_rpg['vita']; ?>"></div></div>
                        <span class="stat-value"><?php echo $stats_rpg['vita']; ?></span>
                    </div>
                    <div class="stat-row stat-tecnica">
                        <span class="stat-label">Difesa</span>
                        <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats_rpg['difesa']; ?>"></div></div>
                        <span class="stat-value"><?php echo $stats_rpg['difesa']; ?></span>
                    </div>
                    <div class="stat-row stat-velocità">
                        <span class="stat-label">Velocità</span>
                        <div class="stat-bar-bg"><div class="stat-bar-fill" style="width:0%" data-w="<?php echo $stats_rpg['velocità']; ?>"></div></div>
                        <span class="stat-value"><?php echo $stats_rpg['velocità']; ?></span>
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

                <!-- Fight button (only if collected) -->
                <?php if (isset($collection[$matched_name])): ?>
                <button class="collect-btn" id="fightBtn" onclick="window.location.href='?mode=game&card=<?php echo urlencode($matched_name); ?>';" style="margin-left: 10px; background: var(--cyber-pink); color: #fff;">
                    ⚔️ Combatti!
                </button>
                <?php endif; ?>

                <!-- Rarity counter -->
                <div class="rarities-counter" id="raritiesCounter"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top:20px; text-align:center; opacity:0.5; font-size:12px; letter-spacing:1px;">
    CARD RPG v1.0 — <?php echo count($authors); ?> GUERRIERI DISPONIBILI |
    <a href="?mode=gacha" style="color:var(--cyber-cyan);opacity:0.7;">→ Vai al Gacha ←</a>
</div>

<script>
const matchedName = "<?php echo $matched_name; ?>";
const audioFile = <?php echo $audio_file ? json_encode($audioFile) : 'null'; ?>;
const rarity = "<?php echo $rarity; ?>";
let collection = JSON.parse(localStorage.getItem('cardGameCollection') || '{}');

// Autocomplete
const input = document.getElementById('authorInput');
const suggBox = document.getElementById('suggestions');
input.addEventListener('input', async () => {
    const val = input.value.trim();
    if (val.length < 0) { suggBox.style.display = 'none'; return; }
    // Filtra localmente invece di AJAX
    const results = ALL_CARDS.filter(name => name.toLowerCase().includes(val.toLowerCase())).slice(0, 8);
    if (results.length > 0) {
        suggBox.innerHTML = results.map(n => `<div class="sugg-item">${n}</div>`).join('');
        suggBox.style.display = 'block';
        document.querySelectorAll('.sugg-item').forEach(i => {
            i.onclick = () => window.location.href = `?mode=gallery&author=${encodeURIComponent(i.innerText)}`;
        });
    } else suggBox.style.display = 'none';
});

// Collect logic
function toggleCollect(name, rarity) {
    const btn = document.getElementById('collectBtn');
    if (collection[name]) {
        delete collection[name];
        btn.textContent = '+ Aggiungi alla Collezione';
        btn.classList.remove('collected');
    } else {
        const cardData = ALL_CARDS[name];
        collection[name] = {
            rarity: rarity,
            collectedAt: Date.now(),
            stats: cardData.stats || {},
            stats_rpg: {
                attacco: cardData.stats?.forza || 50,
                vita: cardData.stats?.mentalità || 50,
                difesa: cardData.stats?.tecnica || 50,
                velocità: cardData.stats?.velocità || 50,
            },
            level: 1, xp: 0, equipped: {}
        };
        btn.textContent = '✓ Nella Collezione';
        btn.classList.add('collected');
    }
    localStorage.setItem('cardGameCollection', JSON.stringify(collection));
    updateRaritiesCounter();
    renderCollection();
}

// Animazioni su load
window.addEventListener('DOMContentLoaded', () => {
    const flash = document.getElementById('flash-overlay');
    if (matchedName) {
        flash.classList.add('do-flash');

        // Cyberpunk transition per Michele e Cesarini
        if (matchedName === "Michele e Cesarini") {
            const card = document.getElementById('mainCard');
            const badge = document.getElementById('mainBadge');
            const desc = document.getElementById('mainDesc');
            setTimeout(() => {
                card.classList.add('glitch-active');
                new Audio('./sounds/glitch.mp3').play().catch(e => {});
                setTimeout(() => {
                    document.querySelector('.search-container').style.opacity = '0';
                    setTimeout(() => { document.querySelector('.search-container').style.display = 'none'; }, 300);
                    card.classList.remove('glitch-active', 'rarity-comune');
                    card.classList.add('rarity-cyberpunk');
                    document.body.classList.remove('body-comune');
                    document.body.classList.add('body-segreto');
                    badge.className = "rarity-badge";
                    badge.innerText = "NETRUNNER";
                    desc.innerHTML = "<?php echo addslashes($matched_desc); ?> <br><span style='font-size:10px; opacity:0.7'>ID: 0x9F2A // SECTOR 7</span>";
                    if (audioFile) new Audio(audioFile).play();
                }, 1300);
            }, 1500);
        } else if (audioFile) {
            new Audio(audioFile).play();
        }

        // Pasta particle per Michele e Stepan
        if (matchedName === "Michele e Stepan") triggerPasta();

        setTimeout(() => spawnParticles(rarity, 20), 2000);
    }

    // Anima stats bar
    document.querySelectorAll('.stat-bar-fill').forEach(bar => {
        const w = bar.getAttribute('data-w');
        setTimeout(() => { bar.style.width = w + '%'; }, 300);
    });
    updateRaritiesCounter();
});

// Audio visualizer
function showAudioViz(show) {
    const viz = document.getElementById('audioViz');
    if (viz) viz.style.display = show ? 'flex' : 'none';
}
document.addEventListener('DOMContentLoaded', () => {
    const origPlay = Audio.prototype.play;
    Audio.prototype.play = function() {
        showAudioViz(true);
        this.addEventListener('ended', () => showAudioViz(false));
        return origPlay.call(this);
    };
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
