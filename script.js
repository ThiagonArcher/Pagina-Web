document.addEventListener('DOMContentLoaded', () => {
  const carrito = [];
  const carritoAside = document.getElementById('carrito');
  const listaCarrito = document.getElementById('lista-carrito');
  const totalCarrito = document.getElementById('total-carrito');
  const btnVaciar = document.querySelector('.btn-vaciar-carrito');
  const btnPagar = document.querySelector('.btn-comprar-carrito');
  const cartCount = document.querySelector('.cart-count');
  const cartIconContainer = document.querySelector('.cart-icon-container');
  const selectSucursal = document.getElementById('sucursal');
  const btnCerrarCarrito = document.getElementById('btn-cerrar-carrito');

  // REFERENCIAS AL NUEVO MODAL
  const modalExito = document.getElementById('modal-exito');
  const btnCerrarModal = document.getElementById('btn-cerrar-modal');
  const btnTicket = document.getElementById('btn-descargar-ticket');

  // --- INTERFAZ CARRITO ---
  cartIconContainer.addEventListener('click', () => carritoAside.classList.add('visible'));
  if(btnCerrarCarrito) btnCerrarCarrito.addEventListener('click', () => carritoAside.classList.remove('visible'));

  // --- AGREGAR PRODUCTOS ---
  document.querySelectorAll('.btn-agregar').forEach(boton => {
    boton.addEventListener('click', e => {
      const articulo = e.target.closest('.producto');
      const id = articulo.dataset.id;
      const nombre = articulo.dataset.nombre;
      const precio = parseFloat(articulo.dataset.precio);
      const stockMaximo = parseInt(articulo.dataset.stock);
      const cantidadInput = articulo.querySelector('.cantidad');
      const cantidad = parseInt(cantidadInput.value) || 1;

      if(cantidad <= 0) { alert("Cantidad inválida"); return; }
      if (validarStock(id, cantidad, stockMaximo)) {
        agregarAlCarrito(id, nombre, precio, cantidad);
        carritoAside.classList.add('visible');
      }
    });
  });

  function validarStock(id, cantidadNueva, stockMaximo) {
    const itemEnCarrito = carrito.find(item => item.id === id);
    const cantidadActual = itemEnCarrito ? itemEnCarrito.cantidad : 0;
    if ((cantidadActual + cantidadNueva) > stockMaximo) {
      alert(`⚠️ Solo hay ${stockMaximo} unidades disponibles.`);
      return false;
    }
    return true;
  }

  function agregarAlCarrito(id, nombre, precio, cantidad) {
    const existente = carrito.find(item => item.id === id);
    if (existente) existente.cantidad += cantidad;
    else carrito.push({ id, nombre, precio, cantidad });
    actualizarCarritoUI();
  }

  function actualizarCarritoUI() {
    listaCarrito.innerHTML = '';
    if (carrito.length === 0) {
      listaCarrito.innerHTML = '<li class="cart-empty-message">Vacío</li>';
      totalCarrito.textContent = '$0.00';
      btnVaciar.disabled = true; btnPagar.disabled = true;
      cartCount.textContent = '0'; return;
    }
    let total = 0, totalItems = 0;
    carrito.forEach(item => {
      const subtotal = item.precio * item.cantidad;
      total += subtotal; totalItems += item.cantidad;
      const li = document.createElement('li');
      li.innerHTML = `${item.nombre}<br><small>${item.cantidad} x $${item.precio.toFixed(2)} = <strong>$${subtotal.toFixed(2)}</strong></small><button class="btn-eliminar-item" data-id="${item.id}" style="float:right;color:red;cursor:pointer;">✖</button>`;
      listaCarrito.appendChild(li);
    });
    totalCarrito.textContent = `$${total.toFixed(2)}`;
    btnVaciar.disabled = false; btnPagar.disabled = false;
    cartCount.textContent = totalItems;
    
    document.querySelectorAll('.btn-eliminar-item').forEach(b => {
      b.addEventListener('click', e => {
        const idx = carrito.findIndex(i => i.id === e.target.dataset.id);
        if (idx !== -1) { carrito.splice(idx, 1); actualizarCarritoUI(); }
      });
    });
  }

  btnVaciar.addEventListener('click', () => { carrito.length = 0; actualizarCarritoUI(); });

  // --- PROCESO DE PAGO Y TICKET ---
  btnPagar.addEventListener('click', () => {
    if (carrito.length === 0) return;
    const id_sucursal = parseInt(selectSucursal.value);
    const nombre_sucursal = selectSucursal.options[selectSucursal.selectedIndex].text;

    fetch('compra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ carrito, id_sucursal })
    })
    .then(response => response.text())
    .then(data => {
      if(data.includes("exitosa")) {
        // 1. Guardamos datos del ticket en memoria del navegador
        const datosTicket = {
            fecha: new Date().toLocaleString(),
            sucursal: nombre_sucursal,
            items: carrito,
            total: document.getElementById('total-carrito').textContent
        };
        localStorage.setItem('ultimo_ticket', JSON.stringify(datosTicket));

        // 2. Mostramos el Modal Bonito
        modalExito.style.display = 'flex';
        carritoAside.classList.remove('visible');
      } else {
        alert(data); // Si hubo error (stock), usamos alert normal
      }
    });
  });

  // BOTONES DEL MODAL
  btnCerrarModal.addEventListener('click', () => {
    carrito.length = 0; actualizarCarritoUI();
    location.reload();
  });

  btnTicket.addEventListener('click', () => {
    // Abrir el generador de PDF en otra pestaña
    window.open('ticket.php', '_blank');
    // Luego recargamos la página principal
    setTimeout(() => { location.reload(); }, 1000);
  });
// --- TEMPORIZADOR DE OFERTAS ---
  function actualizarReloj() {
      const ahora = new Date();
      // Calculamos tiempo restante para el final del día (23:59:59)
      const finDia = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate(), 23, 59, 59);
      const diferencia = finDia - ahora;

      if (diferencia > 0) {
          const h = Math.floor((diferencia / (1000 * 60 * 60)) % 24);
          const m = Math.floor((diferencia / (1000 * 60)) % 60);
          const s = Math.floor((diferencia / 1000) % 60);

          const elH = document.getElementById('horas');
          const elM = document.getElementById('minutos');
          const elS = document.getElementById('segundos');

          if(elH && elM && elS) {
              elH.innerText = h < 10 ? '0' + h : h;
              elM.innerText = m < 10 ? '0' + m : m;
              elS.innerText = s < 10 ? '0' + s : s;
          }
      }
  }
  // Actualizar cada segundo
  setInterval(actualizarReloj, 1000);
  actualizarReloj(); // Ejecutar al inicio
});