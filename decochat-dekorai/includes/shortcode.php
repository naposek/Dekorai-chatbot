15a2 2 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <?php
    }
    
    // Chat container class and styling
    $container_class = 'decochat-container';
    $container_style = '';
    
    if ($atts['position'] === 'fixed') {
        $container_class .= ' decochat-hidden';
    } else {
        // Inline positioning
        $container_style = 'position: relative; width: 100%; max-width: 100%;';
        
        // Apply custom height for inline mode
        if (!empty($atts['height'])) {
            $container_style .= " height: {$atts['height']};";
        }
    }
    ?>
    <div id="<?php echo esc_attr( $chat_id ); ?>" 
         class="<?php echo esc_attr( $container_class ); ?>" 
         data-position="<?php echo esc_attr( $atts['position'] ); ?>"
         style="<?php echo esc_attr( $container_style ); ?>">
        
        <!-- Chat Header -->
        <div class="decochat-header" style="background-color: <?php echo esc_attr( $atts['theme_color'] ); ?>;">
            <div>
                <h3 class="decochat-title"><?php echo esc_html( $atts['title'] ); ?></h3>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div class="decochat-messages">
            <!-- Bot welcome message -->
            <div class="decochat-assistant-message">
                <div class="decochat-message-content">
                    <?php 
                    $welcome_message = isset( $options['welcome_message'] ) 
                        ? $options['welcome_message'] 
                        : __( '¡Hola! ¿En qué puedo ayudarte hoy?', 'decochat-dekorai' );
                    echo esc_html( $welcome_message ); 
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="decochat-input">
            <textarea 
                class="decochat-textarea" 
                placeholder="<?php echo esc_attr( $options['placeholder_text'] ); ?>"
                rows="1"
                aria-label="<?php esc_attr_e( 'Message input', 'decochat-dekorai' ); ?>"
            ></textarea>
            <button class="decochat-send-btn" style="background-color: <?php echo esc_attr( $atts['theme_color'] ); ?>;" title="<?php esc_attr_e( 'Send Message', 'decochat-dekorai' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                <span class="decochat-icon-fallback">➤</span>
            </button>
            <button class="decochat-reset-btn" title="<?php esc_attr_e( 'Reset Conversation', 'decochat-dekorai' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 2v6h6"></path>
                    <path d="M3 13a9 9 0 1 0 3-7.7L3 8"></path>
                </svg>
                <span class="decochat-icon-fallback">↻</span>
            </button>
        </div>
        
        <!-- Hidden fields for API interaction -->
        <input type="hidden" class="decochat-thread-id" value="">
        <input type="hidden" class="decochat-instance-id" value="<?php echo esc_attr( $chat_id ); ?>">
        
        <!-- Dynamic styling based on admin settings -->
        <style type="text/css">
            #<?php echo esc_attr( $chat_id ); ?> .decochat-messages {
                background-color: <?php echo esc_attr( isset( $options['chat_bg_color'] ) ? $options['chat_bg_color'] : '#f9f9f9' ); ?>;
            }
            
            #<?php echo esc_attr( $chat_id ); ?> .decochat-user-message .decochat-message-content {
                background-color: <?php echo esc_attr( isset( $options['user_bubble_color'] ) ? $options['user_bubble_color'] : '#4a4c60' ); ?>;
                color: <?php echo esc_attr( isset( $options['user_text_color'] ) ? $options['user_text_color'] : '#ffffff' ); ?>;
            }
            
            #<?php echo esc_attr( $chat_id ); ?> .decochat-assistant-message .decochat-message-content {
                background-color: <?php echo esc_attr( isset( $options['bot_bubble_color'] ) ? $options['bot_bubble_color'] : '#ffffff' ); ?>;
                color: <?php echo esc_attr( isset( $options['bot_text_color'] ) ? $options['bot_text_color'] : '#333333' ); ?>;
            }
        </style>
    </div>
    
    <!-- Initialize chat interaction -->
    <script type="text/javascript">
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                // Get chat elements
                const chatId = '<?php echo esc_js( $chat_id ); ?>';
                const chatContainer = document.getElementById(chatId);
                const chatToggle = document.getElementById(chatId + '-toggle');
                const positionType = '<?php echo esc_js( $atts['position'] ); ?>';
                
                // Check if we're on a mobile device
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                
                // Check if SVG icons are rendering properly
                setTimeout(function() {
                    const sendBtn = chatContainer.querySelector('.decochat-send-btn svg');
                    if (sendBtn && sendBtn.getBoundingClientRect().width === 0) {
                        // SVG not rendering, add class to show fallback icons
                        chatContainer.classList.add('decochat-svg-missing');
                    }
                }, 500);
                
                // Setup toggle functionality
                if (chatToggle) {
                    // Ensure fixed position toggle works
                    if (positionType === 'fixed') {
                        // Show chat when toggle button is clicked
                        chatToggle.addEventListener('click', function() {
                            chatContainer.classList.remove('decochat-hidden');
                            chatToggle.classList.add('decochat-hidden');
                        });
                    }
                }
                
                // Ensure inline positioning is correct
                if (positionType === 'inline') {
                    // Remove any fixed positioning remnants
                    chatContainer.style.position = 'relative';
                    chatContainer.style.width = '100%';
                    chatContainer.style.maxWidth = '100%';
                    chatContainer.style.boxShadow = 'none';
                    chatContainer.style.transform = 'none';
                }
                
                // Only for mobile devices
                if (isMobile) {
                    const textarea = chatContainer.querySelector('.decochat-textarea');
                    const messagesContainer = chatContainer.querySelector('.decochat-messages');
                    
                    // Handle focus/blur for mobile keyboards
                    textarea.addEventListener('focus', function() {
                        if (positionType === 'inline') {
                            // For inline position, just add a different class
                            document.body.classList.add('keyboard-open-inline');
                            setTimeout(function() {
                                // Just scroll to the textarea without changing position
                                textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }, 300);
                        } else {
                            // For fixed position, apply fixed positioning
                            document.body.classList.add('keyboard-open');
                        }
                    });
                    
                    textarea.addEventListener('blur', function() {
                        setTimeout(function() {
                            document.body.classList.remove('keyboard-open', 'keyboard-open-inline');
                        }, 300);
                    });
                    
                    // Fix for iOS specifically
                    if (isIOS) {
                        // Force scrolling to work on iOS
                        messagesContainer.style.webkitOverflowScrolling = 'touch';
                    }
                    
                    // Listen for resize events (like keyboard showing/hiding)
                    window.addEventListener('resize', function() {
                        if (document.activeElement === textarea) {
                            if (positionType === 'inline') {
                                document.body.classList.add('keyboard-open-inline');
                                setTimeout(function() {
                                    textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }, 100);
                            } else {
                                document.body.classList.add('keyboard-open');
                            }
                        } else {
                            document.body.classList.remove('keyboard-open', 'keyboard-open-inline');
                        }
                    });
                }
            });
        })();
    </script>
    <?php
    
    // Return the buffered output
    return ob_get_clean();
}

/**
 * Check if current page contains our shortcode
 * Used for conditionally loading assets
 *
 * @param string $shortcode The shortcode to check for
 * @return bool True if page contains shortcode
 */
function decochat_dekorai_has_shortcode( $shortcode = 'decochat' ) {
    global $post;
    
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $shortcode ) ) {
        return true;
    }
    
    // Also check for shortcode in widgets
    $has_widget_shortcode = false;
    $widget_areas = wp_get_sidebars_widgets();
    
    if ( is_array( $widget_areas ) ) {
        foreach ( $widget_areas as $widget_area => $widgets ) {
            if ( is_array( $widgets ) ) {
                foreach ( $widgets as $widget ) {
                    if ( strpos( $widget, 'text-' ) === 0 ) {
                        $widget_instance = get_option( 'widget_text' );
                        $widget_id = str_replace( 'text-', '', $widget );
                        
                        if ( isset( $widget_instance[ $widget_id ] ) && isset( $widget_instance[ $widget_id ]['text'] ) ) {
                            if ( has_shortcode( $widget_instance[ $widget_id ]['text'], $shortcode ) ) {
                                $has_widget_shortcode = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $has_widget_shortcode;
}
