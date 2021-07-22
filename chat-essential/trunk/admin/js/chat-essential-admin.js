(function( $ ) {
	'use strict';

	$(document).ready(function(){
		var ChatEssential = {
			init: function () {
            	this.error = $('#errorMessage');
            	this.form = $('#loginForm');
            	this.form.submit(this.onStartSubmit.bind(this));
				this.preview = $('#previewChat');
				this.preview.click(this.previewChat.bind(this));
        	},
			onStartSubmit: function (e) {
				console.log('submit');
				this.showError('Something went wrong.');
				return false;
			},
			previewChat: function (e) {
				if (window.toggleChat) {
					window.toggleChat();
				}
			},
			showError: function (message) {
            	this.error.text(message).fadeIn();
        	},
        	hideError: function () {
            	this.error.hide();
        	}
		};

		ChatEssential.init();
	});

})( jQuery );
