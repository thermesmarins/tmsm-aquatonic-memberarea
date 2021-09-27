<?php

declare(strict_types=1);

namespace TmsmAquatonicMemberarea\Frontend;

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit;

/**
 * Login form and Shortcode template.
 *
 * @since      1.0.0
 * @package    TmsmAquatonicMemberarea
 * @subpackage TmsmAquatonicMemberarea/Includes
 * @author     Thermes Marins de Saint-Malo
 */
class LoginForm
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     */
    private string $pluginSlug;

    /**
     * Initialize the class and set its properties.
     *
     * @since   1.0.0
     * @param   $pluginSlug     The name of the plugin.
     * @param   $version        The version of this plugin.
     */
    public function __construct(string $pluginSlug)
    {
        $this->pluginSlug = $pluginSlug;
    }

    /**
     * Register all the hooks of this class.
     *
     * @since   1.0.0
     * @param   $isAdmin    Whether the current request is for an administrative interface page.
     */
    public function initializeHooks(bool $isAdmin): void
    {
        // 'wp_ajax_' hook needs to be run on frontend and admin area too.
        add_action('wp_ajax_capitalizeText', array($this, 'capitalizeText'), 10);

        // Frontend
        if (!$isAdmin)
        {
            add_shortcode('tmsm-aquatonic-memberarea-login', array($this, 'formShortcode'));
        }
    }

    /**
     * Login form shortcode.
     *
     * @link https://developer.wordpress.org/reference/functions/add_shortcode/
     * Shortcode attribute names are always converted to lowercase before they are passed into the handler function. Values are untouched.
     *
     * The function called by the shortcode should never produce output of any kind.
     * Shortcode functions should return the text that is to be used to replace the shortcode.
     * Producing the output directly will lead to unexpected results.
     *
     * @since   1.0.0
     * @param   $attributes Attributes.
     * @param   $content    The post content.
     * @param   $tag        The name of the shortcode.
     * @return  The text that is to be used to replace the shortcode.
     */
    public function formShortcode($attributes = null, $content = null, string $tag = ''): string
    {
        // Enqueue scripts
        wp_enqueue_script($this->pluginSlug . 'login-form');

        // Inline scripts. This is how we pass data to scripts
        $script  = 'ajaxUrl = ' . json_encode(admin_url('admin-ajax.php')) . '; ';
        $script .= 'nonce = ' . json_encode(wp_create_nonce('capitalizeText')) . '; ';
        if (wp_add_inline_script($this->pluginSlug . 'login-form', $script, 'before') === false)
        {
            // It throws error on the Post edit screen and I don't know why. It works on the frontend.
            //exit('wp_add_inline_script() failed. Inlined script: ' . $script);
        }

        // Show the Form
        $html = $this->getFormHtml();
        $this->processFormData();

        return $html;
    }

    /**
     * This is a template how to receive data from a script, then return data back.
     * In this case it returns a text in capitalized.
     *
     * @since   1.0.0
     */
    public function capitalizeText()
    {
        // Verifies the AJAX request
        if (check_ajax_referer('capitalizeText', 'nonce', false) === false)
        {
            wp_send_json_error('Failed nonce', 403); // Sends json_encoded success=false.
        }

        // Sanitize values
        $text = sanitize_text_field($_POST['text']);

        // Generate response data
        $responseData = array(
            'capitalizedText' => strtoupper($text)
        );

        // Send a JSON response back to an AJAX request, and die().
        wp_send_json($responseData, 200);
    }

    /**
     * The Form's HTML code.
     * @since    1.0.0
     * @return  The form's HTML code.
     */
    private function getFormHtml(): string
    {
        $html = '<div>
                    <form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                        <p>' . wp_nonce_field('getFormHtml', 'getFormHtml_nonce', true, false) . '</p>
                        <p>
                            <label for="email">' . esc_html__('E-mail', 'tmsm-aquatonic-memberarea') . '&nbsp;<span class="required">*</span></label>
                            <input type="text" id="email" name="email" value="' . (isset($_POST["email"]) ? esc_attr($_POST["email"]) : '') . '" required />
                        </p>
                        <p>
                            <label for="password">' . esc_html__('Password', 'tmsm-aquatonic-memberarea') . '&nbsp;<span class="required">*</span></label>
                            <input type="password" id="password" name="password" value="' . (isset($_POST["subject"]) ? esc_attr($_POST["subject"]) : '') . '" required />
                        </p>

                        <p><input type="submit" name="form-submitted" value="' . esc_html__('Submit', 'tmsm-aquatonic-memberarea') . '"/></p>
                    </form>
                </div>';

        return $html;
    }

    /**
     * Validates and process the submitted data.
     * @since    1.0.0
     */
    private function processFormData(): void
    {
        // Check the Submit button is clicked
        if(isset($_POST['form-submitted']))
        {
            // Verify Nonce
            if (wp_verify_nonce($_POST['getFormHtml_nonce'], 'getFormHtml') !== false)
            {
                $email = sanitize_email($_POST["email"]);
                $subject = sanitize_text_field($_POST["subject"]);
                $body = sanitize_text_field($_POST["body"]);

                // Process the data.
                var_dump($email, $subject, $body);
            }
            else
            {
                exit(esc_html__('Failed security check.', 'tmsm-aquatonic-memberarea'));
            }
        }
    }
}
