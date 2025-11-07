<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'config/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['startsWith' => [], 'contains' => []]);
    exit;
}

try {
    $pdo = db();
    
    $sqlExact = "SELECT id, nom_fr, nom_latin, categorie 
                 FROM animaux 
                 WHERE nom_fr LIKE :queryStart 
                    OR nom_latin LIKE :queryStart 
                 ORDER BY nom_fr ASC 
                 LIMIT 5";
    
    $stmtExact = $pdo->prepare($sqlExact);
    $stmtExact->execute([':queryStart' => $query . '%']);
    $exactResults = $stmtExact->fetchAll(PDO::FETCH_ASSOC);
    
    
    $sqlPartial = "SELECT id, nom_fr, nom_latin, categorie 
                   FROM animaux 
                   WHERE (nom_fr LIKE :queryContains OR nom_latin LIKE :queryContains)
                     AND nom_fr NOT LIKE :queryStart1 
                     AND nom_latin NOT LIKE :queryStart2
                   ORDER BY nom_fr ASC 
                   LIMIT 5";
    
    $stmtPartial = $pdo->prepare($sqlPartial);
    $stmtPartial->execute([
        ':queryContains' => '%' . $query . '%',
        ':queryStart1' => $query . '%',
        ':queryStart2' => $query . '%'
    ]);
    $partialResults = $stmtPartial->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'startsWith' => $exactResults,
        'contains' => $partialResults
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur autocomplétion: ' . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur']);
}