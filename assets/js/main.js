/* Narayanganj Press — the only script (deferred, no dependencies).
   1. Header toggles (mobile search / menu drawer)  2. Copy link + Messenger fallback  3. View beacon. */
(function () {
	'use strict';
	var NP = window.NP || {};

	/* 1. Toggles: button.js-toggle[data-target] shows/hides #target and syncs aria-expanded. */
	function closeAll(except) {
		document.querySelectorAll('.js-toggle[aria-expanded="true"]').forEach(function (b) {
			if (b === except) { return; }
			var t = document.getElementById(b.getAttribute('data-target'));
			if (t) { t.hidden = true; }
			b.setAttribute('aria-expanded', 'false');
		});
	}
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-toggle');
		if (!btn) { return; }
		var target = document.getElementById(btn.getAttribute('data-target'));
		if (!target) { return; }
		var open = btn.getAttribute('aria-expanded') !== 'true';
		closeAll(btn);
		target.hidden = !open;
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (open) {
			var f = target.querySelector('input, a');
			if (f) { f.focus(); }
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeAll(null); }
	});

	/* 2. Sharing. */
	function status(row, msg) {
		var s = row && row.querySelector('.sh-status');
		if (!s) { return; }
		s.textContent = msg;
		clearTimeout(s._t);
		s._t = setTimeout(function () { s.textContent = ''; }, 2500);
	}
	function copy(text, row) {
		var done = function () { status(row, NP.copied || 'Copied'); };
		var fail = function () { status(row, NP.failed || 'Copy failed'); };
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text).then(done, fail);
			return;
		}
		var ta = document.createElement('textarea');
		ta.value = text;
		ta.setAttribute('readonly', '');
		ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
		document.body.appendChild(ta);
		ta.select();
		try { document.execCommand('copy') ? done() : fail(); } catch (err) { fail(); }
		document.body.removeChild(ta);
	}
	var touch = window.matchMedia && window.matchMedia('(pointer:coarse)').matches;
	document.addEventListener('click', function (e) {
		var c = e.target.closest('.sh-copy');
		if (c) {
			e.preventDefault();
			copy(c.getAttribute('data-copy') || location.href, c.closest('.share'));
			return;
		}
		/* fb-messenger:// only works where the app is installed (phones). On desktop use the
		   system share sheet, or copy the link so the reader can paste it into Messenger. */
		var m = e.target.closest('.sh-messenger[data-ms-fallback]');
		if (m && !touch) {
			e.preventDefault();
			var url = m.getAttribute('data-ms-fallback');
			if (navigator.share) {
				navigator.share({ url: url, title: document.title }).catch(function () {});
			} else {
				copy(url, m.closest('.share'));
			}
		}
	});

	/* 3. View counter: one ping per post per browser session, after the page is idle. */
	if (NP.view && NP.id) {
		var key = 'np_v_' + NP.id;
		var seen = false;
		try { seen = sessionStorage.getItem(key) === '1'; } catch (err) {}
		if (!seen) {
			var ping = function () {
				var body = 'id=' + encodeURIComponent(NP.id);
				try { sessionStorage.setItem(key, '1'); } catch (err) {}
				if (window.fetch) {
					fetch(NP.view, {
						method: 'POST',
						keepalive: true,
						credentials: 'omit',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: body
					}).catch(function () {});
				} else if (navigator.sendBeacon) {
					navigator.sendBeacon(NP.view, new Blob([body], { type: 'application/x-www-form-urlencoded' }));
				}
			};
			if ('requestIdleCallback' in window) {
				requestIdleCallback(ping, { timeout: 4000 });
			} else {
				setTimeout(ping, 1500);
			}
		}
	}
})();
