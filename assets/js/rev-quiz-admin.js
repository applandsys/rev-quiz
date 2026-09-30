/* REW QUIZZ question editor: add, remove, reorder questions and options; pick option images. */
jQuery(function ($) {
	'use strict';

	var $list = $('.rq-questions');
	if (!$list.length) return;

	var questionTmpl = $('#tmpl-rq-question').html();
	var optionTmpl = $('#tmpl-rq-option').html();

	function renumber() {
		$list.children('.rq-q').each(function (i) {
			$(this).find('.rq-q-num').text('Step ' + (i + 1));
		});
	}

	function nextIndex($el) {
		var n = parseInt($el.attr('data-next'), 10) || 0;
		$el.attr('data-next', n + 1);
		return n;
	}

	function addOption($question) {
		var $tbody = $question.find('.rq-options');
		var html = optionTmpl.replace(/__Q__/g, $question.attr('data-q')).replace(/__O__/g, nextIndex($tbody));
		var $row = $(html);
		$tbody.append($row);
		return $row;
	}

	function makeSortable($context) {
		$context.find('.rq-options').sortable({ handle: '.rq-handle', axis: 'y' });
	}

	$list.sortable({ handle: '.rq-q-head .rq-handle', axis: 'y', update: renumber });
	makeSortable($list);
	renumber();

	$('.rq-add-question').on('click', function () {
		var $q = $(questionTmpl.replace(/__Q__/g, 'n' + nextIndex($list)));
		$list.append($q);
		addOption($q);
		addOption($q);
		makeSortable($q);
		renumber();
		$q.find('.rq-q-title').trigger('focus');
	});

	$list.on('click', '.rq-add-option', function () {
		addOption($(this).closest('.rq-q')).find('input').first().trigger('focus');
	});

	$list.on('click', '.rq-o-remove', function () {
		$(this).closest('.rq-o').remove();
	});

	$list.on('click', '.rq-q-remove', function () {
		if (window.confirm(RevQuizAdmin.confirmQuestion)) {
			$(this).closest('.rq-q').remove();
			renumber();
		}
	});

	$list.on('click', '.rq-q-toggle', function () {
		$(this).closest('.rq-q').toggleClass('is-collapsed');
	});

	function preview($cell) {
		var value = $cell.find('.rq-image').val() || '';
		if (value.indexOf('{rq_assets}') === 0) {
			value = RevQuizAdmin.assetsUrl + encodeURIComponent(value.slice('{rq_assets}'.length));
		}
		$cell.find('.rq-preview').attr('src', value).prop('hidden', !value);
	}

	$list.on('change', '.rq-image', function () {
		preview($(this).closest('.rq-image-cell'));
	});

	$list.on('click', '.rq-pick', function () {
		var $cell = $(this).closest('.rq-image-cell');
		var frame = wp.media({ title: RevQuizAdmin.chooseImage, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var file = frame.state().get('selection').first().toJSON();
			$cell.find('.rq-image').val(file.url);
			preview($cell);
		});
		frame.open();
	});
});
