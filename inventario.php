<?php
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. CONEXIÓN
$sucursal_id = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 3;
switch ($sucursal_id) {
    case 1: $host = "db_norte"; $nombre_suc = "Sucursal Norte"; break;
    case 2: $host = "db_sur"; $nombre_suc = "Sucursal Sur"; break;
    case 3: default: $host = "db_centro"; $nombre_suc = "Sucursal Centro"; $sucursal_id = 3; break;
}
$conn = pg_connect("host=$host port=5432 dbname=tienda user=tienda_user password=tienda123");
$mensaje = "";
$tipo_mensaje = "";

// 3. PROCESAR ACCIONES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A) REABASTECER
    if (isset($_POST['accion']) && $_POST['accion'] === 'reabastecer') {
        $id_prod = intval($_POST['id_producto']);
        $cantidad_sumar = intval($_POST['cantidad']);
        if ($cantidad_sumar > 0) {
            $res = pg_query_params($conn, "UPDATE productos SET stock = stock + $1 WHERE id = $2", [$cantidad_sumar, $id_prod]);
            if ($res) { $mensaje = "✅ Stock actualizado."; $tipo_mensaje = "exito"; }
        }
    }

    // B) NUEVO PRODUCTO
    if (isset($_POST['accion']) && $_POST['accion'] === 'nuevo_producto') {
        $nombre = $_POST['nombre'];
        $desc = $_POST['descripcion'];
        $precio = floatval($_POST['precio']);
        $stock_inicial = intval($_POST['stock']);
        
        // Insertamos por defecto como activo=TRUE
        $query = "INSERT INTO productos (nombre, descripcion, precio, stock, id_sucursal, activo) VALUES ($1, $2, $3, $4, $5, TRUE)";
        $res = pg_query_params($conn, $query, [$nombre, $desc, $precio, $stock_inicial, $sucursal_id]);
        
        if ($res) {
            $mensaje = "✨ Producto creado exitosamente."; $tipo_mensaje = "exito";
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                $nombre_archivo = str_replace(' ', '_', strtolower(trim($nombre))) . ".jpg";
                move_uploaded_file($_FILES['imagen']['tmp_name'], $nombre_archivo);
            }
        } else {
            $mensaje = "❌ Error al crear producto."; $tipo_mensaje = "error";
        }
    }

    // C) "ELIMINAR" PRODUCTO (AHORA ES BAJA LÓGICA)
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_producto') {
        $id_prod = intval($_POST['id_producto']);
        
        // En lugar de DELETE, hacemos UPDATE para ocultarlo
        $res = pg_query_params($conn, "UPDATE productos SET activo = FALSE WHERE id = $1", [$id_prod]);
        
        if ($res) {
            $mensaje = "🗑️ Producto eliminado del catálogo (Historial preservado).";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "⚠️ Error al eliminar producto.";
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body { background-color: #f4f4f4; padding: 20px; font-family: sans-serif; }
        .panel-container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .header-panel { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .bloque-gestion { border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 30px; background: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; vertical-align: middle; }
        th { background: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .btn-crear { background: #007bff; color: white; padding: 10px; border: none; width: 100%; cursor: pointer; font-size: 1.1em; }
        .btn-sumar { background: #28a745; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; }
        .btn-borrar { background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; font-size: 1.2em; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .alert.exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="panel-container">
    <div class="header-panel">
        <h1>📦 Inventario: <?php echo $nombre_suc; ?></h1>
        <a href="admin.php" style="background:#555; color:white; padding:10px; text-decoration:none; border-radius:5px;">Volver</a>
    </div>

    <form method="GET" style="margin-bottom:20px; background:#eee; padding:10px; border-radius:5px;">
        <label><strong>Cambiar Sucursal:</strong></label>
        <select name="sucursal" onchange="this.form.submit()" style="padding:5px;">
            <option value="1" <?php if($sucursal_id==1) echo 'selected'; ?>>Norte</option>
            <option value="3" <?php if($sucursal_id==3) echo 'selected'; ?>>Centro</option>
            <option value="2" <?php if($sucursal_id==2) echo 'selected'; ?>>Sur</option>
        </select>
    </form>

    <?php if($mensaje) echo "<div class='alert $tipo_mensaje'>$mensaje</div>"; ?>

    <div class="bloque-gestion">
        <h2>📊 Lista de Productos (Activos)</h2>
        <?php
        // CAMBIO IMPORTANTE: Solo mostramos los activos (WHERE activo = TRUE)
        $res = pg_query($conn, "SELECT id, nombre, stock FROM productos WHERE activo = TRUE ORDER BY id DESC");
        
        if(pg_num_rows($res) > 0) {
            echo "<table>
                    <tr>
                        <th style='width: 40%;'>Producto</th>
                        <th style='width: 15%;'>Stock</th>
                        <th style='width: 30%;'>Reabastecer</th>
                        <th style='width: 15%; text-align:center;'>Eliminar</th>
                    </tr>";
            while($row = pg_fetch_assoc($res)) {
                echo "<tr>
                        <td>{$row['nombre']}</td>
                        <td><strong>{$row['stock']}</strong></td>
                        <td>
                            <form method='POST' style='display:flex; gap:5px;'>
                                <input type='hidden' name='accion' value='reabastecer'>
                                <input type='hidden' name='id_producto' value='{$row['id']}'>
                                <input type='number' name='cantidad' placeholder='+1' min='1' style='width:70px; margin:0;' required>
                                <button type='submit' class='btn-sumar'>➕</button>
                            </form>
                        </td>
                        <td style='text-align:center;'>
                            <form method='POST' onsubmit='return confirm(\"¿Ocultar {$row['nombre']} de la tienda?\\nEl historial de ventas SE CONSERVARÁ.\");'>
                                <input type='hidden' name='accion' value='eliminar_producto'>
                                <input type='hidden' name='id_producto' value='{$row['id']}'>
                                <button type='submit' class='btn-borrar' title='Eliminar Producto'>🗑️</button>
                            </form>
                        </td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay productos activos en esta sucursal.</p>";
        }
        ?>
    </div>

    <div class="bloque-gestion" style="border-top: 5px solid #007bff; background:#fbfbfb;">
        <h2 style="margin-top:0; color:#007bff;">✨ Registrar Nuevo Producto</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="nuevo_producto">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div><label>Nombre:</label><input type="text" name="nombre" required placeholder="Ej: Calculadora"></div>
                <div><label>Stock Inicial:</label><input type="number" name="stock" required value="10"></div>
                <div><label>Precio ($):</label><input type="number" name="precio" step="0.50" required placeholder="0.00"></div>
                <div><label>Imagen (.jpg):</label><input type="file" name="imagen" accept="image/jpeg" required></div>
                <div style="grid-column: span 2;"><label>Descripción:</label><textarea name="descripcion" required rows="2"></textarea></div>
            </div>
            <button type="submit" class="btn-crear" style="margin-top:15px;">Guardar Producto</button>
        </form>
    </div>
</div>
</body>
</html>