<?php
/**
 * Helper Functions for DecoChat DekorAI
 *
 * Contains utility functions used throughout the plugin.
 *
 * @package DecoChat_DekorAI
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Call the OpenAI API to process chat messages
 *
 * @param string $message The user's message
 * @param string $thread_id Optional thread ID for continued conversations
 * @return array|WP_Error Response data or error
 */
function decochat_dekorai_call_openai_api( $message, $thread_id = '' ) {
    // Get plugin options
    $options = get_option( 'decochat_dekorai_options' );
    $api_key = isset( $options['openai_api_key'] ) ? $options['openai_api_key'] : '';
    $assistant_id = isset( $options['assistant_id'] ) ? $options['assistant_id'] : '';
    
    // Check if API credentials are configured
    if ( empty( $api_key ) ) {
        return new WP_Error( 'api_error', __( 'OpenAI API key is not configured.', 'decochat-dekorai' ) );
    }
    
    if ( empty( $assistant_id ) ) {
        return new WP_Error( 'api_error', __( 'OpenAI Assistant ID is not configured.', 'decochat-dekorai' ) );
    }
    
    // OpenAI API base URL
    $api_base_url = 'https://api.openai.com/v1';
    
    // API request headers
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
        'OpenAI-Beta'   => 'assistants=v2',
    );
    
    // Step 1: Create a new thread if no thread_id is provided
    if ( empty( $thread_id ) ) {
        // Log the request we're about to make
        decochat_dekorai_log( 'Creating new thread with OpenAI API...' );
        
        $thread_response = wp_remote_post(
            $api_base_url . '/threads',
            array(
                'headers' => $headers,
                'body'    => json_encode( array() ),
                'timeout' => 60,
            )
        );
        
        // Check for WP_Error from the HTTP request itself
        if ( is_wp_error( $thread_response ) ) {
            // Log the WP_Error
            decochat_dekorai_log( 'WP_Error when creating thread: ' . $thread_response->get_error_message() );
            return new WP_Error( 'http_error', sprintf( 
                __( 'Failed to connect to OpenAI API: %s', 'decochat-dekorai' ),
                $thread_response->get_error_message()
            ) );
        }
        
        // Check HTTP status code
        $status_code = wp_remote_retrieve_response_code( $thread_response );
        if ( $status_code !== 200 ) {
            // Log response code and body
            $response_body = wp_remote_retrieve_body( $thread_response );
            decochat_dekorai_log( "HTTP error when creating thread. Status: {$status_code}, Response: {$response_body}" );
            
            // Try to parse the error message from the response
            $error_message = __( 'Unknown API error.', 'decochat-dekorai' );
            $response_data = json_decode( $response_body, true );
            
            if ( $response_data && isset( $response_data['error'] ) ) {
                if ( isset( $response_data['error']['message'] ) ) {
                    $error_message = $response_data['error']['message'];
                } else if ( isset( $response_data['error']['type'] ) ) {
                    $error_message = $response_data['error']['type'];
                }
            }
            
            return new WP_Error( 'api_error', sprintf( 
                __( 'OpenAI API error (code %d): %s', 'decochat-dekorai' ),
                $status_code,
                $error_message
            ) );
        }
        
        $thread_body = json_decode( wp_remote_retrieve_body( $thread_response ), true );
        
        // Log the parsed response
        decochat_dekorai_log( 'Thread creation response: ' . print_r( $thread_body, true ) );
        
        if ( ! isset( $thread_body['id'] ) ) {
            return new WP_Error( 'api_error', __( 'Failed to create thread: No thread ID returned from OpenAI API.', 'decochat-dekorai' ) );
        }
        
        $thread_id = $thread_body['id'];
        decochat_dekorai_log( "Thread created successfully with ID: {$thread_id}" );
    }
    
    // Step 2: Add the user message to the thread
    decochat_dekorai_log( "Adding message to thread {$thread_id}..." );
    
    $message_response = wp_remote_post(
        $api_base_url . '/threads/' . $thread_id . '/messages',
        array(
            'headers' => $headers,
            'body'    => json_encode( array(
                'role'    => 'user',
                'content' => $message,
            ) ),
            'timeout' => 60,
        )
    );
    
    if ( is_wp_error( $message_response ) ) {
        decochat_dekorai_log( 'WP_Error when adding message: ' . $message_response->get_error_message() );
        return new WP_Error( 'http_error', sprintf( 
            __( 'Failed to send message to OpenAI API: %s', 'decochat-dekorai' ),
            $message_response->get_error_message()
        ) );
    }
    
    $status_code = wp_remote_retrieve_response_code( $message_response );
    if ( $status_code !== 200 ) {
        $response_body = wp_remote_retrieve_body( $message_response );
        decochat_dekorai_log( "HTTP error when adding message. Status: {$status_code}, Response: {$response_body}" );
        
        $error_message = __( 'Unknown API error.', 'decochat-dekorai' );
        $response_data = json_decode( $response_body, true );
        
        if ( $response_data && isset( $response_data['error'] ) ) {
            if ( isset( $response_data['error']['message'] ) ) {
                $error_message = $response_data['error']['message'];
            } else if ( isset( $response_data['error']['type'] ) ) {
                $error_message = $response_data['error']['type'];
            }
        }
        
        return new WP_Error( 'api_error', sprintf( 
            __( 'OpenAI API error when adding message (code %d): %s', 'decochat-dekorai' ),
            $status_code,
            $error_message
        ) );
    }
    
    // Step 3: Run the assistant on the thread
    decochat_dekorai_log( "Running assistant {$assistant_id} on thread {$thread_id}..." );
    
    $run_response = wp_remote_post(
        $api_base_url . '/threads/' . $thread_id . '/runs',
        array(
            'headers' => $headers,
            'body'    => json_encode( array(
                'assistant_id' => $assistant_id,
            ) ),
            'timeout' => 60,
        )
    );
    
    if ( is_wp_error( $run_response ) ) {
        decochat_dekorai_log( 'WP_Error when creating run: ' . $run_response->get_error_message() );
        return new WP_Error( 'http_error', sprintf( 
            __( 'Failed to start OpenAI processing: %s', 'decochat-dekorai' ),
            $run_response->get_error_message()
        ) );
    }
    
    $status_code = wp_remote_retrieve_response_code( $run_response );
    if ( $status_code !== 200 ) {
        $response_body = wp_remote_retrieve_body( $run_response );
        decochat_dekorai_log( "HTTP error when creating run. Status: {$status_code}, Response: {$response_body}" );
        
        $error_message = __( 'Unknown API error.', 'decochat-dekorai' );
        $response_data = json_decode( $response_body, true );
        
        if ( $response_data && isset( $response_data['error'] ) ) {
            if ( isset( $response_data['error']['message'] ) ) {
                $error_message = $response_data['error']['message'];
            } else if ( isset( $response_data['error']['type'] ) ) {
                $error_message = $response_data['error']['type'];
            }
        }
        
        return new WP_Error( 'api_error', sprintf( 
            __( 'OpenAI API error when creating run (code %d): %s', 'decochat-dekorai' ),
            $status_code,
            $error_message
        ) );
    }
    
    $run_body = json_decode( wp_remote_retrieve_body( $run_response ), true );
    decochat_dekorai_log( 'Run creation response: ' . print_r( $run_body, true ) );
    
    if ( ! isset( $run_body['id'] ) ) {
        return new WP_Error( 'api_error', __( 'Failed to create run: No run ID returned from OpenAI API.', 'decochat-dekorai' ) );
    }
    
    $run_id = $run_body['id'];
    decochat_dekorai_log( "Run created successfully with ID: {$run_id}" );
    
    // Step 4: Check the run status periodically until it's completed
    $max_attempts = 30; // Maximum number of polling attempts
    $attempts = 0;
    $status = 'in_progress';
    $completed_statuses = array( 'completed', 'failed', 'cancelled', 'expired' );
    
    do {
        $attempts++;
        
        // Wait before polling again (longer wait times for later attempts)
        $wait_time = min( 2 * $attempts, 10 ); // Start with 2 seconds, max 10 seconds
        sleep( $wait_time );
        
        // Check run status
        decochat_dekorai_log( "Checking run status (attempt {$attempts})..." );
        
        $status_response = wp_remote_get(
            $api_base_url . '/threads/' . $thread_id . '/runs/' . $run_id,
            array(
                'headers' => $headers,
                'timeout' => 60,
            )
        );
        
        if ( is_wp_error( $status_response ) ) {
            decochat_dekorai_log( 'WP_Error when checking run status: ' . $status_response->get_error_message() );
            return new WP_Error( 'http_error', sprintf( 
                __( 'Failed to check OpenAI processing status: %s', 'decochat-dekorai' ),
                $status_response->get_error_message()
            ) );
        }
        
        $status_code = wp_remote_retrieve_response_code( $status_response );
        if ( $status_code !== 200 ) {
            $response_body = wp_remote_retrieve_body( $status_response );
            decochat_dekorai_log( "HTTP error when checking run status. Status: {$status_code}, Response: {$response_body}" );
            
            $error_message = __( 'Unknown API error.', 'decochat-dekorai' );
            $response_data = json_decode( $response_body, true );
            
            if ( $response_data && isset( $response_data['error'] ) ) {
                if ( isset( $response_data['error']['message'] ) ) {
                    $error_message = $response_data['error']['message'];
                } else if ( isset( $response_data['error']['type'] ) ) {
                    $error_message = $response_data['error']['type'];
                }
            }
            
            return new WP_Error( 'api_error', sprintf( 
                __( 'OpenAI API error when checking status (code %d): %s', 'decochat-dekorai' ),
                $status_code,
                $error_message
            ) );
        }
        
        $status_body = json_decode( wp_remote_retrieve_body( $status_response ), true );
        
        if ( ! isset( $status_body['status'] ) ) {
            decochat_dekorai_log( 'Run status response missing status field: ' . print_r( $status_body, true ) );
            return new WP_Error( 'api_error', __( 'Failed to retrieve run status: Invalid response from OpenAI API.', 'decochat-dekorai' ) );
        }
        
        $status = $status_body['status'];
        decochat_dekorai_log( "Current run status: {$status}" );
        
        // If there's an error, return it
        if ( $status === 'failed' ) {
            if ( isset( $status_body['last_error'] ) ) {
                $error_code = isset( $status_body['last_error']['code'] ) ? $status_body['last_error']['code'] : 'unknown';
                $error_message = isset( $status_body['last_error']['message'] ) ? $status_body['last_error']['message'] : __( 'Unknown error', 'decochat-dekorai' );
                
                decochat_dekorai_log( "Run failed with error code {$error_code}: {$error_message}" );
                return new WP_Error( 'api_error', sprintf( __( 'OpenAI processing error (%s): %s', 'decochat-dekorai' ), $error_code, $error_message ) );
            } else {
                decochat_dekorai_log( "Run failed without specific error details" );
                return new WP_Error( 'api_error', __( 'OpenAI processing failed without specific error details.', 'decochat-dekorai' ) );
            }
        }
        
    } while ( ! in_array( $status, $completed_statuses ) && $attempts < $max_attempts );
    
    // If the run didn't complete in time
    if ( ! in_array( $status, $completed_statuses ) ) {
        decochat_dekorai_log( "Run timed out after {$attempts} attempts. Last status: {$status}" );
        return new WP_Error( 'timeout', __( 'The request timed out waiting for OpenAI to process your message. Please try again.', 'decochat-dekorai' ) );
    }
    
    // If the run completed successfully, get the assistant's response
    if ( $status === 'completed' ) {
        decochat_dekorai_log( "Run completed successfully. Retrieving messages..." );
        
        $messages_response = wp_remote_get(
            $api_base_url . '/threads/' . $thread_id . '/messages',
            array(
                'headers' => $headers,
                'timeout' => 60,
            )
        );
        
        if ( is_wp_error( $messages_response ) ) {
            decochat_dekorai_log( 'WP_Error when retrieving messages: ' . $messages_response->get_error_message() );
            return new WP_Error( 'http_error', sprintf( 
                __( 'Failed to retrieve OpenAI response: %s', 'decochat-dekorai' ),
                $messages_response->get_error_message()
            ) );
        }
        
        $status_code = wp_remote_retrieve_response_code( $messages_response );
        if ( $status_code !== 200 ) {
            $response_body = wp_remote_retrieve_body( $messages_response );
            decochat_dekorai_log( "HTTP error when retrieving messages. Status: {$status_code}, Response: {$response_body}" );
            
            $error_message = __( 'Unknown API error.', 'decochat-dekorai' );
            $response_data = json_decode( $response_body, true );
            
            if ( $response_data && isset( $response_data['error'] ) ) {
                if ( isset( $response_data['error']['message'] ) ) {
                    $error_message = $response_data['error']['message'];
                } else if ( isset( $response_data['error']['type'] ) ) {
                    $error_message = $response_data['error']['type'];
                }
            }
            
            return new WP_Error( 'api_error', sprintf( 
                __( 'OpenAI API error when retrieving messages (code %d): %s', 'decochat-dekorai' ),
                $status_code,
                $error_message
            ) );
        }
        
        $messages_body = json_decode( wp_remote_retrieve_body( $messages_response ), true );
        
        if ( ! isset( $messages_body['data'] ) || ! is_array( $messages_body['data'] ) ) {
            decochat_dekorai_log( 'Messages response missing data field or invalid format: ' . print_r( $messages_body, true ) );
            return new WP_Error( 'api_error', __( 'Failed to retrieve messages: Invalid response format from OpenAI API.', 'decochat-dekorai' ) );
        }
        
        // Find the latest assistant message (should be the first one in the list)
        $assistant_message = '';
        foreach ( $messages_body['data'] as $msg ) {
            if ( $msg['role'] === 'assistant' ) {
                // Extract text content from message parts
                if ( isset( $msg['content'] ) && is_array( $msg['content'] ) ) {
                    foreach ( $msg['content'] as $content_part ) {
                        if ( $content_part['type'] === 'text' ) {
                            $assistant_message .= $content_part['text']['value'] . "\n\n";
                        }
                    }
                }
                break; // We only need the latest message
            }
        }
        
        if ( empty( $assistant_message ) ) {
            decochat_dekorai_log( 'No assistant message found in the response: ' . print_r( $messages_body['data'], true ) );
            return new WP_Error( 'api_error', __( 'No response received from the assistant.', 'decochat-dekorai' ) );
        }
        
        decochat_dekorai_log( "Successfully retrieved assistant response" );
        
        // Return success response with the thread ID and assistant message
        return array(
            'thread_id' => $thread_id,
            'message'   => trim( $assistant_message ),
        );
    } else {
        decochat_dekorai_log( "Run completed with unexpected status: {$status}" );
        return new WP_Error( 'api_error', sprintf( __( 'OpenAI processing completed with unexpected status: %s', 'decochat-dekorai' ), $status ) );
    }
}

/**
 * Adjust color brightness
 * 
 * @param string $hex Hex color code
 * @param float $factor Factor to adjust brightness (-1 to 1)
 * @return string Modified hex color
 */
function decochat_dekorai_adjust_brightness( $hex, $factor ) {
    $hex = ltrim( $hex, '#' );
    
    if ( strlen( $hex ) == 3 ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    
    $red   = hexdec( substr( $hex, 0, 2 ) );
    $green = hexdec( substr( $hex, 2, 2 ) );
    $blue  = hexdec( substr( $hex, 4, 2 ) );
    
    // Adjust brightness
    if ( $factor > 0 ) {
        // Lighter
        $red   = (int) min( 255, $red + ( 255 - $red ) * $factor );
        $green = (int) min( 255, $green + ( 255 - $green ) * $factor );
        $blue  = (int) min( 255, $blue + ( 255 - $blue ) * $factor );
    } else {
        // Darker
        $factor = abs( $factor );
        $red   = (int) max( 0, $red * ( 1 - $factor ) );
        $green = (int) max( 0, $green * ( 1 - $factor ) );
        $blue  = (int) max( 0, $blue * ( 1 - $factor ) );
    }
    
    // Convert back to hex
    return sprintf( '#%02x%02x%02x', $red, $green, $blue );
}

/**
 * Debug logging function (only active when WP_DEBUG is enabled)
 * 
 * @param mixed $data The data to log
 * @return void
 */
function decochat_dekorai_log( $data ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
        if ( is_array( $data ) || is_object( $data ) ) {
            error_log( '[DecoChat] ' . print_r( $data, true ) );
        } else {
            error_log( '[DecoChat] ' . $data );
        }
    }
}

/**
 * Validate OpenAI API credentials
 * 
 * @param string $api_key OpenAI API key
 * @param string $assistant_id OpenAI Assistant ID
 * @return bool|WP_Error True if valid, WP_Error on failure
 */
function decochat_dekorai_validate_api_credentials( $api_key, $assistant_id ) {
    // Check if credentials are provided
    if ( empty( $api_key ) || empty( $assistant_id ) ) {
        return new WP_Error( 'missing_credentials', __( 'API key and Assistant ID are required.', 'decochat-dekorai' ) );
    }
    
    // Test API connection by retrieving assistant info
    $response = wp_remote_get(
        'https://api.openai.com/v1/assistants/' . $assistant_id,
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ),
            'timeout' => 30,
        )
    );
    
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $status_code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( $status_code !== 200 ) {
        $error_message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Unknown API error.', 'decochat-dekorai' );
        return new WP_Error( 'api_error', $error_message );
    }
    
    return true;
}
