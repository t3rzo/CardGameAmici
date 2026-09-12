<?php
// config/equipment.php — Database equipaggiamenti (armi, armature, accessori)
// Ogni equipaggio: nome, tipo, bonus stats, rarity, descrizione

$equipmentTypes = ['arma', 'armatura', 'accessorio'];
$rarityColors = ['comune'=>'#7f8c8d','non-comune'=>'#27ae60','raro'=>'#2980b9','epico'=>'#8e44ad','leggendario'=>'#f1c40f','esotico'=>'#ff512f','mitico'=>'#e74c3c','segreto'=>'#00f2ff'];

// === ARMI === (aumentano attacco)
$weapons = [
    [
        'nome' => 'Pugnale da Cucina',
        'tipo' => 'arma',
        'attacco' => 5,
        'velocità' => 2,
        'rarity' => 'comune',
        'rarity_weight' => 20,
        'desc' => 'Un pugnale arrugginito. Qualcosa di più pericoloso di un cucchiaio.',
        'slot_icon' => '🔪',
    ],
    [
        'nome' => 'Fucile da Caccia',
        'tipo' => 'arma',
        'attacco' => 15,
        'velocità' => -2,
        'rarity' => 'raro',
        'rarity_weight' => 8,
        'desc' => 'Perfetto per inseguire preti in fuga.',
        'slot_icon' => '🔫',
    ],
    [
        'nome' => 'Macheta Termica',
        'tipo' => 'arma',
        'attacco' => 22,
        'velocità' => 3,
        'crit' => 0.15,
        'rarity' => 'epico',
        'rarity_weight' => 4,
        'desc' => 'Taglia attraverso l\'esistenza con stile.',
        'slot_icon' => '⚔️',
    ],
    [
        'nome' => 'Ascia del Colosso',
        'tipo' => 'arma',
        'attacco' => 35,
        'difesa' => 5,
        'velocità' => -5,
        'rarity' => 'leggendario',
        'rarity_weight' => 2,
        'desc' => 'Un\'arma che parla da sola: "CRASH!"',
        'slot_icon' => '🪓',
    ],
    [
        'nome' => 'Kanabo Energetico',
        'tipo' => 'arma',
        'attacco' => 45,
        'velocità' => 4,
        'crit' => 0.20,
        'rarity' => 'mitico',
        'rarity_weight' => 0.5,
        'desc' => 'Un arma giapponese che emana energia cosmica.',
        'slot_icon' => '🌟',
    ],
    [
        'nome' => 'Glitch Blade',
        'tipo' => 'arma',
        'attacco' => 60,
        'velocità' => 10,
        'crit' => 0.25,
        'rarity' => 'segreto',
        'rarity_weight' => 0.1,
        'desc' => 'Un glitch che ha preso forma di lama. Esiste davvero?',
        'slot_icon' => '🌀',
    ],
];

// === ARMOIRE === (aumentano vita + difesa)
$armors = [
    [
        'nome' => 'Maglia di Lana',
        'tipo' => 'armatura',
        'vita' => 10,
        'difesa' => 3,
        'rarity' => 'comune',
        'rarity_weight' => 15,
        'desc' => 'Calda e confortevole, ma non tanto da non sentire un colpo.',
        'slot_icon' => '🧥',
    ],
    [
        'nome' => 'Corazza da Combattimento',
        'tipo' => 'armatura',
        'vita' => 25,
        'difesa' => 8,
        'velocità' => -2,
        'rarity' => 'raro',
        'rarity_weight' => 7,
        'desc' => 'Progettata per resistere a colpi potenti e discussioni infinite.',
        'slot_icon' => '🛡️',
    ],
    [
        'nome' => 'Piastre del Giudice',
        'tipo' => 'armatura',
        'vita' => 45,
        'difesa' => 15,
        'velocità' => -4,
        'rarity' => 'epico',
        'rarity_weight' => 3,
        'desc' => 'Chi le indossa non ha bisogno di scuse.',
        'slot_icon' => '🧱',
    ],
    [
        'nome' => 'Tunica del Cyborg',
        'tipo' => 'armatura',
        'vita' => 35,
        'difesa' => 12,
        'velocità' => 5,
        'rarity' => 'leggendario',
        'rarity_weight' => 1.5,
        'desc' => 'Parti metallo, parte carne. Quasi un ibrido.',
        'slot_icon' => '🤖',
    ],
    [
        'nome' => 'Sudario del Vuoto',
        'tipo' => 'armatura',
        'vita' => 60,
        'difesa' => 25,
        'velocità' => 3,
        'rarity' => 'mitico',
        'rarity_weight' => 0.8,
        'desc' => 'Più che un\'armatura, un\'esistenza paralelletta.',
        'slot_icon' => '🕳️',
    ],
];

// === ACCESSORI === (bonus variabili)
$accessories = [
    [
        'nome' => 'Anello del Ritardo',
        'tipo' => 'accessorio',
        'velocità' => 3,
        'rarity' => 'comune',
        'rarity_weight' => 10,
        'desc' => 'Fa andare un po\' più veloce chi lo indossa. Ironia: si chiama "ritardo".',
        'slot_icon' => '💍',
    ],
    [
        'nome' => 'Collana dei Cuori',
        'tipo' => 'accessorio',
        'attacco' => 5,
        'vita' => 15,
        'rarity' => 'non-comune',
        'rarity_weight' => 6,
        'desc' => 'Battito dopo battito, più forte.',
        'slot_icon' => '📿',
    ],
    [
        'nome' => 'Occhiali da Soothsayer',
        'tipo' => 'accessorio',
        'crit' => 0.08,
        'velocità' => 4,
        'rarity' => 'raro',
        'rarity_weight' => 4,
        'desc' => 'Vede il futuro, ma non il presente.',
        'slot_icon' => '😎',
    ],
    [
        'nome' => 'Polsino di Ferro',
        'tipo' => 'accessorio',
        'difesa' => 7,
        'vita' => 20,
        'rarity' => 'epico',
        'rarity_weight' => 2,
        'desc' => 'Indossato solo da chi parla poco.',
        'slot_icon' => '⌚',
    ],
    [
        'nome' => 'Amuleto della Stella Morente',
        'tipo' => 'accessorio',
        'attacco' => 12,
        'velocità' => 6,
        'crit' => 0.15,
        'rarity' => 'leggendario',
        'rarity_weight' => 1,
        'desc' => "Quando una stella muore, nasce un'idea. E un potere.",
        'slot_icon' => '🌠',
    ],
    [
        'nome' => "CD-ROM dell'Arcobaleno",
        'tipo' => 'accessorio',
        'attacco' => 20,
        'difesa' => 10,
        'velocità' => 8,
        'crit' => 0.30,
        'rarity' => 'segreto',
        'rarity_weight' => 0.2,
        'desc' => "Non legge. Non scrive. Ma può distruggere l'universo.",
        'slot_icon' => '💿',
    ],
];

$allEquipment = array_merge($weapons, $armors, $accessories);

/**
 * Roll equpaggio casuale pesato per rarità.
 */
function weightedRandomEquipment(&$allEquipment) {
    $pool = [];
    foreach ($allEquipment as $item) {
        $weight = $item['rarity_weight'] ?? 1;
        $pool[] = ['item' => $item, 'weight' => $weight];
    }
    $total = array_sum(array_column($pool, 'weight'));
    $roll = mt_rand(0, $total * 1000) / 1000;
    $accum = 0;
    foreach ($pool as $entry) {
        $accum += $entry['weight'];
        if ($roll <= $accum) return $entry['item'];
    }
    return $pool[count($pool)-1]['item'];
}
