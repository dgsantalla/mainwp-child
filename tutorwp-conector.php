<?php
/**
 * MainWP Child Plugin
 *
 * @package MainWP\Child
 */

/**
 * Plugin Name: TutorWP Conector
 * Description: Conecta este sitio con tu panel de TutorWP para mantenerlo al día y protegido.
 * Plugin URI: https://tutorwp.cloud/
 * Author: TutorWP
 * Author URI: https://tutorwp.cloud
 * Text Domain: mainwp-child
 * Version: 6.1.8.3
 * Update URI: https://tutorwp.cloud/conector/
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Guarda contra el MainWP Child oficial (T7 del plan de TutorWP Conector, spec S3.6).
 *
 * TutorWP Conector es un renombre de superficie: adentro sigue siendo el mismo
 * namespace, las mismas clases y la misma funcion global mainwp_child_autoload()
 * que el MainWP Child oficial. Si los dos plugins estan activos a la vez, esa
 * funcion se declara dos veces y PHP tira un fatal error inmediato -- probado
 * de verdad en un WordPress descartable el 2026-08-30 (T3), no es una
 * suposicion. La unica forma de evitarlo es chequear ANTES de la linea que
 * declara esa funcion, mas abajo en este mismo archivo.
 *
 * WordPress ya evita que un fatal en la activacion deje el sitio roto o la
 * opcion active_plugins corrupta (plugin_sandbox_scrape() dentro de
 * activate_plugin(), verificado en el nucleo real) -- esto no es una red de
 * seguridad que falte, es una mejora de UX: en vez del mensaje generico de
 * WordPress, mostramos uno propio en espanol que dice que hacer, y ademas nos
 * autodesactivamos para no quedar activos-pero-sin-hacer-nada.
 *
 * El caso inverso (el oficial se activa con el conector ya activo) no
 * necesita codigo nuestro: ahi el fatal ocurre en el archivo del oficial, que
 * no controlamos, y WordPress ya le muestra su propio aviso sin ayuda
 * nuestra.
 */
if ( function_exists( 'mainwp_child_autoload' ) ) {

    add_action(
        'admin_notices',
        static function () {
            echo '<div class="notice notice-error"><p>' .
                esc_html__( 'TutorWP Conector no se activó: este sitio ya tiene otro conector de MainWP activo. Desactivalo primero y volvé a activar TutorWP Conector.', 'mainwp-child' ) .
                '</p></div>';
        }
    );

    if ( function_exists( 'deactivate_plugins' ) ) {
        add_action(
            'admin_init',
            static function () {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo lee, no actua sobre datos.
                    unset( $_GET['activate'] );
                }
            }
        );
    }

    return;
}

require_once ABSPATH . 'wp-includes' . DIRECTORY_SEPARATOR . 'version.php'; // NOSONAR - WP compatible. Version information from WordPress.

/**
 * Define MainWP Child Plugin Debug Mode.
 *
 * @const ( bool ) Whether or not MainWP Child is in debug mode. Default: false.
 * @source https://code-reference.mainwp.com/classes/MainWP.Child.MainWP_Child.html
 */
if ( ! defined( 'MAINWP_CHILD_DEBUG' ) ) {
    define( 'MAINWP_CHILD_DEBUG', false );
}

if ( ! defined( 'MAINWP_CHILD_FILE' ) ) {

    /**
     * Define MainWP Child Plugin absolute full path and filename of this file.
     *
     * @const ( string ) Defined MainWP Child file path.
     * @source https://github.com/mainwp/mainwp-child/blob/master/mainwp-child.php
     */
    define( 'MAINWP_CHILD_FILE', __FILE__ );
}

if ( ! defined( 'MAINWP_CHILD_PLUGIN_DIR' ) ) {

    /**
     * Define MainWP Child Plugin Directory.
     *
     * @const ( string ) Defined MainWP Child Plugin Directory.
     * @source https://github.com/mainwp/mainwp-child/blob/master/mainwp-child.php
     */
    define( 'MAINWP_CHILD_PLUGIN_DIR', plugin_dir_path( MAINWP_CHILD_FILE ) );
}

if ( ! defined( 'MAINWP_CHILD_MODULES_DIR' ) ) {

    /**
     * Define MainWP Child Modules Directory.
     *
     * @since 5.4.1
     */
    define( 'MAINWP_CHILD_MODULES_DIR', MAINWP_CHILD_PLUGIN_DIR . 'modules/' );
}

if ( ! defined( 'MAINWP_CHILD_URL' ) ) {

    /**
     * Define MainWP Child Plugin URL.
     *
     * @const ( string ) Defined MainWP Child Plugin URL.
     * @source https://github.com/mainwp/mainwp-child/blob/master/mainwp-child.php
     */
    define( 'MAINWP_CHILD_URL', plugin_dir_url( MAINWP_CHILD_FILE ) );
}

/**
 * MainWP Child Plugin Autoloader to load all other class files.
 *
 * @param string $class_name Name of the class to load.
 *
 * @uses \MainWP\Child\MainWP_Child()
 */
if ( ! function_exists( 'mainwp_child_autoload' ) ) {
    // OJO: esta declaracion tiene que quedar DENTRO de un if. Una declaracion
    // de funcion incondicional a nivel superior del archivo se registra en
    // tiempo de COMPILACION -- antes de que corra una sola linea del script,
    // incluido el "return" de la guarda de mas arriba. Sin este if, la guarda
    // no evita el fatal por redeclaracion: PHP intenta compilar la funcion
    // igual, pase lo que pase en el runtime. Probado de verdad (T7,
    // 2026-08-30): con la guarda pero sin este if, el fatal seguia
    // ocurriendo, sin que ninguna linea del archivo llegara a ejecutarse.
    function mainwp_child_autoload( $class_name ) {

        if ( \mainwp_child_modules_loader( $class_name ) ) {
            return;
        }

        $autoload_dir = \trailingslashit( __DIR__ . '/class' );

        if ( 0 === strpos( $class_name, 'MainWP\Child' ) ) {
            // strip the namespace prefix: MainWP\Child\ .
            $class_name    = substr( $class_name, 13 );
            $autoload_path = sprintf( '%sclass-%s.php', $autoload_dir, strtolower( str_replace( '_', '-', $class_name ) ) );
            if ( file_exists( $autoload_path ) ) {
                require_once $autoload_path; // NOSONAR - WP compatible.
            }
        }
    }
}

if ( function_exists( 'spl_autoload_register' ) ) {
    spl_autoload_register( 'mainwp_child_autoload' );
}

require_once MAINWP_CHILD_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php'; // NOSONAR - WP compatible.

// Canal de actualizaciones propio (T6 del plan de TutorWP Conector). Plugin
// Update Checker es la UNICA autoridad que entrega la actualizacion -- via su
// propio hook en site_transient_update_plugins, sin relacion con el header
// "Update URI" de arriba. Ese header no hace nada por si solo: solo le dice
// al nucleo de WordPress que NO consulte wordpress.org para este plugin.
// Verificado leyendo el codigo real de PUC 5.7 (libs/plugin-update-checker/):
// no menciona "Update URI" en ningun lado, asi que no hay forma de que los
// dos mecanismos se superpongan o compitan entre si.
require_once MAINWP_CHILD_PLUGIN_DIR . 'libs' . DIRECTORY_SEPARATOR . 'plugin-update-checker' . DIRECTORY_SEPARATOR . 'plugin-update-checker.php'; // NOSONAR - WP compatible.

\YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://tutorwp.cloud/conector/metadata.json',
    __FILE__,
    'tutorwp-conector'
);

// Delay the heavy constructor until we really need it.
$mainWPChild = null;
$get_child   = static function () use ( &$mainWPChild ) {
    if ( null === $mainWPChild ) {
        $mainWPChild = new MainWP\Child\MainWP_Child(
            WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . plugin_basename( __FILE__ )
        );
    }
    return $mainWPChild;
};

register_activation_hook(
    __FILE__,
    static function () use ( $get_child ) {
        $get_child()->activation();
    }
);
register_deactivation_hook(
    __FILE__,
    static function () use ( $get_child ) {
        $get_child()->deactivation();
    }
);

add_action(
    'plugins_loaded',
    function () use ( $get_child ) {
        if (
            ! is_admin()
            && ! wp_doing_ajax()
            && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            && ! wp_doing_cron()
            && ! defined( 'WP_CLI' )
            && ! isset( $_REQUEST['mainwpsignature'] ) // phpcs:ignore WordPress.Security.NonceVerification

        ) {
            // For frontend requests, use lightweight initialization.
            $get_child()->init_frontend_only();
        } else {
            // For admin, AJAX, REST, cron, CLI, or API requests, use full initialization.
            $get_child()->init_full();
        }
    }
);

$changes_logs_mod_file = MAINWP_CHILD_PLUGIN_DIR . 'modules' . DIRECTORY_SEPARATOR . 'changes-logs' . DIRECTORY_SEPARATOR . 'changes-logs.php';
if ( file_exists( $changes_logs_mod_file ) ) {
    include_once $changes_logs_mod_file; // NOSONAR - ok.
}
