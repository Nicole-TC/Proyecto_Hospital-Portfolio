
    <?php
    if (isset($_SESSION["user_logged_in"])){
    ?>
    <footer class="container-fluid bg-dark fixed-bottom mt-5">
        <div class="row">
            <div class="col-md text-light text-center py-1">
                Hospital Héroes de Malvinas [Beta] <br>
                <?php if (isset($_SESSION["debug"]) && $_SESSION["debug"] == "1"):?>
                <?php echo "Tiempo de carga: " . $tiempo_opcion1 . " segundos<br>"; ?>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    <?php
    }
    ?>

    <!-- JS Librarias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script>
        document.getElementById('switchModoOscuro').addEventListener('change', function () {
            const activado = this.checked;
            document.body.classList.toggle('modo-oscuro', activado);
            document.cookie = "modo_oscuro=" + (activado ? "1" : "0") + ";path=/;max-age=31536000";
        });

        window.addEventListener('DOMContentLoaded', () => {
            const modoOscuroActivo = document.cookie.includes("modo_oscuro=1");
            if (modoOscuroActivo) document.body.classList.add('modo-oscuro');

            const switchInput = document.getElementById('switchModoOscuro');
            if (switchInput) switchInput.checked = modoOscuroActivo;
        });
    </script>
  </body>
</html>