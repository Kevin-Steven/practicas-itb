document.addEventListener('DOMContentLoaded', function () {
    const contenedorExperiencia = document.getElementById('contenedor-experiencia');
    const botonAgregar = document.getElementById('agregar-experiencia');

    // Modal para advertencia de campos incompletos (reutilizando el modal que mencionaste)
    const modalCamposIncompletos = new bootstrap.Modal(document.getElementById('modalCamposIncompletos'));

    let contadorNuevasExperiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral').length || 1;

    botonAgregar.addEventListener('click', function () {
        const experiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');
        const ultimaExperiencia = experiencias[experiencias.length - 1];

        // ✅ Verificar que los campos de la última experiencia no estén vacíos antes de crear una nueva
        const inputsUltima = ultimaExperiencia.querySelectorAll('input, textarea');
        let camposCompletos = true;

        inputsUltima.forEach(input => {
            if (input.value.trim() === '') {
                camposCompletos = false;
            }
        });

        if (!camposCompletos) {
            // ✅ Mostrar modal de advertencia si faltan campos por completar
            modalCamposIncompletos.show();
            return; // Evita continuar si no están completos
        }

        // ✅ Crear un nuevo contenedor de experiencia laboral
        const nuevaExperiencia = document.createElement('div');
        nuevaExperiencia.classList.add('experiencia-laboral', 'border', 'p-3', 'mb-3', 'rounded');

        // ✅ Agregar título dinámico
        const titulo = document.createElement('h5');
        titulo.classList.add('titulo-experiencia', 'mb-3');
        titulo.textContent = `Nueva experiencia ${contadorNuevasExperiencias + 1}`;
        nuevaExperiencia.appendChild(titulo);

        // ✅ Campo: Lugar Laborado
        const lugarDiv = document.createElement('div');
        lugarDiv.classList.add('mb-2');
        lugarDiv.innerHTML = `
            <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
            <input type="text" class="form-control" name="lugar_laborado[]" required>
        `;
        nuevaExperiencia.appendChild(lugarDiv);

        // ✅ Campo: Periodo Tiempo
        const periodoDiv = document.createElement('div');
        periodoDiv.classList.add('mb-2');
        periodoDiv.innerHTML = `
            <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
            <input type="text" class="form-control" name="periodo_tiempo[]" required>
        `;
        nuevaExperiencia.appendChild(periodoDiv);

        // ✅ Campo: Funciones realizadas
        const funcionesDiv = document.createElement('div');
        funcionesDiv.classList.add('mb-2');
        funcionesDiv.innerHTML = `
            <label class="form-label fw-bold">Funciones realizadas:</label>
            <input type="text" class="form-control" name="funciones_realizadas[]" required>
        `;
        nuevaExperiencia.appendChild(funcionesDiv);

        // ✅ Botón Eliminar
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-experiencia');
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.style.backgroundColor = '#df1f1f';
        btnEliminar.style.color = '#ffffff';

        nuevaExperiencia.appendChild(btnEliminar);

        // ✅ Agregar al contenedor
        contenedorExperiencia.appendChild(nuevaExperiencia);

        // ✅ Incrementa el contador para el título
        contadorNuevasExperiencias++;

        actualizarBotonesEliminar();
    });

    // ✅ Delegación de eventos para eliminar experiencia
    contenedorExperiencia.addEventListener('click', function (event) {
        if (event.target.classList.contains('eliminar-experiencia')) {
            const experienciaAEliminar = event.target.closest('.experiencia-laboral');

            if (experienciaAEliminar) {
                experienciaAEliminar.remove();
            }

            // ✅ Reorganizamos los títulos
            const todasLasExperiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');
            let nuevaExpIndex = 1;

            todasLasExperiencias.forEach(exp => {
                const titulo = exp.querySelector('.titulo-experiencia');
                if (titulo) {
                    titulo.textContent = `Nueva experiencia ${nuevaExpIndex++}`;
                }
            });

            contadorNuevasExperiencias = nuevaExpIndex;

            actualizarBotonesEliminar();
        }
    });

    // ✅ Función para mostrar u ocultar el botón eliminar según cantidad
    function actualizarBotonesEliminar() {
        const todasLasExperiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');

        todasLasExperiencias.forEach((exp, index) => {
            let btnEliminar = exp.querySelector('.eliminar-experiencia');

            if (!btnEliminar) {
                btnEliminar = document.createElement('button');
                btnEliminar.type = 'button';
                btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-experiencia');
                btnEliminar.textContent = 'Eliminar';
                btnEliminar.style.backgroundColor = '#df1f1f';
                btnEliminar.style.color = '#ffffff';
                exp.appendChild(btnEliminar);
            }

            // ✅ Oculta el botón si solo queda una experiencia
            btnEliminar.style.display = (todasLasExperiencias.length === 1) ? 'none' : 'inline-block';
        });
    }

    // ✅ Inicializamos los botones al cargar
    actualizarBotonesEliminar();
});
