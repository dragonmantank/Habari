habari.media = {

	showdir: function (path, el, container) {
		$('.media_browser', container).show();
		$('.media_panel', container).hide();
		if ( !el && !container ) {
			container = $('.mediasplitter:visible');
		}
		if ( el && !container ) {
			container = $(el).parents('.mediasplitter');
		}
		if ( $('.pathstore', container).html() != path || $('.pathstore.toload', container).size() ) {
			$.ajax({
				type: "POST",
				url: habari.url.habari + '/admin_ajax/media',
				data: {path: path},
				dataType: 'json',
				beforeSend: function() {
					spinner.start();
				},
				success: function(result) {

					habari.media.unqueueLoad();
					$('.pathstore', container).html(result.path);

					// If we got dirs to show
					if (result.dirs != '') {
						var output = '<li class="media_dirlevel"><ul>';

						// Build path
						for (var dir in result.dirs) {
							output += '<li onclick="return habari.media.clickdir(this, \'' + result.dirs[dir].path + '\');" class="directory">' + result.dirs[dir].title + '</li>';
						}

						output += '</ul></li>';
					}

					// If path changed
					if (el) {
						$($(el).parents('.media_dirlevel')).nextAll().remove();
						$(el).parents('.media_dirlevel').after(output);
					} else if ($('.mediadir', container).html() == '') {
						$('.mediadir', container).html(output);
					}

					// Build Media List
					output = '<ul>';
					var first = ' first';
					var mediaCount = 0;
					habari.media.assets = result.files;

					for (var file in result.files) {
						stats = '';
						output += '<li class="media' + first + '"><span class="foroutput">' + file + '</span>';

						if (result.files[file].filetype && habari.media.preview[result.files[file].filetype]) {
							output += habari.media.preview[result.files[file].filetype](file, result.files[file]);
						} else {
							output += habari.media.preview._(file, result.files[file]);
						}

						output += '<ul class="mediaactions dropbutton">'
						if (result.files[file].filetype && habari.media.output[result.files[file].filetype]) {
							for (method in habari.media.output[result.files[file].filetype]) {
								output += '<li><a href="#" onclick="habari.media.output.' + result.files[file].filetype + '.' + method + '(\'' + file +'\', habari.media.assets[\''+ file + '\']);return false;">' + method.replace('_', ' ', 'g') + '</a></li>';
							}
						} else {
							for (method in habari.media.output._) {
								output += '<li><a href="#" onclick="habari.media.output._.' + method + '(\'' + file +'\', habari.media.assets[\''+ file + '\']);return false;">' + method.replace('_', ' ', 'g') + '</a></li>';
							}
						}
						output += '</ul>';


						output += '</li>';
						first = '';
						mediaCount++;
					}

					output += '<li class="end' + first + '">&nbsp;</li></ul>';

					$('.mediaphotos', container).html(output);
					$('.media').dblclick(function(){
						habari.media.insertAsset(this);
					});
					$('.media_controls ul li', container).remove();
					$('.media_controls ul', container).append(result.controls);
					labeler.init();

					spinner.stop();

					// When first opened, load the first directory automatically, but only if there are no files in the root
//					if ($('.mediaphotos .media', container).length == 0 && $('.media_dirlevel:first-child li.active', container).length == 0) {
//						$('.media_dirlevel:last-child li:first-child', container).click();
//					}

					$('.media img').addClass('loading');

					// As each image loads
					$(".media img").bind('load',function() {
						$(this)
							.removeClass('loading')
							.siblings('div').width($(this).width()+2)
						});

					findChildren();
				}}
			);
		}
	},

	clickdir: function(el, path) {
		// Clear current media items
		$('.mediaphotos').html('<ul><li class="end">&nbsp;</li></ul>')

		// Get new media items
		this.showdir(path, el);

		// Mark the current directory
		$(el).addClass('active').siblings().removeClass('active')

		return false;
	},

	showpanel: function (path, panel) {
		$.post(
			habari.url.habari + '/admin_ajax/media_panel',
			{
				path: path,
				panel: panel
			},
			habari.media.jsonpanel,
			'json'
		);
	},

	jsonpanel: function(result) {
		container = $('.mediasplitter:visible');
		$('.media_controls ul li:first', container).nextAll().remove();
		$('.media_controls ul li:first', container).after(result.controls);
		$('.media_panel', container).html(result.panel);
		$('.media_browser', container).hide();
		$('.media_panel', container).show();
	},

	unqueueLoad: function() {
		container = $('.mediasplitter:visible');
		$('.toload', container).removeClass('toload');
	},

	forceReload: function() {
		container = $('.mediasplitter:visible');
		$('.pathstore', container).addClass('toload');
	},

	fullReload: function() {
		container = $('.mediasplitter:visible');
		$('.mediadir', container).html('');
		$('.mediaphotos', container).html('');
		$('.pathstore', container).addClass('toload');
	},

	preview: {
		_: function(fileindex, fileobj) {
			var stats = '';
			return '<div class="mediatitle"><a class="mediadelete" title="Delete file" href="#" onclick="habari.media.showpanel(\'' + fileobj.path +'\', \'delete\');return false;">#</a>' + fileobj.title + '</div><img src="' + fileobj.thumbnail_url + '"><div class="mediastats"> ' + stats + '</div>';
		}
	},

	output: {
		image_jpeg: {insert_image: function(fileindex, fileobj) {habari.editor.insertSelection('<img alt="' + fileobj.title + '" src="' + fileobj.url + '" width="' + fileobj.width + '" height="' + fileobj.height + '">');}},
		image_gif: {insert_image: function(fileindex, fileobj) {habari.editor.insertSelection('<img alt="' + fileobj.title + '" src="' + fileobj.url + '" width="' + fileobj.width + '" height="' + fileobj.height + '">');}},
		image_png: {insert_image: function(fileindex, fileobj) {habari.editor.insertSelection('<img alt="' + fileobj.title + '" src="' + fileobj.url + '" width="' + fileobj.width + '" height="' + fileobj.height + '">');}},
		audio_mpeg3: {insert_link: function(fileindex, fileobj) {habari.editor.insertSelection('<a href="' + fileobj.url + '">' + fileobj.title + '</a>');}},
		video_mpeg: {insert_link: function(fileindex, fileobj) {habari.editor.insertSelection('<a href="' + fileobj.url + '">' + fileobj.title + '</a>');}},
		audio_wav: {insert_link: function(fileindex, fileobj) {habari.editor.insertSelection('<a href="' + fileobj.url + '">' + fileobj.title + '</a>');}},
		application_x_shockwave_flash: {insert_link: function(fileindex, fileobj) {habari.editor.insertSelection('<a href="' + fileobj.url + '">' + fileobj.title + '</a>');}},
		_: {insert_link: function(fileindex, fileobj) {habari.editor.insertSelection('<a href="' + fileobj.url + '">' + fileobj.title + '</a>');}}
	},

	submitPanel: function(path, panel, form, callback) {
		var query = $(form).serializeArray();
		query.push({name: 'path', value: path});
		query.push({name: 'panel', value: panel});

		$.post(
			habari.url.habari + '/admin_ajax/media_panel',
			query,
			function(result) {
				$('.media_panel').html(result.panel);
				if (callback != '') {
					eval(callback);
				}
			},
			'json');
	},

	insertAsset: function(asset) {
		var id = $('.foroutput', asset).html();
		if(this.assets[id].filetype && habari.media.output[this.assets[id].filetype]) {
			fns = habari.media.output[this.assets[id].filetype];
		}
		else {
			fns = habari.media.output._;
		}
		if(jQuery.makeArray(fns).length == 1) {
			for(var fname in fns) {
				fns[fname](id, this.assets[id]);
			}
		}
		else {
			habari.media.multioutput(fns, id, this.assets[id]);
		}
	},

	multioutput: function(fns, id, asset) {
		/* @todo Create interface for multiple output methods on a single file type */
		for(var fname in fns) {
			fns[fname](id, this.assets[id]);
			break;
		}
	},

	clearSelections: function() {
		var container = $('.mediasplitter:visible')
		// remove all highlights
		$('.mediadir .directory', container).removeClass('active');
		// remove second level directories
		$('.mediadir .media_dirlevel', $('.mediasplitter:visible')).nextAll().remove()
	}

};

$(document).ready(function(){
	$('#mediatabs').parent().tabs({
		fx: { height: 'toggle', opacity: 'toggle' },
		selected: -1,
		collapsible: true,
		show: function(){
			var tabindex = $(this).tabs( 'option', 'selected' );
			var tab = $('.mediasplitter').eq(tabindex);
			var path = $.trim( $('.pathstore', tab).html() );
			if (path != '') {
				habari.media.showdir( path, null, tab );
				habari.media.unqueueLoad();
			}
			return true;
		}
	});
});
