<?php
// gacha_view.php — Schermata gacha con animazione pull
// Mostra pulsante pull, animazione flash + card reveal
?>
<link rel="stylesheet" href="./assets/css/gacha.css">

<div class="gacha-container" id="gachaContainer">
    <div class="gacha-intro" id="gachaIntro">
        <span class="gacha-kicker">Camera di evocazione</span>
        <h2 class="gacha-title">GACHAPON MAGICO</h2>
        <p class="gacha-lead">
            Evoca carte guerriere di rarità variabile.
            Una evocazione costa <strong>50 Fragment</strong>.
            x10 garantisce almeno una carta <strong>Rara</strong> o superiore.
        </p>

        <div class="gacha-wallet" aria-live="polite">
            <div>
                <div class="gacha-wallet-label">Saldo attuale</div>
                <div class="gacha-currency-display">
                    <span aria-hidden="true">💎</span>
                    <span id="gachaFfCount">0</span>
                    <span class="gacha-ff-unit">Fragment</span>
                </div>
            </div>
            <div class="gacha-cost-stack">
                <div>x1 · <strong>50</strong> 💎</div>
                <div>x10 · <strong>500</strong> 💎</div>
            </div>
            <div class="gacha-afford" id="gachaAfford"></div>
        </div>

        <div class="gacha-rarity-strip" aria-label="Rarità ottenibili">
            <span class="gacha-rarity-chip comune">Comune</span>
            <span class="gacha-rarity-chip non-comune">Non-comune</span>
            <span class="gacha-rarity-chip raro">Raro</span>
            <span class="gacha-rarity-chip epico">Epico</span>
            <span class="gacha-rarity-chip leggendario">Leggendario</span>
            <span class="gacha-rarity-chip esotico">Esotico</span>
            <span class="gacha-rarity-chip mitico">Mitico</span>
            <span class="gacha-rarity-chip segreto">Segreto</span>
        </div>

        <div class="gacha-btns">
            <button type="button" class="gacha-btn single" id="pullSingle">
                <span class="gacha-btn-label">EVOCA x1</span>
                <span class="gacha-btn-meta">50 Fragment</span>
            </button>
            <button type="button" class="gacha-btn multi" id="pullTen">
                <span class="gacha-btn-label">EVOCA x10</span>
                <span class="gacha-btn-meta">500 Fragment · 1 Raro+ garantito</span>
            </button>
        </div>
        <p class="gacha-guarantee">x10: l'ultima carta è <strong>Rara o superiore</strong>.</p>
        <div class="gacha-hint" id="gachaHint" role="status"></div>
    </div>

    <div class="gacha-pull-screen" id="gachaPullScreen" hidden>
        <div class="gacha-loading" id="gachaLoading">
            <div class="spinner" aria-hidden="true"></div>
            <div class="loading-text" id="gachaLoadingText">Concentrazione dell'energia...</div>
        </div>
        <div class="gacha-silhouette" id="gachaSilhouette" aria-hidden="true">
            <img src="" alt="" id="gachaSilhouetteImg">
        </div>
        <div class="pull-flash" id="pullFlash"></div>
    </div>

    <div class="gacha-result" id="gachaResult" hidden>
        <div class="result-stage">
            <div class="result-card" id="resultCard">
                <div class="result-rarity-bg" id="resultRarityBg"></div>
                <div class="result-info" id="resultInfo">
                    <div class="result-status" id="resultStatus"></div>
                    <div class="result-rarity-badge" id="resultRarityBadge"></div>
                    <h3 class="result-card-name" id="resultCardName"></h3>
                    <img src="" alt="" class="result-card-img" id="resultCardImg">
                    <p class="result-desc" id="resultDesc"></p>
                    <div class="result-stats" id="resultStats"></div>
                    <div class="result-actions">
                        <button type="button" class="result-collect-btn" id="resultCollectBtn">Aggiungi alla Collezione</button>
                        <button type="button" class="gacha-secondary-btn" id="resultAgainBtn">Evoca di nuovo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="gacha-multi" id="gachaMulti" hidden>
        <h3 class="gacha-multi-title">Risultati evocazione x10</h3>
        <p class="gacha-multi-summary" id="gachaMultiSummary"></p>
        <div class="gacha-multi-grid" id="gachaMultiGrid"></div>
        <div class="result-actions" style="max-width:380px;margin:0 auto;">
            <button type="button" class="result-collect-btn" id="multiCollectBtn">Aggiungi le nuove carte</button>
            <button type="button" class="gacha-secondary-btn" id="multiAgainBtn">Torna al Gacha</button>
        </div>
    </div>
    <div class="gacha-fx-layer" id="gachaFxLayer" aria-hidden="true"></div>
</div>

<script>
const GACHA_COST_SINGLE = 50;
const GACHA_COST_TEN = 500;
const RARITY_COLORS = {
    'comune': '#7f8c8d',
    'non-comune': '#27ae60',
    'raro': '#2980b9',
    'epico': '#8e44ad',
    'leggendario': '#f1c40f',
    'esotico': '#ff512f',
    'mitico': '#e74c3c',
    'segreto': '#00f2ff'
};
const RARITY_BADGES = {
    'comune': 'bg-comune',
    'non-comune': 'bg-non-comune',
    'raro': 'bg-raro',
    'epico': 'bg-epico',
    'leggendario': 'bg-leggendario',
    'esotico': 'bg-esotico',
    'mitico': 'bg-mitico',
    'segreto': 'bg-segreto'
};
const RARITY_WAIT = {
    'comune': 380,
    'non-comune': 520,
    'raro': 720,
    'epico': 980,
    'leggendario': 1180,
    'esotico': 1280,
    'mitico': 1480,
    'segreto': 1680
};
const RARITY_PARTICLES = {
    'comune': 4,
    'non-comune': 8,
    'raro': 14,
    'epico': 22,
    'leggendario': 28,
    'esotico': 32,
    'mitico': 36,
    'segreto': 40
};
const RARITY_RANK = ['comune','non-comune','raro','epico','leggendario','esotico','mitico','segreto'];

let save = JSON.parse(localStorage.getItem('cardGameSave') || '{}');
let fF = parseInt(localStorage.getItem('gachaCurrency') || '0', 10);
let gachaBusy = false;
const gachaTimers = [];
window._pendingCard = null;
window._pendingMulti = null;

function gachaLater(fn, ms) {
    const id = setTimeout(fn, ms);
    gachaTimers.push(id);
    return id;
}

function clearGachaTimers() {
    while (gachaTimers.length) clearTimeout(gachaTimers.pop());
}

function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function updateFFDisplay() {
    const gachaEl = document.getElementById('gachaFfCount');
    if (gachaEl) gachaEl.textContent = String(fF);
    const hud = document.getElementById('fFCount');
    if (hud) hud.textContent = String(fF);
    updateSummonButtons();
}

function updateSummonButtons() {
    const single = document.getElementById('pullSingle');
    const ten = document.getElementById('pullTen');
    const afford = document.getElementById('gachaAfford');
    if (!single || !ten) return;

    const can1 = fF >= GACHA_COST_SINGLE;
    const can10 = fF >= GACHA_COST_TEN;
    single.disabled = gachaBusy || !can1;
    ten.disabled = gachaBusy || !can10;
    single.classList.toggle('is-disabled', single.disabled);
    ten.classList.toggle('is-disabled', ten.disabled);
    single.setAttribute('aria-disabled', String(single.disabled));
    ten.setAttribute('aria-disabled', String(ten.disabled));

    if (afford) {
        afford.classList.toggle('is-short', !can1);
        if (!can1) {
            afford.textContent = 'Non puoi evocare: servono ' + GACHA_COST_SINGLE + ' Fragment (ti mancano ' + (GACHA_COST_SINGLE - fF) + ').';
        } else if (!can10) {
            afford.textContent = 'Puoi evocare x1. Per x10 servono ancora ' + (GACHA_COST_TEN - fF) + ' Fragment.';
        } else {
            afford.textContent = 'Puoi evocare x1 e x10.';
        }
    }
}

function weightedRandom(rarities = RARITY_WEIGHTS) {
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

function randomCardOfRarity(rarity) {
    const cardsOfRarity = Object.entries(ALL_CARDS).filter(([,d]) => d.rarity === rarity);
    if (cardsOfRarity.length === 0) {
        const allRarities = ['comune','non-comune','raro','epico','leggendario','esotico','mitico','segreto'];
        const idx = allRarities.indexOf(rarity);
        for (let i = idx - 1; i >= 0; i--) {
            const fallback = Object.entries(ALL_CARDS).filter(([,d]) => d.rarity === allRarities[i]);
            if (fallback.length > 0) return fallback[Math.floor(Math.random() * fallback.length)];
        }
    }
    return cardsOfRarity[Math.floor(Math.random() * cardsOfRarity.length)];
}

function showHint(msg) {
    const h = document.getElementById('gachaHint');
    if (!h) return;
    h.textContent = msg;
    h.classList.add('is-visible');
    gachaLater(() => { h.classList.remove('is-visible'); }, 3200);
}

function saveCurrency(val) {
    localStorage.setItem('gachaCurrency', val.toString());
}

function cardImageUrl(name) {
    return './images/' + encodeURIComponent(name) + '.png';
}

function isOwned(name) {
    return !!(collection && collection[name]);
}

function buildCollectionEntry(name, rarity, cardData) {
    return {
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
}

function addNewCards(entries) {
    let added = 0;
    entries.forEach(({ name, rarity, cardData }) => {
        if (!collection[name]) {
            collection[name] = buildCollectionEntry(name, rarity, cardData);
            added++;
        }
    });
    if (added > 0) {
        localStorage.setItem('cardGameCollection', JSON.stringify(collection));
        if (typeof updateRaritiesCounter === 'function') updateRaritiesCounter();
        if (typeof renderCollection === 'function') renderCollection();
    }
    return added;
}

function setScreen(name) {
    const intro = document.getElementById('gachaIntro');
    const pull = document.getElementById('gachaPullScreen');
    const result = document.getElementById('gachaResult');
    const multi = document.getElementById('gachaMulti');

    intro.hidden = name !== 'intro';
    intro.style.opacity = name === 'intro' ? '1' : '0';
    intro.style.pointerEvents = name === 'intro' ? 'auto' : 'none';
    if (name !== 'intro') intro.style.display = 'none';
    else intro.style.display = '';

    pull.hidden = name !== 'pull';
    pull.classList.toggle('is-active', name === 'pull');
    pull.style.display = name === 'pull' ? 'flex' : 'none';

    result.hidden = name !== 'result';
    result.classList.toggle('is-active', name === 'result');
    result.style.display = name === 'result' ? 'flex' : 'none';

    multi.hidden = name !== 'multi';
    multi.classList.toggle('is-active', name === 'multi');
    multi.style.display = name === 'multi' ? 'block' : 'none';
}

function resetGachaHome() {
    clearGachaTimers();
    gachaBusy = false;
    window._pendingCard = null;
    window._pendingMulti = null;
    document.getElementById('gachaContainer').classList.remove('is-shaking');
    document.getElementById('gachaSilhouette').className = 'gacha-silhouette';
    document.getElementById('gachaLoading').style.display = '';
    const fx = document.getElementById('gachaFxLayer');
    if (fx) fx.innerHTML = '';
    document.getElementById('gachaMultiGrid').innerHTML = '';
    const collectBtn = document.getElementById('resultCollectBtn');
    collectBtn.disabled = false;
    collectBtn.textContent = 'Aggiungi alla Collezione';
    const multiBtn = document.getElementById('multiCollectBtn');
    multiBtn.disabled = false;
    multiBtn.textContent = 'Aggiungi le nuove carte';
    setScreen('intro');
    updateFFDisplay();
}

function startPullSingle() {
    if (gachaBusy) return;
    if (fF < GACHA_COST_SINGLE) {
        showHint('Non hai abbastanza Fragment. Completa avventure per guadagnarli.');
        return;
    }
    gachaBusy = true;
    fF -= GACHA_COST_SINGLE;
    saveCurrency(fF);
    updateFFDisplay();
    startPullAnimation();
}

function startPullTen() {
    if (gachaBusy) return;
    if (fF < GACHA_COST_TEN) {
        showHint('Non hai abbastanza Fragment per un\'evocazione x10.');
        return;
    }
    gachaBusy = true;
    fF -= GACHA_COST_TEN;
    saveCurrency(fF);
    updateFFDisplay();
    startPullTenAnimation();
}

function playCardSound(cardName) {
    try {
        const audio = new Audio('./sounds/' + encodeURIComponent(cardName) + '.mp3');
        audio.volume = 0.7;
        audio.play().catch(() => {});
    } catch (e) {}
}

function fireFlash() {
    const flash = document.getElementById('pullFlash');
    flash.classList.remove('is-firing');
    void flash.offsetWidth;
    flash.classList.add('is-firing');
}

function startPullAnimation() {
    const rarity = weightedRandom();
    const [cardName, cardData] = randomCardOfRarity(rarity);
    const wait = prefersReducedMotion() ? 200 : (RARITY_WAIT[rarity] || 500);

    setScreen('pull');
    document.getElementById('gachaLoadingText').textContent = 'Sto tirando la carta...';
    document.getElementById('gachaLoading').style.display = '';
    const sil = document.getElementById('gachaSilhouette');
    sil.className = 'gacha-silhouette rarity-' + rarity;
    const silImg = document.getElementById('gachaSilhouetteImg');
    silImg.src = cardImageUrl(cardName);
    silImg.alt = '';

    gachaLater(() => {
        document.getElementById('gachaLoading').style.display = 'none';
        sil.classList.add('is-visible');
        document.getElementById('gachaLoadingText').textContent = 'Qualcosa sta per apparire...';
    }, 700);

    gachaLater(() => {
        fireFlash();
        playCardSound(cardName);
        if (RARITY_RANK.indexOf(rarity) >= RARITY_RANK.indexOf('epico') && !prefersReducedMotion()) {
            const box = document.getElementById('gachaContainer');
            box.classList.remove('is-shaking');
            void box.offsetWidth;
            box.classList.add('is-shaking');
        }
    }, 700 + wait);

    gachaLater(() => {
        setScreen('result');
        showResultCard(cardName, cardData, rarity);
    }, 700 + wait + 420);
}

function startPullTenAnimation() {
    let results = [];
    for (let i = 0; i < 10; i++) {
        let rarity;
        if (i === 9) {
            const raroUp = ['raro','epico','leggendario','esotico','mitico','segreto'];
            rarity = raroUp[Math.floor(Math.random() * raroUp.length)];
        } else {
            rarity = weightedRandom();
        }
        const [cardName, cardData] = randomCardOfRarity(rarity);
        results.push({ cardName, cardData, rarity });
    }

    setScreen('pull');
    document.getElementById('gachaLoading').style.display = '';
    document.getElementById('gachaSilhouette').classList.remove('is-visible');
    document.getElementById('gachaLoadingText').textContent = 'Dieci evocazioni in corso...';

    const top = results.reduce((best, r) => {
        return RARITY_RANK.indexOf(r.rarity) > RARITY_RANK.indexOf(best.rarity) ? r : best;
    }, results[0]);

    gachaLater(() => {
        fireFlash();
        playCardSound(top.cardName);
        if (RARITY_RANK.indexOf(top.rarity) >= RARITY_RANK.indexOf('epico') && !prefersReducedMotion()) {
            const box = document.getElementById('gachaContainer');
            box.classList.remove('is-shaking');
            void box.offsetWidth;
            box.classList.add('is-shaking');
        }
    }, prefersReducedMotion() ? 300 : 1100);

    gachaLater(() => {
        showResultsMulti(results);
    }, prefersReducedMotion() ? 450 : 1600);
}

function showResultCard(cardName, cardData, rarity) {
    const colors = RARITY_COLORS;
    const badges = RARITY_BADGES;
    const owned = isOwned(cardName);

    const cardEl = document.getElementById('resultCard');
    cardEl.className = 'result-card rarity-' + rarity + ' is-flipping';
    document.getElementById('resultCardImg').src = cardImageUrl(cardName);
    document.getElementById('resultCardImg').alt = cardName;
    document.getElementById('resultCardImg').onerror = (e) => { e.target.src = 'https://via.placeholder.com/300?text=CARD+ERROR'; };
    document.getElementById('resultRarityBg').style.background = colors[rarity] || '#7f8c8d';
    document.getElementById('resultRarityBadge').className = 'result-rarity-badge ' + badges[rarity];
    document.getElementById('resultRarityBadge').textContent = rarity.toUpperCase();
    document.getElementById('resultCardName').textContent = cardName;
    document.getElementById('resultDesc').textContent = cardData.desc || '';

    const status = document.getElementById('resultStatus');
    status.className = 'result-status ' + (owned ? 'is-dupe' : 'is-new');
    status.textContent = owned ? 'DUPLICATO · già in collezione' : 'NUOVA CARTA';

    const s = cardData.stats || {};
    document.getElementById('resultStats').innerHTML =
        '<span>Attacco ' + (s.forza || 50) + '</span>' +
        '<span>Vita ' + (s.mentalità || 50) + '</span>' +
        '<span>Difesa ' + (s.tecnica || 50) + '</span>' +
        '<span>Velocità ' + (s.velocità || 50) + '</span>';

    const collectBtn = document.getElementById('resultCollectBtn');
    if (owned) {
        collectBtn.textContent = 'Già in collezione';
        collectBtn.disabled = true;
        window._pendingCard = null;
    } else {
        collectBtn.textContent = 'Aggiungi alla Collezione';
        collectBtn.disabled = false;
        window._pendingCard = { name: cardName, rarity: rarity, cardData: cardData };
    }

    spawnGachaParticles(rarity);
}

function collectFromGacha() {
    if (!window._pendingCard) return;
    const { name, rarity, cardData } = window._pendingCard;
    const added = addNewCards([{ name, rarity, cardData }]);
    window._pendingCard = null;
    const collectBtn = document.getElementById('resultCollectBtn');
    collectBtn.disabled = true;
    collectBtn.textContent = added ? 'Aggiunta alla collezione' : 'Già in collezione';
    showHint(added ? 'Carta aggiunta alla collezione.' : 'Duplicato: la carta era già tua.');
}

function showResultsMulti(results) {
    setScreen('multi');
    const grid = document.getElementById('gachaMultiGrid');
    grid.innerHTML = '';
    const pending = [];
    let newCount = 0;
    results.forEach((r) => {
        const owned = isOwned(r.cardName);
        if (!owned) {
            newCount++;
            pending.push({ name: r.cardName, rarity: r.rarity, cardData: r.cardData });
        }
    });
    window._pendingMulti = pending;

    document.getElementById('gachaMultiSummary').textContent =
        newCount + ' nuov' + (newCount === 1 ? 'a' : 'e') + ' · ' + (results.length - newCount) + ' duplicat' + ((results.length - newCount) === 1 ? 'o' : 'i') +
        ' · ultima carta: garanzia Raro+';

    const multiBtn = document.getElementById('multiCollectBtn');
    if (newCount === 0) {
        multiBtn.disabled = true;
        multiBtn.textContent = 'Nessuna carta nuova';
    } else {
        multiBtn.disabled = false;
        multiBtn.textContent = 'Aggiungi ' + newCount + (newCount === 1 ? ' nuova carta' : ' nuove carte');
    }

    results.forEach((r, i) => {
        const owned = isOwned(r.cardName);
        const cardEl = document.createElement('div');
        cardEl.className = 'pull-result-card rarity-' + r.rarity + ' is-hidden-face';
        cardEl.style.borderColor = RARITY_COLORS[r.rarity];

        const img = document.createElement('img');
        img.src = cardImageUrl(r.cardName);
        img.alt = r.cardName;
        img.onerror = function () { this.src = 'https://via.placeholder.com/80?text=?'; };

        const nameEl = document.createElement('div');
        nameEl.className = 'prc-name';
        nameEl.textContent = r.cardName;

        const rarityEl = document.createElement('div');
        rarityEl.className = 'prc-rarity ' + RARITY_BADGES[r.rarity];
        rarityEl.textContent = r.rarity;

        const flag = document.createElement('span');
        flag.className = 'prc-flag ' + (owned ? 'is-dupe' : 'is-new');
        flag.textContent = owned ? 'duplicato' : 'nuova';

        cardEl.appendChild(img);
        cardEl.appendChild(nameEl);
        cardEl.appendChild(rarityEl);
        cardEl.appendChild(flag);
        grid.appendChild(cardEl);

        gachaLater(() => {
            cardEl.classList.add('is-shown');
            cardEl.classList.remove('is-hidden-face');
            if (i === results.length - 1 || RARITY_RANK.indexOf(r.rarity) >= RARITY_RANK.indexOf('epico')) {
                spawnGachaParticles(r.rarity, Math.min(RARITY_PARTICLES[r.rarity] || 8, 12));
            }
        }, prefersReducedMotion() ? 40 * i : 90 * i);
    });
}

function collectMultiFromGacha() {
    if (!window._pendingMulti) return;
    const added = addNewCards(window._pendingMulti);
    window._pendingMulti = [];
    const multiBtn = document.getElementById('multiCollectBtn');
    multiBtn.disabled = true;
    multiBtn.textContent = added ? ('Aggiunte ' + added + ' carte') : 'Nessuna carta nuova';
    document.querySelectorAll('#gachaMultiGrid .prc-flag.is-new').forEach((el) => {
        el.textContent = 'in collezione';
    });
    showHint(added ? 'Nuove carte aggiunte alla collezione.' : 'Nessuna carta nuova da aggiungere.');
}

function spawnGachaParticles(rarity, count) {
    if (prefersReducedMotion()) return;
    const host = document.getElementById('gachaFxLayer');
    if (!host) return;
    const n = count != null ? count : (RARITY_PARTICLES[rarity] || 6);
    const colors = {
        'comune': ['#aaa', '#888'],
        'non-comune': ['#27ae60', '#2ecc71'],
        'raro': ['#2980b9', '#3498db'],
        'epico': ['#8e44ad', '#e040fb'],
        'leggendario': ['#f1c40f', '#f39c12'],
        'esotico': ['#ff512f', '#DD2476'],
        'mitico': ['#e74c3c', '#ff7300'],
        'segreto': ['#00f2ff', '#ff0055']
    };
    const palette = colors[rarity] || colors.comune;

    for (let i = 0; i < n; i++) {
        const p = document.createElement('div');
        p.className = 'gacha-particle';
        p.style.left = (12 + Math.random() * 76) + '%';
        p.style.bottom = (4 + Math.random() * 18) + '%';
        const size = 4 + Math.random() * 7;
        p.style.width = size + 'px';
        p.style.height = size + 'px';
        p.style.background = palette[Math.floor(Math.random() * palette.length)];
        p.style.boxShadow = '0 0 8px ' + p.style.background;
        p.style.animationDuration = (1.4 + Math.random() * 1.6) + 's';
        host.appendChild(p);
        p.addEventListener('animationend', () => p.remove());
    }
}

document.getElementById('pullSingle').addEventListener('click', startPullSingle);
document.getElementById('pullTen').addEventListener('click', startPullTen);
document.getElementById('resultCollectBtn').addEventListener('click', collectFromGacha);
document.getElementById('resultAgainBtn').addEventListener('click', resetGachaHome);
document.getElementById('multiCollectBtn').addEventListener('click', collectMultiFromGacha);
document.getElementById('multiAgainBtn').addEventListener('click', resetGachaHome);

window.addEventListener('pagehide', clearGachaTimers);
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        fF = parseInt(localStorage.getItem('gachaCurrency') || '0', 10);
        updateFFDisplay();
    }, 0);
});
updateFFDisplay();
</script>
