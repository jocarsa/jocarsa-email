<?php
header('Content-Type: application/json');
$folder = $_GET['folder'] ?? '';
$base = __DIR__ . '/../mail';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['file'])) {
        $file = basename($_GET['file']);
        $path = "$base/$folder/$file";
        if (!in_array($folder, ['incoming','spam']) || !file_exists($path)) {
            http_response_code(404);
            echo json_encode(['error'=>'No encontrado']); exit;
        }
        $data = json_decode(file_get_contents($path), true);
        echo json_encode($data);
    } else {
        if (!in_array($folder, ['incoming','spam'])) { echo json_encode([]); exit; }
        $files = array_map('basename', glob("$base/$folder/*.json"));
        rsort($files);
        echo json_encode($files);
    }
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $file = basename($_GET['file'] ?? '');
    $path = "$base/$folder/$file";
    if (file_exists($path)) { unlink($path); echo json_encode(['message'=>'Correo eliminado']); }
    else { http_response_code(404); echo json_encode(['message'=>'No encontrado']); }
    exit;
}
?>
