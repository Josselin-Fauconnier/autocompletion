<?php
declare(strict_types=1);

// Configuration des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'config/db.php';

// Récupération et validation de l'ID
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
            'description' => 'Animal vertébré à sang chaud qui allaite ses petits'
        ],
        'oiseau' => [
            'name' => 'Oiseau',
            'description' => 'Animal vertébré ovipare au corps recouvert de plumes'
        ],
        'poisson' => [
            'name' => 'Poisson',
            'description' => 'Animal vertébré aquatique respirant par des branchies'
        ],
        'reptile' => [
            'name' => 'Reptile',
            'description' => 'Animal vertébré à sang froid au corps recouvert d\'écailles'
        ],
        'insecte' => [
            'name' => 'Insecte',
            'description' => 'Animal invertébré articulé à six pattes'
        ]
    ];
    
    return $categories[$category] ?? [
        'name' => ucfirst($category),
        'description' => 'Classification non définie'
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
    <style>
        /* Styles spécifiques à element.php intégrés pour éviter un fichier CSS séparé */
        .animal-container { max-width: 800px; margin: 0 auto; padding: var(--spacing-xl); }
        .error-section { display: flex; align-items: center; justify-content: center; min-height: 60vh; text-align: center; }
        .error-content h1 { font-size: 3rem; margin-bottom: var(--spacing-md); color: var(--text-secondary); }
        .error-message { font-size: 1.1rem; color: var(--text-secondary); margin-bottom: var(--spacing-xl); }
        .error-actions { display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap; }
        .breadcrumb { margin-bottom: var(--spacing-xl); font-size: 0.875rem; }
        .breadcrumb-list { display: flex; list-style: none; gap: var(--spacing-xs); align-items: center; flex-wrap: wrap; }
        .breadcrumb-item { display: flex; align-items: center; }
        .breadcrumb-item:not(:last-child)::after { content: "›"; margin-left: var(--spacing-xs); color: var(--text-muted); font-weight: bold; }
        .breadcrumb-item a { color: var(--primary); text-decoration: none; transition: color var(--transition-fast); }
        .breadcrumb-item a:hover { color: var(--primary-dark); text-decoration: underline; }
        .breadcrumb-item.current { color: var(--text-secondary); font-weight: 500; }
        .animal-details { background: var(--bg-input); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; }
        .animal-header { background: linear-gradient(135deg, var(--primary-light), var(--primary)); color: white; padding: var(--spacing-xl); display: flex; align-items: center; gap: var(--spacing-lg); }
        .animal-icon { font-size: 4rem; flex-shrink: 0; }
        .animal-title-group { flex: 1; }
        .animal-name-fr { font-size: 2.5rem; font-weight: 700; margin-bottom: var(--spacing-sm); text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); }
        .animal-name-latin { font-size: 1.25rem; opacity: 0.9; margin-bottom: var(--spacing-md); font-style: italic; }
        .animal-category { display: flex; align-items: center; }
        .animal-header .category-badge { background: rgba(255, 255, 255, 0.2); color: white; padding: var(--spacing-xs) var(--spacing-md); border-radius: var(--radius-full); font-size: 0.875rem; font-weight: 600; backdrop-filter: blur(10px); }
        .animal-content { padding: var(--spacing-xl); }
        .animal-content section { margin-bottom: var(--spacing-xl); }
        .animal-content h2 { color: var(--primary); font-size: 1.5rem; margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-sm); border-bottom: 2px solid var(--border-light); }
        .info-list { display: grid; gap: var(--spacing-md); }
        .info-list dt { font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-xs); }
        .info-list dd { color: var(--text-secondary); padding-left: var(--spacing-md); border-left: 3px solid var(--border-light); margin-bottom: var(--spacing-md); }
        .info-list dd em { color: var(--primary); }
        .action-buttons { display: flex; gap: var(--spacing-md); flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: var(--spacing-xs); padding: var(--spacing-sm) var(--spacing-lg); border-radius: var(--radius-md); text-decoration: none; font-weight: 500; transition: all var(--transition-fast); border: 2px solid transparent; cursor: pointer; font-size: 0.875rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: transparent; color: var(--primary); border-color: var(--primary); }
        .btn-secondary:hover { background: var(--primary); color: white; transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .search-header { padding: var(--spacing-md) 0; }
        .search-form-header { position: relative; max-width: 600px; margin: 0 auto; }
        .search-wrapper-header { position: relative; display: flex; align-items: center; }
        .search-input-header { width: 100%; padding: var(--spacing-sm) var(--spacing-lg); padding-right: 50px; font-size: 0.9rem; border: 1px solid var(--border-color); border-radius: var(--radius-full); background: var(--bg-input); transition: all var(--transition-base); }
        .search-input-header:focus { outline: none; border-color: var(--border-focus); box-shadow: 0 0 0 2px rgba(45, 106, 79, 0.1); }
        .search-button-header { position: absolute; right: 5px; height: 32px; width: 32px; border: none; background: var(--primary); color: white; border-radius: 50%; cursor: pointer; font-size: 0.8rem; transition: all var(--transition-fast); }
        .search-button-header:hover { background: var(--primary-dark); transform: scale(1.05); }
        .footer { margin-top: auto; background: var(--bg-input); border-top: 1px solid var(--border-light); padding: var(--spacing-lg) 0; text-align: center; }
        .footer-content { max-width: 800px; margin: 0 auto; padding: 0 var(--spacing-md); }
        .footer-content p { color: var(--text-secondary); font-size: 0.875rem; margin-bottom: var(--spacing-xs); }
        .footer-link { color: var(--primary); text-decoration: none; transition: color var(--transition-fast); }
        .footer-link:hover { color: var(--primary-dark); text-decoration: underline; }
        @media (max-width: 768px) { 
            .animal-container { padding: var(--spacing-md); }
            .animal-header { flex-direction: column; text-align: center; gap: var(--spacing-md); }
            .animal-name-fr { font-size: 2rem; }
            .animal-content { padding: var(--spacing-lg) var(--spacing-md); }
            .action-buttons { flex-direction: column; }
            .btn { justify-content: center; }
            .error-actions { flex-direction: column; align-items: center; }
        }
        @media (max-width: 480px) { 
            .animal-icon { font-size: 3rem; }
            .animal-name-fr { font-size: 1.75rem; }
            .breadcrumb-list { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav-secondary">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="recherche.php" class="nav-link">Recherche</a>
            </nav>
            
            <div class="search-header">
                <form action="recherche.php" method="get" class="search-form-header" role="search">
                    <div class="search-wrapper-header">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input-header"
                            class="search-input-header"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                        >
                        <button type="submit" class="search-button-header" aria-label="Lancer la recherche">
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
                    <!-- Message d'erreur -->
                    <div class="error-section">
                        <div class="error-content">
                            <h1> Oups !</h1>
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
                                         Voir tous les <?= escapeHtml(strtolower($categoryInfo['name'])) ?>s
                                    </a>
                                </div>
                            </section>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </main>
       
    </div>

    
    <script src="js/completion.js" defer></script>
</body>
</html>