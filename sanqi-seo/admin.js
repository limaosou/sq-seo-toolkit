/**
 * SQ SEO Toolkit - 后台交互脚本 v2.3.0
 * 职责：Toast 自动消失 / 一键复制 / 危险操作确认 / 上手引导状态记忆
 * 全部为渐进增强：JS 失效时页面功能不受影响（Toast 静态可见、复制与确认退化为普通行为）
 */
(function () {
	'use strict';

	/* ---------- 1. Toast：贴在保存按钮旁出现，4.5 秒自动消失（无保存按钮时右下角固定） ---------- */
	function initToasts() {
		var toasts = document.querySelectorAll('.sqseo-toast');
		if (!toasts.length) {
			return;
		}
		var actions = document.querySelector('.sqseo-admin .sqseo-actions');
		toasts.forEach(function (toast) {
			/* 有保存按钮的页面：把提示挪到按钮旁边；否则保持右下角固定 */
			if (actions && actions.parentNode) {
				toast.classList.add('sqseo-toast--inline');
				actions.parentNode.insertBefore(toast, actions.nextSibling);
			}
			function dismiss() {
				toast.classList.add('sqseo-toast--hide');
				window.setTimeout(function () {
					if (toast.parentNode) {
						toast.parentNode.removeChild(toast);
					}
				}, 260);
			}
			var closeBtn = toast.querySelector('.sqseo-toast-close');
			if (closeBtn) {
				closeBtn.addEventListener('click', dismiss);
			}
			window.setTimeout(dismiss, 4500);
		});
	}

	/* ---------- 2. 一键复制（事件委托，动态内容也生效） ---------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.sqseo-copy') : null;
		if (!btn) {
			return;
		}
		var text = btn.getAttribute('data-copy') || '';
		var done = function () {
			var original = btn.innerHTML;
			btn.classList.add('sqseo-copy--done');
			btn.innerHTML = '✓ 已复制';
			window.setTimeout(function () {
				btn.classList.remove('sqseo-copy--done');
				btn.innerHTML = original;
			}, 1600);
		};
		if (navigator.clipboard && navigator.clipboard.writeText) {
			e.preventDefault();
			navigator.clipboard.writeText(text).then(done, function () {
				/* 剪贴板权限被拒时静默失败，用户仍可手动复制 */
			});
		}
	});

	/* ---------- 3. 危险操作确认（清空记录等带 data-confirm 的元素） ---------- */
	document.addEventListener('click', function (e) {
		var el = e.target.closest ? e.target.closest('[data-confirm]') : null;
		if (el && !window.confirm(el.getAttribute('data-confirm'))) {
			e.preventDefault();
			e.stopPropagation();
		}
	}, true);

	/* ---------- 4. 上手引导卡状态记忆（localStorage） ---------- */
	var GUIDE_KEY = 'sqseo_guide_open';
	function initGuide() {
		var guide = document.getElementById('sqseo-guide');
		if (!guide || !window.localStorage) {
			return;
		}
		try {
			if (window.localStorage.getItem(GUIDE_KEY) === '1') {
				guide.open = true;
			}
			guide.addEventListener('toggle', function () {
				window.localStorage.setItem(GUIDE_KEY, guide.open ? '1' : '0');
			});
		} catch (err) {
			/* 隐私模式下 localStorage 不可用：静默降级 */
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initToasts();
			initGuide();
		});
	} else {
		initToasts();
		initGuide();
	}
})();
