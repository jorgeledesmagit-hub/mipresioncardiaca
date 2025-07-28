<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== "jorgeledesma") {
    header("Location: login.php");
    exit();
}

// Configuración de la base de datos
$host = 'localhost';
$usuario = 'root';
$contraseña = '';
$basedatos = 'control_presion';

// Conexión a la base de datos
$conn = new mysqli($host, $usuario, $contraseña, $basedatos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


// Función para guardar registro con hora automática
function guardarRegistro($conn, $sistolica, $diastolica, $pulso, $observaciones) {
    // Obtener la hora actual del servidor
    $fecha = date('Y-m-d H:i:s');
    
    // Preparar la consulta SQL
    $sql = "INSERT INTO presion_arterial (fecha_registro, sistolica, diastolica, pulso, observaciones) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fecha, $sistolica, $diastolica, $pulso, $observaciones);
    
    return $stmt->execute();
}

function borrarRegistro($conn, $id) {
    $sql = "DELETE FROM presion_arterial WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function identificarValoresAnormales($conn) {
    $sql = "SELECT * FROM presion_arterial 
            WHERE sistolica > 130 OR sistolica < 90 
            OR diastolica > 90 OR diastolica < 60";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function calcularPromedios($conn) {
    $sql = "SELECT AVG(sistolica) as prom_sistolica, AVG(diastolica) as prom_diastolica, 
            AVG(pulso) as prom_pulso FROM presion_arterial";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accion']) && $_POST['accion'] == 'borrar') {
        $resultado = borrarRegistro($conn, $_POST['id']);
        if ($resultado) {
            header('Location: index.php?mensaje=Borrado+exitoso');
            exit;
        }
    } else {
        $sistolica = $_POST['sistolica'];
        $diastolica = $_POST['diastolica'];
        $pulso = $_POST['pulso'];
        $observaciones = $_POST['observaciones'];
        if (guardarRegistro($conn, $sistolica, $diastolica, $pulso, $observaciones)) {
            header('Location: index.php?mensaje=Registro+exitoso');
            exit;
        }
    }
}

// Obtener datos para mostrar
$resultado = $conn->query("SELECT COUNT(*) as total FROM presion_arterial");
$datos = $resultado->fetch_assoc();

$resultado = $conn->query("SELECT * FROM presion_arterial ORDER BY fecha_registro DESC");
$filas = $resultado->fetch_all(MYSQLI_ASSOC);

$promedios = calcularPromedios($conn);
$valoresAnormales = identificarValoresAnormales($conn);

// Cerrar conexión
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Control de Presión Arterial</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
</div>
    <div class="container" style="margin-top: 40px;">
        <h1 style="display: flex; justify-content: space-between; align-items: center;">
            <span>Control de Presión Arterial</span>
            <a href="logout.php" class="btn-logout" style="margin-left: 20px;">Cerrar sesión</a>
        </h1>
        
        <!-- Mensaje de estado -->
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Formulario de registro -->
       <!-- Formulario modificado -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Sistolica (mmHg):</label>
        <input type="number" name="sistolica" required min="60" max="200">
    </div>
    <div class="form-group">
        <label>Diastolica (mmHg):</label>
        <input type="number" name="diastolica" required min="40" max="120">
    </div>
    <div class="form-group">
        <label>Pulso (ppm):</label>
        <input type="number" name="pulso" required min="40" max="200">
    </div>
    <div class="form-group">
        <label>Observaciones:</label>
        <textarea name="observaciones"></textarea>
    </div>
    <button type="submit">Guardar Registro</button>
</form>

<!-- Visualización de registros -->
<div class="registros">
    <h2>Registros</h2>
    <table>
        <tr>
            <th>Hora</th>
            <th>Sistolica</th>
            <th>Diastolica</th>
            <th>Pulso</th>
            <th>Observaciones</th>
        </tr>
        <?php foreach ($filas as $fila): ?>
        <tr>
            <td><?php echo $fila['fecha_registro']; ?></td>
            <td><?php echo $fila['sistolica']; ?></td>
            <td><?php echo $fila['diastolica']; ?></td>
            <td><?php echo $fila['pulso']; ?></td>
            <td><?php echo htmlspecialchars($fila['observaciones']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
        <!-- Promedios -->
        <?php if (isset($promedios)): ?>
        <div class="promedios">
            <h2>Promedios</h2>
            <p>Sistolica: <?php echo number_format($promedios['prom_sistolica'], 2); ?> mmHg</p>
            <p>Diastolica: <?php echo number_format($promedios['prom_diastolica'], 2); ?> mmHg</p>
            <p>Pulso: <?php echo number_format($promedios['prom_pulso'], 2); ?> ppm</p>
        </div>
        <?php endif; ?>

        <!-- Registros anormales -->
        <?php if (isset($valoresAnormales) && count($valoresAnormales) > 0): ?>
        <div class="registros-anormales">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <h2 style="margin: 0;">Registros Anormales</h2>
            </div>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Sistolica</th>
                    <th>Diastolica</th>
                    <th>Pulso</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
                <?php foreach ($valoresAnormales as $registro): ?>
                <tr class="anormal">
                    <td><?php echo $registro['fecha_registro']; ?></td>
                    <td><?php echo $registro['sistolica']; ?></td>
                    <td><?php echo $registro['diastolica']; ?></td>
                    <td><?php echo $registro['pulso']; ?></td>
                    <td><?php echo $registro['observaciones']; ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                            <input type="hidden" name="accion" value="borrar">
                            <input type="hidden" name="id" value="<?php echo $registro['id']; ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<style>
/* Estilos generales */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Formulario */
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

input[type="number"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    min-height: 100px;
}

button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

/* Tabla de registros */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
}

/* Registros anormales */
.anormal {
    background-color: #ffe6e6;
}

/* Promedios */
.promedios {
    margin-top: 20px;
    padding: 15px;
    background-color: #e8f5e9;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Mensajes */
.mensaje {
    padding: 10px;
    background-color: #d4edda;
    color: #155724;
    border-radius: 4px;
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    input[type="number"], textarea {
        font-size: 14px;
    }
    
    button {
        padding: 8px 15px;
        font-size: 14px;
    }
}
/* Estilos para la hora */
.hora-registro {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

/* Estilos para la tabla */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
}

.btn-logout {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s;
    display: inline-block;
    margin-left: 10px;
}
.btn-logout:hover {
    background-color: #0056b3;
}

#logout-area:hover #logout-btn {
    display: inline-block !important;
}
#logout-area {
    /* área invisible para detectar el hover */
    background: transparent;
}
</style>