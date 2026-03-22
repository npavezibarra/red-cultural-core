(function () {
	function init(root) {
		if (!root) return;
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-rcp-slide]'));
		if (!slides.length) return;

		var viewport = root.querySelector('[data-rcp-viewport]');
		var interval = parseInt(root.getAttribute('data-interval') || '3000', 10);
		if (!interval || interval < 800) interval = 3000;

		var index = 0;
		function measure() {
			if (!viewport) return;
			var active = slides[index];
			if (!active) return;
			var inner = active.querySelector('.grid') || active;
			var rect = inner.getBoundingClientRect();
			var h = rect && rect.height ? rect.height : inner.scrollHeight;
			if (h && h > 0) {
				viewport.style.minHeight = Math.ceil(h) + 'px';
			}
		}

		function setActive(next) {
			index = (next + slides.length) % slides.length;
			slides.forEach(function (s, i) {
				s.classList.toggle('is-active', i === index);
			});

			// Ensure the absolute-positioned slides have a stable container height.
			requestAnimationFrame(function () {
				measure();
				requestAnimationFrame(measure);
			});
		}

		// Initial sizing.
		setActive(0);
		window.addEventListener('resize', function () {
			setActive(index);
		});
		window.addEventListener('load', function () {
			measure();
		});

		if (slides.length < 2) return;

		var timer = window.setInterval(function () {
			setActive(index + 1);
		}, interval);

		// Pause on hover/focus within.
		root.addEventListener('mouseenter', function () {
			if (timer) window.clearInterval(timer);
			timer = null;
		});
		root.addEventListener('mouseleave', function () {
			if (timer) return;
			timer = window.setInterval(function () {
				setActive(index + 1);
			}, interval);
		});
	}

	function boot() {
		document.querySelectorAll('[data-rcp-carousel]').forEach(function (root) {
			init(root);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
