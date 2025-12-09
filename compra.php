<?php
// Recibir datos JSON del frontend
$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos || !isset($datos['carrito']) || !isset($datos['id_sucursal'])) {
    http_response_code(400);
    echo "Datos inválidos.";
    exit;
}

$id_sucursal = intval($datos['id_sucursal']);

// --- ENRUTAMIENTO DISTRIBUIDO ---
switch ($id_sucursal) {
    case 1: $host = "db_norte"; break;
    case 2: $host = "db_sur"; break;
    case 3: $host = "db_centro"; break;
    default:
        http_response_code(400);
        echo "Sucursal no válida.";
        exit;
}

// Configuración común
$port = "5432";
$dbname = "tienda";
$user = "tienda_user";
$password = "tienda123";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    http_response_code(500);
    echo "Error al conectar con la Sucursal seleccionada ($host).";
    exit;
}

pg_query($conn, "SET TIMEZONE='America/Mexico_City'");

// VARIABLES DE CONTROL
$mensajes_error = "";
$hubo_venta_exitosa = false;

// Procesar el carrito
foreach ($datos['carrito'] as $item) {
    $id_producto = intval($item['id']);
    $cantidad = intval($item['cantidad']);

    // 1. Verificar precio y stock
    $resPrecio = pg_query_params($conn, "SELECT precio, stock FROM productos WHERE id=$1", [$id_producto]);
    $producto = pg_fetch_assoc($resPrecio);
    
    if (!$producto) continue;

    $precio = floatval($producto['precio']);
    $stock = intval($producto['stock']);

    if ($cantidad > $stock) {
        // Guardamos el error pero NO decimos éxito todavía
        $mensajes_error .= "❌ Stock insuficiente para {$item['nombre']} (Disponibles: $stock).\n";
        continue; 
    }

    $total = $precio * $cantidad;

    // 2. Insertar venta
    $resInsert = pg_query_params($conn,
        "INSERT INTO ventas (id_producto, id_sucursal, cantidad, total, fecha) VALUES ($1, $2, $3, $4, NOW())",
        [$id_producto, $id_sucursal, $cantidad, $total]
    );

    // 3. Descontar inventario
    if ($resInsert) {
        pg_query_params($conn, "UPDATE productos SET stock = stock - $1 WHERE id = $2", [$cantidad, $id_producto]);
        $hubo_venta_exitosa = true; // ¡Marcamos que SÍ hubo una venta real!
    }
}

pg_close($conn);

// --- RESPUESTA FINAL LÓGICA ---
if ($hubo_venta_exitosa) {
    echo "✅ Compra procesada exitosamente.\n";
} else {
    echo "⚠️ No se pudo procesar la compra.\n";
}

// Si hubo errores (como falta de stock), los mostramos al final
if ($mensajes_error != "") {
    echo "\nDetalles:\n" . $mensajes_error;
}
?>