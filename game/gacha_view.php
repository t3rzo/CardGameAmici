<?php
// gacha_view.php — Schermata gacha con animazione pull
// Mostra pulsante pull, animazione flash + card reveal
?>

<div class="gacha-container" id="gachaContainer">
    <!-- Schermata inizio gacha -->
    <div class="gacha-intro" id="gachaIntro">
        <h2 class="gacha-title">✨ GACHAPON MAGICO ✨</h2>
        <p style="color:#aaa; font-family:'Share Tech Mono'; max-width:500px; text-align:center; margin-bottom:30px;">
            Tira per ottenere carte guerriere di rarità variabile!<br>
            Ogni pull costa <strong style="color:var(--cyber-cyan);">50 Fragment</strong>.<br>
            Pull da 10 garantisce almeno una carta <strong style="color:var(--raro);">Rara</strong> o superiore!
        </p>
        <div class="gacha-currency-display">
            <span style="font-size:24px;">💎</span>
            <span id="gachaFfCount">0</span> Fragment
        </div>
        <div class="gacha-btns">
            <button class="gacha-btn single" id="pullSingle" onclick="startPullSingle()">PULL x1 (50 💎)</button>
            <button class="gacha-btn multi" id="pullTen" onclick="startPullTen()">PULL x10 (500 💎)</button>
        </div>
        <div class="gacha-hint" id="gachaHint"></div>
    </div>

    <!-- Schermata pull (nascosta fino a quando non si tira) -->
    <div class="gacha-pull-screen" id="gachaPullScreen" style="display:none;">
        <div class="gacha-loading" id="gachaLoading">
            <div class="spinner"></div>
            <div class="loading-text">Sto tirando la carta...</div>
        </div>
        <div class="pull-flash" id="pullFlash"></div>
    </div>

    <!-- Risultato del pull -->
    <div class="gacha-result" id="gachaResult" style="display:none;">
        <div class="result-card" id="resultCard">
            <div class="result-rarity-bg" id="resultRarityBg"></div>
            <img src="" class="result-card-img" id="resultCardImg">
            <div class="result-info">
                <div class="result-rarity-badge" id="resultRarityBadge"></div>
                <h3 class="result-card-name" id="resultCardName"></h3>
                <p class="result-desc" id="resultDesc"></p>
                <div class="result-stats" id="resultStats"></div>
                <button class="result-collect-btn" id="resultCollectBtn" onclick="collectFromGacha()">Aggiungi alla Collezione</button>
            </div>
        </div>
        <div class="result-particles" id="resultParticles"></div>
    </div>
</div>

<script>
// collection definita globalmente in index.php head
let save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
let fF = parseInt(localStorage.getItem('gachaCurrency') || '0');

// Aggiorna display valuta
function updateFFDisplay() {
    document.getElementById('gachaFfCount').textContent = fF;
    document.getElementById('ffCount').textContent = fF; // HUD
}

// Weighted random: sceglie una carta pesata per rarità
function weightedRandom(rarities = RARITY_WEIGHTS) {
    // Sistema garantito: se pull x10, garantisce raro+
    // Per semplicità, implementiamo il guarantee in pullTen
    const entries = Object.entries(rarities);
    const total = entries.reduce((s, [,w]) => s + w, 0);
    let roll = Math.random() * total;
    let accum = 0;
    for (const [rarity, weight] of entries) {
        accum += weight;
        if (roll <= accum) return rarity;
    }
    return entries[entries.length - 1][0];
}

// Sceglie una carta casuale di una certa rarità
function randomCardOfRarity(rarity) {
    const cardsOfRarity = Object.entries(ALL_CARDS).filter(([n,d]) => d.rarity === rarity);
    if (cardsOfRarity.length === 0) {
        // Fallback al rarity più vicino disponibile
        const allRarities = ['comune','non-comune','raro','epico','leggendario','esotico','mitico','segreto'];
        const idx = allRarities.indexOf(rarity);
        for (let i = idx - 1; i >= 0; i--) {
            const fallback = Object.entries(ALL_CARDS).filter(([n,d]) => d.rarity === allRarities[i]);
            if (fallback.length > 0) return fallback[Math.floor(Math.random() * fallback.length)];
        }
    }
    return cardsOfRarity[Math.floor(Math.random() * cardsOfRarity.length)];
}

// Avvia pull singolo
function startPullSingle() {
    const needed = 50;
    if (fF < needed) {
        showHint('❌ Non hai abbastanza Fragment! Completa livelli o raccogli carte duplicate.');
        return;
    }
    fF -= needed;
    saveCurrency(fF);
    updateFFDisplay();
    startPullAnimation();
}

// Avvia pull da 10 (con garanzia raro+)
function startPullTen() {
    const needed = 500;
    if (fF < needed) {
        showHint('❌ Non hai abbastanza Fragment! Salta qualche avventura per guadagnarli.');
        return;
    }
    fF -= needed;
    saveCurrency(fF);
    updateFFDisplay();
    startPullTenAnimation();
}

function showHint(msg) {
    const h = document.getElementById('gachaHint');
    h.innerHTML = msg;
    h.style.opacity = '1';
    setTimeout(() => { h.style.opacity = '0'; }, 3000);
}

function saveCurrency(val) {
    localStorage.setItem('gachaCurrency', val.toString());
}

// Animazione pull singolo
async function startPullAnimation() {
    const intro = document.getElementById('gachaIntro');
    const pullScreen = document.getElementById('gachaPullScreen');
    const flash = document.getElementById('pullFlash');

    intro.style.opacity = '0';
    intro.style.pointerEvents = 'none';

    // Fase 1: caricamento (2s)
    pullScreen.style.display = 'block';

    // Scegli la carta e la rarità
    const rarity = weightedRandom();
    const [cardName, cardData] = randomCardOfRarity(rarity);

    // Fase 2: flash bianco improvviso
    setTimeout(() => {
        flash.style.opacity = '1';
        flash.style.animation = 'none';
        void flash.offsetWidth;
        flash.style.animation = 'flashGlow 0.7s ease-out';
        new Audio('./sounds/' + encodeURIComponent(cardName) + '.mp3').play().catch(e => {});
    }, 1000);

    // Fase 3: card reveal
    setTimeout(() => {
        flash.style.opacity = '0';
        pullScreen.style.display = 'none';
        showResultCard(cardName, cardData, rarity);
    }, 1800);
}

// Animazione pull x10 (sequenza)
async function startPullTenAnimation() {
    const intro = document.getElementById('gachaIntro');
    const pullScreen = document.getElementById('gachaPullScreen');
    const flash = document.getElementById('pullFlash');

    intro.style.opacity = '0';
    intro.style.pointerEvents = 'none';
    pullScreen.style.display = 'block';

    // Genera 10 carte con garantita raro+
    let results = [];
    for (let i = 0; i < 10; i++) {
        let rarity;
        if (i === 9) {
            // Ultimo è garantito raro+
            const raroUp = ['raro','epico','leggendario','esotico','mitico','segreto'];
            rarity = raroUp[Math.floor(Math.random() * raroUp.length)];
        } else {
            rarity = weightedRandom();
        }
        const [cardName, cardData] = randomCardOfRarity(rarity);
        results.push({ cardName, cardData, rarity });
    }

    // Mostra flash per ogni carta in sequenza
    let idx = 0;
    const reveal = () => {
        if (idx >= results.length) {
            pullScreen.style.display = 'none';
            showResultsMulti(results);
            return;
        }
        flash.style.opacity = '1';
        flash.style.animation = 'none';
        void flash.offsetWidth;
        flash.style.animation = 'flashGlow 0.5s ease-out';
        setTimeout(() => {
            flash.style.opacity = '0';
            idx++;
            setTimeout(reveal, 100);
        }, 500);
    };
    setTimeout(reveal, 500);
}

// Mostra card rivelata (singola)
function showResultCard(cardName, cardData, rarity) {
    const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
    const badges = {'comune':'bg-comune','non-comune':'bg-non-comune','raro':'bg-raro','epico':'bg-epico','leggendario':'bg-leggendario','esotico':'bg-esotico','mitico':'bg-mitico','segreto':'bg-segreto'};

    const intro = document.getElementById('gachaIntro');
    intro.innerHTML = ''; // Pulisce

    const resultDiv = document.getElementById('gachaResult');
    resultDiv.style.display = 'flex';

    document.getElementById('resultCardImg').src = './images/' + encodeURIComponent(cardName) + '.png';
    document.getElementById('resultCardImg').onerror = (e) => { e.target.src = 'https://via.placeholder.com/300?text=CARD+ERROR'; };
    document.getElementById('resultRarityBg').style.background = colors[rarity] || '#7f8c8d';
    document.getElementById('resultRarityBadge').className = 'result-rarity-badge ' + badges[rarity];
    document.getElementById('resultRarityBadge').textContent = rarity.toUpperCase();
    document.getElementById('resultCardName').textContent = cardName;
    document.getElementById('resultDesc').textContent = cardData.desc;

    // Stats
    const s = cardData.stats || {};
    document.getElementById('resultStats').innerHTML =
        '⚔️ Attacco: ' + (s.forza || 50) + ' | ❤️ Vita: ' + (s.mentalità || 50) +
        ' | 🛡️ Difesa: ' + (s.tecnica || 50) + ' | ⚡ Vel: ' + (s.velocità || 50);

    // Memorizza la carta trovata
    window._pendingCard = { name: cardName, rarity: rarity };

    // Particelle rarità
    spawnParticles(rarity, 30);

    // Mostra pulsante back
    const backBtn = document.createElement('button');
    backBtn.className = 'gacha-btn';
    backBtn.textContent = '← Torna al Gacha';
    backBtn.onclick = () => {
        resultDiv.style.display = 'none';
        intro.parentNode.innerHTML = ''; // Pulisce intro
        // Ricrea intro vuoto (lo farà il reload o riporta al gacha)
        window.location.href = '?mode=gacha';
    };
    document.getElementById('resultInfo').appendChild(backBtn);
}

// Raccogli carta dal gacha
function collectFromGacha() {
    if (!window._pendingCard) return;
    const { name, rarity } = window._pendingCard;
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
    localStorage.setItem('cardGameCollection', JSON.stringify(collection));
    updateRaritiesCounter();
    renderCollection();
    showHint('✓ Carta aggiunta alla collezione!');
}

// Mostra risultati multipli
function showResultsMulti(results) {
    const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};
    const badges = {'comune':'bg-comune','non-comune':'bg-non-comune','raro':'bg-raro','epico':'bg-epico','leggendario':'bg-leggendario','esotico':'bg-esotico','mitico':'bg-mitico','segreto':'bg-segreto'};

    const intro = document.getElementById('gachaIntro');
    intro.innerHTML = `<h3 style="color:var(--rpg-gold);font-family:'Orbitron';margin-bottom:20px;">RISULTATI PULL 10</h3>`;
    intro.style.opacity = '1';
    intro.style.pointerEvents = 'auto';

    results.forEach((r, i) => {
        setTimeout(() => {
            const cardEl = document.createElement('div');
            cardEl.className = 'pull-result-card';
            cardEl.style.borderColor = colors[r.rarity];
            cardEl.innerHTML = `
                <img src="./images/${encodeURIComponent(r.cardName)}.png"
                     onerror="this.src='https://via.placeholder.com/80?text=?'">
                <div class="prc-name">${r.cardName}</div>
                <div class="prc-rarity ${badges[r.rarity]}">${r.rarity}</div>
            `;
            intro.appendChild(cardEl);
            spawnParticles(r.rarity, 10);

            // Aggiungi alla collezione automaticamente
            setTimeout(() => {
                if (!collection[r.cardName]) {
                    collection[r.cardName] = {
                        rarity: r.rarity, collectedAt: Date.now(),
                        stats: r.cardData.stats || {},
                        stats_rpg: {
                            attacco: r.cardData.stats?.forza || 50,
                            vita: r.cardData.stats?.mentalità || 50,
                            difesa: r.cardData.stats?.tecnica || 50,
                            velocità: r.cardData.stats?.velocità || 50,
                        },
                        level: 1, xp: 0, equipped: {}
                    };
                    localStorage.setItem('cardGameCollection', JSON.stringify(collection));
                }
            }, 500);
        }, i * 400);
    });

    setTimeout(() => {
        updateFFDisplay();
        renderCollection();
        const back = document.createElement('button');
        back.className = 'gacha-btn';
        back.textContent = '← Torna al Gacha';
        back.onclick = () => window.location.href = '?mode=gacha';
        intro.appendChild(back);
    }, results.length * 400 + 500);
}

window.addEventListener('DOMContentLoaded', () => {
    updateFFDisplay();
});
</script>

<style>
/* ===== GACHA STYLES ===== */
.gacha-container {
    width: 100%; max-width: 700px; margin: 20px auto;
    padding: 30px; background: rgba(0,0,0,0.5);
    border-radius: 20px; border: 2px solid var(--rpg-gold);
    min-height: 500px;
}
.gacha-title {
    font-family: 'Orbitron'; font-size: 28px; color: var(--rpg-gold);
    text-align: center; margin-bottom: 10px;
    text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
}
.gacha-currency-display {
    font-family: 'Orbitron'; font-size: 18px; color: var(--cyber-cyan);
    text-align: center; margin: 20px 0;
    text-shadow: 0 0 10px rgba(0,242,255,0.5);
}
.gacha-btns { display: flex; gap: 15px; justify-content: center; margin: 30px 0; }
.gacha-btn {
    padding: 12px 24px; border: 2px solid var(--rpg-gold);
    background: rgba(0,0,0,0.5); color: var(--rpg-gold);
    font-family: 'Orbitron'; font-size: 14px; cursor: pointer;
    border-radius: 8px; transition: all 0.3s ease;
    box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
}
.gacha-btn:hover {
    background: var(--rpg-gold); color: #000;
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.6);
    transform: translateY(-2px);
}
.gacha-btn.single { border-color: var(--cyber-cyan); color: var(--cyber-cyan); }
.gacha-btn.single:hover { border-color: var(--rpg-gold); }
.gacha-btn.multi { border-color: var(--epico); color: var(--epico); }
.gacha-btn.multi:hover { border-color: var(--rpg-gold); }

.gacha-hint {
    text-align: center; font-family: 'Share Tech Mono'; font-size: 12px;
    color: var(--cyber-cyan); min-height: 20px;
    opacity: 0; transition: opacity 0.3s ease;
}

/* Pull screen */
.gacha-pull-screen {
    position: relative; width: 100%; height: 400px;
    background: rgba(0,0,0,0.9); border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
}
.gacha-loading { text-align: center; color: var(--cyber-cyan); font-family: 'Orbitron'; }
.spinner {
    width: 60px; height: 60px; margin: 0 auto 20px;
    border: 4px solid rgba(255,255,255,0.1);
    border-top-color: var(--cyber-cyan);
    border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.loading-text { font-size: 16px; opacity: 0.8; }

/* Flash animation */
.pull-flash {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: white; opacity: 0; z-index: 10;
    animation: none;
}
@keyframes flashGlow {
    0% { opacity: 0; }
    20% { opacity: 1; }
    100% { opacity: 0; }
}

/* Result card */
.gacha-result { display: flex; justify-content: center; align-items: center; margin-top: 20px; }
.result-card {
    position: relative; width: 90%; max-width: 400px;
    background: white; border-radius: 20px; padding: 25px;
    text-align: center; min-height: 500px;
}
.result-rarity-bg {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    border-radius: 20px; opacity: 0.1; z-index: 0;
}
.result-card-img {
    max-width: 90%; max-height: 300px;
    object-fit: contain; filter: drop-shadow(0 8px 12px rgba(0,0,0,0.3));
    z-index: 1; margin-bottom: 15px;
}
.result-info { position: relative; z-index: 2; }
.result-rarity-badge {
    display: inline-block; padding: 8px 20px; border-radius: 30px;
    font-weight: 900; text-transform: uppercase; margin-bottom: 10px;
    color: white; letter-spacing: 2px;
}
.result-card-name { font-size: 24px; font-weight: 900; margin: 10px 0; font-family: 'Orbitron'; }
.result-desc { font-size: 14px; line-height: 1.4; margin: 10px 0; color: #555; }
.result-stats { font-size: 13px; margin: 10px 0; font-family: 'Share Tech Mono'; }
.result-collect-btn {
    margin-top: 15px; padding: 10px 25px;
    background: var(--cyber-cyan); color: #000;
    font-family: 'Orbitron'; font-weight: bold; cursor: pointer;
    border-radius: 8px; transition: all 0.3s ease;
}
.result-collect-btn:hover {
    background: var(--rpg-gold); color: #000;
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
}

/* Pull results grid */
.pull-result-card {
    display: inline-block; width: 100px; height: 120px;
    border-radius: 8px; padding: 5px; margin: 5px;
    background: rgba(255,255,255,0.1); border: 2px solid;
    text-align: center; vertical-align: top;
}
.pull-result-card img { width: 60px; height: 60px; object-fit: contain; }
.prc-name { font-size: 9px; color: #fff; margin-top: 3px; }
.prc-rarity { font-size: 8px; padding: 2px 5px; border-radius: 4px; color: white; }
</style>
