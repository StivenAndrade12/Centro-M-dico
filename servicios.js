document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCita');
    const fechaInput = document.getElementById('fecha');
    const horaSelect = document.getElementById('hora');
    const tipoConsultaSelect = document.getElementById('tipo_consulta');

    if (!form || !fechaInput || !horaSelect || !tipoConsultaSelect) return;

    // Inicialmente deshabilitar fecha y hora
    fechaInput.disabled = true;
    horaSelect.disabled = true;

    async function cargarHorariosDisponibles() {
        const fecha = fechaInput.value;
        const doctor = tipoConsultaSelect.value; // Usamos tipo_consulta como doctor para simplificar

        if (!fecha || !doctor) {
            horaSelect.innerHTML = '<option value="">Seleccione un horario</option>';
            horaSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`obtener_horarios.php?doctor=${encodeURIComponent(doctor)}&fecha=${encodeURIComponent(fecha)}`);
            if (!response.ok) {
                throw new Error('Error al obtener horarios disponibles');
            }
            const horarios = await response.json();

            if (horarios.length === 0) {
                horaSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                horaSelect.disabled = true;
            } else {
                horaSelect.innerHTML = horarios.map(hora => `<option value="${hora}">${hora}</option>`).join('');
                horaSelect.disabled = false;
            }
        } catch (error) {
            horaSelect.innerHTML = `<option value="">Error al cargar horarios</option>`;
            horaSelect.disabled = true;
        }
    }

    tipoConsultaSelect.addEventListener('change', () => {
        if (tipoConsultaSelect.value) {
            fechaInput.disabled = false;
        } else {
            fechaInput.disabled = true;
            horaSelect.disabled = true;
            horaSelect.innerHTML = '<option value="">Seleccione un horario</option>';
        }
    });

    fechaInput.addEventListener('change', cargarHorariosDisponibles);

    // No cargar horarios al cargar la página porque fecha está deshabilitada inicialmente

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        try {
            const response = await fetch('send.php', {
                method: 'POST',
                body: new URLSearchParams(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(result.success + (result.correo_enviado ? ' Se envió el correo de confirmación.' : ' No se pudo enviar el correo.'));
                form.reset();
                fechaInput.disabled = true;
                horaSelect.innerHTML = '<option value="">Seleccione un horario</option>';
                horaSelect.disabled = true;
            } else {
                alert('Error: ' + (result.error || 'Error desconocido'));
            }
        } catch (error) {
            alert('Error al enviar la solicitud: ' + error.message);
        }
    });
});
