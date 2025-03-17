document.addEventListener('DOMContentLoaded', function() {
    const contenedorExperiencia = document.getElementById('contenedor-experiencia');
    const botonAgregar = document.getElementById('agregar-experiencia');

    let contadorClones = 1;

    botonAgregar.addEventListener('click', function() {
        const experiencias = contenedorExperiencia.querySelectorAll('.experiencia-laboral');

        // Oculta todas las experiencias existentes
        experiencias.forEach(exp => exp.style.display = 'none');

        // Clona la última experiencia que estaba agregada (puede ser la primera si es la inicial)
        const ultimaExperiencia = experiencias[experiencias.length - 1];
        const nuevaExperiencia = ultimaExperiencia.cloneNode(true);

        // Limpia los campos del clon
        const inputs = nuevaExperiencia.querySelectorAll('input, textarea');
        inputs.forEach(input => input.value = '');

        // Agrega encabezado dinámico si no existe
        let encabezado = nuevaExperiencia.querySelector('.titulo-experiencia');
        if (!encabezado) {
            encabezado = document.createElement('h5');
            encabezado.classList.add('titulo-experiencia', 'mb-3');
            nuevaExperiencia.prepend(encabezado);
        }
        encabezado.textContent = `Nueva experiencia ${contadorClones}`;

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
                // Oculta todas menos la última
                experiencias.forEach((exp, index) => {
                    exp.style.display = (index === experiencias.length - 1) ? 'block' : 'none';
                });

                // Si es un clon, agrega el estilo visual; si es la original, quítaselo
                const ultima = experiencias[experiencias.length - 1];
                const encabezado = ultima.querySelector('.titulo-experiencia');
                if (encabezado) {
                    encabezado.textContent = `Nueva experiencia ${experiencias.length - 1}`; // Restamos 1 porque la primera no cuenta como nueva
                    ultima.style.border = '2px dashed #007bff';
                    ultima.style.backgroundColor = '#f0f8ff';
                } else {
                    ultima.style.border = '';
                    ultima.style.backgroundColor = '';
                }

                contadorClones = experiencias.length; // Actualizamos el contador
            } else {
                // Si solo queda una, muéstrala sin estilos especiales
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
