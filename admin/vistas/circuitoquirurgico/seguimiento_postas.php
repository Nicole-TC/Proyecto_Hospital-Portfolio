<div class="col-12 mb-5">
    <div class="card">
        <div class="card-header">
            Seguimiento de Postas
        </div>
        <div class="col-12 mt-1 mb-1">
            <div class="table-responsive">
                <table class="table align-center display" id="tabla-postas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">DNI</th>
                            <th scope="col">Paciente</th>
                            <th scope="col">Cirugía Indicada</th>
                            <th scope="col" class="text-center">Admisión</th>
                            <th scope="col" class="text-center">Laboratorio</th>
                            <th scope="col" class="text-center">Cardiología</th>
                            <th scope="col" class="text-center">Imágenes</th>
                            <th scope="col" class="text-center">Pre-Anestesia</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">34.567.890</td>
                            <td>García, María</td>
                            <td>Colecistectomía</td>
                            <td class="text-center">9/4/2026</td>
                            <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i><span class="invisible d-none">1</span></td>
                            <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i><span class="invisible d-none">1</span></td>
                            <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i><span class="invisible d-none">1</span></td>
                            <td class="text-center"><i class="fa-solid fa-clock text-warning"></i><span class="invisible d-none">0</span></td>
                            <td class="text-center">
                                <a class="text-info" href="#" data-bs-toggle="modal" data-bs-target="#expedienteModal" title="Ver Detalle">
                                    <i class="bi bi-eye fs-5"></i>
                                </a>
                            </td>
                        </tr>
                        <!-- Más filas de pacientes -->
                    </tbody>
                </table>
            </div>

            <!-- Leyenda -->
            <div class="mt-3 mb-2 ml-2">
                <span class="text-success"><i class="fa-solid fa-circle-check"></i> Completado</span> &nbsp;|&nbsp;
                <span class="text-warning"><i class="fa-solid fa-clock"></i> Pendiente</span> &nbsp;|&nbsp;
                <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Atrasado</span>
            </div>

        </div>
    </div>
</div>

<!-- Modal Expediente del Paciente -->
<div class="modal fade" id="expedienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-folder-open me-2"></i>Expediente Clínico del Paciente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">

        <!-- Datos del paciente -->
        <div class="card mb-4 border-0 bg-light shadow-sm">
          <div class="card-body">
             <div class="row g-3">
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-user me-1"></i> Paciente</p>
                    <p class="fw-bold mb-0 fs-5 text-dark">García, María</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-id-card me-1"></i> DNI</p>
                    <p class="fw-bold mb-0 fs-5 text-dark">34.567.890</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-solid fa-scalpel me-1"></i> Cirugía Indicada</p>
                    <p class="fw-bold mb-0 fs-5 text-dark">Colecistectomía</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted small text-uppercase fw-semibold"><i class="fa-regular fa-calendar-check me-1"></i> Fecha de Admisión</p>
                    <p class="fw-bold mb-0 fs-5 text-dark">9/4/2026</p>
                </div>
             </div>
          </div>
        </div>

        <!-- Dos columnas -->
        <div class="row g-4 mt-1">
          <!-- Estudios requeridos -->
          <div class="col-md-6">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-vial-circle-check me-2"></i>Estudios Requeridos</h6>
            <div class="list-group list-group-flush mt-3">
              <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3 border-bottom">
                 <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-file-medical text-success me-2"></i> Hemograma Completo</h6>
                    <small class="text-muted"><i class="fa-solid fa-building me-1"></i> Laboratorio &bull; 11/04/2026</small>
                 </div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3 border-bottom">
                 <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-file-medical text-success me-2"></i> Coagulograma</h6>
                    <small class="text-muted"><i class="fa-solid fa-building me-1"></i> Laboratorio &bull; 11/04/2026</small>
                 </div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3 border-bottom">
                 <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-heart-pulse text-success me-2"></i> Electrocardiograma</h6>
                    <small class="text-muted"><i class="fa-solid fa-building me-1"></i> Cardiología &bull; 11/04/2026</small>
                 </div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3 border-bottom">
                 <div>
                    <h6 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-heart-pulse text-success me-2"></i> Ecocardiograma</h6>
                    <small class="text-muted"><i class="fa-solid fa-building me-1"></i> Cardiología &bull; 12/04/2026</small>
                 </div>
              </div>
            </div>
          </div>

          <!-- Historia de auditoría -->
          <div class="col-md-6">
            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historia de Auditoría</h6>
            
            <div class="position-relative mt-4 ms-3">
               <!-- Linea vertical -->
               <div class="position-absolute h-100 bg-primary opacity-25" style="width: 2px; left: 7px; top: 10px;"></div>
               
               <div class="d-flex mb-4 position-relative">
                  <div class="bg-white text-primary mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">Admitido por Admin X</p>
                     <p class="mb-1 small text-secondary">Admin María González</p>
                     <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> 9/4/2026 – 09:00 p.m.</span>
                  </div>
               </div>
               
               <div class="d-flex mb-4 position-relative">
                  <div class="bg-white text-success mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle-check fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">Laboratorio OK - Hemograma y Coagulograma</p>
                     <p class="mb-1 small text-secondary">Lab. Carlos Pérez</p>
                     <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> 10/4/2026 – 09:00 p.m.</span>
                  </div>
               </div>
               
               <div class="d-flex mb-4 position-relative">
                  <div class="bg-white text-success mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle-check fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">Cardiología OK - Riesgo Bajo</p>
                     <p class="mb-1 small text-secondary">Dr. Juan Martínez</p>
                     <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> 11/4/2026 – 09:00 p.m.</span>
                  </div>
               </div>

               <div class="d-flex mb-2 position-relative">
                  <div class="bg-white text-warning mt-1 me-3 position-relative z-1" style="margin-left: -2px;"><i class="fa-solid fa-circle-notch fa-spin fs-5"></i></div>
                  <div>
                     <p class="mb-0 fw-bold text-dark">Pendiente de Pre-Anestesia</p>
                     <p class="mb-1 small text-secondary">Aguardando evaluación</p>
                     <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar me-1"></i> 12/4/2026</span>
                  </div>
               </div>
               
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer border-top pt-3 pb-3">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.js"></script>

<!-- DATATABLES -->
<script>
    $(document).ready(function() {
        $('#tabla-postas').DataTable({
            'language': {
                'lengthMenu': "Mostrar " +
                    `<select class ="custom-SELECT custom-SELECT-sm form-control form-control-sm">
                <option value = '10'>10</option>
                <option value = '25'>25</option>
                <option value = '50'>50</option>
                <option value = '100'>100</option>
                <option value = '-1'>Todos</option>
             </select>` +
                    ' registros por página',
                'zeroRecords': 'Sin resultados',
                'info': 'Se encontraron _TOTAL_ coincidencias',
                'infoEmpty': 'No hay registros por mostrar',
                'infoFiltered': '[de un total de _MAX_]',
                'search': 'Filtrar',
                'paginate': {
                    'next': 'Siguiente',
                    'previous': 'Anterior'
                }
            }
        });
    });
</script>