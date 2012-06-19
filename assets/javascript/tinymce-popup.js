function init() {
    tinyMCEPopup.resizeToInnerSize();
}

function WPAkatusInsertProductShortcode() {

    var product_name_element    = document.getElementById( 'wp-akatus-product-name' );
    var product_name            = ( ( product_name_element.value != "" ) ? " name=\"" + product_name_element.value + "\"": "" ) ;
    var product_price_element   = document.getElementById( 'wp-akatus-product-price' );
    var product_price           = ( ( product_price_element.value != "" ) ? " price=\"" + product_price_element.value + "\"": "" ) ;
    var shortcode               = "[wp-akatus-product" + product_name + product_price + "]";
    
    if( product_name == "" ){
        alert('O nome do produto deve ser informado!')
        return;
    }else if ( product_price == "" ){
        alert('O preço do produto deve ser informado!')
        return;
    }
    
    window.tinyMCE.execInstanceCommand( 'content', 'mceInsertContent', false, shortcode );
    tinyMCEPopup.editor.execCommand( 'mceRepaint' );
    tinyMCEPopup.close();
    return;
}