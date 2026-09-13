<?php
// battle_view.php — Combattimento a turni contro un nemico (con sistema zone)
require_once __DIR__ . '/../config/enemies.php';
require_once __DIR__ . '/../config/zones.php';
require_once __DIR__ . '/../config/skills.php';

$selectedCardName = $_GET['card'] ?? null;
$selectedZone = $_GET['zone'] ?? 'foresta';
$selectedCard = null;
$playerStats = null;

if ($selectedCardName && isset($authors[$selectedCardName])) {
    $selectedCard = $authors[$selectedCardName];
    // HP battle = stats vita × moltiplicatore (render giocabile)
    $hpMult = 5;
    $playerStats = [
        'attacco'  => $selectedCard['stats']['forza'] ?? 50,
        'vita'     => ($selectedCard['stats']['mentalità'] ?? 50) * $hpMult,
        'maxVita'  => ($selectedCard['stats']['mentalità'] ?? 50) * $hpMult,
        'difesa'   => $selectedCard['stats']['tecnica'] ?? 50,
        'velocità' => $selectedCard['stats']['velocità'] ?? 50,
    ];
}

if (!$playerStats) {
    $selectedCardName = 'Michele Castaldo';
    $playerStats = ['attacco' => 20, 'vita' => 100, 'maxVita' => 100, 'difesa' => 15, 'velocità' => 15];
}

$zone = null;
foreach ($zones as $z) {
    if ($z['id'] === $selectedZone) { $zone = $z; break; }
}
if (!$zone) { $zone = $zones[0]; }

$enemyCandidates = [];
foreach ($enemies as $e) {
    if (in_array($e['nome'], $zone['enemy_pool'])) {
        $enemyCandidates[] = $e;
    }
}
if (empty($enemyCandidates)) $enemyCandidates = $enemies;
$enemy = $enemyCandidates[array_rand($enemyCandidates)];
$enemy['lvl'] = max($enemy['lvl'], $zone['livello_min']);
$ffMult = $zone['ff_reward_multiplier'] ?? 1.0;

// Skill della carta (pre-caricate per il JS)
$unlockedSkills = [];
if (isset($cardSkills[$selectedCardName])) {
    $unlockedSkills = $cardSkills[$selectedCardName];
} else {
    $allKeys = array_keys($skillNames);
    shuffle($allKeys);
    $unlockedSkills = array_slice($allKeys, 0, 2);
}
$skillsJson = json_encode([
    'skill_keys' => $unlockedSkills,
    'skill_defs' => $skillNames,
]);

// Equipaggiamento per drop (pre-caricato, non serve require nel JS)
$allEquipment = array_merge($weapons ?? [], $armors ?? [], $accessories ?? []);
$equipJson = json_encode($allEquipment);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combatti - Card RPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff; min-height: 100vh;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-8px) rotate(-3deg); }
            50% { transform: translateX(8px) rotate(3deg); }
            75% { transform: translateX(-6px) rotate(-2deg); }
        }
        @keyframes floatUp {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-80px) scale(1.3); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .battle-container {
            width: 100%; max-width: 800px; margin: 0 auto;
            padding: 80px 20px 140px; position: relative;
        }

        /* Zona selector */
        .zone-selector {
            display: flex; align-items: center; gap: 10px;
            background: rgba(0,0,0,0.4); border-radius: 10px;
            padding: 10px 16px; margin-bottom: 15px;
            border: 1px solid var(--rpg-gold, #d4af37);
        }
        .zone-selector span { font-family: 'Orbitron'; color: var(--rpg-gold, #d4af37); font-size: 11px; }
        .zone-selector select {
            background: rgba(0,0,0,0.6); color: #fff;
            border: 1px solid var(--rpg-gold, #d4af37);
            border-radius: 6px; padding: 4px 10px;
            font-family: 'Orbitron'; font-size: 12px; cursor: pointer;
        }

        /* HUD nemico */
        .battle-enemy-info {
            background: rgba(0,0,0,0.6); border-radius: 10px;
            padding: 10px 16px; width: fit-content; margin-left: auto;
            border: 1px solid #e74c3c; margin-bottom: 10px;
        }
        .entity-name { font-size: 15px; font-weight: bold; color: #e74c3c; margin-bottom: 5px; font-family: 'Orbitron'; }
        .entity-hp-bar { width: 200px; height: 14px; background: rgba(255,255,255,0.1); border-radius: 7px; overflow: hidden; border: 1px solid #333; }
        .entity-hp-fill { height: 100%; border-radius: 7px; transition: width 0.4s ease; }
        .entity-hp-text { font-size: 10px; color: #aaa; text-align: right; margin-top: 3px; font-family: 'Share Tech Mono'; }

        /* Arena */
        .battle-arena {
            display: flex; justify-content: space-around; align-items: center;
            min-height: 320px; position: relative;
            padding: 20px 0;
        }
        .entity { text-align: center; position: relative; }
        .entity-sprite {
            width: 130px; height: 130px; margin: 0 auto 10px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; overflow: hidden;
            background: rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.1);
        }
        .entity-sprite img { max-width: 90%; max-height: 90%; object-fit: contain; filter: drop-shadow(0 0 12px rgba(255,255,255,0.4)); }
        .entity.enemy .entity-sprite { background: rgba(231,76,60,0.15); border-color: rgba(231,76,60,0.4); }
        .entity.player .entity-sprite { background: rgba(0,243,255,0.1); border-color: rgba(0,243,255,0.3); }

        /* Player HUD */
        .battle-player-hud {
            background: rgba(0,0,0,0.5); border-radius: 12px;
            padding: 12px 16px; margin-top: 10px;
            border: 1px solid var(--cyber-cyan, #00f3ff);
        }
        .player-hp-bar-container { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .hp-bar-label { font-family: 'Orbitron'; font-size: 11px; color: var(--cyber-cyan, #00f3ff); min-width: 30px; }
        .player-hp-bar { width: 100%; height: 20px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; border: 1px solid #333; }
        .player-hp-fill { height: 100%; border-radius: 10px; transition: width 0.4s ease; background: linear-gradient(90deg, #27ae60, #2ecc71); }
        .player-stats-mini { font-size: 12px; color: var(--cyber-cyan, #00f3ff); font-family: 'Share Tech Mono'; text-align: center; }

        /* Timeline */
        .timeline { text-align: center; margin: 15px 0; }
        .timeline-title { font-size: 10px; color: #888; margin-bottom: 6px; font-family: 'Share Tech Mono'; }
        .timeline-bar { display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 22px; }
        .timeline-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255,255,255,0.08); border: 1px solid #444; }
        .timeline-arrow { color: var(--rpg-gold, #d4af37); font-weight: bold; }

        /* Action buttons */
        .battle-actions {
            display: flex; justify-content: center; gap: 12px;
            position: fixed; bottom: 20px; left: 0; width: 100%;
            padding: 0 20px; z-index: 100;
        }
        .action-btn {
            padding: 12px 20px; border: 2px solid var(--rpg-gold, #d4af37);
            background: rgba(0,0,0,0.6); color: var(--rpg-gold, #d4af37);
            font-family: 'Orbitron'; font-size: 13px; cursor: pointer;
            border-radius: 10px; transition: all 0.2s ease;
            box-shadow: 0 0 12px rgba(212,175,55,0.3);
        }
        .action-btn:hover:not(:disabled) { background: var(--rpg-gold, #d4af37); color: #000; transform: translateY(-2px); box-shadow: 0 4px 20px rgba(212,175,55,0.6); }
        .action-btn:disabled { opacity: 0.35; cursor: not-allowed; }
        .action-btn.attack { border-color: #3498db; color: #3498db; }
        .action-btn.skill { border-color: #8e44ad; color: #8e44ad; }
        .action-btn.item { border-color: #27ae60; color: #27ae60; }
        .action-btn.flee { border-color: #e74c3c; color: #e74c3c; }

        /* Log */
        .battle-log {
            background: rgba(0,0,0,0.7); border: 1px solid #333;
            border-radius: 10px; padding: 12px; margin-top: 15px;
            max-height: 180px; overflow-y: auto;
        }
        .log-entry { font-family: 'Share Tech Mono'; font-size: 11px; color: #ccc; margin-bottom: 4px; padding: 3px 6px; border-left: 2px solid #555; animation: slideIn 0.2s ease; }

        /* Damage number popup */
        .dmg-popup {
            position: absolute; font-family: 'Orbitron'; font-weight: 900;
            font-size: 28px; pointer-events: none; z-index: 200;
            text-shadow: 0 0 10px currentColor, 2px 2px 0 #000;
            animation: floatUp 1s ease-out forwards;
        }
        .dmg-popup.crit { font-size: 38px; }
        .dmg-popup.heal { color: #2ecc71; }
        .dmg-popup.normal { color: #e74c3c; }
        .dmg-popup.miss { color: #888; font-size: 20px; }
    </style>
</head>
<body class="body-<?php echo $zone['bg_class']; ?>">

<div class="battle-container" id="battleContainer">
    <div class="zone-selector">
        <span>📍 ZONA:</span>
        <select id="zoneSelect" onchange="changeZone()">
            <?php foreach ($zones as $z): ?>
                <option value="<?php echo $z['id']; ?>" <?php echo ($z['id'] === $selectedZone) ? 'selected' : ''; ?>>
                    <?php echo $z['nome']; ?> (Lv≥<?php echo $z['livello_min']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <span style="margin-left:auto; font-size:10px; color:#888; font-family:'Share Tech Mono';">
            FF x<?php echo $ffMult; ?> | XP bonus
        </span>
    </div>

    <div class="battle-enemy-info" id="enemyInfo">
        <div class="entity-name"><?php echo $enemy['nome']; ?> <span style="color:#ff6b6b;font-size:12px;">[Lv.<?php echo $enemy['lvl']; ?>]</span></div>
        <div class="entity-hp-bar"><div class="entity-hp-fill" id="enemyHpBar" style="width:100%;background:#e74c3c;"></div></div>
        <div class="entity-hp-text" id="enemyHpText"><?php echo $enemy['vita']; ?>/<?php echo $enemy['vita']; ?></div>
    </div>

    <div class="battle-arena">
        <!-- Nemico -->
        <div class="entity enemy" id="enemyEntity">
            <div class="entity-sprite" id="enemySprite">
                <img src="./images/<?php echo rawurlencode($enemy['img']); ?>"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='👾'">
            </div>
            <div class="entity-name"><?php echo $enemy['nome']; ?></div>
        </div>
        <!-- Giocatore -->
        <div class="entity player" id="playerEntity">
            <div class="entity-sprite" id="playerSprite">
                <img src="./images/<?php echo rawurlencode($selectedCardName); ?>.png"
                     onerror="this.src='https://via.placeholder.com/120?text=?'">
            </div>
            <div class="entity-name" style="color:var(--cyber-cyan);"><?php echo $selectedCardName; ?></div>
        </div>
    </div>

    <div class="battle-player-hud">
        <div class="player-hp-bar-container">
            <span class="hp-bar-label">HP</span>
            <div class="player-hp-bar"><div class="player-hp-fill" id="playerHpFill" style="width:100%;"></div></div>
            <span class="hp-bar-label" id="playerHpText"><?php echo $playerStats['vita']; ?>/<?php echo $playerStats['vita']; ?></span>
        </div>
        <div class="player-stats-mini">
            ⚔️ <?php echo $playerStats['attacco']; ?> | 🛡️ <?php echo $playerStats['difesa']; ?> | ⚡ <?php echo $playerStats['velocità']; ?>
        </div>
    </div>

    <div class="timeline">
        <div class="timeline-title">ORDINE TURNI</div>
        <div class="timeline-bar">
            <div class="timeline-icon" id="tl1">?</div>
            <div class="timeline-arrow">→</div>
            <div class="timeline-icon" id="tl2">?</div>
        </div>
    </div>

    <div class="battle-actions">
        <button class="action-btn attack" id="btnAttack" onclick="playerAttack()">⚔️ Attacca</button>
        <button class="action-btn skill" id="skillBtn" onclick="playerSkill()">⚡ Skill</button>
        <button class="action-btn item" onclick="useItem()">🧪 Item</button>
        <button class="action-btn flee" onclick="attemptFlee()">🏃 Scappa</button>
    </div>

    <div class="battle-log" id="battleLog"></div>
</div>

<script>
const PLAYER_STATS = <?php echo json_encode($playerStats); ?>;
const ENEMY_DATA = <?php echo json_encode(['nome' => $enemy['nome'], 'attacco' => $enemy['attacco'], 'vita' => $enemy['vita'], 'difesa' => $enemy['difesa'], 'velocità' => $enemy['velocità'], 'lvl' => $enemy['lvl'], 'xp_drop' => $enemy['xp_drop'], 'ff_drop_min' => $enemy['ff_drop_min'], 'ff_drop_max' => $enemy['ff_drop_max']]); ?>;
const CARD_NAME = <?php echo json_encode($selectedCardName); ?>;
const FF_MULT = <?php echo $ffMult; ?>;
const PLAYER_SKILLS = <?php echo $skillsJson; ?>;
const ALL_EQUIPMENT = <?php echo $equipJson; ?>;

let playerHp = PLAYER_STATS.vita;
const playerMaxHp = PLAYER_STATS.maxVita || PLAYER_STATS.vita;
let enemyHp = ENEMY_DATA.vita;
const enemyMaxHp = ENEMY_DATA.vita;
let playerTurn = true;
const skillDefs = PLAYER_SKILLS.skill_defs;
const unlockedSkillKeys = PLAYER_SKILLS.skill_keys || [];
let activeSkillIdx = 0;
let skillCooldown = 0;

function getActiveSkillDef() {
    if (!unlockedSkillKeys.length) return null;
    const key = unlockedSkillKeys[activeSkillIdx % unlockedSkillKeys.length];
    return skillDefs[key] || null;
}

function changeZone() {
    const zone = document.getElementById('zoneSelect').value;
    window.location.href = '?mode=game&card=' + encodeURIComponent(CARD_NAME) + '&zone=' + zone;
}

// Sound effect sintetico via Web Audio API
function playSfx(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        const cfg = {
            'attack':  { type: 'square',    freq: [400,300,200], dur: 0.15, gain: 0.2 },
            'hit':     { type: 'sawtooth',  freq: [200,100,50],  dur: 0.1,  gain: 0.3 },
            'victory': { type: 'sine',      freq: [523,659,784,1046], dur: 0.4, gain: 0.25 },
            'levelup': { type: 'sine',      freq: [392,523,659,784],  dur: 0.5, gain: 0.3 },
            'glitch':  { type: 'sawtooth',  freq: [400,1200,300,900], dur: 0.2, gain: 0.25 },
        }[type] || cfg['attack'];
        osc.type = cfg.type;
        gain.gain.value = cfg.gain;
        let t = ctx.currentTime;
        cfg.freq.forEach((f, i) => {
            osc.frequency.setValueAtTime(f, t);
            t += cfg.dur / cfg.freq.length;
        });
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + cfg.dur);
    } catch(e) {}
}

// Mostra numero danno fluttuante
function showDmgPopup(targetId, value, type) {
    const target = document.getElementById(targetId);
    if (!target) return;
    const rect = target.getBoundingClientRect();
    const popup = document.createElement('div');
    popup.className = 'dmg-popup ' + type;
    popup.textContent = type === 'heal' ? '+' + value : '-' + value;
    popup.style.left = (rect.left + rect.width / 2 - 20) + 'px';
    popup.style.top = (rect.top + 20) + 'px';
    document.body.appendChild(popup);
    setTimeout(() => popup.remove(), 1100);
}

function updateHpBars() {
    const pp = Math.max(0, (playerHp / playerMaxHp) * 100);
    const ep = Math.max(0, (enemyHp / enemyMaxHp) * 100);
    document.getElementById('playerHpFill').style.width = pp + '%';
    document.getElementById('enemyHpBar').style.width = ep + '%';
    document.getElementById('playerHpText').textContent = Math.max(0, playerHp) + '/' + playerMaxHp;
    document.getElementById('enemyHpText').textContent = Math.max(0, enemyHp) + '/' + enemyMaxHp;
    if (pp < 30) document.getElementById('playerHpFill').style.background = 'linear-gradient(90deg,#e74c3c,#c0392b)';
    if (ep < 30) document.getElementById('enemyHpBar').style.background = '#c0392b';
}

function addLog(msg) {
    const log = document.getElementById('battleLog');
    const e = document.createElement('div');
    e.className = 'log-entry';
    e.innerHTML = msg;
    log.prepend(e);
    while (log.children.length > 12) log.lastChild.remove();
}

function calcDamage(atk, def) {
    // Formula bilanciata: danno minimo 3, massima riduzione difesa 60%
    const reducedDef = def * 0.6;
    let dmg = atk - reducedDef;
    const isCrit = Math.random() < 0.10;
    if (isCrit) { dmg *= 1.5; }
    return Math.max(3, Math.floor(dmg));
}

function animateHit(spriteId) {
    const el = document.getElementById(spriteId);
    if (!el) return;
    el.style.animation = 'shake 0.35s';
    setTimeout(() => { el.style.animation = ''; }, 350);
}

function playerAttack() {
    if (!playerTurn) return;
    const dmg = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
    const isCrit = Math.random() < 0.08;
    enemyHp -= dmg;
    playSfx('attack');
    animateHit('enemySprite');
    showDmgPopup('enemyEntity', isCrit ? dmg + '!' : dmg, isCrit ? 'crit' : 'normal');
    addLog('⚔️ ' + (isCrit ? '<span style="color:#f1c40f">CRITICO! </span>' : '') + 'Hai inflitto <strong>' + dmg + '</strong> danni a ' + ENEMY_DATA.nome + '!');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    if (enemyHp <= 0) setTimeout(() => endBattle(true), 400);
    else setTimeout(() => enemyTurn(), 700);
    playerTurn = false;
}

function playerSkill() {
    if (skillCooldown > 0 || !playerTurn) return;
    const def = getActiveSkillDef();
    if (!def) { addLog('❌ Nessuna skill disponibile!'); return; }
    playSfx('attack');

    if (def.multiplier) {
        const dmg = Math.floor(calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa) * def.multiplier);
        enemyHp -= dmg;
        animateHit('enemySprite');
        showDmgPopup('enemyEntity', dmg + '!', 'crit');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: ' + dmg + ' danni!');
    } else if (def.heal_pct) {
        const heal = Math.floor(playerMaxHp * def.heal_pct);
        playerHp = Math.min(playerMaxHp, playerHp + heal);
        animateHit('playerSprite');
        showDmgPopup('playerEntity', heal, 'heal');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: recuperi ' + heal + ' HP!');
    } else if (def.double_attack) {
        const d1 = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
        const d2 = Math.floor(calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa) * 0.6);
        enemyHp -= (d1 + d2);
        animateHit('enemySprite');
        showDmgPopup('enemyEntity', d1 + ' + ' + d2, 'normal');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: ' + d1 + ' + ' + d2 + ' danni!');
    } else if (def.guaranteed_crit) {
        const dmg = Math.floor((PLAYER_STATS.attacco - ENEMY_DATA.difesa * 0.25) * 1.6);
        enemyHp -= dmg;
        animateHit('enemySprite');
        showDmgPopup('enemyEntity', dmg + '!', 'crit');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: CRIT GARANTITO ' + dmg + ' danni!');
    } else {
        addLog(def.icon + ' <strong>' + def.nome + '</strong> usato! (effetto in arrivo...)');
    }
    updateHpBars();
    skillCooldown = def.cd;
    activeSkillIdx = (activeSkillIdx + 1) % unlockedSkillKeys.length;
    updateSkillCd();
    if (enemyHp <= 0) setTimeout(() => endBattle(true), 400);
    else setTimeout(() => enemyTurn(), 700);
    playerTurn = false;
}

function useItem() {
    const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
    const potions = (inv.potions || 0);
    if (potions <= 0) {
        addLog('🧪 Nessun pozione disponibile. Vinci battaglie per ottenerne!');
        return;
    }
    inv.potions--;
    localStorage.setItem('equippedItems', JSON.stringify(inv));
    const heal = Math.floor(playerMaxHp * 0.3);
    playerHp = Math.min(playerMaxHp, playerHp + heal);
    showDmgPopup('playerEntity', heal, 'heal');
    addLog('🧪 Usi una pozione e recuperi ' + heal + ' HP! (rimangono ' + inv.potions + ')');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    playerTurn = false;
    setTimeout(() => enemyTurn(), 700);
}

function attemptFlee() {
    if (Math.random() > 0.4) {
        addLog('🏃 Sei fuggito!');
        setTimeout(() => { window.location.href = '?mode=gacha'; }, 800);
    } else {
        addLog('❌ Fuga fallita!');
        playerTurn = false;
        setTimeout(() => enemyTurn(), 600);
    }
}

function enemyTurn() {
    const dmg = calcDamage(ENEMY_DATA.attacco, PLAYER_STATS.difesa);
    playerHp -= dmg;
    playSfx('hit');
    animateHit('playerSprite');
    showDmgPopup('playerEntity', dmg, 'normal');
    addLog('👾 ' + ENEMY_DATA.nome + ' ti infligge <strong>' + dmg + '</strong> danni!');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    if (playerHp <= 0) {
        setTimeout(() => endBattle(false), 600);
    } else {
        playerTurn = true;
    }
}

function updateSkillCd() {
    const btn = document.getElementById('skillBtn');
    const def = getActiveSkillDef();
    if (!unlockedSkillKeys.length) {
        btn.disabled = true; btn.textContent = '⚡ Nessuna';
    } else if (skillCooldown > 0) {
        btn.disabled = true; btn.textContent = (def ? def.icon : '⚡') + ' ' + (def ? def.nome : 'Skill') + ' (' + skillCooldown + ')';
    } else {
        btn.disabled = false; btn.textContent = (def ? def.icon : '⚡') + ' ' + (def ? def.nome : 'Skill');
    }
}

function endBattle(victory, flee = false) {
    const log = document.getElementById('battleLog');
    if (flee) {
        setTimeout(() => window.location.href = '?mode=gacha', 500);
        return;
    }
    if (victory) {
        playSfx('victory');
        const xpGain = Math.floor(ENEMY_DATA.xp_drop * FF_MULT);
        const ffGain = Math.floor((Math.random() * (ENEMY_DATA.ff_drop_max - ENEMY_DATA.ff_drop_min + 1) + ENEMY_DATA.ff_drop_min) * FF_MULT);
        let dropMsg = '';
        // Drop pozione (30%)
        if (Math.random() < 0.30) {
            const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
            inv.potions = (inv.potions || 0) + 1;
            localStorage.setItem('equippedItems', JSON.stringify(inv));
            dropMsg = ' 🧪 +1 Pozione';
        }
        // Drop equip (12%)
        if (Math.random() < 0.12) {
            const drop = ALL_EQUIPMENT[Math.floor(Math.random() * ALL_EQUIPMENT.length)];
            const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
            if (!inv.inventory) inv.inventory = [];
            inv.inventory.push(drop);
            localStorage.setItem('equippedItems', JSON.stringify(inv));
            dropMsg += ' 🎁 <strong>' + drop.nome + '</strong>';
        }
        addLog('🏆 <span style="color:#2ecc71">VITTORIA!</span> +' + xpGain + ' XP, +' + ffGain + ' 💎' + dropMsg);
        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        save.xp = (save.xp || 0) + xpGain;
        const nextXp = Math.pow((save.level || 1) + 1, 2) * 100;
        if (save.xp >= nextXp) {
            save.level++; save.xp -= nextXp;
            playSfx('levelup');
            addLog('🎉 <span style="color:#f1c40f">LEVEL UP! Liv. ' + save.level + '</span>');
        }
        localStorage.setItem('cardGameSave', JSON.stringify(save));
        let ff = parseInt(localStorage.getItem('gachaCurrency') || '0');
        ff += ffGain;
        localStorage.setItem('gachaCurrency', ff.toString());
        const ffEl = document.getElementById('ffCount');
        if (ffEl) ffEl.textContent = ff;
        setTimeout(() => window.location.href = '?mode=gacha', 2200);
    } else {
        playSfx('hit');
        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        const lostXp = Math.floor((save.xp || 0) * 0.1);
        save.xp = Math.max(0, (save.xp || 0) - lostXp);
        localStorage.setItem('cardGameSave', JSON.stringify(save));
        addLog('<span style="color:#e74c3c">💀 SCONFITTO!</span> Perditi ' + lostXp + ' XP.');
        setTimeout(() => window.location.href = '?mode=gacha', 2000);
    }
}

function initTimeline() {
    const t1 = document.getElementById('tl1');
    const t2 = document.getElementById('tl2');
    if (PLAYER_STATS.velocità >= ENEMY_DATA.velocità) {
        t1.textContent = '👤'; t2.textContent = '👾';
    } else {
        t1.textContent = '👾'; t2.textContent = '👤';
    }
}

window.addEventListener('DOMContentLoaded', () => {
    updateHpBars();
    initTimeline();
    updateSkillCd();
    addLog('⚔️ <strong>' + ENEMY_DATA.nome + '</strong> (Lv.' + ENEMY_DATA.lvl + ') appare! ' + (PLAYER_STATS.velocità >= ENEMY_DATA.velocità ? 'Tu attacchi primo!' : 'Il nemico è più veloce!'));
    setTimeout(() => {
        if (PLAYER_STATS.velocità >= ENEMY_DATA.velocità) {
            playerTurn = true;
        } else {
            addLog('👾 Il nemico attacca per primo!');
            setTimeout(() => enemyTurn(), 800);
            playerTurn = false;
        }
    }, 1000);
});
</script>
</body>
</html>
