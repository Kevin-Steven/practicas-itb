document.addEventListener('DOMContentLoaded', function() {
    const contenedorActividades = document.getElementById('contenedor-semana');
    const botonAgregarSemana = document.getElementById('agregar-semana');
    const modalCamposIncompletos = new bootstrap.Modal(document.getElementById('modalCamposIncompletos'));

    let contadorSemanas = contenedorActividades.querySelectorAll('.semana').length || 1;

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

        // ✅ Clona la última semana
        const nuevaSemana = ultimaSemana.cloneNode(true);

        // ✅ Limpia los campos específicos (fechas y demás)
        const inputFechaInicio = nuevaSemana.querySelector('input[name="semana_inicio[]"]');
        const inputFechaFin = nuevaSemana.querySelector('input[name="semana_fin[]"]');
        const inputHorasRealizadas = nuevaSemana.querySelector('input[name="horas_realizadas[]"]');
        const textareaActividades = nuevaSemana.querySelector('textarea[name="actividades_realizadas[]"]');

        if (inputFechaInicio) inputFechaInicio.value = '';
        if (inputFechaFin) inputFechaFin.value = '';
        if (inputHorasRealizadas) inputHorasRealizadas.value = '';
        if (textareaActividades) textareaActividades.value = '';

        // ✅ Agregar encabezado dinámico o actualizarlo
        let encabezado = nuevaSemana.querySelector('.titulo-semana');
        if (!encabezado) {
            encabezado = document.createElement('h5');
            encabezado.classList.add('titulo-semana', 'mb-3');
            nuevaSemana.prepend(encabezado);
        }
        contadorSemanas++;
        encabezado.textContent = `Semana ${contadorSemanas}`;

        // ✅ Mostrar el botón de eliminar en el clon
        const botonEliminar = nuevaSemana.querySelector('.eliminar-semana');
        botonEliminar.style.display = 'inline-block';

        // ✅ Aplica estilos visuales al clon
        nuevaSemana.style.border = '2px dashed #007bff';
        nuevaSemana.style.padding = '15px';
        nuevaSemana.style.backgroundColor = '#f0f8ff';
        nuevaSemana.style.marginBottom = '15px';

        // ✅ Agregar el clon al final del contenedor (debajo de todo)
        contenedorActividades.appendChild(nuevaSemana);
    });

    // ✅ Delegación de eventos para eliminar una semana
    contenedorActividades.addEventListener('click', function(event) {
        if (event.target.classList.contains('eliminar-semana')) {
            const semanaAEliminar = event.target.closest('.semana');
            semanaAEliminar.remove();

            const semanas = contenedorActividades.querySelectorAll('.semana');

            // ✅ Recalcular contador de semanas y actualizar encabezados
            contadorSemanas = semanas.length;

            semanas.forEach((semana, index) => {
                const encabezado = semana.querySelector('.titulo-semana');
                if (encabezado) {
                    encabezado.textContent = `Semana ${index + 1}`;
                }

                if (index === 0) {
                    // ✅ Oculta el botón eliminar de la primera semana
                    const btnEliminar = semana.querySelector('.eliminar-semana');
                    btnEliminar.style.display = 'none';
                    semana.style.border = '';
                    semana.style.backgroundColor = '';
                } else {
                    // ✅ Estilos para las semanas restantes
                    semana.style.border = '2px dashed #007bff';
                    semana.style.backgroundColor = '#f0f8ff';
                }
            });
        }
    });

    // ✅ Inicial: Oculta el botón de eliminar en la primera semana
    const primeraSemana = contenedorActividades.querySelector('.semana');
    if (primeraSemana) {
        const btnEliminar = primeraSemana.querySelector('.eliminar-semana');
        if (btnEliminar) btnEliminar.style.display = 'none';
    }
});
