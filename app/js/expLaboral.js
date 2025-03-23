document.addEventListener('DOMContentLoaded', function() {
    const contenedorExperiencia = document.getElementById('contenedor-experiencia');
    const botonAgregar = document.getElementById('agregar-experiencia');

    let contadorClones = 1;

    // Instancia del modal (Bootstrap 5)
    const modalAdvertencia = new bootstrap.Modal(document.getElementById('modalAdvertencia'));

    botonAgregar.addEventListener('click', function() {
        const experiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');

        if (experiencias.length === 0) return; // Seguridad

        const ultimaExperiencia = experiencias[experiencias.length - 1];
        const campos = ultimaExperiencia.querySelectorAll('input, textarea');

        let camposCompletos = true;

        campos.forEach(input => {
            if (input.value.trim() === '') {
                camposCompletos = false;
            }
        });

        if (!camposCompletos) {
            // Muestra el modal si hay campos vacíos
            modalAdvertencia.show();
            return; // No continúa con el clon
        }

        // Clona la última experiencia que estaba agregada
        const nuevaExperiencia = ultimaExperiencia.cloneNode(true);

        // Limpia los campos del clon y les agrega el required
        const inputs = nuevaExperiencia.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.value = '';
            input.required = true; // 👈 Aquí le colocamos el required
        });

        // Agrega encabezado dinámico si no existe
        let encabezado = nuevaExperiencia.querySelector('.titulo-experiencia');
        if (!encabezado) {
            encabezado = document.createElement('h5');
            encabezado.classList.add('titulo-experiencia', 'mb-3');
            nuevaExperiencia.prepend(encabezado);
        }

        encabezado.textContent = `Nueva experiencia ${contadorClones + 1}`;

        // Aplica estilos para distinguir
        nuevaExperiencia.style.border = '2px dashed #007bff';
        nuevaExperiencia.style.padding = '15px';
        nuevaExperiencia.style.backgroundColor = '#f0f8ff';
        nuevaExperiencia.style.marginBottom = '15px';

        // Muestra el botón de eliminar
        const botonEliminar = nuevaExperiencia.querySelector('.eliminar-experiencia');
        botonEliminar.style.display = 'inline-block';

        nuevaExperiencia.style.display = 'block';

        contenedorExperiencia.appendChild(nuevaExperiencia);

        contadorClones++;
    });

    // Delegación de eventos para eliminar experiencia
    contenedorExperiencia.addEventListener('click', function(event) {
        if (event.target.classList.contains('eliminar-experiencia')) {
            const experienciaAEliminar = event.target.closest('.experiencia-laboral');
            experienciaAEliminar.remove();

            const experiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');

            if (experiencias.length > 1) {
                experiencias.forEach((exp, index) => {
                    exp.style.display = (index === experiencias.length - 1) ? 'block' : 'none';
                });

                const ultima = experiencias[experiencias.length - 1];
                const encabezado = ultima.querySelector('.titulo-experiencia');

                if (encabezado) {
                    encabezado.textContent = `Nueva experiencia ${experiencias.length - 1}`;
                    ultima.style.border = '2px dashed #df1f1f';
                    ultima.style.backgroundColor = '#f0f8ff';
                } else {
                    ultima.style.border = '';
                    ultima.style.backgroundColor = '';
                }

                contadorClones = experiencias.length;
            } else {
                experiencias[0].style.display = 'block';
                experiencias[0].style.border = '';
                experiencias[0].style.backgroundColor = '';
                contadorClones = 1;
            }
        }
    });

    // Ocultar el botón de eliminar en la primera entrada
    const primeraExperiencia = contenedorExperiencia.querySelector('.experiencia-laboral');
    if (primeraExperiencia) {
        primeraExperiencia.querySelector('.eliminar-experiencia').style.display = 'none';
    }
});
