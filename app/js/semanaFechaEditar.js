document.addEventListener('DOMContentLoaded', function () {
    const contenedorActividades = document.getElementById('contenedor-semana');
    const botonAgregarSemana = document.getElementById('agregar-semana');
    const modalCamposIncompletos = new bootstrap.Modal(document.getElementById('modalCamposIncompletos'));

    let contadorSemanas = contenedorActividades.querySelectorAll('.semana').length || 1;

    botonAgregarSemana.addEventListener('click', function () {
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
            modalCamposIncompletos.show();
            return; // Evita clonar si hay campos incompletos
        }

        // ✅ Crear una nueva semana desde cero
        const nuevaSemana = document.createElement('div');
        nuevaSemana.classList.add('semana', 'border', 'p-3', 'mb-3', 'rounded');

        // ✅ Encabezado de la semana
        const tituloSemana = document.createElement('h5');
        tituloSemana.classList.add('titulo-semana', 'mb-3');
        tituloSemana.textContent = `Semana ${contadorSemanas + 1}`;
        nuevaSemana.appendChild(tituloSemana);

        // ✅ Semanas/Fecha INICIO y FIN
        const semanasFechaDiv = document.createElement('div');
        semanasFechaDiv.classList.add('mb-2');
        semanasFechaDiv.innerHTML = `
            <label class="form-label fw-bold">Semanas/Fecha:</label>
            <div class="d-flex gap-2">
                <input type="date" class="form-control" name="semana_inicio[]" required>
                <input type="date" class="form-control" name="semana_fin[]" required>
            </div>
        `;
        nuevaSemana.appendChild(semanasFechaDiv);

        // ✅ Horas realizadas
        const horasDiv = document.createElement('div');
        horasDiv.classList.add('mb-2');
        horasDiv.innerHTML = `
            <label class="form-label fw-bold">Horas realizadas:</label>
            <input type="number" class="form-control" name="horas_realizadas[]" placeholder="ej. 30" required>
        `;
        nuevaSemana.appendChild(horasDiv);

        // ✅ Actividades realizadas
        const actividadesDiv = document.createElement('div');
        actividadesDiv.classList.add('mb-2');
        actividadesDiv.innerHTML = `
            <label class="form-label fw-bold">Actividades realizadas:</label>
            <textarea class="form-control" rows="2" name="actividades_realizadas[]" placeholder="ej. Integración del software de biométrico..." required></textarea>
        `;
        nuevaSemana.appendChild(actividadesDiv);

        // ✅ Botón eliminar
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-semana', 'mt-2');
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.style.backgroundColor = '#df1f1f';
        btnEliminar.style.color = '#ffffff';
        nuevaSemana.appendChild(btnEliminar);

        // ✅ Agregar la nueva semana al contenedor
        contenedorActividades.appendChild(nuevaSemana);

        contadorSemanas++;

        // ✅ Mostrar u ocultar botones de eliminar según la cantidad de semanas
        actualizarBotonesEliminar();
    });

    // ✅ Delegación de eventos para eliminar semana
    contenedorActividades.addEventListener('click', function (event) {
        if (event.target.classList.contains('eliminar-semana')) {
            const semanaAEliminar = event.target.closest('.semana');

            if (semanaAEliminar) {
                semanaAEliminar.remove();
                contadorSemanas--;
            }

            actualizarNumeracionSemanas();
            actualizarBotonesEliminar();
        }
    });

    // ✅ Función para numerar las semanas
    function actualizarNumeracionSemanas() {
        const semanas = contenedorActividades.querySelectorAll('.semana');

        semanas.forEach((semana, index) => {
            let titulo = semana.querySelector('.titulo-semana');
            if (!titulo) {
                titulo = document.createElement('h5');
                titulo.classList.add('titulo-semana', 'mb-3');
                semana.prepend(titulo);
            }

            titulo.textContent = `Semana ${index + 1}`;

            // ✅ Estilos de diseño (opcional)
            semana.style.border = '2px dashed #007bff';
            semana.style.backgroundColor = '#f0f8ff';
            semana.style.padding = '15px';
        });

        contadorSemanas = semanas.length;
    }

    // ✅ Función para mostrar u ocultar botones de eliminar
    function actualizarBotonesEliminar() {
        const semanas = contenedorActividades.querySelectorAll('.semana');

        semanas.forEach((semana, index) => {
            let btnEliminar = semana.querySelector('.eliminar-semana');

            if (!btnEliminar) {
                btnEliminar = document.createElement('button');
                btnEliminar.type = 'button';
                btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-semana', 'mt-2');
                btnEliminar.textContent = 'Eliminar';
                btnEliminar.style.backgroundColor = '#df1f1f';
                btnEliminar.style.color = '#ffffff';
                semana.appendChild(btnEliminar);
            }

            if (semanas.length === 1) {
                btnEliminar.style.display = 'none';
            } else {
                btnEliminar.style.display = 'inline-block';
            }
        });
    }

    // ✅ Inicializa los botones de eliminar al cargar
    actualizarBotonesEliminar();
});
