<?php
// battle_view.php — Combattimento a turni contro un nemico (con sistema zone)
require_once __DIR__ . '/../config/enemies.php';
require_once __DIR__ . '/../config/zones.php';

$selectedCardName = $_GET['card'] ?? null;
$selectedZone = $_GET['zone'] ?? 'foresta';
$selectedCard = null;
$playerStats = null;

// Se c'è una carta selezionata, usa i suoi stats potenziati
if ($selectedCardName) {
    if (isset($authors[$selectedCardName])) {
        $selectedCard = $authors[$selectedCardName];
        $playerStats = [
            'attacco'  => $selectedCard['stats']['forza'] ?? 50,
            'vita'     => $selectedCard['stats']['mentalità'] ?? 50,
            'difesa'   => $selectedCard['stats']['tecnica'] ?? 50,
            'velocità' => $selectedCard['stats']['velocità'] ?? 50,
        ];
    }
}

// Se nessuna carta selezionata, usa una default
if (!$playerStats) {
    $selectedCardName = 'Michele Castaldo';
    $playerStats = ['attacco' => 22, 'vita' => 12, 'difesa' => 18, 'velocità' => 15];
}

// Trova il nemico in base alla zona (fallback a tutti)
$zone = null;
foreach ($zones as $z) {
    if ($z['id'] === $selectedZone) { $zone = $z; break; }
}
if (!$zone) { $zone = $zones[0]; }

// Filtra nemici per zona (se l'nemico è nel pool zona), altrimenti tutti
$enemyCandidates = [];
foreach ($enemies as $e) {
    if (in_array($e['nome'], $zone['enemy_pool'])) {
        $enemyCandidates[] = $e;
    }
}
if (empty($enemyCandidates)) $enemyCandidates = $enemies;
$enemy = $enemyCandidates[array_rand($enemyCandidates)];
// Scale enemy by zone difficulty
$enemy['lvl'] = max($enemy['lvl'], $zone['livello_min']);
$ffMult = $zone['ff_reward_multiplier'] ?? 1.0;

// HP correnti = vita base
$playerCurrentHp = $playerStats['vita'];
$enemyCurrentHp = $enemy['vita'];
?>

<div class="battle-container" id="battleContainer">
    <!-- HUD combattimento -->
    <div class="battle-hud">
        <div class="zone-selector" style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <span style="font-family:'Orbitron'; color:var(--rpg-gold); font-size:11px;">📍 ZONA:</span>
            <select id="zoneSelect" onchange="changeZone()" style="background:rgba(0,0,0,0.4); color:#fff; border:1px solid var(--rpg-gold); border-radius:6px; padding:4px 8px; font-family:'Orbitron'; font-size:11px;">
                <?php foreach ($zones as $z): ?>
                    <option value="<?php echo $z['id']; ?>" <?php echo ($z['id'] === $selectedZone) ? 'selected' : ''; ?>>
                        <?php echo $z['nome']; ?> (Lv≥<?php echo $z['livello_min']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="battle-enemy-info">
            <div class="entity-name"><?php echo $enemy['nome']; ?> <span style="color:#ff6b6b; font-size:12px;">[Lv.<?php echo $enemy['lvl']; ?>]</span></div>
            <div class="entity-hp-bar">
                <div class="entity-hp-fill" id="enemyHpBar" style="width: 100%; background: #e74c3c;"></div>
            </div>
            <div class="entity-hp-text" id="enemyHpText"><?php echo $enemy['vita']; ?>/<?php echo $enemy['vita']; ?></div>
        </div>
    </div>

    <!-- Area combattimento -->
    <div class="battle-arena">
        <!-- Nemico -->
        <div class="entity enemy" id="enemyEntity">
            <div class="entity-sprite" id="enemySprite">
                <img src="./images/<?php echo rawurlencode($enemy['img']); ?>"
                     onerror="this.style.display='none'; document.getElementById('enemySprite').innerHTML='<div style=\"font-size:48px;\">👾</div>';"
                     alt="<?php echo $enemy['nome']; ?>"
                     style="max-width:100%; max-height:100%; filter: drop-shadow(0 0 15px rgba(231,76,60,0.5));">
            </div>
            <div class="entity-name"><?php echo $enemy['nome']; ?> <span style="color:#ff6b6b; font-size:12px;">[Lv.<?php echo $enemy['lvl']; ?>]</span></div>
        </div>

        <!-- Giocatore -->
        <div class="entity player" id="playerEntity">
            <div class="entity-sprite" id="playerSprite">
                <img src="./images/<?php echo rawurlencode($selectedCardName); ?>.png"
                     onerror="this.src='https://via.placeholder.com/100?text=?'">
            </div>
            <div class="entity-name"><?php echo $selectedCardName; ?> <span style="color:var(--cyber-cyan); font-size:12px;">[Lv.1]</span></div>
        </div>
    </div>

    <!-- HUD giocatore -->
    <div class="battle-player-hud">
        <div class="player-hp-bar-container">
            <span class="hp-bar-label">HP</span>
            <div class="player-hp-bar" id="playerHpBar">
                <div class="player-hp-fill" id="playerHpFill" style="width: 100%; height: 100%; background: linear-gradient(90deg, #27ae60, #2ecc71);"></div>
            </div>
            <span class="hp-bar-label" id="playerHpText"><?php echo $playerStats['vita']; ?>/<?php echo $playerStats['vita']; ?></span>
        </div>
        <div class="player-stats-mini" id="playerStatsMini">
            ⚔️ <?php echo $playerStats['attacco']; ?> | 🛡️ <?php echo $playerStats['difesa']; ?> | ⚡ <?php echo $playerStats['velocità']; ?>
        </div>
    </div>

    <!-- Timeline turni -->
    <div class="timeline" id="timeline">
        <div class="timeline-title">Ordine Turni</div>
        <div class="timeline-bar">
            <div class="timeline-icon" id="tl1">?</div>
            <div class="timeline-arrow">→</div>
            <div class="timeline-icon" id="tl2">?</div>
        </div>
    </div>

    <!-- Pulsanti azione -->
    <div class="battle-actions" id="battleActions">
        <button class="action-btn attack" onclick="playerAttack()">⚔️ Attacca</button>
        <button class="action-btn skill" id="skillBtn" onclick="playerSkill()" disabled>⚡ Skill</button>
        <button class="action-btn item" onclick="useItem()">🧪 Item</button>
        <button class="action-btn flee" onclick="attemptFlee()">🏃 Scappa</button>
    </div>

    <!-- Log combattimento -->
    <div class="battle-log" id="battleLog"></div>
</div>

<script>
// Dati di combattimento (generati da PHP)
const PLAYER_STATS = <?php echo json_encode($playerStats); ?>;
const ENEMY_DATA = <?php echo json_encode($enemy); ?>;
const CARD_NAME = <?php echo json_encode($selectedCardName); ?>;
const FF_MULT = <?php echo $ffMult; ?>;

// Skill del giocatore (se sbloccate) — caricate da PHP
const PLAYER_SKILLS = <?php
require_once __DIR__ . '/../config/skills.php';
// Ottieni skill della carta (se in mappa, o random)
$unlocked = [];
if (isset($cardSkills[$selectedCardName])) {
    $unlocked = $cardSkills[$selectedCardName];
} else {
    // Random 2 skill
    $allKeys = array_keys($skillNames);
    shuffle($allKeys);
    $unlocked = array_slice($allKeys, 0, 2);
}
echo json_encode([
    'skill_keys' => $unlocked,
    'skill_defs' => $skillNames,
]);
?>;

// Cambia zona (ricarica pagina con nuova zona + carta)
function changeZone() {
    const zone = document.getElementById('zoneSelect').value;
    window.location.href = '?mode=game&card=' + encodeURIComponent(CARD_NAME) + '&zone=' + zone;
}

// Sound effect helper — sintetizza suoni via Web Audio API (evita file mancanti)
function playSfx(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        const cfg = {
            'attack': { type: 'square', freq: [400, 300, 200], dur: 0.15, gain: 0.2 },
            'hit':    { type: 'sawtooth', freq: [200, 100, 50], dur: 0.1, gain: 0.3 },
            'victory': { type: 'sine', freq: [523, 659, 784, 1046], dur: 0.4, gain: 0.25 },
            'levelup': { type: 'sine', freq: [392, 523, 659, 784], dur: 0.5, gain: 0.3 },
            'glitch':   { type: 'sawtooth', freq: [400, 1200, 300, 900], dur: 0.2, gain: 0.25 },
        }[type] || cfg_attack;

        osc.type = cfg.type;
        gain.gain.value = cfg.gain;
        osc.frequency.setValueAtTime(cfg.freq[0], ctx.currentTime);

        let t = ctx.currentTime;
        for (let i = 1; i < cfg.freq.length; i++) {
            t += cfg.dur / cfg.freq.length;
            osc.frequency.setValueAtTime(cfg.freq[i], t);
        }
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + cfg.dur);
    } catch(e) {
        // Web Audio non supportato
    }
}
const cfg_attack = { type: 'square', freq: [400,300,200], dur: 0.15, gain: 0.2 };

let playerHp = PLAYER_STATS.vita;
const playerMaxHp = PLAYER_STATS.vita;
let enemyHp = ENEMY_DATA.vita;
const enemyMaxHp = ENEMY_DATA.vita;

let playerTurn = true;
// Sistema skill (usa prima skill sbloccata)
const skillDefs = PLAYER_SKILLS.skill_defs;
const unlockedSkillKeys = PLAYER_SKILLS.skill_keys || [];
let activeSkill = unlockedSkillKeys[0] || 'potere_furia'; // default
let skillCooldown = 0;

function getSkillDef(key) {
    return skillDefs[key] || skillDefs['potere_furia'];
}

// Aggiorna HP bar
function updateHpBars() {
    const playerPct = Math.max(0, (playerHp / playerMaxHp) * 100);
    const enemyPct = Math.max(0, (enemyHp / enemyMaxHp) * 100);

    document.getElementById('playerHpFill').style.width = playerPct + '%';
    document.getElementById('enemyHpBar').style.width = enemyPct + '%';
    document.getElementById('playerHpText').textContent = playerHp + '/' + playerMaxHp;
    document.getElementById('enemyHpText').textContent = enemyHp + '/' + enemyMaxHp;

    // Cambia colore barra se sotto soglia
    if (playerPct < 30) {
        document.getElementById('playerHpFill').style.background = 'linear-gradient(90deg, #e74c3c, #c0392b)';
    }
    if (enemyPct < 30) {
        document.getElementById('enemyHpBar').style.background = '#c0392b';
    }
}

// Aggiungi log
function addLog(msg) {
    const log = document.getElementById('battleLog');
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.textContent = msg;
    log.prepend(entry);
    // Limita log
    while (log.children.length > 10) log.lastChild.remove();
}

// Calcola danni
function calcDamage(atk, def) {
    let dmg = atk - (def * 0.3);
    if (Math.random() < 0.05) { // 5% crit
        dmg *= 1.5;
        addLog('⚡ COLPO CRITICO!');
    }
    return Math.max(1, Math.floor(dmg));
}

// Animazione sprite (movimento + shake colpo)
function animateSprite(element, isAttack) {
    element.style.transition = 'transform 0.15s ease';
    element.style.transform = isAttack ? 'translateX(20px)' : 'translateX(-20px)';
    // Shake il nemico quando colpito
    if (!isAttack) {
        const enemySprite = document.getElementById('enemySprite');
        enemySprite.style.animation = 'shake 0.4s';
        setTimeout(() => { enemySprite.style.animation = ''; }, 400);
    }
    // Shake il giocatore quando colpito
    if (isAttack) {
        const playerSprite = document.getElementById('playerSprite');
        playerSprite.style.animation = 'shake 0.4s';
        setTimeout(() => { playerSprite.style.animation = ''; }, 400);
    }
    setTimeout(() => {
        element.style.transform = 'translateX(0)';
    }, 200);
}

// Turno giocatore
function playerAttack() {
    if (!playerTurn) return;
    const dmg = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
    enemyHp -= dmg;
    playSfx('attack');
    animateSprite(document.getElementById('enemySprite'), false);
    addLog('⚔️ Hai inflitto ' + dmg + ' danni a ' + ENEMY_DATA.nome + '!');
    updateHpBars();

    // Cooldown skill
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();

    if (enemyHp <= 0) {
        setTimeout(() => endBattle(true), 300);
    } else {
        setTimeout(() => enemyTurn(), 800);
    }
    playerTurn = false;
}

function playerSkill() {
    if (skillCooldown > 0) return;
    if (!playerTurn) return;
    const def = getSkillDef(activeSkill);
    playSfx('attack');
    animateSprite(document.getElementById('enemySprite'), false);

    // Gestione skill diversi
    if (def.multiplier) {
        const dmg = Math.floor(calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa) * def.multiplier);
        enemyHp -= dmg;
        addLog(def.icon + ' Hai usato ' + def.nome + ' e inflitto ' + dmg + ' danni!');
        updateHpBars();
    } else if (def.heal_pct) {
        const heal = Math.floor(playerMaxHp * def.heal_pct);
        playerHp = Math.min(playerMaxHp, playerHp + heal);
        addLog(def.icon + ' Hai usato ' + def.nome + ' e curato ' + heal + ' HP!');
        updateHpBars();
    } else if (def.double_attack) {
        const dmg1 = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
        const dmg2 = Math.floor(calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa) * 0.7);
        enemyHp -= (dmg1 + dmg2);
        addLog(def.icon + ' Hai usato ' + def.nome + ': due colpi (' + dmg1 + ' + ' + dmg2 + ')!');
        updateHpBars();
    } else if (def.guaranteed_crit) {
        let dmg = PLAYER_STATS.attacco - (ENEMY_DATA.difesa * 0.3);
        dmg = Math.floor(dmg * 1.5);
        enemyHp -= dmg;
        addLog(def.icon + ' Hai usato ' + def.nome + ' e inflitto ' + dmg + ' danni (CRIT GARANTITO)!');
        updateHpBars();
    } else if (def.buff) {
        addLog(def.icon + ' Hai usato ' + def.nome + ': aumenti la difesa per 2 turni!');
        // TODO: implementare buff timer
    }

    skillCooldown = def.cd;
    updateSkillCd();

    if (enemyHp <= 0) {
        setTimeout(() => endBattle(true), 300);
    } else {
        setTimeout(() => enemyTurn(), 800);
    }
    playerTurn = false;
}

function useItem() {
    // TODO: sistema item
    addLog('🧪 Non hai item disponibili.');
}

function attemptFlee() {
    if (Math.random() > 0.5) {
        addLog('🏃 Sei fuggito con successo!');
        setTimeout(() => endBattle(false, true), 500);
    } else {
        addLog('❌ Fuga fallita!');
        setTimeout(() => enemyTurn(), 500);
    }
}

// Turno nemico
function enemyTurn() {
    const dmg = calcDamage(ENEMY_DATA.attacco, PLAYER_STATS.difesa);
    playerHp -= dmg;
    animateSprite(document.getElementById('playerSprite'), true);
    addLog(ENEMY_DATA.nome + ' ti ha inflitto ' + dmg + ' danni!');
    updateHpBars();

    if (playerHp <= 0) {
        setTimeout(() => endBattle(false), 800);
    } else {
        playerTurn = true;
    }
}

function updateSkillCd() {
    const btn = document.getElementById('skillBtn');
    const def = getSkillDef(activeSkill);
    if (unlockedSkillKeys.length === 0) {
        btn.disabled = true;
        btn.textContent = '⚡ Nessuna Skill';
        return;
    }
    if (skillCooldown > 0) {
        btn.disabled = true;
        btn.textContent = def.icon + ' ' + def.nome + ' (' + skillCooldown + ')';
    } else {
        btn.disabled = false;
        btn.textContent = def.icon + ' ' + def.nome;
    }
}

// Fine combattimento
function endBattle(victory, flee = false) {
    const log = document.getElementById('battleLog');
    if (flee) {
        log.innerHTML = '<div class="log-entry" style="color:var(--cyber-cyan);">Hai fuggito dalla battaglia!</div>' + log.innerHTML;
        setTimeout(() => window.location.href = '?mode=gacha', 1500);
        return;
    }
    if (victory) {
        playSfx('victory');

        // Drop equipaggiamento (15% chance per vittoria)
        const ALL_EQUIPMENT_LOCAL = <?php
        require_once __DIR__ . '/../config/equipment.php';
        echo json_encode($allEquipment);
        ?>;
        let dropMsg = '';
        if (Math.random() < 0.15) {
            const drop = ALL_EQUIPMENT_LOCAL[Math.floor(Math.random() * ALL_EQUIPMENT_LOCAL.length)];
            const equipSave = JSON.parse(localStorage.getItem('equippedItems') || '{}');
            if (!equipSave.inventory) equipSave.inventory = [];
            equipSave.inventory.push(drop);
            localStorage.setItem('equippedItems', JSON.stringify(equipSave));
            dropMsg = ' 🎁 Drop: <strong>' + drop.nome + '</strong> (' + drop.tipo + ')';
        }

        const xpGain = Math.floor(ENEMY_DATA.xp_drop * FF_MULT);
        const ffGain = Math.floor((Math.random() * (ENEMY_DATA.ff_drop_max - ENEMY_DATA.ff_drop_min + 1) + ENEMY_DATA.ff_drop_min) * FF_MULT);
        addLog('🏆 HAI VINTO! Guadagnato: ' + xpGain + ' XP, ' + ffGain + ' Fragment' + dropMsg);

        // Aggiungi XP e FF al salvataggio
        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        save.xp = (save.xp || 0) + xpGain;
        // Level up check
        const nextXp = Math.pow((save.level || 1) + 1, 2) * 100;
        if (save.xp >= nextXp) {
            save.level = (save.level || 1) + 1;
            save.xp = save.xp - nextXp;
            save.levelUp = true;
            playSfx('levelup');
            addLog('🎉 LEVEL UP! Sei ora al livello ' + save.level + '!');
        }
        localStorage.setItem('cardGameSave', JSON.stringify(save));

        let ff = parseInt(localStorage.getItem('gachaCurrency') || '0');
        ff += ffGain;
        localStorage.setItem('gachaCurrency', ff.toString());
        document.getElementById('ffCount').textContent = ff;

        setTimeout(() => {
            window.location.href = '?mode=gacha';
        }, 2000);
    } else {
        log.innerHTML = '<div class="log-entry" style="color:#e74c3c;">SEI SCONFITTO! Hai perso 10% XP.</div>' + log.innerHTML;
        setTimeout(() => window.location.href = '?mode=gacha', 2000);
    }
}

// Inizializza timeline
function initTimeline() {
    const t1 = document.getElementById('tl1');
    const t2 = document.getElementById('tl2');
    // Ordine basato su velocità
    if (PLAYER_STATS.velocità >= ENEMY_DATA.velocità) {
        t1.textContent = '👤';
        t2.textContent = '👾';
    } else {
        t1.textContent = '👾';
        t2.textContent = '👤';
    }
}

// Inizializza su load
window.addEventListener('DOMContentLoaded', () => {
    updateHpBars();
    initTimeline();
    addLog('⚔️ La battaglia è iniziata! ' + ENEMY_DATA.nome + ' (Lv.' + ENEMY_DATA.lvl + ') sei pronto?');
    setTimeout(() => {
        // Se il giocatore è più veloce, inizia lui
        if (PLAYER_STATS.velocità >= ENEMY_DATA.velocità) {
            playerTurn = true;
        } else {
            addLog('👾 Il nemico inizia!');
            setTimeout(() => enemyTurn(), 1000);
            playerTurn = false;
        }
    }, 1000);
});
</script>

<style>
/* ===== COMBATTIMENTO STYLES ===== */
.battle-container {
    width: 100%; max-width: 800px; margin: 0 auto;
    padding: 90px 20px 130px; position: relative;
    font-family: 'Orbitron', sans-serif;
}

@keyframes shake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-5px) rotate(-2deg); }
    50% { transform: translateX(5px) rotate(2deg); }
    75% { transform: translateX(-5px) rotate(-2deg); }
}

.battle-hud {
    position: absolute; top: 0; left: 0; width: 100%;
    padding: 10px 20px; z-index: 10;
}
.battle-enemy-info {
    background: rgba(0,0,0,0.5); border-radius: 10px;
    padding: 10px 15px; width: fit-content; margin-left: auto;
    border: 1px solid #e74c3c;
}
.entity-name { font-size: 16px; font-weight: bold; color: #e74c3c; margin-bottom: 5px; }
.entity-hp-bar {
    width: 200px; height: 12px; background: rgba(255,255,255,0.1);
    border-radius: 6px; border: 1px solid #555; overflow: hidden;
}
.entity-hp-fill { height: 100%; border-radius: 6px; transition: width 0.3s ease; }
.entity-hp-text { font-size: 11px; color: #aaa; text-align: right; }

.battle-arena {
    display: flex; justify-content: space-around; align-items: center;
    min-height: 400px; position: relative;
    padding: 20px 0;
}
.entity { text-align: center; }
.entity-sprite {
    width: 120px; height: 120px; margin: 0 auto 10px;
    display: flex; align-items: center; justify-content: center;
}
.entity-sprite img { max-width: 100%; max-height: 100%; filter: drop-shadow(0 0 10px rgba(255,255,255,0.5)); }
.entity.player { margin-top: 0; }
.entity.enemy { }
.entity.enemy .entity-sprite { background: rgba(255,0,0,0.1); border-radius: 50%; }

.battle-player-hud {
    position: absolute; bottom: 130px; left: 0; width: 100%;
    padding: 0 20px; z-index: 10;
}
.player-hp-bar-container {
    display: flex; align-items: center; gap: 8px;
    background: rgba(0,0,0,0.5); border-radius: 10px;
    padding: 8px 12px; width: fit-content; margin: 0 auto;
    border: 1px solid var(--cyber-cyan);
}
.player-hp-bar { width: 200px; height: 18px; background: rgba(255,255,255,0.1); border-radius: 9px; overflow: hidden; }
.player-hp-fill { height: 100%; border-radius: 9px; transition: width 0.3s ease; }
.player-stats-mini {
    text-align: center; margin-top: 8px; font-size: 13px;
    color: var(--cyber-cyan); font-family: 'Share Tech Mono';
}

.timeline {
    position: absolute; bottom: 90px; left: 0; width: 100%;
    text-align: center; z-index: 10;
}
.timeline-title {
    font-size: 12px; color: #aaa; margin-bottom: 8px;
    font-family: 'Share Tech Mono';
}
.timeline-bar {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; font-size: 20px;
}
.timeline-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.timeline-arrow { color: var(--rpg-gold); font-weight: bold; }

.battle-actions {
    position: absolute; bottom: 20px; left: 0; width: 100%;
    display: flex; justify-content: center; gap: 12px; z-index: 10;
    padding: 0 20px;
}
.action-btn {
    padding: 10px 18px; border: 2px solid var(--rpg-gold);
    background: rgba(0,0,0,0.5); color: var(--rpg-gold);
    font-family: 'Orbitron'; font-size: 14px; cursor: pointer;
    border-radius: 8px; transition: all 0.3s ease;
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
}
.action-btn:hover {
    background: var(--rpg-gold); color: #000;
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
    transform: translateY(-2px);
}
.action-btn.attack { border-color: #3498db; color: #3498db; }
.action-btn.attack:hover { border-color: var(--rpg-gold); }
.action-btn.skill { border-color: var(--epico); color: var(--epico); }
.action-btn.item { border-color: var(--non-comune); color: var(--non-comune); }
.action-btn.flee { border-color: #e74c3c; color: #e74c3c; }
.action-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.battle-log {
    position: absolute; top: 200px; right: 20px;
    width: 250px; max-height: 300px; overflow-y: auto;
    background: rgba(0,0,0,0.7); border: 1px solid #444;
    border-radius: 10px; padding: 10px; z-index: 10;
}
.log-entry {
    font-family: 'Share Tech Mono'; font-size: 11px;
    color: #ddd; margin-bottom: 5px; padding: 4px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
</style>
