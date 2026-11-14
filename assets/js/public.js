(function () {
	'use strict';

	window.MNS = window.MNS || {};

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function qsa(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function toFaDigits(value) {
		return String(value).replace(/\d/g, function (d) {
			return '۰۱۲۳۴۵۶۷۸۹'[d];
		});
	}

	function initDrawer() {
		var sidebar = qs('[data-mns-sidebar]');
		var expand = qs('[data-mns-sidebar-expand]');
		var closeBtn = qs('[data-mns-sidebar-close]');
		if (!sidebar || !expand) return;

		/*
		 * Expand + backdrop always live on body.
		 * On mobile, also move the sidebar to body so it is not trapped under the
		 * Elementor sticky header stacking context (header was covering the drawer).
		 * Desktop keeps the sidebar inside `.mns-dash` for the 306px grid column.
		 */
		var sidebarHome = sidebar.parentElement;
		var sidebarHomeNext = sidebar.nextSibling;

		function placeSidebarForViewport(isMobile) {
			if (isMobile) {
				if (sidebar.parentElement !== document.body) {
					document.body.appendChild(sidebar);
				}
				return;
			}
			if (!sidebarHome || sidebar.parentElement === sidebarHome) return;
			if (sidebarHomeNext && sidebarHomeNext.parentNode === sidebarHome) {
				sidebarHome.insertBefore(sidebar, sidebarHomeNext);
			} else {
				sidebarHome.appendChild(sidebar);
			}
		}

		if (expand.parentElement !== document.body) {
			document.body.appendChild(expand);
		}

		var backdrop = qs('[data-mns-sidebar-backdrop]');
		if (!backdrop) {
			backdrop = document.createElement('div');
			backdrop.className = 'mns-dash__backdrop';
			backdrop.setAttribute('data-mns-sidebar-backdrop', '');
			backdrop.setAttribute('aria-hidden', 'true');
			document.body.appendChild(backdrop);
		} else if (backdrop.parentElement !== document.body) {
			document.body.appendChild(backdrop);
		}
		backdrop.hidden = false;

		var root = document.documentElement;
		var gutterSupported = !!(window.CSS && CSS.supports && CSS.supports('scrollbar-gutter', 'stable'));

		function isOpen() {
			return sidebar.classList.contains('mobile-expanded');
		}

		function lockScroll(lock) {
			if (!lock) {
				root.classList.remove('mns-drawer-open');
				document.body.style.removeProperty('padding-inline-end');
				return;
			}
			var scrollbar = window.innerWidth - root.clientWidth;
			root.classList.add('mns-drawer-open');
			if (!gutterSupported && scrollbar > 0) {
				document.body.style.setProperty('padding-inline-end', scrollbar + 'px');
			}
		}

		function openSidebar() {
			if (isOpen()) return;
			sidebar.classList.add('mobile-expanded');
			backdrop.classList.add('is-visible');
			lockScroll(true);
			expand.setAttribute('aria-expanded', 'true');
			if (closeBtn) closeBtn.focus({ preventScroll: true });
		}

		function closeSidebar() {
			if (!isOpen()) return;
			sidebar.classList.remove('mobile-expanded');
			backdrop.classList.remove('is-visible');
			lockScroll(false);
			expand.setAttribute('aria-expanded', 'false');
			if (sidebar.contains(document.activeElement)) {
				expand.focus({ preventScroll: true });
			}
		}

		expand.removeAttribute('hidden');
		expand.setAttribute('aria-expanded', 'false');
		expand.addEventListener('click', openSidebar);
		backdrop.addEventListener('click', closeSidebar);
		if (closeBtn) {
			closeBtn.addEventListener('click', closeSidebar);
		}
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeSidebar();
		});
		if (window.matchMedia) {
			var mobile = window.matchMedia('(max-width: 767px)');
			placeSidebarForViewport(mobile.matches);
			var onChange = function (e) {
				placeSidebarForViewport(e.matches);
				if (!e.matches) closeSidebar();
			};
			if (mobile.addEventListener) {
				mobile.addEventListener('change', onChange);
			} else if (mobile.addListener) {
				mobile.addListener(onChange);
			}
		}
	}

	function normalizeIranMobile(value) {
		var digits = String(value || '')
			.replace(/[۰-۹]/g, function (d) {
				return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
			})
			.replace(/[٠-٩]/g, function (d) {
				return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
			})
			.replace(/\D/g, '');
		if (digits.indexOf('0098') === 0) digits = '0' + digits.slice(4);
		else if (digits.indexOf('98') === 0) digits = '0' + digits.slice(2);
		else if (digits.length && digits.charAt(0) !== '0') digits = '0' + digits;
		return digits.slice(0, 11);
	}

	function initIranMobile() {
		qsa('input[data-mns-iran-mobile]').forEach(function (input) {
			input.setAttribute('maxlength', '20');
			input.setAttribute('dir', 'ltr');
			input.setAttribute('inputmode', 'tel');
			input.addEventListener('input', function () {
				var next = normalizeIranMobile(input.value);
				if (input.value !== next) input.value = next;
			});
		});
	}

	function closeAllSelects(except) {
		qsa('.mns-select__menu.is-open').forEach(function (menu) {
			if (except && menu === except) return;
			menu.classList.remove('is-open');
			var wrap = menu.closest('.mns-field-block__control--select');
			if (wrap) wrap.classList.remove('is-open');
			var trigger = menu.parentElement && menu.parentElement.querySelector('.mns-select__trigger');
			if (trigger) trigger.setAttribute('aria-expanded', 'false');
		});
	}

	function initCustomSelects() {
		qsa('select[data-mns-select]').forEach(function (native) {
			if (native.dataset.mnsEnhanced === '1') return;
			native.dataset.mnsEnhanced = '1';
			var control = native.closest('.mns-field-block__control--select');
			if (!control) return;

			var shell = document.createElement('div');
			shell.className = 'mns-select';
			var trigger = document.createElement('button');
			trigger.type = 'button';
			trigger.className = 'mns-select__trigger';
			trigger.setAttribute('aria-haspopup', 'listbox');
			trigger.setAttribute('aria-expanded', 'false');
			var menu = document.createElement('ul');
			menu.className = 'mns-select__menu';
			menu.setAttribute('role', 'listbox');

			function syncLabel() {
				var selected = native.options[native.selectedIndex];
				var text = selected ? selected.textContent.trim() : '';
				trigger.textContent = text || 'انتخاب کنید';
				trigger.classList.toggle('is-placeholder', !native.value);
				trigger.disabled = native.disabled;
				control.classList.toggle('is-disabled', native.disabled);
				control.classList.toggle('is-filled', !!native.value);
				/* Disabled custom selects must stay closed; a leftover is-open + visible native option text looks broken. */
				if (native.disabled && menu.classList.contains('is-open')) {
					menu.classList.remove('is-open');
					control.classList.remove('is-open');
					trigger.setAttribute('aria-expanded', 'false');
				}
			}

			function buildOptions() {
				menu.innerHTML = '';
				/* a long list (cities) gets a search box on top */
				if (native.options.length > 14) {
					var sli = document.createElement('li');
					sli.className = 'mns-select__search';
					var sbox = document.createElement('input');
					sbox.type = 'search';
					sbox.className = 'mns-select__searchbox';
					sbox.placeholder = 'جستجو';
					sbox.autocomplete = 'off';
					sbox.addEventListener('input', function () {
						var norm = function (x) {
							return x.replace(/\u064a/g, '\u06cc').replace(/\u0643/g, '\u06a9').replace(/[\u200c\s]+/g, '');
						};
						var q = norm(sbox.value);
						qsa('.mns-select__option', menu).forEach(function (li) {
							li.hidden = !!q && !!li.dataset.value && norm(li.textContent).indexOf(q) === -1;
						});
					});
					sli.appendChild(sbox);
					menu.appendChild(sli);
				}
				Array.prototype.forEach.call(native.options, function (opt, index) {
					var li = document.createElement('li');
					li.className = 'mns-select__option';
					li.setAttribute('role', 'option');
					li.dataset.value = opt.value;
					li.textContent = opt.textContent;
					if (opt.disabled) li.classList.add('is-disabled');
					if (opt.selected) li.classList.add('is-selected');
					li.addEventListener('click', function (e) {
						/* Keep this click from reaching the control's "click anywhere to open" handler, otherwise a rebuilt option list re-opens the menu right after a pick. */
						e.stopPropagation();
						/* The control sits inside a <label>: without this the browser forwards the click to the native <select>, which re-opens the menu. */
						e.preventDefault();
						if (opt.disabled) return;
						native.selectedIndex = index;
						native.dispatchEvent(new Event('change', { bubbles: true }));
						syncLabel();
						closeAllSelects();
					});
					menu.appendChild(li);
				});
			}

			buildOptions();
			syncLabel();
			shell.appendChild(trigger);
			control.appendChild(shell);
			control.appendChild(menu);

			function open() {
				if (native.disabled || trigger.disabled) return;
				closeAllSelects(menu);
				buildOptions();
				menu.classList.add('is-open');
				control.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
				var sb = menu.querySelector('.mns-select__searchbox');
				if (sb) sb.focus({ preventScroll: true });
			}

			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				if (native.disabled || trigger.disabled) return;
				if (menu.classList.contains('is-open')) closeAllSelects();
				else open();
			});
			control.addEventListener('click', function (e) {
				if (native.disabled || trigger.disabled) return;
				if (e.target === trigger || trigger.contains(e.target)) return;
				if (e.target === native || menu.contains(e.target)) return;
				if (!menu.classList.contains('is-open')) open();
			});
			native.addEventListener('change', syncLabel);
		});

		document.addEventListener('click', function (e) {
			if (!e.target.closest('.mns-field-block__control--select')) closeAllSelects();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeAllSelects();
		});
	}

	function initMockForms() {
		qsa('form[data-mns-mock]').forEach(function (form) {
			if (form.hasAttribute('data-mns-order-form')) return;
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var notice = form.querySelector('[data-mns-notice]') || qs('.mns-form-notice', form.parentElement);
				form.classList.add('is-busy');
				form.setAttribute('aria-busy', 'true');
				var btn = form.querySelector('[type="submit"]');
				if (btn) btn.disabled = true;
				window.setTimeout(function () {
					form.classList.remove('is-busy');
					form.setAttribute('aria-busy', 'false');
					if (btn) btn.disabled = false;
					var mode = form.getAttribute('data-mns-mock') || 'ok';
					var msg =
						mode === 'no-nurse'
							? 'برای این زمان و منطقه پرستاری در دسترس نیست. زمان دیگری انتخاب کنید یا با پشتیبانی تماس بگیرید.'
							: form.getAttribute('data-mns-success') || 'با موفقیت ثبت شد.';
					if (notice) {
						notice.hidden = false;
						notice.className =
							'mns-notice mns-form-notice' + (mode === 'no-nurse' || mode === 'error' ? ' mns-notice--error' : '');
						notice.setAttribute('role', mode === 'no-nurse' || mode === 'error' ? 'alert' : 'status');
						notice.textContent = msg;
						notice.focus({ preventScroll: true });
					}
				}, 700);
			});
		});
	}

	function initSwitches() {
		qsa('[data-mns-switch]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var on = !btn.classList.contains('is-on');
				btn.classList.toggle('is-on', on);
				btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		});
	}

	function initAvailabilityCalendar() {
		var root = qs('[data-mns-availability-calendar]');
		if (!root) return;
		var titleEl = qs('[data-mns-cal-title]', root);
		var grid = qs('[data-mns-cal-grid]', root);
		var detail = qs('[data-mns-cal-detail]');
		var monthOffset = 0;
		var selected = null;
		var blocked = {};
		try {
			blocked = JSON.parse(root.getAttribute('data-blocked') || '{}') || {};
		} catch (e) {
			blocked = {};
		}

		var jMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

		function render() {
			// Approximate Jalali display using Gregorian calendar grid for static UI demo.
			var now = new Date();
			var base = new Date(now.getFullYear(), now.getMonth() + monthOffset, 1);
			var year = base.getFullYear();
			var month = base.getMonth();
			var labelMonth = ((month + 9) % 12); // visual shift toward common Jalali season labels for demo
			if (titleEl) titleEl.textContent = jMonths[labelMonth] + ' / ' + toFaDigits(String(1400 + (year - 2021)));
			var firstDow = (base.getDay() + 1) % 7; // Saturday-start-ish for IR
			var daysInMonth = new Date(year, month + 1, 0).getDate();
			grid.innerHTML = '';
			var i;
			for (i = 0; i < firstDow; i++) {
				var empty = document.createElement('button');
				empty.type = 'button';
				empty.className = 'mns-availability-calendar__day is-empty';
				empty.tabIndex = -1;
				grid.appendChild(empty);
			}
			for (i = 1; i <= daysInMonth; i++) {
				var key = year + '-' + (month + 1) + '-' + i;
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'mns-availability-calendar__day';
				btn.textContent = toFaDigits(String(i));
				var dow = new Date(year, month, i).getDay();
				if (dow === 5) btn.classList.add('is-friday');
				if (now.getFullYear() === year && now.getMonth() === month && now.getDate() === i) btn.classList.add('is-today');
				if (blocked[key]) btn.classList.add('is-blocked');
				if (selected === key) btn.classList.add('is-selected');
				btn.addEventListener('click', function (dayKey, dayBtn) {
					return function () {
						selected = dayKey;
						render();
						if (detail) {
							detail.hidden = false;
							var isBlocked = !!blocked[dayKey];
							detail.innerHTML =
								'<p class="mns-dash__lead" style="margin:0 0 12px">روز انتخاب‌شده: <strong dir="ltr">' +
								dayKey +
								'</strong></p>' +
								'<button type="button" class="mns-btn-outline" data-mns-toggle-block>' +
								(isBlocked ? 'رفع مسدودی روز' : 'لغو / مسدود کردن این روز') +
								'</button>';
							var t = detail.querySelector('[data-mns-toggle-block]');
							if (t) {
								t.addEventListener('click', function () {
									if (blocked[dayKey]) delete blocked[dayKey];
									else blocked[dayKey] = 1;
									selected = dayKey;
									render();
									var reopen = qs('[data-mns-cal-grid] .is-selected', root);
									if (reopen) reopen.click();
								});
							}
						}
					};
				}(key, btn));
				grid.appendChild(btn);
			}
		}

		var prev = qs('[data-mns-cal-prev]', root);
		var next = qs('[data-mns-cal-next]', root);
		if (prev) prev.addEventListener('click', function () { monthOffset -= 1; render(); });
		if (next) next.addEventListener('click', function () { monthOffset += 1; render(); });
		render();
	}

	/* Survey form: all three ratings are required; say which one is missing instead of sending zeros. */
	function initSurveyForm() {
		qsa('form[data-mns-survey-form]').forEach(function (form) {
			var label = qs('[data-mns-submit-label]', form);
			var banner = qs('[data-mns-neg]', form);
			function open(el, on) {
				if (!el) return;
				el.classList.toggle('is-open', on);
				if ('inert' in el) el.inert = !on;
			}
			function isNegative(axisName) {
				var v = qs('[data-mns-star-value="' + axisName + '"]', form);
				var n = v ? parseInt(v.value, 10) || 0 : 0;
				return n > 0 && n <= 2;
			}
			/* A score of 1 or 2 opens "what went wrong" under that axis and turns the whole answer into a complaint. */
			function refresh() {
				var any = false;
				qsa('[data-mns-why]', form).forEach(function (panel) {
					var neg = isNegative(panel.getAttribute('data-mns-why'));
					any = any || neg;
					open(panel, neg);
					if (!neg) {
						qsa('input', panel).forEach(function (inp) {
							if (inp.type === 'checkbox') inp.checked = false;
							else inp.value = '';
						});
						var box = qs('.mns-sv__why-box', panel);
						if (box) box.classList.remove('is-missing');
					}
				});
				open(banner, any);
				form.classList.toggle('is-negative', any);
				if (label) label.textContent = label.getAttribute(any ? 'data-negative' : 'data-default') || label.textContent;
			}
			form.addEventListener('mns-rated', refresh);

			form.addEventListener(
				'submit',
				function (e) {
					var missing = qsa('.mns-sv__axis', form).filter(function (axis) {
						var v = qs('[data-mns-star-value]', axis);
						return !v || v.value === '0';
					});
					qsa('.mns-sv__axis', form).forEach(function (axis) {
						axis.classList.toggle('is-missing', missing.indexOf(axis) !== -1);
					});
					if (missing.length) {
						e.preventDefault();
						e.stopImmediatePropagation();
						showNoticeNear(form, 'به هر سه بخش امتیاز بدهید.', true);
						missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
						return;
					}
					var lacking = qsa('[data-mns-why]', form).filter(function (panel) {
						if (!panel.classList.contains('is-open')) return false;
						var picked = qsa('input[type="checkbox"]', panel).some(function (c) {
							return c.checked;
						});
						var text = (qs('input[type="text"], input:not([type])', panel) || {}).value || '';
						var ok = picked || text.trim() !== '';
						var box = qs('.mns-sv__why-box', panel);
						if (box) box.classList.toggle('is-missing', !ok);
						return !ok;
					});
					if (lacking.length) {
						e.preventDefault();
						e.stopImmediatePropagation();
						showNoticeNear(form, 'برای امتیاز پایین، علت را مشخص کنید.', true);
						lacking[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
						return;
					}
					if (form.classList.contains('is-negative')) {
						form.setAttribute('data-mns-success', form.getAttribute('data-mns-success-negative') || '');
						var url = form.getAttribute('data-mns-complaint-url');
						if (url) {
							window.setTimeout(function () {
								window.location.href = url;
							}, 1800);
						}
					}
				},
				true
			);
			form.addEventListener('change', function (e) {
				if (e.target && e.target.closest && e.target.closest('.mns-sv__why-box')) {
					e.target.closest('.mns-sv__why-box').classList.remove('is-missing');
				}
			});
		});
	}

	function initStars() {
		qsa('[data-mns-stars]').forEach(function (group) {
			qsa('button', group).forEach(function (btn, index) {
				btn.addEventListener('click', function () {
					qsa('button', group).forEach(function (b, i) {
						b.classList.toggle('is-on', i <= index);
					});
					group.setAttribute('data-value', String(index + 1));
					qsa('button', group).forEach(function (b, i) {
						b.setAttribute('aria-checked', i === index ? 'true' : 'false');
					});
					var axis = group.closest('.mns-sv__axis');
					if (axis) {
						axis.classList.remove('is-missing');
						var word = qs('[data-mns-star-word]', axis);
						if (word) word.textContent = btn.getAttribute('data-word') || '';
					}
					var name = group.getAttribute('data-name');
					if (name) {
						var hidden = qs('[data-mns-star-value="' + name + '"]');
						if (hidden) hidden.value = String(index + 1);
					}
					/* after the hidden value is stored, so listeners read the new score */
					group.dispatchEvent(new CustomEvent('mns-rated', { bubbles: true }));
				});
			});
		});
	}

	function initRowToggles() {
		document.addEventListener('click', function (e) {
			var row = e.target.closest('.mns-table--expandable .mns-table__row');
			if (!row) return;
			var btn = row.querySelector('.mns-row-toggle');
			if (!btn) return;
			if (!e.target.closest('.mns-row-toggle') && e.target.closest('a, button, input')) return;
			var details = document.getElementById(btn.getAttribute('aria-controls'));
			if (!details) return;
			var open = btn.getAttribute('aria-expanded') !== 'true';
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			row.classList.toggle('is-open', open);
			details.hidden = !open;
		});
	}

	function showNoticeNear(el, msg, isError) {
		var root = el.closest('.mns-dash__main') || document;
		var notice = qs('[data-mns-notice]', root) || qs('.mns-form-notice', root);
		if (!notice) return;
		notice.hidden = false;
		notice.className = 'mns-notice mns-form-notice' + (isError ? ' mns-notice--error' : '');
		notice.setAttribute('role', isError ? 'alert' : 'status');
		notice.textContent = msg;
		notice.focus({ preventScroll: true });
	}

	function initOrderForm() {
		var form = qs('form[data-mns-order-form]');
		if (!form) return;

		var wizard = qs('[data-mns-owizard]');
		var step = 1;
		var maxStep = 3;
		var availState = 'idle';
		var availTimer = null;
		var dateSlots = [];
		var timeSlots = [];
		var locations = null;
		var districtMap = {};
		var availability = {};
		var dateLabel = '';
		var customDateLabel = '';

		try {
			districtMap = JSON.parse(form.getAttribute('data-mns-districts') || '{}') || {};
		} catch (e1) {
			districtMap = {};
		}
		try {
			dateSlots = JSON.parse(form.getAttribute('data-mns-date-slots') || '[]') || [];
		} catch (e2) {
			dateSlots = [];
		}
		try {
			timeSlots = JSON.parse(form.getAttribute('data-mns-time-slots') || '[]') || [];
		} catch (e3) {
			timeSlots = [];
		}
		try {
			availability = JSON.parse(form.getAttribute('data-mns-availability') || '{}') || {};
		} catch (e4) {
			availability = {};
		}

		var svcLabel = form.getAttribute('data-mns-svc-label') || 'سرم‌تراپی در منزل';
		var modeInput = qs('[data-mns-patient-mode]', form);
		var patientIdInput = qs('[data-mns-patient-id]', form);
		var genderInput = qs('[data-mns-nurse-gender]', form);
		var citySelect = qs('[data-mns-city]', form);
		var areaSelect = qs('[data-mns-area]', form);
		var provSelect = qs('[data-mns-prov]', form);
		var areaWrap = qs('[data-mns-area-wrap]', form);
		var dateValue = qs('[data-mns-date-value]', form);
		var timeValue = qs('[data-mns-time-value]', form);
		var serumTypeInput = qs('[data-mns-serum-type]', form);
		var veinInput = qs('[data-mns-vein-access]', form);
		var allergyInput = qs('[data-mns-allergy]', form);
		var detailsEl = qs('[data-mns-details]', form);
		var detailsToggle = qs('[data-mns-details-toggle]', form);
		var allergyCollapse = qs('[data-mns-allergy-collapse]', form);
		var allergyDetail = qs('[data-mns-allergy-detail]', form);
		var rxInput = qs('[data-mns-prescription-file]', form);
		var phoneInput = qs('input[name="phone"]', form);
		var rxTypes = [];
		try {
			rxTypes = JSON.parse(form.getAttribute('data-mns-rx-types') || '[]') || [];
		} catch (e5) {
			rxTypes = [];
		}
		var verifiedPhone = normalizeIranMobile(form.getAttribute('data-mns-verified-phone') || '');
		var qtyInput = qs('[data-mns-serum-qty]', form);
		var ageGroupInput = qs('[data-mns-age-group]', form);
		var patGenderInput = qs('[data-mns-patient-gender]', form);
		var step1Next = qs('[data-mns-step-1-next]', form);
		var dateInput = qs('[data-mns-date-input]', form);
		var dateHint = qs('[data-mns-date-hint]', form);
		var availGenderBox = qs('[data-mns-avail-gender]', form);
		var pickEdit = qs('[data-mns-pick-edit]', form);
		var pickChange = qs('[data-mns-pick-change]', form);
		var wizardReady = false;

		function setChipGroup(rootSel, value, hiddenInput) {
			var root = qs(rootSel, form);
			if (!root) return;
			qsa('[data-value]', root).forEach(function (btn) {
				var on = btn.getAttribute('data-value') === value;
				btn.classList.toggle('is-selected', on);
				btn.setAttribute('aria-checked', on ? 'true' : 'false');
			});
			if (hiddenInput) hiddenInput.value = value || '';
		}

		function timeLabelById(id) {
			var hit = timeSlots.filter(function (t) {
				return String(t.id) === String(id);
			})[0];
			return hit ? hit.label : id;
		}

		function genderNurseLabel(val) {
			return val === 'female' ? 'پرستار خانم' : val === 'male' ? 'پرستار آقا' : '—';
		}

		function ageGroupFa(val) {
			var map = { child: 'کودک', adult: 'بزرگسال', elder: 'سالمند' };
			return map[val] || '';
		}

		function patientGenderFa(val) {
			return val === 'female' ? 'خانم' : val === 'male' ? 'آقا' : '';
		}

		function patientSummary() {
			var mode = modeInput ? modeInput.value : 'self';
			if (mode === 'self') return 'خودم';
			if (mode === 'saved') {
				var pid = patientIdInput ? patientIdInput.value : '';
				var card = qs('[data-mns-patient-card="saved"][data-value="' + pid + '"]', form);
				return card ? (card.querySelector('strong') || {}).textContent || '—' : '—';
			}
			var parts = ['بیمار جدید'];
			var ag = ageGroupInput && ageGroupInput.value ? ageGroupFa(ageGroupInput.value) : '';
			var g = patGenderInput && patGenderInput.value ? patientGenderFa(patGenderInput.value) : '';
			if (ag) parts.push(ag);
			if (g) parts.push(g);
			var nm = qs('input[name="new_patient_name"]', form);
			if (nm && nm.value) parts[0] = nm.value.trim();
			return parts.join('، ');
		}

		function serviceDetailSummary() {
			var qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
			var type = serumTypeInput && serumTypeInput.value ? serumTypeInput.value : '—';
			return toFaDigits(String(qty)) + ' سرم ' + type;
		}

		function whenSummary() {
			if (customDateLabel) return customDateLabel + (timeValue && timeValue.value ? '، ساعت ' + timeLabelById(timeValue.value) : '');
			if (dateLabel) return dateLabel + (timeValue && timeValue.value ? '، ساعت ' + timeLabelById(timeValue.value) : '');
			return '—';
		}

		function updateSideSummary() {
			var elPatient = qs('[data-mns-sum-patient]');
			var elTime = qs('[data-mns-sum-time]');
			var elPlace = qs('[data-mns-sum-place]');
			var elNurse = qs('[data-mns-sum-nurse]');
			var elDetail = qs('[data-mns-sum-detail]');
			var elDetailRow = qs('[data-mns-sum-detail-row]');
			var factors = qs('[data-mns-sum-price-factors]');
			if (elPatient) elPatient.textContent = patientSummary();
			if (elTime) elTime.textContent = dateValue && dateValue.value && timeValue && timeValue.value ? whenSummary() : 'در مرحله بعد';
			if (elPlace) elPlace.textContent = placeComplete() ? placeLabel() : 'در مرحله بعد';
			if (elNurse) elNurse.textContent = genderInput && genderInput.value ? genderNurseLabel(genderInput.value) : 'در مرحله بعد';
			/* Journey nodes: a stop lights up once its answer is known. */
			[elPatient, elTime, elPlace, elNurse].forEach(function (el) {
				var li = el && el.closest('li');
				if (li) li.classList.toggle('is-filled', el.textContent.trim() !== 'در مرحله بعد' && el.textContent.trim() !== '');
			});
			if (elDetail && elDetailRow) {
				var showDetail = step >= 2 && serumTypeInput && serumTypeInput.value;
				if (showDetail) elDetail.textContent = serviceDetailSummary();
				elDetailRow.hidden = !showDetail;
				elDetailRow.classList.toggle('is-hidden', !showDetail);
			}
			if (factors) {
				factors.hidden = step < 2;
				factors.classList.toggle('is-hidden', step < 2);
			}
			var otpPhone = qs('[data-mns-otp-phone]', form);
			var phone = qs('input[name="phone"]', form);
			if (otpPhone && phone) otpPhone.textContent = phone.value || '';
		}

		function goStep(n, focusSel) {
			step = Math.max(1, Math.min(maxStep, n));
			qsa('[data-mns-step]', form).forEach(function (sec) {
				var id = parseInt(sec.getAttribute('data-mns-step'), 10);
				var active = id === step;
				sec.hidden = !active;
				sec.classList.toggle('is-hidden', !active);
				sec.classList.toggle('is-active', active);
				if (!active) sec.setAttribute('aria-hidden', 'true');
				else sec.removeAttribute('aria-hidden');
			});
			var currentLabel = '';
			qsa('[data-mns-step-goto]', wizard || document).forEach(function (btn) {
				var id = parseInt(btn.getAttribute('data-mns-step-goto'), 10);
				btn.classList.toggle('is-current', id === step);
				btn.classList.toggle('is-done', id < step);
				btn.disabled = id > step;
				btn.setAttribute('aria-disabled', id > step ? 'true' : 'false');
				if (id === step) {
					btn.setAttribute('aria-current', 'step');
					currentLabel = btn.getAttribute('data-mns-step-label') || '';
				} else {
					btn.removeAttribute('aria-current');
				}
			});
			setWizardStep(step);
			updateSideSummary();
			if (step === 2) syncRxTag();
			if (step === 3) fillReview();
			syncMobileBar();
			if (wizardReady) {
				var target = focusSel ? qs(focusSel, form) : null;
				var anchor = target || qs('[data-mns-stepper]', wizard || document) || qs('[data-mns-step].is-active', form);
				if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: target ? 'center' : 'start' });
				if (target) {
					/* "Edit" in the review lands on the exact field, not at the top of the step. */
					var focusable = target.matches('button, input, select, textarea') ? target : qs('button:not([disabled]), input, select, textarea', target);
					if (focusable) focusable.focus({ preventScroll: true });
				}
			}
		}

		function setWizardStep(n) {
			if (!wizard) return;
			wizard.setAttribute('data-mns-current-step', String(n));
			var leadEl = qs('[data-mns-ow-lead]', wizard);
			var leads = {};
			try {
				leads = JSON.parse(wizard.getAttribute('data-mns-leads') || '{}') || {};
			} catch (eLead) {
				leads = {};
			}
			var leadKey = n === 4 ? 'done' : n;
			if (leadEl && leads[leadKey]) leadEl.textContent = leads[leadKey];
		}

		function syncMobileBar() {
			var mobBtn = qs('[data-mns-mob-continue]');
			if (!mobBtn) return;
			var label = qs('[data-mns-mob-label]', mobBtn);
			var svc = qs('[data-mns-mob-svc]');
			var back = qs('[data-mns-mob-back]');
			if (back) back.hidden = step <= 1;
			if (step >= maxStep) {
				if (label) label.textContent = 'ثبت درخواست';
				mobBtn.setAttribute('data-mns-mob-mode', 'submit');
				if (svc) svc.textContent = 'بازه قیمت تقریبی';
			} else {
				if (label) label.textContent = 'ادامه';
				mobBtn.setAttribute('data-mns-mob-mode', 'next');
				if (svc) svc.textContent = svcLabel;
			}
		}

		function hideNotice() {
			var notice = qs('[data-mns-notice]', form);
			if (notice) notice.hidden = true;
		}

		/* Field-level feedback: mark the field, say what is wrong right under it, scroll to it. */
		function clearFlags() {
			qsa('.is-invalid, .is-flagged', form).forEach(function (el) {
				el.classList.remove('is-invalid', 'is-flagged');
			});
			qsa('.mns-field-error', form).forEach(function (el) {
				el.remove();
			});
		}

		function fail(msg, target) {
			if (!target) {
				showNoticeNear(form, msg, true);
				return false;
			}
			var host = target.closest('.mns-field-block') || target;
			host.classList.add(host === target ? 'is-flagged' : 'is-invalid');
			var err = document.createElement('p');
			err.className = 'mns-field-error';
			err.setAttribute('role', 'alert');
			err.textContent = msg;
			if (host.parentElement && /mns-form-row--2/.test(host.parentElement.className)) host.appendChild(err);
			else host.insertAdjacentElement('afterend', err);
			host.scrollIntoView({ behavior: 'smooth', block: 'center' });
			if (target.matches('input, textarea')) target.focus({ preventScroll: true });
			return false;
		}

		function failPick(msg, target) {
			setPickOpen(true); /* the field the message talks about may be folded away */
			return fail(msg, target);
		}

		function validateStep(n) {
			hideNotice();
			clearFlags();
			if (n === 1) {
				var mode = modeInput ? modeInput.value : 'self';
				if (mode === 'new') {
					var nm = qs('input[name="new_patient_name"]', form);
					if (!nm || !nm.value.trim()) return failPick('نام بیمار را وارد کنید.', nm);
					if (!ageGroupInput || !ageGroupInput.value) return failPick('گروه سنی را انتخاب کنید.', qs('[data-mns-age-chips]', form));
					if (!patGenderInput || !patGenderInput.value) return failPick('جنسیت بیمار را انتخاب کنید.', qs('[data-mns-patient-gender-chips]', form));
				}
				if (mode === 'saved' && (!patientIdInput || !patientIdInput.value)) return failPick('بیمار را انتخاب کنید.', qs('[data-mns-patient-cards]', form));
				var phone = qs('input[name="phone"]', form);
				if (!phone || !phone.value || phone.value.replace(/\s/g, '').length < 11) return failPick('شماره تماس معتبر وارد کنید.', phone);
				if (availState === 'red') {
					if (availGenderBox) availGenderBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
					return false;
				}
				if (!provSelect || !provSelect.value) return fail('استان را انتخاب کنید.', provSelect);
				if (!citySelect || !citySelect.value) return fail('شهر را انتخاب کنید.', citySelect);
				if (areaNeeded() && !areaSelect.value) return fail('منطقه را انتخاب کنید.', areaSelect);
				if (!dateValue || !dateValue.value) return fail('تاریخ مراجعه را انتخاب کنید.', dateInput);
				if (!timeValue || !timeValue.value) return fail(timeValue && timeValue.options.length <= 1 ? 'برای این روز زمانی موجود نیست؛ روز دیگری انتخاب کنید.' : 'بازه زمانی را انتخاب کنید.', timeValue);
				if (!genderInput || !genderInput.value) return fail('جنسیت پرستار را انتخاب کنید.', qs('[data-mns-nurse-gender-cards]', form));
				var addr = qs('textarea[name="address"]', form) || qs('input[name="address"]', form);
				if (!addr || !addr.value.trim()) return fail('آدرس را کامل‌تر بنویسید.', addr);
			}
			if (n === 2) return validateReview();
			return true;
		}

		/* ---- Review step: service details (inside the review), repeat, phone verification ----------------- */
		function isRxRequired() {
			return !!(serumTypeInput && rxTypes.indexOf(serumTypeInput.value) !== -1);
		}

		function hasRx() {
			return !!(rxInput && rxInput.files && rxInput.files.length);
		}

		/* Service details are their own step now; kept as a no-op for the validators below. */
		function setDetailsOpen() {}

		function syncAllergy() {
			var yes = !!(allergyInput && allergyInput.value === 'yes');
			setCollapse(allergyCollapse, yes);
			if (!yes && allergyDetail) allergyDetail.value = '';
		}

		function syncRxTag() {
			var tag = qs('[data-mns-rx-tag]', form);
			if (!tag) return;
			var req = isRxRequired();
			tag.textContent = req ? 'برای این نوع سرم لازم است' : 'اختیاری';
			tag.classList.toggle('is-required', req);
		}

		function chipLabel(rootSel, value) {
			var hit = qsa('[data-value]', qs(rootSel, form) || document).filter(function (b) {
				return b.getAttribute('data-value') === value;
			})[0];
			return hit ? hit.textContent.trim() : '';
		}

		function stepperInput(name) {
			return name === 'serum' ? qtyInput : qs('[data-mns-rep-count]', form);
		}

		function setStepper(name, val) {
			var root = qs('[data-mns-stepper="' + name + '"]', form);
			if (!root) return;
			var min = parseInt(root.getAttribute('data-min'), 10) || 1;
			var max = parseInt(root.getAttribute('data-max'), 10) || 9;
			val = Math.max(min, Math.min(max, val));
			var disp = qs('[data-step-val]', root);
			if (disp) disp.textContent = toFaDigits(val);
			var hidden = stepperInput(name);
			if (hidden) hidden.value = String(val);
			qs('[data-step="plus"]', root).disabled = val >= max;
			qs('[data-step="minus"]', root).disabled = val <= min;
			updateSideSummary();
			if (step === 3) fillReview();
		}

		function fmtMoney(n) {
			try {
				return new Intl.NumberFormat('fa-IR').format(n);
			} catch (eFmt) {
				return String(n);
			}
		}

		function faDate(d) {
			try {
				return new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(d);
			} catch (eDate) {
				return d.toISOString().slice(0, 10);
			}
		}

		function updateRecurring() {
			var line = qs('[data-mns-rep-line]', form);
			var dates = qs('[data-mns-rep-dates]', form);
			var cost = qs('[data-mns-rep-cost]', form);
			var pattern = qs('[data-mns-rep-pattern]', form);
			if (!line || !dates || !cost) return;
			var count = parseInt((stepperInput('repeat') || {}).value, 10) || 2;
			var every = pattern && pattern.value === 'daily' ? 1 : pattern && pattern.value === 'every_other' ? 2 : 7;
			var base = dateValue ? dateValue.value : '';
			var list = [];
			if (/^\d{4}-\d{2}-\d{2}$/.test(base)) {
				var p = base.split('-');
				for (var i = 0; i < count; i++) list.push(new Date(Date.UTC(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10) + i * every, 12)));
			}
			var when = timeValue && timeValue.value ? '، همه ساعت ' + timeLabelById(timeValue.value) : '';
			line.textContent = toFaDigits(count) + ' بار (با همین سفارش)' + when;
			dates.innerHTML = '';
			list.slice(0, 6).forEach(function (d, i) {
				var chip = document.createElement('span');
				chip.className = 'mns-recurring__date' + (i === 0 ? ' is-first' : '');
				chip.textContent = faDate(d);
				dates.appendChild(chip);
			});
			if (list.length > 6) {
				var more = document.createElement('span');
				more.className = 'mns-recurring__more';
				more.textContent = '+' + toFaDigits(list.length - 6) + ' بار دیگر';
				dates.appendChild(more);
			}
			var min = parseInt(form.getAttribute('data-mns-price-min'), 10) || 0;
			var max = parseInt(form.getAttribute('data-mns-price-max'), 10) || 0;
			cost.textContent = min && max
				? 'هزینهٔ تقریبی هر بار ' + fmtMoney(min) + ' تا ' + fmtMoney(max) + ' · جمع ' + toFaDigits(count) + ' بار حدود ' + fmtMoney(min * count) + ' تا ' + fmtMoney(max * count) + ' تومان · پرداخت بعد از هر بار'
				: '';
		}

		/* Phone verification: only a phone that differs from the verified account number needs a code (mock). */
		var otpState = { sent: false, phone: '', timer: null, left: 0 };

		function phoneChanged() {
			var cur = normalizeIranMobile(phoneInput ? phoneInput.value : '');
			return !!(cur && verifiedPhone && cur !== verifiedPhone);
		}

		function otpDigits() {
			return qsa('[data-mns-otp-digit]', form);
		}

		function otpValue() {
			return otpDigits()
				.map(function (el) {
					return toLatinDigits(el.value);
				})
				.join('');
		}

		function stopOtpTimer() {
			if (otpState.timer) window.clearInterval(otpState.timer);
			otpState.timer = null;
		}

		function resetOtp() {
			stopOtpTimer();
			otpState.sent = false;
			otpState.phone = '';
			otpDigits().forEach(function (el) {
				el.value = '';
			});
			setCollapse(qs('[data-mns-otp-code-collapse]', form), false);
			var send = qs('[data-mns-otp-send]', form);
			var timer = qs('[data-mns-otp-timer]', form);
			if (send) {
				send.disabled = false;
				send.textContent = 'ارسال کد';
			}
			if (timer) timer.textContent = '';
		}

		function otpTick() {
			var send = qs('[data-mns-otp-send]', form);
			var timer = qs('[data-mns-otp-timer]', form);
			if (otpState.left <= 0) {
				stopOtpTimer();
				if (send) {
					send.disabled = false;
					send.textContent = 'ارسال دوباره';
				}
				if (timer) timer.textContent = '';
				return;
			}
			var m = Math.floor(otpState.left / 60);
			var sec = otpState.left % 60;
			if (timer) timer.textContent = 'ارسال دوباره تا ' + toFaDigits('0' + m + ':' + ('0' + sec).slice(-2));
			otpState.left -= 1;
		}

		function updateVerify() {
			var changed = phoneChanged();
			var shown = phoneInput ? phoneInput.value : '';
			qsa('[data-mns-verify-phone], [data-mns-otp-phone]', form).forEach(function (el) {
				el.textContent = shown;
			});
			setCollapse(qs('[data-mns-verify-ok-collapse]', form), !changed);
			setCollapse(qs('[data-mns-otp-collapse]', form), changed);
			if (otpState.sent && (!changed || otpState.phone !== normalizeIranMobile(shown))) resetOtp();
		}

		function verifyReady() {
			if (!phoneChanged()) return true;
			if (!otpState.sent) return fail('ابتدا «ارسال کد» را بزنید.', qs('[data-mns-otp-send]', form));
			if (otpValue().length !== 4) return fail('کد چهار رقمی را وارد کنید.', qs('[data-mns-otp-boxes]', form));
			return true;
		}

		function validateReview() {
			if (!serumTypeInput || !serumTypeInput.value) {
				setDetailsOpen(true);
				return fail('نوع سرم را انتخاب کنید.', serumTypeInput);
			}
			if (!veinInput || !veinInput.value) {
				setDetailsOpen(true);
				return fail('دسترسی رگ را انتخاب کنید.', qs('[data-mns-vein-chips]', form));
			}
			if (!allergyInput || !allergyInput.value) {
				setDetailsOpen(true);
				return fail('سابقهٔ حساسیت را انتخاب کنید.', qs('[data-mns-allergy-chips]', form));
			}
			if (allergyInput.value === 'yes' && (!allergyDetail || !allergyDetail.value.trim())) {
				setDetailsOpen(true);
				return fail('بنویسید به چه دارو یا ماده‌ای حساسیت دارید.', allergyDetail);
			}
			if (isRxRequired() && !hasRx()) {
				setDetailsOpen(true);
				return fail('برای «' + serumTypeInput.value + '» عکس نسخه لازم است.', qs('[data-mns-upload]', form));
			}
			return true;
		}

		function reviewTags() {
			var box = qs('[data-mns-review-tags]', form);
			if (!box) return;
			box.innerHTML = '';
			function tag(text, cls) {
				var el = document.createElement('span');
				el.className = 'mns-review__tag' + (cls ? ' ' + cls : '');
				el.textContent = text;
				box.appendChild(el);
			}
			var vein = chipLabel('[data-mns-vein-chips]', veinInput ? veinInput.value : '');
			if (vein) tag('رگ ' + vein);
			var allergy = allergyInput ? allergyInput.value : '';
			if (allergy === 'yes') tag('حساسیت: ' + ((allergyDetail && allergyDetail.value.trim()) || 'مشخص نشده'), 'is-warn');
			else if (allergy === 'no') tag('بدون حساسیت دارویی', 'is-ok');
			else if (allergy) tag('سابقهٔ حساسیت: نمی‌دانم', 'is-warn');
			if (hasRx()) tag('نسخه ضمیمه شد', 'is-ok');
			else if (isRxRequired()) tag('عکس نسخه لازم است', 'is-warn');
		}

		function fillReview() {
			var box = qs('[data-mns-review]', form);
			if (!box) return;
			var phone = phoneInput ? phoneInput.value : '';
			var cityLabel = citySelect && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '—';
			var areaLabel = areaSelect && areaSelect.options[areaSelect.selectedIndex] ? areaSelect.options[areaSelect.selectedIndex].text : '—';
			var addr = (qs('textarea[name="address"]', form) || {}).value || '';
			var vals = {
				patient: patientSummary() + (phone ? ' · ⁦' + phone + '⁩' : ''),
				service: svcLabel + ' · ' + serviceDetailSummary(),
				headline: whenSummary() + ' · ' + serviceDetailSummary(),
				time: whenSummary(),
				address: placeLabel() + (addr ? '، ' + addr : ''),
				nurse: genderNurseLabel(genderInput ? genderInput.value : '')
			};
			Object.keys(vals).forEach(function (k) {
				var el = qs('[data-mns-review-val="' + k + '"]', box);
				if (el) el.textContent = vals[k];
			});
			reviewTags();
			qsa('[data-mns-review-edit]', box).forEach(function (btn) {
				if (btn.getAttribute('data-mns-bound')) return;
				btn.setAttribute('data-mns-bound', '1');
				btn.addEventListener('click', function () {
					var focusMap = {
						patient: '[data-mns-pick-change]',
						time: '[data-mns-date-hint]',
						address: 'textarea[name="address"]',
						nurse: '[data-mns-nurse-gender-cards]'
					};
					goStep(parseInt(btn.getAttribute('data-mns-review-edit'), 10) || 1, focusMap[btn.getAttribute('data-mns-review-focus')] || null);
				});
			});
			updateRecurring();
			updateVerify();
		}

/* place: province > city > (district only for cities that are divided into districts) */
		function placeCity() {
			return citySelect && citySelect.value && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '';
		}
		function areaNeeded() {
			return !!(areaWrap && !areaWrap.hidden);
		}
		/* the key the availability rules know: the district, or the city itself when it has none */
		function areaKey() {
			return areaNeeded() ? areaSelect.value || '' : placeCity();
		}
		function placeComplete() {
			return !!(provSelect && provSelect.value && citySelect && citySelect.value && (!areaNeeded() || areaSelect.value));
		}
		function placeLabel() {
			var parts = [placeCity()];
			if (areaNeeded() && areaSelect.value && areaSelect.options[areaSelect.selectedIndex]) parts.push(areaSelect.options[areaSelect.selectedIndex].text);
			return parts.join('، ');
		}
		function loadLocations(cb) {
			if (locations) {
				if (cb) cb();
				return;
			}
			var url = form.getAttribute('data-mns-locations');
			if (!url) return;
			fetch(url)
				.then(function (r) {
					if (!r.ok) throw new Error('http');
					return r.json();
				})
				.then(function (d) {
					locations = d;
					if (cb) cb();
				})
				.catch(function () {
					if (citySelect) citySelect.innerHTML = '<option value="">فهرست شهرها بارگذاری نشد؛ صفحه را دوباره باز کنید</option>';
				});
		}
		function fillCities() {
			if (!citySelect || !provSelect) return;
			var code = provSelect.value;
			citySelect.innerHTML = '<option value="">' + (code ? 'در حال بارگذاری…' : 'ابتدا استان را انتخاب کنید') + '</option>';
			citySelect.disabled = !code;
			citySelect.dispatchEvent(new Event('change'));
			if (!code) return;
			loadLocations(function () {
				if (provSelect.value !== code) return;
				var prov = locations.provinces.filter(function (p) {
					return p.code === code;
				})[0];
				citySelect.innerHTML = '<option value="">انتخاب کنید</option>';
				((prov && prov.cities) || []).forEach(function (name, i) {
					var opt = document.createElement('option');
					opt.value = prov.ids && prov.ids[i] ? prov.ids[i] : name;
					opt.textContent = name;
					citySelect.appendChild(opt);
				});
				citySelect.disabled = false;
				citySelect.dispatchEvent(new Event('change'));
			});
		}
		function fillAreas() {
			if (!areaSelect || !citySelect) return;
			var names = districtMap[(provSelect ? provSelect.value : '') + '|' + placeCity()] || null;
			areaSelect.innerHTML = '<option value="">انتخاب کنید</option>';
			(names || []).forEach(function (a) {
				var opt = document.createElement('option');
				opt.value = a;
				opt.textContent = a;
				areaSelect.appendChild(opt);
			});
			if (areaWrap) areaWrap.hidden = !names;
			areaSelect.required = !!names;
			areaSelect.dispatchEvent(new Event('change'));
			scheduleAvailability();
		}

		var AV_ICON = {
			check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
			clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
			alert: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>',
			phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>'
		};

		function escHtml(str) {
			return String(str).replace(/[&<>"']/g, function (ch) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
			});
		}

		/* ---- Date + time -------------------------------------------------------------------------------
		 * A Jalali date field (persianDatepicker, theme "mns") and a time dropdown that lists only windows
		 * that can actually be booked: not already started, and with a nurse in the chosen area. */
		var nowBase = (function () {
			var m = /^(\d{4}-\d{2}-\d{2}) (\d{2}):(\d{2})$/.exec(form.getAttribute('data-mns-now') || '');
			return m ? { iso: m[1], minutes: parseInt(m[2], 10) * 60 + parseInt(m[3], 10), at: Date.now() } : null;
		})();

		/* Minutes since midnight in the business time zone: server time at page load + time elapsed since. */
		function nowMinutes() {
			return nowBase ? nowBase.minutes + Math.floor((Date.now() - nowBase.at) / 60000) : -1;
		}

		function toLatinDigits(str) {
			return String(str)
				.replace(/[۰-۹]/g, function (d) {
					return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
				})
				.replace(/[٠-٩]/g, function (d) {
					return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
				});
		}

		function dayById(id) {
			return dateSlots.filter(function (d) {
				return d.id === id;
			})[0];
		}

		function dayByJ(value) {
			var key = toLatinDigits(value)
				.trim()
				.replace(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/, function (all, y, m, d) {
					return y + '/' + ('0' + m).slice(-2) + '/' + ('0' + d).slice(-2);
				});
			return dateSlots.filter(function (d) {
				return d.j === key;
			})[0];
		}

		function slotStart(slot) {
			return (parseInt(String(slot.id).split('-')[0], 10) || 0) * 60;
		}

		function validSlots(day, area) {
			var allowed = (availability.yellow_areas || []).indexOf(area) !== -1 ? availability.alt_slots || [] : null;
			return timeSlots.filter(function (t) {
				if (t.disabled) return false;
				if (allowed && allowed.indexOf(t.id) === -1) return false;
				if (nowBase && day && day.id === nowBase.iso && slotStart(t) <= nowMinutes()) return false;
				return true;
			});
		}

		function fillTimes(preferId) {
			if (!timeValue) return;
			var day = dayById(dateValue ? dateValue.value : '');
			var area = areaKey();
			var list = day ? validSlots(day, area) : [];
			var keep = preferId || timeValue.value;
			timeValue.innerHTML = '';
			var placeholder = document.createElement('option');
			placeholder.value = '';
			placeholder.textContent = list.length ? 'انتخاب کنید' : 'برای این روز زمانی موجود نیست';
			timeValue.appendChild(placeholder);
			list.forEach(function (t) {
				var opt = document.createElement('option');
				opt.value = t.id;
				opt.textContent = t.label + (t.name ? ' · ' + t.name : '');
				timeValue.appendChild(opt);
			});
			var pick = list.filter(function (t) {
				return t.id === keep;
			})[0] || list[0];
			timeValue.value = pick ? pick.id : '';
			timeValue.dispatchEvent(new Event('change', { bubbles: true }));
		}

		function pickerOf() {
			var $ = window.jQuery;
			return $ && $.fn && $.fn.persianDatepicker && dateInput ? $(dateInput).data('persianDatepicker') : null;
		}

		/* Point the open/closed calendar at `j` (Jalali Y/m/d) so its highlight matches the field. */
		function syncPicker(j) {
			var picker = pickerOf();
			if (!picker) return;
			picker.options.selectedDate = j;
			picker.persianDate = picker.now().parse(j);
			picker.persianDate.formatDate = picker.options.formatDate;
			picker.render();
		}

		function setDay(day, preferTime) {
			if (!day || !dateValue) return;
			dateValue.value = day.id;
			dateLabel = day.label;
			customDateLabel = '';
			if (dateInput) dateInput.value = day.j;
			if (dateHint) dateHint.textContent = day.label;
			syncPicker(day.j);
			fillTimes(preferTime);
			updateSideSummary();
		}

		function initJalaliPicker() {
			var $ = window.jQuery;
			if (!dateInput || !dateSlots.length) return;
			if (!$ || !$.fn || !$.fn.persianDatepicker) {
				dateInput.readOnly = false; /* no picker available: typed, validated date */
				return;
			}
			var arrow = function (d) {
				return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="' + d + '"/></svg>';
			};
			$(dateInput).persianDatepicker({
				theme: 'mns',
				formatDate: 'YYYY/0M/0D',
				startDate: dateSlots[0].j,
				endDate: dateSlots[dateSlots.length - 1].j,
				cellWidth: 38,
				cellHeight: 34,
				fontSize: 13,
				closeOnBlur: false,
				calendarPosition: { x: 0, y: 6 },
				prevArrow: arrow('m9 6 6 6-6 6'),
				nextArrow: arrow('m15 6-6 6 6 6'),
				onShow: function (calendar) {
					/* Right-align with the field (RTL) and keep the calendar inside the viewport. */
					var rect = dateInput.getBoundingClientRect();
					var width = calendar.outerWidth();
					var maxLeft = document.documentElement.clientWidth - width - 8;
					var left = Math.max(8, Math.min(rect.right - width, maxLeft));
					this.calendarPosition.x = left - rect.left;
				},
				onSelect: function () {
					this.selectedDate = dateInput.value;
					dateInput.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
			syncPicker(dateInput.value);
		}

		function initDateTime() {
			if (!dateSlots.length) return;
			/* Default: the first day that still has a bookable window (today, or tomorrow late in the evening). */
			var first = dateSlots.filter(function (d) {
				return validSlots(d, '').length > 0;
			})[0] || dateSlots[0];
			setDay(first);
			initJalaliPicker();
		}

		/* Gender availability: shown right under the gender cards, so the message appears where the finger is. */
		function renderGenderBlock(area, gender) {
			if (!availGenderBox) return;
			if (!area || !gender) {
				availGenderBox.hidden = true;
				return;
			}
			var supportTel = form.getAttribute('data-mns-support-tel') || 'tel:02188607015';
			availGenderBox.hidden = false;
			availGenderBox.className = 'mns-avail mns-avail--red';
			availGenderBox.innerHTML =
				'<span class="mns-avail__ico" aria-hidden="true">' + AV_ICON.alert + '</span>' +
				'<div class="mns-avail__body"><strong>برای ' + escHtml(area) + ' فعلاً ' + genderNurseLabel(gender) + ' نداریم</strong>' +
				'<p>جنسیت را فقط خود بیمار می‌تواند تغییر دهد.</p>' +
				'<div class="mns-avail__alts">' +
				'<a class="mns-avail__call" href="' + escHtml(supportTel) + '">' + AV_ICON.phone + '<span>تماس با پشتیبانی</span></a>' +
				'</div></div>';
		}

		/* Tell the user which gender has no nurse in the chosen area before they commit to it. */
		function markGenderAvailability(area) {
			var redMap = availability.red_area_gender || {};
			qsa('[data-mns-nurse-gender-cards] [data-value]', form).forEach(function (card) {
				var off = !!(area && redMap[area] && redMap[area] === card.getAttribute('data-value'));
				var note = qs('[data-mns-gender-note]', card);
				card.classList.toggle('is-unavailable', off);
				if (note) note.hidden = !off;
			});
		}

		function runAvailability() {
			var area = areaKey();
			var gender = genderInput ? genderInput.value : '';
			var redMap = availability.red_area_gender || {};
			var red = !!(area && gender && redMap[area] && redMap[area] === gender);
			markGenderAvailability(area);
			renderGenderBlock(red ? area : '', red ? gender : '');
			availState = red ? 'red' : 'idle';
			if (step1Next) step1Next.disabled = red;
		}

function scheduleAvailability() {
			if (availTimer) window.clearTimeout(availTimer);
			availTimer = window.setTimeout(runAvailability, 400);
		}

		/* Smooth expand/collapse: .mns-collapse animates grid rows 0fr <-> 1fr (see CSS); inert keeps hidden content out of tab order. */
		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}

		/* "Who is it for?": a known answer is one summary line; the button opens/closes the full list. */
		function setPickOpen(open) {
			if (!pickEdit) return;
			var wasOpen = pickEdit.classList.contains('is-open');
			setCollapse(pickEdit, open);
			if (pickChange) {
				pickChange.setAttribute('aria-expanded', open ? 'true' : 'false');
				var lbl = qs('[data-mns-pick-change-label]', pickChange);
				if (lbl) lbl.textContent = open ? 'بستن' : 'تغییر';
			}
			if (open && !wasOpen) {
				var target = qs('[data-mns-patient-card].is-selected', pickEdit) || qs('[data-mns-patient-card]', pickEdit);
				if (target) target.focus({ preventScroll: true });
			}
			if (!open && pickChange && pickEdit.contains(document.activeElement)) pickChange.focus({ preventScroll: true });
		}

function updatePick() {
			var nameEl = qs('[data-mns-pick-name]', form);
			var tagEl = qs('[data-mns-pick-tag]', form);
			var phoneEl = qs('[data-mns-pick-phone]', form);
			var phoneInput = qs('input[name="phone"]', form);
			if (phoneEl && phoneInput) phoneEl.textContent = phoneInput.value || '';
			var mode = modeInput ? modeInput.value : 'self';
			var card = qs('[data-mns-patient-card].is-selected', form);
			var name = '';
			var tag = '';
			if (mode === 'self') {
				name = card ? ((card.querySelector('.mns-patient-cards__meta') || {}).textContent || '').trim() : '';
				tag = name ? 'خودم' : '';
				if (!name) name = 'خودم';
			} else if (mode === 'saved') {
				name = card ? ((card.querySelector('strong') || {}).textContent || '').trim() : '';
			}
			if (nameEl && name) nameEl.textContent = name;
			if (tagEl) tagEl.textContent = tag ? '· ' + tag : '';
		}

		form.addEventListener('click', function (e) {
			if (e.target.closest && e.target.closest('[data-mns-step-next]')) return;
			clearFlags();
		});

		if (pickChange) {
			pickChange.addEventListener('click', function () {
				setPickOpen(!(pickEdit && pickEdit.classList.contains('is-open')));
			});
		}

function selectPatientCard(card) {
			var mode = card.getAttribute('data-mns-patient-card');
			if (modeInput) modeInput.value = mode === 'saved' ? 'saved' : mode;
			if (patientIdInput) {
				patientIdInput.value = mode === 'saved' ? card.getAttribute('data-value') || '' : '';
			}
			qsa('[data-mns-patient-card]', form).forEach(function (c) {
				var on = c === card;
				c.classList.toggle('is-selected', on);
				c.setAttribute('aria-checked', on ? 'true' : 'false');
			});
			setCollapse(qs('[data-mns-new-collapse]', form), mode === 'new');
			updateSideSummary();
			updatePick();
		}

		qsa('[data-mns-patient-card]', form).forEach(function (card) {
			card.addEventListener('click', function () {
				selectPatientCard(card);
			});
		});

		qsa('[data-mns-age-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-age-chips]', btn.getAttribute('data-value'), ageGroupInput);
			});
		});
		qsa('[data-mns-patient-gender-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-patient-gender-chips]', btn.getAttribute('data-value'), patGenderInput);
			});
		});

		if (dateInput) {
			dateInput.addEventListener('change', function () {
				var day = dayByJ(dateInput.value);
				if (!day) {
					var current = dayById(dateValue ? dateValue.value : '');
					if (current) dateInput.value = current.j; /* back to the last valid date */
					fail('تاریخ را از تقویم انتخاب کنید (از امروز تا ۳۰ روز آینده).', dateInput);
					return;
				}
				clearFlags();
				setDay(day);
			});
		}
		if (timeValue) {
			timeValue.addEventListener('change', function () {
				updateSideSummary();
			});
		}

qsa('[data-mns-nurse-gender-cards] [data-value]', form).forEach(function (card) {
			card.addEventListener('click', function () {
				qsa('[data-mns-nurse-gender-cards] [data-value]', form).forEach(function (c) {
					var on = c === card;
					c.classList.toggle('is-selected', on);
					c.setAttribute('aria-checked', on ? 'true' : 'false');
				});
				if (genderInput) genderInput.value = card.getAttribute('data-value') || '';
				scheduleAvailability();
				updateSideSummary();
			});
		});

		if (detailsToggle) {
			detailsToggle.addEventListener('click', function () {
				setDetailsOpen(!(detailsEl && detailsEl.classList.contains('is-open')));
			});
		}

		qsa('[data-mns-vein-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-vein-chips]', btn.getAttribute('data-value'), veinInput);
				onFormEdit();
			});
		});
		qsa('[data-mns-allergy-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-allergy-chips]', btn.getAttribute('data-value'), allergyInput);
				syncAllergy();
				if (allergyInput.value === 'yes' && allergyDetail) window.setTimeout(function () {
					allergyDetail.focus({ preventScroll: true });
				}, 120);
				onFormEdit();
			});
		});

		qsa('[data-mns-stepper]', form).forEach(function (root) {
			var name = root.getAttribute('data-mns-stepper');
			qsa('[data-step]', root).forEach(function (btn) {
				btn.addEventListener('click', function () {
					var cur = parseInt((stepperInput(name) || {}).value, 10) || 1;
					setStepper(name, cur + (btn.getAttribute('data-step') === 'plus' ? 1 : -1));
				});
			});
		});

		/* Prescription photo: show what was attached, allow replace/remove. */
		var rxUrl = '';
		function showRx() {
			var empty = qs('[data-mns-upload-empty]', form);
			var file = qs('[data-mns-upload-file]', form);
			var has = hasRx();
			if (empty) empty.hidden = has;
			if (file) file.hidden = !has;
			if (rxUrl) {
				URL.revokeObjectURL(rxUrl);
				rxUrl = '';
			}
			if (!has) return;
			var f = rxInput.files[0];
			var thumb = qs('[data-mns-upload-thumb]', form);
			try {
				rxUrl = URL.createObjectURL(f);
				if (thumb) thumb.src = rxUrl;
			} catch (eUrl) {
				/* ignore */
			}
			var nameEl = qs('[data-mns-upload-name]', form);
			var sizeEl = qs('[data-mns-upload-size]', form);
			if (nameEl) nameEl.textContent = f.name;
			if (sizeEl) {
				sizeEl.textContent = f.size >= 1048576
					? toFaDigits((f.size / 1048576).toFixed(1).replace('.', '٫')) + ' مگابایت'
					: toFaDigits(Math.max(1, Math.round(f.size / 1024))) + ' کیلوبایت';
			}
		}
		if (rxInput) {
			rxInput.addEventListener('change', function () {
				var f = rxInput.files && rxInput.files[0];
				if (f && (f.type.indexOf('image/') !== 0 || f.size > 8 * 1048576)) {
					rxInput.value = '';
					showRx();
					fail('فقط عکس قبول می‌شود و حجمش باید کمتر از ۸ مگابایت باشد.', qs('[data-mns-upload]', form));
					return;
				}
				clearFlags();
				showRx();
				onFormEdit();
			});
		}
		var rxRemove = qs('[data-mns-upload-remove]', form);
		if (rxRemove) {
			rxRemove.addEventListener('click', function () {
				rxInput.value = '';
				showRx();
				onFormEdit();
			});
		}

		var notes = qs('[data-mns-notes]', form);
		var notesCount = qs('[data-mns-notes-count]', form);
		if (notes && notesCount) {
			notes.addEventListener('input', function () {
				notesCount.textContent = toFaDigits(notes.value.length) + ' / ' + toFaDigits(notes.maxLength > 0 ? notes.maxLength : 400);
			});
		}

		/* Repeat: frequency + count, with the dates and cost shown live. */
		var repSwitch = qs('[data-mns-recurring-switch]', form);
		var repHidden = qs('[data-mns-is-recurring]', form);
		var repCollapse = qs('[data-mns-recurring-collapse]', form);
		if (repSwitch) {
			repSwitch.addEventListener('click', function () {
				window.setTimeout(function () {
					var on = repSwitch.classList.contains('is-on');
					if (repHidden) repHidden.value = on ? '1' : '0';
					setCollapse(repCollapse, on);
					if (on) updateRecurring();
				}, 0);
			});
		}
		var repPattern = qs('[data-mns-rep-pattern]', form);
		if (repPattern) repPattern.addEventListener('change', updateRecurring);

		/* Phone verification boxes: Persian/Arabic/Latin digits, paste and SMS autofill spread across the boxes. */
		var otpSend = qs('[data-mns-otp-send]', form);
		if (otpSend) {
			otpSend.addEventListener('click', function () {
				var cur = normalizeIranMobile(phoneInput ? phoneInput.value : '');
				if (cur.length < 11) {
					fail('شمارهٔ تماس معتبر نیست؛ آن را در مرحلهٔ اول اصلاح کنید.', otpSend);
					return;
				}
				clearFlags();
				otpState.sent = true;
				otpState.phone = cur;
				setCollapse(qs('[data-mns-otp-code-collapse]', form), true);
				otpSend.disabled = true;
				otpSend.textContent = 'ارسال شد';
				stopOtpTimer();
				otpState.left = 60;
				otpTick();
				otpState.timer = window.setInterval(otpTick, 1000);
				var first = otpDigits()[0];
				if (first) window.setTimeout(function () {
					first.focus({ preventScroll: true });
				}, 150);
			});
		}
		otpDigits().forEach(function (inp, idx, arr) {
			function spread(digits, from) {
				for (var k = 0; k < digits.length && from + k < arr.length; k++) arr[from + k].value = toFaDigits(digits.charAt(k));
				arr[Math.min(arr.length - 1, from + digits.length)].focus();
			}
			inp.addEventListener('input', function () {
				var digits = toLatinDigits(inp.value).replace(/\D/g, '');
				inp.value = '';
				if (!digits) return;
				clearFlags();
				if (digits.length === 1) {
					inp.value = toFaDigits(digits);
					if (arr[idx + 1]) arr[idx + 1].focus();
				} else {
					spread(digits.slice(0, arr.length - idx), idx);
				}
			});
			inp.addEventListener('keydown', function (e) {
				if (e.key === 'Backspace' && !inp.value && arr[idx - 1]) arr[idx - 1].focus();
			});
			inp.addEventListener('paste', function (e) {
				var text = (e.clipboardData || window.clipboardData).getData('text');
				var digits = toLatinDigits(text).replace(/\D/g, '');
				if (!digits) return;
				e.preventDefault();
				spread(digits.slice(0, arr.length - idx), idx);
			});
			inp.addEventListener('focus', function () {
				inp.select();
			});
		});

function genTrackCode() {
			var n = Math.floor(10000 + Math.random() * 90000);
			return 'MD-' + String(n);
		}

		function showConfirmation(code) {
			var confirm = qs('[data-mns-order-confirm]');
			if (!confirm) return;
			var doneCard = qs('[data-mns-done-card]');
			if (doneCard) doneCard.hidden = false;
			setWizardStep(4);
			form.hidden = true;
			form.classList.add('is-hidden');
			var mobBar = qs('[data-mns-owizard-mob-bar]');
			if (mobBar) mobBar.hidden = true;
			var aside = qs('[data-mns-osum]');
			if (aside) aside.hidden = true;
			var draftBanner = qs('[data-mns-order-draft-banner]');
			if (draftBanner) draftBanner.hidden = true;
			confirm.hidden = false;
			confirm.classList.remove('is-hidden');
			var codeEl = qs('[data-mns-confirm-code]', confirm);
			if (codeEl) codeEl.textContent = code;
			var copyBtn = qs('[data-mns-confirm-copy]', confirm);
			if (copyBtn) copyBtn.setAttribute('data-mns-copy', code);
			var track = qs('[data-mns-confirm-track]', confirm);
			if (track) {
				try {
					var url = new URL(track.href, window.location.href);
					url.searchParams.set('mns_order', code);
					track.href = url.toString();
				} catch (err) {
					track.href = track.href.split('?')[0] + '?mns_order=' + encodeURIComponent(code);
				}
			}
			var mini = qs('[data-mns-confirm-mini]', confirm);
			if (mini) {
				var cityLabel = citySelect && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '';
				var areaLabel = areaSelect && areaSelect.options[areaSelect.selectedIndex] ? areaSelect.options[areaSelect.selectedIndex].text : '';
				mini.innerHTML =
					'<div><span>خدمت</span><strong>' +
					svcLabel +
					'</strong></div><div><span>زمان</span><strong>' +
					whenSummary() +
					'</strong></div><div><span>منطقه</span><strong>' +
					placeLabel() +
					'</strong></div><div><span>پرستار</span><strong>' +
					(genderInput && genderInput.value === 'female' ? 'خانم' : 'آقا') +
					'</strong></div>';
			}
			qsa('[data-mns-step-goto]', wizard || document).forEach(function (btn) {
				btn.classList.add('is-done');
				btn.classList.remove('is-current');
				btn.disabled = false;
				btn.removeAttribute('aria-disabled');
			});
			confirm.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			hideNotice();
			clearFlags();
			if (!verifyReady()) return;
			/* Both the desktop submit and the phone bar button: label fades out, then a round spinner fades in. */
			[qs('[data-mns-order-submit]', form), qs('[data-mns-mob-continue]')].forEach(function (btn) {
				if (!btn) return;
				btn.classList.add('is-loading');
				btn.disabled = true;
				btn.setAttribute('aria-busy', 'true');
			});
			window.setTimeout(function () {
				showConfirmation(genTrackCode());
			}, 1100);
		});

		if (provSelect) provSelect.addEventListener('change', fillCities);
		loadLocations();
		if (citySelect) {
			citySelect.addEventListener('change', fillAreas);
			areaSelect &&
				areaSelect.addEventListener('change', function () {
					fillTimes();
					scheduleAvailability();
				});
		}

		qsa('[data-mns-step-next]', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!validateStep(step)) return;
				goStep(step + 1);
			});
		});
		qsa('[data-mns-step-prev]', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				goStep(step - 1);
			});
		});
		qsa('[data-mns-step-goto]', wizard || document).forEach(function (btn) {
			btn.addEventListener('click', function () {
				var target = parseInt(btn.getAttribute('data-mns-step-goto'), 10);
				if (target < step) goStep(target);
				else if (target === step + 1 && validateStep(step)) goStep(target);
				else if (target > step + 1) showNoticeNear(form, 'لطفاً مراحل را به ترتیب کامل کنید.', true);
			});
		});

		var mobBack = qs('[data-mns-mob-back]');
		if (mobBack) {
			mobBack.addEventListener('click', function () {
				goStep(step - 1);
			});
		}

		var mobContinue = qs('[data-mns-mob-continue]');
		if (mobContinue) {
			mobContinue.addEventListener('click', function () {
				if (mobContinue.getAttribute('data-mns-mob-mode') === 'submit') {
					form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true }));
				} else if (validateStep(step)) {
					goStep(step + 1);
				}
			});
		}

		function onFormEdit() {
			updatePick();
			syncRxTag();
			if (step === 3) fillReview();
		}

		form.addEventListener('input', function (e) {
			if (e.target && e.target.matches('input, textarea, select')) onFormEdit();
		});
		form.addEventListener('change', function (e) {
			if (e.target && e.target.matches('select')) onFormEdit();
		});

		setCollapse(pickEdit, !!(pickEdit && pickEdit.classList.contains('is-open')));
		setCollapse(qs('[data-mns-new-collapse]', form), !!qs('[data-mns-new-collapse].is-open', form));
		initDateTime();
		goStep(1);
		updateSideSummary();
		updatePick();
		syncMobileBar();
		wizardReady = true;

		var prepatient = form.getAttribute('data-mns-prepatient') || '';
		if (prepatient && prepatient !== 'self') {
			var pcard = qs('[data-mns-patient-card="saved"][data-value="' + prepatient + '"]', form);
			if (pcard) selectPatientCard(pcard);
		} else {
			var selfCard = qs('[data-mns-patient-card="self"]', form);
			if (selfCard && (!modeInput || modeInput.value === 'self')) selectPatientCard(selfCard);
		}

		/* Defaults (client decision): normal saline, 2 serums, normal vein, no allergy. */
		if (veinInput && !veinInput.value) setChipGroup('[data-mns-vein-chips]', 'normal', veinInput);
		if (allergyInput && !allergyInput.value) setChipGroup('[data-mns-allergy-chips]', 'no', allergyInput);
		syncRxTag();
		setStepper('serum', parseInt(qtyInput && qtyInput.value, 10) || 2);
		setStepper('repeat', 4);
	}

	function initMockActions() {
		qsa('[data-mns-mock-action]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				showNoticeNear(btn, btn.getAttribute('data-mns-success') || 'انجام شد.', false);
			});
		});
		qsa('[data-mns-urgent-confirm]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!window.confirm('با علامت فوری، ادمین مدیار فوراً مطلع می‌شود. ادامه می‌دهید؟')) return;
				showNoticeNear(btn, btn.getAttribute('data-mns-success') || 'اعلام شد.', false);
			});
		});
		qsa('[data-mns-finalize-report]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var ta = qs('[data-mns-report]');
				if (ta) {
					ta.readOnly = true;
					ta.closest('.mns-field-block__control') && ta.closest('.mns-field-block__control').classList.add('is-readonly');
				}
				btn.disabled = true;
				showNoticeNear(btn, btn.getAttribute('data-mns-success') || 'ثبت شد.', false);
			});
		});
	}

	function t(key, fallback) {
		var i18n = (window.mnsPublic && window.mnsPublic.i18n) || {};
		return i18n[key] || fallback || key;
	}

	function initDevTools() {
		var root = qs('[data-mns-dev]');
		if (!root) return;
		var toggle = qs('[data-mns-dev-toggle]', root);
		var panel = qs('#mns-dev-panel', root);
		if (!toggle || !panel) return;
		toggle.addEventListener('click', function () {
			var open = panel.hasAttribute('hidden');
			if (open) panel.removeAttribute('hidden');
			else panel.setAttribute('hidden', '');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	function initAlerts() {
		qsa('[data-mns-alert-close]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var alert = btn.closest('[data-mns-alert], .mns-alert');
				if (alert) alert.setAttribute('hidden', '');
			});
		});
		qsa('[data-mns-retry]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				window.location.reload();
			});
		});
	}

	function initDialogs() {
		function openDialog(id) {
			var el = document.getElementById(id);
			if (!el) return;
			/* Fixed overlays must not be trapped under `.mns-dash { isolation: isolate }`. */
			if (el.parentElement !== document.body) document.body.appendChild(el);
			el.removeAttribute('hidden');
			var panel = qs('.mns-dialog__panel', el);
			if (panel) panel.focus({ preventScroll: true });
			document.documentElement.classList.add('mns-drawer-open');
		}
		function closeDialog(el) {
			if (!el) return;
			el.setAttribute('hidden', '');
			if (!qs('.mns-dialog:not([hidden])')) {
				document.documentElement.classList.remove('mns-drawer-open');
			}
		}
		qsa('[data-mns-dialog-open]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				openDialog(btn.getAttribute('data-mns-dialog-open'));
			});
		});
		qsa('[data-mns-dialog]').forEach(function (dlg) {
			qsa('[data-mns-dialog-close]', dlg).forEach(function (btn) {
				btn.addEventListener('click', function () {
					closeDialog(dlg);
				});
			});
		});
		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') return;
			qsa('.mns-dialog:not([hidden])').forEach(closeDialog);
		});
	}

	/* Generic fold: a button with aria-controls opens/closes its panel (CSS animates grid rows; inert keeps it out of tab order). */
	function initCollapseToggles() {
		qsa('[data-mns-collapse-toggle]').forEach(function (btn) {
			var panel = document.getElementById(btn.getAttribute('aria-controls') || '');
			if (!panel) return;
			btn.addEventListener('click', function () {
				var open = !panel.classList.contains('is-open');
				/* every toggle that controls this panel (e.g. "add" and "cancel") shows the same state */
				qsa('[data-mns-collapse-toggle]').forEach(function (other) {
					if (other.getAttribute('aria-controls') === panel.id) other.setAttribute('aria-expanded', open ? 'true' : 'false');
				});
				panel.classList.toggle('is-open', open);
				if ('inert' in panel) panel.inert = !open;
			});
		});
	}

	/* Delegated so rows swapped in by the orders list refresh keep working. */
	function initCopyButtons() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest && e.target.closest('[data-mns-copy]');
			if (!btn) return;
			var text = btn.getAttribute('data-mns-copy') || '';
			if (!text || !navigator.clipboard) {
				showNoticeNear(btn, t('copy_failed', 'کپی نشد'), true);
				return;
			}
			navigator.clipboard.writeText(text).then(
				function () {
					showNoticeNear(btn, t('copied', 'کپی شد'), false);
				},
				function () {
					showNoticeNear(btn, t('copy_failed', 'کپی نشد'), true);
				}
			);
		});
	}

	/* Nurse "My profile" (P-05): the affiliate panel's profile page. Photo preview/remove, the "about me" counter,
	   and an explicit save (nothing is saved while editing; leaving with changes asks first). */
	function initNurseProfile() {
		var root = qs('[data-mns-profile]');
		if (!root) return;
		var form = qs('[data-mns-pf-form]', root);
		var notice = qs('[data-mns-pf-notice]', root);
		var avatarBox = qs('[data-mns-pf-avatar]', root);
		var img = avatarBox ? qs('img', avatarBox) : null;
		var fileInput = qs('#mns-pf-avatar-input', root);
		var removeBtn = qs('[data-mns-pf-avatar-remove]', root);
		var about = qs('#mns-pf-about', root);
		var count = qs('#mns-pf-about-count', root);
		var dirty = false;
		var busy = false;
		var blobUrl = '';

		function showNotice(kind, text) {
			if (!notice) return;
			notice.textContent = text;
			notice.classList.toggle('is-error', kind === 'error');
			notice.hidden = !text;
			if (text) {
				notice.focus();
				if (notice.scrollIntoView) notice.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
			}
		}
		function markDirty() {
			dirty = true;
			if (notice && !notice.classList.contains('is-error')) notice.hidden = true;
		}

		var birthday = qs('input[data-mns-jdate]', root);
		if (birthday) {
			var $ = window.jQuery;
			if (!$ || !$.fn || !$.fn.persianDatepicker) {
				birthday.readOnly = false;
			} else {
				var arrow = function (d) {
					return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="' + d + '"/></svg>';
				};
				var years = [];
				for (var y = new Date().getFullYear() - 621; y >= 1300; y--) years.push(y);
				var opts = {
					theme: 'mns',
					formatDate: 'YYYY/0M/0D',
					cellWidth: 38,
					cellHeight: 34,
					fontSize: 13,
					closeOnBlur: false,
					calendarPosition: { x: 0, y: 6 },
					selectableYears: years,
					prevArrow: arrow('m9 6 6 6-6 6'),
					nextArrow: arrow('m15 6-6 6 6 6'),
					onShow: function (calendar) {
						var rect = birthday.getBoundingClientRect();
						var width = calendar.outerWidth();
						var maxLeft = document.documentElement.clientWidth - width - 8;
						var left = Math.max(8, Math.min(rect.right - width, maxLeft));
						this.calendarPosition.x = left - rect.left;
					},
					onSelect: function () {
						this.selectedDate = birthday.value;
						birthday.dispatchEvent(new Event('change', { bubbles: true }));
					}
				};
				if (/^\d{4}\/\d{2}\/\d{2}$/.test(birthday.value)) opts.selectedDate = birthday.value;
				$(birthday).persianDatepicker(opts);
			}
		}

		if (about && count) {
			var update = function () {
				if (about.value.length > 200) about.value = about.value.slice(0, 200);
				count.textContent = toFaDigits(about.value.length);
			};
			about.addEventListener('input', update);
			update();
		}

		if (fileInput && img) {
			fileInput.addEventListener('change', function () {
				var file = fileInput.files && fileInput.files[0];
				if (!file) return;
				if (['image/jpeg', 'image/png', 'image/webp', 'image/gif'].indexOf(file.type) === -1) {
					showNotice('error', 'فقط فایل تصویری (JPG، PNG، WEBP، GIF) مجاز است.');
					fileInput.value = '';
					return;
				}
				if (file.size > 5 * 1024 * 1024) {
					showNotice('error', 'حجم تصویر نباید بیشتر از ۵ مگابایت باشد.');
					fileInput.value = '';
					return;
				}
				if (blobUrl) URL.revokeObjectURL(blobUrl);
				blobUrl = URL.createObjectURL(file);
				img.src = blobUrl;
				if (removeBtn) removeBtn.hidden = false;
				markDirty();
			});
		}
		if (removeBtn && img) {
			removeBtn.addEventListener('click', function () {
				if (!window.confirm('تصویر پروفایل حذف شود؟')) return;
				if (blobUrl) URL.revokeObjectURL(blobUrl);
				blobUrl = '';
				if (fileInput) fileInput.value = '';
				img.src = avatarBox.getAttribute('data-default') || img.src;
				removeBtn.hidden = true;
				markDirty();
			});
		}

		if (form) {
			qsa('[data-mns-pf-field]', form).forEach(function (el) {
				el.addEventListener('input', markDirty);
				el.addEventListener('change', markDirty);
			});
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (busy) return;
				var bad = null;
				qsa('[data-mns-pf-field][required]', form).forEach(function (el) {
					var block = el.closest('.mns-field-block');
					var ok = !!String(el.value).trim();
					if (ok && el.type === 'email') ok = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(el.value.trim());
					if (block) block.classList.toggle('is-invalid', !ok);
					if (!ok && !bad) bad = el;
				});
				var nid = qs('[data-mns-pf-nid]', form);
				if (nid) {
					var digits = nid.value.replace(/[\u06F0-\u06F9]/g, function (d) {
						return String(d.charCodeAt(0) - 0x06F0);
					});
					var nidOk = /^\d{10}$/.test(digits.trim());
					var nidBlock = nid.closest('.mns-field-block');
					if (nidBlock) nidBlock.classList.toggle('is-invalid', !nidOk);
					if (!nidOk && !bad) bad = nid;
				}
				if (bad) {
					showNotice('error', 'لطفاً فیلدهای الزامی را درست پر کنید.');
					return;
				}
				if (navigator.onLine === false) {
					showNotice('error', 'ثبت نشد، اتصال برقرار نیست.');
					return;
				}
				var btn = qs('.mns-submit-btn', form);
				busy = true;
				if (btn) {
					btn.classList.add('is-loading');
					btn.disabled = true;
				}
				/* TODO(backend): save the profile (display name, email, experience, workplace, about, photo). */
				window.setTimeout(function () {
					busy = false;
					if (btn) {
						btn.classList.remove('is-loading');
						btn.disabled = false;
					}
					dirty = false;
					var email = qs('[data-mns-pf-email]', form);
					var bind = qs('[data-mns-pf-bind="email"]', root);
					if (email && bind) bind.textContent = email.value.trim();
					var first = qs('[name="first_name"]', form);
					var last = qs('[name="last_name"]', form);
					var nameBind = qs('[data-mns-pf-bind="name"]', root);
					if (first && last && nameBind) nameBind.textContent = (first.value.trim() + ' ' + last.value.trim()).trim();
					var qual = qs('select[name="qualification"]', form);
					var qualBind = qs('[data-mns-pf-bind="qualification"]', root);
					if (qual && qualBind && qual.selectedIndex > -1) qualBind.textContent = qual.options[qual.selectedIndex].textContent.trim();
					showNotice('ok', 'اطلاعات شما ذخیره شد.');
				}, 700);
			});
		}

		window.addEventListener('beforeunload', function (e) {
			if (dirty) {
				e.preventDefault();
				e.returnValue = '';
			}
		});
	}

	function boot() {
		initDrawer();
		initIranMobile();
		initCustomSelects();
		initMockForms();
		initSwitches();
		initAvailabilityCalendar();
		initStars();
		initRowToggles();
		initOrderForm();
		initMockActions();
		initDevTools();
		initAlerts();
		initDialogs();
		initCopyButtons();
		initCollapseToggles();
		initSurveyForm();
		initLiveSearch();
		initOrdersList();
		initInvoiceView();
		initNurseDash();
		initNurseOrders();
		initNurseChecklist();
		initNurseReport();
		initNurseDrug();
		initNurseSchedule();
		initNurseProfile();
	}

	function initInvoiceView() {
		var root = qs('[data-mns-invoice]');
		if (!root) return;
		var total = parseInt(root.getAttribute('data-mns-invoice-total') || '0', 10) || 0;
		var payBtns = qsa('[data-mns-invoice-pay]');
		payBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (btn.disabled) return;
				payBtns.forEach(function (b) {
					b.disabled = true;
					b.classList.add('is-busy');
					var label = qs('[data-mns-pay-label]', b);
					if (label) label.textContent = t('to_gateway', 'در حال انتقال به درگاه…');
				});
				/* TODO(backend): POST invoice + version + nonce; server verifies unpaid/no dispute/same version, then redirects to Zarinpal. */
				var url = btn.getAttribute('data-mns-pay-url');
				setTimeout(function () {
					if (url) window.location.href = url;
				}, 900);
			});
		});

		var dlg = document.getElementById('mns-dispute-dialog');
		var form = dlg ? qs('[data-mns-dispute-form]', dlg) : null;
		if (dlg && dlg.hasAttribute('data-mns-autoopen')) {
			var opener = qs('[data-mns-dispute-open]');
			if (opener) opener.click();
		}
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var reason = qs('textarea', form);
				if (reason && !reason.value.trim()) {
					reason.focus();
					return;
				}
				/* TODO(backend): POST lines[] + reason + nonce → invoice status «dispute», admin notified. */
				dlg.setAttribute('hidden', '');
				if (!qs('.mns-dialog:not([hidden])')) document.documentElement.classList.remove('mns-drawer-open');
				var chip = qs('[data-mns-invoice-status] .mns-ostatus', root);
				if (chip) {
					chip.className = 'mns-ostatus mns-ostatus--inv-dispute';
					var lbl = qs('.mns-ostatus__label', chip);
					if (lbl) lbl.textContent = t('dispute', 'اعتراض در حال بررسی');
				}
				payBtns.forEach(function (b) { b.disabled = true; });
				toggles.forEach(function (el) { el.disabled = true; });
				qsa('[data-mns-pay-locked], [data-mns-dispute-note]', root).forEach(function (el) { el.hidden = false; });
				qsa('[data-mns-dispute-open]', root).forEach(function (el) { el.hidden = true; });
				var note = qs('[data-mns-dispute-note]', root);
				if (note) note.scrollIntoView({ behavior: 'smooth', block: 'center' });
			});
		}
	}

	function initOrdersList() {
		var root = qs('[data-mns-orders]');
		if (!root) return;

		var mobile = window.matchMedia ? window.matchMedia('(max-width: 767px)') : { matches: false };
		var sheet = qs('[data-mns-order-sheet]');
		var cancelDlg = qs('[data-mns-order-cancel-dialog]');
		var form = qs('[data-mns-orders-form]', root);
		var searchInput = qs('[data-mns-orders-q]', root);
		var filterBtn = qs('[data-mns-orders-filter-toggle]', root);
		var openMenu = null;
		var cancelRow = null;
		var controller = null;

		/* Fixed overlays must not be trapped under `.mns-dash { isolation: isolate }`. */
		[sheet, cancelDlg].forEach(function (el) {
			if (el && el.parentElement !== document.body) document.body.appendChild(el);
		});

		if (searchInput) {
			var desk = searchInput.getAttribute('placeholder') || '';
			var mob = searchInput.getAttribute('data-mns-placeholder-mobile') || desk;
			var syncPlaceholder = function () {
				searchInput.setAttribute('placeholder', mobile.matches ? mob : desk);
			};
			syncPlaceholder();
			if (mobile.addEventListener) mobile.addEventListener('change', syncPlaceholder);
			searchInput.addEventListener('keydown', function (e) {
				if (e.key !== 'Enter' || !form) return;
				e.preventDefault();
				submitOrdersForm();
			});
		}

		if (filterBtn && form) {
			filterBtn.addEventListener('click', function () {
				var open = form.hasAttribute('hidden');
				if (open) form.removeAttribute('hidden');
				else form.setAttribute('hidden', '');
				filterBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		}

		/* —— In-place refresh (same flow as ARP initAjaxLists, without a custom endpoint) —— */
		function formUrl() {
			var params = new URLSearchParams();
			new FormData(form).forEach(function (value, key) {
				if (String(value).trim() !== '') params.append(key, value);
			});
			var url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
			url.search = params.toString();
			return url.href;
		}

		function submitOrdersForm() {
			if (!form) return;
			if (form.requestSubmit) form.requestSubmit();
			else form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
		}

		function syncForm(url) {
			if (!form) return;
			var params = new URL(url, window.location.href).searchParams;
			var fields = Array.prototype.slice.call(form.elements);
			if (searchInput && fields.indexOf(searchInput) === -1) fields.push(searchInput);
			fields.forEach(function (el) {
				if (!el.name || el.type === 'hidden' || el.type === 'submit') return;
				var value = params.get(el.name) || '';
				if (el.value === value) return;
				el.value = value;
				if (el.tagName === 'SELECT') {
					/* Refresh ARP's custom select label without triggering its auto-submit. */
					var shell = el.parentElement && el.parentElement.querySelector('.arp-select__trigger');
					if (shell) {
						var opt = el.options[el.selectedIndex];
						shell.textContent = opt ? opt.textContent.trim() : '';
						shell.classList.toggle('is-placeholder', !el.value);
					}
				}
			});
		}

		function swap(doc) {
			qsa('[data-mns-swap]', root).forEach(function (region) {
				var key = region.getAttribute('data-mns-swap');
				var next = doc.querySelector('[data-mns-orders] [data-mns-swap="' + key + '"]');
				if (next) region.innerHTML = next.innerHTML;
			});
			var reset = qs('[data-mns-filters-reset]', root);
			var nextReset = doc.querySelector('[data-mns-orders] [data-mns-filters-reset]');
			if (reset && nextReset) reset.hidden = nextReset.hasAttribute('hidden');
		}

		function load(url, push) {
			if (!window.fetch || !window.DOMParser) {
				window.location.href = url;
				return;
			}
			closeMenu(false);
			if (controller) controller.abort();
			controller = window.AbortController ? new AbortController() : null;
			var regions = qsa('[data-mns-swap]', root);
			regions.forEach(function (r) {
				r.classList.add('is-loading');
				r.setAttribute('aria-busy', 'true');
			});
			fetch(url, { credentials: 'same-origin', signal: controller ? controller.signal : undefined })
				.then(function (res) {
					if (!res.ok) throw new Error(t('load_failed', 'بارگذاری نشد'));
					return res.text();
				})
				.then(function (html) {
					var doc = new DOMParser().parseFromString(html, 'text/html');
					if (!doc.querySelector('[data-mns-orders]')) throw new Error(t('load_failed', 'بارگذاری نشد'));
					swap(doc);
					if (push) window.history.pushState({ mnsOrders: true }, '', url);
					var list = qs('[data-mns-swap="list"]', root);
					if (list && list.getBoundingClientRect().top < 0) list.scrollIntoView({ behavior: 'smooth', block: 'start' });
				})
				.catch(function (err) {
					if (err && err.name === 'AbortError') return;
					showNoticeNear(root, (err && err.message) || t('load_failed', 'بارگذاری نشد'), true);
				})
				.then(function () {
					qsa('[data-mns-swap]', root).forEach(function (r) {
						r.classList.remove('is-loading');
						r.setAttribute('aria-busy', 'false');
					});
				});
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				load(formUrl(), true);
			});
		}

		window.addEventListener('popstate', function () {
			syncForm(window.location.href);
			load(window.location.href, false);
		});

		/* —— ⋯ menu / sheet / cancel —— */
		function showDialog(el) {
			if (!el) return;
			el.removeAttribute('hidden');
			document.documentElement.classList.add('mns-drawer-open');
			var panel = qs('.mns-dialog__panel', el);
			if (panel) panel.focus({ preventScroll: true });
		}

		function hideDialog(el) {
			if (!el) return;
			el.setAttribute('hidden', '');
			if (!qs('.mns-dialog:not([hidden])')) document.documentElement.classList.remove('mns-drawer-open');
		}

		function closeMenu(focusTrigger) {
			if (!openMenu) return;
			openMenu.menu.setAttribute('hidden', '');
			openMenu.menu.classList.remove('is-fixed');
			openMenu.menu.style.top = '';
			openMenu.menu.style.left = '';
			openMenu.btn.setAttribute('aria-expanded', 'false');
			openMenu.row.classList.remove('is-menu-open');
			if (focusTrigger) openMenu.btn.focus({ preventScroll: true });
			openMenu = null;
		}

		/* Anchor the popover to its actions cell with fixed positioning so table scroll wraps can't clip it. */
		function placeMenu(menu, anchor) {
			var rect = anchor.getBoundingClientRect();
			menu.classList.add('is-fixed');
			var width = menu.offsetWidth;
			var height = menu.offsetHeight;
			var vw = document.documentElement.clientWidth;
			var left = Math.max(8, Math.min(rect.right - width, vw - width - 8));
			var top = rect.bottom + 8;
			if (top + height > window.innerHeight - 8 && rect.top - height - 8 > 8) top = rect.top - height - 8;
			menu.style.left = left + 'px';
			menu.style.top = top + 'px';
		}

		function openCancel(row) {
			if (!cancelDlg || !row) return;
			cancelRow = row;
			var text = qs('[data-mns-order-cancel-text]', cancelDlg);
			if (text) text.textContent = row.getAttribute('data-mns-cancel-text') || '';
			var code = qs('[data-mns-order-cancel-code]', cancelDlg);
			var trigger = qs('[data-mns-order-cancel]', row);
			if (code && trigger) code.value = trigger.getAttribute('data-mns-order-cancel') || '';
			var reason = qs('textarea', cancelDlg);
			if (reason) reason.value = '';
			showDialog(cancelDlg);
		}

		function openSheet(row, menu) {
			if (!sheet) return;
			var title = qs('[data-mns-order-sheet-title]', sheet);
			var list = qs('[data-mns-order-sheet-list]', sheet);
			if (title) title.textContent = row.getAttribute('data-mns-order-title') || '';
			if (list) {
				list.innerHTML = '';
				qsa(':scope > li', menu).forEach(function (li) {
					var clone = li.cloneNode(true);
					var cancelBtn = qs('[data-mns-order-cancel]', clone);
					if (cancelBtn) {
						cancelBtn.addEventListener('click', function () {
							hideDialog(sheet);
							openCancel(row);
						});
					}
					list.appendChild(clone);
				});
			}
			showDialog(sheet);
		}

		function isPlainClick(e) {
			return e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey;
		}

		root.addEventListener('click', function (e) {
			var nav = e.target.closest('[data-mns-orders-nav], .mns-olist__sort, .arp-refs-pager a');
			if (nav && nav.href && isPlainClick(e)) {
				e.preventDefault();
				syncForm(nav.href);
				load(nav.href, true);
				return;
			}

			var moreBtn = e.target.closest('[data-mns-order-more]');
			if (moreBtn) {
				var row = moreBtn.closest('[data-mns-order]');
				var menu = row ? qs('[data-mns-order-menu]', row) : null;
				if (!row || !menu) return;
				if (mobile.matches) {
					closeMenu(false);
					openSheet(row, menu);
					return;
				}
				if (openMenu && openMenu.menu === menu) {
					closeMenu(true);
					return;
				}
				closeMenu(false);
				menu.removeAttribute('hidden');
				placeMenu(menu, moreBtn.closest('.mns-order__actions') || moreBtn);
				moreBtn.setAttribute('aria-expanded', 'true');
				row.classList.add('is-menu-open');
				openMenu = { menu: menu, btn: moreBtn, row: row };
				var firstItem = qs('[role="menuitem"]', menu);
				if (firstItem) firstItem.focus({ preventScroll: true });
				return;
			}

			var cancelBtn = e.target.closest('[data-mns-order-cancel]');
			if (cancelBtn) {
				var crow = cancelBtn.closest('[data-mns-order]');
				closeMenu(false);
				openCancel(crow);
				return;
			}

			if (e.target.closest('a, button, input, select, textarea, label, [data-mns-order-menu]')) return;
			var hitRow = e.target.closest('[data-mns-order]');
			if (!hitRow) return;
			if (window.getSelection && String(window.getSelection()).trim() !== '') return;
			var link = qs('[data-mns-order-link]', hitRow);
			if (link) window.location.href = link.href;
		});

		document.addEventListener('click', function (e) {
			if (!openMenu) return;
			if (openMenu.row.contains(e.target) && e.target.closest('.mns-order__actions')) return;
			closeMenu(false);
		});

		window.addEventListener('scroll', function () {
			closeMenu(false);
		}, { passive: true });
		window.addEventListener('resize', function () {
			closeMenu(false);
		});

		document.addEventListener('keydown', function (e) {
			if (!openMenu) return;
			if (e.key === 'Escape') {
				closeMenu(true);
				return;
			}
			if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
			var items = qsa('[role="menuitem"]', openMenu.menu);
			if (!items.length) return;
			e.preventDefault();
			var idx = items.indexOf(document.activeElement);
			idx = e.key === 'ArrowDown' ? (idx + 1) % items.length : (idx - 1 + items.length) % items.length;
			items[idx].focus();
		});

		if (mobile.addEventListener) {
			mobile.addEventListener('change', function () {
				closeMenu(false);
				hideDialog(sheet);
			});
		}

		var cancelForm = cancelDlg ? qs('[data-mns-order-cancel-form]', cancelDlg) : null;
		if (cancelForm) {
			cancelForm.addEventListener('submit', function (e) {
				e.preventDefault();
				/*
				 * TODO(backend): POST order + reason + nonce; server runs an atomic
				 * `UPDATE … WHERE status IN ('submitted','assigned')`, frees the nurse slot,
				 * SMS to nurse/admins. If the nurse already started, show
				 * «پرستار خدمت را شروع کرده، برای لغو با پشتیبانی تماس بگیرید» instead.
				 */
				var row = cancelRow;
				hideDialog(cancelDlg);
				if (!row) return;
				var badge = qs('[data-mns-order-badge]', row);
				if (badge) {
					badge.className = 'mns-ostatus mns-ostatus--cancelled';
					var label = qs('.mns-ostatus__label', badge);
					if (label) label.textContent = t('cancelled', 'لغوشده');
				}
				row.setAttribute('data-mns-order-status', 'cancelled');
				qsa('[data-mns-order-cancel]', row).forEach(function (btn) {
					var li = btn.closest('li');
					if (li) li.remove();
				});
				showNoticeNear(root, t('order_cancelled', 'سفارش لغو شد.'), false);
				cancelRow = null;
			});
		}
	}

	/* Nurse dashboard (P-26): accept switch, inline confirms, service timer, notices, weekly plan. */
	function initNurseDash() {
		var root = qs('[data-mns-ndash]');
		if (!root) return;

		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}
		/* Text changes fade out and back in instead of jumping. */
		function swapText(el, text, holder) {
			if (!el || el.textContent.trim() === text) return;
			var box = holder || el;
			box.classList.add('is-swapping');
			window.setTimeout(function () {
				el.textContent = text;
				box.classList.remove('is-swapping');
			}, 150);
		}

		/* Accept switch: changes at once, goes back if saving fails (A-10). */
		var accept = qs('[data-mns-accept]', root);
		if (accept) {
			var stateEl = qs('[data-mns-accept-state]', accept);
			var hint = qs('[data-mns-accept-hint]', root);
			var hintText = qs('[data-mns-accept-hint-text]', root);
			var noneText = qs('[data-mns-none-text]', root);
			var paint = function (on, failed) {
				accept.classList.toggle('is-on', on);
				accept.setAttribute('aria-checked', on ? 'true' : 'false');
				swapText(stateEl, accept.getAttribute(on ? 'data-on-text' : 'data-off-text') || '');
				if (hintText) {
					hintText.classList.toggle('is-error', !!failed);
					hintText.textContent = failed ? accept.getAttribute('data-fail-text') || '' : hintText.getAttribute('data-default') || '';
				}
				setCollapse(hint, !on || !!failed);
				if (noneText) swapText(noneText, noneText.getAttribute(on ? 'data-on' : 'data-off') || '');
			};
			accept.addEventListener('click', function () {
				if (accept.classList.contains('is-busy')) return;
				var before = accept.classList.contains('is-on');
				paint(!before, false);
				accept.classList.add('is-busy');
				/* TODO(backend): POST the new value + nonce; resolve with the stored value. */
				window.setTimeout(function () {
					accept.classList.remove('is-busy');
					if (navigator.onLine === false) paint(before, true);
				}, 500);
			});
		}

		/* Inline confirm for start / end of service: opens inside the card, never a modal. */
		qsa('[data-mns-confirm-open]', root).forEach(function (btn) {
			var panel = document.getElementById(btn.getAttribute('data-mns-confirm-open'));
			if (!panel) return;
			var ok = qs('[data-mns-confirm-ok]', panel);
			var err = qs('[data-mns-confirm-error]', panel);
			var close = function () {
				setCollapse(panel, false);
				btn.setAttribute('aria-expanded', 'false');
			};
			btn.addEventListener('click', function () {
				var open = !panel.classList.contains('is-open');
				if (err) err.textContent = '';
				setCollapse(panel, open);
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (open && ok) ok.focus({ preventScroll: true });
			});
			var cancel = qs('[data-mns-confirm-cancel]', panel);
			if (cancel) {
				cancel.addEventListener('click', function () {
					close();
					btn.focus({ preventScroll: true });
				});
			}
			if (ok) {
				ok.addEventListener('click', function () {
					if (ok.classList.contains('is-loading')) return;
					if (err) err.textContent = '';
					if (navigator.onLine === false) {
						if (err) err.textContent = 'ثبت نشد، دوباره تلاش کنید.';
						return;
					}
					ok.classList.add('is-loading');
					ok.disabled = true;
					/* TODO(backend): atomic UPDATE … WHERE status IN (…); on failure re-read the status from the server, then show the error line. */
					window.setTimeout(function () {
						var go = ok.getAttribute('data-mns-go');
						if (go) window.location.href = go;
					}, 900);
				});
			}
		});

		/* Notices: "seen" folds the row away; the block folds when the last one goes. */
		var news = qs('[data-mns-news]', root);
		if (news) {
			news.addEventListener('click', function (e) {
				var btn = e.target.closest && e.target.closest('[data-mns-news-dismiss]');
				if (!btn) return;
				var item = btn.closest('[data-mns-news-item]');
				if (!item || !item.classList.contains('is-open')) return;
				/* TODO(backend): mark notice `data-mns-news-dismiss` as seen. */
				var last = qsa('[data-mns-news-item].is-open', news).length <= 1;
				setCollapse(last ? news : item, false);
				window.setTimeout(function () {
					if (last) news.hidden = true;
					else if (item.parentNode) item.parentNode.removeChild(item);
				}, 300);
			});
		}

		/* Weekly plan: pick a day in the strip, its hour view fades in (all panels share one cell, so no jump). */
		var week = qs('[data-mns-week]', root);
		if (week) {
			var dayBtns = qsa('[data-mns-week-day]', week);
			var dayPanels = qsa('[data-mns-week-panel]', week);
			dayBtns.forEach(function (btn) {
				btn.addEventListener('click', function () {
					var key = btn.getAttribute('data-mns-week-day');
					dayBtns.forEach(function (b) {
						var on = b === btn;
						b.classList.toggle('is-selected', on);
						b.setAttribute('aria-pressed', on ? 'true' : 'false');
					});
					dayPanels.forEach(function (p) {
						var on = p.getAttribute('data-mns-week-panel') === key;
						p.classList.toggle('is-selected', on);
						if ('inert' in p) p.inert = !on;
					});
				});
			});
		}

		/* Folds (invoice rows, timeline) and dismissable notes of the order workspace. */
		qsa('[data-mns-fold]', root).forEach(function (btn) {
			var panel = document.getElementById(btn.getAttribute('aria-controls'));
			if (!panel) return;
			btn.addEventListener('click', function () {
				var open = !panel.classList.contains('is-open');
				setCollapse(panel, open);
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		});
		qsa('[data-mns-note-dismiss]', root).forEach(function (btn) {
			btn.addEventListener('click', function () {
				var wrap = btn.closest('[data-mns-note]');
				if (!wrap) return;
				/* TODO(backend): mark the time change as seen. */
				setCollapse(wrap, false);
				window.setTimeout(function () {
					if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
				}, 450);
			});
		});

		/* Connection lost: say so and lock the actions that must reach the server. */
		var net = qs('[data-mns-net]', root);
		var netButtons = qsa('[data-mns-needs-net]', root).filter(function (b) {
			return !b.disabled;
		});
		var syncNet = function () {
			var offline = navigator.onLine === false;
			setCollapse(net, offline);
			netButtons.forEach(function (b) {
				b.disabled = offline;
			});
			if (offline) {
				qsa('[data-mns-confirm].is-open', root).forEach(function (p) {
					setCollapse(p, false);
				});
			}
		};
		window.addEventListener('online', syncNet);
		window.addEventListener('offline', syncNet);
		syncNet();
	}

	/* Nurse orders (P-27): tabs, search, "more filters", history paging and the row menu, over server-rendered rows. */
	function initNurseOrders() {
		var root = qs('[data-mns-nord]');
		if (!root) return;
		var rows = qsa('[data-mns-nord-row]', root);
		if (!rows.length) return;
		var PAGE = 10;
		var state = { tab: root.getAttribute('data-tab') || 'upcoming', q: '', month: '', status: '', recurring: '', page: 1 };

		var tabs = qsa('[data-mns-nord-tab]', root);
		var list = qs('[data-mns-nord-list]', root);
		var history = qs('[data-mns-nord-history]', root);
		var panes = qsa('[data-mns-nord-pane]', root);
		var search = qs('[data-mns-nord-search]', root);
		var form = qs('[data-mns-nord-form]', root);
		var toggle = qs('[data-mns-nord-filter-toggle]', root);
		var emptyBox = qs('[data-mns-nord-empty]', root);
		var histEmpty = qs('[data-mns-nord-history-empty]', root);
		var sum = qs('[data-mns-nord-sum]', root);
		var pager = qs('[data-mns-nord-pager]', root);
		var resetBox = qs('[data-mns-nord-reset-box]', root);
		var selMonth = qs('[data-mns-nord-month]', root);
		var selStatus = qs('[data-mns-nord-status]', root);
		var selRec = qs('[data-mns-nord-recurring]', root);

		function money(n) {
			return toFaDigits(String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '٬'));
		}
		function attr(el, name) {
			return el.getAttribute(name) || '';
		}
		function matches(r) {
			if (attr(r, 'data-tab') !== state.tab) return false;
			if (state.q && attr(r, 'data-text').indexOf(state.q) === -1) return false;
			if (state.recurring && attr(r, 'data-recurring') !== '1') return false;
			if (state.tab === 'done') {
				if (state.month && attr(r, 'data-month') !== state.month) return false;
				if (state.status && attr(r, 'data-status') !== state.status) return false;
			}
			return true;
		}
		function anyFilter() {
			return !!(state.q || state.month || state.status || state.recurring);
		}

		function renderPager(total, pages) {
			if (!pager) return;
			pager.innerHTML = '';
			pager.hidden = total < 1;
			if (total < 1) return;
			var info = document.createElement('p');
			info.className = 'arp-refs-pager__info';
			var from = (state.page - 1) * PAGE;
			info.textContent = pages <= 1
				? toFaDigits(total) + ' مورد'
				: 'نمایش ' + toFaDigits(from + 1) + ' تا ' + toFaDigits(Math.min(from + PAGE, total)) + ' از ' + toFaDigits(total) + ' مورد';
			pager.appendChild(info);
			if (pages <= 1) return;
			var nav = document.createElement('nav');
			nav.className = 'arp-refs-pager__nav';
			nav.setAttribute('aria-label', 'صفحه‌بندی');
			function arrow(dir, enabled) {
				var el = document.createElement(enabled ? 'a' : 'span');
				el.className = 'arp-refs-pager__btn arp-refs-pager__btn--arrow' + (enabled ? '' : ' is-disabled');
				el.innerHTML = dir === 'prev'
					? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>'
					: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
				if (enabled) {
					el.href = '#';
					el.setAttribute('aria-label', dir === 'prev' ? 'صفحهٔ قبل' : 'صفحهٔ بعد');
					el.addEventListener('click', function (e) {
						e.preventDefault();
						state.page += dir === 'prev' ? -1 : 1;
						apply(true);
					});
				} else {
					el.setAttribute('aria-hidden', 'true');
				}
				return el;
			}
			nav.appendChild(arrow('prev', state.page > 1));
			var wrap = document.createElement('span');
			wrap.className = 'arp-refs-pager__pages arp-refs-pager__pages--full';
			for (var p = 1; p <= pages; p++) {
				(function (n) {
					var el = document.createElement(n === state.page ? 'span' : 'a');
					el.className = 'arp-refs-pager__btn' + (n === state.page ? ' is-current' : '');
					el.textContent = toFaDigits(n);
					if (n === state.page) {
						el.setAttribute('aria-current', 'page');
					} else {
						el.href = '#';
						el.addEventListener('click', function (e) {
							e.preventDefault();
							state.page = n;
							apply(true);
						});
					}
					wrap.appendChild(el);
				})(p);
			}
			nav.appendChild(wrap);
			nav.appendChild(arrow('next', state.page < pages));
			pager.appendChild(nav);
		}

		function apply(keepPage) {
			if (!keepPage) state.page = 1;
			root.setAttribute('data-tab', state.tab);
			var isDone = state.tab === 'done';
			if (list) list.hidden = isDone;
			if (history) history.hidden = !isDone;
			panes.forEach(function (p) {
				p.hidden = attr(p, 'data-mns-nord-pane') !== state.tab;
			});
			if (resetBox) resetBox.hidden = !anyFilter();

			if (isDone) {
				var tableRows = rows.filter(function (r) {
					return r.tagName === 'TR';
				});
				var match = tableRows.filter(matches);
				var pages = Math.max(1, Math.ceil(match.length / PAGE));
				if (state.page > pages) state.page = pages;
				var from = (state.page - 1) * PAGE;
				var visible = {};
				match.forEach(function (r, i) {
					if (i >= from && i < from + PAGE) visible[attr(r, 'data-id')] = true;
				});
				rows.forEach(function (r) {
					if (attr(r, 'data-tab') === 'done') r.hidden = !visible[attr(r, 'data-id')];
				});
				var total = 0;
				match.forEach(function (r) {
					total += parseInt(attr(r, 'data-share'), 10) || 0;
				});
				if (sum) sum.textContent = total > 0 ? 'جمع سهم: ' + money(total) + ' تومان' : '';
				if (histEmpty) histEmpty.hidden = match.length > 0;
				renderPager(match.length, pages);
				if (emptyBox) emptyBox.hidden = true;
				return;
			}

			var found = 0;
			rows.forEach(function (r) {
				if (attr(r, 'data-tab') !== state.tab) return;
				var ok = matches(r);
				r.hidden = !ok;
				if (ok) found++;
			});
			qsa('[data-mns-nord-pane="' + state.tab + '"] [data-mns-nord-group]', root).forEach(function (g) {
				g.hidden = !qs('[data-mns-nord-row]:not([hidden])', g);
			});
			var hasRows = rows.some(function (r) {
				return attr(r, 'data-tab') === state.tab;
			});
			if (emptyBox) emptyBox.hidden = !(hasRows && found === 0);
			var head = qs('[data-mns-nord-pane="' + state.tab + '"] .mns-olist__head', root);
			if (head) head.hidden = found === 0;
		}

		tabs.forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				var key = attr(a, 'data-mns-nord-tab');
				if (key === state.tab) return;
				state.tab = key;
				tabs.forEach(function (t) {
					var on = t === a;
					t.classList.toggle('is-active', on);
					if (on) t.setAttribute('aria-current', 'page');
					else t.removeAttribute('aria-current');
				});
				closeMenu();
				apply(false);
			});
		});
		if (search) {
			search.addEventListener('input', function () {
				state.q = (search.value || '').trim().toLowerCase();
				apply(false);
			});
		}
		if (toggle && form) {
			toggle.addEventListener('click', function () {
				var open = form.hidden;
				form.hidden = !open;
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
			form.addEventListener('submit', function (e) {
				e.preventDefault();
			});
		}
		function bindSelect(sel, key) {
			if (!sel) return;
			sel.addEventListener('change', function () {
				state[key] = sel.value || '';
				apply(false);
			});
		}
		bindSelect(selMonth, 'month');
		bindSelect(selStatus, 'status');
		bindSelect(selRec, 'recurring');

		qsa('[data-mns-nord-reset]', root).forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				state.q = '';
				state.month = '';
				state.status = '';
				state.recurring = '';
				if (search) search.value = '';
				[selMonth, selStatus, selRec].forEach(function (sel) {
					if (!sel) return;
					sel.value = '';
					sel.dispatchEvent(new Event('change', { bubbles: false }));
				});
				state.q = '';
				state.month = '';
				state.status = '';
				state.recurring = '';
				apply(false);
			});
		});

		/* The row menu: a popover under the button; inside the scrolling table it is positioned in the viewport. */
		var openMenu = null;
		function closeMenu() {
			if (!openMenu) return;
			openMenu.menu.hidden = true;
			openMenu.btn.setAttribute('aria-expanded', 'false');
			if (openMenu.row) openMenu.row.classList.remove('is-menu-open');
			openMenu = null;
		}
		root.addEventListener('click', function (e) {
			var more = e.target.closest && e.target.closest('[data-mns-order-more]');
			if (more) {
				e.preventDefault();
				var wrap = more.closest('.mns-order__actions');
				var menu = wrap ? qs('[data-mns-order-menu]', wrap) : null;
				if (!menu) return;
				var same = openMenu && openMenu.menu === menu;
				closeMenu();
				if (same) return;
				menu.hidden = false;
				more.setAttribute('aria-expanded', 'true');
				var row = more.closest('[data-mns-nord-row]');
				if (row) row.classList.add('is-menu-open');
				if (more.closest('.arp-table-wrap')) {
					var r = more.getBoundingClientRect();
					menu.classList.add('is-fixed');
					menu.style.top = Math.round(r.bottom + 8) + 'px';
					menu.style.left = Math.round(Math.min(Math.max(8, r.left), window.innerWidth - 196)) + 'px';
				}
				openMenu = { menu: menu, btn: more, row: row };
				return;
			}
			if (openMenu && !e.target.closest('[data-mns-order-menu]')) closeMenu();
			var rowEl = e.target.closest && e.target.closest('[data-mns-nord-row]');
			if (!rowEl || e.target.closest('a, button, [data-mns-order-menu]')) return;
			var href = rowEl.getAttribute('data-href');
			if (href) window.location.href = href;
		});
		document.addEventListener('click', function (e) {
			if (openMenu && !(e.target.closest && e.target.closest('[data-mns-order-menu], [data-mns-order-more]'))) closeMenu();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeMenu();
		});
		window.addEventListener('scroll', closeMenu, { passive: true });

		apply(false);
	}

	/* Nurse checklist (P-29/30): a guided list of steps (one open at a time), order-known defaults, one-tap "all normal",
	   autosave, review and final submit. */
	function initNurseChecklist() {
		var root = qs('[data-mns-ncl]');
		if (!root) return;
		var cfg = {};
		try {
			cfg = JSON.parse(root.getAttribute('data-config') || '{}') || {};
		} catch (e) {
			cfg = {};
		}
		var phase = root.getAttribute('data-phase') || 'draft';
		var locked = phase === 'submitted';
		var storeKey = root.getAttribute('data-key') || 'mns-ncl';
		var ranges = cfg.numbers || {};
		var catalog = cfg.catalog || [];
		var stepEls = qsa('[data-mns-step]', root);
		var segs = qsa('[data-mns-seg]', root);
		var progress = qs('[data-mns-ncl-progress]', root);
		var waitEl = qs('[data-mns-ncl-wait]', root);
		var finishEl = qs('[data-mns-ncl-finish]', root);
		var st = { ack: {} };

		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}
		function latin(s) {
			return String(s == null ? '' : s)
				.replace(/[۰-۹]/g, function (d) {
					return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
				})
				.replace(/[٠-٩]/g, function (d) {
					return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
				})
				.replace(/[٫،]/g, '.');
		}
		function fieldsOf(stepEl) {
			return qsa('[data-mns-ncl-field]', stepEl);
		}
		function allFields() {
			return qsa('[data-mns-ncl-field]', root);
		}
		function fieldByKey(key) {
			return qs('[data-mns-ncl-field="' + key + '"]', root);
		}
		function keyOf(stepEl) {
			return stepEl.getAttribute('data-mns-step');
		}
		function readValue(el) {
			var type = el.getAttribute('data-type');
			if (type === 'number') return latin(qs('input', el).value).trim();
			if (type === 'check') return qs('input', el).checked ? 'yes' : '';
			var on = qs('input:checked', el);
			return on ? on.value : '';
		}
		function numberState(key, raw) {
			if (raw === '') return '';
			var n = parseFloat(raw);
			var r = ranges[key] || {};
			if (isNaN(n)) return 'bad';
			if ((r.min != null && n < r.min) || (r.max != null && n > r.max)) return 'bad';
			if ((r.warn_min != null && n < r.warn_min) || (r.warn_max != null && n > r.warn_max)) return 'warn';
			return 'ok';
		}
		function answered(el) {
			var key = el.getAttribute('data-mns-ncl-field');
			var v = readValue(el);
			if (el.getAttribute('data-type') === 'number') {
				var s = numberState(key, v);
				return s === 'ok' || (s === 'warn' && !!st.ack[key]);
			}
			return v !== '';
		}
		function isGated(stepEl) {
			return phase === 'draft' && stepEl.hasAttribute('data-gated');
		}
		/* required fields of a step that are not answered yet (an unconfirmed unusual number counts as open) */
		function missingOf(stepEl) {
			return fieldsOf(stepEl).filter(function (el) {
				if (!el.hasAttribute('data-required')) {
					/* an optional number that holds an impossible value must still be fixed */
					if (el.getAttribute('data-type') !== 'number') return false;
					var s = numberState(el.getAttribute('data-mns-ncl-field'), readValue(el));
					return s === 'bad' || (s === 'warn' && !st.ack[el.getAttribute('data-mns-ncl-field')]);
				}
				return !answered(el);
			});
		}
		function labelOf(el) {
			return el.getAttribute('data-label') || '';
		}

		/* ---- number feedback ---- */
		function validateNumber(el) {
			var key = el.getAttribute('data-mns-ncl-field');
			var msg = qs('[data-mns-ncl-msg]', el);
			var raw = readValue(el);
			var s = numberState(key, raw);
			el.classList.toggle('is-warn', s === 'warn' && !st.ack[key]);
			el.classList.toggle('is-acked', s === 'warn' && !!st.ack[key]);
			el.classList.toggle('is-bad', s === 'bad');
			if (!msg) return;
			msg.innerHTML = '';
			if (s === 'bad') {
				msg.textContent = 'این عدد ممکن نیست؛ دوباره بررسی کنید.';
			} else if (s === 'warn' && !st.ack[key]) {
				msg.appendChild(document.createTextNode('این عدد را دوباره بررسی کنید. درست است؟ '));
				if (!locked) {
					var b = document.createElement('button');
					b.type = 'button';
					b.className = 'mns-ncl__ack';
					b.textContent = 'بله، درست است';
					b.addEventListener('click', function () {
						st.ack[key] = true;
						validateNumber(el);
						afterEdit(el);
					});
					msg.appendChild(b);
				}
			} else if (s === 'warn') {
				msg.textContent = 'مقدار تأیید شد.';
			}
		}

		/* ---- one-line summary per step ---- */
		function rowsList() {
			return qsa('[data-mns-ncl-row]', root);
		}
		function qtyOf(li) {
			return parseInt(latin(qs('[data-qty]', li).textContent), 10) || 1;
		}
		function hasFlag(stepEl) {
			return fieldsOf(stepEl).some(function (el) {
				var type = el.getAttribute('data-type');
				var v = readValue(el);
				if (type === 'number') return numberState(el.getAttribute('data-mns-ncl-field'), v) === 'warn';
				var normal = el.getAttribute('data-normal');
				return !!normal && v !== '' && v !== normal;
			});
		}
		/* ---- derived state: sections complete themselves, nothing is sequential ---- */
		function isRequiredStep(stepEl) {
			return keyOf(stepEl) !== 'extras';
		}
		function stepState(stepEl) {
			if (isGated(stepEl)) return 'locked';
			if (keyOf(stepEl) === 'extras') return rowsList().length ? 'done' : 'todo';
			var reqs = fieldsOf(stepEl).filter(function (el) {
				return el.hasAttribute('data-required');
			});
			return reqs.every(answered) && missingOf(stepEl).length === 0 ? 'done' : 'todo';
		}
		function firstOpenRequired() {
			for (var i = 0; i < stepEls.length; i++) {
				if (isRequiredStep(stepEls[i]) && stepState(stepEls[i]) === 'todo') return stepEls[i];
			}
			return null;
		}

		function render() {
			var left = 0;
			var segIdx = 0;
			var pending = 0;
			stepEls.forEach(function (el) {
				var s = stepState(el);
				el.classList.toggle('is-done', s === 'done');
				el.classList.toggle('is-locked', s === 'locked');
				el.classList.toggle('is-open', s !== 'locked');
				el.classList.toggle('has-flag', s === 'done' && hasFlag(el));
				setCollapse(qs('.mns-collapse', el), s !== 'locked');
				var sum = qs('[data-mns-step-sum]', el);
				if (sum) sum.textContent = s === 'locked' ? el.getAttribute('data-locked-hint') || '' : el.getAttribute('data-hint') || '';
				if (isRequiredStep(el)) {
					if (s !== 'done') left++;
					if (s === 'todo') pending++;
					if (segs[segIdx]) segs[segIdx].classList.toggle('is-done', s === 'done');
					segIdx++;
				}
			});
			var allDone = left === 0;
			/* nothing left to fill now: only the locked "end of service" remains */
			var waiting = !allDone && pending === 0;
			if (progress) {
				progress.classList.toggle('is-complete', allDone);
				if (allDone) progress.textContent = 'همهٔ بخش‌ها کامل است';
				else if (waiting) progress.textContent = toFaDigits(left) + ' بخش مانده · منتظر پایان خدمت';
				else progress.textContent = toFaDigits(left) + ' بخش مانده';
			}
			setCollapse(waitEl, waiting && !locked);
			if (finishEl) finishEl.classList.toggle('is-ready', allDone);
		}

		/* no draft saving: the form is submitted once; leaving with changes asks first */
		var dirty = false;
		function scheduleSave() {
			if (locked) return;
			dirty = true;
		}
		window.addEventListener('beforeunload', function (e) {
			if (dirty && !locked) {
				e.preventDefault();
				e.returnValue = '';
			}
		});
		/* every change just re-renders */
		function afterEdit(el) {
			var stepEl = el.closest ? el.closest('[data-mns-step]') : null;
			if (stepEl) {
				var r = qs('[data-mns-step-reason]', stepEl);
				if (r) r.textContent = '';
			}
			render();
			scheduleSave();
		}

		/* ---- fields ---- */
		allFields().forEach(function (el) {
			var type = el.getAttribute('data-type');
			var key = el.getAttribute('data-mns-ncl-field');
			if (type === 'number') {
				var input = qs('input', el);
				input.addEventListener('input', function () {
					var fa = toFaDigits(latin(input.value).replace(/[^0-9.]/g, ''));
					if (fa !== input.value) input.value = fa;
					st.ack[key] = false;
					validateNumber(el);
					afterEdit(el);
				});
			} else {
				qsa('input', el).forEach(function (r) {
					r.addEventListener('change', function () {
						afterEdit(el);
					});
				});
			}
		});
		/* ---- rows: consumables and extra services share one list ---- */
		var trashIcon = (qs('[data-mns-ncl-trash]', root) || {}).innerHTML || '×';
		var chosen = qs('[data-mns-ncl-chosen]', root);
		var catalogEl = qs('[data-mns-ncl-catalog]', root);
		var emptyEl = qs('[data-mns-ncl-empty]', root);
		var findEl = qs('[data-mns-ncl-find]', root);
		function itemById(id) {
			return (
				catalog.filter(function (c) {
					return c.id === id;
				})[0] || null
			);
		}
		function refreshEmpty() {
			if (emptyEl) emptyEl.hidden = rowsList().length > 0;
		}
		function addRow(item, qtyVal) {
			if (!chosen || qs('[data-mns-ncl-row="' + item.id + '"]', chosen)) return;
			var li = document.createElement('li');
			li.className = 'mns-ncl__row';
			li.setAttribute('data-mns-ncl-row', item.id);
			li.setAttribute('data-label', item.label);
			li.innerHTML =
				'<span class="mns-ncl__row-name"></span>' +
				(locked
					? '<b class="mns-ncl__row-qty"><output data-qty></output> عدد</b>'
					: '<span class="mns-ncl__stepper"><button type="button" class="mns-ncl__stp" data-step="-1" aria-label="کم کردن">−</button><output data-qty></output><button type="button" class="mns-ncl__stp" data-step="1" aria-label="زیاد کردن">+</button></span>' +
					  '<button type="button" class="mns-ncl__rm" data-rm aria-label="حذف">' + trashIcon + '</button>');
			qs('.mns-ncl__row-name', li).textContent = item.label;
			qs('[data-qty]', li).textContent = toFaDigits(qtyVal || 1);
			qsa('[data-step]', li).forEach(function (b) {
				b.addEventListener('click', function () {
					var n = qtyOf(li) + parseInt(b.getAttribute('data-step'), 10);
					qs('[data-qty]', li).textContent = toFaDigits(Math.max(1, Math.min(999, n)));
					afterEdit(li);
				});
			});
			var rm = qs('[data-rm]', li);
			if (rm) {
				rm.addEventListener('click', function () {
					li.parentNode.removeChild(li);
					renderCatalog();
					refreshEmpty();
					afterEdit(chosen);
				});
			}
			chosen.appendChild(li);
			refreshEmpty();
		}
		function renderCatalog() {
			if (!catalogEl) return;
			var q = latin((findEl && findEl.value) || '').trim().toLowerCase();
			catalogEl.innerHTML = '';
			catalog.forEach(function (item) {
				if (qs('[data-mns-ncl-row="' + item.id + '"]', root)) return;
				if (q && (item.label + ' ' + item.cat).toLowerCase().indexOf(q) === -1) return;
				var li = document.createElement('li');
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'mns-ncl__cat';
				b.innerHTML = '<span></span><small></small>';
				qs('span', b).textContent = item.label;
				qs('small', b).textContent = item.cat;
				b.addEventListener('click', function () {
					addRow(item, 1);
					renderCatalog();
					afterEdit(chosen);
				});
				li.appendChild(b);
				catalogEl.appendChild(li);
			});
			if (!catalogEl.children.length) {
				var none = document.createElement('li');
				none.className = 'mns-ncl__cat-none';
				none.textContent = 'موردی پیدا نشد';
				catalogEl.appendChild(none);
			}
		}
		if (findEl) findEl.addEventListener('input', renderCatalog);

		/* ---- notes ---- */
		var notes = qs('[data-mns-ncl-notes]', root);
		var counter = qs('[data-mns-ncl-counter]', root);
		function noteCount() {
			if (counter && notes) counter.textContent = toFaDigits(notes.value.length) + ' از ۱٬۰۰۰ نویسه';
		}
		if (notes) {
			notes.addEventListener('input', function () {
				noteCount();
				scheduleSave();
			});
		}

		/* ---- start state: the server's values ---- */
		Object.keys(cfg.rows || {}).forEach(function (id) {
			var item = itemById(id);
			if (item) addRow(item, cfg.rows[id]);
		});
		allFields().forEach(function (el) {
			if (el.getAttribute('data-type') === 'number') validateNumber(el);
		});
		renderCatalog();
		refreshEmpty();
		noteCount();
		render();

		/* ---- actions ---- */
		function flash(el) {
			el.classList.add('is-flash');
			window.setTimeout(function () {
				el.classList.remove('is-flash');
			}, 1600);
		}
		function scrollToEl(el, block) {
			window.setTimeout(function () {
				if (el && el.scrollIntoView) el.scrollIntoView({ block: block || 'start', behavior: 'smooth' });
			}, 460); /* after the collapse animation settled, so the target position is final */
		}
		/* ---- review + final submit ---- */
		var formView = qs('[data-mns-ncl-formview]', root);
		var reviewView = qs('[data-mns-ncl-reviewview]', root);
		var reviewBtn = qs('[data-mns-ncl-review]', root);
		var finalBtn = qs('[data-mns-ncl-final]', root);
		var editBtn = qs('[data-mns-ncl-edit]', root);
		var confirmEl = qs('#mns-ncl-confirm', root);

		function valueText(el) {
			var v = readValue(el);
			if (v === '') return '';
			var type = el.getAttribute('data-type');
			if (type === 'check') return 'تأیید شد';
			if (type === 'number') return toFaDigits(v) + (el.getAttribute('data-unit') ? ' ' + el.getAttribute('data-unit') : '');
			return v;
		}
		function buildReview() {
			var host = qs('[data-mns-ncl-summary]', root);
			host.innerHTML = '';
			stepEls.forEach(function (stepEl) {
				var key = keyOf(stepEl);
				var block = document.createElement('div');
				block.className = 'mns-ncl__rv-block';
				var h = document.createElement('p');
				h.className = 'mns-ncl__rv-h';
				h.textContent = qs('.mns-ncl__st-title', stepEl).textContent;
				block.appendChild(h);
				var ul = document.createElement('ul');
				ul.className = 'mns-ncl__sum';
				function line(a, b, cls) {
					var li = document.createElement('li');
					var x = document.createElement('span');
					var y = document.createElement('span');
					x.textContent = a;
					y.textContent = b;
					if (cls) li.className = cls;
					li.appendChild(x);
					li.appendChild(y);
					ul.appendChild(li);
				}
				if (key === 'extras') {
					rowsList().forEach(function (r) {
						line(r.getAttribute('data-label'), toFaDigits(qtyOf(r)) + ' عدد');
					});
					if (!rowsList().length) line('موردی ثبت نشده', '—', 'is-empty');
				} else {
					fieldsOf(stepEl).forEach(function (el) {
						var txt = valueText(el);
						var miss = (el.hasAttribute('data-required') && !answered(el)) || txt === '';
						var cls = '';
						if (miss && el.hasAttribute('data-required')) cls = 'is-miss';
						else if (txt === '') cls = 'is-empty';
						else if (hasFlag(stepEl) && (el.classList.contains('is-acked') || (el.getAttribute('data-normal') && readValue(el) !== el.getAttribute('data-normal')))) cls = 'is-warn';
						line(labelOf(el).replace(/\s*\(اختیاری\)$/, ''), miss && el.hasAttribute('data-required') ? 'ثبت نشده' : txt === '' ? '—' : txt, cls);
					});
				}
				block.appendChild(ul);
				host.appendChild(block);
			});
			var n = notes && notes.value.trim();
			if (n) {
				var nb = document.createElement('div');
				nb.className = 'mns-ncl__rv-block';
				nb.innerHTML = '<p class="mns-ncl__rv-h">یادداشت</p><p class="mns-ncl__rv-note"></p>';
				qs('.mns-ncl__rv-note', nb).textContent = n;
				host.appendChild(nb);
			}
		}
		function showView(review) {
			setCollapse(formView, !review);
			setCollapse(reviewView, review);
			if (review) buildReview();
			if (confirmEl) setCollapse(confirmEl, false);
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
		if (reviewBtn) {
			reviewBtn.addEventListener('click', function () {
				/* something still open: show exactly what */
				var first = firstOpenRequired();
				if (first) {
					var miss = missingOf(first);
					var reason = qs('[data-mns-step-reason]', first);
					if (reason) {
						reason.classList.remove('is-hint');
						reason.textContent = 'هنوز مانده: ' + miss.map(labelOf).join('، ');
					}
					if (miss[0]) {
						flash(miss[0]);
						miss[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
					}
					return;
				}
				showView(true);
			});
		}
		if (editBtn) {
			editBtn.addEventListener('click', function () {
				showView(false);
			});
		}
		if (finalBtn) {
			finalBtn.addEventListener('click', function () {
				setCollapse(confirmEl, true);
				scrollToEl(confirmEl, 'center');
			});
		}
		if (confirmEl) {
			var ok = qs('[data-mns-confirm-ok]', confirmEl);
			var cancel = qs('[data-mns-confirm-cancel]', confirmEl);
			var err = qs('[data-mns-confirm-error]', confirmEl);
			if (cancel) {
				cancel.addEventListener('click', function () {
					setCollapse(confirmEl, false);
				});
			}
			if (ok) {
				ok.addEventListener('click', function () {
					if (ok.classList.contains('is-loading')) return;
					if (err) err.textContent = '';
					if (navigator.onLine === false) {
						if (err) err.textContent = 'ثبت نشد، دوباره تلاش کنید.';
						return;
					}
					ok.classList.add('is-loading');
					ok.disabled = true;
					/* TODO(backend): one-time key; the server validates required fields and ranges, then locks. */
					window.setTimeout(function () {
						try {
							window.localStorage.removeItem(storeKey);
						} catch (e) {
							/* ignore */
						}
						dirty = false;
						window.location.href = ok.getAttribute('data-mns-go') || window.location.href;
					}, 900);
				});
			}
		}
	}

	/* Nurse end-of-visit report (P-31): sections in any order that complete themselves, one tap for the usual visit,
	   an always-reachable urgent notice, photos with consent, autosave, review and final submit. */
	function initNurseReport() {
		var root = qs('[data-mns-nrp]');
		if (!root || !qs('[data-mns-rp-form]', root)) return;
		var cfg = {};
		try {
			cfg = JSON.parse(root.getAttribute('data-config') || '{}') || {};
		} catch (e) {
			cfg = {};
		}
		var normalVals = cfg.normal || {};
		var phase = root.getAttribute('data-phase') || 'open';
		var locked = phase === 'submitted';
		var storeKey = root.getAttribute('data-key') || 'mns-nrp';
		var secs = qsa('[data-mns-sec]', root);
		var fields = qsa('[data-mns-rp-field]', root);
		var segs = qsa('[data-mns-seg]', root);
		var progress = qs('[data-mns-rp-progress]', root);
		var quickEl = qs('[data-mns-rp-quick]', root);
		var normalRow = qs('[data-mns-rp-normalrow]', root);
		var finish = qs('[data-mns-rp-finish]', root);
		var urgentPanel = qs('[data-mns-rp-urgent]', root);
		var urgentBtn = qs('[data-mns-rp-urgent-toggle]', root);
		var urgentLabel = qs('[data-mns-rp-urgent-label]', root);
		var urgentText = qs('[data-mns-rp-urgent-text]', root);
		var urgentSet = qs('[data-mns-rp-urgent-set]', root);
		var urgentReason = qs('[data-mns-rp-urgent-reason]', root);
		var st = { open: {}, urgent: false, quickGone: false, normalGone: false };
		var files = [];

		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}
		function latin(s) {
			return String(s == null ? '' : s)
				.replace(/[۰-۹]/g, function (d) {
					return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
				})
				.replace(/[٠-٩]/g, function (d) {
					return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
				});
		}
		function keyOf(el) {
			return el.getAttribute('data-mns-rp-field');
		}
		function fieldByKey(k) {
			return qs('[data-mns-rp-field="' + k + '"]', root);
		}
		function readRaw(el) {
			var type = el.getAttribute('data-type');
			if (type === 'text') return qs('textarea', el).value.trim();
			if (type === 'number') return latin(qs('input', el).value).trim();
			var on = qs('input:checked', el);
			return on ? on.value : '';
		}
		/* a field that is hidden by its condition has no value */
		function visible(el) {
			var cond = el.getAttribute('data-showif');
			if (!cond) return true;
			var p = cond.split('=');
			var dep = fieldByKey(p[0]);
			if (!dep || !visible(dep)) return false;
			return p[1].split('|').indexOf(readRaw(dep)) !== -1;
		}
		function val(k) {
			var el = fieldByKey(k);
			return el && visible(el) ? readRaw(el) : '';
		}
		function numberBad(el) {
			var raw = readRaw(el);
			if (el.getAttribute('data-type') !== 'number' || raw === '') return false;
			var n = parseFloat(raw);
			var inp = qs('input', el);
			return isNaN(n) || n < parseFloat(inp.getAttribute('data-min')) || n > parseFloat(inp.getAttribute('data-maxv'));
		}
		function missingOf(secEl) {
			return qsa('[data-mns-rp-field]', secEl).filter(function (el) {
				if (!visible(el)) return false;
				if (numberBad(el)) return true;
				return el.hasAttribute('data-required') && readRaw(el) === '';
			});
		}
		function hasAny(secEl) {
			return qsa('[data-mns-rp-field]', secEl).some(function (el) {
				return visible(el) && readRaw(el) !== '';
			});
		}
		function secComplete(secEl) {
			if (secEl.hasAttribute('data-required')) return missingOf(secEl).length === 0;
			return hasAny(secEl) && missingOf(secEl).length === 0;
		}
		function isNormal() {
			return Object.keys(normalVals).every(function (k) {
				return val(k) === normalVals[k];
			}) && val('problem_type') === '' && val('follow_type') === '';
		}
		function anyRequiredAnswered() {
			return fields.some(function (el) {
				return el.hasAttribute('data-required') && visible(el) && readRaw(el) !== '';
			});
		}

		/* ---- conditional fields + the follow-up rule ---- */
		function applyRules() {
			fields.forEach(function (el) {
				if (!el.hasAttribute('data-showif')) return;
				var show = visible(el);
				if (!show && !el.hidden) {
					/* a field that disappears forgets its answer */
					qsa('input[type=radio]', el).forEach(function (r) {
						r.checked = false;
					});
					qsa('textarea', el).forEach(function (t) {
						t.value = '';
					});
				}
				el.hidden = !show;
			});
			/* "needs follow-up" forces "follow-up: yes" */
			var needs = val('patient_status') === 'نیازمند پیگیری';
			var follow = fieldByKey('follow');
			if (follow) {
				qsa('input', follow).forEach(function (r) {
					if (r.value === 'نیازی نیست') {
						r.disabled = locked || needs;
						if (needs && r.checked) r.checked = false;
					}
					if (needs && r.value === 'بله') r.checked = true;
				});
			}
		}

		/* ---- summaries ---- */
		function summaryOf(secEl) {
			var key = secEl.getAttribute('data-mns-sec');
			var hint = secEl.getAttribute('data-hint') || '';
			if (key === 'result') {
				if (!secComplete(secEl)) return hint;
				return val('result') + (val('reason') ? ' · ' + val('reason') : '');
			}
			if (key === 'problem') {
				if (!secComplete(secEl)) return hint;
				return val('problem') === 'خیر' ? 'مشکلی نبود' : 'بله · ' + val('problem_type');
			}
			if (key === 'patient') {
				if (!secComplete(secEl)) return hint;
				return val('patient_status') + ' · ' + (val('follow') === 'بله' ? 'پیگیری: ' + val('follow_type') : 'بدون پیگیری');
			}
			if (key === 'svc') {
				var num = qs('[data-type="number"]', secEl);
				var v = num ? readRaw(num) : '';
				return v ? toFaDigits(v) + ' ' + (num.getAttribute('data-unit') || '') : hint;
			}
			if (key === 'notes') {
				var t = val('notes');
				var parts = [];
				if (t) parts.push(t.length > 36 ? t.slice(0, 36) + '…' : t);
				if (files.length) parts.push(toFaDigits(files.length) + ' عکس');
				return parts.length ? parts.join(' · ') : hint;
			}
			return hint;
		}
		function hasFlag(secEl) {
			var key = secEl.getAttribute('data-mns-sec');
			if (key === 'result') return !!val('result') && val('result') !== 'کامل انجام شد';
			if (key === 'problem') return val('problem') === 'بله';
			if (key === 'patient') return val('patient_status') === 'نیازمند پیگیری' || val('follow') === 'بله';
			return false;
		}

		function render() {
			applyRules();
			var done = 0;
			var total = 0;
			var segIdx = 0;
			secs.forEach(function (secEl) {
				var key = secEl.getAttribute('data-mns-sec');
				var req = secEl.hasAttribute('data-required');
				var ok = secComplete(secEl);
				var isOpen = !!st.open[key];
				secEl.classList.toggle('is-done', ok);
				secEl.classList.toggle('is-open', isOpen);
				secEl.classList.toggle('has-flag', ok && hasFlag(secEl));
				var head = qs('[data-mns-sec-toggle]', secEl);
				head.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
				setCollapse(qs('.mns-collapse', secEl), isOpen);
				var sum = qs('[data-mns-sec-sum]', secEl);
				if (sum) sum.textContent = summaryOf(secEl);
				if (req) {
					total++;
					if (ok) done++;
					if (segs[segIdx]) segs[segIdx].classList.toggle('is-done', ok);
					segIdx++;
				}
			});
			var left = total - done;
			if (progress) {
				progress.classList.toggle('is-complete', left === 0);
				progress.textContent = left === 0 ? 'همهٔ بخش‌های الزامی کامل است' : toFaDigits(left) + ' بخش الزامی مانده';
			}
			var normal = isNormal();
			if (!normal) st.normalGone = false;
			setCollapse(quickEl, !locked && !anyRequiredAnswered() && !st.quickGone);
			setCollapse(normalRow, !locked && normal && !st.normalGone);
			if (urgentLabel) urgentLabel.textContent = st.urgent ? 'پیام ثبت شد' : 'پیام به پشتیبانی';
			if (urgentBtn) urgentBtn.classList.toggle('is-set', st.urgent);
			if (urgentSet) urgentSet.textContent = st.urgent ? 'برداشتن پیام' : 'ثبت پیام';
		}

		/* no draft saving: the form is submitted once; leaving with changes asks first */
		var dirty = false;
		function scheduleSave() {
			if (locked) return;
			dirty = true;
		}
		window.addEventListener('beforeunload', function (e) {
			if (dirty && !locked) {
				e.preventDefault();
				e.returnValue = '';
			}
		});
		function changed() {
			render();
			scheduleSave();
		}

		/* ---- field events ---- */
		function setValue(k, v) {
			var el = fieldByKey(k);
			if (!el) return;
			var type = el.getAttribute('data-type');
			if (type === 'chips') {
				qsa('input', el).forEach(function (r) {
					r.checked = r.value === v;
				});
			} else if (type === 'text') {
				qs('textarea', el).value = v;
			} else if (type === 'number') {
				qs('input', el).value = v ? toFaDigits(v) : '';
			}
		}
		fields.forEach(function (el) {
			var type = el.getAttribute('data-type');
			if (type === 'chips') {
				qsa('input', el).forEach(function (r) {
					r.addEventListener('change', changed);
				});
			} else if (type === 'text') {
				qs('textarea', el).addEventListener('input', changed);
			} else if (type === 'number') {
				var input = qs('input', el);
				input.addEventListener('input', function () {
					var fa = toFaDigits(latin(input.value).replace(/[^0-9]/g, ''));
					if (fa !== input.value) input.value = fa;
					var msg = qs('[data-mns-rp-msg]', el);
					el.classList.toggle('is-bad', numberBad(el));
					if (msg) msg.textContent = numberBad(el) ? 'این عدد ممکن نیست؛ دوباره بررسی کنید.' : '';
					changed();
				});
			}
		});

		/* ---- sections ---- */
		function flash(el) {
			el.classList.add('is-flash');
			window.setTimeout(function () {
				el.classList.remove('is-flash');
			}, 1600);
		}
		function scrollToEl(el, block) {
			window.setTimeout(function () {
				if (el && el.scrollIntoView) el.scrollIntoView({ block: block || 'start', behavior: 'smooth' });
			}, 460);
		}
		secs.forEach(function (secEl) {
			qs('[data-mns-sec-toggle]', secEl).addEventListener('click', function () {
				var key = secEl.getAttribute('data-mns-sec');
				st.open[key] = !st.open[key];
				render();
				if (st.open[key]) scrollToEl(secEl, 'start');
			});
		});

		/* ---- the usual visit in one tap ---- */
		var normalBtn = qs('[data-mns-rp-normal]', root);
		var problemBtn = qs('[data-mns-rp-problem]', root);
		var changeBtn = qs('[data-mns-rp-change]', root);
		if (normalBtn) {
			normalBtn.addEventListener('click', function () {
				Object.keys(normalVals).forEach(function (k) {
					setValue(k, normalVals[k]);
				});
				st.normalGone = false;
				changed();
				if (finish) scrollToEl(finish, 'center');
			});
		}
		if (problemBtn) {
			problemBtn.addEventListener('click', function () {
				st.quickGone = true;
				st.open.result = true;
				st.open.problem = true;
				render();
				scrollToEl(secs[0], 'start');
			});
		}
		if (changeBtn) {
			changeBtn.addEventListener('click', function () {
				st.normalGone = true;
				st.open.result = true;
				render();
				scrollToEl(secs[0], 'start');
			});
		}

		/* ---- urgent notice ---- */
		if (urgentBtn) {
			urgentBtn.addEventListener('click', function () {
				var open = !(urgentPanel && urgentPanel.classList.contains('is-open'));
				setCollapse(urgentPanel, open);
				urgentBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (open) scrollToEl(urgentPanel, 'center');
			});
		}
		if (urgentSet) {
			urgentSet.addEventListener('click', function () {
				if (st.urgent) {
					st.urgent = false;
					if (urgentText) urgentText.value = '';
					changed();
					return;
				}
				if (!urgentText || urgentText.value.trim() === '') {
					if (urgentReason) urgentReason.textContent = 'لطفاً پیام را بنویسید.';
					if (urgentText) {
						flash(urgentText.parentNode);
						urgentText.focus();
					}
					return;
				}
				if (urgentReason) urgentReason.textContent = '';
				st.urgent = true;
				setCollapse(urgentPanel, false);
				if (urgentBtn) urgentBtn.setAttribute('aria-expanded', 'false');
				changed();
			});
		}
		if (urgentText) {
			urgentText.addEventListener('input', function () {
				if (urgentReason) urgentReason.textContent = '';
				scheduleSave();
			});
		}

		/* ---- photos (static preview: only names + thumbnails; the upload is backend work) ---- */
		var consent = qs('[data-mns-rp-consent]', root);
		var pick = qs('[data-mns-rp-pick]', root);
		var fileInput = qs('[data-mns-rp-file]', root);
		var thumbs = qs('[data-mns-rp-thumbs]', root);
		function drawThumbs() {
			if (!thumbs) return;
			thumbs.innerHTML = '';
			files.forEach(function (f, i) {
				var li = document.createElement('li');
				var img = document.createElement('img');
				img.alt = '';
				try {
					img.src = URL.createObjectURL(f);
				} catch (e) {
					img.removeAttribute('src');
				}
				var rm = document.createElement('button');
				rm.type = 'button';
				rm.className = 'mns-nrp__thumb-rm';
				rm.setAttribute('aria-label', 'حذف عکس');
				rm.innerHTML = (qs('[data-mns-rp-trash]', root) || {}).innerHTML || '×';
				rm.addEventListener('click', function () {
					files.splice(i, 1);
					drawThumbs();
					render();
				});
				li.appendChild(img);
				li.appendChild(rm);
				thumbs.appendChild(li);
			});
			if (pick) pick.classList.toggle('is-full', files.length >= 10);
		}
		if (consent && fileInput) {
			consent.addEventListener('change', function () {
				fileInput.disabled = !consent.checked || files.length >= 10;
				if (pick) pick.classList.toggle('is-off', !consent.checked);
			});
			fileInput.addEventListener('change', function () {
				Array.prototype.slice.call(fileInput.files || []).forEach(function (f) {
					if (files.length < 10) files.push(f);
				});
				fileInput.value = '';
				drawThumbs();
				render();
			});
		}

		/* ---- review + final submit ---- */
		var formView = qs('[data-mns-rp-formview]', root);
		var reviewView = qs('[data-mns-rp-reviewview]', root);
		var reviewBtn = qs('[data-mns-rp-review]', root);
		var finalBtn = qs('[data-mns-rp-final]', root);
		var editBtn = qs('[data-mns-rp-edit]', root);
		var confirmEl = qs('#mns-rp-confirm', root);

		function buildReview() {
			var host = qs('[data-mns-rp-summary]', root);
			host.innerHTML = '';
			function block(title) {
				var b = document.createElement('div');
				b.className = 'mns-ncl__rv-block';
				var h = document.createElement('p');
				h.className = 'mns-ncl__rv-h';
				h.textContent = title;
				if (title) b.appendChild(h);
				var ul = document.createElement('ul');
				ul.className = 'mns-ncl__sum';
				b.appendChild(ul);
				host.appendChild(b);
				return ul;
			}
			function line(ul, label, value, vis, cls) {
				var li = document.createElement('li');
				if (cls) li.className = cls;
				var a = document.createElement('span');
				a.appendChild(document.createTextNode(label));
				if (vis) {
					var tag = document.createElement('small');
					tag.className = 'mns-nrp__vis ' + (vis === 'mgmt' ? 'is-mgmt' : 'is-pt');
					tag.textContent = vis === 'mgmt' ? 'فقط مدیریت' : 'بیمار می‌بیند';
					a.appendChild(tag);
				}
				var b = document.createElement('span');
				b.textContent = value;
				li.appendChild(a);
				li.appendChild(b);
				ul.appendChild(li);
			}
			secs.forEach(function (secEl) {
				var title = qs('.mns-ncl__st-title', secEl).firstChild.textContent;
				var items = qsa('[data-mns-rp-field]', secEl).filter(function (el) {
					return visible(el) && readRaw(el) !== '';
				});
				if (!items.length) return;
				/* a section with one answer named like the section needs no second heading */
				var ul = block(items.length === 1 && items[0].getAttribute('data-label') === title ? '' : title);
				items.forEach(function (el) {
					var v = readRaw(el);
					var shown = el.getAttribute('data-type') === 'number' ? toFaDigits(v) + ' ' + (el.getAttribute('data-unit') || '') : v;
					line(ul, el.getAttribute('data-label').replace(/\s*\(اختیاری\)$/, ''), shown, el.getAttribute('data-vis'), '');
				});
			});
			if (files.length) {
				var pu = block('عکس‌ها');
				line(pu, 'تعداد عکس', toFaDigits(files.length), 'mgmt', '');
			}
			if (st.urgent) {
				var uu = block('پیام برای پشتیبانی');
				line(uu, 'متن پیام', urgentText ? urgentText.value.trim() : '', 'mgmt', 'is-warn');
			}
			var partial = val('result') && val('result') !== 'کامل انجام شد';
			var br = qs('[data-mns-rp-banner-review]', root);
			var bu = qs('[data-mns-rp-banner-urgent]', root);
			if (br) br.hidden = !partial;
			if (bu) bu.hidden = !st.urgent;
		}
		function showView(review) {
			setCollapse(formView, !review);
			setCollapse(reviewView, review);
			if (review) buildReview();
			if (confirmEl) setCollapse(confirmEl, false);
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
		if (reviewBtn) reviewBtn.addEventListener('click', function () {
			/* a required section is still open: show exactly where */
			var firstSec = secs.filter(function (s) {
				return s.hasAttribute('data-required') && !secComplete(s);
			})[0];
			if (firstSec) {
				var miss = missingOf(firstSec);
				st.open[firstSec.getAttribute('data-mns-sec')] = true;
				render();
				if (miss[0]) {
					flash(miss[0]);
					scrollToEl(miss[0], 'center');
				} else {
					scrollToEl(firstSec, 'start');
				}
				return;
			}
			showView(true);
		});
		if (editBtn) editBtn.addEventListener('click', function () {
			showView(false);
		});
		if (finalBtn) finalBtn.addEventListener('click', function () {
			setCollapse(confirmEl, true);
			scrollToEl(confirmEl, 'center');
		});
		if (confirmEl) {
			var ok = qs('[data-mns-confirm-ok]', confirmEl);
			var cancel = qs('[data-mns-confirm-cancel]', confirmEl);
			var err = qs('[data-mns-confirm-error]', confirmEl);
			if (cancel) {
				cancel.addEventListener('click', function () {
					setCollapse(confirmEl, false);
				});
			}
			if (ok) {
				ok.addEventListener('click', function () {
					if (ok.classList.contains('is-loading')) return;
					if (err) err.textContent = '';
					if (navigator.onLine === false) {
						if (err) err.textContent = 'ثبت نشد، دوباره تلاش کنید.';
						return;
					}
					ok.classList.add('is-loading');
					ok.disabled = true;
					/* TODO(backend): one-time key; the server validates, locks, sends the urgent SMS, flags partial visits for review. */
					window.setTimeout(function () {
						try {
							window.localStorage.removeItem(storeKey);
						} catch (e) {
							/* ignore */
						}
						dirty = false;
						window.location.href = ok.getAttribute('data-mns-go') || window.location.href;
					}, 900);
				});
			}
		}

		/* reached with answers: the first unfinished required section is open */
		if (!locked && anyRequiredAnswered()) {
			for (var i = 0; i < secs.length; i++) {
				if (secs[i].hasAttribute('data-required') && !secComplete(secs[i])) {
					st.open[secs[i].getAttribute('data-mns-sec')] = true;
					break;
				}
			}
		}
		render();
	}

	/* Nurse medication step (P-33/34): one form. The path (prescription / consult), the consult result (recorded at once,
	   then locked), drug cards with their own photo, review and final submit. Nothing is saved in between. */
	/* Nurse medication step (P-33/34): one form. The path (prescription / consult), the consult result (recorded at once,
	   then locked), drug cards with their own photo, review and final submit. Nothing is saved in between. */
	function initNurseDrug() {
		var root = qs('[data-mns-ndr]');
		if (!root || !qs('[data-mns-nd-form]', root)) return;
		var cfg = {};
		try {
			cfg = JSON.parse(root.getAttribute('data-config') || '{}') || {};
		} catch (e) {
			cfg = {};
		}
		var phase = root.getAttribute('data-phase') || 'draft';
		var locked = phase === 'submitted';
		var routes = cfg.routes || [];
		var st = { path: cfg.path || '', consult: { result: '', time: '', note: '', attempts: [] }, busy: false };
		var cs = cfg.consult || {};
		st.consult.result = cs.result || '';
		st.consult.time = cs.time || '';
		st.consult.note = cs.note || '';
		st.consult.attempts = (cs.attempts || []).slice();
		var trashIcon = (qs('[data-mns-nd-trash]', root) || {}).innerHTML || '×';
		var camIcon = (qs('[data-mns-nd-cam]', root) || {}).innerHTML || '';
		var MAX_DRUGS = 20;

		var pathField = qs('[data-mns-nd-field="path"]', root);
		var rxBlock = qs('[data-mns-nd-rx]', root);
		var consultBlock = qs('[data-mns-nd-consult]', root);
		var consultForm = qs('[data-mns-nd-consult-form]', root);
		var finalEl = qs('[data-mns-nd-final]', root);
		var finalRows = qs('[data-mns-nd-final-rows]', root);
		var denyEl = qs('[data-mns-nd-deny]', root);
		var attemptsEl = qs('[data-mns-nd-attempts]', root);
		var attemptsList = qs('[data-mns-nd-attempts-list]', root);
		var registerBtn = qs('[data-mns-nd-register]', root);
		var cnote = qs('[data-mns-nd-cnote]', root);
		var doctor = qs('[data-mns-nd-doctor]', root);
		var banner = qs('[data-mns-nd-banner]', root);
		var gate = qs('[data-mns-nd-gate]', root);
		var listWrap = qs('[data-mns-nd-listwrap]', root);
		var listEl = qs('[data-mns-nd-list]', root);
		var emptyEl = qs('[data-mns-nd-empty]', root);
		var addBtn = qs('[data-mns-nd-add]', root);
		var progress = qs('[data-mns-nd-progress]', root);
		var secPath = qs('[data-mns-sec="path"]', root);
		var secDrugs = qs('[data-mns-sec="drugs"]', root);

		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}
		function esc(s) {
			var d = document.createElement('div');
			d.textContent = String(s == null ? '' : s);
			return d.innerHTML.replace(/"/g, '&quot;');
		}
		function nowFa() {
			var d = new Date();
			return toFaDigits((d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes());
		}
		function flash(el) {
			if (!el) return;
			el.classList.add('is-flash');
			window.setTimeout(function () {
				el.classList.remove('is-flash');
			}, 1600);
		}
		function scrollToEl(el, block) {
			window.setTimeout(function () {
				if (el && el.scrollIntoView) el.scrollIntoView({ block: block || 'start', behavior: 'smooth' });
			}, 120);
		}

		/* nothing is saved in between: leaving with changes asks first */
		var dirty = false;
		window.addEventListener('beforeunload', function (e) {
			if (dirty && !locked) {
				e.preventDefault();
				e.returnValue = '';
			}
		});
		function changed() {
			if (locked) return;
			dirty = true;
			render();
		}

		/* ---- photos: a picker + tiles with a trash button (the real upload is backend work) ---- */
		function makePhotos(wrap, max, initial, onChange) {
			var items = (initial || []).map(function (n) {
				return { name: n };
			});
			var thumbs = qs('[data-mns-nd-thumbs]', wrap);
			var pick = qs('[data-mns-nd-pick]', wrap);
			var input = qs('[data-mns-nd-file]', wrap);
			function draw() {
				thumbs.innerHTML = '';
				items.forEach(function (it, i) {
					var li = document.createElement('li');
					if (it.file) {
						var img = document.createElement('img');
						img.alt = '';
						try {
							img.src = URL.createObjectURL(it.file);
						} catch (e) {
							img.removeAttribute('src');
						}
						li.appendChild(img);
					} else {
						var ph = document.createElement('span');
						ph.className = 'mns-ndr__ph';
						ph.innerHTML = camIcon;
						var nm = document.createElement('small');
						nm.textContent = it.name;
						ph.appendChild(nm);
						li.appendChild(ph);
					}
					if (!locked) {
						var rm = document.createElement('button');
						rm.type = 'button';
						rm.className = 'mns-nrp__thumb-rm';
						rm.setAttribute('aria-label', 'حذف عکس');
						rm.innerHTML = trashIcon;
						rm.addEventListener('click', function () {
							items.splice(i, 1);
							draw();
							onChange();
						});
						li.appendChild(rm);
					}
					thumbs.appendChild(li);
				});
				if (pick) pick.classList.toggle('is-full', items.length >= max);
				if (input) input.disabled = items.length >= max;
			}
			if (input) {
				input.addEventListener('change', function () {
					Array.prototype.slice.call(input.files || []).forEach(function (f) {
						if (items.length < max) items.push({ file: f, name: f.name });
					});
					input.value = '';
					draw();
					onChange();
				});
			}
			draw();
			return {
				count: function () {
					return items.length;
				}
			};
		}

		/* ---- consult ---- */
		function consultFinal() {
			return st.consult.result === 'ok' || st.consult.result === 'denied';
		}
		function autoRow(a, b, cls) {
			var p = document.createElement('p');
			p.className = 'mns-nrp__auto-row' + (cls ? ' ' + cls : '');
			var x = document.createElement('span');
			var y = document.createElement('b');
			x.textContent = a;
			y.textContent = b;
			p.appendChild(x);
			p.appendChild(y);
			return p;
		}
		if (registerBtn) {
			registerBtn.addEventListener('click', function () {
				if (st.busy) return;
				var reason = qs('[data-mns-nd-consult-reason]', root);
				var chosen = qs('input[name="consult_result"]:checked', consultForm);
				reason.textContent = '';
				if (!chosen) {
					reason.textContent = 'نتیجهٔ مشاوره را انتخاب کنید.';
					flash(qs('[data-mns-nd-field="consult_result"]', root));
					return;
				}
				if (navigator.onLine === false) {
					reason.textContent = 'ثبت نشد، اتصال برقرار نیست.';
					return;
				}
				st.busy = true;
				registerBtn.classList.add('is-loading');
				registerBtn.disabled = true;
				/* TODO(backend): a separate immediate, uneditable action; server time is stored; "not approved" flags the order and notifies admins in one transaction. */
				window.setTimeout(function () {
					st.busy = false;
					registerBtn.classList.remove('is-loading');
					registerBtn.disabled = false;
					var t = nowFa();
					if (chosen.value === 'none') {
						st.consult.attempts.push(t);
						chosen.checked = false;
					} else {
						st.consult.result = chosen.value;
						st.consult.time = t;
						st.consult.note = cnote ? cnote.value.trim() : '';
					}
					render();
				}, 800);
			});
		}

		/* ---- drug cards ---- */
		var cards = [];
		var seq = 0;
		function chipsHtml(name, opts) {
			return opts
				.map(function (o) {
					return '<label class="mns-ncl__chip"><input type="radio" name="' + name + '" value="' + esc(o) + '"' + (locked ? ' disabled' : '') + '><span>' + esc(o) + '</span></label>';
				})
				.join('');
		}
		function addCard(d) {
			if (cards.length >= MAX_DRUGS) return null;
			d = d || {};
			seq++;
			var n = seq;
			var el = document.createElement('div');
			el.className = 'mns-ndr__card';
			el.innerHTML =
				'<div class="mns-ndr__card-head"><b data-mns-nd-card-title></b>' +
				(locked ? '' : '<button type="button" class="mns-ncl__rm" data-mns-nd-rm aria-label="حذف دارو">' + trashIcon + '</button>') +
				'</div>' +
				'<div class="mns-ndr__card-grid">' +
				'<label class="mns-ndr__txt"><span>نام دارو</span><input type="text" autocomplete="off" data-f="name"' + (locked ? ' disabled' : '') + '></label>' +
				'<label class="mns-ndr__txt"><span>دوز یا مقدار</span><input type="text" autocomplete="off" data-f="dose"' + (locked ? ' disabled' : '') + '></label>' +
				'<fieldset class="mns-ncl__field mns-ndr__f-route"><legend class="mns-ncl__label">راه مصرف</legend><div class="mns-ncl__chips">' + chipsHtml('route-' + n, routes) + '</div></fieldset>' +
				'<fieldset class="mns-ncl__field mns-ndr__f-needs"><legend class="mns-ncl__label">نسخه یا تأیید پزشک لازم است؟</legend><div class="mns-ncl__chips">' + chipsHtml('needs-' + n, ['خیر', 'بله']) + '</div>' +
				'<p class="mns-ncl__lead" data-mns-nd-needs-hint hidden>تأیید پزشک را در بخش «تأیید پزشک» همین صفحه و پیش از دادن دارو ثبت کنید.</p></fieldset>' +
				'<fieldset class="mns-ncl__field mns-ndr__f-status"><legend class="mns-ncl__label">وضعیت</legend><div class="mns-ncl__chips">' + chipsHtml('status-' + n, ['تجویز شد', 'تجویز نشد']) + '</div>' +
				'<p class="mns-ndr__forced" data-mns-nd-forced hidden>چون مشاوره تأیید نشد، وضعیت خودکار «تجویز نشد» است.</p></fieldset>' +
				'<div class="mns-ndr__f-photos"><p class="mns-ncl__label">عکس دارو <small class="mns-nrp__opt">الزامی؛ برچسب و نام دارو خوانا باشد</small></p>' +
				'<div class="mns-ndr__photos" data-max="3">' +
				(locked ? '' : '<label class="mns-nrp__pick" data-mns-nd-pick><input type="file" accept="image/*" multiple data-mns-nd-file><span><span aria-hidden="true">' + camIcon + '</span>عکس دارو را بگیرید یا انتخاب کنید</span></label>') +
				'<ul class="mns-nrp__thumbs" data-mns-nd-thumbs></ul></div></div>' +
				'<label class="mns-ndr__txt mns-ndr__f-note"><span>توضیح (اختیاری)</span><input type="text" autocomplete="off" data-f="note"' + (locked ? ' disabled' : '') + '></label>' +
				'</div>' +
				'<p class="mns-ncl__reason" data-mns-nd-card-reason role="alert"></p>';
			qs('[data-f="name"]', el).value = d.name || '';
			qs('[data-f="dose"]', el).value = d.dose || '';
			qs('[data-f="note"]', el).value = d.note || '';
			qsa('input[name="route-' + n + '"]', el).forEach(function (r) {
				r.checked = r.value === d.route;
			});
			qsa('input[name="needs-' + n + '"]', el).forEach(function (r) {
				r.checked = (d.needs === 'yes' && r.value === 'بله') || (d.needs === 'no' && r.value === 'خیر');
			});
			qsa('input[name="status-' + n + '"]', el).forEach(function (r) {
				r.checked = r.value === d.status;
			});
			var card = { el: el, photos: null };
			card.photos = makePhotos(qs('.mns-ndr__photos', el), 3, d.photos || [], changed);
			var rm = qs('[data-mns-nd-rm]', el);
			if (rm) {
				rm.addEventListener('click', function () {
					cards.splice(cards.indexOf(card), 1);
					el.parentNode.removeChild(el);
					changed();
				});
			}
			el.addEventListener('input', changed);
			el.addEventListener('change', changed);
			cards.push(card);
			listEl.appendChild(el);
			return card;
		}
		function readCard(card) {
			var el = card.el;
			var route = qs('input[name^="route-"]:checked', el);
			var status = qs('input[name^="status-"]:checked', el);
			var needs = qs('input[name^="needs-"]:checked', el);
			return {
				needs: needs ? (needs.value === 'بله' ? 'yes' : 'no') : '',
				name: qs('[data-f="name"]', el).value.trim(),
				dose: qs('[data-f="dose"]', el).value.trim(),
				note: qs('[data-f="note"]', el).value.trim(),
				route: route ? route.value : '',
				status: status ? status.value : '',
				photos: card.photos.count()
			};
		}
		function missingCard(card) {
			var d = readCard(card);
			var m = [];
			if (!d.name) m.push('نام دارو');
			if (!d.dose) m.push('دوز');
			if (!d.route) m.push('راه مصرف');
			if (!d.needs) m.push('نیاز به نسخه');
			if (!d.status) m.push('وضعیت');
			if (!d.photos) m.push('عکس دارو');
			return m;
		}
		if (addBtn) {
			addBtn.addEventListener('click', function () {
				var c = addCard({});
				changed();
				if (c) scrollToEl(c.el, 'center');
			});
		}

		/* ---- path ---- */
		qsa('input', pathField).forEach(function (r) {
			r.addEventListener('change', function () {
				st.path = r.value;
				changed();
			});
		});
		if (doctor) {
			doctor.value = cfg.rx_doctor || '';
			doctor.addEventListener('input', changed);
		}
		var rxPhotos = makePhotos(qs('[data-mns-nd-rxphotos]', root), 5, cfg.rx_photos || [], changed);

		/* ---- derived state ---- */
		function needNames() {
			return cards
				.map(function (c, i) {
					var r = readCard(c);
					return r.needs === 'yes' ? r.name || 'دارو ' + toFaDigits(i + 1) : '';
				})
				.filter(Boolean);
		}
		function anyNeeds() {
			return needNames().length > 0;
		}
		function pathComplete() {
			if (!anyNeeds()) return true;
			if (st.path === 'rx') return rxPhotos.count() > 0;
			if (st.path === 'consult') return consultFinal();
			return false;
		}
		function drugsComplete() {
			return cards.length > 0 && cards.every(function (c) {
				return missingCard(c).length === 0;
			});
		}
		function render() {
			var final = consultFinal();
			var denied = st.consult.result === 'denied';
			var needs = anyNeeds();
			if (secPath) secPath.hidden = !needs;
			var listText = qs('[data-mns-nd-needs-list]', root);
			if (listText) listText.textContent = needs ? 'داروهای نیازمند نسخه یا تأیید: ' + needNames().join('، ') : '';
			qsa('input', pathField).forEach(function (r) {
				r.checked = r.value === st.path;
				r.disabled = locked || final;
			});
			if (rxBlock) rxBlock.hidden = st.path !== 'rx';
			if (consultBlock) consultBlock.hidden = st.path !== 'consult';
			if (consultForm) consultForm.hidden = final || locked;
			if (finalEl) finalEl.hidden = !final;
			if (denyEl) denyEl.hidden = !denied;
			var callBtn = qs('.mns-ndr__call', root);
			if (callBtn) callBtn.hidden = final;
			if (banner) banner.hidden = !(denied && needs);
			if (attemptsEl) {
				attemptsEl.hidden = !st.consult.attempts.length || final;
				if (attemptsList) {
					attemptsList.innerHTML = '';
					st.consult.attempts.forEach(function (t, i) {
						attemptsList.appendChild(autoRow('تلاش ' + toFaDigits(i + 1), 'بدون پاسخ · ' + t));
					});
				}
			}
			if (finalRows) {
				finalRows.innerHTML = '';
				if (final) {
					finalRows.appendChild(autoRow('نتیجهٔ مشاوره', denied ? 'تأیید نشد' : 'تأیید شد', denied ? 'is-bad' : 'is-ok'));
					finalRows.appendChild(autoRow('زمان ثبت', 'امروز ' + st.consult.time));
					if (st.consult.note) finalRows.appendChild(autoRow('توضیح پزشک', st.consult.note));
				}
			}
			cards.forEach(function (c, i) {
				qs('[data-mns-nd-card-title]', c.el).textContent = 'دارو ' + toFaDigits(i + 1);
				var statusRadios = qsa('input[name^="status-"]', c.el);
				var forced = qs('[data-mns-nd-forced]', c.el);
				var cardNeeds = readCard(c).needs === 'yes';
				var hint = qs('[data-mns-nd-needs-hint]', c.el);
				if (hint) hint.hidden = !(cardNeeds && !pathComplete());
				if (denied && cardNeeds) {
					statusRadios.forEach(function (r) {
						r.checked = r.value === 'تجویز نشد';
						r.disabled = true;
					});
				} else {
					statusRadios.forEach(function (r) {
						r.disabled = locked;
					});
				}
				if (forced) forced.hidden = !(denied && cardNeeds);
			});
			if (emptyEl) emptyEl.hidden = cards.length > 0;
			if (addBtn) addBtn.hidden = cards.length >= MAX_DRUGS;
			var pOk = pathComplete();
			var dOk = drugsComplete();
			if (secPath) secPath.classList.toggle('is-done', pOk && needs);
			if (secDrugs) secDrugs.classList.toggle('is-done', dOk);
			var left = (pOk ? 0 : 1) + (dOk ? 0 : 1);
			if (progress) {
				progress.classList.toggle('is-complete', left === 0);
				progress.textContent = left === 0 ? 'همهٔ بخش‌ها کامل است' : toFaDigits(left) + ' بخش مانده';
			}
		}

		/* ---- start state ---- */
		(cfg.drugs || []).forEach(function (d) {
			addCard(d);
		});
		if (!cards.length && !locked) addCard({});
		render();

		/* ---- review + final submit ---- */
		var formView = qs('[data-mns-nd-formview]', root);
		var reviewView = qs('[data-mns-nd-reviewview]', root);
		var reviewBtn = qs('[data-mns-nd-review]', root);
		var finalBtn = qs('[data-mns-nd-final]', root);
		var editBtn = qs('[data-mns-nd-edit]', root);
		var confirmEl = qs('#mns-nd-confirm', root);

		function buildReview() {
			var host = qs('[data-mns-nd-summary]', root);
			host.innerHTML = '';
			function block(title) {
				var b = document.createElement('div');
				b.className = 'mns-ncl__rv-block';
				var h = document.createElement('p');
				h.className = 'mns-ncl__rv-h';
				h.textContent = title;
				var ul = document.createElement('ul');
				ul.className = 'mns-ncl__sum';
				b.appendChild(h);
				b.appendChild(ul);
				host.appendChild(b);
				return ul;
			}
			function line(ul, a, b) {
				var li = document.createElement('li');
				var x = document.createElement('span');
				var y = document.createElement('span');
				x.textContent = a;
				y.textContent = b;
				li.appendChild(x);
				li.appendChild(y);
				ul.appendChild(li);
			}
			cards.forEach(function (c, i) {
				var d = readCard(c);
				var u = block('دارو ' + toFaDigits(i + 1));
				line(u, 'نام و دوز', d.name + ' · ' + d.dose);
				line(u, 'راه مصرف', d.route);
				line(u, 'نسخه یا تأیید پزشک', d.needs === 'yes' ? 'لازم است' : 'لازم نیست');
				line(u, 'وضعیت', d.status);
				line(u, 'عکس دارو', toFaDigits(d.photos) + ' عکس');
				if (d.note) line(u, 'توضیح', d.note);
			});
			if (anyNeeds()) {
				var u1 = block('تأیید پزشک');
				if (st.path === 'rx') {
					line(u1, 'مسیر', 'نسخه دارد');
					line(u1, 'عکس نسخه', toFaDigits(rxPhotos.count()) + ' عکس');
					if (doctor && doctor.value.trim()) line(u1, 'نام پزشک', doctor.value.trim());
				} else {
					line(u1, 'مسیر', 'نسخه ندارد (مشاورهٔ پزشک)');
					line(u1, 'نتیجهٔ مشاوره', st.consult.result === 'denied' ? 'تأیید نشد' : 'تأیید شد');
					line(u1, 'زمان ثبت', 'امروز ' + st.consult.time);
					if (st.consult.note) line(u1, 'توضیح پزشک', st.consult.note);
				}
			}
			var br = qs('[data-mns-nd-banner-review]', root);
			if (br) br.hidden = !(st.consult.result === 'denied' && anyNeeds());
		}
		function showView(review) {
			setCollapse(formView, !review);
			setCollapse(reviewView, review);
			if (review) buildReview();
			if (confirmEl) setCollapse(confirmEl, false);
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
		if (reviewBtn) {
			reviewBtn.addEventListener('click', function () {
				/* something is still open: show exactly where */
				var pr = qs('[data-mns-nd-reason="path"]', root);
				var dr = qs('[data-mns-nd-reason="drugs"]', root);
				pr.textContent = '';
				dr.textContent = '';
				qsa('[data-mns-nd-card-reason]', root).forEach(function (p) {
					p.textContent = '';
				});
				if (!cards.length) {
					dr.textContent = 'حداقل یک دارو اضافه کنید.';
					scrollToEl(secDrugs, 'start');
					return;
				}
				for (var i = 0; i < cards.length; i++) {
					var m = missingCard(cards[i]);
					if (m.length) {
						qs('[data-mns-nd-card-reason]', cards[i].el).textContent = 'هنوز مانده: ' + m.join('، ');
						flash(cards[i].el);
						scrollToEl(cards[i].el, 'center');
						return;
					}
				}
				if (!pathComplete()) {
					pr.textContent = !st.path
						? 'مسیر تأیید را انتخاب کنید: نسخه یا مشاورهٔ پزشک.'
						: st.path === 'rx'
						? 'حداقل یک عکس نسخه لازم است.'
						: 'نتیجهٔ مشاوره («تأیید شد» یا «تأیید نشد») هنوز ثبت نشده است. «پاسخ نداد» کافی نیست.';
					flash(pathField);
					scrollToEl(secPath, 'start');
					return;
				}
				showView(true);
			});
		}
		if (editBtn) editBtn.addEventListener('click', function () {
			showView(false);
		});
		if (finalBtn) finalBtn.addEventListener('click', function () {
			setCollapse(confirmEl, true);
			scrollToEl(confirmEl, 'center');
		});
		if (confirmEl) {
			var ok = qs('[data-mns-confirm-ok]', confirmEl);
			var cancel = qs('[data-mns-confirm-cancel]', confirmEl);
			var err = qs('[data-mns-confirm-error]', confirmEl);
			if (cancel) {
				cancel.addEventListener('click', function () {
					setCollapse(confirmEl, false);
				});
			}
			if (ok) {
				ok.addEventListener('click', function () {
					if (ok.classList.contains('is-loading')) return;
					if (err) err.textContent = '';
					if (navigator.onLine === false) {
						if (err) err.textContent = 'ثبت نشد، دوباره تلاش کنید.';
						return;
					}
					ok.classList.add('is-loading');
					ok.disabled = true;
					/* TODO(backend): one-time key; the server validates, uploads the files, locks. */
					window.setTimeout(function () {
						dirty = false;
						window.location.href = ok.getAttribute('data-mns-go') || window.location.href;
					}, 900);
				});
			}
		}
	}

	/* Nurse "schedule and access" (P-06/07): status that follows the accept switch, the nurse's own monthly schedule (day by day, slot by slot)
	   and the working regions across Iran (two searchable dropdowns). */
	function initNurseSchedule() {
		var root = qs('[data-mns-nvl]');
		if (!root) return;
		var cfg = {};
		try {
			cfg = JSON.parse(root.getAttribute('data-config') || '{}') || {};
		} catch (e) {
			cfg = {};
		}
		var xIcon = (qs('[data-mns-nvl-x]', root) || {}).innerHTML || '×';
		var lockIcon = (qs('[data-mns-nvl-lock]', root) || {}).innerHTML || '';
		var monthNames = cfg.monthNames || [];

		function setCollapse(el, open) {
			if (!el) return;
			el.classList.toggle('is-open', open);
			if ('inert' in el) el.inert = !open;
		}
		function mk(tag, cls, text) {
			var e = document.createElement(tag);
			if (cls) e.className = cls;
			if (text != null) e.textContent = text;
			return e;
		}
		function flash(el) {
			if (!el) return;
			el.classList.add('is-flash');
			window.setTimeout(function () {
				el.classList.remove('is-flash');
			}, 1600);
		}
		function scrollToEl(el, block) {
			window.setTimeout(function () {
				if (el && el.scrollIntoView) el.scrollIntoView({ block: block || 'start', behavior: 'smooth' });
			}, 120);
		}

		/* ---------------------------------------------------------------- status */
		var accept = qs('[data-mns-accept]', root);
		var statusEl = qs('[data-mns-nvl-status]', root);
		var statusText = qs('[data-mns-nvl-status-text]', root);
		var statusSub = qs('[data-mns-nvl-status-sub]', root);
		function paintStatus() {
			var on = !accept || accept.classList.contains('is-on');
			var s = on ? (cfg.statuses || {})[cfg.case] : { tone: 'warn', text: cfg.pausedText, sub: cfg.pausedSub };
			if (!s || !statusEl) return;
			statusEl.className = 'mns-nvl__status is-' + s.tone;
			statusText.textContent = s.text;
			statusSub.textContent = s.sub;
		}
		if (accept && window.MutationObserver) {
			new MutationObserver(paintStatus).observe(accept, { attributes: true, attributeFilter: ['class'] });
		}
		paintStatus();

		/* nothing is saved while editing: leaving with changes asks first */
		var scDirty = false;
		var rgDirty = false;
		window.addEventListener('beforeunload', function (e) {
			if (scDirty || rgDirty) {
				e.preventDefault();
				e.returnValue = '';
			}
		});

		/* ---------------------------------------------------------------- monthly schedule */
		var slots = cfg.slots || [];
		var days = cfg.days || [];
		var byIso = {};
		var idxOf = {};
		var cur = {};
		var saved = {};
		days.forEach(function (d, i) {
			byIso[d.iso] = d;
			idxOf[d.iso] = i;
			cur[d.iso] = d.on.slice();
			saved[d.iso] = d.on.slice();
		});
		var months = [];
		days.forEach(function (d) {
			var k = d.y * 100 + d.m;
			if (months.indexOf(k) === -1) months.push(k);
		});
		var mIdx = 0;
		var selFrom = '';
		var selTo = '';
		var grid = qs('[data-mns-sc-grid]', root);
		var title = qs('[data-mns-sc-title]', root);
		var prevBtn = qs('[data-mns-sc-prev]', root);
		var nextBtn = qs('[data-mns-sc-next]', root);
		var panel = qs('[data-mns-sc-panel]', root);
		var pTitle = qs('[data-mns-sc-ptitle]', root);
		var slotsEl = qs('[data-mns-sc-slots]', root);
		var lockNote = qs('[data-mns-sc-lock]', root);
		var hint = qs('[data-mns-sc-hint]', root);
		var bar = qs('[data-mns-sc-bar]', root);
		var barText = qs('[data-mns-sc-bartext]', root);
		var scError = qs('[data-mns-sc-error]', root);
		var scDone = qs('[data-mns-sc-done]', root);
		var scBusy = false;

		function ordered(list) {
			return slots
				.filter(function (s) {
					return list.indexOf(s.id) > -1;
				})
				.map(function (s) {
					return s.id;
				});
		}
		function same(a, b) {
			return a.length === b.length && a.every(function (x) {
				return b.indexOf(x) > -1;
			});
		}
		function isOn(iso, id) {
			return cur[iso].indexOf(id) > -1;
		}
		function dayLabel(iso) {
			var d = byIso[iso];
			return d ? toFaDigits(d.d) + ' ' + monthNames[d.m - 1] : iso;
		}
		function selDays() {
			if (!selFrom) return [];
			var i = idxOf[selFrom];
			var j = idxOf[selTo || selFrom];
			if (i > j) {
				var t = i;
				i = j;
				j = t;
			}
			return days.slice(i, j + 1).filter(function (d) {
				return !d.past;
			});
		}
		function changedDays() {
			return days.filter(function (d) {
				return !d.past && !same(cur[d.iso], saved[d.iso]);
			});
		}
		function renderCal() {
			var k = months[mIdx];
			var y = Math.floor(k / 100);
			var m = k % 100;
			title.textContent = monthNames[m - 1] + ' ' + toFaDigits(y);
			prevBtn.disabled = mIdx === 0;
			nextBtn.disabled = mIdx === months.length - 1;
			grid.innerHTML = '';
			var sel = {};
			selDays().forEach(function (d) {
				sel[d.iso] = true;
			});
			var monthDays = days.filter(function (d) {
				return d.y === y && d.m === m;
			});
			for (var b = 0; b < monthDays[0].dow; b++) grid.appendChild(mk('span', 'mns-nvl__blank'));
			monthDays.forEach(function (d) {
				var btn = mk('button', 'mns-nvl__cd');
				btn.type = 'button';
				btn.disabled = d.past;
				var on = cur[d.iso];
				btn.classList.toggle('is-past', d.past);
				btn.classList.toggle('is-today', d.today);
				btn.classList.toggle('is-sel', !!sel[d.iso]);
				btn.classList.toggle('is-off', !d.past && on.length === 0);
				btn.classList.toggle('has-order', d.orders.length > 0);
				btn.classList.toggle('is-changed', !d.past && !same(cur[d.iso], saved[d.iso]));
				btn.appendChild(mk('span', 'mns-nvl__cdn', toFaDigits(d.d)));
				var bars = mk('span', 'mns-nvl__bars');
				slots.forEach(function (s) {
					bars.appendChild(mk('i', on.indexOf(s.id) > -1 ? 'is-on' : ''));
				});
				btn.appendChild(bars);
				btn.setAttribute('aria-pressed', sel[d.iso] ? 'true' : 'false');
				btn.setAttribute('aria-label', toFaDigits(d.d) + ' ' + monthNames[d.m - 1] + ' · ' + (on.length ? toFaDigits(on.length) + ' بازه در دسترس' : 'نیستم') + (d.orders.length ? ' · سفارش دارد' : ''));
				btn.addEventListener('click', function () {
					scError.textContent = '';
					scDone.hidden = true;
					if (!selFrom || selTo) {
						selFrom = d.iso;
						selTo = '';
					} else if (d.iso < selFrom) {
						selTo = selFrom;
						selFrom = d.iso;
					} else {
						selTo = d.iso;
					}
					renderAll();
					if (!panel.classList.contains('is-open')) scrollToEl(panel, 'nearest');
				});
				grid.appendChild(btn);
			});
		}
		/* slot icons: the sun's place on its path across the sky (sunrise -> noon -> sunset), the moon at night */
		var svgOpen = '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false">';
		var skyBase = '<path d="M3 19h18" /><path d="M4 19a8 8 0 0 1 16 0" stroke-dasharray="1.2 2.6" opacity=".55" />';
		var sunAt = function (x, y) {
			return '<circle cx="' + x + '" cy="' + y + '" r="2.6" fill="currentColor" />';
		};
		var slotIcons = {
			'4-8': skyBase + sunAt(4.6, 15.6),
			'8-12': skyBase + sunAt(8.1, 10.8),
			'12-16': skyBase + sunAt(15.9, 10.8),
			'16-20': skyBase + sunAt(19.4, 15.6),
			'20-24': '<path d="M20 14.2A8 8 0 1 1 9.8 4a6.4 6.4 0 0 0 10.2 10.2z" fill="currentColor" /><path d="M18.5 4v3M17 5.5h3" />'
		};
		function slotIcon(id) {
			return svgOpen + (slotIcons[id] || skyBase) + '</svg>';
		}
		var iconCheck = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg>';
		var iconZzz = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M4 3.5h6.5L4 11h6.5" /><path d="M13 11.5h5L13 17.5h5" /><path d="M18.5 18.5h3l-3 3.5h3" /></svg>';
		var iconDash = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" focusable="false"><path d="M6 12h12" /></svg>';
		function renderPanel() {
			var sel = selDays();
			setCollapse(panel, sel.length > 0);
			hint.hidden = sel.length > 0;
			if (!sel.length) return;
			pTitle.textContent = sel.length === 1 ? dayLabel(sel[0].iso) : dayLabel(sel[0].iso) + ' تا ' + dayLabel(sel[sel.length - 1].iso) + ' (' + toFaDigits(sel.length) + ' روز)';
			slotsEl.innerHTML = '';
			var anyLocked = false;
			slots.forEach(function (s) {
				var onCount = sel.filter(function (d) {
					return isOn(d.iso, s.id);
				}).length;
				var state = onCount === sel.length ? 'on' : onCount === 0 ? 'off' : 'mixed';
				var locked = sel.some(function (d) {
					return d.orders.indexOf(s.id) > -1;
				});
				if (locked) anyLocked = true;
				var chip = mk('button', 'mns-nvl__sl is-' + state + (locked ? ' is-locked' : ''));
				chip.type = 'button';
				chip.setAttribute('aria-pressed', state === 'on' ? 'true' : 'false');
				var stText = locked ? 'سفارش دارد' : state === 'on' ? 'در دسترس' : state === 'off' ? 'نیستم' : 'متفاوت';
				chip.setAttribute('aria-label', s.name + '، ' + s.label + '، ' + stText);
				chip.title = s.name;
				var top = mk('span', 'mns-nvl__sltop');
				var ic = mk('span', 'mns-nvl__slic');
				ic.setAttribute('aria-hidden', 'true');
				ic.innerHTML = slotIcon(s.id);
				var hrs = mk('small', '', s.label);
				hrs.setAttribute('dir', 'ltr');
				top.appendChild(ic);
				top.appendChild(hrs);
				var st = mk('span', 'mns-nvl__slst');
				var sti = mk('span', 'mns-nvl__slsti');
				sti.setAttribute('aria-hidden', 'true');
				sti.innerHTML = locked ? '' : state === 'on' ? iconCheck : state === 'off' ? iconZzz : iconDash;
				st.appendChild(sti);
				st.appendChild(document.createTextNode(stText));
				if (locked) {
					var li = mk('span', 'mns-nvl__sllock');
					li.innerHTML = lockIcon;
					chip.appendChild(li);
				}
				chip.appendChild(top);
				chip.appendChild(st);
				chip.addEventListener('click', function () {
					var target = state === 'on' ? 'off' : 'on';
					sel.forEach(function (d) {
						var list = cur[d.iso].slice();
						var p = list.indexOf(s.id);
						if (target === 'on' && p === -1) list.push(s.id);
						if (target === 'off' && p > -1 && d.orders.indexOf(s.id) === -1) list.splice(p, 1);
						cur[d.iso] = ordered(list);
					});
					afterEdit();
				});
				slotsEl.appendChild(chip);
			});
			lockNote.hidden = !anyLocked;
		}
		function renderBar() {
			var n = changedDays().length;
			scDirty = n > 0;
			bar.hidden = n === 0;
			barText.textContent = toFaDigits(n) + ' روز تغییر کرده؛ هنوز ذخیره نشده است.';
		}
		function renderAll() {
			renderCal();
			renderPanel();
			renderBar();
		}
		function afterEdit() {
			scDone.hidden = true;
			scError.textContent = '';
			renderAll();
		}
		function applyToSel(fn) {
			selDays().forEach(function (d) {
				cur[d.iso] = ordered(fn(d));
			});
			afterEdit();
		}
		prevBtn.addEventListener('click', function () {
			if (mIdx > 0) {
				mIdx--;
				renderCal();
			}
		});
		nextBtn.addEventListener('click', function () {
			if (mIdx < months.length - 1) {
				mIdx++;
				renderCal();
			}
		});
		qs('[data-mns-sc-close]', root).addEventListener('click', function () {
			selFrom = '';
			selTo = '';
			renderAll();
		});
		qs('[data-mns-sc-allon]', root).addEventListener('click', function () {
			applyToSel(function () {
				return slots.map(function (s) {
					return s.id;
				});
			});
		});
		qs('[data-mns-sc-alloff]', root).addEventListener('click', function () {
			applyToSel(function (d) {
				return d.orders.slice();
			});
		});
		qs('[data-mns-sc-reset]', root).addEventListener('click', function () {
			applyToSel(function (d) {
				return d.base.concat(d.orders);
			});
		});
		qs('[data-mns-sc-undo]', root).addEventListener('click', function () {
			days.forEach(function (d) {
				cur[d.iso] = saved[d.iso].slice();
			});
			afterEdit();
		});
		qs('[data-mns-sc-save]', root).addEventListener('click', function () {
			if (scBusy) return;
			var btn = qs('[data-mns-sc-save]', root);
			scError.textContent = '';
			if (navigator.onLine === false) {
				scError.textContent = 'ثبت نشد، اتصال برقرار نیست.';
				return;
			}
			scBusy = true;
			btn.classList.add('is-loading');
			btn.disabled = true;
			/* TODO(backend): store the day overrides; the server re-checks that a slot holding an order stays on (row lock on the nurse). */
			window.setTimeout(function () {
				scBusy = false;
				btn.classList.remove('is-loading');
				btn.disabled = false;
				days.forEach(function (d) {
					saved[d.iso] = cur[d.iso].slice();
				});
				renderAll();
				scDone.hidden = false;
			}, 800);
		});
		renderAll();

		/* ---------------------------------------------------------------- regions: two searchable dropdowns */
		var rg = qs('[data-mns-rg]', root);
		if (!rg) return;
		var provSel = qs('[data-mns-rg-prov]', rg);
		var citySel = qs('[data-mns-rg-city]', rg);
		var addBtn = qs('[data-mns-rg-add]', rg);
		var addAllBtn = qs('[data-mns-rg-addall]', rg);
		var addReason = qs('[data-mns-rg-addreason]', rg);
		var selH = qs('[data-mns-rg-sel-h]', rg);
		var groupsEl = qs('[data-mns-rg-groups]', rg);
		var distEl = qs('[data-mns-rg-districts]', rg);
		var noneEl = qs('[data-mns-rg-none]', rg);
		var warnEl = qs('[data-mns-rg-warn]', rg);
		var warnText = qs('[data-mns-rg-warn-text]', rg);
		var reasonEl = qs('[data-mns-rg-reason]', rg);
		var saveBtn = qs('[data-mns-rg-save]', rg);
		var doneEl = qs('[data-mns-rg-done]', rg);
		var distMap = cfg.districts || {};
		var orderMap = cfg.regionOrders || {};
		var data = null;
		var provByCode = {};
		var provNames = {};
		qsa('option', provSel).forEach(function (o) {
			if (o.value) provNames[o.value] = o.textContent;
		});
		var sel = (cfg.regions || []).map(function (r) {
			return { prov: r.prov, city: r.city, d: (r.districts || []).slice() };
		});

		function canon(list) {
			return list
				.map(function (s) {
					return { prov: s.prov, city: s.city, d: s.d.slice().sort(function (a, b) { return a - b; }) };
				})
				.sort(function (a, b) {
					return a.prov + a.city < b.prov + b.city ? -1 : 1;
				});
		}
		var baseline = JSON.stringify(canon(sel));
		var baselineSel = JSON.parse(baseline);
		function keyOf(prov, city) {
			return prov + '|' + city;
		}
		function findSel(prov, city) {
			for (var i = 0; i < sel.length; i++) if (sel[i].prov === prov && sel[i].city === city) return i;
			return -1;
		}
		function provCityCount(code) {
			return provByCode[code] ? provByCode[code].cities.length : 0;
		}
		function remove(prov, city) {
			var i = findSel(prov, city);
			if (i > -1) sel.splice(i, 1);
			update();
		}

		function renderSelected() {
			var provSet = {};
			var cities = 0;
			sel.forEach(function (s) {
				provSet[s.prov] = true;
				cities += s.city === '*' ? provCityCount(s.prov) || 1 : 1;
			});
			var np = Object.keys(provSet).length;
			selH.textContent = sel.length ? 'مناطق فعالیت شما · ' + toFaDigits(np) + ' استان، ' + toFaDigits(cities) + ' شهر' : 'هنوز شهری اضافه نکرده‌اید.';
			groupsEl.innerHTML = '';
			Object.keys(provSet).forEach(function (code) {
				var g = mk('div', 'mns-nvl__grp');
				g.appendChild(mk('b', 'mns-nvl__grpname', provNames[code] || code));
				var chips = mk('div', 'mns-nvl__chips');
				sel.filter(function (s) {
					return s.prov === code;
				}).forEach(function (s) {
					var chip = mk('span', 'mns-nvl__chip' + (s.city === '*' ? ' is-all' : ''));
					var label = s.city === '*' ? 'همهٔ شهرها' + (provCityCount(code) ? ' (' + toFaDigits(provCityCount(code)) + ')' : '') : s.city + (distMap[keyOf(s.prov, s.city)] && s.d.length ? ' · ' + toFaDigits(s.d.length) + ' منطقه' : '');
					chip.appendChild(mk('span', '', label));
					var x = mk('button', 'mns-nvl__chip-x');
					x.type = 'button';
					x.setAttribute('aria-label', 'حذف ' + (s.city === '*' ? 'همهٔ شهرهای ' + (provNames[code] || '') : s.city));
					x.innerHTML = xIcon;
					x.addEventListener('click', function () {
						remove(s.prov, s.city);
					});
					chip.appendChild(x);
					chips.appendChild(chip);
					var dcard = s.city === '*' ? null : buildDistrictCard(s);
					if (dcard) chips.appendChild(dcard);
				});
				g.appendChild(chips);
				groupsEl.appendChild(g);
			});
		}
		/* the districts of a chosen city: a card that sits right under that city's chip */
		function buildDistrictCard(s) {
			var names = distMap[keyOf(s.prov, s.city)];
			if (!names) return null;
			{
				var card = mk('div', 'mns-nvl__dcard');
				card.setAttribute('data-mns-rg-dcard', keyOf(s.prov, s.city));
				var head = mk('div', 'mns-nvl__dhead');
				head.appendChild(mk('b', '', 'مناطق ' + s.city));
				head.appendChild(mk('small', '', toFaDigits(s.d.length) + ' از ' + toFaDigits(names.length)));
				card.appendChild(head);
				var acts = mk('div', 'mns-nvl__dacts');
				var all = mk('button', 'mns-nvl__link', 'انتخاب همه');
				all.type = 'button';
				all.addEventListener('click', function () {
					s.d = names.map(function (n, i) {
						return i + 1;
					});
					update();
				});
				var clr = mk('button', 'mns-nvl__link', 'پاک کردن');
				clr.type = 'button';
				clr.addEventListener('click', function () {
					s.d = [];
					update();
				});
				acts.appendChild(all);
				acts.appendChild(clr);
				card.appendChild(acts);
				var chips = mk('div', 'mns-ncl__chips');
				names.forEach(function (n, i) {
					var num = i + 1;
					var label = mk('label', 'mns-ncl__chip');
					var inp = document.createElement('input');
					inp.type = 'checkbox';
					inp.checked = s.d.indexOf(num) > -1;
					inp.addEventListener('change', function () {
						var p = s.d.indexOf(num);
						if (p > -1) s.d.splice(p, 1);
						else s.d.push(num);
						update();
					});
					label.appendChild(inp);
					label.appendChild(mk('span', '', toFaDigits(num)));
					label.title = n;
					chips.appendChild(label);
				});
				card.appendChild(chips);
				if (!s.d.length) {
					card.classList.add('is-warn');
					card.appendChild(mk('p', 'mns-nvl__dwarn', 'حداقل یک منطقه انتخاب کنید.'));
				}
				return card;
			}
		}
		function renderDistricts() {
			distEl.innerHTML = '';
		}
		function removedOrders() {
			var msgs = [];
			var total = 0;
			baselineSel.forEach(function (b) {
				if (b.city === '*') return;
				var i = findSel(b.prov, b.city);
				var hasDistricts = !!distMap[keyOf(b.prov, b.city)];
				if (i === -1 || hasDistricts) {
					if (hasDistricts) {
						b.d.forEach(function (num) {
							if (i === -1 || sel[i].d.indexOf(num) === -1) {
								var c = orderMap[keyOf(b.prov, b.city) + '|' + num] || 0;
								if (c) {
									total += c;
									msgs.push('منطقه ' + toFaDigits(num) + ' ' + b.city);
								}
							}
						});
					} else {
						var c2 = orderMap[keyOf(b.prov, b.city)] || 0;
						if (c2) {
							total += c2;
							msgs.push(b.city);
						}
					}
				}
			});
			return total ? toFaDigits(total) + ' سفارش تخصیص‌یافته در ' + msgs.join('، ') + ' دارید. حذف منطقه روی آن‌ها اثری ندارد.' : '';
		}
		function update() {
			renderSelected();
			renderDistricts();
			noneEl.hidden = sel.length > 0;
			rgDirty = JSON.stringify(canon(sel)) !== baseline;
			saveBtn.hidden = !rgDirty;
			doneEl.hidden = true;
			reasonEl.textContent = '';
			var w = rgDirty ? removedOrders() : '';
			warnEl.hidden = !w;
			warnText.textContent = w;
		}

		/* the two dropdowns */
		function loadLocations(cb) {
			if (data) {
				cb();
				return;
			}
			fetch(cfg.locationsUrl)
				.then(function (r) {
					if (!r.ok) throw new Error('http');
					return r.json();
				})
				.then(function (d) {
					data = d;
					d.provinces.forEach(function (p) {
						provByCode[p.code] = p;
					});
					cb();
					renderSelected();
				})
				.catch(function () {
					citySel.innerHTML = '<option value="">فهرست شهرها بارگذاری نشد؛ صفحه را دوباره باز کنید</option>';
				});
		}
		provSel.addEventListener('change', function () {
			addReason.textContent = '';
			var code = provSel.value;
			citySel.innerHTML = '<option value="">' + (code ? 'در حال بارگذاری…' : 'ابتدا استان را انتخاب کنید') + '</option>';
			citySel.disabled = !code;
			citySel.dispatchEvent(new Event('change'));
			if (!code) return;
			loadLocations(function () {
				if (provSel.value !== code) return;
				citySel.innerHTML = '<option value="">انتخاب کنید</option>';
				provByCode[code].cities.forEach(function (name) {
					var o = document.createElement('option');
					o.value = name;
					o.textContent = name;
					citySel.appendChild(o);
				});
				citySel.disabled = false;
				citySel.dispatchEvent(new Event('change'));
			});
		});
		function resetCity() {
			citySel.value = '';
			citySel.dispatchEvent(new Event('change'));
		}
		addBtn.addEventListener('click', function () {
			addReason.textContent = '';
			var code = provSel.value;
			var city = citySel.value;
			if (!code) {
				addReason.textContent = 'اول استان را انتخاب کنید.';
				return;
			}
			if (!city) {
				addReason.textContent = 'شهر را انتخاب کنید.';
				return;
			}
			if (findSel(code, '*') > -1) {
				addReason.textContent = 'همهٔ شهرهای این استان قبلاً اضافه شده است.';
				return;
			}
			if (findSel(code, city) > -1) {
				addReason.textContent = 'این شهر قبلاً اضافه شده است.';
				return;
			}
			sel.push({ prov: code, city: city, d: [] });
			resetCity();
			update();
		});
		addAllBtn.addEventListener('click', function () {
			addReason.textContent = '';
			var code = provSel.value;
			if (!code) {
				addReason.textContent = 'اول استان را انتخاب کنید.';
				return;
			}
			sel = sel.filter(function (s) {
				return s.prov !== code;
			});
			sel.push({ prov: code, city: '*', d: [] });
			resetCity();
			update();
		});
		saveBtn.addEventListener('click', function () {
			if (saveBtn.classList.contains('is-loading')) return;
			reasonEl.textContent = '';
			var missing = sel.filter(function (s) {
				return distMap[keyOf(s.prov, s.city)] && !s.d.length;
			})[0];
			if (missing) {
				reasonEl.textContent = 'برای ' + missing.city + ' حداقل یک منطقه انتخاب کنید.';
				flash(qs('[data-mns-rg-dcard="' + keyOf(missing.prov, missing.city) + '"]', rg));
				return;
			}
			if (navigator.onLine === false) {
				reasonEl.textContent = 'ثبت نشد، اتصال برقرار نیست.';
				return;
			}
			saveBtn.classList.add('is-loading');
			saveBtn.disabled = true;
			/* TODO(backend): replace the nurse's regions; assigned orders keep their nurse; reject cities/districts the admin switched off. */
			window.setTimeout(function () {
				saveBtn.classList.remove('is-loading');
				saveBtn.disabled = false;
				baseline = JSON.stringify(canon(sel));
				baselineSel = JSON.parse(baseline);
				rgDirty = false;
				saveBtn.hidden = true;
				warnEl.hidden = true;
				doneEl.hidden = false;
			}, 800);
		});
		update();
		loadLocations(function () {});
	}

	function initLiveSearch() {
		qsa('[data-mns-live-search]').forEach(function (form) {
			var input = qs('[data-mns-live-search-input]', form);
			var list = document.querySelector('[data-mns-live-search-list]');
			if (!input || !list) return;
			input.addEventListener('input', function () {
				var q = (input.value || '').trim().toLowerCase();
				qsa('.mns-list-item', list).forEach(function (li) {
					var hay = (li.textContent || '').toLowerCase();
					li.hidden = q !== '' && hay.indexOf(q) === -1;
				});
			});
		});
	}

	// WP Rocket / delayed JS often runs after DOMContentLoaded — boot either way.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
