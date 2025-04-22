<?php
header('Content-Type: application/json');
$spamFile = __DIR__ . '/../spamfilter.txt';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['index'])) {
        $i = intval($_GET['index']);
        $words = file_exists($spamFile)?file($spamFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];
        if (isset($words[$i])) echo json_encode($words[$i]);
        else { http_response_code(404); echo json_encode(['error'=>'No encontrado']); }
    } else {
        $words = file_exists($spamFile)?file($spamFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];
        echo json_encode($words);
    }
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $w = trim($input['word'] ?? '');
    if ($w==='') { http_response_code(400); echo json_encode(['message'=>'Vacío']); exit; }
    $words = file_exists($spamFile)?file($spamFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];
    if (in_array($w,$words)) { http_response_code(409); echo json_encode(['message'=>'Ya existe']); exit; }
    $words[]=$w; file_put_contents($spamFile, implode("\n",$words)."\n");
    echo json_encode(['message'=>'Agregado']); exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $i = intval($_GET['index'] ?? -1);
    $w2 = trim($input['word'] ?? '');
    $words = file_exists($spamFile)?file($spamFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];
    if (!isset($words[$i]) || $w2==='') { http_response_code(400); echo json_encode(['message'=>'Error']); exit; }
    $words[$i] = $w2; file_put_contents($spamFile, implode("\n",$words)."\n");
    echo json_encode(['message'=>'Actualizado']); exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $i = intval($_GET['index'] ?? -1);
    $words = file_exists($spamFile)?file($spamFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES):[];
    if (!isset($words[$i])) { http_response_code(404); echo json_encode(['message'=>'No encontrado']); exit; }
    array_splice($words, $i, 1);
    file_put_contents($spamFile, implode("\n",$words)."\n");
    echo json_encode(['message'=>'Eliminado']); exit;
}
?>
