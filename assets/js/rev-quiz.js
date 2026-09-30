/* Rev Quiz front end: saves answers, applies the language choice and moves between steps. */
(function () {
	'use strict';

	var main = document.querySelector('.rq-main[data-quiz]');
	if (!main) return;

	var quizId = main.getAttribute('data-quiz');
	var step = parseInt(main.getAttribute('data-step'), 10) || 1;
	var storageKey = 'rq_answers_' + quizId;

	function readAnswers() {
		try {
			return JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
		} catch (e) {
			return {};
		}
	}

	function saveAnswer(label, correct) {
		var answers = readAnswers();
		answers[step] = { label: label, correct: correct };
		try {
			window.localStorage.setItem(storageKey, JSON.stringify(answers));
		} catch (e) {
			// Storage can be unavailable in private browsing; the quiz still works.
		}
	}

	// Google Translate reads the googtrans cookie on every page load.
	function setLanguage(code) {
		var from = main.getAttribute('data-page-lang') || 'en';
		var value = code === from ? '' : '/' + from + '/' + code;
		var expires = value ? '' : '; expires=Thu, 01 Jan 1970 00:00:00 GMT';
		document.cookie = 'googtrans=' + value + '; path=/' + expires;
		try {
			window.localStorage.setItem('language', code);
		} catch (e) {}
	}

	function go(url) {
		var moved = false;
		function leave() {
			if (moved) return;
			moved = true;
			window.location.href = url;
		}
		var ads = window.SiteAds;
		if (ads && ads.config && ads.config.formats && ads.config.formats.interstitial) {
			// Never leave the visitor stuck if the ad library is blocked.
			window.setTimeout(leave, 4000);
			ads.showInterstitial(leave);
		} else {
			leave();
		}
	}

	main.addEventListener('click', function (event) {
		var option = event.target.closest('.rq-option[data-label]');
		if (!option || event.metaKey || event.ctrlKey || event.shiftKey) return;
		event.preventDefault();
		option.classList.add('is-picked');
		saveAnswer(option.getAttribute('data-label'), option.hasAttribute('data-correct'));
		if (option.hasAttribute('data-lang')) {
			setLanguage(option.getAttribute('data-lang'));
		}
		go(option.href);
	});

	// Result screen: list the saved answers.
	var box = main.querySelector('.rq-answers');
	if (box) {
		var answers = readAnswers();
		var any = false;
		box.querySelectorAll('li[data-step]').forEach(function (item) {
			var answer = answers[item.getAttribute('data-step')];
			// Answers saved by 1.x were plain labels.
			if (typeof answer === 'string') answer = { label: answer, correct: false };
			if (answer) {
				item.querySelector('strong').textContent = answer.label;
				any = true;
			} else {
				item.remove();
			}
		});
		if (any) box.hidden = false;
	}

	// Trivia quizzes: count the correct answers.
	var score = main.querySelector('.rq-score');
	if (score) {
		var saved = readAnswers();
		var correct = 0;
		Object.keys(saved).forEach(function (key) {
			if (saved[key] && saved[key].correct) correct++;
		});
		score.querySelector('.rq-score-value').textContent = correct;
		score.hidden = false;
	}

	// "Start again" clears the saved answers so the score starts from zero.
	var restart = main.querySelector('.rq-restart');
	if (restart) {
		restart.addEventListener('click', function () {
			try {
				window.localStorage.removeItem(storageKey);
			} catch (e) {}
		});
	}
}());
