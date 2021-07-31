(function( $ ) {
	'use strict';

	const coreModels = [
		{
			kitId: 0,
			modelType: 'gpt3',
			name: 'GPT-3',
		}
	];

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
						this.showStatus('Upgrade to premium to select more than 1 topic', -1);
					}
					this.aiTopics[e.target.id] = e.target.value;
				} else if (this.aiTopics[e.target.id]) {
					delete this.aiTopics[e.target.id];
				}
			},
			init: function() {
            	this.status = $('.status-msg');
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
						case 'chat-essential-ai':
							this.form = $('#aiForm');
            				this.form.submit(this.onAISubmit.bind(this));
							this.aiModels = $('#aiModels');
							this.aiPreview = $('#previewChat');
							this.aiPreview.click(this.previewChat.bind(this));
							this.aiTopics = {};
							this.loadModels();
							return;
					}
				}
				this.showStatus('');
				this.pageContent.show();
			},
			renderModels: function(model, kits) {
				let idx = 0;
				let models = '';
				const modelKits = {};
				if (model && model.kits) {
					for (const ii in model.kits) {
						const k = model.kits[ii];
						modelKits[k.kitId] = k;
					}
				}
				for (const val of kits) {
					const id = val.name.replace(' ', '-');
					let col = '<td><input id="topic-' + id + '" class="ai-checkbox" type="checkbox" name="topic-' + id + '" value="' + val.name + '"';
					let classes = 'ai-input-label';
					if (val.kitId < 1) {
						col += ' disabled checked';
					} else if (modelKits[val.kitId]) {
						col += ' checked';
						this.aiTopics['topic-' + id] = val.name;
					}
					col += ' /><div class="' + classes + '">' + val.name + '</div></td>';

					if (idx % 4) {
						models += col;
					} else {
						if (idx > 0) {
							models += '</tr>';
						}
						models += '<tr>' + col;
					}
					idx++;
					if (idx == models.length) {
						models += '</tr>';
					}
				}
				return models;
			},
			loadModels: function() {
				$.when(
					api(this.n, 'chat_essential_get', 'nlp/model/{apiKey}'),
					api(this.n, 'chat_essential_get', 'nlp/kit'),
				)
				.then((function(v1, v2) {
					let model, kits;
					if (v1 && v1.nlp && v1.nlp.model) {
						model = v1.nlp.model;
					}
					if (v2 && v2.nlp && v2.nlp.kits) {
						kits = v2.nlp.kits;
					}
					let allKits = coreModels;
					if (kits && kits.length) {
						allKits = coreModels.concat(kits);
					}
					const models = this.renderModels(model, allKits);
					this.aiModels.html(models);
					this.hideStatus();
					this.pageContent.show();
					this.aiTopic = $('.ai-checkbox');
					this.aiTopic.click(this.aiTopicSelect.bind(this));
				}).bind(this))
				.catch((function(e1, e2) {
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
					} else if (e2.responseText) {
						err = parseError(e2);
					}
					if (err) {
						this.showStatus(err, -1);
					}
				}).bind(this));
			},
			onAISubmit: function (e) {
				this.showStatus('Uploading training data...This might take a while...');
				const data = this.siteOptionValues();
				if (!data) {
					return false;
				}
				api(this.n, 'chat_essential_train', null, null, data)
				.then((function(v1) {
					console.log(v1);
					this.showStatus('Upload complete! Training started...This might also take a while...', 1);
				}).bind(this))
				.catch((function(e1) {
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
						this.showStatus(err, -1);
					} else {
						this.showStatus(e1, -1);
					}
				}).bind(this));
				return false;
			},
			onStartSubmit: function (e) {
				console.log('submit');
				this.showStatus('Something went wrong.', -1);
				return false;
			},
			previewChat: function (e) {
				if (window.toggleChat) {
					window.toggleChat();
				}
			},
			siteOptionValues: function() {
				const data = {
					siteType: $('#siteTypeSelect').val(),
				};
				let dt1, dt2;
				switch (data.siteType) {
					case 'all':
						dt1 = $('#exPages').val();
						if(dt1 && dt1.length) {
							data.ex_pages = dt1;
						}
						dt2 = $('#exPosts').val();
						if(dt2 && dt2.length) {
							data.ex_posts = dt2;
						}
						return data;
					case 'pages':
						dt1 = $('#inPages').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one page', -1);
							return;
						}
						data.in_pages = dt1;
						return data;
					case 'posts':
						dt1 = $('#inPosts').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one post', -1);
							return;
						}
						data.in_posts = dt1;
						return data;
					case 'categories':
						dt1 = $('#inCategories').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one category', -1);
							return;
						}
						data.in_categories = dt1;
						return data;
					case 'tags':
						dt1 = $('#inTags').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one tag', -1);
							return;
						}
						data.in_tags = dt1;
						return data;
					case 'postTypes':
						dt1 = $('#inPostTypes').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one post type', -1);
							return;
						}
						data.in_postTypes = dt1;
						return data;
					default:
						return;
				}
			},
			showStatus: function (message, msgType) {
				for (var idx in this.status) {
					const st = $('#' + this.status[idx].id);
					st.removeClass('error-msg');
					st.removeClass('success-msg');
					if (msgType !== undefined) {
						if (msgType < 0) {
							st.addClass('error-msg');
						} else {
							st.addClass('success-msg');
						}
					}
            		st.html(message).fadeIn();
				}
        	},
        	hideStatus: function () {
				for (var idx in this.status) {
					const st = $('#' + this.status[idx].id);
					st.removeClass('error-msg');
            		st.html('');
				}
        	}
		};

		ChatEssential.init();
	});

	const parseError = function(err) {
		if (err.responseText) {
			try {
				const jdata = JSON.parse(err.responseText);
				if (jdata.message) {
					return jdata.message;
				} else {
					return jdata;
				}
			} catch(e) {
				return err.responseText
			}
		}
		return 'Internal server error';
	};

	const api = function(nonce, action, path, query, body, response) {
		return $.post(ajaxurl, {
			'action': action,
			'path': path,
			'query': query,
			'body': body,
			'_wpnonce': nonce,
		})
			.then(function(data) {
				try {
					const jdata = JSON.parse(data);
					if (response) response(null, jdata);
					return jdata;
				} catch(e) {
					if (response) response(null, data);
					return data;
				}
			})
			.fail(function(err) {
				if (err.responseText) {
					try {
						const jdata = JSON.parse(err.responseText);
						if (jdata.message) {
							if (response) response(jdata.message);
							return jdata.message;
						} else {
							if (response) response(jdata);
							return jdata;
						}
					} catch(e) {
						if (response) response(err.responseText);
						return err.responseText
					}
				} else {
					if (response) response('Internal server error');
					return 'Internal server error';
				}
			});
	};

})( jQuery );
