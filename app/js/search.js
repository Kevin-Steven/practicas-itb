document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const filterCarrera = document.getElementById('filterCarrera');
    const postulantesBody = document.getElementById('postulantesBody');
    const noResultsRow = document.getElementById('noResultsRow');
  
    const renderRows = (data) => {
      postulantesBody.innerHTML = ''; // Limpiamos
  
      if (data.length === 0) {
        noResultsRow.style.display = '';
        postulantesBody.appendChild(noResultsRow);
        return;
      }
  
      data.forEach(postulante => {
        const tr = document.createElement('tr');
  
        tr.innerHTML = `
          <td class="d-none">${postulante.id}</td>
          <td>${postulante.cedula}</td>
          <td>${postulante.nombres}</td>
          <td>${postulante.apellidos}</td>
          <td class="d-none d-sm-table-cell">${postulante.carrera}</td>
          <td class="text-center">
            <a href="detalle-documentos.php?id=${postulante.id}" class="text-decoration-none d-flex align-items-center justify-content-center">
              <i class='bx bx-search'></i> Ver detalles
            </a>
          </td>
        `;
  
        postulantesBody.appendChild(tr);
      });
    };
  
    const filterData = () => {
      const searchValue = searchInput.value.toLowerCase();
      const carreraSelected = filterCarrera.value;
  
      const filtered = postulantesData.filter(p => {
        const cedula = p.cedula.toLowerCase();
        const nombres = p.nombres.toLowerCase();
        const apellidos = p.apellidos.toLowerCase();
        const carrera = p.carrera.toLowerCase();
  
        const matchesSearch = cedula.includes(searchValue) || nombres.includes(searchValue) || apellidos.includes(searchValue);
        const matchesCarrera = (carreraSelected === 'todos' || carrera === carreraSelected.toLowerCase());
  
        return matchesSearch && matchesCarrera;
      });
  
      renderRows(filtered);
    };
  
    searchInput.addEventListener('input', filterData);
    filterCarrera.addEventListener('change', filterData);
  });
  