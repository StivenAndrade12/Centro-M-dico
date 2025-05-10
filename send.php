<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once 'conexion.php';

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función para enviar correo de confirmación
function enviarCorreoConfirmacion($email, $nombre, $fecha, $hora) {
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambiar si se usa otro servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'wilsonstivenand12@gmail.com'; // Cambiar por tu correo
        $mail->Password = 'yhaz dids yjcf bsuj'; // Cambiar por tu contraseña de aplicación
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('tu_correo@gmail.com', 'Centro Médico');
        $mail->addAddress($email, $nombre);

        // Contenido
        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirmación de cita médica - Centro Médico';
        $mail->Body = "Hola $nombre,\n\nTu cita médica ha sido agendada para el día $fecha a las $hora.\n\nGracias por confiar en nosotros.\n\nCentro Médico Las Américas";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Recibir y sanitizar datos
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
$apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_SPECIAL_CHARS);
$documento = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_SPECIAL_CHARS);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_SPECIAL_CHARS);
$hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_SPECIAL_CHARS);
$tipo_consulta = filter_input(INPUT_POST, 'tipo_consulta', FILTER_SANITIZE_SPECIAL_CHARS);
$motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS);

// Validar campos obligatorios
$campos = ['nombre' => $nombre, 'apellido' => $apellido, 'documento' => $documento, 'telefono' => $telefono, 'email' => $email, 'fecha' => $fecha, 'hora' => $hora, 'tipo_consulta' => $tipo_consulta];
foreach ($campos as $campo => $valor) {
    if (empty($valor)) {
        http_response_code(400);
        echo json_encode(['error' => "El campo $campo es requerido"]);
        exit;
    }
}

$validarHorarioQuery = "SELECT COUNT(*) as count FROM horarios_disponibles WHERE doctor = ? AND fecha = ? AND hora = ?";
$validarStmt = $conex->prepare($validarHorarioQuery);
$validarStmt->bind_param("sss", $tipo_consulta, $fecha, $hora);
$validarStmt->execute();
$validarResult = $validarStmt->get_result();
$validarRow = $validarResult->fetch_assoc();

$validarStmt->close();

// Validar horario por defecto si no está en la tabla horarios_disponibles
if ($validarRow['count'] == 0) {
    $diaSemana = date('N', strtotime($fecha)); // 1=lunes, 7=domingo
    $horaTime = strtotime($hora);

    $horariosDiaLaboral = [];
    for ($h = 6; $h <= 20; $h++) {
        $horariosDiaLaboral[] = strtotime(sprintf('%02d:00:00', $h));
    }

    $horariosDomingo = [];
    for ($h = 10; $h <= 16; $h++) {
        $horariosDomingo[] = strtotime(sprintf('%02d:00:00', $h));
    }

    $horarioValido = false;
    if ($diaSemana >= 1 && $diaSemana <= 6) { // Lunes a sábado
        if (in_array($horaTime, $horariosDiaLaboral)) {
            $horarioValido = true;
        }
    } elseif ($diaSemana == 7) { // Domingo
        if (in_array($horaTime, $horariosDomingo)) {
            $horarioValido = true;
        }
    }

    if (!$horarioValido) {
        http_response_code(400);
        echo json_encode(['error' => 'El horario seleccionado no está disponible']);
        exit;
    }
}


// Insertar en base de datos
$stmt = $conex->prepare("INSERT INTO citas (nombre, apellido, documento, telefono, email, fecha, hora, tipo_consulta, motivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conex->error]);
    exit;
}
$stmt->bind_param("sssssssss", $nombre, $apellido, $documento, $telefono, $email, $fecha, $hora, $tipo_consulta, $motivo);

if ($stmt->execute()) {
    // Enviar correo de confirmación
    $nombreCompleto = $nombre . ' ' . $apellido;
    $correoEnviado = enviarCorreoConfirmacion($email, $nombreCompleto, $fecha, $hora);

    echo json_encode([
        'success' => 'Cita agendada exitosamente',
        'correo_enviado' => $correoEnviado
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la cita en la base de datos: ' . $stmt->error]);
}

$stmt->close();
$conex->close();
?>
