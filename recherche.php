<?php
declare(strict_types=1);

require_once 'config/db.php';

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

const RESULTS_PER_PAGE = 5;
const TABLE_NAME = 'animaux';

$results = [];
$totalResults = 0;
$errorMessage = '';

if (strlen($search) >= 2) {
    try {
        $pdo = db();
        $pdo->query('SET NAMES utf8mb4');
        
        $searchLike = '%' . $search . '%';
        $searchStartsWith = $search . '%';
        
        $offset = ($page - 1) * RESULTS_PER_PAGE;
        
        
        $countSql = 'SELECT COUNT(*) as total FROM ' . TABLE_NAME . ' 
                     WHERE nom_fr LIKE ? OR nom_latin LIKE ?';
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([$searchLike, $searchLike]);
        $totalResults = (int)$countStmt->fetch()['total'];
        
       
        $searchSql = 'SELECT id, nom_fr, nom_latin, categorie 
                      FROM ' . TABLE_NAME . ' 
                      WHERE nom_fr LIKE ? OR nom_latin LIKE ?
                      ORDER BY 
                        CASE 
                          WHEN nom_fr LIKE ? THEN 1
                          WHEN nom_latin LIKE ? THEN 2
                          ELSE 3 
                        END,
                        nom_fr ASC
                      LIMIT ' . RESULTS_PER_PAGE . ' OFFSET ' . $offset;
        
        $searchStmt = $pdo->prepare($searchSql);
        $searchStmt->execute([
            $searchLike, $searchLike, 
            $searchStartsWith, $searchStartsWith
        ]);
        
        $results = $searchStmt->fetchAll();
        
    } catch (Exception $e) {
        $errorMessage = 'Erreur lors de la recherche. Veuillez réessayer.';
        error_log('Erreur recherche: ' . $e->getMessage());
        echo "<!-- Debug: " . $e->getMessage() . " -->";
    }
}

$totalPages = $totalResults > 0 ? ceil($totalResults / RESULTS_PER_PAGE) : 0;
$hasResults = !empty($results);
$isValidSearch = strlen($search) >= 2;

function escapeHtml(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function highlightSearch(string $text, string $search): string {
    if (empty($search)) return escapeHtml($text);
    
    $escaped = escapeHtml($text);
    $searchEscaped = escapeHtml($search);
    
    return preg_replace(
        '/(' . preg_quote($searchEscaped, '/') . ')/i',
        '<mark>$1</mark>',
        $escaped
    );
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Résultats de recherche pour <?= escapeHtml($search) ?>">
    <title><?= $search ? 'Résultats pour "' . escapeHtml($search) . '"' : 'Recherche' ?> - Le dico des animaux</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
         <div class="container">
        <header class="header">
            <nav class="nav-secondary">
                <button id="theme-toggle" type="button" class="nav-link" aria-label="Basculer le thème">
                    <span id="theme-icon" aria-hidden="true">🐣</span>
                </button>
            </nav>
            
            <div class="search-header">
                <form action="recherche.php" method="GET" class="search-form" role="search">
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
                    
                    <div id="search-hint-header" class="sr-only">
                        Tapez au moins 2 caractères pour voir les suggestions.
                    </div>

                    <div 
                        id="suggestions-container-header" 
                        class="suggestions-container"
                        role="listbox"
                        aria-label="Suggestions de recherche"
                    >
                        <ul id="suggestions-list-header" class="suggestions-list">
                        </ul>
                    </div>
                </form>
            </div>
        </header>

        <main class="main-content">
            <div class="results-container">
                
                <?php if (!$isValidSearch): ?>
                    <div class="search-info">
                        <h1>Recherche d'animaux</h1>
                        <p>Veuillez saisir au moins 2 caractères pour effectuer une recherche.</p>
                    </div>
                    
                <?php elseif ($errorMessage): ?>
                    <div class="error-message">
                        <h1>Erreur</h1>
                        <p><?= escapeHtml($errorMessage) ?></p>
                    </div>
                    
                <?php elseif (!$hasResults): ?>
                    <div class="no-results">
                        <h1>Aucun résultat trouvé</h1>
                        <p>Aucun animal ne correspond à votre recherche "<strong><?= escapeHtml($search) ?></strong>".</p>
                        <div class="search-suggestions">
                            <h2>Suggestions :</h2>
                            <ul>
                                <li>Vérifiez l'orthographe de votre recherche</li>
                                <li>Essayez des termes plus généraux</li>
                                <li>Essayez des synonymes</li>
                            </ul>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="results-header">
                        <h1>Résultats de recherche</h1>
                        <p class="results-meta">
                            <strong><?= $totalResults ?></strong> résultat<?= $totalResults > 1 ? 's' : '' ?> 
                            trouvé<?= $totalResults > 1 ? 's' : '' ?> pour 
                            "<strong><?= escapeHtml($search) ?></strong>"
                            <?php if ($totalPages > 1): ?>
                                (page <?= $page ?> sur <?= $totalPages ?>)
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="results-list">
                        <?php foreach ($results as $result): ?>
                            <article class="result-item">
                                <a href="element.php?id=<?= (int)$result['id'] ?>" class="result-link">
                                    <div class="result-content">
                                        <h2 class="result-title">
                                            <?= highlightSearch($result['nom_fr'], $search) ?>
                                        </h2>
                                        <p class="result-latin">
                                            <em><?= highlightSearch($result['nom_latin'], $search) ?></em>
                                        </p>
                                        
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination" aria-label="Navigation par pages">
                            <ul class="pagination-list">
                                
                                <?php if ($page > 1): ?>
                                    <li class="pagination-item">
                                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" 
                                           class="pagination-link" aria-label="Page précédente">
                                            ← Précédent
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="pagination-item">
                                        <?php if ($i === $page): ?>
                                            <span class="pagination-link pagination-current" aria-current="page">
                                                <?= $i ?>
                                            </span>
                                        <?php else: ?>
                                            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" 
                                               class="pagination-link">
                                                <?= $i ?>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="pagination-item">
                                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" 
                                           class="pagination-link" aria-label="Page suivante">
                                            Suivant →
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
    <script src="js/completion.js" defer></script>
</body>
</html>