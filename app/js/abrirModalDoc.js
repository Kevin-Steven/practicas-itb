document.addEventListener('DOMContentLoaded', function () {
  const btnAprobar  = document.querySelectorAll('.btn-aprobar');
  const btnCorregir = document.querySelectorAll('.btn-corregir');

  const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmAction'));
  const modalTitle   = document.getElementById('modalConfirmTitle');
  const modalBody    = document.getElementById('modalConfirmBody');

  const modalActionInput = document.getElementById('modalActionInput');
  const modalIdInput     = document.getElementById('modalIdInput');
  const modalTipoInput   = document.getElementById('modalTipoInput');

  // Elementos para el motivo de rechazo
  const motivoRechazoGroup    = document.getElementById('motivoRechazoGroup');
  const motivoRechazoTextarea = document.getElementById('motivoRechazo');

  // 1) Clic en "Aprobar"
  btnAprobar.forEach(btn => {
    btn.addEventListener('click', function () {
      const docId = this.getAttribute('data-id');
      const tipo  = this.getAttribute('data-tipo');

      // Título y cuerpo del modal
      modalTitle.textContent = "Confirmar Aprobación";
      modalBody.textContent  = "¿Estás seguro de aprobar este documento?";

      // Seteamos valores ocultos
      modalActionInput.value = 'aprobar';
      modalIdInput.value     = docId;
      modalTipoInput.value   = tipo;

      // Omitimos campo 'motivo de rechazo'
      motivoRechazoGroup.style.display = 'none';
      // **Quitamos** el required del textarea
      motivoRechazoTextarea.removeAttribute('required');
      // Limpiamos el textarea
      motivoRechazoTextarea.value = '';

      // Mostramos el modal
      modalConfirm.show();
    });
  });

  // 2) Clic en "Corregir"
  btnCorregir.forEach(btn => {
    btn.addEventListener('click', function () {
      const docId = this.getAttribute('data-id');
      const tipo  = this.getAttribute('data-tipo');

      // Título y cuerpo del modal
      modalTitle.textContent = "Solicitar Corrección";
      modalBody.textContent  = "Por favor, indique el motivo de la corrección antes de confirmar.";

      // Seteamos valores ocultos
      modalActionInput.value = 'corregir';
      modalIdInput.value     = docId;
      modalTipoInput.value   = tipo;

      // Mostramos el campo 'motivo de rechazo'
      motivoRechazoGroup.style.display = 'block';
      // **Agregamos** el required
      motivoRechazoTextarea.setAttribute('required', 'required');
      // Limpiamos el textarea
      motivoRechazoTextarea.value = '';

      // Mostramos el modal
      modalConfirm.show();
    });
  });

  // Opcional: al cerrar el modal, limpiar todo de nuevo
  const modalElement = document.getElementById('modalConfirmAction');
  modalElement.addEventListener('hidden.bs.modal', function () {
    motivoRechazoGroup.style.display = 'none';
    motivoRechazoTextarea.value = '';
    motivoRechazoTextarea.removeAttribute('required');
  });
});
