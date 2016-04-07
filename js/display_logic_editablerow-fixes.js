// This patch applies some fixes to displaylogic to make it work with EditableRows,
// probably also fixes deep updates with dot notation in fieldnames.
// These edits could probably included in the general display_logic, as they mainly make the selectors
// less rigid, because we have no way of knowing the exact IDs & names on beforehand for every situation
(function($) {

    // make selectors more specific to override default display logic behaviour
	$('.ss-gridfield-blockenhancements div.display-logic, .ss-gridfield-blockenhancements div.display-logic-master')
			.entwine({

		findHolder: function(name) {
            // Again, don't search exact ID as we have way of knowing that, check for ends with instead
			return this.closest('form,fieldset').find('[id$='+name+'_Holder]');
		},

		getFormField: function() {
			// displaylogicwrappers conain the eval & masters on themselves instead of the child fields
            if(this.hasClass('displaylogicwrapper')){
                return this;
            }
			// leave out the actual name, just return the first element with a name attribute CONTAINING the name,
            // as we have no way of knowing the exact name because of deep editing
			return this.find('[name*='+name+']');
			//return this.find('[name]');
			//return this.find('[name='+name+']');
		},

		onmatch: function () {
			
			var allReadonly = true;
			var masters = [];
			var field = this.getFormField();
			if(field.data('display-logic-eval') && field.data('display-logic-masters')) {
				this.data('display-logic-eval', field.data('display-logic-eval'))
					.data('display-logic-masters', field.data('display-logic-masters'));
			}

			masters = this.getMasters();

			for(m in masters) {	
				var holderName = this.nameToHolder(masters[m]);

				// again, search for field ending with MasterName_Holder, and limit to current fieldset as
                // multiple identical fieldsets may be included on the same form only with different IDs
				//var master = this.closest('form').find(this.escapeSelector('#'+holderName));
				master = this.closest('form,fieldset').find('[id$='+masters[m]+'_Holder]');
				// Continue with regular code:

				if(!master.is('.readonly')) allReadonly = false;

				master.addClass("display-logic-master");
				if(master.find('input[type=radio]').length) {
					master.addClass('optionset');
				}
				if(master.find("input[type=checkbox]").length > 1) {
					master.addClass('checkboxset');
				}
			}

			// If all the masters are readonly fields, the field has no way of displaying.
			if(masters.length && allReadonly) {				
				this.show();
			}

            // Bubble up to super methods if both master & listener (needed for ao Switch field)
            if(this.hasClass('display-logic-hidden') && this.hasClass('display-logic-master')) {
                this._super();
            }

		}

	});

    // make selectors more specific to override default display logic behaviour
	$('.ss-gridfield-blockenhancements div.display-logic-master').entwine({
		Listeners: null,

		getListeners: function() {
			if(l = this._super()) {
				return l;
			}
			var self = this;
			var listeners = [];
            // EDIT
			this.closest("form,fieldset").find('.display-logic').each(function() {
				masters = $(this).getMasters();
				for(m in  masters) {
					//if(self.nameToHolder(masters[m]) == self.attr('id')) {
					var endswith = new RegExp(masters[m]+'_Holder$');
					if (self.attr('id').match(endswith)!=null){
            // END:EDIT
						listeners.push($(this)[0]);
						break;
					}
				}
			});
			this.setListeners(listeners);
			return this.getListeners();
		}
	});

})(jQuery);
