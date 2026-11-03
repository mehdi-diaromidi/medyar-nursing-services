(function () {
	'use strict';

	window.MNS = window.MNS || {};

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function initDrawer() {
		var dash = qs('.mns-dash');
		if (!dash) {
			return;
		}

		var sidebar = qs('[data-mns-sidebar]', dash);
		var expand = qs('[data-mns-sidebar-expand]');
		var backdrop = qs('[data-mns-sidebar-backdrop]');
		if (!sidebar || !expand || !backdrop) {
			return;
		}

		function isMobile() {
			return window.matchMedia('(max-width: 767px)').matches;
		}

		function setOpen(open) {
			sidebar.classList.toggle('mobile-expanded', open);
			backdrop.hidden = !open;
			backdrop.classList.toggle('is-visible', open);
			expand.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.documentElement.classList.toggle('mns-drawer-open', open);
			if (open) {
				var closeFocus = qs('[data-mns-sidebar-close]', sidebar) || expand;
				closeFocus.focus({ preventScroll: true });
			}
		}

		expand.hidden = !isMobile();
		expand.addEventListener('click', function () {
			setOpen(!sidebar.classList.contains('mobile-expanded'));
		});
		backdrop.addEventListener('click', function () {
			setOpen(false);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				setOpen(false);
			}
		});
		window.addEventListener('resize', function () {
			var mobile = isMobile();
			expand.hidden = !mobile;
			if (!mobile) {
				setOpen(false);
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		initDrawer();
	});
})();
