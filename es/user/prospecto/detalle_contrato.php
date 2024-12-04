<?php
session_start();
include_once('../../../../Outsourcing/config.php');


// Verificar si el usuario está logueado y es un prospecto
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'PRO') {
    header("Location: login.php");
    exit();
}

// Obtener el ID del contrato de la URL
$contrato_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($contrato_id === 0) {
    die("ID de contrato no válido");
}

$user_id = $_SESSION['user_id'];

// Obtener el número del prospecto asociado al usuario
$query_prospecto = "SELECT numero FROM prospecto WHERE usuario = ?";
$stmt_prospecto = mysqli_prepare($conexion, $query_prospecto);
mysqli_stmt_bind_param($stmt_prospecto, "i", $user_id);
mysqli_stmt_execute($stmt_prospecto);
$result_prospecto = mysqli_stmt_get_result($stmt_prospecto);

if (mysqli_num_rows($result_prospecto) == 0) {
    die("Error: No se encontró un prospecto asociado a este usuario.");
}

$prospecto = mysqli_fetch_assoc($result_prospecto);
$prospecto_id = $prospecto['numero'];

// Obtener los detalles del contrato
$query = "SELECT c.*, v.titulo AS vacante_titulo, v.es_directo, v.empresa AS empresa_id,
                 e.nombre AS empresa_nombre, e.ciudad AS empresa_ciudad, e.calle AS empresa_calle,
                 e.numeroCalle AS empresa_numeroCalle, e.colonia AS empresa_colonia,
                 e.codigoPostal AS empresa_codigoPostal, e.nombreCont AS empresa_nombreCont,
                 e.primerApellidoCont AS empresa_primerApellidoCont, e.segundoApellidoCont AS empresa_segundoApellidoCont,
                 p.nombre AS prospecto_nombre, p.primerApellido AS prospecto_primerApellido,
                 p.segundoApellido AS prospecto_segundoApellido, tc.nombre AS tipo_contrato_nombre
          FROM contrato AS c
          INNER JOIN vacante AS v ON c.vacante = v.numero
          INNER JOIN empresa AS e ON v.empresa = e.numero
          INNER JOIN prospecto AS p ON c.prospecto = p.numero
          INNER JOIN tipo_contrato AS tc ON c.tipo_contrato = tc.codigo
          WHERE c.numero = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$resultado = $stmt->get_result();
$contrato = $resultado->fetch_assoc();

if (!$contrato) {
    die("Contrato no encontrado");
}

// Obtener los requerimientos de la vacante
$query_req = "SELECT descripcion FROM requerimiento WHERE vacante = ?";
$stmt_req = $conexion->prepare($query_req);
$stmt_req->bind_param("i", $contrato['vacante']);
$stmt_req->execute();
$resultado_req = $stmt_req->get_result();
$requerimientos = $resultado_req->fetch_all(MYSQLI_ASSOC);

// Función para formatear la fecha
function formatearFecha($fecha)
{
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaObj = new DateTime($fecha);
    $dia = $fechaObj->format('j');
    $mes = $meses[(int) $fechaObj->format('n') - 1];
    $año = $fechaObj->format('Y');
    return "$dia de $mes de $año";
}

// Generar el contenido del contrato
$contenido_contrato = "
<h1 style='text-align: center;'>CONTRATO INDIVIDUAL DE TRABAJO</h1>

<p style='text-align: right;'>{$contrato['empresa_ciudad']}, a " . formatearFecha($contrato['fechaInicio']) . "</p>

<p>CONTRATO QUE CELEBRAN POR UNA PARTE</p>
";

if (!$contrato['es_directo']) {
    $contenido_contrato .= "
    <p>TalentBridge, persona moral de nacionalidad MEXICANA con domicilio en:
    Carretera Libre Tijuana-Tecate Km 10 Fracc. El Refugio, 22253 Redondo, Tijuana.</p>

    <p>Representada por:
    Christopher Anahel Gonzalez Leyva, persona mayor de edad, con las facultades necesarias para la firma de este contrato.</p>

    <p>Y</p>
    ";
}

$contenido_contrato .= "
<p>{$contrato['empresa_nombre']}, persona moral de nacionalidad MEXICANA que cuenta con domicilio basado en:</p>
<p>{$contrato['empresa_calle']} {$contrato['empresa_numeroCalle']}, {$contrato['empresa_colonia']}, {$contrato['empresa_ciudad']}, {$contrato['empresa_codigoPostal']}</p>

<p>Actuando en este contrato a través de su representante {$contrato['empresa_nombreCont']} {$contrato['empresa_primerApellidoCont']} {$contrato['empresa_segundoApellidoCont']}, persona mayor de edad que cuenta con las facultades suficientes y necesarias para celebrar el presente contrato.</p>

<p>Y POR LA OTRA</p>

<p>{$contrato['prospecto_nombre']} {$contrato['prospecto_primerApellido']} {$contrato['prospecto_segundoApellido']} persona fisica mayor de edad.</p>

<p>QUIENES SE RECONOCEN EXPRESA Y RECÍPROCAMENTE CON CAPACIDAD PLENA Y SUFICIENTE PARA CELEBRAR EL PRESENTE CONTRATO INDIVIDUAL DE TRABAJO, Y PARA TAL EFECTO, {$contrato['empresa_nombre']} SERÁ IDENTIFICADO EN EL PRESENTE CONTRATO COMO \"PATRÓN\" Y {$contrato['prospecto_nombre']} {$contrato['prospecto_primerApellido']} SERÁ IDENTIFICADO COMO \"TRABAJADOR\"; ADEMÁS SE PODRÁ HACER REFERENCIA A ELLAS DE MANERA CONJUNTA COMO \"LAS PARTES\"; EN ESTE SENTIDO, LAS PARTES MANIFIESTAN EN PRIMER LUGAR LAS SIGUIENTES:</p>

<h2 style='text-align: center;'>DECLARACIONES</h2>

<p>I. LAS PARTES manifiestan que reúnen los requisitos legales exigidos para la celebración del presente contrato.</p>

<p>II. EL PATRÓN manifiesta que tiene interés en contratar los servicios DEL TRABAJADOR</p>

<p>III. EL TRABAJADOR manifiesta que tiene la capacitación y aptitudes para desarrollar las actividades que le encomiende EL PATRÓN en términos del presente contrato</p>

<p>IV. EL TRABAJADOR señala además que está de acuerdo en desempeñar los requerimientos DEL PATRON y ajustarse a las condiciones generales de trabajo sobre las cuales prestará SUS servicios.</p>

<p>V. Habiendo llegado las Partes, libre y espontáneamente, a una coincidencia mutua de sus voluntades, formalizan el presente CONTRATO INDIVIDUAL DE TRABAJO, en adelante únicamente el \"Contrato\" o el \"Contrato de Trabajo\", el cual tiene por objeto el establecimiento de una relación laboral entre LAS PARTES, que se regirá por las siguientes:</p>

<h2 style='text-align: center;'>CLAUSULAS</h2>

<p>PRIMERA. DEL TRABAJO A DESEMPEÑAR</p>
";

switch ($contrato['tipo_contrato_nombre']) {
    case 'Contrato permanente':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de {$contrato['vacante_titulo']}, el cual será con carácter indefinido a partir del día " . formatearFecha($contrato['fechaInicio']) . " y las labores principales que deberá desempeñar EL TRABAJADOR consistirán en:</p>";
        break;
    case 'Contrato definido (plazo fijo)':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de " . strtoupper($contrato['vacante_titulo']) . ", a partir de " . formatearFecha($contrato['fechaInicio']) . " y hasta el " . formatearFecha($contrato['fechaCierre']) . " y las labores principales que deberá desempeñar EL TRABAJADOR consistirán en:</p>";
        break;
    case 'Contrato por obra o proyecto':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de {$contrato['vacante_titulo']}, bajo la modalidad de obra o proyecto. La duración del contrato estará determinada por el tiempo necesario para la finalización de las actividades asignadas, comenzando a partir del día " . formatearFecha($contrato['fechaInicio']) . ". Las labores principales que deberá desempeñar el TRABAJADOR consistirán en:</p>";
        break;
}

$contenido_contrato .= "<ul>";
foreach ($requerimientos as $req) {
    $contenido_contrato .= "<li>{$req['descripcion']}</li>";
}
$contenido_contrato .= "</ul>";

$contenido_contrato .= "
<p>SEGUNDA. DEL LUGAR DE TRABAJO.</p>
<p>EL TRABAJADOR prestará sus servicios en el centro de trabajo ubicado en: {$contrato['empresa_calle']} {$contrato['empresa_numeroCalle']}, {$contrato['empresa_colonia']}, {$contrato['empresa_ciudad']}, {$contrato['empresa_codigoPostal']}. No obstante lo anterior, EL PATRÓN se reserva el derecho de modificar el lugar de trabajo DEL TRABAJADOR, respetando los derechos establecidos en favor de este; comunicando en todo caso la modificación del lugar de trabajo de manera oportuna y siempre que dicho cambio se encuentre justificado por razones económicas, técnicas, organizativas o de producción.</p>

<p>TERCERA. DE LA JORNADA DE TRABAJO.</p>
<p>La duración de la jornada será de {$contrato['horasDiarias']} horas por día, las cuales serán prestadas conforme al siguiente horario: {$contrato['horario']}.</p>

<p>Cuando por circunstancias extraordinarias la jornada de trabajo llegue a prolongarse, los servicios prestados durante el tiempo excedente se considerarán como extraordinarios y se pagarán a razón del cien por ciento adicional al salario establecido para las horas de trabajo normal.</p>

<p>Las horas de trabajo extraordinario no podrán exceder de tres horas diarias ni de tres veces en una misma semana. En este sentido, EL TRABAJADOR en ningún caso podrá labor, por tiempo extraordinario, salvo que EL PATRÓN lo autorice o lo requiera expresamente.</p>

<p>Cuando la prolongación del tiempo extraordinario exceda de nueve horas a la semana, EL PATRÓN estará obligado a pagar AL TRABAJADOR el tiempo excedente a razón de un doscientos por ciento más del salario que le corresponda a las horas de la jornada establecida.</p>

<p>CUARTA. DEL SALARIO</p>
<p>EL TRABAJADOR percibirá, por la prestación de los servicios a que se refiere el presente contrato, un salario de {$contrato['salario']} mensuales, al cual se aplicará la parte proporcional correspondiente a los descansos semanales.</p>

<p>Una vez recibido el salario por parte DEL TRABAJADOR, este se encontrará obligado a firmar las constancias de pago respectivas.</p>

<p>QUINTA. DE LAS OBLIGACIONES DEL TRABAJADOR</p>
<p>EL TRABAJADOR tendrá, durante el tiempo que se encuentre vigente el presente contrato, las siguientes obligaciones:</p>
<p>I. Estará obligado a prestar los servicios personales que se especifican en la cláusula primera del presente contrato, subordinado jurídicamente AL PATRÓN. Dichos servicios deberán ser proporcionados con esmero, dedicación y eficacia.</p>
<p>II. Acatará en el desempeño de su trabajo todas las órdenes, circulares y disposiciones que dicte EL PATRÓN y aquellas que se encuentren establecidas en los ordenamientos legales que le sean aplicables.</p>
<p>III. Se someterá a los exámenes médicos que periódicamente establezca EL PATRÓN, en los términos del artículo 134 de la Ley Federal del Trabajo, a fin de mantener en forma óptima sus facultades físicas e intelectuales, para el mejor desempeño de sus funciones. El médico que practique los reconocimientos será designado y retribuido por EL PATRÓN.</p>
<p>IV. Deberá realizar el trabajo que se le encomiende observando las normas de calidad y fabricación que EL PATRÓN le indique.</p>

<p>SEXTA. DE LA TERMINACION DEL CONTRATO</p>
<p>Al finalizar el contrato, EL TRABAJADOR recibirá sin excepción alguna: los salarios que se encuentren pendientes de pago, y el pago de las partes proporcionales que correspondan al aguinaldo, vacaciones y prima vacacional. Lo anterior, además de las cantidades e indemnizaciones que le correspondan con motivo de su antigüedad.</p>

<p>SEPTIMA. DE LA INTEGRIDAD DEL ACUERDO</p>
<p>LAS PARTES reconocen y aceptan que este Contrato y sus adiciones constituyen un acuerdo total entre ellas, por lo que desde el momento de su firma quedarán sin efecto cualquier acuerdo o negociación previa, prevaleciendo lo dispuesto en este instrumento respecto de cualquier otro contrato o convenio.</p>

<p>Asimismo, las Partes reconocen que, en caso de existir, documentos Anexos y/o adjuntos al presente Contrato de trabajo, estos forman parte o integran el mismo, para todos los efectos legales.</p>

<p>Además, si alguna de las cláusulas resultara nula en virtud de la aplicación, interpretación o modificación de la legislación laboral, esta se tendrá por no puesta, manteniendo su vigencia el resto del Contrato. Llegado este caso, LAS PARTES se comprometen, a adaptar el texto de las cláusulas o partes del Contrato afectadas, a la aplicación, interpretación o modificaciones legales.</p>

<p>OCTAVA. DE LA LEGISLACIÓN Y JURISDICCIÓN APLICABLE.</p>
<p>Respecto a las obligaciones y derechos que mutuamente les corresponden y que no hayan sido motivo de cláusula expresa en el presente contrato, LAS PARTES se sujetarán a las disposiciones de la Ley Federal del Trabajo.</p>

<p>Para todo lo relativo a la interpretación y cumplimiento de las obligaciones derivadas del presente contrato, las partes acuerdan someterse a la jurisdicción y competencia de la junta local que conforme a derecho deba conocer el asunto en razón del lugar en el que se desempeña el trabajo, con renuncia a su propio fuero en caso que este les aplique y sea procedente por razón de domicilio, vecindad, o por cualquier otra naturaleza.</p>

<p>Leído que fue el presente instrumento y enteradas las partes de su contenido y alcance, lo firman de conformidad en el lugar y fecha indicados al inicio del  documento.</p>
";

// Obtener las firmas guardadas
$query = "SELECT firma_empresa, firma_prospecto FROM contrato WHERE numero = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$resultado = $stmt->get_result();
$firmas = $resultado->fetch_assoc();

// Inicializar las variables de firma si no existen
$firma_empresa = isset($firmas['firma_empresa']) ? $firmas['firma_empresa'] : null;
$firma_prospecto = isset($firmas['firma_prospecto']) ? $firmas['firma_prospecto'] : null;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Contrato - <?php echo $contrato['vacante_titulo']; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <link rel="stylesheet" href="css/detalleContrato.css">
</head>

<body>
    <h1>
        Detalles del Contrato
    </h1>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Contrato de <?php echo $contrato['vacante_titulo']; ?></h2>
                <p>Empresa: <?php echo $contrato['empresa_nombre']; ?></p>
                <p>Salario: <?php echo $contrato['salario']; ?></p>
            </div>
        </div>
    </div>
    <?php include 'incluides/sidebar.php'; ?>

    <?php if (empty($contrato['firma_prospecto'])): ?>
        <div class="continue-button-container">
            <button class="continue-application" id="openSignatureModal">
                <div>
                    <div class="pencil"></div>
                    <div class="folder">
                        <div class="top">
                            <svg viewBox="0 0 24 27">
                                <path
                                    d="M1,0 L23,0 C23.5522847,-1.01453063e-16 24,0.44771525 24,1 L24,8.17157288 C24,8.70200585 23.7892863,9.21071368 23.4142136,9.58578644 L20.5857864,12.4142136 C20.2107137,12.7892863 20,13.2979941 20,13.8284271 L20,26 C20,26.5522847 19.5522847,27 19,27 L1,27 C0.44771525,27 6.76353751e-17,26.5522847 0,26 L0,1 C-6.76353751e-17,0.44771525 0.44771525,1.01453063e-16 1,0 Z">
                                </path>
                            </svg>
                        </div>
                        <div class="paper"></div>
                    </div>
                </div>
                Firmar Contrato
            </button>
        </div>
    <?php endif; ?>

    <hr style="border: 1px solid #000; margin: 20px;">

    <div id="contenidoContrato">
        <?php echo $contenido_contrato; ?>

        <div style='display: flex; justify-content: space-between; margin-top: 50px;'>
            <div style='text-align: center;'>
                <?php if (!empty($firma_empresa)): ?>
                    <img src="<?php echo htmlspecialchars($firma_empresa); ?>" alt="Firma del PATRÓN"
                        style="max-width: 200px; max-height: 100px;">
                <?php else: ?>
                    <p>Firma pendiente</p>
                <?php endif; ?>
                <div style='border-bottom: 1px solid black; width: 200px;'></div>
                <p>Representante de <?php echo $contrato['empresa_nombre']; ?></p>
            </div>
            <div style='text-align: center;'>
                <?php if (!empty($firma_prospecto)): ?>
                    <img src="<?php echo htmlspecialchars($firma_prospecto); ?>" alt="Firma del TRABAJADOR"
                        style="max-width: 200px; max-height: 100px;">
                <?php else: ?>
                    <p>Firma pendiente</p>
                <?php endif; ?>
                <div style='border-bottom: 1px solid black; width: 200px;'></div>
                <p><?php echo $contrato['prospecto_nombre'] . " " . $contrato['prospecto_primerApellido'] . " " . $contrato['prospecto_segundoApellido']; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="download-div">
        <hr style="border: 1px solid #000; margin: 20px;">
        <button onclick="descargarPDF()" class="download-button">
            <div class="docs">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"
                    stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Descargar PDF
            </div>
            <div class="download">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                    stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
            </div>
        </button>
    </div>

    <div id="signatureModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Firma del Contrato</h2>
            <p> *La firma debe ser lo mas parecido posible a la firma oficial</p>
            <div class="canvas-container">
                <canvas id="signatureCanvas" width="400" height="200"></canvas>
            </div>
            <div class="modal-buttons">
                <button id="saveSignature" class="btn-save-modal">Guardar firma</button>
                <button id="clearSignature" class="btn-clear-modal">Limpiar firma</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación antes de guardar la firma -->
    <div id="confirmSaveModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeConfirmModal">&times;</span>
            <h2>Confirmación</h2>
            <p>¿Está seguro de que desea guardar esta firma?</p>
            <button id="confirmSave">Sí, guardar firma</button>
            <button id="cancelSave">Cancelar</button>
        </div>
    </div>


    <script>
        var modal = document.getElementById('signatureModal');
        var btn = document.getElementById('openSignatureModal');
        var span = document.getElementsByClassName('close')[0];
        var canvas = document.getElementById('signatureCanvas');
        var signaturePad = new SignaturePad(canvas);

        // Modal de firma
        btn.onclick = function () {
            modal.style.display = 'block';
        }

        span.onclick = function () {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        document.getElementById('clearSignature').addEventListener('click', function () {
            signaturePad.clear();
        });

        // Modal de confirmación
        var confirmSaveModal = document.getElementById('confirmSaveModal');
        var closeConfirmModal = document.getElementById('closeConfirmModal');
        var confirmSaveButton = document.getElementById('confirmSave');
        var cancelSaveButton = document.getElementById('cancelSave');

        // Mostrar el modal de confirmación antes de guardar
        document.getElementById('saveSignature').addEventListener('click', function () {
            if (signaturePad.isEmpty()) {
                alert('Por favor, proporcione una firma antes de guardar.');
            } else {
                confirmSaveModal.style.display = 'block'; // Mostrar el modal de confirmación
            }
        });

        // Cerrar el modal de confirmación
        closeConfirmModal.addEventListener('click', function () {
            confirmSaveModal.style.display = 'none';
        });

        // Cancelar la acción de guardar
        cancelSaveButton.addEventListener('click', function () {
            confirmSaveModal.style.display = 'none';
        });

        // Confirmar y guardar la firma
        confirmSaveButton.addEventListener('click', function () {
            var signatureData = signaturePad.toDataURL();

            fetch('guardar_firma.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'firma_prospecto=' + encodeURIComponent(signatureData) + '&contrato_id=<?php echo $contrato_id; ?>'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Firma guardada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al guardar la firma: ' + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Error al guardar la firma');
                });

            confirmSaveModal.style.display = 'none'; // Cerrar el modal de confirmación
            modal.style.display = 'none'; // Cerrar el modal de firma
        });

        // Función para descargar el PDF (sin cambios)
        function descargarPDF() {
            var contenido = document.getElementById('contenidoContrato');
            var opciones = {
                margin: 1,
                filename: 'Contrato.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opciones).from(contenido).save();
        }
    </script>

</body>

</html>