<?php
// admin.php — Interfaccia admin per aggiungere nuove carte
// Salva in assets/data/custom_cards.json (formato identico a cards.php author array)

$action = $_GET['action'] ?? 'list';
$customPath = __DIR__ . '/assets/data/custom_cards.json';

// Lista carte custom
function listCustomCards($path) {
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

// Salva carta custom
if ($_POST['add_card']) {
    $newCard = [
        'name' => trim($_POST['name']),
        'desc' => trim($_POST['desc']),
        'rarity' => in_array($_POST['rarity'], ['comune','non-comune','raro','epico','leggendario','esotico','mitico','segreto']) ? $_POST['rarity'] : 'comune',
    ];

    if (isset($_POST['extra_gif']) && trim($_POST['extra_gif']) !== '') {
        $newCard['extra_gif'] = trim($_POST['extra_gif']);
    }

    if ($newCard['name']) {
        $existing = listCustomCards($customPath);
        $existing[$newCard['name']] = $newCard;
        file_put_contents($customPath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $saved = true;
    }
}

// Rimuovi carta
if (isset($_GET['remove'])) {
    $name = $_GET['remove'];
    $existing = listCustomCards($customPath);
    unset($existing[$name]);
    file_put_contents($customPath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Carica carte esistenti
$customCards = listCustomCards($customPath);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Card Creator</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/main.css">
    <style>
    .admin-container {
        width: 100%; max-width: 900px; margin: 40px auto;
        padding: 30px; background: rgba(0,0,0,0.7);
        border-radius: 20px; border: 2px solid var(--rpg-gold);
    }
    .admin-title {
        font-family: 'Orbitron'; font-size: 28px; color: var(--rpg-gold);
        text-align: center; margin-bottom: 30px;
        text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
    }
    .admin-form {
        display: grid; grid-template-columns: 1fr 1fr; gap: 15px;
        background: rgba(255,255,255,0.05); padding: 20px;
        border-radius: 12px; margin-bottom: 30px;
    }
    .admin-form.full { grid-column: 1 / -1; }
    .admin-form label {
        font-family: 'Share Tech Mono'; font-size: 12px; color: #aaa;
        text-transform: uppercase; letter-spacing: 1px;
    }
    .admin-form input, .admin-form textarea, .admin-form select {
        width: 100%; padding: 10px; border-radius: 8px;
        border: 2px solid rgba(255,255,255,0.15);
        background: rgba(0,0,0,0.4); color: #fff;
        font-family: 'Share Tech Mono'; font-size: 13px;
    }
    .admin-form input:focus, .admin-form textarea:focus, .admin-form select:focus {
        border-color: var(--rpg-gold); outline: none;
        box-shadow: 0 0 10px var(--rpg-gold);
    }
    .admin-form textarea { min-height: 80px; resize: vertical; }
    .admin-submit {
        grid-column: 1 / -1; text-align: center;
    }
    .admin-btn {
        padding: 12px 30px; border: 2px solid var(--rpg-gold);
        background: rgba(0,0,0,0.5); color: var(--rpg-gold);
        font-family: 'Orbitron'; font-size: 14px; cursor: pointer;
        border-radius: 8px; transition: all 0.3s ease;
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
    }
    .admin-btn:hover {
        background: var(--rpg-gold); color: #000;
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
    }

    /* Lista carte */
    .card-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
    .custom-card-item {
        background: rgba(255,255,255,0.05); border: 1px solid var(--cyber-cyan);
        border-radius: 10px; padding: 12px; text-align: center;
    }
    .custom-card-item img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 8px; }
    .custom-card-name { font-weight: bold; color: #fff; font-size: 13px; margin-bottom: 4px; }
    .custom-card-rarity { font-size: 10px; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
    .remove-btn { margin-top: 8px; font-size: 10px; }

    .success-msg {
        background: rgba(39, 174, 98, 0.2); border: 1px solid #27ae60;
        border-radius: 8px; padding: 10px; margin-bottom: 20px;
        color: #27ae60; text-align: center; font-family: 'Orbitron';
    }
    </style>
</head>
<body class="gallery-theme">
    <div class="scanline-overlay"></div>
    <div class="admin-container">
        <h1 class="admin-title">⚔ ADMIN — CREATORE CARTE</h1>

        <?php if (isset($saved)): ?>
            <div class="success-msg">✓ Carta '<?php echo htmlspecialchars($newCard['name']); ?>' aggiunta con successo!</div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div>
                <label>Nome Carta *</label>
                <input type="text" name="name" placeholder="Es: Michele e Cesarini" required>
            </div>
            <div>
                <label>Rarità *</label>
                <select name="rarity" required>
                    <option value="comune">Comune</option>
                    <option value="non-comune">Non Comune</option>
                    <option value="raro">Raro</option>
                    <option value="epico">Epico</option>
                    <option value="leggendario">Leggendario</option>
                    <option value="esotico">Esotico</option>
                    <option value="mitico">Mitico</option>
                    <option value="segreto">Segreto</option>
                </select>
            </div>
            <div class="full">
                <label>Descrizione *</label>
                <textarea name="desc" placeholder="Inserisci la descrizione/flavor text della carta" required></textarea>
            </div>
            <div>
                <label>Extra GIF (opzionale)</label>
                <input type="text" name="extra_gif" placeholder="Es: ./gif/eldenring.gif">
            </div>
            <div class="full" style="text-align: right;">
                <button type="submit" name="add_card" class="admin-btn">✨ Aggiungi Carta</button>
            </div>
        </form>

        <h3 style="color:var(--cyber-cyan); font-family:'Orbitron'; margin-bottom:15px;">Carte Personalizzate (<?php echo count($customCards); ?>)</h3>

        <div class="card-list">
            <?php if (empty($customCards)): ?>
                <div style="color:#888; font-family:'Share Tech Mono'; grid-column:1/-1; text-align:center; padding:30px;">
                    Nessuna carta personalizzata. Aggiungine una!
                </div>
            <?php else: ?>
                <?php foreach ($customCards as $name => $card): ?>
                <?php
                $rarityColors = ['comune'=>'#7f8c8d','non-comune'=>'#27ae60','raro'=>'#2980b9','epico'=>'#8e44ad','leggendario'=>'#f1c40f','esotico'=>'#ff512f','mitico'=>'#e74c3c','segreto'=>'#00f2ff'];
                $imgPath = './images/' . rawurlencode($name) . '.png';
                ?>
                <div class="custom-card-item">
                    <div style="position:relative;width:80px;height:80px;margin:0 auto 8px;">
                        <img src="<?php echo $imgPath; ?>" onerror="this.src='https://via.placeholder.com/80?text=?'" alt="<?php echo $name; ?>">
                        <div style="position:absolute;bottom:-5px;left:0;right:0;text-align:center;">
                            <span class="custom-card-rarity" style="background:<?php echo $rarityColors[$card['rarity']] ?? '#7f8c8f'; ?>; color:white;">
                                <?php echo $card['rarity']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="custom-card-name"><?php echo htmlspecialchars($name); ?></div>
                    <a href="?remove=<?php echo urlencode($name); ?>" class="remove-btn admin-btn"
                       style="background:rgba(231,76,60,0.2); border-color:#e74c3c; color:#e74c3c;"
                       onclick="return confirm('Rimuovere <?php echo addslashes($name); ?>?')">🗑 Rimuovi</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top:20px; text-align:center;">
            <a href="?mode=gallery" style="color:var(--cyber-cyan);">← Torna alla Galleria</a>
        </div>
    </div>
</body>
</html>
