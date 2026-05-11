<div class="grey-bg container-fluid mb-5">

    <section id="minimal-statistics">
        <div class="row">

            <!-- Usuarios-->
            <?php HTML::tarjeta("menu_usuarios", "Usuarios", "Beta", "fa-solid fa-users-gear", "info", "index.php?modulo=menu_usuarios"); ?>

            <!-- Notas -->
            <?php $html = HTML::tarjeta("notas", "Notas", "Presentadas a dirección", "fa-solid fa-file", "warning", "index.php?modulo=notas"); ?>

            <?php HTML::tarjeta("menu_digitalizar", "Digitalizacion", "Beta", "fa-solid fa-users-gear", "info", "index.php?modulo=menu_digitalizar"); ?>

            <!-- Circuito Quirúrgico -->
            <?php HTML::tarjeta("menu_circuitoquirurgico", "Circuito Quirúrgico", "Beta", "fa-solid fa-heart-pulse", "danger", "index.php?modulo=menu_circuitoquirurgico"); ?>



            <!-- <div class="col-xl-3 col-lg-4 col-sm-6 col-12 mb-1">
                <div class="card shadow-sm">
                    <div class="card-content">
                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#noticias_dph">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="align-self-center">
                                        <i class="fa-solid fa-radio fa-beat-fade info fs-1 float-left"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h5 class="fw-bold">Noticias DPH</h5>
                                        <span class="fst-italic">Novedades</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div> -->

        </div>
    </section>
</div>