<?php
// Script para crear la tabla de ofertas persistentes
$nodos = [
    1 => ["host" => "db_norte", "nombre" => "Norte"],
    2 => ["host" => "db_sur", "nombre" => "Sur"],
    3 => ["host" => "db_centro", "nombre" => "Centro"]
];

echo "<h2>🛠️ Configurando Sistema de Ofertas Diarias...</h2>";

foreach ($nodos as $id => $nodo) {
    $conn = pg_connect("host={$nodo['host']} port=5432 dbname=tienda user=tienda_user password=tienda123");
    
    if ($conn) {
        // Crear tabla si no existe
        $sql = "CREATE TABLE IF NOT EXISTS ofertas_del_dia (
            id SERIAL PRIMARY KEY,
            id_producto INTEGER NOT NULL,
            descuento INTEGER NOT NULL,
            fecha DATE DEFAULT CURRENT_DATE
        )";
        $res = pg_query($conn, $sql);
        
        if ($res) echo "<p style='color:green'>✅ {$nodo['nombre']}: Tabla de ofertas lista.</p>";
        else echo "<p style='color:red'>❌ {$nodo['nombre']}: Error al crear tabla.</p>";
        
        pg_close($conn);
    } else {
        echo "<p style='color:red'>⚠️ No se pudo conectar a {$nodo['nombre']}.</p>";
    }
}
echo "<p><strong>¡Listo! Ya puedes borrar este archivo.</strong></p>";
?>