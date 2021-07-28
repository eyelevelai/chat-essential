(function( $ ) {
	'use strict';

	const coreModels = {
		'GPT-3': 1,
	}
	const demoModels = {
		'Home Renovations': 1,
		'Residential Electrical': 1,
		'Residential HVAC': 1,
		'Residential Plumbing': 1,
		'Tax Accounting': 1,
		'Personal Injury Law': 1,
		'Crossfit': 1,
		'Pilates': 1,
		'Yoga': 1,
		'Mortgages': 1,
		'Credit Cards': 1,
	}

/*

$models = '';
		$n_models = count($this->models);
		$cnt = 0;
		foreach ($this->models as $idx => $val) {
			$id = str_replace(' ', '-', $idx);
			$col = '<td><input id="topic-' . $id . '" class="ai-checkbox" type="checkbox" name="topic-' . $id . '" value="' . $idx . '"';
			if (!$val) {
				$col .= ' disabled checked';
			}
			$col .= ' /><div class="ai-input-label">' . $idx . '</div></td>';

			if ($cnt % 4) {
				$models .= $col;
			} else {
				if ($cnt > 0) {
					$models .= '</tr>';
				}
				$models .= '<tr>' . $col;
			}
			$cnt++;
			if ($cnt == $n_models) {
				$models .= '</tr>';
			}
		}

*/

	$(document).ready(function(){
		var ChatEssential = {
			aiTopicSelect: function(e) {
				this.showStatus('');
				if (e.target.checked) {
					var len = Object.keys(this.aiTopics).length;
					if (len > 0) {
						for (var key in this.aiTopics) {
							$('#' + key).prop('checked', false);
							delete this.aiTopics[key];
						}
						this.showStatus('Upgrade to premium to select more than 1 topic', true);
					}
					this.aiTopics[e.target.id] = e.target.value;
				} else if (this.aiTopics[e.target.id]) {
					delete this.aiTopics[e.target.id];
				}
			},
			init: function () {
            	this.status = $('#statusMessage');
            	this.form = $('#loginForm');
            	this.form.submit(this.onStartSubmit.bind(this));
				this.aiModels = $('#aiModels');
				this.aiPreview = $('#previewChat');
				this.aiPreview.click(this.previewChat.bind(this));
				this.aiTopic = $('.ai-checkbox');
				this.aiTopic.click(this.aiTopicSelect.bind(this));
				this.aiTopics = {};
				this.pageContent = $('#pageContent');
				this.n = $('#_wpnonce').val();
				this.loadPage();
        	},
			loadPage: function() {
				this.pageContent.hide();
				this.showStatus('Loading...');
				if (pageParams && pageParams.slug) {
					switch(pageParams.slug) {
						case 'chat-essential':
							this.loadModels();
							return;
					}
				}
				this.showStatus('');
				this.pageContent.show();
			},
			loadModels: function() {
				api(this.n, 'GET', 'nlp/kits', null, null, (function(err, res) {
					if (err) {
						this.showStatus(err, true);
					} else {
						console.log(typeof res, res);
						this.hideStatus();
						this.aiModels.empty();
						this.pageContent.show();
					}
				}).bind(this));
			},
			onStartSubmit: function (e) {
				console.log('submit');
				this.showStatus('Something went wrong.', true);
				return false;
			},
			previewChat: function (e) {
				if (window.toggleChat) {
					window.toggleChat();
				}
			},
			showStatus: function (message, isErr) {
				if (isErr) {
					this.status.addClass('error-msg');
				}
            	this.status.html(message).fadeIn();
        	},
        	hideStatus: function () {
				this.status.removeClass('error-msg');
            	this.status.hide();
        	}
		};

		ChatEssential.init();
	});

	const api = function(nonce, ty, path, query, body, response) {
		const type = ty.toLowerCase();
		const req = {
			'path': path,
			'query': query,
			'body': body,
			'_wpnonce': nonce,
		};
		if (type == 'get') {
			req.action = 'chat_essential_get';
			return $.get(ajaxurl, req, function(data) {
				try {
					const jdata = JSON.parse(data);
					response(null, jdata);
				} catch(e) {
					response(null, data);
				}
			})
			.fail(function(err) {
				if (err.responseText) {
					try {
						const jdata = JSON.parse(err.responseText);
						if (jdata.message) {
							response(jdata.message);
						} else {
							response(jdata);
						}
					} catch(e) {
						response(err.responseText);
					}
				} else {
					response('Internal server error');
				}
			});
		} else if (type == 'post') {
			req.action = 'chat_essential_post';
			return $.post(ajaxurl, req, res);
		}
	}

})( jQuery );
