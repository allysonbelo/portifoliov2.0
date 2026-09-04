<?php
/**
 * Plugin Name: ABC Engagement Toast (Popup Style)
 * Plugin URI:  https://allysonbelo.com
 * Description: Notificações inteligentes tipo Toast e Popups de alta conversão com múltiplos gatilhos e validação por chave de licença remota.
 * Version:     1.4.1
 * Author:      Allyson Belo
 * Author URI:  https://allysonbelo.com
 * Text Domain: abc-engagement-toast
 * Domain Path: /languages
 * License:     Proprietary
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ABC_ENGAGEMENT_TOAST_VERSION', '1.4.1');
define('ABC_ENGAGEMENT_TOAST_PATH', plugin_dir_path(__FILE__));
define('ABC_ENGAGEMENT_TOAST_URL', plugin_dir_url(__FILE__));
define('ABC_ENGAGEMENT_TOAST_BASENAME', plugin_basename(__FILE__));

// Carregar Módulos
require_once ABC_ENGAGEMENT_TOAST_PATH . 'includes/class-license-client.php';
require_once ABC_ENGAGEMENT_TOAST_PATH . 'includes/class-cpt-toast.php';
require_once ABC_ENGAGEMENT_TOAST_PATH . 'includes/class-frontend-toast.php';

/**
 * Inicializador Principal do Plugin
 */
class ABC_Engagement_Toast_Plugin {

    private static $instance = null;
    public $license;
    public $cpt;
    public $frontend;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->license  = new ABC_Toast_License_Client();
        $this->cpt      = new ABC_Toast_CPT();
        $this->frontend = new ABC_Toast_Frontend();

        add_filter('plugin_action_links_' . ABC_ENGAGEMENT_TOAST_BASENAME, array($this, 'add_action_links'));
    }

    /**
     * Adiciona links rápidos na lista de plugins instalados
     */
    public function add_action_links($links) {
        $settings_links = array(
            '<a href="' . admin_url('edit.php?post_type=abc_popup&page=abc-toast-license') . '" style="font-weight: bold; color: #f9a045;">' . __('🔑 Licença', 'abc-engagement-toast') . '</a>',
            '<a href="' . admin_url('edit.php?post_type=abc_popup') . '">' . __('Toasts', 'abc-engagement-toast') . '</a>'
        );
        return array_merge($settings_links, $links);
    }
}

function abc_engagement_toast_init() {
    return ABC_Engagement_Toast_Plugin::get_instance();
}
add_action('plugins_loaded', 'abc_engagement_toast_init');

// Ativação e Desativação
register_activation_hook(__FILE__, function() {
    // Registra o CPT temporariamente para atualizar os rewrite rules
    if (class_exists('ABC_Toast_CPT')) {
        $cpt = new ABC_Toast_CPT();
        $cpt->register_popup_cpt();
    }
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
