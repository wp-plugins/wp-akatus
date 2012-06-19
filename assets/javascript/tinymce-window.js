jQuery( function(){
    WP_AKATUS_PRODUCT_WINDOW.init();
} );

var WP_AKATUS_PRODUCT_WINDOW = {
    
    init : function(){
        jQuery("#wp-akatus-product-price").setMask( { mask : '99,999.999.999.999', type : 'reverse', defaultValue : ''} );
    }
}
