document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCita');
    if (!form) return;

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
            } else {
                alert('Error: ' + (result.error || 'Error desconocido'));
            }
        } catch (error) {
            alert('Error al enviar la solicitud: ' + error.message);
        }
    });
});
