document.addEventListener('DOMContentLoaded', function () {
  const btnAprobar = document.querySelectorAll('.btn-aprobar');
  const btnCorregir = document.querySelectorAll('.btn-corregir');

  const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmAction'));
  const modalTitle = document.getElementById('modalConfirmTitle');
  const modalBody = document.getElementById('modalConfirmBody');
  const modalForm = document.getElementById('modalConfirmForm');

  const modalActionInput = document.getElementById('modalActionInput');
  const modalIdInput = document.getElementById('modalIdInput');
  const modalTipoInput = document.getElementById('modalTipoInput');

  // Campos para el motivo de rechazo
  const motivoRechazoGroup = document.getElementById('motivoRechazoGroup');
  const motivoRechazoTextarea = document.getElementById('motivoRechazo');

  // Botones de aprobar
  btnAprobar.forEach(btn => {
    btn.addEventListener('click', function () {
      const docId = this.getAttribute('data-id');
      const tipo = this.getAttribute('data-tipo');

      // Configura los textos del modal
      modalTitle.textContent = "Confirmar Aprobación";
      modalBody.textContent = "¿Estás seguro de aprobar este documento?";

      // Set valores de inputs ocultos
      modalActionInput.value = 'aprobar';
      modalIdInput.value = docId;
      modalTipoInput.value = tipo;

      // Oculta el campo del motivo de rechazo (no aplica aquí)
      motivoRechazoGroup.style.display = 'none';
      motivoRechazoTextarea.value = '';

      // Muestra el modal
      modalConfirm.show();
    });
  });

  // Botones de corregir
  btnCorregir.forEach(btn => {
    btn.addEventListener('click', function () {
      const docId = this.getAttribute('data-id');
      const tipo = this.getAttribute('data-tipo');

      // Configura los textos del modal
      modalTitle.textContent = "Solicitar Corrección";
      modalBody.textContent = "Por favor, indique el motivo de la corrección antes de confirmar.";

      // Set valores de inputs ocultos
      modalActionInput.value = 'corregir';
      modalIdInput.value = docId;
      modalTipoInput.value = tipo;

      // Muestra el campo para el motivo de rechazo
      motivoRechazoGroup.style.display = 'block';
      motivoRechazoTextarea.value = '';

      // Muestra el modal
      modalConfirm.show();
    });
  });

  // Opcional: limpiar el textarea cuando se cierre el modal
  const modalElement = document.getElementById('modalConfirmAction');
  modalElement.addEventListener('hidden.bs.modal', function () {
    motivoRechazoGroup.style.display = 'none';
    motivoRechazoTextarea.value = '';
  });
});
