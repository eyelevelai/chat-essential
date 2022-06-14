(function( $ ) {
	'use strict';

	let apiInProgress = false;
	const selectKitsDisabled = true;

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
				this.logoutBtn = $('#logoutBtn');
				this.logoutBtn.click(this.logout.bind(this));
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
							this.form = $('#settingsForm');
							this.form.submit(this.onSettingsSubmit.bind(this));
							this.emailInput = $('#email');
							this.emailInput.change(this.onSettingsChange.bind(this));
							this.phoneInput = $('#phone');
							this.phoneInput.change(this.onSettingsChange.bind(this));
							this.confirmContent = $('#confirmContent');
							this.confirmChange = $('#confirmChange');
							this.confirmChange.click(this.settingsConfirmed.bind(this));
							this.cancelChange = $('#cancelChange');
							this.cancelChange.click(this.settingsCancelled.bind(this));
							this.submitBtn = $('#submit');
							this.submitBtn.prop('disabled', true);
							return;
						case 'chat-essential-signup':
						case 'chat-essential-login':
							this.form = $('#loginForm');
							this.form.submit(this.onAuthSubmit.bind(this));
							this.switchBtn = $('#footerBtn');
							this.switchBtn.click(this.switchAuth.bind(this));
							return;
						case 'chat-essential-edit-load-on-rule':
							this.deleteRule = $('#deleteRule');
							this.deleteRule.click(this.confirmDeleteRule.bind(this));
							this.deleteRuleContent = $('#deleteRuleContent');
							this.confirmDeleteRule = $('#confirmDeleteRule');
							this.confirmDeleteRule.click(this.deleteRuleConfirmed.bind(this));
							this.cancelDeleteRule = $('#cancelDeleteRule');
							this.cancelDeleteRule.click(this.deleteRuleCancelled.bind(this));
						case 'chat-essential-create-load-on-rule':
							this.form = $('#ruleForm');
							this.form.submit(this.onRuleSubmit.bind(this));
							return;
						case 'chat-essential-signup-phone':
							this.form = $('#loginForm');
							this.form.submit(this.onPhoneSubmit.bind(this));
							this.skipBtn = $('#footerBtn');
							this.skipBtn.click(this.skipPhone.bind(this));
							return;
						case 'chat-essential-website':
							const parent = this;
							$('.delete-rule').each(function(i, e) {
								$(e).click(parent.confirmDeleteRule.bind(parent));
							});
							this.deleteRuleContent = $('#deleteRuleContent');
							this.confirmDeleteRule = $('#confirmDeleteRule');
							this.confirmDeleteRule.click(this.deleteRuleConfirmed.bind(this));
							this.cancelDeleteRule = $('#cancelDeleteRule');
							this.cancelDeleteRule.click(this.deleteRuleCancelled.bind(this));
							this.switches = $('.ey-switch');
							this.switches.click(this.websiteSwitch.bind(this));
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
/*
					if (len > 0) {
						for (var key in this.aiTopics) {
							$('#' + key).prop('checked', false);
							delete this.aiTopics[key];
						}
						this.showStatus('Upgrade to premium to select more than 1 topic', 'error');
					}
*/
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
						kits.push(k);
					}
				}

				let modelSelected = false;
				const existingKits = {};
				for (const val of kits) {
					if (!existingKits[val.kitId]) {
						existingKits[val.kitId] = true;
						const id = val.name.replace(' ', '-');
						let col = '';
						if (selectKitsDisabled) {
							if (val.engine) {
								if (!modelSelected) {
									modelSelected = true;
									this.aiEngines.push('topic-' + id);
									col += '<input id="topic-' + id + '" type="hidden" value="' + val.engine + '" />';
								}
							} else {
								col = '<td><input id="topic-' + id + '" type="checkbox" name="topic-' + id + '"';
								col += ' value="' + val.kitId + '" class="ai-model-checkbox" checked disabled';
								col += ' /><div class="ai-input-label">' + val.name + '</div></td>';
							}
						} else {
							col = '<td><input id="topic-' + id + '" type="checkbox" name="topic-' + id + '"';
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
						}

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
				}
				return models;
			},
			loadModels: function() {
				if (apiInProgress) {
					return;
				}
				if (model === undefined || !pageParams.coreEngines) {
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
					if(model.training && model.training.taskId && model.training.status !== 'complete' && model.training.status !== 'error') {
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
				if (model && model.training && model.training.status && model.training.status !== 'complete' && model.training.status !== 'error') {
					this.showStatus('You have already submitted a training request that is not yet complete', 'error');
					return false;
				}

				this.showStatus('Uploading training data...This might take a while...');
				const data = this.siteOptionValues();
				if (!data) {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				} else if (data.error) {
					this.showStatus(data.error, 'error');
					return false;
				}
				apiInProgress = true;

				api(this.n, 'chat_essential_train', null, data)
				.then((function(v1) {
					apiInProgress = false;
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
				if (data.type === 'chat-essential-login') {
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
			onPhoneSubmit: function() {
				if (apiInProgress) {
					return false;
				}
				const data = this.phoneValue();
				if (!data) {
					return false;
				}
				apiInProgress = true;

				this.showStatus('Submitting...');

				api(this.n, 'chat_essential_phone_signup', null, data)
				.then((function(v1) {
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
				return false;
			},
			onRuleSubmit: function () {
				if (apiInProgress) {
					return false;
				}

				const data = this.siteOptionValues();
				const rid = $('#ruleId').val();
				if (rid) {
					data['rid'] = rid;
				}

				const flow = $('#flow').val();
				if (flow) {
					data['flow'] = flow;
				} else {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				}

				const device = $('#device_display').val();
				if (device) {
					data['device_display'] = device;
				} else {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				}

				const status = $('#status').val();
				if (device) {
					data['status'] = status;
				} else {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				}

				if (!data) {
					this.showStatus('There was an internal issue submitting your request', 'error');
					return false;
				} else if (data.error) {
					this.showStatus(data.error, 'error');
					return false;
				}
				apiInProgress = true;

				api(this.n, 'chat_essential_rule_update', null, data)
				.then((function(v1) {
					apiInProgress = false;
					console.log(v1);
					if (v1.url) {
						location.href = location.origin + location.pathname + v1.url;
					} else {
						this.showStatus(v1.message ? v1.message : 'The rule has been added', 'success');
					}
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
			confirmDeleteRule: function(e) {
				this.deleteValue = $(e.target).attr('value');
				this.deleteRuleContent.html('<p>Are you sure you want to delete this Load On Rule? You cannot undo this change.</p>');
				const url = '#TB_inline?inlineId=deleteRuleModal';
				tb_show('CONFIRM DELETE LOAD ON RULE', url);
				return false;
			},
			deleteRuleCancelled: function() {
				this.deleteValue = null;
				tb_remove();
			},
			deleteRuleConfirmed: function() {
				if (this.deleteValue) {
					this.processDeleteRuleSubmit({ rid: this.deleteValue });
				}
				tb_remove();
				return false;
			},
			processDeleteRuleSubmit: function(data) {
				console.log('delete');
				if (apiInProgress) {
					return false;
				}
				apiInProgress = true;
				this.showStatus('Deleting...');

				api(this.n, 'chat_essential_rule_delete', null, data)
				.then((function(v1) {
					apiInProgress = false;
					location.href = '?page=chat-essential-website';
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
			confirmSettingsSubmit: function(addingNumber) {
				if (addingNumber) {
					this.confirmContent.html('<p>We will automatically update your chat to enable live chatting.</p><p>Any changes you may have made to your chat flows will be overwritten by these updates.</p><p>Do you wish to proceed?</p>');
				} else {
					this.confirmContent.html('<p>We will automatically update your chat to disable live chatting.</p><p>Any changes you may have made to your chat flows will be overwritten by these updates.</p><p>Do you wish to proceed?</p>');
				}
				const url = '#TB_inline?inlineId=confirmModal';
				tb_show('CONFIRM UPDATES TO YOUR CHATS', url);
				return false;
			},
			settingsCancelled: function() {
				tb_remove();
			},
			settingsConfirmed: function() {
				const data = this.settingsValue();
				if (data) {
					data['updateType'] = og_phones === '' ? 'live' : 'automated';
					this.processSettingsSubmit(data);
				}
				tb_remove();
				return false;
			},
			onSettingsSubmit: function() {
				if (apiInProgress) {
					return false;
				}
				const data = this.settingsValue();
				if (!data) {
					return false;
				}

				if (data.phones !== undefined) {
					if (data.phones === '' || og_phones === '') {
						return this.confirmSettingsSubmit(og_phones === '');
					}
				}

				return this.processSettingsSubmit(data);
			},
			processSettingsSubmit: function(data) {
				if (apiInProgress) {
					return false;
				}
				apiInProgress = true;
				this.showStatus('Saving...');

				api(this.n, 'chat_essential_settings_change', null, data)
				.then((function(v1) {
					apiInProgress = false;
					this.showStatus('Saved!', 'success');
					setTimeout((function() {
						this.hideStatus();
					}).bind(this), 1000);
					if (data.email !== undefined) {
						og_email = data.email;
					}
					if (data.phones !== undefined) {
						og_phones = data.phones;
					}
					this.submitBtn.prop('disabled', true);
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
			onSettingsChange: function(evt) {
				const data = this.settingsValue();
				if (!data) {
					this.submitBtn.prop('disabled', true);
					return false;
				}
				this.submitBtn.prop('disabled', false);
			},
			skipPhone: function() {
				if (apiInProgress) {
					return false;
				}
				apiInProgress = true;

				this.showStatus('Submitting...');
				const phone = $('#phone');
				phone.removeClass('error-value');

				api(this.n, 'chat_essential_phone_signup', null, { phone: 'skip' })
				.then((function(v1) {
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
								this.showTrainingStatus(data.nlp.task.status, data.nlp.task);
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
			showTrainingStatus: function(status, task) {
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
				} else if (status === 'error') {
					this.showStatus('Training failed! Reverted to last successful trained model.', 'error');
					setTimeout((function(){
						this.printTrainingDate();
					}).bind(this), 5000);
				} else {
					this.showStatus('Your training request is currently being processed', 'warn');
					this.pollTrainingStatus();
				}
			},
			phoneValue: function() {
				this.hideStatus();

				const phone = $('#phone');
				phone.removeClass('error-value');

				const val = libphonenumber.parseNumber(phone.val(), 'US');

				if (!val.phone || 
					!libphonenumber.isValidNumber(val.phone, 'US')) {
					if (val.phone && val.phone === '5550000000') {
					} else {
						this.showStatus('Enter a valid US mobile number', 'error');
						phone.addClass('error-value');
						return;
					}
				}

				return {
					phone: val.phone
				};
			},
			settingsValue: function() {
				this.hideStatus();

				this.phoneInput.removeClass('error-value');
				const phones = this.phoneInput.val();

				this.emailInput.removeClass('error-value');
				const email = this.emailInput.val();

				const data = {};
				if (phones) {
					const phone1 = libphonenumber.parseNumber(og_phones, 'US');
					const phone2 = libphonenumber.parseNumber(phones, 'US');
					if (phone2.phone) {
						if (!libphonenumber.isValidNumber(phone2.phone, 'US')) {
							if (phone2.phone && phone2.phone === '5550000000') {
							} else {
								this.showStatus('Enter a valid US mobile number', 'error');
								this.phoneInput.addClass('error-value');
								return;
							}
						}
					} else {
						this.showStatus('Enter a valid US mobile number', 'error');
						this.phoneInput.addClass('error-value');
						return;
					}
					if (phone2.phone !== phone1.phone) {
						data.phones = phone2.phone;
					}
				} else if (phones !== og_phones) {
					data.phones = phones;
				}

				if (og_email !== email) {
					const err = validateEmail(email);
					if (err) {
						this.showStatus(err, 'error');
						this.emailInput.addClass('error-value');
						return;
					}
					data.email = email;
				}

				if (data.email === undefined &&
					data.phones === undefined
				) {
					return;
				}

				return data;
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
					case 'chat-essential-login':
						return data;
					case 'chat-essential-signup':
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

				var topics = 0;
				if (this.aiTopics) {
					topics = Object.keys(this.aiTopics).length;
					if (topics > 0) {
						data['kits'] = [];
						for (var k in this.aiTopics) {
							data['kits'].push(parseInt(this.aiTopics[k]));
						}
					}
				}
				if (this.aiEngines && this.aiEngines.length > 0) {
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
							return { error: 'Choose at least one page' };
						}
						data.in_pages = dt1;
						return data;
					case 'posts':
						dt1 = $('#inPosts').val();
						if(!dt1 || !dt1.length) {
							return { error: 'Choose at least one post' };
						}
						data.in_posts = dt1;
						return data;
					case 'categories':
						dt1 = $('#inCategories').val();
						if(!dt1 || !dt1.length) {
							return { error: 'Choose at least one category' };
						}
						data.in_categories = dt1;
						return data;
					case 'tags':
						dt1 = $('#inTags').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one tag', 'error');
							return { error: 'Choose at least one tag' };
						}
						data.in_tags = dt1;
						return data;
					case 'postTypes':
						dt1 = $('#inPostTypes').val();
						if(!dt1 || !dt1.length) {
							this.showStatus('Choose at least one post type', 'error');
							return { error: 'Choose at least one post type' };
						}
						data.in_postTypes = dt1;
						return data;
					case 'none':
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
			websiteSwitch: function(e) {
				const swt = $(e.target);
				const inp = $('#' + swt.attr('for'));
				if (apiInProgress) {
					e.preventDefault();
					return;
				}
				apiInProgress = true;
				const swtch = $('#' + swt.attr('for') + '-preview');
				swtch.toggle();
				api(this.n, 'chat_essential_switch_platform_status', null, {
					rulesId: swt.attr('for').replace('status', ''),
					status: !inp.is(':checked') ? 'active' : 'inactive',
				})
				.then((function(v) {
					apiInProgress = false;
				}).bind(this))
				.catch((function(e1) {
					apiInProgress = false;
					swtch.toggle();
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
