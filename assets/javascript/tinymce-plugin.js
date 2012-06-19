(function() {
    
    tinymce.PluginManager.requireLangPack('wp_akatus');

    tinymce.create('tinymce.plugins.wp_akatus', {
		
        init : function(ed, url) {
            ed.addCommand('wp_akatus_product', function() {
                ed.windowManager.open({
                    file    : url + '../../../tinymce/akatus-window-product.php',
                    width   : 370,
                    height  : 120,
                    inline  : 1
                }, {
                    plugin_url : url
                });
            });

            ed.addButton('wp_akatus_product', {
                title   : 'WP Akatus',
                cmd     : 'wp_akatus_product',
                image   : url + '../../images/wp-akatus-20.png'
            });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add( function( ed, cm, n ) {
                cm.setActive('wp_akatus_product', n.nodeName == 'IMG');
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname  : 'wp_akatus',
                author 	  : 'Apiki WordPress',
                authorurl : 'http://apiki.com',
                infourl   : 'http://apiki.com',
                version   : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'wp_akatus', tinymce.plugins.wp_akatus );
})();