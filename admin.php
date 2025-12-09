<?php
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. RECIBIR FILTROS (Sucursal y Fechas)
$vista_actual = isset($_GET['ver_sucursal']) ? intval($_GET['ver_sucursal']) : 3;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01'); // Por defecto: 1ro del mes actual
$fecha_fin    = isset($_GET['fecha_fin'])    ? $_GET['fecha_fin']    : date('Y-m-d');  // Por defecto: Hoy

// Selección de Sucursal
switch ($vista_actual) {
    case 1: $host_db = "db_norte"; $nombre_sucursal = "Sucursal Norte"; break;
    case 2: $host_db = "db_sur"; $nombre_sucursal = "Sucursal Sur"; break;
    case 3: default: $host_db = "db_centro"; $nombre_sucursal = "Sucursal Centro"; $vista_actual = 3; break;
}

$conn = pg_connect("host=$host_db port=5432 dbname=tienda user=tienda_user password=tienda123");
$mensaje = "";

// Lógica de Reinicio de Ofertas
if (isset($_POST['accion']) && $_POST['accion'] === 'reset_ofertas') {
    if ($conn) {
        pg_query($conn, "DELETE FROM ofertas_del_dia WHERE fecha = CURRENT_DATE");
        $mensaje = "🔄 ¡Ofertas reiniciadas! Se generarán nuevas al azar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - Admin</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body { background-color: #f0f0f0; font-family: sans-serif; }
        .admin-header { background-color: #333; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #ff4d4d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .dashboard { max-width: 1000px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        .acciones-panel { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-inventario { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
        .btn-ofertas { background-color: #ff9800; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 1em; }
        
        /* Formulario de Filtros */
        .filtros-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
            border: 1px solid #ccc;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 0.85em; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input, .form-group select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-filtrar { background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; height: 35px; }
        .btn-filtrar:hover { background-color: #0056b3; }

        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #800000; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .alert { padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }

        /* CABECERA SOLO PARA IMPRESIÓN */
        .print-header { display: none; text-align: center; margin-bottom: 20px; }
        .print-header h2 { margin: 0; color: black; }
        .print-header p { margin: 5px 0; color: #555; font-size: 0.9em; }

        /* --- ESTILOS DE IMPRESIÓN (PDF) --- */
        @media print {
            body { background-color: white; margin: 0; padding: 0; font-size: 12pt; }
            .admin-header, .acciones-panel, .btn-logout, .filtros-box, .btn-print, .alert, hr { display: none !important; }
            .dashboard { box-shadow: none; border: none; padding: 0; margin: 0; width: 100%; max-width: 100%; }
            
            /* Mostrar encabezado especial en PDF */
            .print-header { display: block; } 
            
            /* Tabla limpia para papel */
            table { border: 2px solid #000; width: 100%; }
            th { background-color: #ddd !important; color: black !important; border: 1px solid #000; font-weight: bold; }
            td { border: 1px solid #000; color: black; }
            tr:nth-child(even) { background-color: #fff !important; }
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>🛠️ Panel de Administrador</h1>
        <div>
            <span>Usuario: <strong>Admin</strong></span> | 
            <a href="index.php" style="color:#ddd; margin-right:10px;">Ver Tienda</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="dashboard">
        
        <?php if($mensaje): ?>
            <div class="alert"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="acciones-panel">
            <a href="inventario.php" class="btn-inventario">📦 Gestionar Inventario</a>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="reset_ofertas">
                <button type="submit" class="btn-ofertas">⚡ Cambiar Ofertas del Día</button>
            </form>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 style="margin:0;">Reporte de Ventas</h2>
            <button onclick="window.print()" class="btn-print" style="background:#6c757d; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; font-weight:bold;">
                🖨️ Imprimir PDF
            </button>
        </div>
        
        <form method="GET" action="admin.php" class="filtros-box">
            <div class="form-group">
                <label>Sucursal:</label>
                <select name="ver_sucursal">
                    <option value="1" <?php if($vista_actual==1) echo 'selected'; ?>>📍 Norte</option>
                    <option value="3" <?php if($vista_actual==3) echo 'selected'; ?>>📍 Centro</option>
                    <option value="2" <?php if($vista_actual==2) echo 'selected'; ?>>📍 Sur</option>
                </select>
            </div>

            <div class="form-group">
                <label>Desde:</label>
                <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" required>
            </div>

            <div class="form-group">
                <label>Hasta:</label>
                <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>" required>
            </div>

            <button type="submit" class="btn-filtrar">🔎 Filtrar Reporte</button>
        </form>

        <div id="area-impresion">
            
            <div class="print-header">
                <h2>REPORTE DE VENTAS - TIENDA UGM</h2>
                <p><strong>Sucursal:</strong> <?php echo $nombre_sucursal; ?></p>
                <p><strong>Periodo:</strong> Del <?php echo date("d/m/Y", strtotime($fecha_inicio)); ?> al <?php echo date("d/m/Y", strtotime($fecha_fin)); ?></p>
                <hr style="border-top:1px dashed #000;">
            </div>

            <?php
            if (!$conn) {
                echo "<p style='color:red; font-weight:bold;'>Error: No se pudo conectar a la base de datos.</p>";
            } else {
                // CONSULTA FILTRADA POR FECHAS
                // Usamos parámetros $1 y $2 para seguridad y formato de fecha
                $query = "
                    SELECT v.id, p.nombre AS producto, v.cantidad, v.total, v.fecha 
                    FROM ventas v
                    JOIN productos p ON v.id_producto = p.id
                    JOIN sucursales s ON v.id_sucursal = s.id
                    WHERE v.fecha >= $1 AND v.fecha <= $2
                    ORDER BY v.fecha DESC
                ";
                
                // Agregamos horas para cubrir todo el día seleccionado
                $inicio_sql = $fecha_inicio . " 00:00:00";
                $fin_sql    = $fecha_fin . " 23:59:59";

                $resVentas = pg_query_params($conn, $query, array($inicio_sql, $fin_sql));

                if ($resVentas && pg_num_rows($resVentas) > 0) {
                    echo "<table>
                            <thead>
                                <tr>
                                    <th style='width:10%'>ID</th>
                                    <th style='width:40%'>Producto</th>
                                    <th style='width:10%'>Cant.</th>
                                    <th style='width:20%'>Fecha</th>
                                    <th style='width:20%'>Total</th>
                                </tr>
                            </thead>
                            <tbody>";
                    
                    $gran_total = 0;
                    $total_items = 0;

                    while ($v = pg_fetch_assoc($resVentas)) {
                        $gran_total += $v['total'];
                        $total_items += $v['cantidad'];
                        // Formato de fecha bonito
                        $fecha_format = date("d/m/Y H:i", strtotime($v['fecha']));
                        
                        echo "<tr>
                                <td>{$v['id']}</td>
                                <td>{$v['producto']}</td>
                                <td style='text-align:center;'>{$v['cantidad']}</td>
                                <td>{$fecha_format}</td>
                                <td>$" . number_format($v['total'], 2) . "</td>
                              </tr>";
                    }
                    echo "</tbody>
                          <tfoot>
                            <tr style='background:#ffeb3b; font-weight:bold;'>
                                <td colspan='2' style='text-align:right;'>TOTAL PERIODO:</td>
                                <td style='text-align:center;'>$total_items</td>
                                <td></td>
                                <td>$" . number_format($gran_total, 2) . "</td>
                            </tr>
                          </tfoot>
                          </table>";
                } else {
                    echo "<p style='padding:40px; text-align:center; color:#666; font-size:1.2em;'>
                            🚫 No se encontraron ventas en el rango de fechas seleccionado.
                          </p>";
                }
                pg_close($conn);
            }
            ?>
        </div>
    </div>

</body>
</html>