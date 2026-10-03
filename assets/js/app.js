
 // Pequeñas mejoras de interfaz (el procesamiento real se hace en PHP).

// Modal de confirmación de eliminación: copia el código y título del libro al modal
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalEliminar');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (evento) {
            const boton = evento.relatedTarget;
            modal.querySelector('#eliminarCodigo').value = boton.getAttribute('data-codigo');
            modal.querySelector('#eliminarTitulo').textContent = boton.getAttribute('data-titulo');
        });
    }

    // Confirmación simple para devoluciones
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.getAttribute('data-confirmar'))) {
                e.preventDefault();
            }
        });
    });
});
