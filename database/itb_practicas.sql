CREATE DATABASE IF NOT EXISTS itb_practicas;
USE itb_practicas;

CREATE TABLE carrera (
	id INT AUTO_INCREMENT PRIMARY KEY,
    carrera VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

CREATE TABLE cursos (
	id INT AUTO_INCREMENT PRIMARY KEY,
    paralelo VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

CREATE TABLE usuarios (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nombres VARCHAR(100) NOT NULL,
	apellidos VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	cedula VARCHAR(20) NOT NULL UNIQUE,
	direccion VARCHAR(255) NOT NULL,		
	telefono VARCHAR(20) NOT NULL,
	convencional VARCHAR(20) DEFAULT NULL,
	carrera_id INT NOT NULL,
	curso_id INT DEFAULT NULL,
	password VARCHAR(255) NOT NULL,
	foto_perfil VARCHAR(255),
    periodo VARCHAR(255) DEFAULT NULL,
	rol ENUM('estudiante', 'gestor') NOT NULL DEFAULT 'estudiante',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carrera_id) REFERENCES carrera(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);


CREATE TABLE registro (
	id INT AUTO_INCREMENT PRIMARY KEY,
	cedula VARCHAR(20) NOT NULL,
	password VARCHAR(255) NOT NULL
);

CREATE TABLE documento_uno (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '1 FICHA DE ESTUDIANTE',
  promedio_notas DECIMAL(5,2) NOT NULL,
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado') DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  CREATE TABLE experiencia_laboral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento_uno_id INT(11) NOT NULL,
    lugar_laborado VARCHAR(255) NOT NULL,
    periodo_tiempo_meses VARCHAR(255) NOT NULL,
    funciones_realizadas TEXT NOT NULL,
    FOREIGN KEY (documento_uno_id) REFERENCES documento_uno(id) ON DELETE CASCADE
);

CREATE TABLE documento_dos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '2 PLAN DE APRENDIZAJE PRACTICO Y DE ROTACION',
  fecha_inicio DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  fecha_fin DATE NOT NULL,
  hora_fin TIME NOT NULL,
  hora_practicas INT(3) NOT NULL,
  documento_eva_s VARCHAR(255) NOT NULL,
  nota_eva_s INT(3) NOT NULL,
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
INSERT INTO cursos (paralelo) VALUES ('DH4-DL-A01C');
INSERT INTO carrera (carrera) VALUES ('Tecnología Superior en Desarrollo de software');
  
  DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
	INSERT INTO registro (cedula, password) 
	VALUES (NEW.cedula, NEW.password);
END$$
DELIMITER ;