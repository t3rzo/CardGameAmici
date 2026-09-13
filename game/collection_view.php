<?php
// collection_view.php — Vista collezione dettagiata
?>

<div class="gacha-container" style="text-align: center;">
    <h2 style="font-family:'Orbitron'; color:var(--rpg-gold); margin-bottom:20px;">⚔ LA TUA COLLEZIONE</h2>

    <div id="collectionDetail">
        <div style="color:#aaa; font-family:'Share Tech Mono'; padding:40px;">
            Caricamento collezione...
        </div>
    </div>

    <div style="margin-top:20px;">
        <button class="gacha-btn" onclick="window.location.href='?mode=gacha'" style="margin-top:10px;">
            ← Torna al Gacha
        </button>
    </div>
</div>

<script>
// collection definita globalmente in index.php head
window.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('collectionDetail');
    const names = Object.keys(collection);
    if (names.length === 0) {
        container.innerHTML = '<div style="color:#888; font-family:"Share Tech Mono"; padding:40px;">Nessuna carta collezionata.<br>Usa il gacha per iniziare!</div>';
        return;
    }
    let totalPts = 0;
    const colors = {'comune':'#7f8c8d','non-comune':'#27ae60','raro':'#2980b9','epico':'#8e44ad','leggendario':'#f1c40f','esotico':'#ff512f','mitico':'#e74c3c','segreto':'#00f2ff'};

    container.innerHTML = names.map(name => {
        const c = collection[name];
        const s = c.stats_rpg || c.stats || {};
        const pts = Object.values(s).reduce((a,b)=>a+b,0) || 0;
        totalPts += pts;
        return `<div style="background:rgba(255,255,255,0.05); border:1px solid ${colors[c.rarity]||'#fff'}; border-radius:10px; padding:15px; margin-bottom:10px; text-align:left; cursor:pointer;"
                    onclick="window.location.href='?mode=gacha'">
            <div style="display:flex; align-items:center; gap:12px;">
                <img src="./images/${encodeURIComponent(name)}.png" onerror="this.src='https://via.placeholder.com/60?text=?'" style="width:60px;height:60px;object-fit:contain;">
                <div>
                    <div style="font-weight:bold; color:#fff;">${name} <span style="color:${colors[c.rarity]||'#fff'}">Lv.${c.level||1}</span></div>
                    <div style="font-size:11px; color:#aaa;">${c.rarity}</div>
                    <div style="font-size:11px; font-family:'Share Tech Mono'; color:#ccc;">ATK:${s.attacco||s.forza||50} VITA:${s.vita||s.mentalità||50} DIF:${s.difesa||s.tecnica||50} SPD:${s.velocità||0}</div>
                </div>
            </div>
        </div>`;
    }).join('');
    container.innerHTML += `<div style="color:var(--rpg-gold); font-family:'Orbitron'; margin-top:15px;">Totale carte: ${names.length} | Punti: ${totalPts}</div>`;
});
</script>
