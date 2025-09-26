# Condominio

 1. Configurar la Base de Datos:
     * Asegúrate de que tu servidor de MySQL o PostgreSQL esté en funcionamiento.
     * Crea una base de datos (p. ej., condominio_db).
     * Ejecuta el script SQL correspondiente (database/create_mysql.sql o database/create_postgresql.sql) en tu base de datos.
     * Verifica y ajusta los datos de conexión en config/db_config.php.
     * Ejecuta php proyecto-vue-php/database/seed.php desde el directorio /opt/Deveploment/Condominio para crear el usuario administrador.

 2. Instalar Dependencias del Frontend:
     * Navega a la carpeta del frontend: cd proyecto-vue-php/frontend.
     * Ejecuta npm install para descargar todas las librerías necesarias.

 3. Iniciar los Servidores:
     * Backend: En una terminal, desde /opt/Deveploment/Condominio, ejecuta: php -S localhost:8000 -t proyecto-vue-php/backend
     * Frontend: En otra terminal, desde /opt/Deveploment/Condominio/proyecto-vue-php/frontend, ejecuta: npm run dev

 4. Probar la Aplicación:
     * Abre tu navegador y visita http://localhost:5173.
     * Inicia sesión con admin@test.com y admin123.
