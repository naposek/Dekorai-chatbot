/**
 * DecoChat DekorAI Admin Scripts
 *
 * Enhanced scripts for the admin settings page.
 *
 * @package DecoChat_DekorAI
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize all color pickers
    $('.decochat-color-picker').wpColorPicker({
        change: function(event, ui) {
            updateColorPreview();
        },
        clear: function() {
            setTimeout(updateColorPreview, 100);
        }
    });
    
    // Function to update preview elements
    function updateColorPreview() {
        const userBubbleColor = $('#user_bubble_color').val() || '#4a4c60';
        const botBubbleColor = $('#bot_bubble_color').val() || '#ffffff';
        const userTextColor = $('#user_text_color').val() || '#ffffff';
        const botTextColor = $('#bot_text_color').val() || '#333333';
        const chatBgColor = $('#chat_bg_color').val() || '#f9f9f9';
        const themeColor = $('#theme_color').val() || '#8a2be2';
        
        // Update preview elements
        $('.decochat-preview-header').css('background-color', themeColor);
        $('.decochat-preview-chat').css('background-color', chatBgColor);
        
        $('.decochat-preview-user-bubble').css({
            'background-color': userBubbleColor,
            'color': userTextColor
        });
        
        $('.decochat-preview-bot-bubble').css({
            'background-color': botBubbleColor,
            'color': botTextColor
        });
        
        // Update send button
        $('.decochat-preview').find('div[style*="border-radius: 50%"]').css('background-color', themeColor);
    }
    
    // Handle API key input masking
    const $apiKeyInput = $('#openai_api_key');
    
    // If API key exists (shows as placeholder with ***), handle focus/blur
    if ($apiKeyInput.length && $apiKeyInput.val().includes('...')) {
        // Clear masked value on focus
        $apiKeyInput.on('focus', function() {
            if ($(this).val().includes('...')) {
                $(this).val('');
            }
        });
        
        // Warn user if leaving without saving changes
        $apiKeyInput.on('blur', function() {
            if ($(this).val() && !$(this).val().includes('...')) {
                const $notice = $('<p class="notice notice-warning inline-notice"></p>')
                    .text('Remember to save your changes to update the API key.');
                
                if (!$(this).next('.inline-notice').length) {
                    $(this).after($notice);
                }
            }
        });
    }
    
    // Add live preview functionality for welcome message
    $('#welcome_message').on('input', function() {
        const welcomeText = $(this).val() || '¡Hola! ¿En qué puedo ayudarte hoy?';
        $('.decochat-preview-welcome').text(welcomeText);
    });
    
    // Add live preview for chat title
    $('#chat_title').on('input', function() {
        const chatTitle = $(this).val() || 'Chat Title';
        $('.decochat-preview-header h3').text(chatTitle);
    });
    
    // Call update preview on page load
    updateColorPreview();
});