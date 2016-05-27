(function($) {
    
	$.entwine("ss", function($) {
        
        // Haven't been able to 'arrive' at the select before the Leftandmain script does
        // and applies chozen, despite having way more specific selectors...
    //    $('#Form_EditForm_Blocks .ss-gridfield-add-new-multi-class select').addClass('no-chzn');
        // Just remove it instead...
        $('#Form_EditForm_Blocks #GridFieldAddNewMultiClass_ClassName_chzn, '
                + 'select.block-type + .select2 + .chzn-container').entwine({
			onmatch: function() {
                //console.log('match');
                this.remove();
            }
        });
        
        // initialize select2 on blocktype dropdowns
        //+'.cms .field.dropdown select#Form_ItemEditForm_ClassName.block-type'
        $('select.select2blocktype, .block-type select, '
                +'.cms .ss-gridfield-blockenhancements .field.dropdown select#GridFieldAddNewMultiClass_ClassName'
                ).entwine({
                    
            //onmatch: function(){ // attempt to override chzn, doesnt work consistently hence above remove() hack
            //    //this.addClass('no-chzn');
            //}, // override LeftAndMain.js line ~1266 (Chosen)
    
            onadd: function(){
// console.log($.cookie('js-project-dir'));
                var projectdir = this.data('project-dir');
                if(!projectdir) projectdir = $.cookie('js-project-dir'); // try & get from cookie
                if(!projectdir) projectdir = 'mysite'; // guess default...

                var optionHtmlTemplate = function(data, container) {
                    if (!data.id) { return data.text; }
                    var $state = $( // $ThemeDir
                        '<span><img src="' + projectdir + '/block_images/' + data.element.value
                        + '.png" /> ' + data.text + '</span>'
                    );
                    return $state;
                };
                // apply select2 (set inline width if in gridfield to get consistens results)
                if(this.parents('.ss-gridfield').length){
                    this.css('width','100%');
                }
                this.select2({
                      templateSelection: optionHtmlTemplate,
                      templateResult: optionHtmlTemplate,
                      minimumResultsForSearch: 'Infinity'
                    });
                //this.select2();
            }
            
        });
        
        $('.ss-gridfield select.select2blocktype').entwine({
            
            // update blocks on change (in Gridfield)
            onchange: function(){
                
                // duplicated from GridFieldOrderableRows.onadd.update: 
                var grid = this.getGridField();
                var data = [];
                data.push({
                    name: 'block_id',
                    value: this.closest('tr').data("id")
                });
                data.push({
                    name: 'block_type',
                    value: this.val()
                });
                
                // area-assignment forwards the request to gridfieldextensions::reorder server side
                grid.reload({
                    //url: grid.data("url-reorder"),
                    url: grid.data("url-blocktype-assignment"),
                    data: data
                });
            }
            
        });
        
	});
})(jQuery);
