<?php
// config/tasks.php — Task giornalieri per guadagnare valuta

$dailyTasks = [
    [
        'id' => 'win_1_battle',
        'nome' => 'Vinci 1 battaglia',
        'descrizione' => 'Completa una vittoria in combattimento.',
        'reward_type' => 'ff',
        'reward_amount' => 50,
        'icon' => '⚔️',
    ],
    [
        'id' => 'win_3_battle',
        'nome' => 'Vinci 3 battaglie',
        'descrizione' => 'Ottieni 3 vittorie in combattimento.',
        'reward_type' => 'ff',
        'reward_amount' => 120,
        'icon' => '⚔️⚔️⚔️',
        'requires' => 'win_1_battle',
    ],
    [
        'id' => 'pull_3_gacha',
        'nome' => 'Tira 3 volte dal Gacha',
        'descrizione' => 'Effettua 3 pull singole nel gacha.',
        'reward_type' => 'ff',
        'reward_amount' => 80,
        'icon' => '🎰🎰🎰',
    ],
    [
        'id' => 'collect_5_cards',
        'nome' => 'Raccogli 5 carte',
        'descrizione' => 'Aggiungi 5 carte alla tua collezione.',
        'reward_type' => 'ff',
        'reward_amount' => 100,
        'icon' => '📚📚📚📚📚',
    ],
];

// Track del completamento task (in localStorage)
// Struttura: dailyTasks = { '2024-01-15': { 'win_1_battle': true, ... } }
