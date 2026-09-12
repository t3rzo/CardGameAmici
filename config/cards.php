<?php
// config/cards.php — Database carte guerrieri + helper stats RPG

// Pesi di gacha per ogni rarità (somma = 98.5)
$rarityWeights = [
    'comune'       => 50,
    'non-comune'   => 25,
    'raro'         => 12,
    'epico'        => 8,
    'leggendario'  => 4,
    'esotico'      => 2,
    'mitico'       => 1,
    'segreto'      => 0.5,
];

// Minimi/max per rarità (range di stats)
$rarityRanges = [
    'comune'      => [10, 30],
    'non-comune'  => [25, 45],
    'raro'        => [40, 60],
    'epico'       => [55, 75],
    'leggendario' => [70, 90],
    'mitico'      => [80, 100],
    'esotico'     => [75, 95],
    'segreto'     => [90, 100],
];

/**
 * Genera statistiche RPG deterministiche basate sul nome e rarità.
 * Mappa le stats legacy: forza→attacco, mentalità→vita, tecnica→difesa, velocità→velocità
 */
function genStats($name, $rarity) {
    global $rarityRanges;
    $seed = abs(crc32($name)) % 1000;
    $range = $rarityRanges[$rarity] ?? [20, 80];
    $mn = $range[0]; $mx = $range[1];
    return [
        'attacco'   => ($seed % ($mx - $mn + 1)) + $mn,
        'vita'      => ((int)($seed/3) % ($mx - $mn + 1)) + $mn,
        'difesa'    => ((int)($seed/7) % ($mx - $mn + 1)) + $mn,
        'velocità'  => ((int)($seed/11) % ($mx - $mn + 1)) + $mn,
    ];
}

/**
 * Genera un array compatto con legacy keys per backward compatibility.
 */
function genStatsLegacy($name, $rarity) {
    $s = genStats($name, $rarity);
    return [
        'forza'     => $s['attacco'],
        'velocità'  => $s['velocità'],
        'tecnica'   => $s['difesa'],
        'mentalità' => $s['vita'],
    ];
}

/**
 * Sceglie una carta pesata casualmente basandosi sui pesi di rarità.
 * Ritorna il nome della carta scelta.
 */
function weightedRandomCard(&$authors, &$rarityWeights) {
    // Costruisci pool: nome carta → peso (basato su rarità)
    $pool = [];
    foreach ($authors as $name => $data) {
        $pool[$name] = $rarityWeights[$data['rarity']] ?? 1;
    }
    $total = array_sum($pool);
    $roll = mt_rand(0, $total * 1000) / 1000; // precisione
    $accum = 0;
    foreach ($pool as $name => $weight) {
        $accum += $weight;
        if ($roll <= $accum) return $name;
    }
    return array_key_last($pool);
}

/**
 * Carica carte estese: unisce carte di base + custom_cards.json
 */
function loadCards() {
    global $authors;
    $base = $authors;

    $customPath = __DIR__ . '/../assets/data/custom_cards.json';
    if (file_exists($customPath)) {
        $customRaw = json_decode(file_get_contents($customPath), true);
        if (is_array($customRaw)) {
            foreach ($customRaw as $card) {
                if (!isset($card['name'])) continue;
                $cardCopy = $card;
                if (!isset($cardCopy['stats'])) {
                    $cardCopy['stats'] = genStatsLegacy($card['name'], $card['rarity'] ?? 'comune');
                }
                $base[$card['name']] = $cardCopy;
            }
        }
    }
    return $base;
}

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
    'Bruno e Stepan'     => ['desc' => 'Il Pinguino impallato e l\'Ucraino senza cittadinanza uniscono le forze sotto il ferro e il sudore. Uno avanza con calma glaciale, l\'altro spinge oltre ogni limite imposto al mondo. Insieme trasformano lo sforzo in progresso: non per vincere oggi, ma per arrivare più lontano di tutti.', 'rarity' => 'esotico'],
    'Ugo Verola'         => ['desc' => 'Il Farmer definitivo (Marzò). Mentre tu dormi, lui grinda; mentre tu mangi, lui scala le classifiche di Anime Eternal.', 'rarity' => 'leggendario'],
    'Stepan e Cesarini'  => ['desc' => 'Io non li ho mai visti insieme...', 'rarity' => 'comune'],
];

// Genera stats legacy per ogni carta (backward compat con search.php esistente)
foreach ($authors as $name => &$data) {
    if (!isset($data['stats'])) {
        $data['stats'] = genStatsLegacy($name, $data['rarity']);
    }
}
unset($data);
