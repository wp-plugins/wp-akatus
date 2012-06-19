<?php
/*
Plugin Name: WP Akatus
Plugin URI: http://connect.akatus.com/
Version: 1.0
Description: Instale o carrinho de compras da Akatus em seu site WordPress. A melhor forma de pagar e receber pagamentos online. É rápido, fácil e seguro.
Author: Apiki WordPress
Author URI: http://apiki.com

Copyright 2012 Apiki WordPress

This program is a free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Free icons used

Icons title: Bimbilini, Humility, Refresh
Icons author: Visual-Blast Magazine, Andy Fitzsimon, Futurosoft
Icons licence: GPL
*/

// Avoid to load this file directly
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) and basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) )
    exit();

class WP_Akatus {
    
    /**
     * Capability name
     * 
     * @var string Capability name 
     */
    public $capability = 'manage_wp_akatus';
    
    /**
     * Construct method
     * 
     * @since 1.0
     */
    public function __construct()
    {
        add_action( 'activate_wp-akatus/wp-akatus.php', array( &$this, 'install' ) );
        
        add_action( 'wp_print_scripts', array( &$this, 'scripts' ) );
        add_action( 'wp_print_styles',  array( &$this, 'styles' ) );
        add_action( 'widgets_init',     array( &$this, 'widgets' ) );
        add_action( 'admin_menu',       array( &$this, 'menu' ) );
        add_action( 'admin_init',       array( &$this, 'tinymce_button' ) );
        add_action( 'get_header',       array( &$this, 'track_product_click' ) );
        add_action( 'get_header',       array( &$this, 'track_change_product_quantity' ) );
        add_action( 'get_header',       array( &$this, 'track_delete_product_from_cart' ) );
        add_action( 'get_header',       array( &$this, 'track_checkout' ) );
        add_action( 'admin_notices',    array( &$this, 'admin_config_notice' ) );
        
        add_shortcode( 'wp-akatus-product', array( &$this, 'shortcode' ) );
    }
    
    /**
     * Installs
     * 
     * @since 1.0
     * @return void
     */
    public function install()
    {
        $this->_add_capability( $this->capability, 'administrator' );
    }
    
    /**
     * Displays an admin notice when requires to set plugin settings
     * 
     * @since 1.0
     * @return void
     */
    public function admin_config_notice()
    {
        $wp_akatus_seller_email = get_option( 'wp_akatus_seller_email' );
        
        if( empty( $wp_akatus_seller_email ) )
            echo '<div class="error"><p>O plugin WP Akatus precisa ser configurado. <a href="' . admin_url( 'options-general.php?page=wp-akatus/wp-akatus-config.php' ) . '">Ir para a página de configurações do plugin</a>.</p></div>';
    }
    
    /**
     * Tracks product click
     * 
     * @since 1.0
     * @return void 
     */
    public function track_product_click()
    {
        if( !isset( $_GET['add_to_cart'] ) )
            return;
        
        $product_name   = (isset( $_GET['product_name'] ) )     ? esc_html( $_GET['product_name'] )     : __( 'Nome do produto não identificado', 'wp-akatus' );
        $product_price  = ( isset( $_GET['product_price'] ) )   ? esc_html( $_GET['product_price'] )    : __( 'Preço do produto não identificado', 'wp-akatus' );
        
        $this->add_to_cart( $product_name, $product_price );
    }
    
    /**
     * Tracks change product quantity
     * 
     * @since 1.0
     * @return boolean True if success, otherwise returns false
     */
    public function track_change_product_quantity()
    {
        if( !isset( $_POST['change_product_quantity'], $_POST['change_product_name'] ) )
            return;
     
        $product_quantity   = intval( $_POST['change_product_quantity'] );
        $product_name       = esc_html( $_POST['change_product_name'] );
        
        if( $product_quantity <= 0 )
            return;
     
        $shopping_cart_id       = $this->get_shopping_cart_id();
        $shopping_cart_products = $this->get_shopping_cart_products( $shopping_cart_id );
        
        if( isset( $shopping_cart_products[$product_name] ) )
            $shopping_cart_products[$product_name]['product_quantity'] = $product_quantity;
        
        $change_product_quantity = set_transient( 'shopping_cart_' . $shopping_cart_id, $shopping_cart_products, $this->get_shopping_cart_expiration_time() );
     
        if( $change_product_quantity )
            return true;
     
        return false;
    }
    
    /**
     * Tracks delete product from cart
     * 
     * @since 1.0
     * return void
     */
    public function track_delete_product_from_cart()
    {
        if( !isset( $_GET['delete_from_cart'] ) )
            return;
        
        $product_name = esc_html( $_GET['delete_from_cart'] );
                
        $shopping_cart_id       = $this->get_shopping_cart_id();
        $shopping_cart_products = $this->get_shopping_cart_products( $shopping_cart_id );
        
        if( isset( $shopping_cart_products[$product_name] ) )
            unset( $shopping_cart_products[$product_name] );
                
        $delete_product = set_transient( 'shopping_cart_' . $shopping_cart_id, $shopping_cart_products, $this->get_shopping_cart_expiration_time() );
     
        wp_redirect( add_query_arg( 'deleted_from_cart', ( $delete_product ) ? 'success' : 'error', remove_query_arg( $this->remove_query_args( array( 'deleted_from_cart' ) ) ) ) );
        exit(0);
    }
    
    /**
     * Tracks checkout
     * 
     * #since 1.0
     * @return void
     */
    public function track_checkout()
    {
        if( !isset( $_POST['akatus_checkout'] ) )
            return;
        
        $shopping_cart_id       = $this->get_shopping_cart_id();
        $shopping_cart_products = $this->get_shopping_cart_products( $shopping_cart_id );
        
        $this->delete_shopping_cart_id( $shopping_cart_id );
        echo $this->generate_submit_form( $shopping_cart_products );
        echo '<script type="text/javascript">document.getElementById("akatus-checkout").submit();</script>';
    }
    
    /**
     * Delete Shopping Cart by ID
     * 
     * @since 1.0
     * @param string $shopping_cart_id Cart ID
     */
    public function delete_shopping_cart_id( $shopping_cart_id )
    {
        $shopping_cart_cookie_name = 'wp_akatus_shopping_cart_id';
        
        if( isset( $_COOKIE[$shopping_cart_cookie_name] ) )
            setcookie( $shopping_cart_cookie_name, "", time() - 99999999999999, '/' );
        
        if( get_transient( 'shopping_cart_' . $shopping_cart_id ) )
            delete_transient( 'shopping_cart_' . $shopping_cart_id );
    }
    
    /**
     * Builds the menu
     * 
     * @since 1.0
     * @return void
     */
    public function menu()
    {
        add_menu_page( 'WP Akatus', 'WP Akatus', $this->capability, 'wp-akatus/wp-akatus-config.php', null, WP_PLUGIN_URL . '/wp-akatus/assets/images/wp-akatus-16.png' );
    }        
    
    /**
     * Displays the shopping cart
     * 
     * @since 1.0
     * @return void
     */
    public function shopping_cart()
    {
        $products_total_value   = 0;
        $set_shopping_cart      = false;
        $shopping_cart_products = array();
        $shopping_cart_id       = $this->get_shopping_cart_id( $set_shopping_cart );
        
        if( $shopping_cart_id )
            $shopping_cart_products = $this->get_shopping_cart_products( $shopping_cart_id );
        
        $products_quantity = ( !empty( $shopping_cart_products ) ) ? count( $shopping_cart_products) : 0;
        
        $output  = "";
        $output .= '<div class="widget-content wp-akatus-widget-cart">';
        
        if( $shopping_cart_id and !empty( $shopping_cart_products ) ) :
            
            $i = 1;
        
            foreach( (array)$shopping_cart_products as $product_name => $product_data ) :
            
                $product_quantity       = $product_data['product_quantity'];
                $product_price          = $product_data['product_price'];
                $_product_price         = str_replace( '.', '', $product_price );    
                $_product_price         = str_replace( ',', '.', $_product_price );
                $products_total_value   = $products_total_value + floatval( $_product_price * $product_quantity );
                $output .= '<div class="product">';
                $output .= '<div class="product-data">';
                $output .= '<div class="product-name">';
                $output .= $product_name;
                $output .= '</div>';
                $output .= '<div class="product-price">';
                $output .= 'R$ ' . $product_price;
                $output .= '</div>';
                $output .= '<div class="product-quantity">';
                $output .= '<form action="" method="post">';
                $output .= '<input type="text" id="change-product-quantity-' . $i . '" name="change_product_quantity" value="' . $product_data['product_quantity'] . '" size="1" class="quantity" /><span> ' . __( 'Quantidade', 'wp-akatus' ) . ' </span>';
                $output .= '<input type="hidden" id="change-product-name-' . $i . '" name="change_product_name" value="' . $product_name . '" />';
                $output .= '<input type="submit" id="submit-change-product-quantity-' . $i . '" name="submit_change_product_quantity" value="' . __( 'Atualizar', 'wp-akatus' ) . '" title="Atualizar a quantidade deste produto" />';
                $output .= '<a class="product-delete" href="' . add_query_arg( 'delete_from_cart', $product_name, remove_query_arg( $this->remove_query_args( array( 'delete_from_cart' ) ) ) ) . '" title="' . __( 'Remover este produto do carrinho', 'wp-akatus' ) . '">' . __( 'Remover do carrinho', 'wp-akatus' ) . '</a>';
                $output .= '<div id="quantity-info-' . $i . '" class="info">Pressione ENTER para confirmar a quantidade ou clique em ATUALIZAR</div>';
                $output .= '</form>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';
                
                $i++;
                
            endforeach;
        else :
            $output .= '<div class="no-product">';
            $output .= '<p>' . __( 'Nenhum produto foi adicionado ao seu carrinho de compras.', 'wp-akatus' ) . '</p>';
            $output .= '</div>';
        endif;
        
        if( !empty( $products_quantity ) ) :
            $output .= '<div class="products-total-value">';
            $output .= 'Valor dos produtos: <span>R$ ' . number_format( $products_total_value, 2, ',', '.' ) . '</span>';
            $output .= '</div>';
            $output .= '<div class="checkout">';
            $output .= '<form id="akatus-checkout" target="akatus" method="post" action="">';
            $output .= '<input type="submit" name="akatus_checkout" value="Pagar com Akatus" />';
            $output .= '</form>';
            $output .= '</div>';
        endif;
        $output .= '</div>';
        
        echo $output;
    }
    
    /**
     * Adds a product to cart
     * 
     * @since 1.0
     * @param string $product_name Product name
     * @param string $product_price Product price
     */
    public function add_to_cart( $product_name, $product_price )
    {
        $shopping_cart_id       = $this->get_shopping_cart_id();
        $shopping_cart_products = $this->get_shopping_cart_products( $shopping_cart_id );
        
        if( !$shopping_cart_products ) :
            
            $shopping_cart_products_list = array();
        
            $shopping_cart_products_list[$product_name] = array( 
                'product_price'     => $product_price,
                'product_quantity'  => 1
            );
            
            $add_to_cart = set_transient( 'shopping_cart_' . $shopping_cart_id, $shopping_cart_products_list, $this->get_shopping_cart_expiration_time() );
            
        else :
            
            if( isset( $shopping_cart_products[$product_name] ) ) :
                $shopping_cart_products[$product_name]['product_quantity']++;
            else :
                $shopping_cart_products[$product_name]['product_quantity'] = 1;
                $shopping_cart_products[$product_name]['product_price'] = $product_price;
            endif;
            
            $add_to_cart = set_transient( 'shopping_cart_' . $shopping_cart_id, $shopping_cart_products, $this->get_shopping_cart_expiration_time() );
            
        endif;
        
        wp_redirect( add_query_arg( 'added_to_cart', ( $add_to_cart ) ? 'success' : 'error', remove_query_arg( $this->remove_query_args( array( 'added_to_cart' ) ) ) ) );
        exit(0);
    }
    
    /**
     * Generates submit form with products
     * 
     * @since 1.0
     * @param array $shopping_cart_products Products
     * @return string Submit form HTML
     */
    public function generate_submit_form( $shopping_cart_products )
    {
        $action = ( get_option( 'wp_akatus_env' ) == 'dev' ) ? 'https://dev.akatus.com/carrinho/' : 'https://www.akatus.com/carrinho/';
        
        $output .= '<form id="akatus-checkout" target="akatus" method="post" action="' . $action . '" style="display:none">';
        $output .= '<input type="hidden" name="email_cobranca" value="' . get_option( 'wp_akatus_seller_email' ) . '" />';
        $output .= '<input type="hidden" name="tipo" value="CP" />';
        $output .= '<input type="hidden" name="moeda" value="BRL" />';
        
        $item = 1;
        
        foreach( (array)$shopping_cart_products as $product_name => $product_data ) :
            
            $output .= sprintf( '<input type="hidden" name="item_id_%1$d" value="%1$d" />', $item );
            $output .= sprintf( '<input type="hidden" name="item_descr_%d" value="%s" />', $item, esc_html( $product_name ) );
            $output .= sprintf( '<input type="hidden" name="item_quant_%d" value="%d" />', $item, intval( $product_data['product_quantity'] ) );
            $output .= sprintf( '<input type="hidden" name="item_valor_%d" value="%s" />', $item, str_replace( array('.',','), '', $product_data['product_price'] ) );
            $output .= '<input type="hidden" name="item_frete_1" value="0" />';
            $output .= '<input type="hidden" name="item_peso_1" value="0" />';
            $item++;
            
        endforeach;
        
        $output .= '<input type="submit" name="akatus_checkout" value="Pagar com Akatus" />';
        $output .= '</form>';
        
        $output .= 'Redirecionando...';
        
        return $output;
    }
        
    /**
     * Gets shopping cart products
     * 
     * @since 1.0
     * @return mixed
     */
    public function get_shopping_cart_products( $shopping_cart_id )
    {
        $shopping_cart_products = get_transient( 'shopping_cart_' . $shopping_cart_id );
        
        if( $shopping_cart_products )
            return $shopping_cart_products;
        
        return false;
    }
    
    /**
     * Gets shopping cart ID
     * 
     * @since 1.0
     * @param boolean $set_shopping_cart Set shopping cart
     * @return int|boolean Cart ID or false if not exists
     */
    public function get_shopping_cart_id( $set_shopping_cart = true )
    {
        $shopping_cart_cookie_name = 'wp_akatus_shopping_cart_id';
        
        if( isset( $_COOKIE[$shopping_cart_cookie_name] ) )
            return $_COOKIE[$shopping_cart_cookie_name];
        
        if( $set_shopping_cart )
            return $this->set_shopping_cart_id( $shopping_cart_cookie_name );
        
        return false;
    }
    
    /**
     * Gets shopping cart expiration time
     * 
     * @since 1.0
     * @return int Time 
     */
    public function get_shopping_cart_expiration_time()
    {
        return time() + 60 * 60 * 24;
    }
    
    /**
     * Sets shopping cart ID
     * 
     * @since 1.0
     * @param string $shopping_cart_cookie_name Cookie name
     * @return Shopping cart ID
     */
    public function set_shopping_cart_id( $shopping_cart_cookie_name )
    {
        $shopping_cart_id       = substr( wp_hash( time() ), 0, 10 );
        $shopping_cart_expire   = $this->get_shopping_cart_expiration_time();
        
        setcookie( $shopping_cart_cookie_name, $shopping_cart_id, $shopping_cart_expire, '/' );
        
        return $shopping_cart_id;
    }
    
    /**
     * Enqueue sctipts
     * 
     * @since 1.0
     * @return void
     */
    public function scripts()
    {
        wp_enqueue_script( 'akatus-script', WP_PLUGIN_URL . '/wp-akatus/assets/javascript/site-script.js', array( 'jquery' ), filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/javascript/site-script.js' ) );
    }
    
    /**
     * Enqueue styles
     * 
     * @since 1.0
     * @return void
     */
    public function styles()
    {
        wp_enqueue_style( 'akatus-style', WP_PLUGIN_URL . '/wp-akatus/assets/css/style.css', null,  filemtime( WP_PLUGIN_DIR . '/wp-akatus/assets/css/style.css' ) );
    }
    
    /**
     * Register widgets
     * 
     * @since 1.0
     * @return void
     */
    public function widgets()
    {
        require_once WP_PLUGIN_DIR . '/wp-akatus/widgets/wp-akatus-widget-cart.php';
        
        register_widget( 'WP_Akatus_Widget_Cart' );
    }
    
    /**
     * Reads the shortcode
     * 
     * @since 1.0
     * @param array $args 
     * @return string Output HTML 
     */
    public function shortcode( $args )
    {
        if( empty( $args['name'] ) || empty( $args['price'] ) )
            return;
            
        ob_start();
        extract( $args, EXTR_SKIP );
        ?>
        <div class="wp-akatus-add-to-cart">
            <a href="<?php echo add_query_arg( array( 'add_to_cart' => true, 'product_name' => $name, 'product_price' => $price ), remove_query_arg( $this->remove_query_args( array( 'add_to_cart', 'product_name', 'product_price' ) ) ) ); ?>" title="<?php echo sprintf( __( 'Adicionar %s ao carrinho.', 'wp-akatus' ), $name ); ?>">
                <span><?php _e( 'Adicionar ao carrinho', 'wp-akatus' ); ?></span>
            </a>
        </div>
        <?php
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }
    
    /**
     * Removes unneeded query args
     * 
     * @since 1.0
     * @param array $args_used Arguments used
     * @return array Arguments to remove in URL 
     */
    public function remove_query_args( $args_used )
    {
        $args_available = array(
            'add_to_cart',
            'product_name',
            'product_price',
            'added_to_cart',
            'delete_from_cart',
            'deleted_from_cart'
        );
        
        $args_to_remove = array();
        
        foreach( $args_available as $arg ) :
            
            if( !key_exists( $arg, $args_used ) )
                $args_to_remove[] = $arg;
                    
        endforeach;
        
        return $args_to_remove;
    }
    
    /**
     * Call the hooks for add the WP Akatus Product TinyMce button in the WordPress editor.
     * Runs with init hook.
     * 
     * @since 1.0
     * @return void
     */
    public function tinymce_button()
    {
        add_filter( 'mce_buttons',          array( &$this, 'tinymce_register_button') , 5 );
        add_filter( 'mce_external_plugins', array( &$this, 'tinymce_register_plugin'),  5 );
    }
    
    /**
     * Register the button in the array of buttons in the tinymce bar. Runs with
     * mce_buttons hook.
     *
     * @since 1.0
     * @param array $buttons The original array contains all buttons in tinymce
     * @return array Buttons
     */
    public function tinymce_register_button( $buttons )
    {        
        array_push( $buttons, 'separator', 'wp_akatus_product' );
        
        return $buttons;
    }
    
    /**
     * Register the TinyMCE javascript plugin in the array os plugins. Runs with
     * mce_external_plugin hook.
     *
     * @since 1.0
     * @param array $plugins The original array contains all plugins
     * @return Plugins
     */
    public function tinymce_register_plugin( $plugins )
    {
        $plugins['wp_akatus'] = WP_PLUGIN_URL . '/wp-akatus/assets/javascript/tinymce-plugin.js';
        
        return $plugins;
    }
    
    /**
     * Saves settings
     * 
     * @since 1.0
     * @return mixed
     */
    public function save_settings()
    {
        if( !isset( $_POST['wp_akatus_seller_email'] ) || !isset( $_POST['wp_akatus_env'] ) )
            return;

        $seller_email = esc_html( $_POST['wp_akatus_seller_email'] ); 
        
        if( !is_email( $seller_email ) )
            return new WP_Error ( 'wp_akatus_seller_email', 'Por favor, digite um endereço de e-mail válido.' );

        update_option( 'wp_akatus_seller_email', $seller_email );
        
        $env = esc_html( $_POST['wp_akatus_env'] ); 
        update_option( 'wp_akatus_env', $env );
        
        return true;
    }
    
    /**
     * Displays an updated message
     * 
     * @since 1.0
     * @return void
     */
    public function display_updated_message( $message )
    {
        printf( '<div id="message" class="updated"><p>%s</p></div>', $message );
    }
    
    /**
     * Displays an error message
     * 
     * @since 1.0
     * @param object $errors WP Errors
     */
    public function display_error_message( $errors )
    {
        if( is_wp_error( $errors ) )
            $errors = $errors->get_error_message();
        
        $output  =  '';
        $output .=  '<div id="message" class="error">';
        $output .=  '<ul>';
        
        foreach( (array)$errors as $error )
            $output .= sprintf('<li><p>%s</p></li>', $error );
            
        $output .=  '</ul>';
        $output .=  '</div>';

        echo $output;
    }
    
    /**
     * Adds capability
     * 
     * @since 1.0
     * @param string $capability Capability name
     * @param string $role Role name
     */
    private function _add_capability( $capability, $role = 'administrator' )
    {
        $role = get_role( $role );
        
        if( !$role->has_cap( $capability ) )
            $role->add_cap( $capability );
    }
}

// Instance of WP Akatus plugin
$wp_akatus = new WP_Akatus();