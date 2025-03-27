/**
 * DecoChat DekorAI Frontend Script (Mobile Position-Fixed)
 *
 * Handles frontend interactions for the chatbot.
 *
 * @package DecoChat_DekorAI
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Find all chatbot instances
    const $chatContainers = $('.decochat-container');
    
    // Track processing state and thread IDs for multiple instances
    const processingState = {};
    const threadIds = {};
    
    // Detect if using a mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    
    // Initialize each chatbot instance
    $chatContainers.each(function() {
        const $container = $(this);
        const instanceId = $container.find('.decochat-instance-id').val();
        
        // Initialize thread ID from localStorage if available
        const savedThreadId = getSavedThreadId(instanceId);
        if (savedThreadId) {
            $container.find('.decochat-thread-id').val(savedThreadId);
            threadIds[instanceId] = savedThreadId;
        }
        
        // Set up event listeners
        initEventListeners($container, instanceId);
        
        // Fix iOS scrolling issues
        fixIOSScrolling($container);
        
        // Handle mobile keyboard issues
        if (isMobile) {
            handleMobileKeyboard($container);
        }
    });
    
    /**
     * Fix scrolling issues on iOS devices
     */
    function fixIOSScrolling($container) {
        const $messagesContainer = $container.find('.decochat-messages');
        
        // Force redraw to ensure scrolling works
        $messagesContainer.css('-webkit-overflow-scrolling', 'touch');
        
        // On iOS, we need a little help to make scrolling work properly
        if (isIOS) {
            $messagesContainer.on('touchstart', function() {
                const top = $messagesContainer.scrollTop();
                const totalScroll = $messagesContainer[0].scrollHeight;
                const currentScroll = top + $messagesContainer.outerHeight();
                
                // If we're at the top, bump down one pixel to allow scrolling up
                if (top <= 0) {
                    $messagesContainer.scrollTop(1);
                }
                // If we're at the bottom, bump up to allow scrolling down
                else if (currentScroll >= totalScroll) {
                    $messagesContainer.scrollTop(top - 1);
                }
            });
        }
    }
    
   /**
 * Handle mobile keyboard appearance and layout shifts
 */
function handleMobileKeyboard($container) {
    const $textarea = $container.find('.decochat-textarea');
    const $messagesContainer = $container.find('.decochat-messages');
    const position = $container.data('position') || 'fixed';
    
    // When input field is focused
    $textarea.on('focus', function() {
        if (window.innerWidth <= 480) {
            // Important: For "inline" position, don't apply fixed positioning
            if (position === 'inline') {
                // Just mark the body for styling, but don't change positioning
                $('body').addClass('keyboard-open-inline');
                
                // For iOS, scroll to the input after a delay
                if (isIOS) {
                    setTimeout(function() {
                        // Just scroll to the input but don't change positioning
                        $textarea[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                }
            } else {
                // For fixed position chatbots, apply the fixed positioning
                $('body').addClass('keyboard-open');
                
                setTimeout(function() {
                    $textarea[0].scrollIntoView(false);
                }, 300);
            }
        }
    });
    
    // When input loses focus
    $textarea.on('blur', function() {
        if (window.innerWidth <= 480) {
            $('body').removeClass('keyboard-open keyboard-open-inline');
            
            // Scroll messages to bottom
            setTimeout(function() {
                scrollToBottom($messagesContainer);
            }, 300);
        }
    });
    
    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        if (window.innerWidth <= 480) {
            setTimeout(function() {
                if ($textarea.is(':focus')) {
                    if (position === 'inline') {
                        // For inline chatbots, just scroll to the textarea
                        $textarea[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        $textarea[0].scrollIntoView(false);
                    }
                } else {
                    scrollToBottom($messagesContainer);
                }
            }, 500);
        }
    });
    
    // Handle resize events with position check
    $(window).on('resize', function() {
        if (window.innerWidth <= 480) {
            setTimeout(function() {
                if ($textarea.is(':focus')) {
                    if (position === 'inline') {
                        // Only scroll, don't change position for inline chatbots
                        $textarea[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        $textarea[0].scrollIntoView(false);
                    }
                } else {
                    scrollToBottom($messagesContainer);
                }
            }, 300);
        }
    });
}
    
    /**
     * Initialize event listeners for a chatbot instance
     */
    function initEventListeners($container, instanceId) {
        const $textarea = $container.find('.decochat-textarea');
        const $sendBtn = $container.find('.decochat-send-btn');
        const $messagesContainer = $container.find('.decochat-messages');
        const $resetBtn = $container.find('.decochat-reset-btn');
        
        // Send button click
        $sendBtn.on('click', function() {
            sendMessage($container, instanceId);
        });
        
        // Reset button click
        $resetBtn.on('click', function() {
            resetChat($container, instanceId);
        });
        
        // Enter key to send (Shift+Enter for new line)
        $textarea.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage($container, instanceId);
            }
        });
        
        // Auto-resize textarea
        $textarea.on('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px'; // Limit max height
            // Reset if empty
            if (this.value === '') {
                this.style.height = '';
            }
        });
        
        // Ensure scrollbar appears on mobile when needed
        $messagesContainer.on('DOMNodeInserted', function() {
            scrollToBottom($messagesContainer);
            
            // Ensure iOS shows scrollbar when needed
            if (isIOS) {
                if ($messagesContainer[0].scrollHeight > $messagesContainer.height()) {
                    $messagesContainer.css('overflow-y', 'scroll');
                }
            }
        });
        
        // Handle window resize to maintain proper dimensions
        $(window).on('resize', function() {
            // After resize, scroll to bottom and check overflow
            scrollToBottom($messagesContainer);
        });
    }
    
    /**
     * Send a message to the API
     */
    function sendMessage($container, instanceId) {
        // Get elements and values
        const $textarea = $container.find('.decochat-textarea');
        const $sendBtn = $container.find('.decochat-send-btn');
        const $messagesContainer = $container.find('.decochat-messages');
        const $threadIdField = $container.find('.decochat-thread-id');
        const message = $textarea.val().trim();
        
        // Don't process if empty or already processing
        if (!message || processingState[instanceId]) {
            return;
        }
        
        // Get current thread ID
        const threadId = $threadIdField.val();
        
        // Display user message
        addUserMessage($messagesContainer, message);
        
        // Clear textarea and adjust height
        $textarea.val('').trigger('input');
        
        // Show processing state
        processingState[instanceId] = true;
        $sendBtn.addClass('sending');
        $textarea.prop('disabled', true);
        
        // Add typing indicator
        const $typingIndicator = addTypingIndicator($messagesContainer);
        
        // Scroll to bottom
        scrollToBottom($messagesContainer);
        
        // Make AJAX request
        $.ajax({
            url: decochatData.ajax_url,
            type: 'POST',
            data: {
                action: 'decochat_process',
                nonce: decochatData.nonce,
                message: message,
                thread_id: threadId
            },
            success: function(response) {
                // Remove typing indicator
                $typingIndicator.remove();
                
                if (response.success && response.data) {
                    // Save thread ID for future messages
                    if (response.data.thread_id) {
                        $threadIdField.val(response.data.thread_id);
                        threadIds[instanceId] = response.data.thread_id;
                        saveThreadId(instanceId, response.data.thread_id);
                    }
                    
                    // Display assistant response
                    if (response.data.message) {
                        addAssistantMessage($messagesContainer, response.data.message);
                        scrollToBottom($messagesContainer);
                    }
                } else {
                    // Show error message
                    const errorMsg = response.data && response.data.message 
                        ? response.data.message 
                        : decochatData.error_message;
                        
                    addAssistantMessage($messagesContainer, errorMsg, true);
                    scrollToBottom($messagesContainer);
                }
            },
            error: function() {
                // Remove typing indicator
                $typingIndicator.remove();
                
                // Show generic error message
                addAssistantMessage($messagesContainer, decochatData.error_message, true);
                scrollToBottom($messagesContainer);
            },
            complete: function() {
                // Reset UI state
                processingState[instanceId] = false;
                $sendBtn.removeClass('sending');
                $textarea.prop('disabled', false).focus();
                
                // Extra check for mobile to ensure scrolling works
                if (window.innerWidth <= 480) {
                    setTimeout(function() {
                        scrollToBottom($messagesContainer);
                    }, 300);
                }
            }
        });
    }
    
    /**
     * Reset the chat conversation
     */
    function resetChat($container, instanceId) {
        // Clear the messages container except for the initial welcome message
        const $messagesContainer = $container.find('.decochat-messages');
        const $welcomeMessage = $messagesContainer.find('.decochat-assistant-message').first().clone();
        
        // Clear all messages
        $messagesContainer.empty();
        
        // Re-add the welcome message
        $messagesContainer.append($welcomeMessage);
        
        // Clear the thread ID
        $container.find('.decochat-thread-id').val('');
        threadIds[instanceId] = '';
        
        // Remove thread ID from localStorage
        try {
            localStorage.removeItem('decochat_thread_' + instanceId);
        } catch (e) {
            console.warn('DecoChat: Unable to remove thread ID from localStorage', e);
        }
        
        // Scroll to top of messages
        $messagesContainer.scrollTop(0);
    }
    
    /**
     * Add a user message to the chat
     */
    function addUserMessage($container, content) {
        // Create message element
        const $message = $('<div>', {
            'class': 'decochat-user-message'
        }).append(
            $('<div>', {
                'class': 'decochat-message-content',
                'html': content
            })
        );
        
        // Append to container
        $container.append($message);
        
        return $message;
    }
    
    /**
     * Add an assistant message to the chat
     */
    function addAssistantMessage($container, content, isError = false) {
        // Create message element
        const $message = $('<div>', {
            'class': 'decochat-assistant-message'
        });
        
        // Add message content
        $message.append(
            $('<div>', {
                'class': 'decochat-message-content' + (isError ? ' decochat-error-message' : ''),
                'html': content
            })
        );
        
        // Append to container
        $container.append($message);
        
        return $message;
    }
    
    /**
     * Add typing indicator
     */
    function addTypingIndicator($container) {
        const $typing = $('<div>', {
            'class': 'decochat-assistant-message'
        }).append(
            $('<div>', {
                'class': 'decochat-typing'
            }).append(
                $('<span>', {'class': 'decochat-typing-dot'}),
                $('<span>', {'class': 'decochat-typing-dot'}),
                $('<span>', {'class': 'decochat-typing-dot'})
            )
        );
        
        $container.append($typing);
        return $typing;
    }
    
    /**
     * Scroll messages container to the bottom
     */
    function scrollToBottom($container) {
        $container.stop().animate({
            scrollTop: $container[0].scrollHeight
        }, 200);
    }
    
    /**
     * Save thread ID to localStorage
     */
    function saveThreadId(instanceId, threadId) {
        try {
            localStorage.setItem('decochat_thread_' + instanceId, threadId);
        } catch (e) {
            console.warn('DecoChat: Unable to save thread ID to localStorage', e);
        }
    }
    
    /**
     * Get saved thread ID from localStorage
     */
    function getSavedThreadId(instanceId) {
        try {
            return localStorage.getItem('decochat_thread_' + instanceId);
        } catch (e) {
            console.warn('DecoChat: Unable to retrieve thread ID from localStorage', e);
            return null;
        }
    }
});