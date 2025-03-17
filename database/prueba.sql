create database prueba;
use prueba;
CREATE TABLE usuarios (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nombres VARCHAR(100) NOT NULL,
	apellidos VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	cedula VARCHAR(20) NOT NULL UNIQUE,
	direccion VARCHAR(255) NOT NULL,		
	telefono VARCHAR(20) NOT NULL,
	whatsapp VARCHAR(20) NOT NULL,
	carrera VARCHAR(100) NOT NULL,
	password VARCHAR(255) NOT NULL,
	pareja_tesis INT default 0,
	foto_perfil VARCHAR(255),
	fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    orcid VARCHAR(255) DEFAULT NULL,
	rol ENUM('postulante', 'administrador', 'gestor', 'docente') NOT NULL DEFAULT 'postulante'
);

CREATE TABLE registro (
	id INT AUTO_INCREMENT PRIMARY KEY,
	cedula VARCHAR(20) NOT NULL,
	password VARCHAR(255) NOT NULL
);

CREATE TABLE documento_uno (
  id int(11) NOT NULL AUTO_INCREMENT,
  usuario_id int(11) NOT NULL,
  nombres varchar(255) NOT NULL,
  apellidos varchar(255) NOT NULL,
  cedula varchar(10) NOT NULL,
  telefono varchar(10) NOT NULL,
  convencional varchar(9) NOT NULL,
  direccion_docimilio varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  estado varchar(100) DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY usuario_id (usuario_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
	INSERT INTO registro (cedula, password) 
	VALUES (NEW.cedula, NEW.password);
END$$
DELIMITER ;