<?php
// config/enemies.php — Database nemici per combattimento
// Ogni nemico: nome, livello, stats-base (attacco, vita, difesa, velocità), immagine opzionale, xp_drop

$enemies = [
    [
        'nome' => 'Francesco Orciuoli',
        'lvl' => 3,
        'attacco' => 25,
        'vita' => 120,
        'difesa' => 10,
        'velocità' => 8,
        'xp_drop' => 45,
        'ff_drop_min' => 2,
        'ff_drop_max' => 5,
        'img' => '', // placeholder
    ],
    [
        'nome' => 'Matteo Iazzetta',
        'lvl' => 5,
        'attacco' => 35,
        'vita' => 180,
        'difesa' => 15,
        'velocità' => 12,
        'xp_drop' => 75,
        'ff_drop_min' => 4,
        'ff_drop_max' => 8,
        'img' => '',
    ],
    [
        'nome' => 'Cesarini Shadow',
        'lvl' => 8,
        'attacco' => 50,
        'vita' => 280,
        'difesa' => 20,
        'velocità' => 14,
        'xp_drop' => 120,
        'ff_drop_min' => 6,
        'ff_drop_max' => 12,
        'img' => '',
    ],
    [
        'nome' => 'Bruno il Pinguino',
        'lvl' => 10,
        'attacco' => 45,
        'vita' => 400,
        'difesa' => 30,
        'velocità' => 5,
        'xp_drop' => 180,
        'ff_drop_min' => 8,
        'ff_drop_max' => 15,
        'img' => '',
    ],
];
