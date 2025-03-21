document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#usuariosTable tbody tr.usuario-row');
    const noResultsRow = document.getElementById('noResultsRow');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = searchInput.value.toLowerCase();
        let found = false;

        tableRows.forEach(row => {
            const cedula = row.cells[1]?.textContent.toLowerCase();
            const nombre = row.cells[2]?.textContent.toLowerCase();
            const apellido = row.cells[3]?.textContent.toLowerCase();

            if (cedula.includes(searchTerm) || nombre.includes(searchTerm) || apellido.includes(searchTerm)) {
                row.style.display = '';
                found = true;
            } else {
                row.style.display = 'none';
            }
        });

        // ✅ Mostrar o no la fila "No se encontraron resultados"
        noResultsRow.style.display = found ? 'none' : '';
    });
});
