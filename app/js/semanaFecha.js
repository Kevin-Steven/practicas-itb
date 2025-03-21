document.addEventListener('DOMContentLoaded', function() {
    const contenedorActividades = document.getElementById('contenedor-semana');
    const botonAgregarSemana = document.getElementById('agregar-semana');
    const modalCamposIncompletos = new bootstrap.Modal(document.getElementById('modalCamposIncompletos'));

    let contadorSemanas = 1;

    botonAgregarSemana.addEventListener('click', function() {
        const semanas = contenedorActividades.querySelectorAll('.semana');
        const ultimaSemana = semanas[semanas.length - 1];

        // ✅ Verificar si los campos de la última semana están completos antes de clonar
        const inputsUltimaSemana = ultimaSemana.querySelectorAll('input, textarea');
        let camposCompletos = true;

        inputsUltimaSemana.forEach(input => {
            if (input.value.trim() === '') {
                camposCompletos = false;
            }
        });

        if (!camposCompletos) {
            modalCamposIncompletos.show();  // ✅ Muestra el modal si hay campos incompletos
            return; // ✅ Evita agregar otra semana
        }

        // ✅ Ocultar todas las semanas existentes
        semanas.forEach(semana => semana.style.display = 'none');

        // ✅ Clona la última semana
        const nuevaSemana = ultimaSemana.cloneNode(true);

        // ✅ Limpia los campos del clon
        const inputsNuevaSemana = nuevaSemana.querySelectorAll('input, textarea');
        inputsNuevaSemana.forEach(input => input.value = '');

        // ✅ Agregar encabezado dinámico si no existe
        let encabezado = nuevaSemana.querySelector('.titulo-semana');
        if (!encabezado) {
            encabezado = document.createElement('h5');
            encabezado.classList.add('titulo-semana', 'mb-3');
            nuevaSemana.prepend(encabezado);
        }
        encabezado.textContent = `Semana ${contadorSemanas + 1}`;

        // ✅ Aplica estilos visuales al clon
        nuevaSemana.style.border = '2px dashed #007bff';
        nuevaSemana.style.padding = '15px';
        nuevaSemana.style.backgroundColor = '#f0f8ff';
        nuevaSemana.style.marginBottom = '15px';

        // ✅ Muestra el botón de eliminar en el clon
        const botonEliminar = nuevaSemana.querySelector('.eliminar-semana');
        botonEliminar.style.display = 'inline-block';

        nuevaSemana.style.display = 'block';

        contenedorActividades.appendChild(nuevaSemana);

        contadorSemanas++;
    });

    // ✅ Delegación de eventos para eliminar una semana
    contenedorActividades.addEventListener('click', function(event) {
        if (event.target.classList.contains('eliminar-semana')) {
            const semanaAEliminar = event.target.closest('.semana');
            semanaAEliminar.remove();

            const semanas = contenedorActividades.querySelectorAll('.semana');

            if (semanas.length > 1) {
                // ✅ Oculta todas menos la última
                semanas.forEach((semana, index) => {
                    semana.style.display = (index === semanas.length - 1) ? 'block' : 'none';
                });

                // ✅ Actualiza el encabezado y estilos
                const ultima = semanas[semanas.length - 1];
                const encabezado = ultima.querySelector('.titulo-semana');
                if (encabezado) {
                    encabezado.textContent = `Semana ${semanas.length}`;
                    ultima.style.border = '2px dashed #007bff';
                    ultima.style.backgroundColor = '#f0f8ff';
                } else {
                    ultima.style.border = '';
                    ultima.style.backgroundColor = '';
                }

                contadorSemanas = semanas.length;
            } else {
                // ✅ Si solo queda una semana
                semanas[0].style.display = 'block';
                semanas[0].style.border = '';
                semanas[0].style.backgroundColor = '';
                contadorSemanas = 1;
            }
        }
    });

    // ✅ Oculta el botón de eliminar en la primera semana
    const primeraSemana = contenedorActividades.querySelector('.semana');
    if (primeraSemana) {
        primeraSemana.querySelector('.eliminar-semana').style.display = 'none';
    }
});
