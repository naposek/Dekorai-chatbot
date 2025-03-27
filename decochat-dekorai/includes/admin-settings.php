<?php
/**
 * Admin Settings Page for DecoChat DekorAI
 *
 * Handles all admin settings, API key management, and configuration options.
 *
 * @package DecoChat_DekorAI
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the admin menu and settings page
 */
function decochat_dekorai_admin_menu() {
    add_options_page(
        __( 'DecoChat DekorAI Settings', 'decochat-dekorai' ),
        __( 'DecoChat DekorAI', 'decochat-dekorai' ),
        'manage_options',
        'decochat-dekorai-settings',
        'decochat_dekorai_settings_page'
    );
}
add_action( 'admin_menu', 'decochat_dekorai_admin_menu' );

/**
 * Register plugin settings
 */
function decochat_dekorai_register_settings() {
    register_setting(
        'decochat_dekorai_settings_group',
        'decochat_dekorai_options',
        'decochat_dekorai_sanitize_options'
    );
    
    // API Settings Section
    add_settings_section(
        'decochat_dekorai_api_section',
        __( 'OpenAI API Configuration', 'decochat-dekorai' ),
        'decochat_dekorai_api_section_callback',
        'decochat-dekorai-settings'
    );
    
    add_settings_field(
        'openai_api_key',
        __( 'OpenAI API Key', 'decochat-dekorai' ),
        'decochat_dekorai_api_key_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_api_section'
    );
    
    add_settings_field(
        'assistant_id',
        __( 'OpenAI Assistant ID', 'decochat-dekorai' ),
        'decochat_dekorai_assistant_id_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_api_section'
    );
    
    // Appearance Settings Section
    add_settings_section(
        'decochat_dekorai_appearance_section',
        __( 'Chatbot Appearance', 'decochat-dekorai' ),
        'decochat_dekorai_appearance_section_callback',
        'decochat-dekorai-settings'
    );
    
    add_settings_field(
        'chat_title',
        __( 'Chat Title', 'decochat-dekorai' ),
        'decochat_dekorai_chat_title_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'welcome_message',
        __( 'Welcome Message', 'decochat-dekorai' ),
        'decochat_dekorai_welcome_message_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'placeholder_text',
        __( 'Input Placeholder Text', 'decochat-dekorai' ),
        'decochat_dekorai_placeholder_text_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'send_button_text',
        __( 'Send Button Text', 'decochat-dekorai' ),
        'decochat_dekorai_send_button_text_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'theme_color',
        __( 'Theme Color', 'decochat-dekorai' ),
        'decochat_dekorai_theme_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'user_bubble_color',
        __( 'User Message Color', 'decochat-dekorai' ),
        'decochat_dekorai_user_bubble_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'bot_bubble_color',
        __( 'Bot Message Color', 'decochat-dekorai' ),
        'decochat_dekorai_bot_bubble_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'bot_text_color',
        __( 'Bot Message Text Color', 'decochat-dekorai' ),
        'decochat_dekorai_bot_text_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'user_text_color',
        __( 'User Message Text Color', 'decochat-dekorai' ),
        'decochat_dekorai_user_text_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
    
    add_settings_field(
        'chat_bg_color',
        __( 'Chat Background Color', 'decochat-dekorai' ),
        'decochat_dekorai_chat_bg_color_callback',
        'decochat-dekorai-settings',
        'decochat_dekorai_appearance_section'
    );
}
add_action( 'admin_init', 'decochat_dekorai_register_settings' );

/**
 * Sanitize options before saving to database
 */
function decochat_dekorai_sanitize_options( $options ) {
    // Get existing options to retain sensitive data if not changed
    $existing_options = get_option( 'decochat_dekorai_options', array() );
    
    // Sanitize API key (if provided)
    if ( ! empty( $options['openai_api_key'] ) ) {
        // If the key starts with "sk-" it's likely a real key, otherwise it could be the placeholder
        if ( substr( $options['openai_api_key'], 0, 3 ) === 'sk-' ) {
            $options['openai_api_key'] = sanitize_text_field( $options['openai_api_key'] );
        } else {
            // Restore the existing API key
            $options['openai_api_key'] = isset( $existing_options['openai_api_key'] ) ? $existing_options['openai_api_key'] : '';
        }
    }
    
    // Sanitize assistant ID
    $options['assistant_id'] = sanitize_text_field( $options['assistant_id'] );
    
    // Sanitize appearance fields
    $options['chat_title'] = sanitize_text_field( $options['chat_title'] );
    $options['placeholder_text'] = sanitize_text_field( $options['placeholder_text'] );
    $options['send_button_text'] = sanitize_text_field( $options['send_button_text'] );
    
    // Sanitize welcome message
    if ( isset( $options['welcome_message'] ) ) {
        $options['welcome_message'] = sanitize_textarea_field( $options['welcome_message'] );
    }
    
    // Sanitize color fields
    $color_fields = array(
        'theme_color',
        'user_bubble_color',
        'bot_bubble_color',
        'bot_text_color',
        'user_text_color',
        'chat_bg_color'
    );
    
    foreach ( $color_fields as $field ) {
        if ( isset( $options[$field] ) ) {
            $options[$field] = sanitize_hex_color( $options[$field] );
        }
    }
    
    return $options;
}

/**
 * API Settings Section description
 */
function decochat_dekorai_api_section_callback() {
    echo '<p>' . esc_html__( 'Enter your OpenAI API credentials to connect your chatbot to the OpenAI Assistant API.', 'decochat-dekorai' ) . '</p>';
    
    // Display shortcode usage instructions
    echo '<div class="notice notice-info inline">';
    echo '<p><strong>' . esc_html__( 'Usage Instructions:', 'decochat-dekorai' ) . '</strong> ' . 
         sprintf( 
             esc_html__( 'Add the %s shortcode to any page or post where you want the chatbot to appear.', 'decochat-dekorai' ),
             '<code>[decochat]</code>' 
         ) . '</p>';
    echo '</div>';
}

/**
 * API Key field callback
 */
function decochat_dekorai_api_key_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $api_key = isset( $options['openai_api_key'] ) ? $options['openai_api_key'] : '';
    
    // For security, only show first and last few characters if key exists
    $display_key = '';
    if ( ! empty( $api_key ) ) {
        $display_key = substr( $api_key, 0, 5 ) . '...' . substr( $api_key, -4 );
    }
    
    echo '<input type="password" id="openai_api_key" name="decochat_dekorai_options[openai_api_key]" class="regular-text" value="' . esc_attr( $display_key ) . '" autocomplete="off" />';
    echo '<p class="description">' . esc_html__( 'Enter your OpenAI API key. For security, the full key is not displayed once saved.', 'decochat-dekorai' ) . '</p>';
    
    // Add API key validation on input
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#openai_api_key').on('input', function() {
            const apiKey = $(this).val();
            if (apiKey && !apiKey.startsWith('sk-')) {
                $(this).after('<p class="error" style="color:red;"><?php echo esc_js( __( 'OpenAI API keys typically start with "sk-"', 'decochat-dekorai' ) ); ?></p>');
            } else {
                $(this).siblings('.error').remove();
            }
        });
    });
    </script>
    <?php
}

/**
 * Assistant ID field callback
 */
function decochat_dekorai_assistant_id_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $assistant_id = isset( $options['assistant_id'] ) ? $options['assistant_id'] : '';
    
    echo '<input type="text" id="assistant_id" name="decochat_dekorai_options[assistant_id]" class="regular-text" value="' . esc_attr( $assistant_id ) . '" />';
    echo '<p class="description">' . esc_html__( 'Enter your OpenAI Assistant ID. You can find this in your OpenAI dashboard.', 'decochat-dekorai' ) . '</p>';
    
    // Add validation
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#assistant_id').on('input', function() {
            const assistantId = $(this).val();
            if (assistantId && !assistantId.startsWith('asst_')) {
                $(this).after('<p class="error" style="color:red;"><?php echo esc_js( __( 'Assistant IDs typically start with "asst_"', 'decochat-dekorai' ) ); ?></p>');
            } else {
                $(this).siblings('.error').remove();
            }
        });
    });
    </script>
    <?php
}

/**
 * Appearance Section description
 */
function decochat_dekorai_appearance_section_callback() {
    echo '<p>' . esc_html__( 'Customize the appearance and text of your chatbot.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Chat Title field callback
 */
function decochat_dekorai_chat_title_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $chat_title = isset( $options['chat_title'] ) ? $options['chat_title'] : __( 'Chat with our AI Assistant', 'decochat-dekorai' );
    
    echo '<input type="text" id="chat_title" name="decochat_dekorai_options[chat_title]" class="regular-text" value="' . esc_attr( $chat_title ) . '" />';
    echo '<p class="description">' . esc_html__( 'Title that appears at the top of the chat interface.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Welcome Message field callback
 */
function decochat_dekorai_welcome_message_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $welcome_message = isset( $options['welcome_message'] ) ? $options['welcome_message'] : __( '¡Hola! ¿En qué puedo ayudarte hoy?', 'decochat-dekorai' );
    
    echo '<textarea id="welcome_message" name="decochat_dekorai_options[welcome_message]" class="large-text" rows="3">' . esc_textarea( $welcome_message ) . '</textarea>';
    echo '<p class="description">' . esc_html__( 'The initial message shown when the chat first loads.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Placeholder Text field callback
 */
function decochat_dekorai_placeholder_text_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $placeholder_text = isset( $options['placeholder_text'] ) ? $options['placeholder_text'] : __( 'Type your message here...', 'decochat-dekorai' );
    
    echo '<input type="text" id="placeholder_text" name="decochat_dekorai_options[placeholder_text]" class="regular-text" value="' . esc_attr( $placeholder_text ) . '" />';
    echo '<p class="description">' . esc_html__( 'Placeholder text for the message input field.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Send Button Text field callback
 */
function decochat_dekorai_send_button_text_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $send_button_text = isset( $options['send_button_text'] ) ? $options['send_button_text'] : __( 'Send', 'decochat-dekorai' );
    
    echo '<input type="text" id="send_button_text" name="decochat_dekorai_options[send_button_text]" class="regular-text" value="' . esc_attr( $send_button_text ) . '" />';
    echo '<p class="description">' . esc_html__( 'Text for the send message button.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Theme Color field callback
 */
function decochat_dekorai_theme_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $theme_color = isset( $options['theme_color'] ) ? $options['theme_color'] : '#8a2be2';
    
    echo '<input type="text" id="theme_color" name="decochat_dekorai_options[theme_color]" value="' . esc_attr( $theme_color ) . '" class="decochat-color-picker" data-default-color="#8a2be2" />';
    echo '<p class="description">' . esc_html__( 'Primary color for the chat header and send button.', 'decochat-dekorai' ) . '</p>';
}

/**
 * User Message Bubble Color field callback
 */
function decochat_dekorai_user_bubble_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $user_bubble_color = isset( $options['user_bubble_color'] ) ? $options['user_bubble_color'] : '#4a4c60';
    
    echo '<input type="text" id="user_bubble_color" name="decochat_dekorai_options[user_bubble_color]" value="' . esc_attr( $user_bubble_color ) . '" class="decochat-color-picker" data-default-color="#4a4c60" />';
    echo '<p class="description">' . esc_html__( 'Background color for user message bubbles.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Bot Message Bubble Color field callback
 */
function decochat_dekorai_bot_bubble_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $bot_bubble_color = isset( $options['bot_bubble_color'] ) ? $options['bot_bubble_color'] : '#ffffff';
    
    echo '<input type="text" id="bot_bubble_color" name="decochat_dekorai_options[bot_bubble_color]" value="' . esc_attr( $bot_bubble_color ) . '" class="decochat-color-picker" data-default-color="#ffffff" />';
    echo '<p class="description">' . esc_html__( 'Background color for bot message bubbles.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Bot Message Text Color field callback
 */
function decochat_dekorai_bot_text_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $bot_text_color = isset( $options['bot_text_color'] ) ? $options['bot_text_color'] : '#333333';
    
    echo '<input type="text" id="bot_text_color" name="decochat_dekorai_options[bot_text_color]" value="' . esc_attr( $bot_text_color ) . '" class="decochat-color-picker" data-default-color="#333333" />';
    echo '<p class="description">' . esc_html__( 'Text color for bot messages.', 'decochat-dekorai' ) . '</p>';
}

/**
 * User Message Text Color field callback
 */
function decochat_dekorai_user_text_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $user_text_color = isset( $options['user_text_color'] ) ? $options['user_text_color'] : '#ffffff';
    
    echo '<input type="text" id="user_text_color" name="decochat_dekorai_options[user_text_color]" value="' . esc_attr( $user_text_color ) . '" class="decochat-color-picker" data-default-color="#ffffff" />';
    echo '<p class="description">' . esc_html__( 'Text color for user messages.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Chat Background Color field callback
 */
function decochat_dekorai_chat_bg_color_callback() {
    $options = get_option( 'decochat_dekorai_options' );
    $chat_bg_color = isset( $options['chat_bg_color'] ) ? $options['chat_bg_color'] : '#f9f9f9';
    
    echo '<input type="text" id="chat_bg_color" name="decochat_dekorai_options[chat_bg_color]" value="' . esc_attr( $chat_bg_color ) . '" class="decochat-color-picker" data-default-color="#f9f9f9" />';
    echo '<p class="description">' . esc_html__( 'Background color for the chat messages area.', 'decochat-dekorai' ) . '</p>';
}

/**
 * Settings page content
 */
function decochat_dekorai_settings_page() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Show settings update messages
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error(
            'decochat_dekorai_messages',
            'decochat_dekorai_message',
            __( 'Settings Saved', 'decochat-dekorai' ),
            'updated'
        );
    }
    
    // Check if API credentials are set
    $options = get_option( 'decochat_dekorai_options' );
    $api_key = isset( $options['openai_api_key'] ) ? $options['openai_api_key'] : '';
    $assistant_id = isset( $options['assistant_id'] ) ? $options['assistant_id'] : '';
    
    if ( empty( $api_key ) || empty( $assistant_id ) ) {
        add_settings_error(
            'decochat_dekorai_messages',
            'decochat_dekorai_message',
            __( 'Warning: OpenAI API credentials are not fully configured. The chatbot will not function until both API Key and Assistant ID are provided.', 'decochat-dekorai' ),
            'warning'
        );
    }
    
    // Show status/error messages
    settings_errors( 'decochat_dekorai_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields
            settings_fields( 'decochat_dekorai_settings_group' );
            // Output setting sections and fields
            do_settings_sections( 'decochat-dekorai-settings' );
            // Output save settings button
            submit_button();
            ?>
            
            <!-- Live Preview Section -->
            <div class="card" style="margin-top: 20px;">
                <h2><?php esc_html_e( 'Chat Preview', 'decochat-dekorai' ); ?></h2>
                
                <div class="decochat-preview" style="max-width: 400px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; margin: 20px 0;">
                    <!-- Preview Header -->
                    <div class="decochat-preview-header" style="background-color: <?php echo esc_attr( isset( $options['theme_color'] ) ? $options['theme_color'] : '#8a2be2' ); ?>; padding: 12px 15px; color: white;">
                        <h3 style="margin: 0; font-size: 16px;"><?php echo esc_html( isset( $options['chat_title'] ) ? $options['chat_title'] : __( 'Chat Title', 'decochat-dekorai' ) ); ?></h3>
                    </div>
                    
                    <!-- Preview Chat Area -->
                    <div class="decochat-preview-chat" style="padding: 15px; background-color: <?php echo esc_attr( isset( $options['chat_bg_color'] ) ? $options['chat_bg_color'] : '#f9f9f9' ); ?>; min-height: 200px;">
                        <!-- Bot Welcome Message -->
                        <div style="margin-bottom: 15px; text-align: left;">
                            <div class="decochat-preview-bot-bubble" style="display: inline-block; padding: 10px 15px; max-width: 80%; border-radius: 18px; border-bottom-left-radius: 4px; background-color: <?php echo esc_attr( isset( $options['bot_bubble_color'] ) ? $options['bot_bubble_color'] : '#ffffff' ); ?>; color: <?php echo esc_attr( isset( $options['bot_text_color'] ) ? $options['bot_text_color'] : '#333333' ); ?>; border: 1px solid #eaeaea;">
                                <span class="decochat-preview-welcome"><?php echo esc_html( isset( $options['welcome_message'] ) ? $options['welcome_message'] : __( '¡Hola! ¿En qué puedo ayudarte hoy?', 'decochat-dekorai' ) ); ?></span>
                            </div>
                        </div>
                        
                        <!-- User Example Message -->
                        <div style="margin-bottom: 15px; text-align: right;">
                            <div class="decochat-preview-user-bubble" style="display: inline-block; padding: 10px 15px; max-width: 80%; border-radius: 18px; border-bottom-right-radius: 4px; background-color: <?php echo esc_attr( isset( $options['user_bubble_color'] ) ? $options['user_bubble_color'] : '#4a4c60' ); ?>; color: <?php echo esc_attr( isset( $options['user_text_color'] ) ? $options['user_text_color'] : '#ffffff' ); ?>;">
                                <?php esc_html_e( 'This is an example user message', 'decochat-dekorai' ); ?>
                            </div>
                        </div>
                        
                        <!-- Bot Example Response -->
                        <div style="text-align: left;">
                            <div class="decochat-preview-bot-bubble" style="display: inline-block; padding: 10px 15px; max-width: 80%; border-radius: 18px; border-bottom-left-radius: 4px; background-color: <?php echo esc_attr( isset( $options['bot_bubble_color'] ) ? $options['bot_bubble_color'] : '#ffffff' ); ?>; color: <?php echo esc_attr( isset( $options['bot_text_color'] ) ? $options['bot_text_color'] : '#333333' ); ?>; border: 1px solid #eaeaea;">
                                <?php esc_html_e( 'This is an example bot response.', 'decochat-dekorai' ); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Input -->
                    <div style="padding: 10px 15px; background-color: white; border-top: 1px solid #eaeaea; display: flex; align-items: center;">
                        <div style="flex: 1; height: 36px; background-color: #f9f9f9; border-radius: 18px; border: 1px solid #eaeaea;"></div>
                        <div style="width: 36px; height: 36px; border-radius: 50%; background-color: <?php echo esc_attr( isset( $options['theme_color'] ) ? $options['theme_color'] : '#8a2be2' ); ?>; margin-left: 8px;"></div>
                    </div>
                </div>
                
                <p class="description"><?php esc_html_e( 'Live preview of your chatbot appearance. Changes will update as you modify the settings above.', 'decochat-dekorai' ); ?></p>
            </div>
        </form>
        
        <div class="card">
            <h2><?php esc_html_e( 'Shortcode Usage', 'decochat-dekorai' ); ?></h2>
            
            <h3><?php esc_html_e( 'Shortcode Parameters:', 'decochat-dekorai' ); ?></h3>
            <table class="widefat" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Parameter', 'decochat-dekorai' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'decochat-dekorai' ); ?></th>
                        <th><?php esc_html_e( 'Example', 'decochat-dekorai' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>position</code></td>
                        <td><?php esc_html_e( 'Set the chatbot position. Use "fixed" for a floating chatbot in the bottom-right corner or "inline" to embed it within your page content.', 'decochat-dekorai' ); ?></td>
                        <td><code>position="fixed"</code> or <code>position="inline"</code></td>
                    </tr>
                    <tr>
                        <td><code>title</code></td>
                        <td><?php esc_html_e( 'Override the default chat title.', 'decochat-dekorai' ); ?></td>
                        <td><code>title="Ask our Design Assistant"</code></td>
                    </tr>
                    <tr>
                        <td><code>theme_color</code></td>
                        <td><?php esc_html_e( 'Change the main color of the chatbot interface. Use hex color codes.', 'decochat-dekorai' ); ?></td>
                        <td><code>theme_color="#e91e63"</code></td>
                    </tr>
                    <tr>
                        <td><code>height</code></td>
                        <td><?php esc_html_e( 'Set a custom height for the chat messages container.', 'decochat-dekorai' ); ?></td>
                        <td><code>height="500px"</code></td>
                    </tr>
                </tbody>
            </table>
            
            <h3><?php esc_html_e( 'Examples:', 'decochat-dekorai' ); ?></h3>
            <p><strong><?php esc_html_e( 'Floating Chat:', 'decochat-dekorai' ); ?></strong> <code>[decochat position="fixed" theme_color="#8a2be2"]</code></p>
            <p><strong><?php esc_html_e( 'Embedded Chat:', 'decochat-dekorai' ); ?></strong> <code>[decochat position="inline" height="400px" title="Chatea con nosotros"]</code></p>
            <p><strong><?php esc_html_e( 'Custom Colors:', 'decochat-dekorai' ); ?></strong> <code>[decochat theme_color="#2196f3" title="Blue Theme Chat"]</code></p>
            
            <div class="notice notice-info inline" style="margin: 15px 0;">
                <p><?php esc_html_e( 'Tip: The "fixed" position (floating) is ideal for most websites as it stays visible as users scroll. The "inline" position is useful for dedicated chat pages or sections.', 'decochat-dekorai' ); ?></p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Enqueue admin scripts and styles
 */
function decochat_dekorai_admin_enqueue_scripts( $hook ) {
    // Only enqueue on our settings page
    if ( 'settings_page_decochat-dekorai-settings' !== $hook ) {
        return;
    }
    
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    
    // Admin-specific script
    wp_enqueue_script(
        'decochat-admin-script',
        DECOCHAT_DEKORAI_PLUGIN_URL . 'assets/js/decochat-admin.js',
        array( 'jquery', 'wp-color-picker' ),
        DECOCHAT_DEKORAI_VERSION,
        true
    );
}