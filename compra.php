<?php
$host = "pgmaster";
$port = "5432";
$dbname = "tienda";
$user = "tienda_user";
$password = "tienda123";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    http_response_code(500);
    echo "Error de conexión a la base de datos.";
    exit;
}

// Establecer zona horaria
pg_query($conn, "SET TIMEZONE='America/Mexico_City'");

$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos || !isset($datos['carrito'])) {
    http_response_code(400);
    echo "Datos inválidos.";
    exit;
}

$id_sucursal = 1;

foreach ($datos['carrito'] as $item) {
    $id_producto = intval($item['id']);
    $cantidad = intval($item['cantidad']);

    // Obtener precio actual
    $resPrecio = pg_query_params($conn, "SELECT precio, stock FROM productos WHERE id=$1", [$id_producto]);
    $producto = pg_fetch_assoc($resPrecio);
    if (!$producto) continue; // si no existe el producto

    $precio = floatval($producto['precio']);
    $stock = intval($producto['stock']);

    if ($cantidad > $stock) {
        echo "Stock insuficiente para {$item['nombre']}";
        continue;
    }

    $total = $precio * $cantidad;

    // Insertar en ventas
    pg_query_params($conn,
        "INSERT INTO ventas (id_producto, id_sucursal, cantidad, total, fecha) VALUES ($1, $2, $3, $4, NOW())",
        [$id_producto, $id_sucursal, $cantidad, $total]
    );

    // Actualizar stock
    pg_query_params($conn, "UPDATE productos SET stock = stock - $1 WHERE id = $2", [$cantidad, $id_producto]);
}

pg_close($conn);
echo "Compra registrada ✅";
?>
