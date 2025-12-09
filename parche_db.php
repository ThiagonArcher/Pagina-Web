<?php
// Script para actualizar la estructura de la base de datos sin borrar datos
$nodos = [
    1 => ["host" => "db_norte", "nombre" => "Norte"],
    2 => ["host" => "db_sur", "nombre" => "Sur"],
    3 => ["host" => "db_centro", "nombre" => "Centro"]
];

echo "<h2>🛠️ Aplicando parche de 'Baja Lógica' a las Bases de Datos...</h2>";

foreach ($nodos as $id => $nodo) {
    $conn = pg_connect("host={$nodo['host']} port=5432 dbname=tienda user=tienda_user password=tienda123");
    
    if ($conn) {
        // Comando SQL para agregar la columna 'activo' si no existe
        $sql = "ALTER TABLE productos ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE";
        $res = pg_query($conn, $sql);
        
        if ($res) {
            echo "<p style='color:green'>✅ {$nodo['nombre']}: Estructura actualizada correctamente.</p>";
        } else {
            echo "<p style='color:red'>❌ {$nodo['nombre']}: Error al actualizar.</p>";
        }
        pg_close($conn);
    } else {
        echo "<p style='color:red'>⚠️ No se pudo conectar a {$nodo['nombre']}.</p>";
    }
}
echo "<p><strong>Listo. Ya puedes borrar este archivo y seguir con los siguientes pasos.</strong></p>";
?>