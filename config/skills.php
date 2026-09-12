<?php
// config/skills.php — Sistema skill (abilità) per le carte

$skillNames = [
    'potere_furia'   => ['nome' => 'Furia',       'descrizione' => 'Attacco potenziato del 50%. CD: 3 turni.', 'icon' => '🔥', 'cd' => 3, 'multiplier' => 1.5],
    'scudo_duramento' => ['nome' => 'Scudo',      'descrizione' => 'Difesa aumentata del 30% per 2 turni. CD: 4 turni.', 'icon' => '🛡️', 'cd' => 4, 'buff' => ['difesa' => 0.3, 'duration' => 2]],
    'velocita_istantanea' => ['nome' => 'Rapidità', 'descrizione' => 'Agisci due volte nel prossimo turno. CD: 5 turni.', 'icon' => '⚡', 'cd' => 5, 'double_attack' => true],
    'colpo_critico'  => ['nome' => 'Colpo Critico', 'descrizione' => 'Garantisce un critico nel prossimo attacco. CD: 4 turni.', 'icon' => '🎯', 'cd' => 4, 'guaranteed_crit' => true],
    'rigenerazione'  => ['nome' => 'Rigenerazione', 'descrizione' => 'Recupera 20% HP massimi. CD: 5 turni.', 'icon' => '💚', 'cd' => 5, 'heal_pct' => 0.2],
    'trappola'       => ['nome' => 'Trappola',    'descrizione' => 'Riflette 50% danni subiti al prossimo attacco nemico. CD: 4 turni.', 'icon' => '🪤', 'cd' => 4, 'reflect' => 0.5],
];

// Skill sbloccabili per level
$skillUnlockLevels = [10, 25, 40];

// Mappa default skill per ogni carta (può essere sovrascritto)
// Se non specificata, la carta ottiene skill casuali
$cardSkills = [
    // Carte speciali hanno skill uniche
    'Il Trio Tarallo'  => ['potere_furia', 'rigenerazione'],
    'Michele e Cesarini' => ['velocita_istantanea', 'trappola'],
    'Umberto Cesarini'   => ['colpo_critico', 'scudo_duramento'],
    'Bruno Silvati'     => ['scudo_duramento', 'rigenerazione'],
];
