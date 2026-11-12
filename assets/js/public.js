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
		 * Like ARP: only reparent the expand tab + backdrop to body.
		 * Keep the sidebar inside `.mns-dash` so desktop `width: 100%` stays
		 * in the 306px grid column (reparenting it broke desktop layout).
		 * Mobile stacking: `.mns-dash { isolation: auto }` under 768px.
		 */
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
			var onChange = function (e) {
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
				control.classList.toggle('is-filled', !!native.value);
			}

			function buildOptions() {
				menu.innerHTML = '';
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
				closeAllSelects(menu);
				buildOptions();
				menu.classList.add('is-open');
				control.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
			}

			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				if (menu.classList.contains('is-open')) closeAllSelects();
				else open();
			});
			control.addEventListener('click', function (e) {
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

	function initStars() {
		qsa('[data-mns-stars]').forEach(function (group) {
			qsa('button', group).forEach(function (btn, index) {
				btn.addEventListener('click', function () {
					qsa('button', group).forEach(function (b, i) {
						b.classList.toggle('is-on', i <= index);
					});
					group.setAttribute('data-value', String(index + 1));
					var name = group.getAttribute('data-name');
					if (name) {
						var hidden = qs('[data-mns-star-value="' + name + '"]');
						if (hidden) hidden.value = String(index + 1);
					}
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
		var cities = [];
		var availability = {};
		var dateLabel = '';
		var customDateLabel = '';

		try {
			cities = JSON.parse(form.getAttribute('data-mns-cities') || '[]') || [];
		} catch (e1) {
			cities = [];
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
			var cityOk = citySelect && citySelect.value;
			var areaOk = areaSelect && areaSelect.value;
			var cityLabel = cityOk && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '';
			var areaLabel = areaOk && areaSelect.options[areaSelect.selectedIndex] ? areaSelect.options[areaSelect.selectedIndex].text : '';
			if (elPlace) elPlace.textContent = cityOk && areaOk ? cityLabel + '، ' + areaLabel : 'در مرحله بعد';
			if (elNurse) elNurse.textContent = genderInput && genderInput.value ? genderNurseLabel(genderInput.value) : 'در مرحله بعد';
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
				if (!citySelect || !citySelect.value) return fail('شهر را انتخاب کنید.', citySelect);
				if (!areaSelect || !areaSelect.value) return fail('منطقه را انتخاب کنید.', areaSelect);
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
				time: whenSummary(),
				address: cityLabel + '، ' + areaLabel + (addr ? '، ' + addr : ''),
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

function fillAreas() {
			if (!areaSelect || !citySelect) return;
			var city = cities.filter(function (c) {
				return String(c.id) === String(citySelect.value);
			})[0];
			var areas = city && city.areas ? city.areas : [];
			areaSelect.innerHTML = '<option value="">' + 'انتخاب کنید' + '</option>';
			areas.forEach(function (a) {
				var opt = document.createElement('option');
				opt.value = a;
				opt.textContent = a;
				areaSelect.appendChild(opt);
			});
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
			var area = areaSelect && areaSelect.value ? areaSelect.value : '';
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
			var area = areaSelect && areaSelect.value ? areaSelect.value : '';
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
					cityLabel +
					'، ' +
					areaLabel +
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

	/* Delegated so rows swapped in by the orders list refresh keep working. */
	/* Generic fold: a button with aria-controls opens/closes its panel (CSS animates grid rows; inert keeps it out of tab order). */
	function initCollapseToggles() {
		qsa('[data-mns-collapse-toggle]').forEach(function (btn) {
			var panel = document.getElementById(btn.getAttribute('aria-controls') || '');
			if (!panel) return;
			btn.addEventListener('click', function () {
				var open = btn.getAttribute('aria-expanded') !== 'true';
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				panel.classList.toggle('is-open', open);
				if ('inert' in panel) panel.inert = !open;
			});
		});
	}

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
		initLiveSearch();
		initOrdersList();
		initInvoiceView();
		initNurseDash();
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
