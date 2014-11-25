/**
 * Created by Мурат Эркенов <murat@11bits.net> on 2/20/14.
 */

/**
 * @var $ jQuery
 */
!(function($){

	var FileInput = function(url) {
		this.url = url;
		this.fileInfo = {
			name: null,
			size: null
		};
		this.basicAttributes = {
			name: null,
			value: null,
			ajaxParamName: null
		};
		this.options = {};
		this.fileButtonOptions = {
			'label': 'Загрузить файл',
			'class': 'choose_file btn btn-primary'
		};
		this.restrictions = {
			sizeLimit: null,
			fileTypes: null
		};

		/**
		 *
		 * @type {jQuery}
		 */
		this.uploaderElement = null;
	};

	FileInput.prototype = {
		setFileInfo: function(name, size){
			this.fileInfo.name = name;
			this.fileInfo.size = size;
		},
		setFieldName: function(name) {
			this.basicAttributes.name = name;
		},
		setValue: function(value) {
			this.basicAttributes.value = value;
		},
		setAjaxParamName: function(name) {
			this.basicAttributes.ajaxParamName = name;
		},
		setFileButtonOptions: function(fileButtonOptions) {
			$.extend(this.fileButtonOptions, fileButtonOptions || {});
		},
		setOption: function(option, value){
			this.options[option] = value;
		},
		setOptions: function(options){
			this.options = options;
		},
		setMaxFileSize: function(bytes) {
			this.restrictions.sizeLimit = bytes;
		},
		setAllowedFileTypes: function(typesArray) {
			this.restrictions.fileTypes = typesArray;
		},
		updateFileInfo: function(filename, filesize){
			if (!filename.length) {
				filename = '';
			}
			if (filesize > 0) {
				filesize = formatFileSize(filesize)
			} else {
				filesize = '';
			}
			this.uploaderElement.find('.fileinfo .name').text(filename);
			this.uploaderElement.find('.fileinfo .size').text(filesize);

			function formatFileSize(size) {
				var units = [
					'байт',
					'КБ',
					'МБ',
					'ГБ'
				];

				var tenPow = Math.min(parseInt(Math.log(size)  / Math.log(2)/10), units.length - 1);
				var unit = units[tenPow];

				var base = Math.pow(2, tenPow * 10);
				var str = parseInt(size / base);
				var ost = (size - str * base) / base;
				if (ost > 0) {
					str += parseFloat(ost);
					str = str.toFixed(2);
				}
				str += ' ' + unit;

				return str;
			}
		},
		/**
		 *
		 * @param elementsNames {String}
		 */
		showActionElements: function (elementsNames) {
			var elementsNames = elementsNames.split(',');
			this.uploaderElement.find('.actions > *').each(function(ind, el){
				el = $(el);
				if ($.inArray(el.attr('class'), elementsNames) > -1) {
					el.show();
				} else {
					el.hide();
				}
			});
		},

		updateValue: function () {
			this.uploaderElement.find('.uploaderHiddenField').attr('name', this.basicAttributes.name);
			this.uploaderElement.find('.uploaderHiddenField').val(this.basicAttributes.value);
		},


		updateUploadStatus: function (status) {
			this.uploaderElement.find('.uploadStatus').text(status);
		},

		/**
		 * @param el {jQuery}
		 */
		renderInto: function(el) {
			this.uploaderElement = $('<span />', {class: 'uploader', css: {display: 'inline-block' }});
			el.replaceWith(this.uploaderElement);

			renderStatusBlock(this.uploaderElement);
			renderFileInfoBlock(this.uploaderElement);
			renderActionsBlock(this.uploaderElement, this.fileButtonOptions);
			renderHiddenField(this.uploaderElement);

			this.updateFileInfo(this.fileInfo.name, this.fileInfo.size);
			this.showActionElements('choose_file');
			this.updateValue();

			/**
			 *
			 * @type {FileInput}
			 */
			var zis = this;
			createFUObject();

			function createFUObject() {

				$(zis.uploaderElement).fileupload({
					url: zis.url,
					dataType: 'json',
					paramName: zis.basicAttributes.ajaxParamName,

					change: function(e, data) {
						var file = data.files[0];
						zis.updateFileInfo(file.name, file.size);
						zis.updateUploadStatus('Загружается...');
						zis.showActionElements('cancel_upload');
					},

					complete: function (e, data) {
						var response = $.parseJSON(this.xhr().response);
						if (response.error) {
							this.fail(e, response.error)
						} else {
							zis.setValue(response.result);
							zis.updateValue();
							zis.updateUploadStatus('Загружен');
							zis.showActionElements('choose_file');
						}
					},

					progressall: function(e, data) {
						var progress = parseInt(data.loaded / data.total * 100, 10);
						zis.updateUploadStatus(progress + '%');
						zis.showActionElements('cancel_upload');
					},

					fail: function (e, data) {
						zis.updateUploadStatus('Ошибка ' + data);
						zis.showActionElements('choose_file');
					},

					cancel: function(e, data) {
						zis.updateUploadStatus('Загрузка отменена');
						zis.showActionElements('choose_file');
					}
				});
			}

			function renderFileInfoBlock(container) {
				var block = $('<span />', {class: 'fileinfo'});
				block.append($('<span />', {class: 'name'}));
				block.append($('<span />', {class: 'size'}));
				container.append(block);
			}

			function renderStatusBlock(container) {
				var block = $('<span />', {class: 'uploadStatus'});
				container.append(block);
			}

			function renderActionsBlock(container, buttonOptions) {
				var block = $('<span />', {class: 'actions'});
				container.append(block);
				renderChooseFileActionBlock(block, buttonOptions);
//        renderCancelActionBlock(block);
			}

			function renderCancelActionBlock(container) {
				container.append($('<input />', {
					type: 'button',
					value: 'Отмена',
					class: 'cancel_upload',
					click: function(){
						uploader.abort();
					}
				}));
			}

			function renderChooseFileActionBlock(container, buttonOptions) {
				var wrapper = $('<span />', {
					class: 'choose_file',
					css: {
						display: 'inline-block',
						position: 'relative'
					}
				});
				container.append(wrapper);

				var fileInput = $('<input />', {
					type: 'file',
					class: buttonOptions.class,
					css: {
						opacity: 0,
						position: 'absolute',
						left: 0,
						top: 0,
						width: '100%',
						zIndex: 2
					}
				});
				wrapper.append(fileInput);
				wrapper.append($('<input />', {type: 'button', value: buttonOptions.label, class: buttonOptions.class}));
			}

			function renderHiddenField(wrapper) {
				wrapper.append($('<input />', {
					"type": "hidden",
					"class": "uploaderHiddenField"
				}));
			}

		}
	};

	FileInput.createInstanceWithObject = function(obj) {
		if (null == obj.url) {
			throw new Error('Can not create FileInput without URL');
		}
		var instance = new FileInput(obj.url);
		if (0 != obj.fileInfo) {
			instance.setFileInfo(obj.fileInfo.name, obj.fileInfo.size);
		}
		if (obj.name) {
			instance.setFieldName(obj.name);
		}
		if (obj.value) {
			instance.setValue(obj.value);
		}
		if (obj.ajaxParamName) {
			instance.setAjaxParamName(obj.ajaxParamName);
		}
		if (obj.fileButtonOptions) {
			instance.setFileButtonOptions(obj.fileButtonOptions);
		}
		if (obj.maxSize) {
			instance.setMaxFileSize(obj.maxSize);
		}
		if (obj.allowedTypes) {
			instance.setAllowedFileTypes(obj.allowedTypes);
		}
		return instance;
	};

	FileInput.captureAll = function() {
		$('file').each(function(ind, el){
			FileInput.captureOne($(el));
		});
	};

	/**
	 * @param el jQuery
	 */
	FileInput.captureOne = function(el) {
		var instance = FileInput.createInstanceWithObject(parseAttributesOutFromElement(el));
		instance.renderInto(el);
	};

	function parseAttributesOutFromElement(el) {
		var attr = {
			url: el.attr('url'),
			fileInfo: {
				name: el.attr('file_name'),
				size: el.attr('file_size')
			},
			name: el.attr('name'),
			value: el.attr('value'),
			ajaxParamName: el.attr('ajax_param'),
			maxSize: el.attr('max_size'),
			fileButtonOptions: $(el).data('options')['fileButton']
		};

		var typesStr = el.attr('allowed_types');
		if (null != typesStr && typesStr.length) {
			var allowedTypes = [];
			$(typesStr.split(',')).each(function(ind, type){
				type = type.trim();
				if (type.length) {
					allowedTypes.push(type);
				}
			});
			if (allowedTypes.length) {
				attr.allowedTypes = allowedTypes;
			}
		}

		return attr;
	}

	$(document).ready(
		function(){
			FileInput.captureAll();
		}
	);
})(jQuery);
