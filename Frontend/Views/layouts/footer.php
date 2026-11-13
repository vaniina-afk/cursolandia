<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Limpiar la URL para que no se repitan los carteles al recargar
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('exito');
                url.searchParams.delete('error');
                url.searchParams.delete('baja');
                url.searchParams.delete('borrado');
                url.searchParams.delete('foro');
                url.searchParams.delete('mensaje');
                url.searchParams.delete('pendiente');
                url.searchParams.delete('inscripcion');
                window.history.replaceState(null, null, url);
            }

            // Ocultar mensajes automáticamente
            setTimeout(function() {
                let alertas = document.querySelectorAll('.auto-ocultar');
                alertas.forEach(function(alerta) {
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(() => alerta.remove(), 500); 
                });
            }, 3000); 
        });
    </script>
</body>
</html>