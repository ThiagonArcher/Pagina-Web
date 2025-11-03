document.addEventListener('DOMContentLoaded', () => {
  const carrito = [];
  const carritoAside = document.getElementById('carrito');
  const listaCarrito = document.getElementById('lista-carrito');
  const totalCarrito = document.getElementById('total-carrito');
  const btnVaciar = document.querySelector('.btn-vaciar-carrito');
  const btnPagar = document.querySelector('.btn-comprar-carrito');
  const cartCount = document.querySelector('.cart-count');
  const cartIconContainer = document.querySelector('.cart-icon-container');

  // --- Mostrar/Ocultar carrito ---
  cartIconContainer.addEventListener('click', () => {
    carritoAside.classList.toggle('visible');
  });

  // --- Agregar productos ---
  document.querySelectorAll('.btn-agregar').forEach(boton => {
    boton.addEventListener('click', e => {
      const articulo = e.target.closest('.producto');
      const id = articulo.dataset.id;
      const nombre = articulo.dataset.nombre;
      const precio = parseFloat(articulo.dataset.precio);
      const cantidadInput = articulo.querySelector('.cantidad');
      const cantidad = parseInt(cantidadInput.value);

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
      li.innerHTML = `
        ${item.nombre} - ${item.cantidad} x $${item.precio.toFixed(2)} = $${subtotal.toFixed(2)}
        <button class="btn-eliminar-item" data-id="${item.id}">✖</button>
      `;
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

  // --- Vaciar carrito ---
  btnVaciar.addEventListener('click', () => {
    carrito.length = 0;
    actualizarCarrito();
  });

  // --- Enviar compra al servidor ---
document.addEventListener('DOMContentLoaded', () => {
  const carrito = [];
  const carritoAside = document.getElementById('carrito');
  const listaCarrito = document.getElementById('lista-carrito');
  const totalCarrito = document.getElementById('total-carrito');
  const btnVaciar = document.querySelector('.btn-vaciar-carrito');
  const btnPagar = document.querySelector('.btn-comprar-carrito');
  const cartCount = document.querySelector('.cart-count');
  const cartIconContainer = document.querySelector('.cart-icon-container');

  // --- Mostrar/Ocultar carrito ---
  cartIconContainer.addEventListener('click', () => {
    carritoAside.classList.toggle('visible');
  });

  // --- Agregar productos ---
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

  // --- Función para agregar al carrito ---
  function agregarAlCarrito(id, nombre, precio, cantidad) {
    const existente = carrito.find(item => item.id === id);
    if (existente) {
      existente.cantidad += cantidad;
    } else {
      carrito.push({ id, nombre, precio, cantidad });
    }
    actualizarCarrito();
  }

  // --- Actualizar vista del carrito ---
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
      li.innerHTML = `
        ${item.nombre} - ${item.cantidad} x $${item.precio.toFixed(2)} = $${subtotal.toFixed(2)}
        <button class="btn-eliminar-item" data-id="${item.id}">✖</button>
      `;
      listaCarrito.appendChild(li);
    });

    totalCarrito.textContent = `$${total.toFixed(2)}`;
    btnVaciar.disabled = false;
    btnPagar.disabled = false;
    cartCount.textContent = totalItems;

    // --- Eventos para eliminar producto ---
    document.querySelectorAll('.btn-eliminar-item').forEach(boton => {
      boton.addEventListener('click', e => {
        const id = e.target.dataset.id;
        eliminarDelCarrito(id);
      });
    });
  }

  // --- Eliminar producto individual ---
  function eliminarDelCarrito(id) {
    const index = carrito.findIndex(item => item.id === id);
    if (index !== -1) {
      carrito.splice(index, 1);
      actualizarCarrito();
    }
  }

  // --- Vaciar carrito ---
  btnVaciar.addEventListener('click', () => {
    carrito.length = 0;
    actualizarCarrito();
  });

  // --- Enviar compra al servidor ---
function actualizarVentas() {
    fetch('ventas.php')
    .then(resp => resp.text())
    .then(html => {
        document.getElementById('historial-ventas').innerHTML = html;
    });
}

// Llamamos al cargar la página para mostrar ventas actuales
actualizarVentas();

// Dentro de btnPagar después de registrar la compra:
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
        actualizarVentas(); // <--- esto refresca las ventas sin recargar la página
    })
    .catch(err => {
        alert('Error al procesar la compra');
        console.error(err);
    });
});

});
 // <-- Cierra document.addEventListener correctamente
 });