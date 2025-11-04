<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config/db.php';   // pas de "/" initial

const TABLE_NAME   = 'animaux';
const COL_ID       = 'id';
const COL_NAME_FR  = 'nom_fr';

$q     = isset($_GET['query']) ? trim((string)$_GET['query']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$limit = max(1, min($limit, 25));

if ($q === '') {
  echo json_encode(['query'=>$q,'startsWith'=>[],'contains'=>[],'total'=>0], JSON_UNESCAPED_UNICODE);
  exit;
}


$likeEscaper = function(string $s): string {
  return str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s);
};
$qEsc = $likeEscaper($q);

$pdo = db(); 
$pdo->query('SET NAMES utf8mb4');

$sqlStarts = sprintf(
  'SELECT %1$s AS id, %2$s AS name
   FROM %3$s
   WHERE %2$s LIKE CONCAT(?, "%%") ESCAPE "\\\\"
   ORDER BY %2$s ASC
   LIMIT ?',
  COL_ID, COL_NAME_FR, TABLE_NAME
);

$sqlContains = sprintf(
  'SELECT %1$s AS id, %2$s AS name
   FROM %3$s
   WHERE %2$s LIKE CONCAT("%%", ?, "%%") ESCAPE "\\\\"
     AND %2$s NOT LIKE CONCAT(?, "%%") ESCAPE "\\\\"
   ORDER BY %2$s ASC
   LIMIT ?',
  COL_ID, COL_NAME_FR, TABLE_NAME
);

try {
  $st = $pdo->prepare($sqlStarts);
  $st->execute([$qEsc, $limit]);
  $starts = $st->fetchAll() ?: [];

  $sc = $pdo->prepare($sqlContains);
  $sc->execute([$qEsc, $qEsc, $limit]);
  $contains = $sc->fetchAll() ?: [];

  echo json_encode([
    'query'      => $q,
    'startsWith' => $starts,
    'contains'   => $contains,
    'total'      => count($starts) + count($contains)
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>'Erreur serveur','details'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
