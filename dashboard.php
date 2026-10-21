


<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario'];

$consultaCama = $pdo->prepare("SELECT tamano_m2, cantidad_lombrices_inicial FROM camas_lombriz WHERE id_usuario = ? LIMIT 1");
$consultaCama->execute([$id_usuario]);
$cama = $consultaCama->fetch(PDO::FETCH_ASSOC);
$tamano_m2 = $cama ? $cama['tamano_m2'] : 1.00;
$cantidad_lombrices = $cama ? $cama['cantidad_lombrices_inicial'] : 2000;





$consultaSensorGlobal = $pdo->query("SELECT id_dispositivo FROM dispositivos_sensores ORDER BY id_dispositivo ASC LIMIT 1");
$sensor = $consultaSensorGlobal->fetch(PDO::FETCH_ASSOC);

$id_dispositivo = 0;
if ($sensor) {
    $id_dispositivo = $sensor['id_dispositivo'];
}

$lectura = null;
if ($id_dispositivo > 0) {
    $consultaLectura = $pdo->prepare("SELECT temperatura, humedad, nivel_lixiviado, fecha_hora FROM lecturas_sensores WHERE id_dispositivo = ? ORDER BY fecha_hora DESC LIMIT 1");
    $consultaLectura->execute([$id_dispositivo]);
    $lectura = $consultaLectura->fetch(PDO::FETCH_ASSOC);
}

if (!$lectura) {
    $lectura = [
        'temperatura' => 20.0,
        'humedad' => 70.0,
        'nivel_lixiviado' => 0.0,
        'fecha_hora' => date('Y-m-d H:i:s')
    ];
}

$ultima_alimentacion = date('Y-m-d', strtotime('-4 days'));
$ultima_recoleccion = date('Y-m-d', strtotime('-5 days'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Lombricontrol</title>
    <link rel="stylesheet" href="lombricontrol.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="pagina-dashboard">
    <!-- 67 -->
    <div class="app">
        <aside>
            <div class="logo"><img src="logo.png" alt="Logo"></div>
            <button class="nav" aria-current="page">INICIO</button>
            <button class="nav" onclick="document.getElementById('tareas').scrollIntoView({behavior:'smooth'})">TAREAS</button>
            <button class="nav">HISTORIAL</button>
            <button class="nav">AJUSTES</button>
            <button class="exit" title="Cerrar sesión" onclick="window.location.href='cerrar_sesion.php'">
                <svg viewBox="0 0 64 64" fill="none" stroke="#fff" stroke-width="5"><path d="M26 6H10v52h16"/><path d="M24 32h34M46 20l12 12-12 12"/></svg>
            </button>
        </aside>
        <main>
            <header>
                <h1 class="pill" id="pill">CARGANDO...</h1>
                <div class="user">
                    <i>❤︎</i>
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong><br>
                        <small>Cama de <?php echo $tamano_m2; ?> m² · <?php echo $cantidad_lombrices; ?> lombrices</small>
                    </div>
                </div>
            </header>

            <section class="panel">
                <div>
                    <h2>TEMPERATURA</h2>
                    <div class="card">
                        <div class="track">
                            <div class="bar"></div>
                            <div class="needle" id="agujaTemperatura"></div>
                        </div>
                        <div class="scale"><span>bajo</span><span>Ideal</span><span>Alto</span></div>
                    </div>
                    <div class="desc" id="descTemperatura"></div>
                </div>

                <div>
                    <h2>HUMEDAD</h2>
                    <div class="card">
                        <div class="track">
                            <div class="bar"></div>
                            <div class="needle" id="agujaHumedad"></div>
                        </div>
                        <div class="scale"><span>Seco</span><span>Ideal</span><span>Mojado</span></div>
                    </div>
                    <div class="desc" id="descHumedad"></div>
                </div>

                <div class="alert-card" id="tarjetaAlimento">
                    <div class="big" id="tituloAlimento">ALIMENTO</div>
                    <p id="infoAlimento"></p>
                    <button class="btn" id="botonAlimento" style="display:none;">Ya lo hice</button>
                </div>

                <div class="alert-card" id="tarjetaLixiviado">
                    <div class="big" id="tituloLixiviado">LIXIVIADO</div>
                    <div class="meter"><div id="barraLixiviado"></div></div>
                    <p id="infoLixiviado"></p>
                    <button class="btn" id="botonLixiviado" style="display:none;">Ya lo hice</button>
                </div>

                <div class="tasks" id="tareas">
                    <h2>TAREAS</h2>
                    <div class="card">
                        <ul>
                            <li><label><input type="checkbox"><span>Regado automatico</span></label></li>
                            <li><label><input type="checkbox"><span>Alimentacion</span></label></li>
                            <li><label><input type="checkbox"><span>Revisar sensor de humedad</span></label></li>
                        </ul>
                    </div>
                </div>

                <div class="stamp" id="sello"></div>
            </section>
        </main>
    </div>

    <script>
        const DATOS_BD = {
            temperatura: <?php echo $lectura['temperatura']; ?>,
            humedad: <?php echo $lectura['humedad']; ?>,
            nivel_lixiviado: <?php echo $lectura['nivel_lixiviado']; ?>,
            fecha_lectura: "<?php echo $lectura['fecha_hora']; ?>",
            tamano_m2: <?php echo $tamano_m2; ?>,
            cantidad_lombrices: <?php echo $cantidad_lombrices; ?>,
            ultima_alimentacion: "<?php echo $ultima_alimentacion; ?>",
            ultima_recoleccion: "<?php echo $ultima_recoleccion; ?>"
        };

        const COLORES = { rojo: '#eb4d62', amarillo: '#e8f374', verde: '#83a74d' };

        const TEMPERATURA = {
            puntos: [5, 15, 25, 35],
            zonas: [
                { maximo: 10, tipo: 'Muy fría', color: 'rojo', descripcion: 'Por debajo de 10 °C casi no comen.', accion: 'Cubre el cajón con una cobija.' },
                { maximo: 15, tipo: 'Fría', color: 'rojo', descripcion: 'Actividad lenta.', accion: 'Evita corrientes de aire.' },
                { maximo: 25, tipo: 'Ideal', color: 'amarillo', descripcion: 'Rango óptimo (15 a 25 °C).', accion: 'Manten tu caja en la sombra.' },
                { maximo: 30, tipo: 'Cálida', color: 'verde', descripcion: 'Se acercan al límite.', accion: 'Mueve tu lombricario a la sombra.' },
                { maximo: 99, tipo: 'Caliente', color: 'verde', descripcion: 'Riesgo de muerte.', accion: 'URGENCIA!!! sombra y ventilacion.' }
            ]
        };

        const HUMEDAD = {
            puntos: [40, 70, 85, 100],
            zonas: [
                { maximo: 50, tipo: 'Muy seca', color: 'rojo', descripcion: 'Se deshidratan rápido.', accion: 'ROCIA agua sin encharcar.' },
                { maximo: 70, tipo: 'Baja', color: 'rojo', descripcion: 'El sustrato se está secando.', accion: 'ROCIA un poco de agua.' },
                { maximo: 85, tipo: 'Correcta', color: 'amarillo', descripcion: 'Rango ideal (70 a 85 %).', accion: 'nada.' },
                { maximo: 90, tipo: 'Alta', color: 'verde', descripcion: 'Exceso moderado.', accion: 'Agrega material seco, como carton' },
                { maximo: 101, tipo: 'Exceso', color: 'verde', descripcion: 'Está encharcado.', accion: 'Mezcla material seco.' }
            ]
        };
        function obtenerZona(configuracion, valor) {
            return configuracion.zonas.find(zona => valor <= zona.maximo) || configuracion.zonas[configuracion.zonas.length - 1];
        }
        function obtenerPosicion(configuracion, valor) {
            const puntos = configuracion.puntos;
            const offsets = [0, 37.5, 62.5, 100];
            if (valor <= puntos[0]) return 0;
            if (valor >= puntos[3]) return 100;
            for (let i = 0; i < 3; i++) {
                if (valor <= puntos[i + 1]) {
                    return offsets[i] + (valor - puntos[i]) / (puntos[i + 1] - puntos[i]) * (offsets[i + 1] - offsets[i]);
                }
            }
            return 50;
        }





        function dibujarMedidor(configuracion, valor, idAguja, idDescripcion, nombre) {
            const zona = obtenerZona(configuracion, valor);
            document.getElementById(idAguja).style.left = obtenerPosicion(configuracion, valor) + '%';
            document.getElementById(idDescripcion).innerHTML = `
                <h3>${nombre} ${zona.tipo} <span class="badge" style="background:${COLORES[zona.color]}">${zona.tipo}</span></h3>
                <p>${zona.descripcion}</p>
                <p class="tip"><b>Que hacer:</b> ${zona.accion}</p>
            `;
            return zona;
        }

        
        function calcularDias(fechaTexto) {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const fecha = new Date(fechaTexto);
            fecha.setHours(0, 0, 0, 0);
            return Math.round((hoy - fecha) / 86400000);
        }

        function iniciar() {
            const temperatura = DATOS_BD.temperatura;
            const humedad = DATOS_BD.humedad;

            const zonaTemperatura = dibujarMedidor(TEMPERATURA, temperatura, 'agujaTemperatura', 'descTemperatura', 'Temperatura');
            const zonaHumedad = dibujarMedidor(HUMEDAD, humedad, 'agujaHumedad', 'descHumedad', 'Humedad');
            const indicador = document.getElementById('pill');
            const todoBien = zonaTemperatura.color === 'amarillo' && zonaHumedad.color === 'amarillo';
            indicador.className = 'pill ' + (todoBien ? 'ok' : 'warn');
            indicador.innerHTML = todoBien ? 'SISTEMA OPERANDO CORRECTAMENTE!!' : 'SISTEMA REQUIERE ATENCIÓN...';
            const diasSinAlimento = calcularDias(DATOS_BD.ultima_alimentacion);
            const tocaAlimentar = diasSinAlimento >= 3;
            const tarjetaAlimento = document.getElementById('tarjetaAlimento');
            const tituloAlimento = document.getElementById('tituloAlimento');
            const infoAlimento = document.getElementById('infoAlimento');
            const botonAlimento = document.getElementById('botonAlimento');







            if (tocaAlimentar) {
                tarjetaAlimento.classList.add('due');
                tituloAlimento.textContent = 'ALIMENTO REQUERIDO';
                infoAlimento.innerHTML = `Última vez: hace ${diasSinAlimento} días.<br>`;

                const racion = (DATOS_BD.cantidad_lombrices / 2000) * 1.0;
                infoAlimento.innerHTML += `medida sugerida: <b>${racion.toFixed(1)} kg</b> de residuos.`;
                botonAlimento.style.display = 'block';
            } else {
                tarjetaAlimento.classList.remove('due');
                tituloAlimento.textContent = 'NO REQUIEREN ALIMENTO';
                infoAlimento.innerHTML = `Ultima vez: hace ${diasSinAlimento} dias.`;
                botonAlimento.style.display = 'none';
            }








            const diasSinRecolectar = calcularDias(DATOS_BD.ultima_recoleccion);
            const nivel = DATOS_BD.nivel_lixiviado;
            const tocaRecolectar = nivel >= 1.0 || diasSinRecolectar >= 7;

            const tarjetaLixiviado = document.getElementById('tarjetaLixiviado');
            const tituloLixiviado = document.getElementById('tituloLixiviado');
            const infoLixiviado = document.getElementById('infoLixiviado');
            const barraLixiviado = document.getElementById('barraLixiviado');
            const botonLixiviado = document.getElementById('botonLixiviado');

            barraLixiviado.style.width = Math.min(100, (nivel / 1.0) * 100) + '%';

            if (tocaRecolectar) {
                tarjetaLixiviado.classList.add('due');
                tituloLixiviado.textContent = 'LIXIVIADO LISTO';
                infoLixiviado.innerHTML = `Nivel actual: <b>${nivel.toFixed(1)} L</b>.<br>ultima recoleccion: hace ${diasSinRecolectar} días.`;
                botonLixiviado.style.display = 'block';
            } else {
                tarjetaLixiviado.classList.remove('due');
                tituloLixiviado.textContent = 'LIXIVIADO NO ES SUFICIENTE';
                infoLixiviado.innerHTML = `Nivel actual: <b>${nivel.toFixed(1)} L</b>.<br>ultima recoleccion: hace ${diasSinRecolectar} dias.`;
                botonLixiviado.style.display = 'none';
            }

















            botonAlimento.onclick = () => { alert('Ya puedes consultarlo en tu historial :)'); };
            botonLixiviado.onclick = () => { alert('Ya puedes consultarlo en tu historial :)'); };

            document.getElementById('sello').textContent = `ultima lectura: ${DATOS_BD.fecha_lectura} · Cama #${DATOS_BD.tamano_m2}m²`;
        }

        document.addEventListener('DOMContentLoaded', iniciar);
    </script>
</body>
</html>