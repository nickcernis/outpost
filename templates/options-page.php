<div class="wrap">
    <div id="icon-tools" class="icon32"><br></div>
    <h2>Outpost</h2>

    <div class="tool-box">
        <p>Generate a packaged version of this site to download for development use on your local machine.</p>

        <form action="" method="post">
            <h3>Themes to export</h3>
            <?php
            $themes = wp_get_themes();
            $active_theme = wp_get_theme();

            foreach ($themes as $theme => $details) {
                $checked = '';
                if ($active_theme->name == $details->name)
                    $checked = 'checked';

                if ($details->name == $active_theme->parent_theme) {
                    $checked = 'checked';
                }

                echo '<label>';
                echo '<input type="checkbox" value="' . $theme . '" name="themes[]"' . $checked . ' /> ';
                echo $details;
                if ($details->name == $active_theme->name) {
                    echo ' [active]';
                } else if ($details->name == $active_theme->parent_theme) {
                    echo ' [parent]';
                }
                echo '</label>';
                echo '<br/>';
            }
            ?>

            <h3>Plugins to export</h3>
            <?php
            $plugins = get_plugins();

            foreach ($plugins as $file => $details) {
//                echo WP_PLUGIN_DIR . "/" . $file . "<br>";
                if (strpos($file, 'outpostcc.php'))
                    continue; // don't offer the Outpost plugin as a download option

                echo '<label>';
                echo '<input type="checkbox" value="' . $file . '" name="plugins[]"' . checked(is_plugin_active($file), true, false) . ' /> ';
                echo $details['Name'];
                if (is_plugin_active($file))
                    echo ' [active]';
                echo '</label>';
                echo '<br/>';
            }
            ?>
            <p><input type="submit" name="submit" value="Download Your Outpost" class="button button-primary"/></p>
        </form>

    </div>
</div>