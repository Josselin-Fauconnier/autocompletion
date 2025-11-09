<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$animal = null;
$errorMessage = '';

if ($id <= 0) {
    $errorMessage = 'ID d\'animal invalide.';
} else {
    try {
        $pdo = db();
        $pdo->query('SET NAMES utf8mb4');
        
        $sql = 'SELECT id, nom_fr, nom_latin, categorie 
                FROM animaux 
                WHERE id = ? 
                LIMIT 1';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $animal = $stmt->fetch();
        
        if (!$animal) {
            $errorMessage = 'Animal non trouvé.';
        }
        
    } catch (Exception $e) {
        $errorMessage = 'Erreur lors de la récupération des données.';
        error_log('Erreur element.php: ' . $e->getMessage());
    }
}

function escapeHtml(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function getCategoryInfo(string $category): array {
    $categories = [
        'mammifere' => [
            'name' => 'Mammifère',
            'description' => 'Animal vertébré à sang chaud qui allaite ses petits',
            'icon' => '🦌'
        ],
        'oiseau' => [
            'name' => 'Oiseau',
            'description' => 'Animal vertébré ovipare au corps recouvert de plumes',
            'icon' => '🦅'
        ],
        'poisson' => [
            'name' => 'Poisson',
            'description' => 'Animal vertébré aquatique respirant par des branchies',
            'icon' => '🟠'
        ],
        'reptile' => [
            'name' => 'Reptile',
            'description' => 'Animal vertébré à sang froid au corps recouvert d\'écailles',
            'icon' => '🦎'
        ],
        'insecte' => [
            'name' => 'Insecte',
            'description' => 'Animal invertébré articulé à six pattes',
            'icon' => '🛠'
        ]
    ];
    
    return $categories[$category] ?? [
        'name' => ucfirst($category),
        'description' => 'Classification non définie',
        'icon' => '❓'
    ];
}

$pageTitle = $animal ? 
    escapeHtml($animal['nom_fr']) . ' - Le dico des animaux' : 
    'Animal non trouvé - Le dico des animaux';

$metaDescription = $animal ? 
    'Découvrez ' . escapeHtml($animal['nom_fr']) . ' (' . escapeHtml($animal['nom_latin']) . '), ' . 
    strtolower(getCategoryInfo($animal['categorie'])['description']) . '.' :
    'Animal non trouvé dans notre base de données.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= escapeHtml($metaDescription) ?>">
    <?php if ($animal): ?>
        <meta name="keywords" content="<?= escapeHtml($animal['nom_fr']) ?>, <?= escapeHtml($animal['nom_latin']) ?>, <?= getCategoryInfo($animal['categorie'])['name'] ?>, animaux">
    <?php endif; ?>
    <title><?= escapeHtml($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
             <nav class="nav-secondary">
                <button id="theme-toggle" type="button" class="nav-link" aria-label="Basculer le thème">
                    <span id="theme-icon" aria-hidden="true">🐣</span>
                </button>
            </nav>
            
            <div class="search-header">
                <form action="recherche.php" method="get" class="search-form" role="search">
                    <div class="search-wrapper">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input-header"
                            class="search-input"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                            aria-autocomplete="list"
                            role="combobox"
                            aria-expanded="false"
                            aria-owns="suggestions-list-header"
                            aria-controls="suggestions-list-header"
                        >
                        <div class="search-loading" id="search-loading-header" aria-hidden="true">
                            <span class="loading-spinner"></span>
                        </div>
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
                            🔍
                        </button>
                    </div>
                    
                    <div id="suggestions-container-header" class="suggestions-container" role="listbox">
                        <ul id="suggestions-list-header" class="suggestions-list"></ul>
                    </div>
                </form>
            </div>
        </header>

        <main class="main-content">
            <div class="animal-container">
                <?php if ($errorMessage): ?>
                    <div class="error-section">
                        <div class="error-content">
                            <h1>😕 Oups !</h1>
                            <p class="error-message"><?= escapeHtml($errorMessage) ?></p>
                            <div class="error-actions">
                                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                                <a href="recherche.php" class="btn btn-secondary">Faire une recherche</a>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <?php $categoryInfo = getCategoryInfo($animal['categorie']); ?>
                    
                    <nav class="breadcrumb" aria-label="Fil d'Ariane">
                        <ol class="breadcrumb-list">
                            <li class="breadcrumb-item">
                                <a href="index.php">Accueil</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="recherche.php?search=<?= urlencode($categoryInfo['name']) ?>">
                                    <?= escapeHtml($categoryInfo['name']) ?>s
                                </a>
                            </li>
                            <li class="breadcrumb-item current" aria-current="page">
                                <?= escapeHtml($animal['nom_fr']) ?>
                            </li>
                        </ol>
                    </nav>

                    <article class="animal-details">
                        <header class="animal-header">
                            <div class="animal-icon">
                                <?= $categoryInfo['icon'] ?>
                            </div>
                            <div class="animal-title-group">
                                <h1 class="animal-name-fr"><?= escapeHtml($animal['nom_fr']) ?></h1>
                                <p class="animal-name-latin">
                                    <em><?= escapeHtml($animal['nom_latin']) ?></em>
                                </p>
                                <div class="animal-category">
                                    <span class="category-badge category-<?= escapeHtml($animal['categorie']) ?>">
                                        <?= $categoryInfo['icon'] ?> <?= escapeHtml($categoryInfo['name']) ?>
                                    </span>
                                </div>
                            </div>
                        </header>

                        <div class="animal-content">
                            <section class="animal-info">
                                <h2>Informations taxonomiques</h2>
                                <dl class="info-list">
                                    <dt>Nom français :</dt>
                                    <dd><?= escapeHtml($animal['nom_fr']) ?></dd>
                                    
                                    <dt>Nom scientifique :</dt>
                                    <dd><em><?= escapeHtml($animal['nom_latin']) ?></em></dd>
                                    
                                    <dt>Classification :</dt>
                                    <dd><?= escapeHtml($categoryInfo['description']) ?></dd>
                                    
                                    <dt>Identifiant :</dt>
                                    <dd>#<?= (int)$animal['id'] ?></dd>
                                </dl>
                            </section>

                            <section class="animal-actions">
                                <h2>Actions</h2>
                                <div class="action-buttons">
                                    <a href="recherche.php?search=<?= urlencode($animal['nom_fr']) ?>" 
                                       class="btn btn-primary">
                                        🔍 Recherches similaires
                                    </a>
                                    <a href="recherche.php?search=<?= urlencode($categoryInfo['name']) ?>" 
                                       class="btn btn-secondary">
                                        📋 Voir tous les <?= escapeHtml(strtolower($categoryInfo['name'])) ?>s
                                    </a>
                                </div>
                            </section>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
    <script src="js/completion.js" defer></script>
</body>
</html>