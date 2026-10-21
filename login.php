<?php
session_start();
require 'conexion.php';

$error = '';
$vista = 'login';
$registroExitoso = isset($_GET['registro']) && $_GET['registro'] === 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion   = $_POST['accion'] ?? '';
    $usuario  = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['password'] ?? '';

    if ($accion === 'registro') {
        $vista  = 'registro';
        $tamano = trim($_POST['tamano'] ?? '');
        $tiempo = trim($_POST['tiempo'] ?? '');

        // Listas blancas: solo se aceptan estos valores
        $tamanosValidos = ['poco', 'medio', 'mucho'];
        $tiemposValidos = ['inicial', 'intermedio', 'veterano'];

        if ($usuario === '' || $contrasena === '' || $tamano === '' || $tiempo === '') {
            $error = 'Completa todos los campos y selecciona las opciones de tu cama.';
        } elseif (!in_array($tamano, $tamanosValidos, true) || !in_array($tiempo, $tiemposValidos, true)) {
            $error = 'Las opciones seleccionadas no son válidas.';
        } elseif (strlen($contrasena) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $consulta = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?');
            $consulta->execute([$usuario]);

            if ($consulta->fetch()) {
                $error = 'Ese nombre de usuario ya está registrado.';
            } else {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                // 1. Insertamos el usuario en la tabla usuarios
                $consulta = $pdo->prepare(
                    'INSERT INTO usuarios (nombre_usuario, password_hash, tamano, tiempo)
                     VALUES (?, ?, ?, ?)'
                );
                $consulta->execute([$usuario, $hash, $tamano, $tiempo]);

                // Obtenemos el ID del usuario recién registrado
                $idUsuarioNuevo = $pdo->lastInsertId();

                // 2. Traducimos el tamaño seleccionado a metros cuadrados y cantidad de lombrices
                $tamanoM2 = 1.00;
                $cantidadLombrices = 2000;

                if ($tamano === 'medio') {
                    $tamanoM2 = 2.00;
                    $cantidadLombrices = 4000;
                } elseif ($tamano === 'mucho') {
                    $tamanoM2 = 3.00;
                    $cantidadLombrices = 6000;
                }

                // 3. Fecha actual de registro para la cama
                $fechaInicio = date('Y-m-d');

                // Valores por defecto (el usuario luego los personaliza en el panel)
                $nombreIdentificador = 'Cama de ' . $usuario;
                $estadoActual = 'Activo';

                // 4. Insertamos automáticamente en la tabla camas_lombriz
                $consultaCama = $pdo->prepare(
                    'INSERT INTO camas_lombriz (id_usuario, nombre_identificador, tamano_m2, cantidad_lombrices_inicial, fecha_inicio_cuidado, estado_actual)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $consultaCama->execute([
                    $idUsuarioNuevo,
                    $nombreIdentificador,
                    $tamanoM2,
                    $cantidadLombrices,
                    $fechaInicio,
                    $estadoActual
                ]);

                // Redirigimos al login con éxito
                header('Location: login.php?registro=ok');
                exit;
            }
        }
    }

    if ($accion === 'login') {
        $consulta = $pdo->prepare(
            'SELECT id_usuario, nombre_usuario, password_hash FROM usuarios WHERE nombre_usuario = ?'
        );
        $consulta->execute([$usuario]);
        $datos = $consulta->fetch();

        if ($datos && password_verify($contrasena, $datos['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $datos['id_usuario'];
            $_SESSION['usuario']    = $datos['nombre_usuario'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Lombricontrol</title>
    <link rel="stylesheet" href="lombricontrol.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>

<body class="pagina-login">

    <!-- Vista de inicio de sesión -->
    <div class="contenedor-login <?= $vista === 'registro' ? 'oculto' : '' ?>" id="vista-login">
        <img src="logo.png" alt="logo">
        <h1>Iniciar Sesión</h1>

        <?php if ($registroExitoso): ?>
            <div class="mensaje mensaje-ok">¡Registro exitoso... Ya puedes iniciar sesión.</div>
        <?php endif; ?>
        <?php if ($error !== '' && $vista === 'login'): ?>
            <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="accion" value="login">
            <h2>USUARIO</h2>
            <input type="text" name="usuario" placeholder="Ej: lombricita67" required>
            <h2>CONTRASEÑA</h2>
            <input type="password" name="password" placeholder="••••••••" required>
            <button type="submit">CONTINUAR</button>
        </form>

        <h3>¿Sin cuenta? <a href="#" onclick="mostrarRegistro(event)">¡Haz click aquí!</a> Para cuidar tus lombrices.</h3>
    </div>

    <!-- Vista de registro -->
    <div class="layout-container <?= $vista === 'registro' ? '' : 'oculto' ?>" id="vista-registro">
        <div class="tarjeta-registro" id="tarjetaRegistro">
            <img src="logo.png" alt="Logo Lombricontrol">
            <h1>REGISTRO</h1>

            <?php if ($error !== '' && $vista === 'registro'): ?>
                <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="formRegistro">
                <input type="hidden" name="accion" value="registro">

                <h2>USUARIO</h2>
                <input type="text" name="usuario" placeholder="Ej: lombricita67" required>

                <h2>CONTRASEÑA</h2>
                <input type="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">

                <h2>TAMAÑO A MANEJAR</h2>

                <!-- Input visible: solo muestra el texto bonito, NO se envía -->
                <input
                    type="text"
                    id="tamanoVisible"
                    class="input-interactivo"
                    readonly
                    placeholder="Haz clic para seleccionar..."
                    onclick="desplegarPanel()"
                >

                <!-- Inputs ocultos: son los que SÍ se envían al servidor -->
                <input type="hidden" name="tamano" id="valorTamano" value="">
                <input type="hidden" name="tiempo" id="valorTiempo" value="">

                <button type="submit">REGISTRARSE</button>
            </form>

            <h3>
                <a href="#" onclick="mostrarLogin(event)">¡Ya tengo una cuenta!</a>
            </h3>
        </div>

        <!-- Panel de preguntas (derecha) -->
        <div class="panel-preguntas" id="panelPersonalizado">
            <!-- PASO 1 -->
            <div id="paso1">
                <span class="contador-paso">Pregunta 1 de 2</span>
                <h2 class="titulo-pregunta">TAMAÑO A MANEJAR</h2>
                <p class="descripcion-pregunta">
                    La cantidad de lombrices que utilices tendrán que tener un ambiente espacioso para que puedan sobrevivir y ser felices. Por favor, considera la siguiente información, <u><i>la información que muestre nuestra web dependerá de la cantidad de lombrices y el tamaño de tu contenedor.</i></u>
                </p>
                <div class="pregunta-principal">
                    ¿Cuánto residuo orgánico generas por semana? <span class="icono-info">i</span>
                </div>
                <div class="opciones-grid">
                    <div>
                        <button type="button" class="btn-opcion"
                                onclick="seleccionarResiduo('poco','Poco (1-2 KG)','2kg de lombrices, una cama de 1m²')">POCO</button>
                        <div class="subtexto-opcion">(1 - 2 KG)</div>
                    </div>
                    <div>
                        <button type="button" class="btn-opcion"
                                onclick="seleccionarResiduo('medio','Medio (3-5 KG)','4kg de lombrices, una cama de 2m²')">MEDIO</button>
                        <div class="subtexto-opcion">(3 - 5 KG)</div>
                    </div>
                    <div>
                        <button type="button" class="btn-opcion"
                                onclick="seleccionarResiduo('mucho','Mucho (+5 KG)','6kg+ de lombrices, una cama de 3m²')">MUCHO</button>
                        <div class="subtexto-opcion">+ 5 KG</div>
                    </div>
                </div>
                <div class="info-recomendacion">
                    <img src="imagen-registro.png" alt="Ilustración Abono">
                    <p id="textoRecomendacion">Selecciona una opción para ver la recomendación.</p>
                </div>
            </div>

            <!-- PASO 2 -->
            <div id="paso2" class="oculto">
                <span class="contador-paso">Pregunta 2 de 2</span>
                <h2 class="titulo-pregunta">TIEMPO DE CUIDADO</h2>
                <p class="descripcion-pregunta">
                    El tiempo de cuidado indicará el aproximado del resultado de tu lixiviado o humus. Así podrás saber cuándo retirar el producto. Para esto usaremos la fecha aproximada o exacta de inicio.
                </p>
                <div class="pregunta-principal">
                    ¿Cuánto tiempo llevas cuidando tus lombrices? <span class="icono-info">i</span>
                </div>
                <div class="opciones-columna">
                    <button type="button" class="btn-opcion-larga"
                            onclick="seleccionarTiempo('inicial','Voy empezando (0 a 2 semanas)')">VOY EMPEZANDO (0 A 2 SEMANAS)</button>
                    <button type="button" class="btn-opcion-larga"
                            onclick="seleccionarTiempo('intermedio','Intermedio (3 a 5 semanas)')">INTERMEDIO (3 A 5 SEMANAS)</button>
                    <button type="button" class="btn-opcion-larga"
                            onclick="seleccionarTiempo('veterano','Veteranas (+2 meses)')">VETERANAS (+2 MESES)</button>
                </div>
            </div>

            <!-- Botón siguiente -->
            <div class="footer-panel">
                <button type="button" class="btn-siguiente" id="btnSiguiente" onclick="siguientePaso()">
                    SIGUIENTE <span class="flecha-icono"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        let pasoActual = 1;
        let opcionResiduo = ""; // código: 'poco' | 'medio' | 'mucho'
        let opcionTiempo  = ""; // código: 'inicial' | 'intermedio' | 'veterano'

        function mostrarRegistro(evento) {
            if (evento) evento.preventDefault();
            document.getElementById('vista-login').classList.add('oculto');
            document.getElementById('vista-registro').classList.remove('oculto');
        }

        function mostrarLogin(evento) {
            if (evento) evento.preventDefault();
            document.getElementById('panelPersonalizado').classList.remove('activo');
            document.getElementById('vista-registro').classList.remove('con-panel');
            document.getElementById('tarjetaRegistro').style.transform = 'translateX(0)';
            document.getElementById('vista-registro').classList.add('oculto');
            document.getElementById('vista-login').classList.remove('oculto');
        }

        function desplegarPanel() {
            const panel = document.getElementById('panelPersonalizado');
            const tarjeta = document.getElementById('tarjetaRegistro');
            const diseno = document.getElementById('vista-registro');

            if (panel.classList.contains('activo')) return;

            panel.classList.add('activo');
            diseno.classList.add('con-panel');
            tarjeta.style.transform = 'translateX(-20px)';
        }

        // codigo = valor que se guarda en BD ('poco','medio','mucho')
        // etiqueta = texto bonito que se muestra en el input
        // recomendacion = texto de la caja verde
        function seleccionarResiduo(codigo, etiqueta, recomendacion) {
            opcionResiduo = codigo;

            document.getElementById('textoRecomendacion').innerHTML =
                `Necesitarás alrededor de <b>${recomendacion}</b>`;

            // Texto visible para el usuario
            document.getElementById('tamanoVisible').value = etiqueta;

            // Valor real que se envía al PHP
            document.getElementById('valorTamano').value = codigo;
        }

        function seleccionarTiempo(codigo, etiqueta) {
            opcionTiempo = codigo;

            // Agrega el tiempo al texto visible
            const visible = document.getElementById('tamanoVisible');
            visible.value = `${visible.value} | ${etiqueta}`;

            // Valor real que se envía al PHP
            document.getElementById('valorTiempo').value = codigo;

            alert("Registro de cama completo :)");

            setTimeout(() => {
                document.getElementById('panelPersonalizado').classList.remove('activo');
                document.getElementById('vista-registro').classList.remove('con-panel');
                document.getElementById('tarjetaRegistro').style.transform = 'translateX(0)';
                pasoActual = 1;
                document.getElementById('paso1').classList.remove('oculto');
                document.getElementById('paso2').classList.add('oculto');
                document.getElementById('btnSiguiente').style.display = 'flex';
            }, 400);
        }

        function siguientePaso() {
            if (pasoActual === 1) {
                if (!opcionResiduo) {
                    alert("Selecciona cuánto residuo generas por semana.");
                    return;
                }
                document.getElementById('paso1').classList.add('oculto');
                document.getElementById('paso2').classList.remove('oculto');
                document.getElementById('btnSiguiente').style.display = 'none';
                pasoActual = 2;
            }
        }
    </script>
</body>
</html>