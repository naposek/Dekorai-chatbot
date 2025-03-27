<?php
/**
 * Plugin Name: DecoChat DekorAI
 * Plugin URI: https://example.com/decochat-dekorai
 * Description: A fully functional WordPress chatbot powered by OpenAI's Assistant API, easily integrated through shortcodes.
 * Version: 1.0.0
 * Author: DekorAI
 * Author URI: https://example.com
 * Text Domain: decochat-dekorai
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package DecoChat_DekorAI
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'DECOCHAT_DEKORAI_VERSION', '1.0.0' );
define( 'DECOCHAT_DEKORAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DECOCHAT_DEKORAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DECOCHAT_DEKORAI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load plugin textdomain for translations
function decochat_dekorai_load_textdomain() {
    load_plugin_textdomain( 'decochat-dekorai', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'decochat_dekorai_load_textdomain' );

// Include required files
require_once DECOCHAT_DEKORAI_PLUGIN_DIR . 'includes/functions.php';
require_once DECOCHAT_DEKORAI_PLUGIN_DIR . 'includes/admin-settings.php';
require_once DECOCHAT_DEKORAI_PLUGIN_DIR . 'includes/shortcode.php';

// Register activation hook
register_activation_hook( __FILE__, 'decochat_dekorai_activate' );
function decochat_dekorai_activate() {
    // Default settings on activation
    $default_options = array(
        'openai_api_key'   => '',
        'assistant_id'     => '',
        'chat_title'       => __( 'Chat with our AI Assistant', 'decochat-dekorai' ),
        'placeholder_text' => __( 'Type your message here...', 'decochat-dekorai' ),
        'send_button_text' => __( 'Send', 'decochat-dekorai' ),
        'theme_color'      => '#4a90e2'
    );
    
    // Only set options if they don't already exist
    if ( ! get_option( 'decochat_dekorai_options' ) ) {
        update_option( 'decochat_dekorai_options', $default_options );
    }
    
    // Create necessary database tables if needed
    // No custom tables needed for this implementation
}

// Register deactivation hook
register_deactivation_hook( __FILE__, 'decochat_dekorai_deactivate' );
function decochat_dekorai_deactivate() {
    // Clean up if needed
    // We'll keep the settings in the database in case the plugin is reactivated
}

// Register uninstall hook (static method)
register_uninstall_hook( __FILE__, 'decochat_dekorai_uninstall' );
function decochat_dekorai_uninstall() {
    // Complete cleanup on uninstall
    delete_option( 'decochat_dekorai_options' );
}

// Enqueue scripts and styles for the frontend
function decochat_dekorai_enqueue_scripts() {
    // Only enqueue if the shortcode is present on the page
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'decochat' ) ) {
        wp_enqueue_style( 'decochat-style', DECOCHAT_DEKORAI_PLUGIN_URL . 'assets/css/decochat-style.css', array(), DECOCHAT_DEKORAI_VERSION );
        
        // Register and localize the main script
        wp_enqueue_script( 'decochat-script', DECOCHAT_DEKORAI_PLUGIN_URL . 'assets/js/decochat-script.js', array( 'jquery' ), DECOCHAT_DEKORAI_VERSION, true );
        
        // Get plugin settings
        $options = get_option( 'decochat_dekorai_options' );
        
        // Localize script with data and translations
        wp_localize_script( 'decochat-script', 'decochatData', array(
            'ajax_url'        => admin_url( 'admin-ajax.php' ),
            'nonce'           => wp_create_nonce( 'decochat_nonce' ),
            'placeholder'     => esc_attr( $options['placeholder_text'] ),
            'sending_message' => __( 'Sending...', 'decochat-dekorai' ),
            'error_message'   => __( 'An error occurred. Please try again.', 'decochat-dekorai' ),
            'theme_color'     => esc_attr( $options['theme_color'] )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'decochat_dekorai_enqueue_scripts' );

// AJAX handler for chat processing
function decochat_dekorai_process_chat() {
    // Verify nonce for security
    if ( ! check_ajax_referer( 'decochat_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'decochat-dekorai' ) ) );
        wp_die();
    }
    
    // Get user message from POST data
    $user_message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
    $thread_id = isset( $_POST['thread_id'] ) ? sanitize_text_field( wp_unslash( $_POST['thread_id'] ) ) : '';
    
    if ( empty( $user_message ) ) {
        wp_send_json_error( array( 'message' => __( 'Empty message.', 'decochat-dekorai' ) ) );
        wp_die();
    }
    
    // Process the message with OpenAI API
    $response = decochat_dekorai_call_openai_api( $user_message, $thread_id );
    
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    } else {
        wp_send_json_success( $response );
    }
    
    wp_die();
}
add_action( 'wp_ajax_decochat_process', 'decochat_dekorai_process_chat' );
add_action( 'wp_ajax_nopriv_decochat_process', 'decochat_dekorai_process_chat' );

// Add settings link on plugin page
function decochat_dekorai_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=decochat-dekorai-settings' ) . '">' . __( 'Settings', 'decochat-dekorai' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . DECOCHAT_DEKORAI_PLUGIN_BASENAME, 'decochat_dekorai_settings_link' );
