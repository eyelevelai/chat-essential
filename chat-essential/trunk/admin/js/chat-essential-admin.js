(function( $ ) {
	'use strict';

	const coreEngines = [
		{
			name: 'GPT-3',
			engine: 'gpt3',
		}
	];

	let apiInProgress = false;

	$(document).ready(function(){
		var ChatEssential = {
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
							this.aiEngines = [];
							this.loadModels();
							return;
					}
				}
				this.showStatus('');
				this.pageContent.show();
			},
			aiModelSelect: function(e) {
				this.showStatus('');
				if (e.target.checked) {
					if (this.aiEngines.length > 0) {
						for (var idx in this.aiEngines) {
							const key = this.aiEngines[idx];
							$('#' + key).prop('checked', false);
							this.aiEngines.splice(idx);
						}
						this.showStatus('Upgrade to premium to select more than 1 model', 'error');
					}
					this.aiEngines.push(e.target.id);
				} else if (this.aiEngines.indexOf(e.target.id) > -1) {
					this.aiEngines.splice(this.aiEngines.indexOf(e.target.id));
				}
			},
			aiTopicSelect: function(e) {
				this.showStatus('');
				if (e.target.checked) {
					var len = Object.keys(this.aiTopics).length;
					if (len > 0) {
						for (var key in this.aiTopics) {
							$('#' + key).prop('checked', false);
							delete this.aiTopics[key];
						}
						this.showStatus('Upgrade to premium to select more than 1 topic', 'error');
					}
					this.aiTopics[e.target.id] = e.target.value;
				} else if (this.aiTopics[e.target.id]) {
					delete this.aiTopics[e.target.id];
				}
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
				let modelSelected = false;
				for (const val of kits) {
					const id = val.name.replace(' ', '-');
					let col = '<td><input id="topic-' + id + '" type="checkbox" name="topic-' + id + '"';
					let classes = 'ai-input-label';
					if (val.engine) {
						col += ' class="ai-model-checkbox" value="' + val.engine + '"';
						if (!modelSelected) {
							modelSelected = true;
							this.aiEngines.push('topic-' + id);
						}
						col += ' checked disabled';
					} else {
						col += ' value="' + val.kitId + '" class="ai-checkbox"';
						if (modelKits[val.kitId]) {
							col += ' checked';
							this.aiTopics['topic-' + id] = val.kitId;
						}
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
				if (apiInProgress) {
					return;
				}
				apiInProgress = true;

				api(this.n, 'chat_essential_get', 'nlp/kit')
				.then((function(v) {
					apiInProgress = false;
					let kits;
					if (v && v.nlp && v.nlp.kits) {
						kits = v.nlp.kits;
					}
					let allKits = coreEngines;
					if (kits && kits.length) {
						allKits = coreEngines.concat(kits);
					}
					if (model && model.training && model.training.taskId && model.training.status !== 'complete') {
						this.showTrainingStatus(model.training.status);
						this.pollTrainingStatus();
					} else {
						this.printTrainingDate();
					}
					const models = this.renderModels(model, allKits);
					this.aiModels.html(models);
					this.pageContent.show();
					this.aiTopic = $('.ai-checkbox');
					this.aiTopic.click(this.aiTopicSelect.bind(this));
					this.aiModel = $('.ai-model-checkbox');
					this.aiModel.click(this.aiModelSelect.bind(this));
				}).bind(this))
				.catch((function(e1, e2) {
					apiInProgress = false;
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
					} else if (e2.responseText) {
						err = parseError(e2);
					}
					if (err) {
						this.showStatus(err, 'error');
					}
				}).bind(this));
			},
			onAISubmit: function (e) {
				if (apiInProgress) {
					return false;
				}
				if (model && model.training && model.training.status && model.training.status !== 'complete') {
					this.showStatus('You have already submitted a training request that is not yet complete', 'error');
					return false;
				}

				this.showStatus('Uploading training data...This might take a while...');
				const data = this.siteOptionValues();
				if (!data) {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				}

				apiInProgress = true;
				api(this.n, 'chat_essential_train', null, null, data)
				.then((function(v1) {
					apiInProgress = false;
					console.log(v1);
					if (v1 && v1.nlp && v1.nlp.task) {
						model.training = v1.nlp.task;
					}
					this.showStatus('Upload complete! Training started...This might also take a while...', 'success');
					this.pollTrainingStatus();
				}).bind(this))
				.catch((function(e1) {
					apiInProgress = false;
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
						this.showStatus(err, 'error');
					} else {
						this.showStatus(e1, 'error');
					}
				}).bind(this));
				return false;
			},
			onStartSubmit: function (e) {
				console.log('submit');
				this.showStatus('Something went wrong', 'error');
				return false;
			},
			previewChat: function (e) {
				if (window.toggleChat) {
					window.toggleChat();
				}
			},
			pollTrainingStatus: function() {
				setTimeout((function() {
					if (model && model.training && model.training.taskId) {
						api(this.n, 'chat_essential_get', 'nlp/task/' + model.training.taskId)
						.then((function(data) {
							if (data && data.nlp && data.nlp.task && data.nlp.task.status) {
								model.training.status = data.nlp.task.status;
								this.showTrainingStatus(data.nlp.task.status);
							}
						}).bind(this))
						.catch((function(e1) {
							var err = '';
							if (e1.responseText) {
								err = parseError(e1);
							}
							if (err) {
								console.error(err);
							}
						}).bind(this));
					}
				}).bind(this), 5000);
			},
			printTrainingDate: function() {
				if (model && model.training && model.training.started) {
					const dt = new Date(model.training.started);
      				const dtf = new Intl.DateTimeFormat('en', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' });
      				const [{ value: mo }, , { value: da }, , { value: yr }, , { value: hr }, , { value: min }, , { value: per }] = dtf.formatToParts(dt);
					this.showStatus('<i>Your AI was last trained on ' + mo + ' ' + da + ', ' + yr + ' at ' + hr + ':' + min + per + '</i>');
				} else {
					this.hideStatus();
				}
			},
			showTrainingStatus: function(status) {
				if (status === 'queued') {
					this.showStatus('Your training request is queued but has not started yet', 'warn');
					this.pollTrainingStatus();
				} else if (status === 'generating') {
					this.showStatus('Your custom AI model is currently being generated', 'warn');
					this.pollTrainingStatus();
				} else if (status === 'complete') {
					this.showStatus('Training is now complete!', 'success');
					setTimeout((function(){
						this.printTrainingDate();
					}).bind(this), 5000);
				} else {
					this.showStatus('Your training request is currently being processed', 'warn');
					this.pollTrainingStatus();
				}
			},
			siteOptionValues: function() {
				const data = {
					siteType: $('#siteTypeSelect').val(),
				};

				var topics = Object.keys(this.aiTopics).length;
				if (topics > 0) {
					data['kits'] = [];
					for (var k in this.aiTopics) {
						data['kits'].push(parseInt(this.aiTopics[k]));
					}
				}
				if (this.aiEngines.length > 0) {
					data['engines'] = [];
					for (var k in this.aiEngines) {
						const md = $('#' + this.aiEngines[k]);
						data['engines'].push(md.val());
					}
				}

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
							this.showStatus('Choose at least one page', 'error');
							return;
						}
						data.in_pages = dt1;
						return data;
					case 'posts':
						dt1 = $('#inPosts').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one post', 'error');
							return;
						}
						data.in_posts = dt1;
						return data;
					case 'categories':
						dt1 = $('#inCategories').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one category', 'error');
							return;
						}
						data.in_categories = dt1;
						return data;
					case 'tags':
						dt1 = $('#inTags').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one tag', 'error');
							return;
						}
						data.in_tags = dt1;
						return data;
					case 'postTypes':
						dt1 = $('#inPostTypes').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one post type', 'error');
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
					st.removeClass('warn-msg');
					st.removeClass('success-msg');
					if (msgType !== undefined) {
						if (msgType === 'error') {
							st.addClass('error-msg');
						} else if (msgType === 'success') {
							st.addClass('success-msg');
						} else if (msgType === 'warn') {
							st.addClass('warn-msg');
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
