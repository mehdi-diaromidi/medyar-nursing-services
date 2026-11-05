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
		var dash = qs('.mns-dash');
		if (!dash) return;
		var sidebar = qs('[data-mns-sidebar]', dash);
		var expand = qs('[data-mns-sidebar-expand]');
		var backdrop = qs('[data-mns-sidebar-backdrop]');
		if (!sidebar || !expand || !backdrop) return;
		var lastFocus = null;

		function isMobile() {
			return window.matchMedia('(max-width: 767px)').matches;
		}

		function setOpen(open) {
			sidebar.classList.toggle('mobile-expanded', open);
			backdrop.hidden = !open;
			backdrop.classList.toggle('is-visible', open);
			expand.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.documentElement.classList.toggle('mns-drawer-open', open);
			document.body.style.overflow = open ? 'hidden' : '';
			if (open) {
				lastFocus = document.activeElement;
				var first = qs('.mns-dash__nav-link', sidebar);
				if (first) first.focus({ preventScroll: true });
			} else if (lastFocus && typeof lastFocus.focus === 'function') {
				lastFocus.focus({ preventScroll: true });
				lastFocus = null;
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
			if (e.key === 'Escape') setOpen(false);
		});
		window.addEventListener('resize', function () {
			var mobile = isMobile();
			expand.hidden = !mobile;
			if (!mobile) setOpen(false);
		});
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

		var step = 1;
		var maxStep = 4;
		var services = [];
		var cities = [];
		try {
			services = JSON.parse(form.getAttribute('data-mns-services') || '[]') || [];
		} catch (e1) {
			services = [];
		}
		try {
			cities = JSON.parse(form.getAttribute('data-mns-cities') || '[]') || [];
		} catch (e2) {
			cities = [];
		}

		var modeInput = qs('[data-mns-patient-mode]', form);
		var genderInput = qs('[data-mns-nurse-gender]', form);
		var urgentInput = qs('[data-mns-is-urgent]', form);
		var categoryInput = qs('[data-mns-category]', form);
		var citySelect = qs('[data-mns-city]', form);
		var areaSelect = qs('[data-mns-area]', form);
		var serviceSelect = qs('[data-mns-service]', form);

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
			qsa('[data-mns-step-goto]').forEach(function (btn) {
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
			var compact = qs('[data-mns-step-compact]');
			if (compact) {
				compact.textContent = 'مرحلهٔ ' + step + ' از ' + maxStep + (currentLabel ? ' — ' + currentLabel : '');
			}
			var bar = qs('[data-mns-step-progress]');
			if (bar) {
				bar.style.width = Math.round((step / maxStep) * 100) + '%';
			}
			updateOrderSummary();
			if (step === 4) fillReview();
			var activeSec = qs('[data-mns-step].is-active', form);
			if (activeSec) activeSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		function updateOrderSummary() {
			var box = qs('[data-mns-order-summary]');
			var body = qs('[data-mns-order-summary-body]');
			if (!box || !body) return;
			var bits = [];
			var svc = serviceSelect && serviceSelect.options[serviceSelect.selectedIndex]
				? serviceSelect.options[serviceSelect.selectedIndex].text
				: '';
			if (svc && serviceSelect.value) bits.push(svc);
			var mode = modeInput ? modeInput.value : 'self';
			if (mode === 'self') bits.push('خودم');
			else if (mode === 'saved') {
				var pid = qs('[data-mns-patient-select]', form);
				if (pid && pid.value && pid.options[pid.selectedIndex]) bits.push(pid.options[pid.selectedIndex].text);
			} else {
				var nm = qs('input[name="new_patient_name"]', form);
				if (nm && nm.value) bits.push(nm.value);
			}
			var urgent = urgentInput && urgentInput.value === '1';
			if (urgent) bits.push('فوری');
			else {
				var date = qs('[data-mns-preferred-date]', form);
				var time = qs('[data-mns-preferred-time]', form);
				if (date && date.value) bits.push(date.value + (time && time.value ? ' · ' + time.value : ''));
			}
			if (!bits.length) {
				box.hidden = true;
				return;
			}
			body.textContent = bits.join(' · ');
			box.hidden = bits.length === 0;
		}

		function validateStep(n) {
			if (n === 1) {
				if (!categoryInput || !categoryInput.value || !serviceSelect || !serviceSelect.value) {
					showNoticeNear(form, 'دسته و خدمت اصلی را انتخاب کنید.', true);
					return false;
				}
				if (!genderInput || !genderInput.value) {
					showNoticeNear(form, 'جنسیت پرستار را انتخاب کنید.', true);
					return false;
				}
			}
			if (n === 2) {
				var phone = qs('input[name="phone"]', form);
				if (!phone || !phone.value || phone.value.length < 11) {
					showNoticeNear(form, 'شماره تماس معتبر وارد کنید.', true);
					return false;
				}
				var mode = modeInput ? modeInput.value : 'self';
				if (mode === 'saved') {
					var pid = qs('[data-mns-patient-select]', form);
					if (!pid || !pid.value) {
						showNoticeNear(form, 'بیمار را انتخاب کنید.', true);
						return false;
					}
				}
				if (mode === 'new') {
					var nm = qs('input[name="new_patient_name"]', form);
					var age = qs('input[name="new_patient_age"]', form);
					if (!nm || !nm.value || !age || !age.value) {
						showNoticeNear(form, 'نام و سن بیمار جدید را وارد کنید.', true);
						return false;
					}
				}
			}
			if (n === 3) {
				var urgent = urgentInput && urgentInput.value === '1';
				if (!citySelect || !citySelect.value || !areaSelect || !areaSelect.value) {
					showNoticeNear(form, 'شهر و منطقه را انتخاب کنید.', true);
					return false;
				}
				var addr = qs('input[name="address"]', form);
				if (!addr || !addr.value) {
					showNoticeNear(form, 'آدرس دقیق را وارد کنید.', true);
					return false;
				}
				if (!urgent) {
					var date = qs('[data-mns-preferred-date]', form);
					var time = qs('[data-mns-preferred-time]', form);
					if (!date || !date.value || !time || !time.value) {
						showNoticeNear(form, 'تاریخ و ساعت مراجعه را وارد کنید یا سفارش فوری را فعال کنید.', true);
						return false;
					}
				}
			}
			var notice = qs('[data-mns-notice]') || qs('.mns-form-notice');
			if (notice) notice.hidden = true;
			return true;
		}

		function fillReview() {
			var box = qs('[data-mns-review]', form);
			if (!box) return;
			var mode = modeInput ? modeInput.value : 'self';
			var patientLabel = 'خودم';
			if (mode === 'saved') {
				var sel = qs('[data-mns-patient-select]', form);
				patientLabel = sel && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '—';
			} else if (mode === 'new') {
				var n = qs('input[name="new_patient_name"]', form);
				patientLabel = n && n.value ? n.value : 'بیمار جدید';
			}
			var cityLabel = citySelect && citySelect.options[citySelect.selectedIndex] ? citySelect.options[citySelect.selectedIndex].text : '—';
			var areaLabel = areaSelect && areaSelect.options[areaSelect.selectedIndex] ? areaSelect.options[areaSelect.selectedIndex].text : '—';
			var serviceLabel = serviceSelect && serviceSelect.options[serviceSelect.selectedIndex] ? serviceSelect.options[serviceSelect.selectedIndex].text : '—';
			var genderMap = { female: 'خانم', male: 'آقا', any: 'فرقی ندارد' };
			var genderLabel = genderMap[genderInput.value] || '—';
			var urgent = urgentInput && urgentInput.value === '1';
			var when = urgent
				? 'فوری'
				: ((qs('[data-mns-preferred-date]', form) || {}).value || '') + ' · ' + ((qs('[data-mns-preferred-time]', form) || {}).value || '');
			var addr = (qs('input[name="address"]', form) || {}).value || '—';
			var rows = [
				['بیمار', patientLabel],
				['تماس', (qs('input[name="phone"]', form) || {}).value || '—'],
				['مکان', cityLabel + ' / ' + areaLabel],
				['آدرس', addr],
				['زمان', when],
				['خدمت', serviceLabel],
				['جنسیت پرستار', genderLabel]
			];
			box.innerHTML = rows
				.map(function (r) {
					return '<div class="mns-review__row"><span>' + r[0] + '</span><strong>' + r[1] + '</strong></div>';
				})
				.join('');
		}

		function fillAreas() {
			if (!areaSelect || !citySelect) return;
			var city = cities.filter(function (c) {
				return String(c.id) === String(citySelect.value);
			})[0];
			var areas = city && city.areas ? city.areas : [];
			areaSelect.innerHTML = '<option value="">انتخاب کنید</option>';
			areas.forEach(function (a) {
				var opt = document.createElement('option');
				opt.value = a;
				opt.textContent = a;
				areaSelect.appendChild(opt);
			});
			areaSelect.dispatchEvent(new Event('change'));
		}

		function fillServices() {
			if (!serviceSelect || !categoryInput) return;
			var cat = categoryInput.value;
			serviceSelect.innerHTML = '<option value="">انتخاب کنید</option>';
			services
				.filter(function (s) {
					return !cat || s.category === cat;
				})
				.forEach(function (s) {
					var opt = document.createElement('option');
					opt.value = s.id;
					opt.textContent = s.name;
					serviceSelect.appendChild(opt);
				});
			serviceSelect.dispatchEvent(new Event('change'));
		}

		function selectPatientPick(value) {
			var sel = qs('[data-mns-patient-select]', form);
			if (sel) {
				sel.value = value || '';
				sel.dispatchEvent(new Event('change'));
			}
			qsa('[data-mns-patient-pick]', form).forEach(function (card) {
				var on = card.getAttribute('data-value') === value;
				card.classList.toggle('is-selected', on);
				card.setAttribute('aria-checked', on ? 'true' : 'false');
			});
			updateOrderSummary();
		}

		qsa('[data-mns-patient-pick]', form).forEach(function (card) {
			card.addEventListener('click', function () {
				selectPatientPick(card.getAttribute('data-value') || '');
			});
		});

		qsa('[data-mns-seg="patient_mode"] .mns-seg__btn', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				var val = btn.getAttribute('data-value');
				qsa('[data-mns-seg="patient_mode"] .mns-seg__btn', form).forEach(function (b) {
					var on = b === btn;
					b.classList.toggle('is-selected', on);
					b.setAttribute('aria-checked', on ? 'true' : 'false');
				});
				if (modeInput) modeInput.value = val;
				qsa('[data-mns-panel-mode]', form).forEach(function (panel) {
					var show = panel.getAttribute('data-mns-panel-mode') === val;
					panel.hidden = !show;
					panel.classList.toggle('is-hidden', !show);
				});
				updateOrderSummary();
			});
		});

		var urgentChip = qs('[data-mns-urgent-chip]', form);
		if (urgentChip) {
			urgentChip.addEventListener('click', function () {
				var on = urgentChip.getAttribute('aria-pressed') !== 'true';
				urgentChip.setAttribute('aria-pressed', on ? 'true' : 'false');
				urgentChip.classList.toggle('is-selected', on);
				if (urgentInput) urgentInput.value = on ? '1' : '0';
				var hint = qs('[data-mns-urgent-hint]', form);
				if (hint) {
					hint.hidden = !on;
					hint.classList.toggle('is-hidden', !on);
				}
				var sched = qs('[data-mns-schedule-fields]', form);
				if (sched) {
					sched.hidden = on;
					sched.classList.toggle('is-hidden', on);
				}
			});
		}

		qsa('[data-mns-category-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-category-chips]', btn.getAttribute('data-value'), categoryInput);
				fillServices();
			});
		});

		qsa('[data-mns-gender-chips] .mns-chip', form).forEach(function (btn) {
			btn.addEventListener('click', function () {
				setChipGroup('[data-mns-gender-chips]', btn.getAttribute('data-value'), genderInput);
			});
		});

		var recurringSwitch = qs('[data-mns-recurring-switch]', form);
		if (recurringSwitch) {
			recurringSwitch.addEventListener('click', function () {
				window.setTimeout(function () {
					var fields = qs('[data-mns-recurring-fields]', form);
					if (!fields) return;
					var show = recurringSwitch.classList.contains('is-on');
					fields.hidden = !show;
					fields.classList.toggle('is-hidden', !show);
				}, 0);
			});
		}

		if (citySelect) citySelect.addEventListener('change', fillAreas);

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
		qsa('[data-mns-step-goto]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var target = parseInt(btn.getAttribute('data-mns-step-goto'), 10);
				if (target < step) goStep(target);
				else if (target === step + 1 && validateStep(step)) goStep(target);
				else if (target > step + 1) showNoticeNear(form, 'لطفاً مراحل را به ترتیب کامل کنید.', true);
			});
		});

		goStep(1);

		var preselect = form.getAttribute('data-mns-preselect') || '';
		if (preselect) {
			var chip = qs('[data-mns-category-chips] [data-value="' + preselect + '"]', form);
			if (chip) {
				setChipGroup('[data-mns-category-chips]', preselect, categoryInput);
				fillServices();
			}
		}

		var prepatient = form.getAttribute('data-mns-prepatient') || '';
		if (prepatient && prepatient !== 'self') {
			if (modeInput) modeInput.value = 'saved';
			qsa('[data-mns-seg="patient_mode"] .mns-seg__btn', form).forEach(function (b) {
				var on = b.getAttribute('data-value') === 'saved';
				b.classList.toggle('is-selected', on);
				b.setAttribute('aria-checked', on ? 'true' : 'false');
			});
			qsa('[data-mns-panel-mode]', form).forEach(function (panel) {
				var show = panel.getAttribute('data-mns-panel-mode') === 'saved';
				panel.hidden = !show;
				panel.classList.toggle('is-hidden', !show);
			});
			selectPatientPick(prepatient);
		}
		updateOrderSummary();
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

	function initCopyButtons() {
		qsa('[data-mns-copy]').forEach(function (btn) {
			btn.addEventListener('click', function () {
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
