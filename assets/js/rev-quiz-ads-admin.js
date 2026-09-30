/* REW QUIZZ > Ad blocks: tabs, code editors and fields that depend on the insertion. */
jQuery(function ($) {
	'use strict';

	var $tabs = $('.rq-ai-tab');
	if (!$tabs.length) return;

	var numbered = ['before_paragraph', 'after_paragraph', 'before_image', 'after_image', 'between_posts', 'quiz_after_option', 'before_excerpt', 'after_excerpt'];
	var editors = {};
	var storageKey = 'rq_ads_tab';

	// CodeMirror sizes itself wrongly while hidden, so each editor starts when its tab is first shown.
	function startEditors($panel) {
		if (!window.wp || !wp.codeEditor || !rqAdsAdmin.editor) return;
		$panel.find('textarea.rq-ai-code').each(function () {
			if (editors[this.name]) {
				editors[this.name].codemirror.refresh();
				return;
			}
			editors[this.name] = wp.codeEditor.initialize(this, rqAdsAdmin.editor);
		});
	}

	function show(tab) {
		var $panel = $('.rq-ai-panel[data-panel="' + tab + '"]');
		if (!$panel.length) {
			tab = 'block-1';
			$panel = $('.rq-ai-panel[data-panel="block-1"]');
		}
		$('.rq-ai-panel').attr('hidden', true);
		$panel.removeAttr('hidden');
		$tabs.removeClass('is-current').attr('aria-selected', 'false');
		$tabs.filter('[data-tab="' + tab + '"]').addClass('is-current').attr('aria-selected', 'true');
		startEditors($panel);
		try {
			window.localStorage.setItem(storageKey, tab);
		} catch (e) {}
	}

	$tabs.on('click', function () {
		show($(this).attr('data-tab'));
	});

	function syncPosition($select) {
		var $panel = $select.closest('.rq-ai-panel');
		var value = $select.val();
		var isNumbered = numbered.indexOf(value) !== -1;
		$panel.find('.rq-ai-numbers, .rq-ai-numbers-help').prop('hidden', !isNumbered);
		$panel.find('.rq-ai-popup').prop('hidden', value !== 'popup');
		var labels = {
			before_paragraph: 'Paragraphs', after_paragraph: 'Paragraphs',
			before_image: 'Images', after_image: 'Images',
			between_posts: 'After posts', before_excerpt: 'Excerpts', after_excerpt: 'Excerpts',
			quiz_after_option: 'After options'
		};
		$panel.find('.rq-ai-numbers-label').text(labels[value] || 'Numbers');
	}

	$('.rq-ai-position').on('change', function () {
		syncPosition($(this));
	}).each(function () {
		syncPosition($(this));
	});

	$('.rq-ai-align').on('change', function () {
		$(this).closest('.rq-ai-panel').find('.rq-ai-css').prop('hidden', $(this).val() !== 'custom');
	});

	// Keep the block name in the tab tooltip current.
	$('.rq-ai-head input[type="text"]').on('input', function () {
		var tab = $(this).closest('.rq-ai-panel').attr('data-panel');
		$tabs.filter('[data-tab="' + tab + '"]').attr('title', this.value || this.placeholder);
	});

	var start = 'block-1';
	try {
		start = window.localStorage.getItem(storageKey) || start;
	} catch (e) {}
	show(start);
});
