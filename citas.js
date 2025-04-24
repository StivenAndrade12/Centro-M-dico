// Clase base abstracta para citas
class Cita {
    constructor(datos) {
        if (new.target === Cita) {
            throw new Error("No se puede instanciar la clase abstracta Cita");
        }
        this.id = Date.now();
        this.fechaCreacion = new Date();
        this.datos = datos;
    }

    // Método abstracto
    validar() {
        throw new Error("Método abstracto: debe ser implementado por subclases");
    }
}

// Implementación concreta para citas médicas
class CitaMedica extends Cita {
    validar() {
        if (!this.datos.paciente || !this.datos.fecha || !this.datos.especialidad) {
            throw new Error("Datos de cita médica incompletos");
        }
    }
}

// Gestor de citas (Singleton)
class GestorCitas {
    static instancia;

    constructor() {
        if (GestorCitas.instancia) {
            return GestorCitas.instancia;
        }

        this.citas = new Map();
        this.cargarCitas();
        GestorCitas.instancia = this;
    }

    cargarCitas() {
        try {
            const citasGuardadas = JSON.parse(localStorage.getItem('citasMedicas')) || [];
            citasGuardadas.forEach(cita => {
                this.citas.set(cita.id, cita);
            });
        } catch (error) {
            console.error("Error al cargar citas:", error);
        }
    }

    guardarCitas() {
        const citasArray = Array.from(this.citas.values());
        localStorage.setItem('citasMedicas', JSON.stringify(citasArray));
    }

    agregarCita(cita) {
        try {
            cita.validar();
            this.citas.set(cita.id, cita);
            this.guardarCitas();
            return cita;
        } catch (error) {
            console.error("Error al agregar cita:", error);
            throw error;
        }
    }

    eliminarCita(id) {
        if (!this.citas.has(id)) {
            throw new Error(`Cita con ID ${id} no encontrada`);
        }
        this.citas.delete(id);
        this.guardarCitas();
    }

    obtenerTodas() {
        return Array.from(this.citas.values());
    }

    obtenerPorId(id) {
        return this.citas.get(id);
    }
}

// Crear instancia global del gestor
window.gestorCitas = new GestorCitas();
