# DecoChat DekorAI - WordPress Chatbot Plugin

A fully functional WordPress chatbot powered by OpenAI's Assistant API, easily integrated through shortcodes.

## Description

DecoChat DekorAI is a WordPress plugin that allows you to seamlessly integrate OpenAI's powerful Assistant API into your WordPress website. The plugin provides a user-friendly chatbot interface that can be embedded anywhere on your site using a simple shortcode.

### Key Features:

- **Easy Integration**: Add the chatbot to any page, post, or widget using the `[decochat]` shortcode.
- **Customizable Appearance**: Change the chat title, colors, and text elements to match your brand.
- **Secure API Handling**: Securely store and manage your OpenAI API credentials.
- **Responsive Design**: Works seamlessly on desktop and mobile devices.
- **Conversation Memory**: Maintains conversation context throughout the user session.
- **Translation-Ready**: Fully translatable with .pot/.po/.mo files included.

## Installation

1. Download the `decochat-dekorai.zip` file.
2. Navigate to your WordPress admin area and go to **Plugins > Add New**.
3. Click the **Upload Plugin** button at the top of the page.
4. Choose the downloaded zip file and click **Install Now**.
5. After installation, click **Activate Plugin**.

## Configuration

1. After activation, go to **Settings > DecoChat DekorAI** in your WordPress admin menu.
2. Enter your OpenAI API Key and Assistant ID. (You can obtain these from your OpenAI account dashboard).
3. Customize the appearance settings if desired (chat title, colors, etc.).
4. Save your settings.

## Usage

### Basic Usage

Add the chatbot to any page or post using the shortcode:

```
[decochat]
```

### Advanced Usage

Customize the chatbot appearance with these optional parameters:

```
[decochat title="Ask our AI Expert" height="500px"]
```

### Available Parameters:

- `title`: Custom title for the chat interface
- `height`: Height of the chat messages container
- `width`: Width of the entire chat container (default: 100%)

## File Structure

The plugin follows WordPress best practices with this file structure:

```
decochat-dekorai/
├── decochat-dekorai.php        # Main plugin file
├── includes/
│   ├── admin-settings.php      # Settings page logic
│   ├── shortcode.php           # Shortcode implementation
│   └── functions.php           # Helper functions
├── assets/
│   ├── css/
│   │   └── decochat-style.css  # Chatbot styling
│   └── js/
│       ├── decochat-script.js  # Chatbot frontend interactions
│       └── decochat-admin.js   # Admin settings scripts
└── languages/
    └── decochat-dekorai.pot    # Translation template
```

## Customization

### Styling

The plugin uses a dedicated stylesheet for the chatbot interface. You can override styles by adding custom CSS to your theme or using a CSS customization plugin.

### Hooks and Filters

The plugin provides several hooks and filters for developers to extend its functionality:

```php
// Filter the chatbot response before displaying
add_filter('decochat_response_message', 'my_custom_response_filter', 10, 2);
function my_custom_response_filter($message, $thread_id) {
    // Modify the message
    return $message;
}

// Action before sending message to OpenAI
add_action('decochat_before_api_call', 'my_before_api_action', 10, 2);
function my_before_api_action($message, $thread_id) {
    // Do something before API call
}
```

## Translations

The plugin is translation-ready. To translate it to your language:

1. Use the provided `.pot` file in the `languages` directory as a template.
2. Create `.po` and `.mo` files for your language using a tool like Poedit.
3. Place these files in the `languages` directory.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- OpenAI API key and Assistant ID

## Security Considerations

- The plugin uses WordPress nonces to protect against CSRF attacks.
- API keys are stored securely in the WordPress database.
- User input is sanitized before processing.

## Support

For questions, feature requests, or bug reports, please contact us at support@example.com.

## License

This plugin is licensed under the GPL v2 or later.

---

© 2025 DekorAI
