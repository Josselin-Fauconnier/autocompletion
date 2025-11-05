<?php

require_once 'config/db.php';


$query = isset($_GET['q']) ? trim($_GET['q']) : '';


if (strlen($query) < 2) {
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
    $stmtExact->bindValue(':queryStart', $query . '%');
    $stmtExact->execute();
    $exactResults = $stmtExact->fetchAll();
    
    $sqlPartial = "SELECT id, nom_fr, nom_latin, categorie 
                   FROM animaux 
                   WHERE (nom_fr LIKE :queryPartial OR nom_latin LIKE :queryPartial)
                     AND nom_fr NOT LIKE :queryStart 
                     AND nom_latin NOT LIKE :queryStart
                   ORDER BY nom_fr ASC 
                   LIMIT 8";
    
    $stmtPartial = $pdo->prepare($sqlPartial);
    $stmtPartial->bindValue(':queryPartial', '%' . $query . '%');
    $stmtPartial->bindValue(':queryStart', $query . '%');
    $stmtPartial->execute();
    $partialResults = $stmtPartial->fetchAll();
    
    foreach ($exactResults as $animal) {
        echo '<div data-animal-id="' . $animal['id'] . '" ';
        echo 'data-animal-name="' . htmlspecialchars($animal['nom_fr']) . '" ';
        echo 'data-animal-latin="' . htmlspecialchars($animal['nom_latin']) . '" ';
        echo 'data-animal-category="' . htmlspecialchars($animal['categorie']) . '" ';
        echo 'data-animal-exact="true"></div>';
    }
    
    
    foreach ($partialResults as $animal) {
        echo '<div data-animal-id="' . $animal['id'] . '" ';
        echo 'data-animal-name="' . htmlspecialchars($animal['nom_fr']) . '" ';
        echo 'data-animal-latin="' . htmlspecialchars($animal['nom_latin']) . '" ';
        echo 'data-animal-category="' . htmlspecialchars($animal['categorie']) . '" ';
        echo 'data-animal-exact="false"></div>';
    }
    
} catch (PDOException $e) {
    error_log('Erreur SQL autocomplétion: ' . $e->getMessage());
}
?>