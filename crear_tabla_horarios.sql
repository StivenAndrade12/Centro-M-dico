-- Script para crear la tabla de horarios disponibles por doctor
CREATE TABLE IF NOT EXISTS horarios_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    UNIQUE KEY unique_horario (doctor, fecha, hora)
);
