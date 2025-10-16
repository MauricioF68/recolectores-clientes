## Sobre EcoRuta (Proyecto Recolectores)

**EcoRuta** es una aplicación web diseñada para conectar a los ciudadanos (clientes) con los recolectores de residuos de su comunidad. Nuestra misión es optimizar y facilitar el proceso de recolección de basura, fomentando un manejo de residuos más eficiente y responsable. ♻️

Esta plataforma permite a los usuarios registrarse, solicitar recolecciones, ver la ubicación de los recolectores cercanos y gestionar sus servicios de manera sencilla y efectiva. El proyecto está construido con el framework Laravel, utilizando PHP y MySQL.

### ✨ Características Principales

  - **Registro de Usuarios**: Perfiles separados para **Clientes** y **Recolectores**.
  - **Solicitud de Servicios**: Los clientes pueden programar recolecciones de residuos.
  - **Panel de Control**: Cada tipo de usuario tiene un panel personalizado para gestionar sus actividades.
  - **Sistema de Notificaciones**: Alertas para confirmar servicios y mantener informados a los usuarios.
  - **Mapa Interactivo (Próximamente)**: Para visualizar rutas y ubicaciones en tiempo real.


## 🚀 Guía de Instalación Local (con XAMPP)

Sigue estos pasos detalladamente para desplegar el proyecto en tu computadora local.

### ✅ Prerrequisitos

Antes de empezar, asegúrate de tener instalado lo siguiente:

1.  **XAMPP**: Incluye Apache y MySQL. [Descargar aquí](https://www.apachefriends.org/es/index.html).
2.  **Composer**: El manejador de dependencias para PHP. [Descargar aquí](https://getcomposer.org/download/).
3.  **Git**: Para clonar el repositorio. [Descargar aquí](https://git-scm.com/downloads).
3.  **Node.js y npm**: Para compilar los archivos de front-end. [Descargar aquí](https://nodejs.org/es).


### 🔧 Pasos de Configuración

**1. Iniciar los servicios de XAMPP**

  - Abre el **Panel de Control de XAMPP**.
  - Inicia los módulos de **Apache** y **MySQL**. Deberían aparecer en color verde.

**2. Clonar el Repositorio**

  - Abre una terminal o consola de comandos (como Git Bash o CMD).
  - Navega hasta la carpeta `htdocs` dentro de tu directorio de instalación de XAMPP (ej. `C:/xampp/htdocs`).
  - Clona el proyecto con el siguiente comando:
    ```bash
    git clone https://github.com/MauricioF68/recolectores-clientes.git EcoRuta
    ```
   

**3. Instalar Dependencias**

  - Navega dentro de la carpeta del proyecto recién creada:
    ```bash
    cd recolectores-clientes
    ```
  - Instala todas las dependencias de PHP con Composer:
    ```bash
    composer install
    ```

**4. Configurar el Archivo de Entorno (.env)**

  - En la carpeta del proyecto, encontrarás un archivo llamado `.env.example`. Haz una copia de este archivo y renómbrala a `.env`.
  - Abre una terminal en la raíz del proyecto y ejecuta el siguiente comando para generar la clave de la aplicación:
    ```bash
    php artisan key:generate
    ```
    *Verás un mensaje confirmando que la clave se generó correctamente.*

**5. Crear y Configurar la Base de Datos**

  - Abre tu navegador y ve a `http://localhost/phpmyadmin/`.
  - Crea una nueva base de datos. Para este ejemplo, la llamaremos `ecoruta_db`. **Asegúrate de usar el cotejamiento `utf8mb4_unicode_ci`**.
  - Ahora, abre el archivo `.env` que creaste en el paso 4 y modifica las siguientes líneas para que coincidan con tu configuración de XAMPP:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ecoruta_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    *(Por defecto, el usuario de MySQL en XAMPP es `root` y no tiene contraseña).*

**6. Ejecutar las Migraciones**

  - Las migraciones crean la estructura de tablas en tu base de datos. En tu terminal (asegúrate de estar en la carpeta del proyecto), ejecuta:
    ```bash
    php artisan migrate
    ```
- front ejecuta:
    ```bash
    npm install
    ```
    *Si quieres poblar la base de datos con datos de prueba (si existen), puedes usar:*
    ```bash
    php artisan migrate --seed
    ```
    

**7. ¡Lanzar la Aplicación\!** 🚀

  - Finalmente, para iniciar el servidor de desarrollo de Laravel, ejecuta:
    ```bash
    php artisan serve
    ```
    - Para que se carguen los estilos abre otro terminal en tu vs , ejecuta:
    ```bash
    npm run dev
    ```
  - Abre tu navegador web y visita la dirección que te indica la consola, generalmente es: **[http://127.0.0.1:8000](https://www.google.com/search?q=http://127.0.0.1:8000)**

¡Y listo\! 🎉 Ya deberías tener el proyecto corriendo en tu máquina local.

-----

## 🤝 Contribuciones

-----

## 📜 Licencia
