<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$doctor = isset($_GET['doctor']) ? $_GET['doctor'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;

if (!$doctor || !$fecha) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros doctor y fecha son requeridos']);
    exit;
}

// Obtener día de la semana (1=lunes, 7=domingo)
$diaSemana = date('N', strtotime($fecha));

$query = "SELECT hora FROM horarios_disponibles WHERE doctor = ? AND fecha = ? ORDER BY hora";
$stmt = $conex->prepare($query);
$stmt->bind_param("ss", $doctor, $fecha);
$stmt->execute();
$result = $stmt->get_result();

$horarios = [];
while ($row = $result->fetch_assoc()) {
    $horarios[] = $row['hora'];
}

$stmt->close();
$conex->close();

    if (count($horarios) === 0) {
        // Si no hay horarios disponibles, devolver horarios por defecto según día
        if ($diaSemana >= 1 && $diaSemana <= 6) { // Lunes a sábado
            $horarios = [];
            for ($h = 6; $h <= 20; $h++) {
                $horarios[] = sprintf('%02d:00:00', $h);
            }
        } elseif ($diaSemana == 7) { // Domingo
            $horarios = [];
            for ($h = 10; $h <= 16; $h++) {
                $horarios[] = sprintf('%02d:00:00', $h);
            }
        }
    }

echo json_encode($horarios);
?>
