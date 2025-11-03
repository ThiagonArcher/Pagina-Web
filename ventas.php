<?php
$conn = pg_connect("host=pgmaster port=5432 dbname=tienda user=tienda_user password=tienda123");
if (!$conn) { echo "Error al conectar"; exit; }
pg_query($conn, "SET TIMEZONE='America/Mexico_City'");

$resVentas = pg_query($conn, "
    SELECT v.id, p.nombre AS producto, v.cantidad, v.total, v.id_sucursal, v.fecha
    FROM ventas v
    JOIN productos p ON v.id_producto = p.id
    ORDER BY v.fecha DESC
");

if ($resVentas && pg_num_rows($resVentas) > 0) {
    echo "<h2>Historial de Ventas</h2>";
    echo "<table border='1'>
            <tr>
                <th>ID</th><th>Producto</th><th>Cantidad</th><th>Total</th><th>Sucursal</th><th>Fecha</th>
            </tr>";
    while ($v = pg_fetch_assoc($resVentas)) {
        echo "<tr>
                <td>{$v['id']}</td>
                <td>{$v['producto']}</td>
                <td>{$v['cantidad']}</td>
                <td>{$v['total']}</td>
                <td>{$v['id_sucursal']}</td>
                <td>{$v['fecha']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay ventas registradas.</p>";
}

pg_close($conn);
?>
