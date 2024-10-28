<?php
/**
 *  Plugin Name: 51Degrees - Optimize by Device & Location
 *  Plugin URI:  https://51degrees.com/
 *  Description: Optimize your website for a range of devices and personalize your content based on your user’s location.
 *  Version:     1.0.11
 *  Author:      51Degrees
 *  Author URI:  https://51degrees.com/
 *  Text Domain: fiftyonedegrees
 *  License:     EUPL-1.2
 *
 *  This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 *  Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 *  Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 *  This Original Work is licensed under the European Union Public Licence (EUPL) 
 *  v.1.2 and is subject to its terms as set out below.
 *
 *  If a copy of the EUPL was not distributed with this file, You can obtain
 *  one at https://opensource.org/licenses/EUPL-1.2.
 *
 *  The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 *  amended by the European Commission) shall be deemed incompatible for
 *  the purposes of the Work and the provisions of the compatibility
 *  clause in Article 5 of the EUPL shall not apply.
 */

require_once __DIR__ . '/pipeline.php';

class FiftyoneService {

    /**
     * Setup action hooks for the plugin. These hooks are handled
     * by wordpress.
     * 
     * See available actions:
     * https://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @access      private
     * @since       1.0.11
     * @return      void
     */
    public function setup_wp_actions() {

        // The main init action. This runs the processing.
        add_action(
            'init',
            array($this, 'fiftyonedegrees_init'));     

        // Admin actions. These are initialization actions to run before
        // loading the admin interface.
        add_action(
            'admin_init',
            array($this, 'fiftyonedegrees_register_settings'));
        add_action(
            'admin_init',
            array($this, 'fiftyonedegrees_setup_blocks'));
        add_action(
            'admin_init',
            array($this, 'submit_rk_submit_action'));

        // Admin menu actions. These are actions run before the admin
        // menu is written.
        add_action(
            'admin_menu',
            array($this, 'fiftyonedegrees_register_options_page'));

        // Enqueue scripts actions for admin.
        add_action(
            'admin_enqueue_scripts',
            array($this, 'fiftyonedegrees_admin_enqueue_scripts'));
        
        // Add Javascript to the enqueued scripts.
        add_action(
            'wp_enqueue_scripts',
            array($this, 'fiftyonedegrees_javascript'));

        // Add the JSON rest endpoint.
        add_action(
            'rest_api_init',
            array($this, 'fiftyonedegrees_rest_api_init'));     

        // Cache Resource Key data / pipeline after saving options page
        add_action(
            'update_option',
            array($this, 'fiftyonedegrees_update_option'),
            10,
            10);
	    add_action(
            'admin_init',
            array($this, 'fiftyonedegrees_register_settings'));
    }
    
    /**
     * Setup filter hooks for the plugin. These hooks are handled
     * by wordpress.
     * 
     * See available filters:
     * https://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since       1.0.11
     * @param       string $pluginName name of the plugin
     * @return      void
     */
    public function setup_wp_filters($pluginName) {
        
        // Plugin page settings actions.
        add_filter(
            'plugin_action_links_' . $pluginName,
            array($this, 'fiftyonedegrees_add_plugin_page_settings_link'));

        // Add block filter
        add_filter(
            'render_block',
            array($this, 'fiftyonedegrees_block_filter'),
            10,
            2);
        // Register a custom block category
        add_filter(
            'block_categories_all',
            array($this, 'fiftyonedegrees_block_categories'));
        // Show and hide the conditional-group-block based on properties
        add_filter(
            'render_block',
            array($this, 'fiftyonedegrees_render_block'),
            10,
            2);
    }


    /**
     * Main initialization function. This calls the pipeline to process the
     * request. If there is a problem processing, then an error is logged.
     * 
     * @return void
     */
    static function fiftyonedegrees_init() {

        // Error logging happens inside process().
        Pipeline::process();

    }
      
    /**
     * Register the settings used by the plugin.
     * 
     * @return void
     */
    function fiftyonedegrees_register_settings() {
        // This is the cached pipeline for the current Resource Key.
        add_option(Options::PIPELINE);
        // This is the Resource Key set by the user to be used to access
        // cloud services.
        add_option(Options::RESOURCE_KEY);

        // Register the new settings with wordpress.
        register_setting(
            Options::GROUP_KEY,
            Options::RESOURCE_KEY);
    }

    /**
     * Register the JSON endpoint for the pipeline. This is where the
     * JavaScript will callback to instead of the 51Degrees domain.
     * 
     * @return void
     */
    function fiftyonedegrees_rest_api_init() {	
        register_rest_route('fiftyonedegrees/v4', "json", array(
            'methods' => 'POST',
            'args' => array(),
            'callback' => array('Pipeline','getJSON'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Checks if the Resource Key has been changed, and stores the new one
     * if it has. When the new option has been updated, the pipeline will be
     * rebuilt.
     * 
     * @return void
     */
    function submit_rk_submit_action() {

        if (isset($_POST[Options::RESOURCE_KEY]) &&
            isset($_POST["action"]) &&
            $_POST[Options::RESOURCE_KEY] !==
            get_option(Options::RESOURCE_KEY)) {

            $resource_key = sanitize_text_field(wp_unslash(
                $_POST[Options::RESOURCE_KEY]));
            update_option(Options::RESOURCE_KEY, $resource_key);

            if (!isset($cachedPipeline['error'])) {
                if (get_option(Options::ENABLE_GA) &&
                    get_option(Options::RESOURCE_KEY_UPDATED)) {
                
                    wp_redirect(get_admin_url() .
                        'options-general.php?page=51Degrees&tab=google-analytics');
                    exit();
                }
            }
            else {
                wp_redirect(get_admin_url() .
                    'options-general.php?page=51Degrees&tab=setup' );
                exit();
            }

        }
    }

    /**
     * Add stylesheet for admin pages.
     * 
     * @return void
     */
    function fiftyonedegrees_admin_enqueue_scripts() {
        wp_enqueue_style(
            "fiftyonedegrees_admin_styles",
            plugin_dir_url(__FILE__) . "../assets/css/fod.css");
        wp_enqueue_style(
            "fiftyonedegrees_admin_styles_icons",
            "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css");
        wp_enqueue_script(
            "fiftyonedegrees_jQuery",
            plugin_dir_url(__FILE__) . '../assets/js/51D.js',
            array('jquery') #dependencies
        );			
    }

    /**
     * After any option is updated, check if the option was something that
     * needs to be taken care of. For Resource Key, the flow data needs to
     * be removed from the session cache, and a new pipeline created.
     * 
     * @return void
     */
    function fiftyonedegrees_update_option($option, $old_value, $new_value) {

        if ($option === Options::RESOURCE_KEY) {

            // Remove the cached flowdata from the session.
            if (session_status() === PHP_SESSION_ACTIVE &&
                isset($_SESSION["fiftyonedegrees_data"])) {
                unset($_SESSION["fiftyonedegrees_data"]);
                update_option(
                    Options::SESSION_INVALIDATED,
                    time());
            }

            $pipeline = Pipeline::make_pipeline($new_value);

            if ($pipeline) {
                update_option(
                    Options::PIPELINE,
                    $pipeline);
            }

            if ($old_value !== $new_value) {
                update_option(Options::RESOURCE_KEY_UPDATED, true);
                delete_option(Options::GA_DIMENSIONS);
            }
            else {
                delete_option(Options::RESOURCE_KEY_UPDATED);
            }
            
        }

        if ($option === Options::GA_TRACKING_ID &&
            $old_value !== $new_value) {
            update_option(Options::GA_ID_UPDATED, true);
            delete_option(Options::GA_DIMENSIONS);
        }

        if ($option === Options::GA_SEND_PAGE_VIEW_VAL &&
            $old_value !== $new_value) {
            update_option(Options::GA_SEND_PAGE_VIEW_UPDATED, true);
        }
    }

    /**
     * Register the options page for the plugin.
     * 
     * @return void
     */
    function fiftyonedegrees_register_options_page() {
        add_options_page(
            '51Degrees',
            '51Degrees',
            'manage_options',
            '51Degrees',
            array($this, 'fiftyonedegrees_admin_page'));
    }


    /**
     * Set the link to settings for this plugin.
     * 
     * @param string[] $links array of links to add to.
     * @return string[] updated array of links.
     */
    function fiftyonedegrees_add_plugin_page_settings_link($links) {
        $links[] = '<a href="' .
            admin_url('options-general.php?page=51Degrees') .
            '">' . __('Settings') . '</a>';
        return $links;
    }

    /**
     * Inlude the admin page for this plugin.
     * 
     * @return void
     */
    function fiftyonedegrees_admin_page() {
        include plugin_dir_path(__FILE__) . "../admin.php";
    }

    /**
     * Add the 51Degrees JavaScript to the page.
     * 
     * @return void
     */
    function fiftyonedegrees_javascript() {
        wp_enqueue_script(
            "fiftyonedegrees",
            plugin_dir_url(__FILE__) . "../assets/js/fod.js");
        wp_add_inline_script(
            "fiftyonedegrees",
            Pipeline::getJavaScript(),
            "before");
    }

    /**
     * Block filter function to replace tokens in the format
     * '{Pipeline::get("engine","property")}'.
     * 
     * @param string $block_content the existing blocb content to parse
     * @param object $block not used in this function
     * @return string the updated block content
     */
    public function fiftyonedegrees_block_filter($block_content, $block) {
        $content = $block_content;
        $pattern = '/\{Pipeline::get\("[A-Za-z]+",[ ]*"[A-Za-z]+"\)\}/';
        preg_match_all(
            $pattern,
            $block_content,
            $matches,
            PREG_PATTERN_ORDER);

        foreach ($matches as $pattern_matches) {
            foreach ($pattern_matches as $match) {
                $args = str_replace("{Pipeline::get(", "", $match);
                $args = str_replace(")}", "", $args);
                $args = str_replace("\"", "", $args);
                $args = str_replace(" ", "", $args);
                $args = explode(",", $args);

                $value = Pipeline::get($args[0], $args[1]);

                switch (gettype($value)) {
                    case "string":
                        break;
                    case "boolean":
                        if ($value) {
                            $value = "true";
                        } else {
                            $value = "false";
                        }
                        break;
                    case "array":
                        $value = implode(",", $value);
                        break;
                    default:
                        $value = json_encode($value);
                }

                $content = str_replace($match, $value, $content);
            }
        }
        return $content;
    }
    
    /**
     * Add a '51Degrees' category to the list of block categories
     * available in the editor.
     * 
     * @param array $categories the existing list of categories
     * @return object the updated categories including the 51Degrees category
     */
    function fiftyonedegrees_block_categories($categories) {
        $category_slugs = wp_list_pluck($categories, 'slug');

        return in_array('51Degrees', $category_slugs, true) ?
            $categories :
            array_merge(
                $categories,
                array(
                    array(
                        'slug'  => '51Degrees',
                        'title' => __( '51Degrees', '51D' ),
                        'icon'  => null,
                    ),
                )
        );
    }

    /**
     * Setup everything needed for editing 51Degrees blocks. For example,
     * a list of properties is initialized to be used in a drop down list.
     * 
     * @return void
     */
    function fiftyonedegrees_setup_blocks() {

        wp_register_script(
            'fiftyonedegrees-conditional-group-block',
            plugins_url('../conditional-group-block/build/index.js', __FILE__),
            [
                'wp-i18n',
                'wp-element',
                'wp-blocks',
                'wp-components',
                'wp-editor'
            ],
            '1.0.0'
        );

        wp_register_style(
            'fiftyonedegrees-conditional-group-block',
            plugins_url('../conditional-group-block/src/editor.css', __FILE__),
            [],
            '1.0.0'
        );
            
        register_block_type(
            'fiftyonedegrees/conditional-group-block',
            [
                'editor_script' => 'fiftyonedegrees-conditional-group-block',
                'style'         => 'fiftyonedegrees-conditional-group-block',
            ]
        );
        
        // Add list of properties to select field in editor interface
        $propertySelect = [
            [
                "label" => "Property", 
                "value" => ""
            ]
        ];
        if (Pipeline::$data) {
            foreach (Pipeline::$data["properties"] as $dataKey =>
                $engineProperties) {
                foreach ($engineProperties as $property) {
                    $propertySelect[] = array(
                        "label" => strtolower(
                            $property["name"] . " (" . $dataKey . ")"),
                        "value" => strtolower(
                            $dataKey . "|" . $property["name"])
                    );
                }
            }

            wp_localize_script(
                "fiftyonedegrees-conditional-group-block",
                'fiftyoneProperties',
                $propertySelect);
        }
    }
    
    /**
     * Handles conditional blocks.
     * Compares the target property value set in the block with the actual
     * property value from the flow data for the requesting device using the
     * operator specified in the block. If the result is true then the block
     * is rendered, otherwise not.
     * 
     * @param string $block_content content of the block to potentially be
     * displayed
     * @param object $block the block itself, containing the options used to
     * determine whether to display the content
     * @return string|null either the value of $block_content if the condition
     * is met, otherwise null
     */
    function fiftyonedegrees_render_block($block_content, $block) {

        if ('fiftyonedegrees/conditional-group-block' === $block['blockName']) {

            if(isset($block["attrs"]["property"]) &&
                !empty($block["attrs"]["property"]) &&
                isset($block["attrs"]["operator"]) &&
                !empty($block["attrs"]["operator"]) &&
                isset($block["attrs"]["value"]) &&
                !empty($block["attrs"]["value"])){

                $property = $block["attrs"]["property"];

                // Split property and engine by pipe
                $engineDataKey = explode("|", $property)[0];
                $propertyName = explode("|", $property)[1];

                // Get property value
                $value = Pipeline::get($engineDataKey, $propertyName);

                // JSON encode to string if not a string already
                if (!is_string($value)) {

                    $value = json_encode($value);
                }

                $compareValue = $block["attrs"]["value"];

                if (empty($compareValue)) {
                    return null;       
                }

                $operator = $block["attrs"]["operator"];

                // Default to not show and then overwrite based on
                // operator rules
                $show = false;

                switch ($operator) {
                    case "is":
                        $show = $value === $compareValue;
                        break;
                    case "not":
                        $show = $value !== $compareValue;
                        break;
                    case "contains":
                        $show = strpos($value, $compareValue) !== false;
                        break;
                }
                
                if (!$show) {
                    return null;
                }

            } else {
                return null;
            }
            
        }

        return $block_content;       
    }
}
?>
