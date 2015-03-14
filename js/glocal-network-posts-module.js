//Create button to display shortcode [network_sites]
//Add parameter fields: numbersites, excludesites, sortby, defaultimage, instanceid, classname, hidemeta, hideimage 

(function() {
    tinymce.PluginManager.add('glocal_network_sites_button', function( editor, url ) {
        editor.addButton( 'glocal_network_sites_button', {
            text: '',
            icon: 'icon dashicons-networking',
            tooltip: 'Network Sites Shortcode',
            onclick: function() {
                editor.windowManager.open( {
                    title: 'Network Sites Options',
                    body: [
                    {
                        type: 'textbox',
                        name: 'numbersites',
                        label: 'Number of Sites'
                    },
                    {
                        type: 'textbox',
                        name: 'excludesites',
                        label: 'Sites to Exclude (Comma-separated)'
                    },
                    //@sortby - newest, updated, active, alpha (registered, last_updated, post_count, blogname) (default: alpha)
                    {
                        type: 'listbox',
                        name: 'sortby',
                        label: 'Sort By',
                        'values': [
                            {text: 'Alphabetic (default)', value: ''},
                            {text: 'Recently Added', value: 'registered'},
                            {text: 'Recently Updated', value: 'last_updated'},
                            {text: 'Most Active', value: 'post_count'}
                        ]
                    },
                    {
                        type: 'textbox',
                        name: 'defaultimage',
                        label: 'Default Site Image (URL)'
                    },
                    {
                        type: 'checkbox',
                        name: 'hideimage',
                        label: 'Hide Site Image',
                        checked: false,
                    },
                    {
                        type: 'checkbox',
                        name: 'hidemeta',
                        label: 'Hide Meta Info (Date updated and latest post)',
                        checked: false,
                    },
                    {
                        type: 'textbox',
                        name: 'instanceid',
                        label: 'ID',
                    },
                    {
                        type: 'textbox',
                        name: 'classname',
                        label: 'Class'
                    },
                    ],
                       
                    onsubmit: function( e ) {
                        var shortcode = '[network_sites';
                        if(e.data.numbersites) {
                            shortcode += ' numbersites="' + e.data.numbersites + '"';
                        }
                        if(e.data.excludesites) {
                            shortcode += ' excludesites="' + e.data.excludesites + '"';
                        }
                        if(e.data.sortby) {
                            shortcode += ' sortby="' + e.data.sortby + '"';
                        }
                        if(e.data.defaultimage) {
                            shortcode += ' defaultimage="' + e.data.defaultimage + '"';
                        }
                        if(e.data.hidemeta) {
                            shortcode += ' hidemeta="' + e.data.hidemeta + '"';
                        }
                        if(e.data.hideimage) {
                            shortcode += ' hideimage="' + e.data.hideimage + '"';
                        }
                        if(e.data.instanceid) {
                            shortcode += ' instanceid="' + e.data.instanceid + '"';
                        }
                        if(e.data.classname) {
                            shortcode += ' classname="' + e.data.classname + '"';
                        }
                        shortcode += ']';
                        
                        editor.insertContent( shortcode );
                    }
                });
            }
        });
    });
})();

