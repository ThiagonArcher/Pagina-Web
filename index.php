<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda UGM</title>
  <link rel="stylesheet" href="estilos.css">
  
  <style>
    /* Estilos del Modal (Emergente) */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
    .modal-box { background: white; padding: 30px; border-radius: 15px; text-align: center; width: 90%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: popIn 0.3s ease; }
    .modal-icon { font-size: 3em; margin-bottom: 10px; }
    .modal-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
    .btn-modal { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: transform 0.2s; }
    .btn-modal:hover { transform: scale(1.05); }
    .btn-primary { background: #800000; color: white; }
    .btn-secondary { background: #ddd; color: #333; }
    @keyframes popIn { from {transform: scale(0.8); opacity: 0;} to {transform: scale(1); opacity: 1;} }

    /* ESTILO BOTÓN ADMIN GRANDE */
    .btn-admin-grande {
        position: absolute; top: 20px; left: 20px; 
        background-color: rgba(255,255,255,0.2); color: white; text-decoration: none; 
        font-size: 14px; font-weight: bold; padding: 10px 15px;
        border: 2px solid rgba(255,255,255,0.5); border-radius: 5px;
        transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;
    }
    .btn-admin-grande:hover { background-color: white; color: #800000; border-color: white; }
  </style>
</head>
<body>
  
  <?php
  // 1. SUCURSAL
  $sucursal_actual = isset($_GET['visitar']) ? intval($_GET['visitar']) : 3;
  switch ($sucursal_actual) {
      case 1: $host_db = "db_norte"; $nombre_tienda = "📍 Sucursal Norte"; break;
      case 2: $host_db = "db_sur"; $nombre_tienda = "📍 Sucursal Sur"; break;
      case 3: default: $host_db = "db_centro"; $nombre_tienda = "📍 Sucursal Centro"; $sucursal_actual = 3; break;
  }
  $conn = pg_connect("host=$host_db port=5432 dbname=tienda user=tienda_user password=tienda123");

  // 2. OFERTAS PERSISTENTES
  if ($conn) {
      $check = pg_query($conn, "SELECT count(*) FROM ofertas_del_dia WHERE fecha = CURRENT_DATE");
      if (pg_fetch_result($check, 0, 0) == 0) {
          $prods = pg_query($conn, "SELECT id FROM productos WHERE activo = TRUE ORDER BY RANDOM() LIMIT 4");
          if ($prods) {
              while ($p = pg_fetch_assoc($prods)) {
                  $desc = rand(10, 50);
                  pg_query($conn, "INSERT INTO ofertas_del_dia (id_producto, descuento) VALUES ({$p['id']}, $desc)");
              }
          }
      }
  }

  // 3. VARIABLES
  $busqueda = isset($_GET['q']) ? trim($_GET['q']) : "";
  $categoria = isset($_GET['cat']) ? $_GET['cat'] : "";
  $ofertas = isset($_GET['ofertas']);
  ?>

  <header>
    <h1>Tienda UGM</h1>
    <p>Estás comprando en: <strong><?php echo $nombre_tienda; ?></strong></p>
    
    <a href="login.php" class="btn-admin-grande">🔒 Panel Admin</a>

    <form method="GET" action="index.php" class="search-container">
        <input type="hidden" name="visitar" value="<?php echo $sucursal_actual; ?>">
        <select name="visitar" class="search-select" onchange="this.form.submit()">
            <option value="1" <?php if($sucursal_actual==1) echo 'selected'; ?>>Norte</option>
            <option value="3" <?php if($sucursal_actual==3) echo 'selected'; ?>>Centro</option>
            <option value="2" <?php if($sucursal_actual==2) echo 'selected'; ?>>Sur</option>
        </select>
        <input type="text" name="q" class="search-input" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off">
        <?php if($busqueda): ?><a href="index.php?visitar=<?php echo $sucursal_actual; ?>" class="btn-limpiar">✕</a><?php endif; ?>
        <button type="submit" class="search-button"><span class="search-icon">🔍</span></button>
    </form>
    
    <div class="cart-icon-container">
      <span class="cart-icon">🛒</span>
      <span class="cart-count">0</span>
    </div>
  </header>

  <nav class="nav-categorias">
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&ofertas=1" class="oferta-link">🔥 Ofertas del Día</a>
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=computacion">💻 Computación</a>
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=electronica">🔌 Electrónica</a>
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=ropa">👕 Ropa</a>
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=escolar">✏️ Materiales</a>
      <a href="index.php?visitar=<?php echo $sucursal_actual; ?>">Ver Todo</a>
  </nav>

  <aside id="carrito" class="carrito">
    <button id="btn-cerrar-carrito" class="btn-cerrar-x">✖</button>
    <h2>🛒 Carrito</h2>
    <ul id="lista-carrito"><li class="cart-empty-message">Vacío</li></ul>
    <div class="carrito-resumen">
      <label>Sucursal:</label>
      <select id="sucursal">
        <option value="1" <?php if($sucursal_actual==1) echo 'selected'; ?>>Norte</option>
        <option value="3" <?php if($sucursal_actual==3) echo 'selected'; ?>>Centro</option>
        <option value="2" <?php if($sucursal_actual==2) echo 'selected'; ?>>Sur</option>
      </select>
      <p>Total: <strong id="total-carrito">$0.00</strong></p>
      <button class="btn-comprar-carrito" disabled>Pagar</button>
      <button class="btn-vaciar-carrito" disabled>Vaciar</button>
    </div>
  </aside>

  <section>
    <?php if($ofertas): ?>
        <div class="banner-ofertas">
            <div class="banner-texto"><h2>⚡ Ofertas Relámpago (24hrs)</h2><p>Descuentos exclusivos hoy.</p></div>
            <div class="contador-box">
                Termina en: <div class="tiempo-unidad"><span class="tiempo-num" id="horas">00</span>HRS</div> : <div class="tiempo-unidad"><span class="tiempo-num" id="minutos">00</span>MIN</div> : <div class="tiempo-unidad"><span class="tiempo-num" id="segundos">00</span>SEG</div>
            </div>
        </div>
    <?php elseif($busqueda): ?>
        <h2>Resultados para: "<em><?php echo htmlspecialchars($busqueda); ?></em>"</h2>
    <?php elseif($categoria): ?>
        <h2>Categoría: <?php echo ucfirst($categoria); ?></h2>
    <?php else: ?>
        <h2>Catálogo Completo</h2>
    <?php endif; ?>

    <?php
    if (!$conn) { echo "<p>Error de conexión.</p>"; } else {
        $imagenes_viejas = ["Taza personalizada"=>"taza.jpg", "Playera estampada"=>"playera.jpg", "Cuaderno creativo"=>"cuaderno.jpg", "Lápices de colores"=>"lapices.jpg"];
        $sql = "SELECT p.id, p.nombre, p.precio, p.descripcion, p.stock, o.descuento FROM productos p LEFT JOIN ofertas_del_dia o ON p.id = o.id_producto AND o.fecha = CURRENT_DATE WHERE p.activo = TRUE ";
        $params = [];

        if ($busqueda) { $sql .= "AND p.nombre ILIKE $1 "; $params[] = "%{$busqueda}%"; }
        elseif ($ofertas) { $sql .= "AND o.descuento IS NOT NULL "; }
        elseif ($categoria) {
            switch($categoria) {
                case 'computacion': $sql .= "AND (p.nombre ILIKE '%laptop%' OR p.nombre ILIKE '%mouse%' OR p.nombre ILIKE '%usb%' OR p.nombre ILIKE '%teclado%')"; break;
                case 'electronica': $sql .= "AND (nombre ILIKE '%cable%' OR nombre ILIKE '%bocina%' OR nombre ILIKE '%audifonos%' OR nombre ILIKE '%steren%' OR nombre ILIKE '%cargador%')"; break;
                case 'ropa': $sql .= "AND (p.nombre ILIKE '%playera%' OR p.nombre ILIKE '%gorra%' OR p.nombre ILIKE '%sudadera%')"; break;
                case 'escolar': $sql .= "AND (p.nombre ILIKE '%cuaderno%' OR p.nombre ILIKE '%lapiz%' OR p.nombre ILIKE '%mochila%' OR p.nombre ILIKE '%taza%')"; break;
            }
        }
        $sql .= "ORDER BY o.descuento DESC NULLS LAST, p.id ASC";
        $res = empty($params) ? pg_query($conn, $sql) : pg_query_params($conn, $sql, $params);
        
        if ($res && pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $nombre_archivo = str_replace(' ', '_', strtolower(trim($row['nombre']))) . ".jpg";
                $img = file_exists($nombre_archivo) ? $nombre_archivo : ($imagenes_viejas[$row['nombre']] ?? "default.jpg");
                
                $precio_original = floatval($row['precio']);
                $precio_mostrar = $precio_original;
                $tiene_descuento = false;
                $porcentaje = 0;

                if ($row['descuento']) {
                    $tiene_descuento = true;
                    $porcentaje = intval($row['descuento']);
                    $precio_mostrar = $precio_original - ($precio_original * $porcentaje / 100);
                }
                $sinStock = ($row['stock'] <= 0);
                
                echo '<article class="producto" style="'.($sinStock?'opacity:0.6':'').'" data-id="'.$row['id'].'" data-nombre="'.$row['nombre'].'" data-precio="'.$precio_mostrar.'" data-stock="'.$row['stock'].'">';
                echo '<h3>'.$row['nombre'].'</h3><img src="'.$img.'" style="object-fit:cover; height:200px; width:200px; border-radius:5px;"><p>'.$row['descripcion'].'</p>';
                if ($tiene_descuento) {
                    echo '<p class="precio"><span class="precio-anterior">$'.number_format($precio_original,2).'</span> <span class="precio-oferta">$'.number_format($precio_mostrar,2).'</span> <span class="badge-descuento">-'.$porcentaje.'%</span></p>';
                } else {
                    echo '<p class="precio"><strong>$'.number_format($precio_original,2).'</strong></p>';
                }
                echo '<p class="stock" style="color:'.($sinStock?'red':'green').';">Disponibles: '.$row['stock'].'</p>';
                echo '<div class="compra-controles"><label>Cant:</label><input type="number" class="cantidad" value="1" min="1" '.($sinStock?'disabled':'').'><button class="btn-agregar" '.($sinStock?'disabled style="background:#ccc"':'').'>'.($sinStock?'Agotado':'Agregar').'</button></div></article>';
            }
        } else { echo "<p style='width:100%; text-align:center;'>❌ No hay productos.</p>"; }
        pg_close($conn);
    }
    ?>
  </section>

  <div id="modal-exito" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-icon">✅</div><h2>¡Compra Exitosa!</h2><p>Tu pedido ha sido procesado.</p>
      <div class="modal-buttons"><button id="btn-cerrar-modal" class="btn-modal btn-secondary">Aceptar</button><button id="btn-descargar-ticket" class="btn-modal btn-primary">📄 Ticket PDF</button></div>
    </div>
  </div>

  <footer>
    <div class="footer-container">
        
        <div class="footer-col">
            <h3><span class="ugm-icon">🎓</span> Tienda UGM</h3>
            <p>Proyecto académico web.<br>Universidad del Golfo de México.</p>
        </div>

        <div class="footer-col">
            <h3>Secciones</h3>
            <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&ofertas=1">Ofertas</a>
            <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=computacion">Computación</a>
            <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=electronica">Electrónica</a>
            <a href="index.php?visitar=<?php echo $sucursal_actual; ?>&cat=ropa">Ropa</a>
            <a href="index.php?visitar=<?php echo $sucursal_actual; ?>">Ver Todo</a>
        </div>

        <div class="footer-col">
            <h3>Contacto</h3>
            <p>Campus Minatitlán<br>
            soporte@tiendaugm.mx<br>
            (922) 276-76-73</p>
        </div>

        <div class="footer-col">
            <h3>Síguenos</h3>
            <div class="social-icons">
                <a href="https://www.facebook.com/ugminatitlan/" target="_blank" title="Facebook">📘</a>
                <a href="https://www.instagram.com/ugmminainsta/" target="_blank" title="Instagram">📷</a>
                <a href="https://web.ugm.mx/veracruz/minatitlan/" target="_blank" title="Página Oficial">🎓</a>
            </div>
        </div>
    </div>

    <div class="copyright">
        <p>© 2025 Tienda UGM - Somos tu mejor opción</p>
    </div>
  </footer>

  <script src="script.js"></script>
  <script>
  function actualizarReloj() {
      const ahora = new Date(); const finDia = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate(), 23, 59, 59); const diff = finDia - ahora;
      if (diff > 0) {
          const h = Math.floor((diff/(1000*60*60))%24); const m = Math.floor((diff/(1000*60))%60); const s = Math.floor((diff/1000)%60);
          if(document.getElementById('horas')) { document.getElementById('horas').innerText = h<10?'0'+h:h; document.getElementById('minutos').innerText = m<10?'0'+m:m; document.getElementById('segundos').innerText = s<10?'0'+s:s; }
      }
  } setInterval(actualizarReloj, 1000); actualizarReloj();
  </script>
</body>
</html>