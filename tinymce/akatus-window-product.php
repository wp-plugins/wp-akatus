<?php
if ( !function_exists( 'add_action' ) ) {
    $wp_root = realpath( dirname( __FILE__ ) . '/../../../..');

    if ( file_exists( $wp_root . '/wp-load.php' ) )
        require_once $wp_root . '/wp-load.php';
    else
        require_once $wp_root . '/wp-config.php';
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php _e( 'WP Akatus', 'wp-akatus' ); ?></title>
        <?php wp_print_head_scripts(); ?>
        <script language="javascript" type="text/javascript" src="<?php echo get_bloginfo( 'url' ); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_bloginfo( 'url' ); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo WP_PLUGIN_URL . '/wp-akatus/assets/javascript/tinymce-popup.js?v=' . filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/javascript/tinymce-popup.js' ); ?>"></script>
        <script language="javascript" type="text/javascript" src="<?php echo WP_PLUGIN_URL . '/wp-akatus/assets/javascript/jquery-meio-mask.js?v=' . filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/javascript/jquery-meio-mask.js' ); ?>"></script>
        <script language="javascript" type="text/javascript" src="<?php echo WP_PLUGIN_URL . '/wp-akatus/assets/javascript/tinymce-window.js?v=' . filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/javascript/tinymce-window.js' ); ?>"></script>
        <link rel='stylesheet' href="<?php echo WP_PLUGIN_URL . '/wp-akatus/assets/css/tinymce-popup-style.css?v=' . filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/css/tinymce-popup-style.css' ); ?>" type='text/css' media='all' />
        <base target="_self" />
        
    </head>
    <body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
        <form id="wp_akatus_form" action="#">
            <table border="0" cellpadding="4" cellspacing="0">
                <tr>
                    <td nowrap="nowrap">
                        <label for="wp-akatus-product-name">
                            <?php _e( 'Nome do produto', 'wp-akatus' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" id="wp-akatus-product-name" name="product_name" value="" />
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap">
                        <label for="wp-akatus-product-price">
                            <?php _e( 'Preço do produto', 'wp-akatus' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" id="wp-akatus-product-price" name="product_price" value="" placeholder="Ex: 9,99"/>
                    </td>
                </tr>
            </table>
            <div class="mceActionPanel">
                <p>
                    <div id="button-cancel">
                        <input type="button" id="cancel" name="cancel" value="<?php _e( "Cancelar", 'wp-akatus' ); ?>" onclick="tinyMCEPopup.close();" />
                    </div>
                    <div id="button-insert">
                        <input type="submit" id="insert" name="insert" value="<?php _e( "Inserir", 'wp-akatus' ); ?>" onclick="WPAkatusInsertProductShortcode();" />
                    </div>
                </p>
            </div>
        </form>
    </body>
</html>