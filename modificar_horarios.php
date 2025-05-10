<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener horarios disponibles para doctor y fecha
    $doctor = isset($_GET['doctor']) ? $_GET['doctor'] : null;
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;

    if (!$doctor || !$fecha) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros doctor y fecha son requeridos']);
        exit;
    }

    $query = "SELECT id, hora FROM horarios_disponibles WHERE doctor = ? AND fecha = ? ORDER BY hora";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("ss", $doctor, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();

    $horarios = [];
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row;
    }

    echo json_encode($horarios);

    $stmt->close();
    $conex->close();
} elseif ($method === 'POST') {
    // Agregar o modificar horario disponible
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['doctor'], $data['fecha'], $data['hora'])) {
        http_response_code(400);
        echo json_encode(['error' => 'doctor, fecha y hora son requeridos']);
        exit;
    }

    $doctor = $data['doctor'];
    $fecha = $data['fecha'];
    $hora = $data['hora'];

    // Verificar si el horario ya existe
    $query = "SELECT id FROM horarios_disponibles WHERE doctor = ? AND fecha = ? AND hora = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("sss", $doctor, $fecha, $hora);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Ya existe, no insertar duplicado
        echo json_encode(['message' => 'El horario ya existe']);
    } else {
        $stmt->close();
        $query = "INSERT INTO horarios_disponibles (doctor, fecha, hora) VALUES (?, ?, ?)";
        $stmt = $conex->prepare($query);
        $stmt->bind_param("sss", $doctor, $fecha, $hora);
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Horario agregado exitosamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al agregar horario: ' . $stmt->error]);
        }
    }

    $stmt->close();
    $conex->close();
} elseif ($method === 'DELETE') {
    // Eliminar horario disponible
    parse_str(file_get_contents("php://input"), $delete_vars);

    if (!isset($delete_vars['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es requerido para eliminar']);
        exit;
    }

    $id = $delete_vars['id'];

    $query = "DELETE FROM horarios_disponibles WHERE id = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Horario eliminado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar horario: ' . $stmt->error]);
    }

    $stmt->close();
    $conex->close();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
