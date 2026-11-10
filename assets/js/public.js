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
					li.addEventListener('click', function () {
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
				if (menu.contains(e.target)) return;
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
		var maxStep = 4;
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
		var qtyInput = qs('[data-mns-serum-qty]', form);
		var ageGroupInput = qs('[data-mns-age-group]', form);
		var patGenderInput = qs('[data-mns-patient-gender]', form);
		var step2Next = qs('[data-mns-step-2-next]', form);
		var availBox = qs('[data-mns-avail]', form);
		var availGenderBox = qs('[data-mns-avail-gender]', form);
		var pickEdit = qs('[data-mns-pick-edit]', form);
		var pickSummary = qs('[data-mns-pick-summary]', form);
		var pickChange = qs('[data-mns-pick-change]', form);
		var DRAFT_KEY = 'mns_order_wizard_draft_v2';
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
				var showDetail = step >= 3 && serumTypeInput && serumTypeInput.value;
				if (showDetail) elDetail.textContent = serviceDetailSummary();
				elDetailRow.hidden = !showDetail;
				elDetailRow.classList.toggle('is-hidden', !showDetail);
			}
			if (factors) {
				factors.hidden = step < 4;
				factors.classList.toggle('is-hidden', step < 4);
			}
			var otpPhone = qs('[data-mns-otp-phone]', form);
			var phone = qs('input[name="phone"]', form);
			if (otpPhone && phone) otpPhone.textContent = phone.value || '';
		}

		function goStep(n) {
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
			if (step === 4) fillReview();
			syncMobileBar();
			saveDraft();
			if (wizardReady) {
				var anchor = qs('[data-mns-stepper]', wizard || document) || qs('[data-mns-step].is-active', form);
				if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
			var leadKey = n === 5 ? 'done' : n;
			if (leadEl && leads[leadKey]) leadEl.textContent = leads[leadKey];
		}

		function syncMobileBar() {
			var mobBtn = qs('[data-mns-mob-continue]');
			if (!mobBtn) return;
			var label = qs('[data-mns-mob-label]', mobBtn);
			var svc = qs('[data-mns-mob-svc]');
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

		function validateStep(n) {
			hideNotice();
			if (n === 1) {
				var failStep1 = function (msg) {
					setPickOpen(true); /* the field the message talks about may be folded away */
					showNoticeNear(form, msg, true);
					return false;
				};
				var mode = modeInput ? modeInput.value : 'self';
				if (mode === 'new') {
					var nm = qs('input[name="new_patient_name"]', form);
					if (!nm || !nm.value.trim()) return failStep1('نام بیمار را وارد کنید.');
					if (!ageGroupInput || !ageGroupInput.value) return failStep1('گروه سنی را انتخاب کنید.');
					if (!patGenderInput || !patGenderInput.value) return failStep1('جنسیت بیمار را انتخاب کنید.');
				}
				if (mode === 'saved' && (!patientIdInput || !patientIdInput.value)) return failStep1('بیمار را انتخاب کنید.');
				var phone = qs('input[name="phone"]', form);
				if (!phone || !phone.value || phone.value.replace(/\s/g, '').length < 11) return failStep1('شماره تماس معتبر وارد کنید.');
			}
if (n === 2) {
				if (availState === 'red') {
					showNoticeNear(form, 'برای این منطقه و جنسیت پرستار نداریم.', true);
					return false;
				}
				if (!citySelect || !citySelect.value || !areaSelect || !areaSelect.value) {
					showNoticeNear(form, 'شهر و منطقه را انتخاب کنید.', true);
					return false;
				}
				if (!dateValue || !dateValue.value) {
					showNoticeNear(form, 'تاریخ مراجعه را انتخاب کنید.', true);
					return false;
				}
				if (!timeValue || !timeValue.value) {
					showNoticeNear(form, 'بازه زمانی را انتخاب کنید.', true);
					return false;
				}
				if (!genderInput || !genderInput.value) {
					showNoticeNear(form, 'جنسیت پرستار را انتخاب کنید.', true);
					return false;
				}
				var addr = qs('textarea[name="address"]', form) || qs('input[name="address"]', form);
				if (!addr || !addr.value.trim()) {
					showNoticeNear(form, 'آدرس را کامل‌تر بنویسید.', true);
					return false;
				}
			}
if (n === 3) {
				if (!serumTypeInput || !serumTypeInput.value) {
					showNoticeNear(form, 'نوع سرم را انتخاب کنید.', true);
					return false;
				}
				if (!veinInput || !veinInput.value) {
					showNoticeNear(form, 'دسترسی رگ را انتخاب کنید.', true);
					return false;
				}
				if (!allergyInput || !allergyInput.value) {
					showNoticeNear(form, 'وضعیت حساسیت را انتخاب کنید.', true);
					return false;
				}
			}
			return true;
		}

		function fillReview() {
			var box = qs('[data-mns-review]', form);
			if (!box) return;
			var phone = (qs('input[name="phone"]', form) || {}).value || '';
			var cityLabel = citySelect && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '—';
			var areaLabel = areaSelect && areaSelect.options[areaSelect.selectedIndex] ? areaSelect.options[areaSelect.selectedIndex].text : '—';
			var addr = (qs('textarea[name="address"]', form) || {}).value || '';
			var presc = qs('[data-mns-prescription-file]', form);
			var hasRx = presc && presc.files && presc.files.length;
			var vals = {
				patient: patientSummary() + (phone ? ' · ⁦' + phone + '⁩' : ''),
				service: svcLabel + ' · ' + serviceDetailSummary() + (hasRx ? '' : ' · بدون نسخه'),
				time: whenSummary(),
				address: cityLabel + '، ' + areaLabel + (addr ? '، ' + addr : ''),
				nurse: genderNurseLabel(genderInput ? genderInput.value : '')
			};
			Object.keys(vals).forEach(function (k) {
				var el = qs('[data-mns-review-val="' + k + '"]', box);
				if (el) el.textContent = vals[k];
			});
			qsa('[data-mns-review-edit]', box).forEach(function (btn) {
				if (btn.getAttribute('data-mns-bound')) return;
				btn.setAttribute('data-mns-bound', '1');
				btn.addEventListener('click', function () {
					goStep(parseInt(btn.getAttribute('data-mns-review-edit'), 10) || 1);
				});
			});
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

		/* Time-slot availability: shown right under the slot chips once area + day + slot are known. */
		function renderAvailability(state, alts) {
			if (!availBox) return;
			availBox.hidden = false;
			availBox.className = 'mns-avail mns-avail--' + state;
			if (state === 'green') {
				availBox.innerHTML =
					'<span class="mns-avail__ico" aria-hidden="true">' + AV_ICON.check + '</span>' +
					'<div class="mns-avail__body"><strong>برای این زمان و منطقه پرستار موجود است</strong></div>';
			} else if (state === 'yellow') {
				var dayWord = dateLabel ? dateLabel.split('،')[0] : '';
				var chips = (alts || [])
					.map(function (id, i) {
						return (
							'<button type="button" class="mns-avail__alt' + (i === 0 ? ' is-primary' : '') + '" data-mns-avail-alt="' + id + '">' +
							(dayWord ? dayWord + ' ' : '') + timeLabelById(id) +
							'</button>'
						);
					})
					.join('');
				availBox.innerHTML =
					'<span class="mns-avail__ico" aria-hidden="true">' + AV_ICON.clock + '</span>' +
					'<div class="mns-avail__body"><strong>در این بازه پرستاری نداریم</strong>' +
					'<p>نزدیک‌ترین زمان‌های موجود:</p><div class="mns-avail__alts">' + chips + '</div></div>';
				qsa('[data-mns-avail-alt]', availBox).forEach(function (btn) {
					btn.addEventListener('click', function () {
						var id = btn.getAttribute('data-mns-avail-alt');
						if (timeValue) timeValue.value = id;
						qsa('[data-mns-slot-chips] [data-value]', form).forEach(function (chip) {
							var on = chip.getAttribute('data-value') === id;
							chip.classList.toggle('is-selected', on);
							chip.setAttribute('aria-checked', on ? 'true' : 'false');
						});
						runAvailability();
					});
				});
			} else {
				availBox.hidden = true;
			}
		}

		/* Gender availability: shown right under the gender cards, so the message appears where the finger is. */
		function renderGenderBlock(area, gender) {
			if (!availGenderBox) return;
			if (!area || !gender) {
				availGenderBox.hidden = true;
				return;
			}
			var other = gender === 'male' ? 'female' : 'male';
			var supportTel = form.getAttribute('data-mns-support-tel') || 'tel:02188607015';
			availGenderBox.hidden = false;
			availGenderBox.className = 'mns-avail mns-avail--red';
			availGenderBox.innerHTML =
				'<span class="mns-avail__ico" aria-hidden="true">' + AV_ICON.alert + '</span>' +
				'<div class="mns-avail__body"><strong>برای ' + escHtml(area) + ' فعلاً ' + genderNurseLabel(gender) + ' نداریم</strong>' +
				'<p>اگر بیمار موافق است، ' + genderNurseLabel(other) + ' را انتخاب کنید.</p>' +
				'<div class="mns-avail__alts">' +
				'<button type="button" class="mns-avail__alt is-primary" data-mns-gender-switch="' + other + '">انتخاب ' + genderNurseLabel(other) + '</button>' +
				'<a class="mns-avail__call" href="' + escHtml(supportTel) + '">' + AV_ICON.phone + '<span>تماس با پشتیبانی</span></a>' +
				'</div></div>';
			var sw = qs('[data-mns-gender-switch]', availGenderBox);
			if (sw) {
				sw.addEventListener('click', function () {
					var card = qs('[data-mns-nurse-gender-cards] [data-value="' + other + '"]', form);
					if (card) card.click();
				});
			}
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
			var haveSlot = !!(area && dateValue && dateValue.value && timeValue && timeValue.value);
			var redMap = availability.red_area_gender || {};
			var yellow = availability.yellow_areas || [];
			var red = !!(area && gender && redMap[area] && redMap[area] === gender);
			var slotState = haveSlot ? (yellow.indexOf(area) !== -1 ? 'yellow' : 'green') : 'idle';
			markGenderAvailability(area);
			renderAvailability(slotState, availability.alt_slots || []);
			renderGenderBlock(red ? area : '', red ? gender : '');
			availState = red ? 'red' : slotState;
			if (step2Next) step2Next.disabled = red;
		}

function scheduleAvailability() {
			if (availTimer) window.clearTimeout(availTimer);
			availTimer = window.setTimeout(runAvailability, 400);
		}

		/* "Who is it for?": a known answer collapses into one line; "change" opens the full list. */
		function setPickOpen(open) {
			if (!pickEdit || !pickSummary) return;
			pickEdit.hidden = !open;
			pickSummary.hidden = open;
			if (pickChange) pickChange.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				var target = qs('[data-mns-patient-card].is-selected', pickEdit) || qs('[data-mns-patient-card]', pickEdit);
				if (target) target.focus({ preventScroll: true });
			}
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

		if (pickChange) {
			pickChange.addEventListener('click', function () {
				setPickOpen(true);
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
			var panel = qs('[data-mns-new-patient-panel]', form);
			if (panel) {
				var showNew = mode === 'new';
				panel.hidden = !showNew;
				panel.classList.toggle('is-hidden', !showNew);
			}
			updateSideSummary();
			updatePick();
			saveDraft();
		}

		qsa('[data-mns-patient-card]', form).forEach(function (card) {
			card.addEventListener('click', function () {
				selectPatientCard(card);
			});
		});

		qsa('[data-mns-age-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-age-chips]', btn.getAttribute('data-value'), ageGroupInput);
				saveDraft();
			});
		});
		qsa('[data-mns-patient-gender-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-patient-gender-chips]', btn.getAttribute('data-value'), patGenderInput);
				saveDraft();
			});
		});

		qsa('[data-mns-date-rail] .mns-date-rail__chip[data-value]', form).forEach(function (chip) {
			chip.addEventListener('click', function () {
				qsa('.mns-date-rail__chip', form).forEach(function (c) {
					var on = c === chip;
					if (c.hasAttribute('data-value')) {
						c.classList.toggle('is-selected', on);
						c.setAttribute('aria-checked', on ? 'true' : 'false');
					}
				});
				if (dateValue) dateValue.value = chip.getAttribute('data-value') || '';
				dateLabel = chip.getAttribute('data-label') || '';
				customDateLabel = '';
				var wrap = qs('[data-mns-custom-date-wrap]', form);
				if (wrap) wrap.hidden = true;
				scheduleAvailability();
				updateSideSummary();
				saveDraft();
			});
		});

		var otherDateBtn = qs('[data-mns-date-other]', form);
		if (otherDateBtn) {
			otherDateBtn.addEventListener('click', function () {
				var wrap = qs('[data-mns-custom-date-wrap]', form);
				if (wrap) {
					wrap.hidden = false;
					wrap.classList.remove('is-hidden');
				}
				qsa('.mns-date-rail__chip[data-value]', form).forEach(function (c) {
					c.classList.remove('is-selected');
					c.setAttribute('aria-checked', 'false');
				});
			});
		}
		var customDate = qs('[data-mns-custom-date]', form);
		if (customDate) {
			customDate.addEventListener('change', function () {
				if (dateValue) dateValue.value = 'custom:' + customDate.value;
				customDateLabel = customDate.value;
				dateLabel = '';
				scheduleAvailability();
				updateSideSummary();
				saveDraft();
			});
		}

		qsa('[data-mns-slot-chips] [data-value]', form).forEach(function (chip) {
			chip.addEventListener('click', function () {
				qsa('[data-mns-slot-chips] [data-value]', form).forEach(function (c) {
					var on = c === chip;
					c.classList.toggle('is-selected', on);
					c.setAttribute('aria-checked', on ? 'true' : 'false');
				});
				if (timeValue) timeValue.value = chip.getAttribute('data-value') || '';
				scheduleAvailability();
				updateSideSummary();
				saveDraft();
			});
		});

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
				saveDraft();
			});
		});

		qsa('[data-mns-serum-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-serum-chips]', btn.getAttribute('data-value'), serumTypeInput);
				updateSideSummary();
				saveDraft();
			});
		});
		qsa('[data-mns-vein-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-vein-chips]', btn.getAttribute('data-value'), veinInput);
				saveDraft();
			});
		});
		qsa('[data-mns-allergy-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-allergy-chips]', btn.getAttribute('data-value'), allergyInput);
				saveDraft();
			});
		});

		var qtyDisplay = qs('[data-mns-qty-display]', form);
		function setQty(n) {
			var val = Math.max(1, Math.min(9, n));
			if (qtyInput) qtyInput.value = String(val);
			if (qtyDisplay) qtyDisplay.textContent = toFaDigits(val);
			updateSideSummary();
			saveDraft();
		}
		var qtyMinus = qs('[data-mns-qty-minus]', form);
		var qtyPlus = qs('[data-mns-qty-plus]', form);
		if (qtyMinus) qtyMinus.addEventListener('click', function () {
			setQty((parseInt(qtyInput.value, 10) || 1) - 1);
		});
		if (qtyPlus) qtyPlus.addEventListener('click', function () {
			setQty((parseInt(qtyInput.value, 10) || 1) + 1);
		});

		var recurringSwitch = qs('[data-mns-recurring-switch]', form);
		var recurringHidden = qs('[data-mns-is-recurring]', form);
		if (recurringSwitch) {
			recurringSwitch.addEventListener('click', function () {
				window.setTimeout(function () {
					var fields = qs('[data-mns-recurring-fields]', form);
					var on = recurringSwitch.classList.contains('is-on');
					if (recurringHidden) recurringHidden.value = on ? '1' : '0';
					if (fields) {
						fields.hidden = !on;
						fields.classList.toggle('is-hidden', !on);
					}
				}, 0);
			});
		}

		qsa('[data-mns-otp-digit]', form).forEach(function (inp, idx, arr) {
			inp.addEventListener('input', function () {
				var v = inp.value.replace(/\D/g, '').slice(-1);
				inp.value = v ? toFaDigits(v) : '';
				if (v && arr[idx + 1]) arr[idx + 1].focus();
			});
			inp.addEventListener('keydown', function (e) {
				if (e.key === 'Backspace' && !inp.value && arr[idx - 1]) arr[idx - 1].focus();
			});
		});

		function otpComplete() {
			var digits = qsa('[data-mns-otp-digit]', form).map(function (el) {
				return String(el.value || '').replace(/[۰-۹]/g, function (d) {
					return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
				});
			});
			return digits.join('').length === 4;
		}

		function genTrackCode() {
			var n = Math.floor(10000 + Math.random() * 90000);
			return 'MD-' + String(n);
		}

		function showConfirmation(code) {
			var confirm = qs('[data-mns-order-confirm]');
			if (!confirm) return;
			var doneCard = qs('[data-mns-done-card]');
			if (doneCard) doneCard.hidden = false;
			setWizardStep(5);
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
			try {
				localStorage.removeItem(DRAFT_KEY);
			} catch (eDraft) {
				/* ignore */
			}
			confirm.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!validateStep(4)) return;
			if (!otpComplete()) {
				showNoticeNear(form, 'کد چهار رقمی پیامک را وارد کنید.', true);
				return;
			}
			var submitBtn = qs('[data-mns-order-submit]', form);
			if (submitBtn) submitBtn.disabled = true;
			form.classList.add('is-busy');
			window.setTimeout(function () {
				form.classList.remove('is-busy');
				showConfirmation(genTrackCode());
			}, 700);
		});

		if (citySelect) {
			citySelect.addEventListener('change', fillAreas);
			areaSelect && areaSelect.addEventListener('change', scheduleAvailability);
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

		function saveDraft() {
			try {
				var data = {
					step: step,
					mode: modeInput ? modeInput.value : '',
					patient_id: patientIdInput ? patientIdInput.value : '',
					phone: (qs('input[name="phone"]', form) || {}).value || '',
					new_name: (qs('input[name="new_patient_name"]', form) || {}).value || '',
					age_group: ageGroupInput ? ageGroupInput.value : '',
					pat_gender: patGenderInput ? patGenderInput.value : '',
					city: citySelect ? citySelect.value : '',
					area: areaSelect ? areaSelect.value : '',
					address: (qs('textarea[name="address"]', form) || {}).value || '',
					date: dateValue ? dateValue.value : '',
					date_label: dateLabel,
					custom_date: customDateLabel,
					time: timeValue ? timeValue.value : '',
					nurse_gender: genderInput ? genderInput.value : '',
					serum: serumTypeInput ? serumTypeInput.value : '',
					vein: veinInput ? veinInput.value : '',
					allergy: allergyInput ? allergyInput.value : '',
					qty: qtyInput ? qtyInput.value : '1'
				};
				localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
			} catch (eSave) {
				/* ignore */
			}
		}

		function loadDraft(data) {
			if (!data) return;
			if (data.phone) {
				var phone = qs('input[name="phone"]', form);
				if (phone) phone.value = data.phone;
			}
			if (data.mode === 'saved' && data.patient_id) {
				var card = qs('[data-mns-patient-card="saved"][data-value="' + data.patient_id + '"]', form);
				if (card) selectPatientCard(card);
			} else if (data.mode === 'new') {
				var newCard = qs('[data-mns-patient-card="new"]', form);
				if (newCard) selectPatientCard(newCard);
				var nm = qs('input[name="new_patient_name"]', form);
				if (nm && data.new_name) nm.value = data.new_name;
				if (data.age_group) setChipGroup('[data-mns-age-chips]', data.age_group, ageGroupInput);
				if (data.pat_gender) setChipGroup('[data-mns-patient-gender-chips]', data.pat_gender, patGenderInput);
			}
			if (data.city && citySelect) {
				citySelect.value = data.city;
				fillAreas();
				if (data.area && areaSelect) areaSelect.value = data.area;
			}
			if (data.address) {
				var addr = qs('textarea[name="address"]', form);
				if (addr) addr.value = data.address;
			}
			if (data.date && dateValue) {
				dateValue.value = data.date;
				dateLabel = data.date_label || '';
				customDateLabel = data.custom_date || '';
			}
			if (data.time && timeValue) {
				timeValue.value = data.time;
				qsa('[data-mns-slot-chips] [data-value="' + data.time + '"]', form).forEach(function (c) {
					c.classList.add('is-selected');
					c.setAttribute('aria-checked', 'true');
				});
			}
			if (data.nurse_gender && genderInput) {
				genderInput.value = data.nurse_gender;
				qsa('[data-mns-nurse-gender-cards] [data-value="' + data.nurse_gender + '"]', form).forEach(function (c) {
					c.classList.add('is-selected');
					c.setAttribute('aria-checked', 'true');
				});
			}
			if (data.serum) setChipGroup('[data-mns-serum-chips]', data.serum, serumTypeInput);
			if (data.vein) setChipGroup('[data-mns-vein-chips]', data.vein, veinInput);
			if (data.allergy) setChipGroup('[data-mns-allergy-chips]', data.allergy, allergyInput);
			if (data.qty) setQty(parseInt(data.qty, 10) || 1);
			goStep(Math.min(maxStep, parseInt(data.step, 10) || 1));
			runAvailability();
		}

		var draftBanner = qs('[data-mns-order-draft-banner]');
		try {
			var raw = localStorage.getItem(DRAFT_KEY);
			if (raw && draftBanner) {
				draftBanner.hidden = false;
				var parsed = JSON.parse(raw);
				var restore = qs('[data-mns-order-draft-restore]');
				var dismiss = qs('[data-mns-order-draft-dismiss]');
				if (restore) {
					restore.addEventListener('click', function () {
						loadDraft(parsed);
						draftBanner.hidden = true;
					});
				}
				if (dismiss) {
					dismiss.addEventListener('click', function () {
						localStorage.removeItem(DRAFT_KEY);
						draftBanner.hidden = true;
					});
				}
			}
		} catch (eLoad) {
			/* ignore */
		}

		form.addEventListener('input', function (e) {
			if (e.target && e.target.matches('input, textarea, select')) {
				updatePick();
				saveDraft();
			}
		});

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

		/* PNG defaults for serum step */
		var firstSerum = qs('[data-mns-serum-chips] .mns-chip[data-value]', form);
		if (firstSerum && serumTypeInput && !serumTypeInput.value) {
			setChipGroup('[data-mns-serum-chips]', firstSerum.getAttribute('data-value'), serumTypeInput);
		}
		if (veinInput && !veinInput.value) setChipGroup('[data-mns-vein-chips]', 'normal', veinInput);
		if (allergyInput && !allergyInput.value) setChipGroup('[data-mns-allergy-chips]', 'no', allergyInput);
		if (qtyInput) setQty(parseInt(qtyInput.value, 10) || 2);
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
		initLiveSearch();
		initOrdersList();
		initInvoiceView();
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
