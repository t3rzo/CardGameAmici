<?php
// config/zones.php — Zone di avanzamento RPG con nemici e scaling

$zones = [
    [
        'id' => 'foresta',
        'nome' => 'Foresta di Marzabot',
        'livello_min' => 1,
        'descrizione' => 'Una foresta luminosa dove i semi parlano e le carte crescono.',
        'enemy_pool' => ['Francesco Orciuoli', 'Matteo Iazzetta'],
        'ff_reward_multiplier' => 1.0,
        'bg_class' => 'body-forest',
    ],
    [
        'id' => 'città',
        'nome' => 'Città di Napoli',
        'livello_min' => 5,
        'descrizione' => 'Strade strette, caffè forti e mercenari senza scrupoli.',
        'enemy_pool' => ['Matteo Iazzetta', 'Cesarini Shadow'],
        'ff_reward_multiplier' => 1.5,
        'bg_class' => 'body-city',
    ],
    [
        'id' => 'torre',
        'nome' => 'Torre del Cazzeggio',
        'livello_min' => 8,
        'descrizione' => 'La cima della torre custodisce i segreti più antichi del gruppo.',
        'enemy_pool' => ['Bruno Silvati', 'Umberto Cesarini'],
        'ff_reward_multiplier' => 2.0,
        'bg_class' => 'body-tower',
    ],
];

$rarityColors = ['comune'=>'#7f8c8d','non-comune'=>'#27ae60','raro'=>'#2980b9','epico'=>'#8e44ab','leggendario'=>'#f1c40f','esotico'=>'#ff512f','mitico'=>'#e74c3c','segreto'=>'#00f2ff'];
