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
console.log($.cookie('js-project-dir'));
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

        // 
		$(".ss-gridfield-orderable.ss-gridfield-blockenhancements tbody").entwine({
            
            // Add 'virtual' items to indicate areas
            OriginalSortCallback: null,
			//onmatch: function() {
			onadd: function() {
				var self = this; // this & self are already a jQuery obj
                
                this._super(); // execute GridFieldOrderableRows::onadd
                
                // if not on SiteTree, return (e.g. BlockManager)
                if(! self.getGridField().attr('data-block-areas')){ return; }
                    
                //var blockAreas = JSON.parse('$BlockAreas'); // output by php into js var via javascriptTemplate()
                var blockAreas = self.getGridField().data('block-areas'); // valid json, already parsed by jQ
                if(Object.keys(blockAreas).length){
                    blockAreas.none = self.getGridField().data('block-area-none-title');
                } else {
                    return; // don't add headers if we have no areas defined
                }
                
                // get initial ID order to check if we need to update after sorting
                var initialIdOrder = self.getGridField().getItems()
                        .map(function() { return $(this).data("id"); }).get();
                
                // insert blockAreas boundaries
                var blockAreaBoundElements = [];
                $.each(blockAreas, function(areaKey, areaTitle) {
                    //console.log(index); console.log(value);
                    //var colSpan = $('tr',self).first().find('td').length;
                    var colSpan = self.siblings('thead').find('tr.ss-gridfield-title-header th').length;
                    // ▾ / ▼ / ↓
                    var boundEl = $('<tr class="block-area-bound"><td>↓</td>'
                        +'<td colspan="'+(colSpan-1)+'">Area: <strong>'
                        +(areaTitle || '(none)')+'</strong></td></tr>');
                    boundEl.data('blockareaKey', areaKey);
                    blockAreaBoundElements[areaKey] = boundEl;
                    $(self).append(boundEl); //before(bound);
                });
                // and put blocks in order below boundaries
                jQuery.fn.reverseOrder = [].reverse; // small reverse plugin
                self.getGridField().getItems().reverseOrder().each(function(){
                    var myArea = $('.col-BlockArea select',this).val() || 'none';
                    $(this).insertAfter( blockAreaBoundElements[myArea] );
                });
                // hide the blockarea column
                $('.col-action_SetOrderBlockArea, .col-BlockArea').hide();
                
                // get ID order again to check if we need to update now we've sorted primarily by area
                var sortedIdOrder = self.getGridField().getItems()
                        .map(function() { return $(this).data("id"); }).get();
                // ifchanged, we should call this.sortable.update() (from orderablegridfield)
                if(JSON.stringify(initialIdOrder)!=JSON.stringify(sortedIdOrder)){ // test same array & order
                    this.sortable("option", "update")();
                }
            
                // remove the auto sortable callback (called by hand after setting the correct area first)
//                this.setOriginalSortCallback(this.sortable("option", "update"));
                this.sortable({ update: null });
                
			},
            
            onsortstop: function( event, ui ) {
                
                // set correct area on row/item areaselect
                var blockarea = ui.item.prevAll('.block-area-bound').first().data('blockareaKey');
                $('.col-BlockArea select',ui.item).val(blockarea);

                // save area on object/rel
                
                // duplicated from GridFieldOrderableRows.onadd.update: 
                var grid = this.getGridField();
                var data = grid.getItems().map(function() {
                    return { 
                        name: "order[]", 
                        value: $(this).data("id")
                    };
                }).get();
                
                // insert area assignment data as well
                data.push({
                    name: 'blockarea_block_id',
                    value: ui.item.data("id")
                });
                data.push({
                    name: 'blockarea_area',
                    value: blockarea
                });
                
                // area-assignment forwards the request to gridfieldextensions::reorder server side
                grid.reload({
                    //url: grid.data("url-reorder"),
                    url: grid.data("url-area-assignment"),
                    data: data
                });
                
                // don't call original from JS to prevent double reload, instead request gets forwarded via PHP
                //this.getOriginalSortCallback()();
            },
            

		});
        
	});
})(jQuery);
