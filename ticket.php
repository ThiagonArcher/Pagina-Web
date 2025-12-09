<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Compra</title>
    <style>
        /* ESTILO TIPO TICKET DE LUJO */
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');
        
        body {
            background-color: #555;
            display: flex;
            justify-content: center;
            padding-top: 20px;
            font-family: 'Courier Prime', monospace; /* Fuente tipo máquina de escribir */
        }
        .ticket {
            background: white;
            width: 300px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            position: relative;
        }
        /* Efecto de papel cortado */
        .ticket::after {
            content: "";
            position: absolute;
            left: 0px;
            bottom: -10px;
            width: 100%;
            height: 10px;
            background: radial-gradient(circle, transparent, transparent 50%, white 50%, white 100%) -7px -8px / 16px 16px repeat-x;
        }
        
        .logo { text-align: center; margin-bottom: 10px; }
        .logo h2 { margin: 0; color: #800000; text-transform: uppercase; border-bottom: 2px dashed #333; padding-bottom: 10px;}
        .info { text-align: center; font-size: 0.8em; margin-bottom: 15px; color: #555; }
        
        table { width: 100%; font-size: 0.85em; border-collapse: collapse; }
        td { padding: 5px 0; }
        .precio { text-align: right; }
        .linea-total { border-top: 2px dashed #000; font-weight: bold; font-size: 1.1em; padding-top: 10px; margin-top: 10px; }
        
        .footer { text-align: center; margin-top: 20px; font-size: 0.7em; color: #777; }
        .barcode { text-align: center; margin-top: 15px; font-family: 'Libre Barcode 39 Text', cursive; font-size: 2em;}

        /* Ocultar botones al imprimir */
        @media print {
            body { background: none; }
            .ticket { box-shadow: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="ticket" id="ticket-content">
        <div class="logo">
            <h2>Mi Tienda<br>Escolar</h2>
        </div>
        
        <div class="info">
            <p id="t-sucursal">Cargando...</p>
            <p id="t-fecha">Fecha: --/--/----</p>
            <p>Folio: #<span id="t-folio">000</span></p>
        </div>

        <table id="t-items">
            </table>

        <div class="linea-total" style="display:flex; justify-content:space-between;">
            <span>TOTAL:</span>
            <span id="t-total">$0.00</span>
        </div>

        <div class="footer">
            <p>¡GRACIAS POR SU COMPRA!</p>
            <p>Conserve este ticket para cualquier reclamación.</p>
            <p>www.mitiendaescolar.edu.mx</p>
        </div>
        
        <div class="barcode">|| ||| || |||| || |||</div>
    </div>

    <script>
        // RECUPERAR DATOS
        const datos = JSON.parse(localStorage.getItem('ultimo_ticket'));

        if (datos) {
            document.getElementById('t-sucursal').innerText = datos.sucursal;
            document.getElementById('t-fecha').innerText = datos.fecha;
            document.getElementById('t-folio').innerText = Math.floor(Math.random() * 1000000); // Folio random
            document.getElementById('t-total').innerText = datos.total;

            const tabla = document.getElementById('t-items');
            datos.items.forEach(item => {
                const subtotal = (item.precio * item.cantidad).toFixed(2);
                const row = `<tr>
                    <td colspan="2">${item.nombre}</td>
                </tr>
                <tr>
                    <td style="color:#555;">${item.cantidad} x $${item.precio}</td>
                    <td class="precio">$${subtotal}</td>
                </tr>`;
                tabla.innerHTML += row;
            });

            // IMPRIMIR AUTOMÁTICAMENTE
            setTimeout(() => {
                window.print();
            }, 500);
        } else {
            document.body.innerHTML = "<h1>Error: No hay ticket reciente.</h1>";
        }
    </script>

</body>
</html>