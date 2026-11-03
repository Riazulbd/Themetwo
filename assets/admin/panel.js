/* Theme Panel: tabs, colour pickers, media picker, reset confirmation. */
(function ($) {
	'use strict';

	var $tabs = $('.np-tabs a'),
		$panels = $('.np-tab'),
		KEY = 'npPanelTab';

	function show(id) {
		if (!$panels.filter('#' + id).length) {
			id = $panels.first().attr('id');
		}
		$panels.attr('hidden', 'hidden').filter('#' + id).removeAttr('hidden');
		$tabs.removeClass('nav-tab-active').filter('[href="#' + id + '"]').addClass('nav-tab-active');
		try { sessionStorage.setItem(KEY, id); } catch (e) { /* storage blocked: fine */ }
	}

	$tabs.on('click', function (e) {
		e.preventDefault();
		var id = this.getAttribute('href').slice(1);
		show(id);
		if (window.history && history.replaceState) {
			history.replaceState(null, '', '#' + id);
		}
	});

	var start = window.location.hash.slice(1);
	if (!start) {
		try { start = sessionStorage.getItem(KEY) || ''; } catch (e) { start = ''; }
	}
	show(start);

	/* Colours: an empty value falls back to the default, so "Clear" is the same as "Default". */
	$('.np-color').wpColorPicker();

	/* Images. */
	$(document).on('click', '.np-img-pick', function (e) {
		e.preventDefault();
		var $box = $(this).closest('.np-img'),
			frame = wp.media({
				title: window.npPanel.chooseTitle,
				button: { text: window.npPanel.chooseBtn },
				library: { type: 'image' },
				multiple: false
			});
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON(),
				url = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
			$box.find('input[type="hidden"]').val(a.id);
			$box.find('.np-img-preview').empty().append($('<img>').attr('src', url).attr('alt', ''));
			$box.find('.np-img-remove').prop('hidden', false);
		});
		frame.open();
	});
	$(document).on('click', '.np-img-remove', function (e) {
		e.preventDefault();
		var $box = $(this).closest('.np-img');
		$box.find('input[type="hidden"]').val('');
		$box.find('.np-img-preview').empty();
		$(this).prop('hidden', true);
	});

	/* Reset button. */
	$('#np-panel-form').on('click', '[name="np_reset"]', function (e) {
		if (!window.confirm(window.npPanel.confirm)) {
			e.preventDefault();
		}
	});
})(jQuery);
