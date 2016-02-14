(function() {  
    tinymce.create('tinymce.plugins.shortcodes', {  
        init : function(ed, url) {  
				
			ed.addButton('one-half', {  
                title : 'Add 2 Columns',  
                image : url+'/images/two-column.png',  
                onclick : function() {  
                     ed.selection.setContent('[one-half-first]' + ed.selection.getContent() + '[/one-half-first]');  
					 ed.selection.setContent('[one-half]' + ed.selection.getContent() + '[/one-half]');  
                }  
				
            }); 
			
			ed.addButton('one-third', {  
                title : 'Add 3 Columns',  
                image : url+'/images/three-column.png',  
                onclick : function() {  
                     ed.selection.setContent('[one-third-first]' + ed.selection.getContent() + '[/one-third-first]');  
					 ed.selection.setContent('[one-third]' + ed.selection.getContent() + '[/one-third]');  
					 ed.selection.setContent('[one-third]' + ed.selection.getContent() + '[/one-third]');  
                }  
				
            });   
			
			ed.addButton('one-fourth', {  
                title : 'Add 4 Columns',  
                image : url+'/images/four-column.png',  
                onclick : function() {  
                     ed.selection.setContent('[one-fourth-first]' + ed.selection.getContent() + '[/one-fourth-first]');  
					 ed.selection.setContent('[one-fourth]' + ed.selection.getContent() + '[/one-fourth]');  
					 ed.selection.setContent('[one-fourth]' + ed.selection.getContent() + '[/one-fourth]');  
					 ed.selection.setContent('[one-fourth]' + ed.selection.getContent() + '[/one-fourth]'); 
                }  
				
            }); 
			
			ed.addButton('one-fifth', {  
                title : 'Add 5 Columns',  
                image : url+'/images/five-column.png',  
                onclick : function() {  
                     ed.selection.setContent('[one-fifth-first]' + ed.selection.getContent() + '[/one-fifth-first]');  
					 ed.selection.setContent('[one-fifth]' + ed.selection.getContent() + '[/one-fifth]');  
					 ed.selection.setContent('[one-fifth]' + ed.selection.getContent() + '[/one-fifth]');  
					 ed.selection.setContent('[one-fifth]' + ed.selection.getContent() + '[/one-fifth]'); 
					 ed.selection.setContent('[one-fifth]' + ed.selection.getContent() + '[/one-fifth]'); 
                }  
				
            }); 
			
			ed.addButton('one-sixth', {  
                title : 'Add 6 Columns',  
                image : url+'/images/six-column.png',  
                onclick : function() {  
                     ed.selection.setContent('[one-sixth-first]' + ed.selection.getContent() + '[/one-sixth-first]');  
					 ed.selection.setContent('[one-sixth]' + ed.selection.getContent() + '[/one-sixth]');  
					 ed.selection.setContent('[one-sixth]' + ed.selection.getContent() + '[/one-sixth]');  
					 ed.selection.setContent('[one-sixth]' + ed.selection.getContent() + '[/one-sixth]');  
					 ed.selection.setContent('[one-sixth]' + ed.selection.getContent() + '[/one-sixth]'); 
					 ed.selection.setContent('[one-sixth]' + ed.selection.getContent() + '[/one-sixth]'); 
                }  
				
            }); 
			
			ed.addButton('clear', {  
                title : 'Add clear fix.',  
                image : url+'/images/clear.png',  
                onclick : function() {  
                     ed.selection.setContent('[clear]');  

                }  
				
            }); 
			
			ed.addButton('clear-line', {  
                title : 'Add clear fix line.',  
                image : url+'/images/clear-line.png',  
                onclick : function() {  
                     ed.selection.setContent('[clear-line]');  
 
                }  
				
            }); 
        },  
        createControl : function(n, cm) {  
            return null;  
        },  
    });  
	tinymce.PluginManager.add('column', tinymce.plugins.shortcodes);
	tinymce.PluginManager.add('one-half', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('one-third', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('one-fourth', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('one-fifth', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('one-sixth', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('clear', tinymce.plugins.shortcodes); 
	tinymce.PluginManager.add('clear-line', tinymce.plugins.shortcodes); 
})();