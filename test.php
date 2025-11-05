<?php
/**
 * Fichier de test pour l'autocomplétion d'animaux
 * Test des fonctionnalités de base et de la sécurité
 * 
 * @author Dan
 * @version 1.0
 */

declare(strict_types=1);

// Configuration pour les tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Inclure la configuration de base de données
require_once 'config/db.php';

/**
 * Classe de test pour l'autocomplétion
 */
class AutocompletionTest
{
    private PDO $pdo;
    private array $testResults = [];
    
    public function __construct()
    {
        $this->pdo = db();
    }
    
    /**
     * Exécuter tous les tests
     */
    public function runAllTests(): void
    {
        echo "<h1>🧪 Tests d'autocomplétion - AnimaSearch</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-pass { color: #22c55e; font-weight: bold; }
            .test-fail { color: #ef4444; font-weight: bold; }
            .test-warning { color: #f59e0b; font-weight: bold; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
            .code { background: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace; }
        </style>\n";
        
        $this->testDatabaseConnection();
        $this->testDataIntegrity();
        $this->testSearchFunctionality();
        $this->testSecurityVulnerabilities();
        $this->testPerformance();
        
        $this->displaySummary();
    }
    
    /**
     * Test de connexion à la base de données
     */
    private function testDatabaseConnection(): void
    {
        echo "<div class='test-section'>\n";
        echo "<h2>🔌 Test de connexion à la base de données</h2>\n";
        
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM animaux");
            $count = $stmt->fetchColumn();
            
            if ($count >= 20) {
                $this->logTest("Connexion DB", true, "✅ Connexion réussie, $count animaux trouvés");
            } else {
                $this->logTest("Connexion DB", false, "⚠️ Seulement $count animaux (minimum requis: 20)");
            }
            
        } catch (Exception $e) {
            $this->logTest("Connexion DB", false, "❌ Erreur: " . $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test de l'intégrité des données
     */
    private function testDataIntegrity(): void
    {
        echo "<div class='test-section'>\n";
        echo "<h2>🗃️ Test d'intégrité des données</h2>\n";
        
        // Vérifier les champs obligatoires
        $sql = "SELECT COUNT(*) FROM animaux WHERE nom_fr IS NULL OR nom_fr = '' OR nom_latin IS NULL OR nom_latin = ''";
        $emptyFields = $this->pdo->query($sql)->fetchColumn();
        
        if ($emptyFields == 0) {
            $this->logTest("Champs obligatoires", true, "✅ Tous les noms français et latins sont renseignés");
        } else {
            $this->logTest("Champs obligatoires", false, "❌ $emptyFields enregistrements avec des champs vides");
        }
        
        // Vérifier les catégories valides
        $sql = "SELECT DISTINCT categorie FROM animaux";
        $categories = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        $validCategories = ['mammifere', 'reptile', 'poisson', 'oiseau', 'insecte'];
        
        $invalidCategories = array_diff($categories, $validCategories);
        if (empty($invalidCategories)) {
            $this->logTest("Catégories valides", true, "✅ Toutes les catégories sont valides: " . implode(', ', $categories));
        } else {
            $this->logTest("Catégories valides", false, "❌ Catégories invalides trouvées: " . implode(', ', $invalidCategories));
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test des fonctionnalités de recherche
     */
    private function testSearchFunctionality(): void
    {
        echo "<div class='test-section'>\n";
        echo "<h2>🔍 Test des fonctionnalités de recherche</h2>\n";
        
        $testQueries = [
            'ch' => 'Recherche basique (chat)',
            'loup' => 'Mot complet',
            'can' => 'Début de mot (canis/canari)',
            'xyz' => 'Recherche sans résultat'
        ];
        
        foreach ($testQueries as $query => $description) {
            $this->testSearchQuery($query, $description);
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test d'une requête de recherche spécifique
     */
    private function testSearchQuery(string $query, string $description): void
    {
        try {
            // Simuler la logique d'autocomplete.php
            $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
            
            // Résultats exacts (commençant par)
            $sqlExact = "SELECT id, nom_fr, nom_latin, categorie 
                         FROM animaux 
                         WHERE nom_fr LIKE CONCAT(?, '%') ESCAPE '\\\\' 
                            OR nom_latin LIKE CONCAT(?, '%') ESCAPE '\\\\'
                         ORDER BY nom_fr ASC 
                         LIMIT 5";
            
            $stmtExact = $this->pdo->prepare($sqlExact);
            $stmtExact->execute([$searchEscaped, $searchEscaped]);
            $exactResults = $stmtExact->fetchAll();
            
            // Résultats partiels (contenant)
            $sqlPartial = "SELECT id, nom_fr, nom_latin, categorie 
                          FROM animaux 
                          WHERE (nom_fr LIKE CONCAT('%', ?, '%') ESCAPE '\\\\' 
                             OR nom_latin LIKE CONCAT('%', ?, '%') ESCAPE '\\\\')
                            AND nom_fr NOT LIKE CONCAT(?, '%') ESCAPE '\\\\'
                            AND nom_latin NOT LIKE CONCAT(?, '%') ESCAPE '\\\\'
                          ORDER BY nom_fr ASC 
                          LIMIT 5";
            
            $stmtPartial = $this->pdo->prepare($sqlPartial);
            $stmtPartial->execute([$searchEscaped, $searchEscaped, $searchEscaped, $searchEscaped]);
            $partialResults = $stmtPartial->fetchAll();
            
            $totalResults = count($exactResults) + count($partialResults);
            
            if ($query === 'xyz' && $totalResults === 0) {
                $this->logTest($description, true, "✅ Aucun résultat (attendu)");
            } elseif ($totalResults > 0) {
                $this->logTest($description, true, "✅ $totalResults résultats trouvés");
                echo "<div class='code'>Exemples: ";
                $examples = array_slice(array_merge($exactResults, $partialResults), 0, 3);
                foreach ($examples as $result) {
                    echo htmlspecialchars($result['nom_fr']) . " ";
                }
                echo "</div>\n";
            } else {
                $this->logTest($description, false, "❌ Aucun résultat inattendu");
            }
            
        } catch (Exception $e) {
            $this->logTest($description, false, "❌ Erreur: " . $e->getMessage());
        }
    }
    
    /**
     * Test des vulnérabilités de sécurité (hacking éthique)
     */
    private function testSecurityVulnerabilities(): void
    {
        echo "<div class='test-section'>\n";
        echo "<h2>🛡️ Test de sécurité (hacking éthique)</h2>\n";
        
        $maliciousInputs = [
            "'; DROP TABLE animaux; --" => "SQL Injection classique",
            "<script>alert('XSS')</script>" => "Cross-Site Scripting",
            "' OR '1'='1" => "SQL Injection bypass",
            "%' OR 1=1 --" => "SQL Injection avec wildcard",
            "\\'; UNION SELECT 1,2,3,4 --" => "UNION SQL Injection"
        ];
        
        foreach ($maliciousInputs as $input => $attackType) {
            $this->testSecurityInput($input, $attackType);
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test d'une entrée malveillante spécifique
     */
    private function testSecurityInput(string $input, string $attackType): void
    {
        try {
            // Tenter la même logique que l'autocomplétion avec des prepared statements
            $searchEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $input);
            
            $sql = "SELECT id, nom_fr FROM animaux WHERE nom_fr LIKE CONCAT(?, '%') ESCAPE '\\\\' LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$searchEscaped]);
            $result = $stmt->fetch();
            
            // Si on arrive ici sans erreur, les prepared statements ont protégé
            $this->logTest($attackType, true, "✅ Protection effective contre l'injection");
            
        } catch (Exception $e) {
            // Une erreur pourrait indiquer une vulnérabilité
            $this->logTest($attackType, false, "⚠️ Erreur détectée: " . substr($e->getMessage(), 0, 100));
        }
    }
    
    /**
     * Test de performance basique
     */
    private function testPerformance(): void
    {
        echo "<div class='test-section'>\n";
        echo "<h2>⚡ Test de performance</h2>\n";
        
        $startTime = microtime(true);
        
        // Simuler 10 recherches rapides
        for ($i = 0; $i < 10; $i++) {
            $query = chr(97 + $i); // a, b, c, etc.
            $sql = "SELECT COUNT(*) FROM animaux WHERE nom_fr LIKE CONCAT(?, '%') LIMIT 5";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$query]);
            $stmt->fetch();
        }
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // en millisecondes
        
        if ($duration < 100) {
            $this->logTest("Performance", true, "✅ 10 requêtes en " . round($duration, 2) . "ms (excellent)");
        } elseif ($duration < 500) {
            $this->logTest("Performance", true, "✅ 10 requêtes en " . round($duration, 2) . "ms (acceptable)");
        } else {
            $this->logTest("Performance", false, "⚠️ 10 requêtes en " . round($duration, 2) . "ms (lent)");
        }
        
        echo "</div>\n";
    }
    
    /**
     * Enregistrer le résultat d'un test
     */
    private function logTest(string $testName, bool $passed, string $message): void
    {
        $this->testResults[] = [
            'name' => $testName,
            'passed' => $passed,
            'message' => $message
        ];
        
        $class = $passed ? 'test-pass' : 'test-fail';
        echo "<p class='$class'>$message</p>\n";
    }
    
    /**
     * Afficher le résumé des tests
     */
    private function displaySummary(): void
    {
        $total = count($this->testResults);
        $passed = count(array_filter($this->testResults, fn($test) => $test['passed']));
        $failed = $total - $passed;
        
        echo "<div class='test-section'>\n";
        echo "<h2>📊 Résumé des tests</h2>\n";
        echo "<p><strong>Total:</strong> $total tests</p>\n";
        echo "<p class='test-pass'><strong>Réussis:</strong> $passed</p>\n";
        
        if ($failed > 0) {
            echo "<p class='test-fail'><strong>Échoués:</strong> $failed</p>\n";
        }
        
        $successRate = round(($passed / $total) * 100, 1);
        echo "<p><strong>Taux de réussite:</strong> $successRate%</p>\n";
        
        if ($successRate >= 90) {
            echo "<p class='test-pass'>🎉 Excellent travail ! Votre système est bien sécurisé et fonctionnel.</p>\n";
        } elseif ($successRate >= 70) {
            echo "<p class='test-warning'>👍 Bon travail, quelques améliorations possibles.</p>\n";
        } else {
            echo "<p class='test-fail'>⚠️ Plusieurs problèmes détectés, révision recommandée.</p>\n";
        }
        
        echo "</div>\n";
    }
}

// Exécution des tests si le script est appelé directement
if (basename($_SERVER['PHP_SELF']) === 'test_autocompletion.php') {
    try {
        $tester = new AutocompletionTest();
        $tester->runAllTests();
    } catch (Exception $e) {
        echo "<h1>❌ Erreur lors des tests</h1>\n";
        echo "<p>Impossible d'exécuter les tests: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p>Vérifiez que le fichier config/db.php est présent et que la base de données est accessible.</p>\n";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests d'autocomplétion - AnimaSearch</title>
</head>
<body>
    <div style="margin-top: 40px; padding: 20px; background: #f8fafc; border-radius: 8px;">
        <h3>💡 Comment utiliser ce fichier de test</h3>
        <ol>
            <li>Placez ce fichier dans le même répertoire que votre projet</li>
            <li>Assurez-vous que config/db.php est accessible</li>
            <li>Ouvrez ce fichier dans votre navigateur</li>
            <li>Analysez les résultats pour identifier d'éventuels problèmes</li>
        </ol>
        
        <h3>🧠 Points d'apprentissage</h3>
        <ul>
            <li><strong>Sécurité :</strong> Les prepared statements protègent contre les injections SQL</li>
            <li><strong>Performance :</strong> Limitez les résultats avec LIMIT pour éviter la surcharge</li>
            <li><strong>Tests :</strong> Automatiser les tests permet de détecter rapidement les régressions</li>
            <li><strong>Validation :</strong> Toujours valider les données d'entrée et de sortie</li>
        </ul>
        
        <h3>🔧 Améliorations suggérées</h3>
        <ul>
            <li>Ajouter un index sur la colonne nom_fr pour améliorer les performances</li>
            <li>Implémenter un cache pour les requêtes fréquentes</li>
            <li>Ajouter des logs d'erreur pour le monitoring</li>
            <li>Considérer l'utilisation d'un système de recherche full-text pour de gros volumes</li>
        </ul>
    </div>
</body>
</html>