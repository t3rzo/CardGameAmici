<?php
// game/functions.php — Logica di gioco condivisa (gacha, progressione, combattimento)

/**
 * Calcola l'XP necessario per un livello.
 */
function xpForLevel($level) {
    return (int)pow($level, 2) * 100;
}

/**
 * Aggiunge XP a una carta e gestisce il level up.
 * Returns ['level' => newLevel, 'xp' => newXp, 'levelUp' => bool]
 */
function addXp($card, $amount) {
    $card['xp'] = ($card['xp'] ?? 0) + $amount;
    $levelUp = false;
    while ($card['xp'] >= xpForLevel(($card['level'] ?? 1) + 1)) {
        $card['level'] = ($card['level'] ?? 1) + 1;
        $card['xp'] -= xpForLevel($card['level']);
        $levelUp = true;
    }
    return ['level' => ($card['level'] ?? 1), 'xp' => $card['xp'], 'levelUp' => $levelUp];
}

/**
 * Calcola lo stat di una carta con bonus level + equipaggiamento.
 * Returns ['attacco', 'vita', 'difesa', 'velocità']
 */
function calcStats($card, $equipment = null) {
    $base = $card['stats_rpg'] ?? null;
    if (!$base) {
        // Genera da legacy se necessario
        $base = [
            'attacco'  => $card['stats']['forza'] ?? 50,
            'vita'     => $card['stats']['mentalità'] ?? 50,
            'difesa'   => $card['stats']['tecnica'] ?? 50,
            'velocità' => $card['stats']['velocità'] ?? 50,
        ];
    }
    $level = $card['level'] ?? 1;
    $multiplier = 1 + ($level - 1) * 0.05; // +5% per level

    $stats = [
        'attacco'  => (int)(($base['attacco'] ?? 50) * $multiplier),
        'vita'     => (int)(($base['vita'] ?? 50) * $multiplier),
        'difesa'   => (int)(($base['difesa'] ?? 50) * $multiplier),
        'velocità' => (int)(($base['velocità'] ?? 50) * $multiplier),
    ];

    // Applica bonus equipaggiamento
    if ($equipment) {
        if (isset($equipment['weapon']['attacco'])) $stats['attacco'] += $equipment['weapon']['attacco'];
        if (isset($equipment['armor']['vita'])) $stats['vita'] += $equipment['armor']['vita'];
        if (isset($equipment['armor']['difesa'])) $stats['difesa'] += $equipment['armor']['difesa'];
        if (isset($equipment['accessory']['velocità'])) $stats['velocità'] += $equipment['accessory']['velocità'];
        if (isset($equipment['accessory']['attacco'])) $stats['attacco'] += $equipment['accessory']['attacco'];
    }

    return $stats;
}

/**
 * Calcola danni in combattimento.
 */
function calcDamage($attackerStats, $defenderStats) {
    $dmg = $attackerStats['attacco'] - ($defenderStats['difesa'] * 0.3);
    return max(1, (int)$dmg);
}

/**
 * Determina chi va prima in combattimento (basato su velocità).
 * Returns array of [attackerStats, name] in turn order.
 */
function resolveTurnOrder($playerStats, $enemyStats) {
    if ($playerStats['velocità'] >= $enemyStats['velocità']) {
        return ['player', 'enemy'];
    }
    return ['enemy', 'player'];
}
