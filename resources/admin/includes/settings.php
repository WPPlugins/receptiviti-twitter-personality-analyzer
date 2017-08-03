<div class="wrap">
    <h2><?php _e("Settings", RECEPTIVITI_PLUGIN_SLUG__);?></h2>

<?php
    $active_tab = isset( $_REQUEST[ 'tab' ] ) ? $_REQUEST[ 'tab' ] : "api";
?>
<?php
    $plugin_version = "1.1.28";
?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=<?php echo RECEPTIVITI_PLUGIN_SLUG__;?>&tab=api" class="nav-tab <?php echo $active_tab == 'stripe' ? 'nav-tab-active' : ''; ?>"><?php _e("API", RECEPTIVITI_PLUGIN_SLUG__);?></a>
    </h2>

    <form method="post" action="">
        <table class="form-table">
<?php
    if( $active_tab == 'api' ) {
?>
            <tr valign="top">
                <th scope="row"><?php _e("API Key", RECEPTIVITI_PLUGIN_SLUG__);?></th>
                <td><input type="text" name="key" id="key" value="<?php echo self::getOption("key");?>" class="regular-text"></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("Secret API Key", RECEPTIVITI_PLUGIN_SLUG__);?></th>
                <td><input type="text" name="secret" id="secret" value="<?php echo self::getOption("secret");?>" class="regular-text"></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("Override URL", RECEPTIVITI_PLUGIN_SLUG__);?></th>
                <td><input type="text" name="app_url" id="app_url" value="<?php echo self::getOption("app_url");?>" class="regular-text"></td>
            </tr>
<?php
    }
?>
        </table>
    
        <input type="hidden" name="tab" value="<?php echo $active_tab;?>">
        <input type="hidden" name="plugin_version" value="<?php echo $plugin_version;?>">
        <?php submit_button(__("Save Changes", RECEPTIVITI_PLUGIN_SLUG__), "primary", "ra-settings"); ?>
    </form>
