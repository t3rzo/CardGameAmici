<?php
// battle_view.php — Combattimento a turni completo e funzionante
require_once __DIR__ . '/../config/enemies.php';
require_once __DIR__ . '/../config/zones.php';
require_once __DIR__ . '/../config/skills.php';

$selectedCardName = $_GET['card'] ?? null;
$selectedZone = $_GET['zone'] ?? 'foresta';
$selectedCard = null;
$playerStats = null;

if ($selectedCardName && isset($authors[$selectedCardName])) {
    $selectedCard = $authors[$selectedCardName];
    $hpMult = 5; // Moltiplicatore HP per rendere il gioco giocabile
    $rawStats = $selectedCard['stats'];
    $playerStats = [
        'attacco'  => $rawStats['forza'] ?? 50,
        'vita'     => ($rawStats['mentalita'] ?? 50) * $hpMult,
        'maxVita'  => ($rawStats['mentalita'] ?? 50) * $hpMult,
        'difesa'   => $rawStats['tecnica'] ?? 50,
        'velocita' => $rawStats['velocita'] ?? 50,
    ];
}

if (!$playerStats) {
    $selectedCardName = 'Michele Castaldo';
    $playerStats = ['attacco' => 20, 'vita' => 100, 'maxVita' => 100, 'difesa' => 15, 'velocita' => 15];
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

// Skill della carta
$unlockedSkills = [];
if (isset($cardSkills[$selectedCardName])) {
    $unlockedSkills = $cardSkills[$selectedCardName];
} else {
    $allKeys = array_keys($skillNames);
    shuffle($allKeys);
    $unlockedSkills = array_slice($allKeys, 0, 2);
}
$skillsJson = json_encode(['skill_keys' => $unlockedSkills, 'skill_defs' => $skillNames]);

// Equipaggiamento per drop
$allEquipment = array_merge($weapons ?? [], $armors ?? [], $accessories ?? []);
$equipJson = json_encode($allEquipment);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Combatti - Card RPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff; min-height: 100vh; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-8px) rotate(-3deg); } 50% { transform: translateX(8px) rotate(3deg); } 75% { transform: translateX(-6px); } }
        @keyframes floatUp { 0% { opacity: 1; transform: translateY(0) scale(1); } 100% { opacity: 0; transform: translateY(-80px) scale(1.3); } }
        .battle-container { width: 100%; max-width: 800px; margin: 0 auto; padding: 80px 20px 160px; }
        .zone-selector { display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.4); padding: 10px 16px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #d4af37; }
        .zone-selector select { background: rgba(0,0,0,0.6); color: #fff; border: 1px solid #d4af37; padding: 4px 10px; border-radius: 6px; font-family: 'Orbitron'; }
        .battle-enemy-info { background: rgba(0,0,0,0.6); padding: 10px 16px; border-radius: 10px; margin-left: auto; border: 1px solid #e74c3c; width: fit-content; }
        .entity-name { font-size: 15px; font-weight: bold; color: #e74c3c; font-family: 'Orbitron'; }
        .entity-hp-bar { width: 200px; height: 14px; background: rgba(255,255,255,0.1); border-radius: 7px; overflow: hidden; margin-top: 5px; }
        .entity-hp-fill { height: 100%; transition: width 0.4s ease; }
        .battle-arena { display: flex; justify-content: space-around; align-items: center; min-height: 300px; padding: 20px 0; }
        .entity { text-align: center; }
        .entity-sprite { width: 120px; height: 120px; margin: 0 auto 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; background: rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.1); }
        .entity.enemy .entity-sprite { background: rgba(231,76,60,0.15); border-color: rgba(231,76,60,0.4); }
        .entity.player .entity-sprite { background: rgba(0,243,255,0.1); border-color: rgba(0,243,255,0.3); }
        .entity-sprite img { max-width: 90%; max-height: 90%; object-fit: contain; }
        .battle-player-hud { background: rgba(0,0,0,0.5); padding: 12px 16px; border-radius: 12px; margin-top: 10px; border: 1px solid #00f3ff; }
        .player-hp-bar { width: 100%; height: 20px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; margin: 8px 0; }
        .player-hp-fill { height: 100%; background: linear-gradient(90deg, #27ae60, #2ecc71); transition: width 0.4s ease; }
        .battle-actions { display: flex; justify-content: center; gap: 12px; position: fixed; bottom: 20px; left: 0; width: 100%; padding: 0 20px; z-index: 100; }
        .action-btn { padding: 12px 20px; border: 2px solid #d4af37; background: rgba(0,0,0,0.6); color: #d4af37; font-family: 'Orbitron'; cursor: pointer; border-radius: 10px; transition: all 0.2s; }
        .action-btn:hover:not(:disabled) { background: #d4af37; color: #000; transform: translateY(-2px); }
        .action-btn:disabled { opacity: 0.35; cursor: not-allowed; }
        .action-btn.attack { border-color: #3498db; color: #3498db; }
        .action-btn.skill { border-color: #8e44ad; color: #8e44ad; }
        .action-btn.flee { border-color: #e74c3c; color: #e74c3c; }
        .battle-log { background: rgba(0,0,0,0.7); border: 1px solid #333; border-radius: 10px; padding: 12px; max-height: 150px; overflow-y: auto; margin-top: 15px; }
        .log-entry { font-family: 'Share Tech Mono'; font-size: 11px; color: #ccc; margin-bottom: 4px; padding: 3px; border-left: 2px solid #555; }
        .dmg-popup { position: absolute; font-family: 'Orbitron'; font-weight: 900; font-size: 26px; pointer-events: none; z-index: 200; animation: floatUp 1s ease-out forwards; text-shadow: 0 0 10px currentColor; }

        /* Overlay risultato */
        .battle-result-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; justify-content: center; align-items: center; }
        .battle-result-box { background: rgba(10,10,20,0.98); border: 3px solid #d4af37; border-radius: 20px; padding: 40px; text-align: center; max-width: 450px; width: 90%; }
        .battle-result-box h2 { font-family: 'Orbitron'; font-size: 28px; margin-bottom: 15px; }
        .battle-result-box p { font-family: 'Share Tech Mono'; color: #ccc; margin-bottom: 25px; line-height: 1.6; }
        .result-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .result-btn { padding: 12px 20px; border: none; border-radius: 8px; font-family: 'Orbitron'; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .result-btn:hover { transform: translateY(-2px); }
        .btn-next { background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000; box-shadow: 0 4px 15px rgba(241,196,15,0.4); }
        .btn-retry { background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff; box-shadow: 0 4px 15px rgba(231,76,60,0.4); }
        .btn-home { background: rgba(0,243,255,0.2); color: #00f3ff; border: 2px solid #00f3ff; }
    </style>
</head>
<body>

<div class="battle-container">
    <div class="zone-selector">
        <span style="font-family:'Orbitron'; color:#d4af37;">📍 ZONA:</span>
        <select id="zoneSelect" onchange="changeZone()">
            <?php foreach ($zones as $z): ?>
                <option value="<?php echo $z['id']; ?>" <?php echo ($z['id'] === $selectedZone) ? 'selected' : ''; ?>>
                    <?php echo $z['nome']; ?> (Lv≥<?php echo $z['livello_min']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="battle-enemy-info">
        <div class="entity-name"><?php echo $enemy['nome']; ?> [Lv.<?php echo $enemy['lvl']; ?>]</div>
        <div class="entity-hp-bar"><div class="entity-hp-fill" id="enemyHpBar" style="width:100%;background:#e74c3c;"></div></div>
        <div style="font-size:10px; color:#aaa; text-align:right;" id="enemyHpText"><?php echo $enemy['vita']; ?>/<?php echo $enemy['vita']; ?></div>
    </div>

    <div class="battle-arena">
        <div class="entity enemy">
            <div class="entity-sprite" id="enemySprite">
                <img src="./images/<?php echo rawurlencode($enemy['img']); ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='👾'">
            </div>
            <div class="entity-name"><?php echo $enemy['nome']; ?></div>
        </div>
        <div class="entity player">
            <div class="entity-sprite" id="playerSprite">
                <img src="./images/<?php echo rawurlencode($selectedCardName); ?>.png" onerror="this.src='https://via.placeholder.com/120?text=?'">
            </div>
            <div class="entity-name" style="color:#00f3ff;"><?php echo $selectedCardName; ?></div>
        </div>
    </div>

    <div class="battle-player-hud">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-family:'Orbitron'; color:#00f3ff;">HP</span>
            <div class="player-hp-bar"><div class="player-hp-fill" id="playerHpFill" style="width:100%;"></div></div>
            <span style="font-family:'Orbitron'; color:#00f3ff;" id="playerHpText"><?php echo $playerStats['vita']; ?>/<?php echo $playerStats['vita']; ?></span>
        </div>
        <div style="font-size:12px; color:#00f3ff; font-family:'Share Tech Mono'; text-align:center; margin-top:5px;">
            ⚔️ <?php echo $playerStats['attacco']; ?> | 🛡️ <?php echo $playerStats['difesa']; ?> | ⚡ <?php echo $playerStats['velocita']; ?>
        </div>
    </div>

    <div class="battle-actions">
        <button class="action-btn attack" onclick="playerAttack()">⚔️ Attacca</button>
        <button class="action-btn skill" onclick="playerSkill()">⚡ Skill</button>
        <button class="action-btn flee" onclick="attemptFlee()">🏃 Scappa</button>
    </div>

    <div class="battle-log" id="battleLog"></div>
</div>

<!-- Overlay risultato -->
<div class="battle-result-overlay" id="battleResultOverlay">
    <div class="battle-result-box">
        <h2 id="resultTitle"></h2>
        <p id="resultMsg"></p>
        <div class="result-btns">
            <button class="result-btn btn-next" id="resultNextBtn" style="display:none;" onclick="goNextZone()">🗺️ Prossima Zona</button>
            <button class="result-btn btn-retry" id="resultRetryBtn" style="display:none;" onclick="retryBattle()">🔄 Riprova</button>
            <button class="result-btn btn-home" onclick="window.location.href='?mode=gacha'">🏠 Gacha</button>
        </div>
    </div>
</div>

<script>
const PLAYER_STATS = <?php echo json_encode($playerStats); ?>;
const ENEMY_DATA = <?php echo json_encode(['nome' => $enemy['nome'], 'attacco' => $enemy['attacco'], 'vita' => $enemy['vita'], 'difesa' => $enemy['difesa'], 'velocita' => $enemy['velocita'], 'lvl' => $enemy['lvl'], 'xp_drop' => $enemy['xp_drop'], 'ff_drop_min' => $enemy['ff_drop_min'], 'ff_drop_max' => $enemy['ff_drop_max']]); ?>;
const CARD_NAME = <?php echo json_encode($selectedCardName); ?>;
const FF_MULT = <?php echo $ffMult; ?>;
const PLAYER_SKILLS = <?php echo $skillsJson; ?>;
const ALL_EQUIPMENT = <?php echo $equipJson; ?>;
const CURRENT_ZONE = <?php echo json_encode($selectedZone); ?>;
const ZONES_DATA = <?php echo json_encode(array_map(fn($z) => ['id' => $z['id'], 'nome' => $z['nome'], 'livello_min' => $z['livello_min']], $zones)); ?>;

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

function playSfx(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        const cfg = {attack:{type:'square',freq:[400,300,200],dur:0.15,gain:0.2},hit:{type:'sawtooth',freq:[200,100,50],dur:0.1,gain:0.3},victory:{type:'sine',freq:[523,659,784,1046],dur:0.4,gain:0.25},levelup:{type:'sine',freq:[392,523,659,784],dur:0.5,gain:0.3}}[type] || cfg.attack;
        osc.type = cfg.type;
        gain.gain.value = cfg.gain;
        let t = ctx.currentTime;
        cfg.freq.forEach((f, i) => { osc.frequency.setValueAtTime(f, t); t += cfg.dur / cfg.freq.length; });
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + cfg.dur);
    } catch(e) {}
}

function showDmgPopup(targetId, value, type) {
    const target = document.getElementById(targetId);
    if (!target) return;
    const rect = target.getBoundingClientRect();
    const popup = document.createElement('div');
    popup.className = 'dmg-popup';
    popup.textContent = (type === 'heal' ? '+' : '-') + value;
    popup.style.left = (rect.left + rect.width/2 - 20) + 'px';
    popup.style.top = (rect.top + 20) + 'px';
    popup.style.color = type === 'heal' ? '#2ecc71' : (type === 'crit' ? '#f1c40f' : '#e74c3c');
    if (type === 'crit') popup.style.fontSize = '34px';
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
    while (log.children.length > 10) log.lastChild.remove();
}

function calcDamage(atk, def) {
    const reducedDef = def * 0.6;
    let dmg = atk - reducedDef;
    const isCrit = Math.random() < 0.10;
    if (isCrit) dmg *= 1.5;
    return { dmg: Math.max(3, Math.floor(dmg)), isCrit };
}

function animateHit(spriteId) {
    const el = document.getElementById(spriteId);
    if (!el) return;
    el.style.animation = 'shake 0.35s';
    setTimeout(() => { el.style.animation = ''; }, 350);
}

function disableBattleButtons() {
    const btns = document.querySelectorAll('.battle-actions .action-btn');
    btns.forEach(btn => btn.disabled = true);
}

function getAvailableZones(playerLevel) {
    return ZONES_DATA.filter(z => z.livello_min <= playerLevel);
}

function getNextZone(playerLevel) {
    const available = getAvailableZones(playerLevel);
    const currentIdx = available.findIndex(z => z.id === CURRENT_ZONE);
    return available[currentIdx + 1] || null;
}

function playerAttack() {
    if (!playerTurn) return;
    const { dmg, isCrit } = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
    enemyHp -= dmg;
    playSfx('attack');
    animateHit('enemySprite');
    showDmgPopup('enemyEntity', isCrit ? dmg + '!' : dmg, isCrit ? 'crit' : 'normal');
    addLog('⚔️ ' + (isCrit ? '<span style="color:#f1c40f">CRITICO! </span>' : '') + 'Hai inflitto <strong>' + dmg + '</strong> danni!');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    if (enemyHp <= 0) setTimeout(() => endBattle(true), 400);
    else { playerTurn = false; setTimeout(() => enemyTurn(), 700); }
}

function playerSkill() {
    if (skillCooldown > 0 || !playerTurn) return;
    const def = getActiveSkillDef();
    if (!def) { addLog('❌ Nessuna skill disponibile!'); return; }
    playSfx('attack');
    if (def.multiplier) {
        const { dmg } = calcDamage(PLAYER_STATS.attacco, ENEMY_DATA.difesa);
        const totalDmg = Math.floor(dmg * def.multiplier);
        enemyHp -= totalDmg;
        animateHit('enemySprite');
        showDmgPopup('enemyEntity', totalDmg + '!', 'crit');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: ' + totalDmg + ' danni!');
    } else if (def.heal_pct) {
        const heal = Math.floor(playerMaxHp * def.heal_pct);
        playerHp = Math.min(playerMaxHp, playerHp + heal);
        showDmgPopup('playerEntity', heal, 'heal');
        addLog(def.icon + ' <strong>' + def.nome + '</strong>: recuperi ' + heal + ' HP!');
    } else {
        addLog(def.icon + ' <strong>' + def.nome + '</strong> usato!');
    }
    updateHpBars();
    skillCooldown = def.cd;
    activeSkillIdx = (activeSkillIdx + 1) % unlockedSkillKeys.length;
    updateSkillCd();
    if (enemyHp <= 0) setTimeout(() => endBattle(true), 400);
    else { playerTurn = false; setTimeout(() => enemyTurn(), 700); }
}

function useItem() {
    const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
    const potions = inv.potions || 0;
    if (potions <= 0) { addLog('🧪 Nessuna pozione disponibile!'); return; }
    inv.potions--;
    localStorage.setItem('equippedItems', JSON.stringify(inv));
    const heal = Math.floor(playerMaxHp * 0.3);
    playerHp = Math.min(playerMaxHp, playerHp + heal);
    showDmgPopup('playerEntity', heal, 'heal');
    addLog('🧪 Usi una pozione: +' + heal + ' HP (rimanenti: ' + inv.potions + ')');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    playerTurn = false;
    setTimeout(() => enemyTurn(), 700);
}

function attemptFlee() {
    if (Math.random() > 0.4) {
        addLog('🏃 Sei fuggito!');
        disableBattleButtons();
        setTimeout(() => { window.location.href = '?mode=gacha'; }, 800);
    } else {
        addLog('❌ Fuga fallita!');
        playerTurn = false;
        setTimeout(() => enemyTurn(), 600);
    }
}

function enemyTurn() {
    const { dmg } = calcDamage(ENEMY_DATA.attacco, PLAYER_STATS.difesa);
    playerHp -= dmg;
    playSfx('hit');
    animateHit('playerSprite');
    showDmgPopup('playerEntity', dmg, 'normal');
    addLog('👾 ' + ENEMY_DATA.nome + ' ti infligge <strong>' + dmg + '</strong> danni!');
    updateHpBars();
    skillCooldown = Math.max(0, skillCooldown - 1);
    updateSkillCd();
    if (playerHp <= 0) setTimeout(() => endBattle(false), 600);
    else { playerTurn = true; }
}

function updateSkillCd() {
    const btn = document.getElementById('skillBtn');
    const def = getActiveSkillDef();
    if (!unlockedSkillKeys.length) { btn.disabled = true; btn.textContent = '⚡ Nessuna'; return; }
    if (skillCooldown > 0) { btn.disabled = true; btn.textContent = (def?.icon || '⚡') + ' ' + (def?.nome || 'Skill') + ' (' + skillCooldown + ')'; }
    else { btn.disabled = false; btn.textContent = (def?.icon || '⚡') + ' ' + (def?.nome || 'Skill'); }
}

function endBattle(victory, flee = false) {
    if (flee) {
        addLog('🏃 Hai deciso di fuggire.');
        disableBattleButtons();
        return;
    }

    if (victory) {
        playSfx('victory');
        const xpGain = Math.floor(ENEMY_DATA.xp_drop * FF_MULT);
        const ffGain = Math.floor((Math.random() * (ENEMY_DATA.ff_drop_max - ENEMY_DATA.ff_drop_min + 1) + ENEMY_DATA.ff_drop_min) * FF_MULT);
        let dropMsg = '';

        if (Math.random() < 0.30) {
            const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
            inv.potions = (inv.potions || 0) + 1;
            localStorage.setItem('equippedItems', JSON.stringify(inv));
            dropMsg = ' 🧪 +1 Pozione';
        }
        if (Math.random() < 0.12) {
            const drop = ALL_EQUIPMENT[Math.floor(Math.random() * ALL_EQUIPMENT.length)];
            const inv = JSON.parse(localStorage.getItem('equippedItems') || '{}');
            if (!inv.inventory) inv.inventory = [];
            inv.inventory.push(drop);
            localStorage.setItem('equippedItems', JSON.stringify(inv));
            dropMsg += ' 🎁 <strong>' + drop.nome + '</strong>';
        }

        addLog('🏆 <span style="color:#2ecc71">VITTORIA!</span> +' + xpGain + ' XP, +' + ffGain + ' Fragment' + dropMsg);

        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        save.xp = (save.xp || 0) + xpGain;
        const nextXp = Math.pow((save.level || 1) + 1, 2) * 100;
        let levelUp = false;
        if (save.xp >= nextXp) {
            save.level++; save.xp -= nextXp;
            levelUp = true;
            playSfx('levelup');
            addLog('🎉 <span style="color:#f1c40f">LEVEL UP! Liv. ' + save.level + '!</span>');

            const available = getAvailableZones(save.level);
            const newZone = available.find(z => z.id !== CURRENT_ZONE);
            if (newZone) addLog('🗺️ <span style="color:#f1c40f">Zona sbloccata: ' + newZone.nome + '!</span>');
        }

        localStorage.setItem('cardGameSave', JSON.stringify(save));
        let ff = parseInt(localStorage.getItem('gachaCurrency') || '0');
        ff += ffGain;
        localStorage.setItem('gachaCurrency', ff.toString());
        const ffEl = document.getElementById('ffCount');
        if (ffEl) ffEl.textContent = ff;

        disableBattleButtons();
        showBattleResult(true, xpGain, ffGain, levelUp);

    } else {
        playSfx('hit');
        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        const lostXp = Math.floor((save.xp || 0) * 0.1);
        save.xp = Math.max(0, (save.xp || 0) - lostXp);
        localStorage.setItem('cardGameSave', JSON.stringify(save));
        addLog('<span style="color:#e74c3c">💀 SCONFITTO!</span> Perditi ' + lostXp + ' XP.');
        disableBattleButtons();
        showBattleResult(false, 0, 0, false);
    }
}

function showBattleResult(victory, xpGain, ffGain, levelUp) {
    const overlay = document.getElementById('battleResultOverlay');
    const title = document.getElementById('resultTitle');
    const msg = document.getElementById('resultMsg');
    const nextBtn = document.getElementById('resultNextBtn');
    const retryBtn = document.getElementById('resultRetryBtn');

    if (!overlay) return;

    if (victory) {
        title.textContent = '🏆 VITTORIA!';
        title.style.color = '#2ecc71';
        let stats = '+' + xpGain + ' XP | +' + ffGain + ' Fragment';
        if (levelUp) stats += '<br><span style="color:#f1c40f;font-weight:bold;">★ LEVEL UP! ★</span>';
        msg.innerHTML = stats;

        const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
        const next = getNextZone(save.level || 1);
        if (next) {
            nextBtn.style.display = 'inline-block';
            nextBtn.textContent = '🗺️ Vai a: ' + next.nome;
        } else {
            nextBtn.style.display = 'none';
        }
    } else {
        title.textContent = '💀 SCONFITTO!';
        title.style.color = '#e74c3c';
        msg.innerHTML = 'Il nemico è stato più forte...<br>Riprova o cambia strategia!';
        nextBtn.style.display = 'none';
    }

    retryBtn.style.display = 'inline-block';
    overlay.style.display = 'flex';
}

function goNextZone() {
    const save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
    const next = getNextZone(save.level || 1);
    if (next) {
        window.location.href = '?mode=game&card=' + encodeURIComponent(CARD_NAME) + '&zone=' + next.id;
    }
}

function retryBattle() {
    window.location.reload();
}

function initTimeline() {
    const t1 = document.getElementById('tl1');
    const t2 = document.getElementById('tl2');
    if (!t1 || !t2) return;
    if (PLAYER_STATS.velocita >= ENEMY_DATA.velocita) {
        t1.textContent = '👤'; t2.textContent = '👾';
    } else {
        t1.textContent = '👾'; t2.textContent = '👤';
    }
}

window.addEventListener('DOMContentLoaded', () => {
    updateHpBars();
    initTimeline();
    updateSkillCd();
    addLog('⚔️ <strong>' + ENEMY_DATA.nome + '</strong> (Lv.' + ENEMY_DATA.lvl + ') appare!');
    setTimeout(() => {
        if (PLAYER_STATS.velocita >= ENEMY_DATA.velocita) {
            playerTurn = true;
            addLog('Tu attacchi per primo!');
        } else {
            addLog('👾 Il nemico è più veloce!');
            setTimeout(() => enemyTurn(), 800);
            playerTurn = false;
        }
    }, 800);
});
</script>
</body>
</html>
