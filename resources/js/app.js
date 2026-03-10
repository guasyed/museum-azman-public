import './bootstrap';

const EYE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
const EYE_OFF_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"></path><path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c6.4 0 10 7 10 7a17.73 17.73 0 0 1-3.03 3.94"></path><path d="M6.61 6.61C4.42 8.12 3 12 3 12s3.6 7 10 7a10.93 10.93 0 0 0 5.39-1.39"></path></svg>';

const attachPasswordToggle = (input) => {
	if (!(input instanceof HTMLInputElement) || input.type !== 'password') {
		return;
	}

	if (input.dataset.passwordToggleBound === '1') {
		return;
	}

	const wrapper = document.createElement('div');
	wrapper.className = 'museum-password-wrap';

	input.parentNode.insertBefore(wrapper, input);
	wrapper.appendChild(input);

	const toggle = document.createElement('button');
	toggle.type = 'button';
	toggle.className = 'museum-password-toggle';
	toggle.setAttribute('aria-label', 'Show password');
	toggle.setAttribute('aria-pressed', 'false');
	toggle.innerHTML = EYE_ICON;

	toggle.addEventListener('click', () => {
		const revealing = input.type === 'password';
		input.type = revealing ? 'text' : 'password';
		toggle.innerHTML = revealing ? EYE_OFF_ICON : EYE_ICON;
		toggle.setAttribute('aria-label', revealing ? 'Hide password' : 'Show password');
		toggle.setAttribute('aria-pressed', revealing ? 'true' : 'false');
	});

	wrapper.appendChild(toggle);
	input.dataset.passwordToggleBound = '1';
};

const initPasswordToggles = (root = document) => {
	root.querySelectorAll('input[type="password"]').forEach(attachPasswordToggle);
};

document.addEventListener('DOMContentLoaded', () => {
	initPasswordToggles(document);

	const observer = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			mutation.addedNodes.forEach((node) => {
				if (!(node instanceof HTMLElement)) {
					return;
				}

				if (node.matches('input[type="password"]')) {
					attachPasswordToggle(node);
				} else {
					initPasswordToggles(node);
				}
			});
		}
	});

	observer.observe(document.body, { childList: true, subtree: true });
});
