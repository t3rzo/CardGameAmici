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

                <!-- Equipment slots -->
                <div style="width:100%; margin-top:15px; display:flex; gap:8px; justify-content:center;">
                    <div class="equip-slot" data-slot="weapon" style="background:rgba(255,255,255,0.05); border:1px solid #444; border-radius:8px; padding:8px 12px; cursor:pointer; font-size:11px; text-align:center;" onclick="openEquipModal('weapon')">
                        <div style="font-size:18px;">🔪</div>Arma
                        <div id="slotWeapon" style="color:#888; font-size:10px; margin-top:3px;">VUOTO</div>
                    </div>
                    <div class="equip-slot" data-slot="armor" style="background:rgba(255,255,255,0.05); border:1px solid #444; border-radius:8px; padding:8px 12px; cursor:pointer; font-size:11px; text-align:center;" onclick="openEquipModal('armor')">
                        <div style="font-size:18px;">🛡️</div>Armatura
                        <div id="slotArmor" style="color:#888; font-size:10px; margin-top:3px;">VUOTO</div>
                    </div>
                    <div class="equip-slot" data-slot="accessory" style="background:rgba(255,255,255,0.05); border:1px solid #444; border-radius:8px; padding:8px 12px; cursor:pointer; font-size:11px; text-align:center;" onclick="openEquipModal('accessory')">
                        <div style="font-size:18px;">💍</div>Accessorio
                        <div id="slotAccessory" style="color:#888; font-size:10px; margin-top:3px;">VUOTO</div>
                    </div>
                </div>
                <?php endif; ?>

                            <!-- Admin link -->
                <div style="margin-top:12px;">
                    <a href="admin.php" style="color:var(--rpg-gold); font-family:'Orbitron'; font-size:12px; text-decoration:none; text-transform:uppercase; letter-spacing:1px;">
                        ℹ️ Aggiungi una Carta
                    </a>
                </div>

                <!-- Rarity counter -->
                <div class="rarities-counter" id="raritiesCounter"></div>
            </div>
        </div>
    <?php else: ?>
        <!-- Welcome banner when no card selected -->
        <div class="welcome-banner" style="max-width:550px; width:100%; text-align:center; padding:40px 20px; background:rgba(255,255,255,0.1); border-radius:20px; border:2px dashed var(--rpg-gold); margin-top:10px;">
            <h2 style="font-family:'Orbitron'; color:var(--rpg-gold); font-size:24px; margin-bottom:15px;">
                ✨ BENVENUTO NEL CARDS RPG ✨
            </h2>
            <p style="color:#ccc; font-size:14px; line-height:1.6; margin-bottom:20px;">
                Benvenuto nel gioco! Cerca un guerriero con la barra sopra,<br>
                oppure inizia subito tirando dal <strong style="color:var(--cyber-cyan);">Gacha</strong>!<br><br>
                Ogni carta ha statistiche uniche: <strong>Attacco</strong>, <strong>Vita</strong>, <strong>Difesa</strong>, <strong>Velocità</strong>.
                Colleziona carte, migliora i livelli, equipaggia armi e combatti nemici!
            </p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="?mode=gacha" class="gacha-btn single" style="text-decoration:none;">🎰 Inizia dal Gacha</a>
                <a href="?mode=gallery&author=Michele%20Castaldo" class="gacha-btn" style="text-decoration:none; border-color:var(--raro); color:var(--raro);">📖 Guarda Prima Carta</a>
            </div>
            <p style="color:#888; font-size:11px; margin-top:15px; font-family:'Share Tech Mono';">
                🇮🇹 {count($authors)} guerrieri disponibili | 8 rarità | Sistema level & equipaggiamenti
            </p>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top:20px; text-align:center; opacity:0.5; font-size:12px; letter-spacing:1px; font-family:'Orbitron';">
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
    const cardNames = Object.keys(ALL_CARDS);
    const results = cardNames.filter(name => name.toLowerCase().includes(val.toLowerCase())).slice(0, 8);
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
                playGlitchSound();
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

// Glitch sound syntetico (sostituisce glitch.mp3 mancante)
function playGlitchSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.type = 'sawtooth';
        gain.gain.value = 0.2;
        osc.frequency.setValueAtTime(400, ctx.currentTime);
        let t = ctx.currentTime;
        [1200, 300, 900, 150, 800].forEach((f, i) => {
            t += 0.05; osc.frequency.setValueAtTime(f, t);
        });
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

// =================== EQUIPAGGIAMENTO ===================
const EQUIP_KEY = 'equippedItems';
let equipped = JSON.parse(localStorage.getItem(EQUIP_KEY) || '{}');

// Dati equipaggiamento (da PHP)
const ALL_EQUIPMENT_PHP = <?php
require_once __DIR__ . '/../config/equipment.php';
echo json_encode($allEquipment);
?>;

// Carica equipaggiamento equipaggiato nella carta
function loadCardEquip(cardName) {
    const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
    const equippedSlots = save.equipped && save.equipped[cardName] || {};
    ['weapon','armor','accessory'].forEach(slot => {
        const el = document.getElementById('slot' + slot.charAt(0).toUpperCase() + slot.slice(1));
        if (el && equippedSlots[slot]) {
            el.textContent = `${equippedSlots[slot].nome}`;
            const rarityColor = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ab','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
            el.style.color = rarityColor[equippedSlots[slot].rarity] || '#888';
        }
    });
}

function openEquipModal(slot) {
    if (!matchedName) return;
    const itemsOfType = ALL_EQUIPMENT_PHP.filter(item => item.tipo === slot);
    let html = '<div style="padding:5px;">';
    html += `<h3 style="color:var(--rpg-gold); font-family:'Orbitron'; margin-bottom:15px; font-size:16px;">${slot === 'weapon' ? '🔪 Armi' : slot === 'armor' ? '🛡️ Armature' : '💍 Accessori'}</h3>`;
    if (itemsOfType.length === 0) {
        html += '<p style="color:#888;">Nessun equipaggiamento disponibile.</p>';
    } else {
        const rarityColor = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ab','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
        itemsOfType.forEach(item => {
            html += `<div style="background:rgba(0,0,0,0.3); border:1px solid ${rarityColor[item.rarity]||'#7f8c8d'}; border-radius:8px; padding:10px; margin-bottom:8px; cursor:pointer;" onclick="equipItem('${slot}', ${JSON.stringify(item)})">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:20px;">${item.slot_icon}</span>
                    <div style="text-align:left;">
                        <div style="font-weight:bold; color:${rarityColor[item.rarity]||'#7f8c8d'};">${item.nome}</div>
                        <div style="font-size:10px; color:#aaa;">${item.desc.substring(0, 70)}</div>
                        <div style="font-size:9px; color:#888; margin-top:3px;">
                            ${item.attacco ? 'ATK+'+item.attacco+' ' : ''}${item.vita ? 'HP+'+item.vita+' ' : ''}${item.difesa ? 'DEF+'+item.difesa+' ' : ''}${item.velocità ? 'SPD'+(item.velocità>0?'+':'')+item.velocità+' ' : ''}${item.crit ? 'CRIT+'+(item.crit*100|0)+'% ' : ''}
                        </div>
                    </div>
                </div>
            </div>`;
        });
    }
    html += '</div>';
    showModal('Equipaggia ' + matchedName, html);
}

function showModal(title, contentHtml) {
    const existing = document.getElementById('equipModal');
    if (existing) existing.remove();
    const modal = document.createElement('div');
    modal.id = 'equipModal';
    modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10001; display:flex; align-items:center; justify-content:center; padding:20px;';
    modal.innerHTML = `
        <div style="background:rgba(30,30,40,0.98); border:2px solid var(--rpg-gold); border-radius:15px; max-width:500px; width:100%; max-height:80vh; overflow-y:auto;">
            <div style="padding:15px 20px; background:rgba(0,0,0,0.4); border-bottom:1px solid var(--rpg-gold); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="color:var(--rpg-gold); font-family:'Orbitron'; margin:0;">${title}</h3>
                <button onclick="closeModal()" style="background:transparent; border:none; color:#aaa; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <div style="padding:15px;">${contentHtml}</div>
            <div style="padding:10px 20px; border-top:1px solid #333; text-align:right;">
                <button onclick="closeModal()" class="collect-btn" style="margin-top:0; padding:8px 20px;">Chiudi</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const m = document.getElementById('equipModal');
    if (m) m.remove();
}

function equipItem(slot, item) {
    const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
    if (!save.equipped) save.equipped = {};
    if (!save.equipped[matchedName]) save.equipped[matchedName] = {};
    save.equipped[matchedName][slot] = item;
    localStorage.setItem('cardGameSave', JSON.stringify(save));
    loadCardEquip(matchedName);
    const hint = document.createElement('div');
    hint.textContent = '✓ ' + item.nome + ' equipaggiato!';
    hint.style.cssText = 'position:fixed; bottom:80px; left:50%; transform:translateX(-50%); background:rgba(39,174,98,0.2); border:1px solid #27ae60; color:#fff; padding:8px 16px; border-radius:8px; font-family:Orbitron; font-size:12px; z-index:10001;';
    document.body.appendChild(hint);
    setTimeout(() => hint.remove(), 2000);
    closeModal();
}

// Carica equip su init se carta collezionata
if (collection[matchedName]) {
    setTimeout(() => loadCardEquip(matchedName), 100);
}
</script>
