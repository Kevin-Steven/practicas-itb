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
	rol ENUM('estudiante', 'gestor', 'administrador') NOT NULL DEFAULT 'estudiante',
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carrera_id) REFERENCES carrera(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

CREATE TABLE registro (
	id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id int NOT NULL,
	cedula VARCHAR(20) NOT NULL,
	password VARCHAR(255) NOT NULL,
    CONSTRAINT fk_usuario_registro FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE recuperacion_clave (
	id INT AUTO_INCREMENT PRIMARY KEY,
	usuario_id INT NOT NULL,
	token VARCHAR(255) NOT NULL,
	expira DATETIME NOT NULL,
	FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
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
    documento_uno_id INT(11),
    lugar_laborado VARCHAR(255) NULL,
    periodo_tiempo_meses VARCHAR(255) NULL,
    funciones_realizadas TEXT NULL,
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
  hora_practicas INT(3)	 NOT NULL,
  documento_eva_s VARCHAR(255) NOT NULL,
  nota_eva_s DECIMAL(5,2)  NOT NULL,
  motivo_rechazo TEXT NULL,
  nombre_tutor_academico VARCHAR(255) NOT NULL,
  cedula_tutor_academico VARCHAR(255) NOT NULL,
  correo_tutor_academico VARCHAR(255) NOT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  -- ! 3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE + ciudad provincia, departamento, nombres_tutor_entidad, cargo_tutor_entidad, numero_telefono_tutor_entidad
  CREATE TABLE documento_tres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE',
  nombre_entidad_receptora VARCHAR(255) NOT NULL,
  departamento_entidad_receptora VARCHAR(255) NOT NULL,
  nombres_tutor_receptor VARCHAR(255) NOT NULL,
  cargo_tutor_receptor VARCHAR(255) NOT NULL,
  numero_telefono_tutor_receptor VARCHAR(10) NOT NULL,
  ciudad_entidad_receptora VARCHAR(255) NOT NULL,
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  CREATE TABLE documento_cuatro (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '4 PERFIL DE EGRESO DESARROLLO DE SOFTWARE',
  pdf_escaneado VARCHAR(255) NOT NULL,
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  -- ! 5 CARTA DE COMPROMISO - Se extrajo la ciudad y el nombre de la entidad receptora
  CREATE TABLE documento_cinco (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '5 CARTA DE COMPROMISO',
  ruc VARCHAR(13) NOT NULL,
  direccion_entidad_receptora VARCHAR(255) NOT NULL,
  logo_entidad_receptora VARCHAR(255) NOT NULL,
  nombre_representante_rrhh VARCHAR(255) NOT NULL,
  numero_institucional VARCHAR(10) NOT NULL,
  correo_institucional VARCHAR(255) NOT NULL,
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado') DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );

  -- ! 6 FICHA DE ENTIDAD RECEPTORA - SE Extrajo el nombre_tutor_entidad, cargo_tutor_entidad, numero_telefono_tutor_entidad
CREATE TABLE documento_seis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '6 FICHA DE ENTIDAD RECEPTORA',
  actividad_economica VARCHAR(255) NOT NULL,
  provincia VARCHAR(255) NOT NULL,
  jornada_laboral VARCHAR(255) NOT NULL,
  numero_practicas VARCHAR(255) NOT NULL,
  numero_institucional VARCHAR(20) DEFAULT 'NO APLICA',
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  motivo_rechazo TEXT NULL,	
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  
CREATE TABLE documento_siete (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '7 COMPROMISO ÉTICO DE RESPONSABILIDAD Y NORMAS DE SEGURIDAD PARA EL CUMPLIMIENTO DE PRÁCTICAS EN EL ENTORNO LABORAL REAL',
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  -- ! 8 INFORME DE ACTIVIDADES - SE Extrajo el departamento
 CREATE TABLE documento_ocho (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '8 INFORME DE ACTIVIDADES',
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado') DEFAULT 'Pendiente',
  fecha_subida TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE informe_actividades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  documento_ocho_id INT(11) NOT NULL,
  semana_inicio DATE NOT NULL,
  semana_fin DATE NOT NULL,
  horas_realizadas VARCHAR(255) NULL,
  actividades_realizadas TEXT NULL,
  FOREIGN KEY (documento_ocho_id) REFERENCES documento_ocho(id) ON DELETE CASCADE
);

CREATE TABLE documento_nueve (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id int(11) NOT NULL,
  nombre_doc VARCHAR(250) NOT NULL DEFAULT '9 EVALUACIÓN CONDUCTUAL DEL ESTUDIANTE',
	opcion_uno_puntaje INT CHECK (opcion_uno_puntaje BETWEEN 1 AND 5),
	opcion_dos_puntaje INT CHECK (opcion_dos_puntaje BETWEEN 1 AND 5),
	opcion_tres_puntaje INT CHECK (opcion_tres_puntaje BETWEEN 1 AND 5),
	opcion_cuatro_puntaje INT CHECK (opcion_cuatro_puntaje BETWEEN 1 AND 5),
	opcion_cinco_puntaje INT CHECK (opcion_cinco_puntaje BETWEEN 1 AND 5),
	opcion_seis_puntaje INT CHECK (opcion_seis_puntaje BETWEEN 1 AND 5),
	opcion_siete_puntaje INT CHECK (opcion_siete_puntaje BETWEEN 1 AND 5),
	opcion_ocho_puntaje INT CHECK (opcion_ocho_puntaje BETWEEN 1 AND 5),
	opcion_nueve_puntaje INT CHECK (opcion_nueve_puntaje BETWEEN 1 AND 5),
	opcion_diez_puntaje INT CHECK (opcion_diez_puntaje BETWEEN 1 AND 5),
	opcion_once_puntaje INT CHECK (opcion_once_puntaje BETWEEN 1 AND 5),
	opcion_doce_puntaje INT CHECK (opcion_doce_puntaje BETWEEN 1 AND 5),
	opcion_trece_puntaje INT CHECK (opcion_trece_puntaje BETWEEN 1 AND 5),
	opcion_catorce_puntaje INT CHECK (opcion_catorce_puntaje BETWEEN 1 AND 5),
	opcion_quince_puntaje INT CHECK (opcion_quince_puntaje BETWEEN 1 AND 5),
  motivo_rechazo TEXT NULL,
  estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
  fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );
  
  CREATE TABLE documento_diez (
	id INT AUTO_INCREMENT PRIMARY KEY,
	usuario_id int(11) NOT NULL,
	nombre_doc VARCHAR(250) NOT NULL DEFAULT '10 EVALUACIÓN FINAL DEL ESTUDIANTE EN EL ENTORNO LABORAL REAL',
	opcion_uno_puntaje INT CHECK (opcion_uno_puntaje BETWEEN 1 AND 5),
	opcion_dos_puntaje INT CHECK (opcion_dos_puntaje BETWEEN 1 AND 5),
	opcion_tres_puntaje INT CHECK (opcion_tres_puntaje BETWEEN 1 AND 5),
	opcion_cuatro_puntaje INT CHECK (opcion_cuatro_puntaje BETWEEN 1 AND 5),
	opcion_cinco_puntaje INT CHECK (opcion_cinco_puntaje BETWEEN 1 AND 5),
	opcion_seis_puntaje INT CHECK (opcion_seis_puntaje BETWEEN 1 AND 5),
	opcion_siete_puntaje INT CHECK (opcion_siete_puntaje BETWEEN 1 AND 5),
	opcion_ocho_puntaje INT CHECK (opcion_ocho_puntaje BETWEEN 1 AND 5),
	opcion_nueve_puntaje INT CHECK (opcion_nueve_puntaje BETWEEN 1 AND 5),
	opcion_diez_puntaje INT CHECK (opcion_diez_puntaje BETWEEN 1 AND 5),
	motivo_rechazo TEXT NULL,
	estado ENUM('Pendiente', 'Corregir', 'Aprobado')DEFAULT 'Pendiente',
	fecha_subida timestamp NOT NULL DEFAULT current_timestamp(),
	FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
  );

INSERT INTO cursos (paralelo) VALUES ('DH4-DL-A01C');
INSERT INTO carrera (carrera) VALUES ('Tecnología Superior en Desarrollo de software');
  
-- TRIGGERS --
DELIMITER $$
CREATE TRIGGER after_insert_documento_dos
AFTER INSERT ON documento_seis
FOR EACH ROW
BEGIN
    INSERT INTO documento_tres (usuario_id, nombre_doc, motivo_rechazo, estado, fecha_subida)
    VALUES (NEW.usuario_id, '3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE', NULL, 'Pendiente', NOW());
END$$
DELIMITER ;

DELIMITER $$

CREATE TRIGGER after_insert_documento_uno
AFTER INSERT ON documento_uno
FOR EACH ROW
BEGIN
    INSERT INTO documento_siete (usuario_id, nombre_doc, motivo_rechazo, estado,fecha_subida)
    VALUES (NEW.usuario_id, '7 COMPROMISO ÉTICO DE RESPONSABILIDAD Y NORMAS DE SEGURIDAD PARA EL CUMPLIMIENTO DE PRÁCTICAS EN EL ENTORNO LABORAL REAL',
			NULL,'Pendiente', NOW() );
END$$
DELIMITER ;
documento_uno
  
DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
	INSERT INTO registro (usuario_id, cedula, password)
	VALUES (NEW.id, NEW.cedula, NEW.password);
END$$
DELIMITER ;