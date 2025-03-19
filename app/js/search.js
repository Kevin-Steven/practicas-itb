document.addEventListener('DOMContentLoaded', function () {
  // Elementos de la página
  const searchInput = document.getElementById('searchInput');
  const filterCarrera = document.getElementById('filterCarrera');
  const postulantesBody = document.getElementById('postulantesBody');
  const noResultsRow = document.getElementById('noResultsRow');

  // Función para "pintar" los resultados filtrados
  function renderRows(data) {
    // Limpia el tbody
    postulantesBody.innerHTML = '';

    // Si no hay registros que mostrar, mostramos la fila "No se encontraron resultados."
    if (data.length === 0) {
      noResultsRow.style.display = '';
      postulantesBody.appendChild(noResultsRow);
      return;
    }

    // Si sí hay resultados, aseguramos que la fila de "noResultsRow" no se vea
    noResultsRow.style.display = 'none';

    // Generamos las filas
    data.forEach(postulante => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="d-none">${postulante.id}</td>
        <td>${postulante.cedula}</td>
        <td>${postulante.nombres}</td>
        <td>${postulante.apellidos}</td>
        <td>${postulante.paralelo}</td>
        <td class="text-center">
          <a href="detalle-documentos.php?id=${postulante.id}" 
             class="text-decoration-none d-flex align-items-center justify-content-center">
            <i class='bx bx-search'></i> Ver detalles
          </a>
        </td>
      `;
      postulantesBody.appendChild(tr);
    });
  }

  // Función para filtrar datos según input y select
  function filterData() {
    const searchValue = searchInput.value.toLowerCase();
    let carreraSelected = filterCarrera.value;

    // Si el select está en "Seleccionar Carrera", lo tratamos como "todos"
    if (carreraSelected === 'Seleccionar Carrera') {
      carreraSelected = 'todos';
    }

    // Filtramos la data
    const filtered = postulantesData.filter(p => {
      const cedula = p.cedula.toLowerCase();
      const nombres = p.nombres.toLowerCase();
      const apellidos = p.apellidos.toLowerCase();
      const paralelo = p.paralelo.toLowerCase();

      // Coincidencia con la búsqueda
      const matchesSearch =
        cedula.includes(searchValue) ||
        nombres.includes(searchValue) ||
        apellidos.includes(searchValue);

      // Coincidencia con la carrera (o "todos")
      const matchesCarrera =
        carreraSelected === 'todos' || 
        paralelo === carreraSelected.toLowerCase();

      return matchesSearch && matchesCarrera;
    });

    // Renderizamos los resultados
    renderRows(filtered);
  }

  // Al cargar la página, dejamos la tabla vacía
  // (ocultamos la fila de "noResultsRow" y no dibujamos nada)
  noResultsRow.style.display = 'none';
  postulantesBody.innerHTML = '';

  // Al escribir en el input o cambiar el select, filtramos
  searchInput.addEventListener('input', filterData);
  filterCarrera.addEventListener('change', filterData);
});