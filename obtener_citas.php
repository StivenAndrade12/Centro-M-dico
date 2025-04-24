<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$query = "SELECT id, nombre, apellido, documento, telefono, email, fecha, hora, tipo_consulta, motivo FROM citas ORDER BY fecha, hora";

$result = $conex->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las citas: ' . $conex->error]);
    exit;
}

$citas = [];

while ($row = $result->fetch_assoc()) {
    $citas[] = $row;
}

echo json_encode($citas);

$conex->close();
?>
