<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Tienda Escolar</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>
  <header>
    <h1>Mi Tienda Escolar</h1>
    <p>Productos hechos por estudiantes</p>
    <div class="cart-icon-container">
      <span class="cart-icon">🛒</span>
      <span class="cart-count">0</span>
    </div>
  </header>

  <aside id="carrito" class="carrito">
    <h2>🛒 Carrito de Compras</h2>
    <ul id="lista-carrito">
      <li class="cart-empty-message">El carrito está vacío.</li>
    </ul>
    <div class="carrito-resumen">
      <p>Total: <strong id="total-carrito">$0.00</strong></p>
      <button class="btn-comprar-carrito" disabled>Proceder a Pagar</button>
      <button class="btn-vaciar-carrito" disabled>Vaciar Carrito</button>
    </div>
  </aside>

  <section>
    <h2>Catálogo de Productos</h2>

    <?php
    // Conexión a PostgreSQL
$conn = pg_connect("host=pgmaster port=5432 dbname=tienda user=tienda_user password=tienda123");

    if (!$conn) {
        echo "<p style='color:red;'>❌ Error al conectar a la base de datos: " . pg_last_error() . "</p>";
    } else {
        $imagenes = [
            "Taza personalizada" => "taza.jpg",
            "Playera estampada" => "playera.jpg",
            "Cuaderno creativo" => "cuaderno.jpg",
            "Lápices de colores" => "lapices.jpg",
        ];

        $res = pg_query($conn, "SELECT id, nombre, precio, descripcion FROM productos");
        if ($res && pg_num_rows($res) > 0) {
            while ($row = pg_fetch_assoc($res)) {
                $img = $imagenes[$row['nombre']] ?? "default.jpg";
                echo '<article class="producto" data-id="'.$row['id'].'" data-nombre="'.$row['nombre'].'" data-precio="'.$row['precio'].'">';
                echo '<h3>'.$row['nombre'].'</h3>';
                echo '<img src="'.$img.'" alt="'.$row['nombre'].'">';
                echo '<p>'.$row['descripcion'].'</p>';
                echo '<p class="precio"><strong>Precio:</strong> $<span class="product-price">'.$row['precio'].'</span></p>';
                echo '<div class="compra-controles">';
                echo '<label for="cantidad-'.$row['id'].'">Cantidad:</label>';
                echo '<input type="number" id="cantidad-'.$row['id'].'" class="cantidad" value="1" min="1">';
                echo '<button class="btn-agregar">Agregar al Carrito</button>';
                echo '</div></article>';
            }
        } else {
            echo "<p>No hay productos disponibles.</p>";
        }
    }
    ?>

  </section>

  <section>
    <section id="historial-ventas">
  <!-- Aquí se cargará el historial dinámicamente -->
</section>

    <h2>Historial de Ventas</h2>
    <?php
    if ($conn) {
        // Asegurar zona horaria
        pg_query($conn, "SET TIMEZONE='America/Mexico_City'");

        $resVentas = pg_query($conn, "
            SELECT v.id, p.nombre AS producto, v.cantidad, v.total, v.fecha, v.id_sucursal
            FROM ventas v
            JOIN productos p ON v.id_producto = p.id
            ORDER BY v.fecha DESC
        ");

        if ($resVentas && pg_num_rows($resVentas) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Sucursal</th>
                        <th>Fecha</th>
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
    }
    ?>
  </section>

  <footer>
    <p>Contacto: tiendaescolar@email.com</p>
  </footer>

  <script>
document.addEventListener('DOMContentLoaded', () => {
  const carrito = [];
  const carritoAside = document.getElementById('carrito');
  const listaCarrito = document.getElementById('lista-carrito');
  const totalCarrito = document.getElementById('total-carrito');
  const btnVaciar = document.querySelector('.btn-vaciar-carrito');
  const btnPagar = document.querySelector('.btn-comprar-carrito');
  const cartCount = document.querySelector('.cart-count');
  const cartIconContainer = document.querySelector('.cart-icon-container');

  cartIconContainer.addEventListener('click', () => {
    carritoAside.classList.toggle('visible');
  });

  document.querySelectorAll('.btn-agregar').forEach(boton => {
    boton.addEventListener('click', e => {
      const articulo = e.target.closest('.producto');
      const id = articulo.dataset.id;
      const nombre = articulo.dataset.nombre;
      const precio = parseFloat(articulo.dataset.precio);
      const cantidadInput = articulo.querySelector('.cantidad');
      const cantidad = parseInt(cantidadInput.value) || 1;
      agregarAlCarrito(id, nombre, precio, cantidad);
    });
  });

  function agregarAlCarrito(id, nombre, precio, cantidad) {
    const existente = carrito.find(item => item.id === id);
    if (existente) {
      existente.cantidad += cantidad;
    } else {
      carrito.push({ id, nombre, precio, cantidad });
    }
    actualizarCarrito();
  }

  function actualizarCarrito() {
    listaCarrito.innerHTML = '';
    if (carrito.length === 0) {
      listaCarrito.innerHTML = '<li class="cart-empty-message">El carrito está vacío.</li>';
      totalCarrito.textContent = '$0.00';
      btnVaciar.disabled = true;
      btnPagar.disabled = true;
      cartCount.textContent = '0';
      return;
    }

    let total = 0;
    let totalItems = 0;
    carrito.forEach(item => {
      const subtotal = item.precio * item.cantidad;
      total += subtotal;
      totalItems += item.cantidad;

      const li = document.createElement('li');
      li.innerHTML = `${item.nombre} - ${item.cantidad} x $${item.precio.toFixed(2)} = $${subtotal.toFixed(2)}
        <button class="btn-eliminar-item" data-id="${item.id}">✖</button>`;
      listaCarrito.appendChild(li);
    });

    totalCarrito.textContent = `$${total.toFixed(2)}`;
    btnVaciar.disabled = false;
    btnPagar.disabled = false;
    cartCount.textContent = totalItems;

    document.querySelectorAll('.btn-eliminar-item').forEach(boton => {
      boton.addEventListener('click', e => {
        const id = e.target.dataset.id;
        eliminarDelCarrito(id);
      });
    });
  }

  function eliminarDelCarrito(id) {
    const index = carrito.findIndex(item => item.id === id);
    if (index !== -1) {
      carrito.splice(index, 1);
      actualizarCarrito();
    }
  }

  btnVaciar.addEventListener('click', () => {
    carrito.length = 0;
    actualizarCarrito();
  });

  btnPagar.addEventListener('click', () => {
    if (carrito.length === 0) return;

    fetch('compra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ carrito })
    })
    .then(response => response.text())
    .then(data => {
      alert('Gracias por tu compra 🛍️\n' + data);
      carrito.length = 0;
      actualizarCarrito();
      location.reload(); // <-- recargar para actualizar ventas
    })
    .catch(err => {
      alert('Error al procesar la compra');
      console.error(err);
    });
  });
});
  </script>
</body>
</html>
