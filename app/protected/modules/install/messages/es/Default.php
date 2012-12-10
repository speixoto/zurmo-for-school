<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    // KEEP these in alphabetical order.
    // KEEP them indented correctly.
    // KEEP all the language files up-to-date with each other.
    // DON'T MAKE A MESS!
    return array(
        '$_SERVER does not have {vars}.'
            => '$_SERVER no tiene {vars}.',
        '$_SERVER is accessible.'
            => '$_SERVER es accesible',
        'Apache'
            => 'Apache', // Same Word Translated
        'APC'
            => 'APC', // Same Word Translated
        'Automatically submit crash reports to Sentry.'
            => 'Enviar automáticamente informes de error a Sentry.',
        'Below you will find the results of the system check. If any required ' .
        'services are not setup correctly, you will need to make sure they are ' .
        'installed correctly before you can continue.'
            => 'Estos son los resultados del análisis. ' .
               'Si alguno de los requeridos no está instalado correctamente, debe corregir los problemas antes de proceder.',
        'Can either be a domain name or an IP address.'
            => 'Puede ser un nombre de dominio o una dirección IP.',
        'Click below to go to the login page. The username is <b>super</b>'
            => 'Haga clic abajo para ir a la página de login. El usuario es <b>súper</ b>',
        'Click here to access index page, after you disable maintenance mode.'
            => 'Haga clic aquí para ir a la página índice después de desactivar el modo de mantenimiento.',
        'Click Here to continue with next step'
            => 'Haga clic aquí para continuar',
        'Click Here to install the demo data'
            => 'Haga clic aquí para instalar los datos demo',
        'Click here to start upgrade'
            => 'Haga clic aquí para iniciar la actualización',
        'Click to start'
            => 'Haga clic para iniciar',
        'Congratulations! The demo data has been successfully loaded.'
            => '¡Felicidades! Los datos demo se ha cargado correctamente.',
        'Congratulations! The installation of Zurmo is complete.'
            => '¡Felicitaciones! La instalación de Zurmo se ha completado.',
        'Connecting to Database.'
            => 'Realizar la conexión a la base de datos.',
        'Continue'
            => 'Continuar',
        'Copy upgrade file to app/protected/runtime/upgrade folder and start upgrade process.'
            => 'Copie la actualización de aplicaciones/protected/runtime/upgrade carpeta y comenzar la actualización.',
        'Correctly Installed Services'
            => 'Servicios correctamente instalado',
        'Could not get value of database default collation.'
            => 'No se puede obtener el valor de la intercalación predeterminada de la base de datos.',
        'Could not get value of database max_allowed_packet.'
            => 'No se puede obtener el valor de max_allowed_packet.',
        'Could not get value of database max_sp_recursion_depth.'
            => 'No se puede obtener el valor de max_sp_recursion_depth.',
        'Could not get value of database optimizer_search_depth.'
            => 'No se puede obtener el valor de optimizer_search_depth.',
        'Could not get value of database thread_stack.'
            => 'No se puede obtener el valor de database thread_stack.',
        'Creating super user.'
            => 'Creando super usuario.',
        'Ctype extension is loaded.'
            => 'El ctype extensión está cargada.',
        'Ctype extension is not loaded.'
            => 'El ctype extensión no está cargada.',
        'Curl'
            => 'Curl', // Same Word Translated
        'Database admin password'
            => 'Contraseña de administrador',
        'Database admin username'
            => 'Nombre de usuario de administrador',
        'Database default collation is: {collation}'
            => 'La colación de la base de datos es: {collation}',
        'Database default collation meets minimum requirement.'
            => 'La colación predeterminada de la base de datos cumple con los requisitos mínimos.',
        'Database default collation should not be in: {listOfCollations}'
            => 'La colación predeterminada de la base de datos no debería ser: {listOfCollations}',
        'Database host'
            => 'Host de base de datos',
        'Database is in strict mode.'
            => 'Base de datos está en modo estricto.',
        'Database is not in strict mode.'
            => 'Base de datos no está en modo estricto.',
        'Database log_bin=off and therefore satisfies this requirement.' // Not Coding Standard
            => 'En la base de datos, el valor del parámetro log_bin=off y cumple con las condiciones necesarias.', // Not Coding Standard
        'Database log_bin=on and log_bin_trust_function_creators=on and therefore satisfies this requirement' // Not Coding Standard
            => 'En la base de datos, los valores de parámetros log_bin=on y log_bin_trust_function_creators=on y cumplen con las condiciones necesarias.', // Not Coding Standard
        'Database log_bin=on. Either set log_bin=off or set log_bin_trust_function_creators=on.' // Not Coding Standard
            => 'En la base de datos, los valores de parámetros log_bin=on. Debe cambiar la configuración de log_bin=off o log_bin_trust_function_creators=on.', // Not Coding Standard
        'Database max_allowed_packet size is:'
            => 'Base de datos max_allowed_packet tamaño es:',
        'Database max_allowed_packet size meets minimum requirement.'
            => 'Base de datos max_allowed_packet tamaño cumple con el requisito mínimo.',
        'Database max_sp_recursion_depth size is:'
            => 'Base de datos de tamaño max_sp_recursion_depth es:',
        'Database max_sp_recursion_depth size meets minimum requirement.'
            => 'Base de datos cumple con el requisito mínimo tamaño max_sp_recursion_depth.',
        'Database name'
            => 'Nombre de base de datos',
        'Database optimizer_search_depth size meets requirement.'
            => 'El valor del parámetro optimizer_search_depth cumple el requisito.',
        'Database optimizer_search_depth value is {searchDepth}. It is required to be set to 0.'
            => 'El valor del parámetro optimizer_search_depth es {searchDepth}. El valor debe ser 0.',
        'Database password'
            => 'Contraseña de base de datos',
        'Database port.'
            => 'Puerto (base de datos)',
        'Database schema creation complete.'
            => 'Esquema de la base de datos creada',
        'Database thread_stack value is:'
            => 'El valor de thread_stack la base de datos es:',
        'Database thread_stack value meets minimum requirement.'
            => 'El valor de thread_stack en la base de datos cumple con los requisitos mínimos.',
        'Database username'
            => 'Nombre de usuario de base de datos',
        'Do not use the RedBean Legacy version'
            => 'No utilice la versión RedBean Legacy',
        'Dropping existing tables.'
            => 'Eliminación de las tablas existentes',
        'Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.'
            => '$_SERVER["REQUEST_URI"] o $_SERVER["QUERY_STRING"] no existe.',
        'Error code:'
            => 'Código de error:',
        'FAIL'
            => 'FALLA',
        'Failed Optional Services'
            => 'Servicios opcionales no están instalados',
        'Failed Required Services'
            => 'Servicios necesarios no están instalados',
        'Finished loading demo data.'
            => 'Terminado de cargar datos de demostración.',
        'Freezing database.'
            => 'Freezing base de datos',
        'Host name where Zurmo will be installed.'
            => 'El nombre del host donde Zurmo se instalará.',
        'If this website is in production mode, please remove the app/test.php file.'
            => 'Sí, la página está en producción, por favor, elimine la aplicación del archivo app/test.php.',
        'IMAP extension is loaded.'
            => 'Extensión de IMAP está cargado.',
        'IMAP extension is not loaded.'
            => 'Extensión de IMAP no está cargado.',
        'In all likelihood, these items were supplied to you by your Web Host. '.
        'If you do not have this information, then you will need to contact them ' .
        'before you can continue. If you\'re all ready...'
            => 'Esta información es proporcionada por el anfitrión. Si usted no tiene ' .
               'toda esta información, póngase en contacto con su anfitrión o ' .
               'el administrador del sistema. Si todo está en orden ...',
        'Install'
            => 'Instalar',
        'Install demo data.'
            => 'Instalar los datos de demostración.',
        'Installation Complete.'
            => 'Instalación completa',
        'Installation in progress. Please wait.'
            => 'Instalación en curso. Por favor espere.',
        'Installation Output:'
            => 'Log de la instalación:',
        'is installed, but the version is unknown.'
            => 'está instalado, pero la versión es desconocido.',
        'is not installed.'
            => 'no está instalado.',
        'It is highly recommended that all optional services are installed and ' .
        'working before continuing.'
            => 'Se recomienda instalar todos los servicios opcionales' .
               'antes de continuar.',
        'Leave this blank unless you would like to create the user and database ' .
        'for Zurmo to run in.'
            => 'Deje el valor vacío si desea crear un usuario y un' .
               'base de datos para Zurmo.',
        'Loading demo data. Please wait.'
            => 'Cargando datos de demostración. Por favor espere.',
        'Locking Installation.'
            => 'Bloqueo de la página de instalación',
        'Mbstring is installed.'
            => 'Mbstring está instalado',
        'Mbstring is not installed.'
            => 'Mbstring no está instalado',
        'Memcache extension'
            => 'Extensión Memcache',
        'Memcache host'
            => 'host de Memcache',
        'Memcache host name. Default is 127.0.0.1'
            => 'Nombre de Memcache host. Predeterminado es 127.0.0.1',
        'Memcache port number'
            => 'Memcache número de port',
        'Memcache port number. Default is 11211'
            => 'Memcache número de port. Predeterminado es 11211',
        'Microsoft-IIS'
            => 'Microsoft-IIS', // Same Word Translated
        'Minify has been disabled due to a system issue. Try to resolve the problem and re-enable Minify.'
            => 'Minify ha sido deshabilitado por un problema del sistema. Tratar de resolver el problema y volver a habilitar Minify.',
        'Minify library is included.'
            => 'Biblioteca minify está incluido.',
        'minimum requirement is:'
            => 'requisito mínimo es:',
        'Minimum version required:'
            => 'Versión mínima requerida:',
        'Mysql'
            => 'Mysql', // Same Word Translated
        'Oh no!'
            => '¡Oh no!',
        'PASS'
            => 'EXITOSA',
        'PCRE extension is loaded.'
            => 'La extensión PCRE está cargado.',
        'PCRE extension is not loaded.'
            => 'La extensión PCRE no está cargada.',
        'pdo is installed.'
            => 'pdo está instalado.',
        'pdo is not installed.'
            => 'pdo no está instalado.',
        'pdo_mysql is installed.'
            => 'pdo_mysql está instalado.',
        'pdo_mysql is not installed.'
            => 'pdo_mysql no está instalado.',
        'PHP'
            => 'PHP', // Same Word Translated
        'PHP date.timezone is not set.'
            => 'Configuración de date.timezone PHP no está configurado.',
        'PHP date.timezone is set.'
            => 'Configuración de date.timezone PHP está configurado.',
        'PHP file_uploads is Off.  This should be on.'
            => 'PHP file_uploads está deshabilitado. Este debe estar encendido.',
        'PHP file_uploads is on which is ok.'
            => 'PHP file_uploads está encendido. Esto está bien.',
        'PHP memory_limit is:'
            => 'Configuración de memory_limit PHP es:',
        'PHP memory_limit meets minimum requirement.'
            => 'Configuración de memory_limit PHP cumple con el requisito mínimo.',
        'PHP post_max_size meets minimum requirement.'
            => 'PHP post_max_size cumple con el requisito mínimo.',
        'PHP post_max_size setting is:'
            => 'PHP configuración post_max_size es:',
        'PHP upload_max_filesize value is:'
            => 'PHP upload_max_filesize es:',
        'PHP upload_max_filesize value meets minimum requirement.'
            => 'PHP upload_max_filesize cumple con el requisito mínimo.',
        'Please delete all files from assets folder on server.'
            => 'Por favor, elimine todos los archivos en la carpeta Assets.',
        'Please set $maintenanceMode = true in perInstance.php config file.'
            => 'Por favor, defina $maintenanceMode = true en el archivo de configuración perInstance.php.',
        'Rebuilding Permissions.'
            => 'Reconstruido de permissos',
        'Recheck System'
            => 'Revisar el sistema',
        'RedBean'
            => 'RedBean', // Same Word Translated
        'RedBean file is missing patch.'
            => 'Archivo de RedBean falta el parche.',
        'RedBean file is patched correctly'
            => 'Archivo de RedBean está correctamente parcheado',
        'Schema update complete.'
            => 'Esquema de actualización realizada.',
        'Service Status Partially Known'
            => 'Estado del servicio parcialmente conocido',
        'Setting up default data.'
            => 'Configuración de datos predeterminados.',
        'Since you specified an existing database you must check this box in order ' .
        'to proceed. THIS WILL REMOVE ALL EXISTING DATA.'
            => 'Se especifica una base de datos existente, marque la casilla para continuar. ' .
               'LOS DATOS EXISTENTES SERÁN BORRADOS',
        'SOAP is installed.'
            => 'SOAP está instalado.',
        'SOAP is not installed.'
            => 'SOAP no está instalado.',
        'SPL extension is loaded.'
            => 'La extensión del SPL se ha cargado.',
        'SPL extension is not loaded.'
            => 'La extensión del SPL no está cargada.',
        'Starting database schema creation.'
            => 'Creación de esquemas de bases de datos.',
        'Starting schema update process.'
            => 'Esquema de actualización comenzó.',
        'Starting to load demo data.'
            => 'Creación la base de datos de demostración',
        'Starting upgrade process.'
            => 'Iniciar el proceso upgrade.',
        'The database name specified does not exist or the user specified does not have access.'
            => 'El nombre de base de datos especificada no existe o el usuario especificado no tiene acceso.',
        'The debug.php config file is not writable.'
            => 'No se puede escribirEl archivo de configuración  debug.php',
        'The debug.php config file is writable.'
            => 'Se puede escribirEl archivo de configuración  debug.php',
        'The instance folders are present and writable.'
            => 'Los archivos del sistema están presentes y escritura.',
        'The name of the database you want to run Zurmo in.'
            => 'El nombre de la base de datos para Zurmo.',
        'The next step is to install the demo data.'
            => 'El siguiente paso es instalar los datos de demostración.',
        'The next step is to reload the application and upgrade the schema.'
            => 'El siguiente paso es volver a cargar la aplicación y actualizar el esquema.',
        'The perInstance.php config file is not writable.'
            => 'No se puede escribir ll archivo de configuración perInstance.php.',
        'The perInstance.php config file is writable.'
            => 'Se puede escribir ll archivo de configuración perInstance.php.',
        'The relative path where Zurmo will be installed.'
            => 'La ruta relativa en Zurmo se instalará.',
        'The system has detected that the hostInfo and/or scriptUrl are not set up. Please open the perInstance.php config file and set up these parameters.'
            => 'El sistema ha detectado que hostinfo y/o ScirptUrl no se configuran. Por favor, abra el archivo de configuración perInstance.php y configurar estos parámetros.',
        'There is a problem with php set_include_path command. Command can fail if "php_admin_value include_path" directive is set in Apache configuration.'
            => 'Existe un problema con php set_include_path. El comando puede fallar si la directiva "php_admin_value include_path" está activado en la configuración de Apache. ',
        'There was a problem creating the database Error code:'
            => 'Hubo un problema al crear la base de datos de errores',
        'There was a problem creating the user Error code:'
            => 'Hubo un problema al crear el código de error de usuario',
        'This is the Zurmo upgrade process. Please backup all files and the database before you continue.'
            => 'Este es el proceso de actualización Zurmo. Por favor, haga una copia de seguridad de todos los archivos y la base de datos antes de continuar.',
        'Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.'
            => 'No se puede determinar info ruta URL. Por favor, asegúrese $_SERVER ["PATH_INFO"] (o $_SERVER["PHP_SELF"] y $_SERVER["SCRIPT_NAME"]) contiene el valor correcto.',
        'Upgrade in progress. Please wait.'
            => 'Actualización en progreso. Por favor, espere.',
        'Upgrade Output:'
            => 'Actualiza salida:',
        'Upgrade process is completed. Please edit perInstance.php file, and disable maintenance mode.'
            => 'El proceso de actualización se ha completado. Por favor, modifique el archivo perInstance.php y desactivar el modo de mantenimiento.',
        'User who can connect to the database.'
            => 'El usuario que puede conectarse a la base de datos.',
        'User`s password.'
            => 'Contraseña de usuario',
        'version installed:'
            => 'versión instalada:',
        'WARNING'
            => 'AVISO',
        'WARNING! - If the database already exists the data will be completely removed. ' .
        'This must be checked if you are specifying an existing database.'
            => 'AVISO - Si la base de datos ya existe, la base de datos existente' .
               'será totalmente excluidos. Marque la casilla si se especifica una base de datos existente.',
        'Welcome to Zurmo. Before getting started, we need some information on the database. ' .
        'You will need to know the following items before proceeding:'
            => 'Bienvenido a Zurmo. Antes de empezar, necesitamos un poco información sobre la base de datos' .
               'Usted necesita saber lo siguiente antes de continuar:',
        'Writing Configuration File.'
            => 'Crear el archivo de configuración.',
        'Yii'
            => 'Yii', // Same Word Translated
        'You cannot access the installation area because the application is already installed.'
            => 'No se puede acceder a la zona de instalación porque la aplicación ya está instalada.',
        'You have specified an existing database. If you would like to use this database, ' .
        'then do not specify the database admin username and password. Otherwise pick a ' .
        'database name that does not exist.'
            => 'Se ha especificado una base de datos existente. Si desea utilizar la base de datos' .
               'los datos no se especifica un nombre de usuario y contraseña de administrador.' .
               'O elegir un nombre de base de datos que no existe.',
        'You have specified an existing user. If you would like to use this user, then do ' .
        'not specify the database admin username and password. Otherwise pick a database ' .
        'username that does not exist.'
            => 'Ha especificado un usuario existente. Si desea utilizar este usuario, entonces no ' .
               'especifica el nombre de usuario admin y la contraseña de la base de datos. , ' .
               'O elegir un nombre de usuario de base de datos que no existe.',
        'Your ZurmoCRM software is outdated, new stable release available:'
            => 'Su versión de Zumo es obsoleto, una nueva versión estable está disponible:',
        'Zip extension is loaded.'
            => 'El Extension de Zip está cargado.',
        'Zip extension is not loaded.'
            => 'El Extension de Zip no está cargado. ',
        'Zurmo administrative password. The username is `super`. You can change this later.'
            => 'Zurmo contraseña administrativa. El usuario es `super. Se puede cambiar esto más adelante.',
        'Zurmo Installation'
            => 'Instalación de Zurmo',
        'Zurmo runs only on Apache {apacheMinVersion} and higher or Microsoft-IIS {iisMinVersion} or higher web servers.'
            => 'Zurmo sólo se ejecuta en Apache {apacheMinVersion} y superior o Microsoft-IIS {iisMinVersion} o superiores servidores web.',
        'Zurmo Version'
            => 'Version de Zurmo',
        '{folderPath} is missing.'
            => '{folderPath} falta',
        '{folderPath} is not writable.'
            => '{folderPath} no se puede escribir.',
    );
?>
