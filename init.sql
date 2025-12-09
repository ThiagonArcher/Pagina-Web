CREATE TABLE IF NOT EXISTS sucursales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    id_sucursal INT REFERENCES sucursales(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS ventas (
    id SERIAL PRIMARY KEY,
    id_producto INT NOT NULL REFERENCES productos(id),
    id_sucursal INT NOT NULL REFERENCES sucursales(id),
    cantidad INT NOT NULL,
    total DECIMAL(10,2),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sucursales (nombre, ciudad, direccion) VALUES 
('Sucursal Norte', 'Monterrey', 'Av. Siempre Viva 123'),
('Sucursal Sur', 'Cancún', 'Blvd. Kukulcan Km 12'),
('Sucursal Centro', 'CDMX', 'Av. Reforma 222');

INSERT INTO productos (nombre, descripcion, precio, stock, id_sucursal) VALUES
('Taza personalizada', 'Taza de cerámica con diseño', 150.00, 50, 1),
('Playera estampada', 'Playera algodón 100%', 250.00, 30, 2),
('Cuaderno creativo', 'Cuaderno pasta dura', 80.00, 100, 3),
('Lápices de colores', 'Caja de 24 colores', 120.00, 200, 3);

