<div class="col-lg-12 mt-1">

<?php
// Mensaje de bienvenida (SweetAlert2)
if(isset($_SESSION['bienvenido'])):
?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Bienvenido <?php echo $_SESSION["bienvenido"]; ?>'
        });
    </script>
<?php
    unset($_SESSION['bienvenido']);
endif;
?>

<?php if (isset($_SESSION['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill white me-2 fs-5"></i>
        <div><?php echo $_SESSION["ok"]; ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['ok']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center rounded shadow-sm" role="alert">
        <i class="bi bi-x-circle-fill me-2 fs-5"></i>
        <div><?php echo $_SESSION["error"]; ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['info'])): ?>
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center rounded shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div><?php echo $_SESSION["info"]; ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['info']); ?>
<?php endif; ?>

<?php if (@$_SESSION['debug'] == TRUE): ?>
    <div class="alert alert-info alert-dismissible fade show rounded shadow-sm text-center" role="alert">
        <strong>
            Reporte DEBUG:
            <a class="float" data-bs-toggle="modal" data-bs-target="#debug">
                <i class="fa-solid fa-file-lines white fs-3 ms-1"></i>
            </a>
        </strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php //unset($_SESSION['debugger']); ?>
<?php endif; ?>

</div>
