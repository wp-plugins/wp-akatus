<?php
// Avoid to load this file directly
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) and basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) )
    exit();

class WP_Akatus_Widget_Cart extends WP_Widget {
    
    /**
     * Construct method
     * 
     * @since 1.0
     */
    public function __construct()
    {    
        $widget_ops = array(
            'classname'     => 'wp_akatus_widget_cart',
            'description'   => __( 'Exibe o carrinho de compras da Akatus', 'wp-akatus' )
        );
        
        parent::__construct( 'wp-akatus-widget-cart', __( 'WP Akatus - Carrinho de compras', 'wp-akatus' ), $widget_ops );
    }
    
    /**
     * Build widget to show
     * 
     * @global object $wp_akatus
     * @since 1.0
     * @param array $_args 
     * @param array $instance
     */
    public function widget( $args, $instance )
    {
        global $wp_akatus;
        
        extract( $args );
        
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        
        echo $before_widget;
            
        if( !empty( $title ) )
            printf( '%s%s%s', $before_title, $title, $after_title );
        
        $wp_akatus->shopping_cart();
        
        echo $after_widget;
    }
    
    /**
     * Build Widget form
     *     
     * @since 1.0
     * @param array $instance 
     */
    public function form( $instance )
    {        
        $title = ( $instance['title'] ) ? $instance['title'] : 'Carrinho de compras';
        ?><p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Título', 'wp-akatus' ); ?>:</label>
            <input type="text" value="<?php echo esc_attr( $title ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" />
        </p><?php
    }
    
    /**
     * Update Widget
     * 
     * @since 1.0
     * @param array $new_instance
     * @param array $old_instance
     * @return array Instance 
     */
    public function update( $new_instance, $old_instance )
    {
        $instance['title'] = esc_html( $new_instance['title'] );
        
        return $instance;
    }
}