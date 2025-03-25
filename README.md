# 📌 Sistema de Prácticas Profesionales para el ITB

📄 **Descripción**  
Este sistema permite la gestión digital del proceso de prácticas preprofesionales en el **Instituto Superior Tecnológico Bolivariano de Tecnología (ITB)**. Desarrollado con PHP y MySQL, facilita a estudiantes, gestores y administradores el manejo completo de documentos y seguimiento del avance.  
Incluye funcionalidades como generación automática de PDFs, asignación de cursos, revisión de documentos y recuperación de cuentas mediante correo electrónico.

🚧 **Estado del proyecto**  
En desarrollo — Se continúan agregando módulos y funcionalidades.

---

## 🚀 Funcionalidades principales

- 🗂️ Creación de cursos por parte del gestor para asignación de estudiantes
- 🔁 Recuperación de cuenta vía correo/cedula con token temporal (válido por 10 minutos)
- 🔐 Autenticación de usuarios (estudiantes, gestores, administradores)
- 📄 Registro y carga de documentos por parte del estudiante
- ✅ Aprobación, corrección o rechazo de documentos por parte de los gestores
- 📥 Generación automática de documentos en PDF (usando TCPDF)
- 📊 Panel de seguimiento del estado de cada documento
- 🔔 Notificaciones tipo *toast* según el estado del envío

---

## 🧱 Estructura del Proyecto

- 📂 **TCPDF-main** → Generación de archivos PDF
- 📂 **PHPMailer** → Envío de correos electrónicos 
- 📂 **app** → Código principal de la aplicación 
- 📂 **admin** → Panel de administración
- 📂 **cerrar-sesion** → Lógica para cerrar sesión
- 📂 **config** → Configuración de la base de datos y constantes del sistema
- 📂 **email** → Envío de correos personalizados (opcional)
- 📂 **estudiante** → Módulo para el estudiante (gestión de documentos, formularios, etc.)
- 📂 **gestor** → Módulo de gestión de prácticas para usuarios con rol gestor
- 📂 **js** → Scripts JavaScript personalizados
- 📂 **photos** → Fotos de perfil de los usuarios
- 📂 **registrar** → Registro y validación de nuevos estudiantes
- 📂 **uploads** → Archivos subidos por los usuarios
- 📂 **database** → Script SQL de creación de tablas y relaciones (itb_practicas.sql) 
- 📂 **images** → Archivos e imágenes del sistema (logos, íconos, etc.) 
- 📜 **index.php** → Página principal del sistema (login y redirección por rol) 


---

## 🛠️ Tecnologías usadas

- **PHP 8.2**
- **MySQL**
- **HTML5 / CSS3 / Bootstrap**
- **JavaScript / Toasts**
- **TCPDF** – Para generación de documentos PDF
- **PHPMailer** – (opcional) Para envío de correos


---

## 🧪 Cómo instalar  
1. Clona el repositorio  
2. Crea una base de datos `itb_practicas` y ejecuta el archivo `itb_practicas.sql`  
3. Configura tu archivo de conexión en `config/config.php`  
4. Abre `index.php` en tu navegador local  


---

## 👨‍💻 **Autores**  
- **Kevin Barzola Villamar**
- **Angelo Barzola Villamar**


