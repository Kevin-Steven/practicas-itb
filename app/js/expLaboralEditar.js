document.addEventListener('DOMContentLoaded', function () {
    const contenedorExperiencia = document.getElementById('contenedor-experiencia');
    const botonAgregar = document.getElementById('agregar-experiencia');

    let contadorNuevasExperiencias = 1;

    botonAgregar.addEventListener('click', function () {
        const nuevaExperiencia = document.createElement('div');
        nuevaExperiencia.classList.add('experiencia-laboral', 'border', 'p-3', 'mb-3', 'rounded');

        // Agregar título dinámico
        const titulo = document.createElement('h5');
        titulo.classList.add('titulo-experiencia', 'mb-3');
        titulo.textContent = `Nueva experiencia ${contadorNuevasExperiencias}`;
        nuevaExperiencia.appendChild(titulo);

        // Campo: Lugar Laborado
        const lugarDiv = document.createElement('div');
        lugarDiv.classList.add('mb-2');
        lugarDiv.innerHTML = `
            <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
            <input type="text" class="form-control" name="lugar_laborado[]" required>
        `;
        nuevaExperiencia.appendChild(lugarDiv);

        // Campo: Periodo Tiempo
        const periodoDiv = document.createElement('div');
        periodoDiv.classList.add('mb-2');
        periodoDiv.innerHTML = `
            <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
            <input type="text" class="form-control" name="periodo_tiempo[]" required>
        `;
        nuevaExperiencia.appendChild(periodoDiv);

        // Campo: Funciones realizadas
        const funcionesDiv = document.createElement('div');
        funcionesDiv.classList.add('mb-2');
        funcionesDiv.innerHTML = `
            <label class="form-label fw-bold">Funciones realizadas:</label>
            <input type="text" class="form-control" name="funciones_realizadas[]" required>
        `;
        nuevaExperiencia.appendChild(funcionesDiv);

        // Botón Eliminar
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-experiencia');
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.style.backgroundColor = '#df1f1f'; // ✅ Color personalizado
        btnEliminar.style.color = '#ffffff'; // Texto en blanco (opcional para contraste)

        nuevaExperiencia.appendChild(btnEliminar);

        // Agregar al contenedor
        contenedorExperiencia.appendChild(nuevaExperiencia);

        // Incrementar contador para el título
        contadorNuevasExperiencias++;
    });

    // Delegación de eventos para eliminar experiencia
    contenedorExperiencia.addEventListener('click', function (event) {
        if (event.target.classList.contains('eliminar-experiencia')) {
            const experienciaAEliminar = event.target.closest('.experiencia-laboral');

            if (experienciaAEliminar) {
                experienciaAEliminar.remove();
            }

            // Reorganizamos los títulos si es necesario
            const todasLasExperiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');
            let nuevaExpIndex = 1;

            todasLasExperiencias.forEach(exp => {
                const titulo = exp.querySelector('.titulo-experiencia');
                if (titulo) {
                    titulo.textContent = `Nueva experiencia ${nuevaExpIndex++}`;
                }
            });

            contadorNuevasExperiencias = nuevaExpIndex;
        }
    });

    // Mostrar/ocultar botón eliminar en las existentes según cantidad
    const todasLasExperiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');
    todasLasExperiencias.forEach((exp, index) => {
        let btnEliminar = exp.querySelector('.eliminar-experiencia');

        if (!btnEliminar) {
            btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.classList.add('btn', 'btn-sm', 'eliminar-experiencia');
            btnEliminar.textContent = 'Eliminar';

            btnEliminar.style.backgroundColor = '#df1f1f';
            btnEliminar.style.color = '#ffffff'; // Texto en blanco (opcional)

            exp.appendChild(btnEliminar);
        } else {
            // ✅ Si el botón ya existe, nos aseguramos que tenga el color correcto
            btnEliminar.style.backgroundColor = '#df1f1f';
            btnEliminar.style.color = '#ffffff'; // Blanco para que el texto sea visible
        }

        // Si solo hay una experiencia, ocultar el botón eliminar
        if (todasLasExperiencias.length === 1) {
            btnEliminar.style.display = 'none';
        } else {
            btnEliminar.style.display = 'inline-block';
        }
    });

});
