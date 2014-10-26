<?php
/**
 * Plugin Name: Outpost
 * Plugin URI: http://outpost.rocks
 * Description: Create local development versions of live WordPress sites, or fresh WordPress environments from scratch.
 * Version: 0.1.3
 * Author: Nick Cernis
 * Author URI: http://goburo.com
 * License: GPLv2+
 */

/*
TODO: Test plugins in mu-plugins directory
TODO: Check unlink(), copy() etc. are available
TODO: Autodetect Apache/Nginx, PHP version, WP version - but allow overrides - and create outpost config file.
TODO: Stagger file copying, database dumps, and zipping to avoid timeouts with larger files and databases (use JS on timer, like Regenerate Thumbnails plugin)
TODO: Give user progress feedback (Store progress with PHP using http://codex.wordpress.org/Transients_API then poll to get progress with JS.)
TODO: WP multisite support?
TODO: Setup for hacking on WP itself?
TODO: Give users option to store wp-config.php file?
*/


include_once(dirname(__FILE__) . '/plugin/inc/class.outpost-utils.php');
include_once(dirname(__FILE__) . '/plugin/inc/class.outpost-db-dump.php');

class Outpost_Rocks
{
    private $upload_dir;
    private $outpost_dir;
    private $outpost_content_dir;
    private $outpost_data_dir;
    private $outpost_plugins_dir;
    private $outpost_themes_dir;

    function __construct()
    {
        $this->upload_dir = wp_upload_dir();
        $this->outpost_dir = $this->upload_dir['basedir'] . '/' . 'outpost' . uniqid();
        $this->outpost_content_dir = $this->outpost_dir . '/wp/wp-content';
        $this->outpost_data_dir = $this->outpost_dir . '/wp/wp-data';
        $this->outpost_plugins_dir = $this->outpost_content_dir . '/' . 'plugins';
        $this->outpost_themes_dir = $this->outpost_content_dir . '/' . 'themes';

        register_activation_hook(__FILE__, array(&$this, 'install'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
        add_action('admin_menu', array(&$this, 'add_menu'));

    }

    function install()
    {
        $outpost_options = array();
        update_option('outpostcc_options', $outpost_options);
    }

    function deactivate()
    {

    }

    function add_menu()
    {
        if ( isset($_POST['submit']) && isset($_POST['generate_outpost']) ) {
            if (current_user_can('export'))
                $this->generate_outpost();
        } else {
            add_management_page('Outpost', 'Outpost', 'manage_options', 'outpostrocks', array($this, 'show_admin_page'));
        }
    }

    function show_admin_page()
    {
        load_template(dirname(__FILE__) . '/plugin/templates/options-page.php');
    }

    function generate_outpost()
    {
        set_time_limit(0);

        // Create a blank index.php file in the uploads directory to prevent directory listing
        file_put_contents($this->upload_dir['basedir'] . '/index.php', '<?php // silence is golden');

        // Create the Outpost directory structure in the uploads folder
//        Outpost_Utils::delete_file_or_folder($this->outpost_dir); // cleanup old outpost if required
        Outpost_Utils::copy_file_or_folder(dirname(__FILE__), $this->outpost_dir);
        
        // Dump the database
        $db_dump = new Outpost_DB_Dump($this->outpost_data_dir . '/outpost.sql');

        // Copy all plugins across
        $plugins = count($_POST['plugins']) ? $_POST['plugins'] : array();

        foreach ($plugins as $plugin){
            $plugin_location = WP_PLUGIN_DIR . '/' . $plugin;
            $plugin_root_directory = dirname($plugin_location);

            // If the plugin is a single naked PHP file in the plugin root directory, copy that file
            if ($plugin_root_directory == WP_PLUGIN_DIR){
                $new_plugin_location = $this->outpost_plugins_dir . '/' . $plugin;
                Outpost_Utils::copy_file_or_folder($plugin_location, $new_plugin_location);
            }

            // If the plugin is a PHP file in a folder, copy the entire folder
            if ($plugin_root_directory != WP_PLUGIN_DIR){
                $new_plugin_location = $this->outpost_plugins_dir . substr($plugin_root_directory, strlen(WP_PLUGIN_DIR));
                Outpost_Utils::copy_file_or_folder($plugin_root_directory, $new_plugin_location);
            }

        }

        // Copy themes across
        $themes = count($_POST['themes']) ? $_POST['themes'] : array();

        foreach ($themes as $theme){
            $theme_dir = get_theme_root($theme) . '/' . $theme;
            $new_theme_dir = $this->outpost_themes_dir . '/' . $theme;
            Outpost_Utils::copy_file_or_folder($theme_dir, $new_theme_dir);
        }

        // Compress the generated Outpost into a zip file
        $outpost_zip = $this->upload_dir['basedir'] . '/' . 'outpost-' . uniqid() . '.zip';

        $zip = Outpost_Utils::zip($this->outpost_dir, $outpost_zip, true);

        // Send it to the browser to download
        if ($zip) {
            $zip_name = (isset($_SERVER['HTTP_HOST']) ? "$_SERVER[HTTP_HOST].zip" : 'outpost.zip');
            Outpost_Utils::download_file($outpost_zip, $zip_name, true);
        }

        //echo '<div id="message2" class="updated"><p>An Outpost was generated. <a href="http://my.outpost.rocks/">Click here to download it again.</a></p></div>';
    }

}

$outpost_rocks = new Outpost_Rocks();