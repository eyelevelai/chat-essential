(function( $ ) {
	'use strict';

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
				if (pageParams && pageParams.slug) {
					switch(pageParams.slug) {
						case 'chat-essential':
						case 'chat-essential-ai':
							this.showStatus('Loading...');
							this.form = $('#aiForm');
            				this.form.submit(this.onAISubmit.bind(this));
							this.aiModels = $('#aiModels');
							this.aiPreview = $('#previewChat');
							this.aiPreview.click(this.previewChat.bind(this));
							this.aiTopics = {};
							this.aiEngines = [];
							this.loadModels();
							return;
						case 'chat-essential-settings':
							this.logoutBtn = $('#logoutBtn');
							this.logoutBtn.click(this.logout.bind(this));
							return;
						case 'signup':
						case 'login':
							this.form = $('#loginForm');
							this.form.submit(this.onAuthSubmit.bind(this));
							this.switchBtn = $('#footerBtn');
							this.switchBtn.click(this.switchAuth.bind(this));
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
				if (typeof(model) === 'undefined' || !pageParams.coreEngines) {
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
					let allKits = pageParams.coreEngines;
					if (kits && kits.length) {
						allKits = pageParams.coreEngines.concat(kits);
					}
					if(model.training && model.training.taskId && model.training.status !== 'complete') {
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
					} else {
						console.error(e1, e2);
					}
				}).bind(this));
			},
			logout: function() {
				if (apiInProgress) {
					return;
				}
				apiInProgress = true;
				api(this.n, 'chat_essential_logout')
				.then((function(v) {
					location.reload();
				}).bind(this))
				.catch((function(e1) {
					apiInProgress = false;
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
						this.showStatus(err, 'error');
					} else {
						console.error(e1);
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

				api(this.n, 'chat_essential_train', null, data)
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
						console.error(e1);
					}
				}).bind(this));
				return false;
			},
			onAuthSubmit: function (e) {
				if (apiInProgress) {
					return false;
				}
				const data = this.authValues();
				if (!data) {
					return false;
				}
				apiInProgress = true;
				if (data.type === 'login') {
					this.showStatus('Submitting...');
				} else {
					this.showStatus('Submitting...');
				}

				api(this.n, 'chat_essential_auth', null, data)
				.then((function(v1) {
					location.reload();
				}).bind(this))
				.catch((function(e1) {
					apiInProgress = false;
					var err = '';
					if (e1.status && e1.status === 405) {
						this.showStatus('An account with this email already exists', 'error');
					} else if (e1.responseText) {
						err = parseError(e1);
						this.showStatus(err, 'error');
					} else {
						console.error(e1);
					}
				}).bind(this));
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
								this.showStatus(err, 'error');
							} else {
								console.error(e1);
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
			authValues: function() {
				let err = '';
				const data = {
					type: pageParams.slug,
				};
				this.hideStatus();

				const email = $('#email');
				email.removeClass('error-value');
				const pass1 = $('#password1');
				pass1.removeClass('error-value');
				const pass2 = $('#password2');
				if (pass2) {
					pass2.removeClass('error-value');
				}

				err = validateEmail(email.val());
				if (err) {
					this.showStatus(err, 'error');
					email.addClass('error-value');
					return;
				}
				data['email'] = email.val();

				err = validatePassword(pass1.val());
				if (err) {
					this.showStatus(err, 'error');
					pass1.addClass('error-value');
					return;
				}
				data['password'] = pass1.val();
				switch (pageParams.slug) {
					case 'login':
						return data;
					case 'signup':
						if (pass1.val() !== pass2.val()) {
							this.showStatus('Passwords are not the same', 'error');
							pass2.addClass('error-value');
							return;
						}
						err = validatePassword(pass2.val());
						if (err) {
							this.showStatus(err, 'error');
							pass2.addClass('error-value');
							return;
						}
						return data;
				}

				return;
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
			switchAuth: function() {
				if (apiInProgress) {
					return;
				}
				apiInProgress = true;
				api(this.n, 'chat_essential_switch_auth')
				.then((function(v) {
					apiInProgress = false;
					location.reload();
				}).bind(this))
				.catch((function(e1) {
					apiInProgress = false;
					var err = '';
					if (e1.responseText) {
						err = parseError(e1);
						this.showStatus(err, 'error');
					} else {
						console.error(e1);
					}
				}).bind(this));
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

	const api = function(nonce, action, path, body) {
		const options = {
			'action': action,
			'path': path,
			'body': body,
			'_wpnonce': nonce,
		};
		return $.post(ajaxurl, options)
			.then(function(data) {
				try {
					const jdata = JSON.parse(data);
					return jdata;
				} catch(e) {
					return data;
				}
			})
			.fail(function(err) {
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
				} else {
					console.error(err);
					return 'Internal server error';
				}
			});
	};

	const validateEmail = function(email) {
		if (!email)
			return 'Email is required';

		if (email.length > 254) {
			return 'Email must be less than 255 characters';
		}

		const emailRegex = new RegExp(pageParams.emailRegex, 'g');
		const res = emailRegex.exec(email);
		if (!res) {
			return 'Enter a valid email address';
		}

		const parts = email.split("@");
		if (parts[0].length > 64) {
			return 'Email before @ must be less than 65 characters';
		}

		const domainParts = parts[1].split(".");
		if (domainParts.some(function(part) {
			return part.length > 63;
		})) {
			return 'Email domain must be less than 64 characters';
		}

		return;
	};

	const validatePassword = function(pass) {
		if (!pass || pass.length < 1) {
			return 'Password is required';
		}
		if (pass.length < 8 || pass > 16) {
			return 'Password must be between 8 and 16 characters';
		}

		const passwordRegex = new RegExp(pageParams.passwordRegex, 'g');
		const res = passwordRegex.exec(pass);
		if (!res) {
			return 'Password must contain at least 1 special character';
		}
		return;
	};

})( jQuery );
