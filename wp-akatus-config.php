<?php
// Avoid to load this file directly
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) and ( __FILE__ == basename( $_SERVER['SCRIPT_FILENAME'] ) ) )
    exit();

// Define base name and base page
$base_name = plugin_basename( 'wp-akatus/wp-akatus-config.php' );
$base_page = 'admin.php?page=' . $base_name;

// Check if form is submited and call methods needed to save it
if( isset( $_POST['submit'] ) ) :
    
    $save = $wp_akatus->save_settings();

    if( is_wp_error( $save ) )
        $wp_akatus->display_error_message( $save );

    if( $save and !is_wp_error( $save ) )
        $wp_akatus->display_updated_message( 'Configurações salvas com sucesso.' );
    
endif;

// Gets WP Akatus Seller E-mail
$wp_akatus_seller_email = get_option( 'wp_akatus_seller_email' );
$wp_akatus_env          = get_option( 'wp_akatus_env' );              
?>

<div class="wrap">
    <div class="icon32">
        <img src="<?php echo WP_PLUGIN_URL . '/wp-akatus/assets/images/wp-akatus-32.png' ?>" />
    </div>
    <h2><?php _e( 'WP Akatus - Configurações', 'wp-akatus' ); ?></h2>
    <br />
    <p>Com o plugin WP Akatus ficou mais fácil fazer e receber pagamentos online. É só configurar o plugin com sua conta Akatus, inserir o nosso carrinho de compras e começar a vender. Não tem uma conta na Akatus? <a href="https://www.akatus.com/users/sign_up" target="_blank">Crie uma gratuitamente</a>.</p>
    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="wp-akatus-seller-email">E-mail do vendedor</label>
                    </th>
                    <td>
                        <input type="email" id="wp-akatus-seller-email" name="wp_akatus_seller_email" class="regular-text" value="<?php echo $wp_akatus_seller_email; ?>" required />
                        <span class="description">Este e-mail deve ser o mesmo utilizado para fazer login na Akatus.</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wp-akatus-env">Usar o plugin em ambiente de produção?</label>
                    </th>
                    <td>
                        <input type="radio" id="wp-akatus-env-prod" name="wp_akatus_env" value="prod" <?php echo ( $wp_akatus_env == 'prod' || empty( $wp_akatus_env ) ) ? 'checked="checked" ' : ''; ?>/>
                        <label for="wp-akatus-env-prod">Sim</label>
                        <input type="radio" id="wp-akatus-env-dev" name="wp_akatus_env" value="dev" <?php echo ( $wp_akatus_env == 'dev' ) ? 'checked="checked" ' : ''; ?>/>
                        <label for="wp-akatus-env-dev">Não</label>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" id="submit" name="submit" class="button-primary" value="Salvar configurações" />
        </p>
    </form>
</div>