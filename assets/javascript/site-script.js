jQuery( function(){
    AKATUS.init();
} );

var AKATUS = {
    
    init : function(){
       this.changeQuantityInfo();
    },
    
    changeQuantityInfo : function()
    {
        jQuery('.quantity').keydown( function(){
            var id = jQuery(this).attr('id').replace('change-product-quantity-', '');
            jQuery( '#quantity-info-' + id ).show();
        });
    }
}