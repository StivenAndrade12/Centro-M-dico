<?php
header('Content-Type: application/json');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cita inválido']);
    exit;
}

$stmt = $conex->prepare("DELETE FROM citas WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conex->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Cita eliminada correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar la cita: ' . $stmt->error]);
}

$stmt->close();
$conex->close();
?>
