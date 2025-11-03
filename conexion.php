<?php
// Configuración de conexión
$host = "pgmaster";      // nombre del contenedor Docker
$port = "5432";
$dbname = "tienda";
$user = "tienda_user";   // usuario con permisos
$password = "tienda123";

// Conectar a PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("❌ Error al conectar a la base de datos: " . pg_last_error());
}

echo "<h2>✅ Conexión exitosa a la base de datos 'tienda'</h2>";

// --- Mostrar productos ---
$result = pg_query($conn, "SELECT id, nombre, descripcion, precio, stock FROM productos ORDER BY id");

if ($result && pg_num_rows($result) > 0) {
    echo "<h3>🛒 Productos disponibles:</h3>";
    echo "<table border='1' cellpadding='5'>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Stock</th>
            </tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['descripcion']}</td>
                <td>$ {$row['precio']}</td>
                <td>{$row['stock']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay productos registrados.</p>";
}

// --- Mostrar ventas ---
$resultVentas = pg_query($conn, "
    SELECT v.id, p.nombre AS producto, v.cantidad, v.total, v.id_sucursal, v.fecha
    FROM ventas v
    JOIN productos p ON v.id_producto = p.id
    ORDER BY v.fecha DESC
");

if ($resultVentas && pg_num_rows($resultVentas) > 0) {
    echo "<h3>📊 Historial de Ventas:</h3>";
    echo "<table border='1' cellpadding='5'>
            <tr>
                <th>ID Venta</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Total</th>
                <th>Sucursal</th>
                <th>Fecha</th>
            </tr>";
    while ($v = pg_fetch_assoc($resultVentas)) {
        echo "<tr>
                <td>{$v['id']}</td>
                <td>{$v['producto']}</td>
                <td>{$v['cantidad']}</td>
                <td>$ {$v['total']}</td>
                <td>{$v['id_sucursal']}</td>
                <td>{$v['fecha']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay ventas registradas.</p>";
}

// Cerrar conexión
pg_close($conn);
?>
