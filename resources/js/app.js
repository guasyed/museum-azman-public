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
	initInstallExperience();

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

const INSTALL_STEPS = {
	windows: {
		chromium: {
			title: 'Install on Windows',
			description: 'This browser can install Museum Azman as a desktop-style app.',
			steps: [
				'Click the install icon in the address bar or open the browser menu.',
				'Select Install app, Install Museum Azman, or Apps > Install this site as an app.',
				'Confirm the install prompt to pin the app to your desktop or taskbar.'
			],
			note: 'Chrome and Edge on Windows provide the best native install flow.'
		},
		firefox: {
			title: 'Install on Windows',
			description: 'Firefox on desktop does not provide a full app-install prompt for this site.',
			steps: [
				'Open this site in Chrome or Edge.',
				'Use the install icon in the address bar or the browser menu.',
				'Confirm the install prompt to create the app window.'
			],
			note: 'If you stay in Firefox, use a normal browser shortcut instead of app install.'
		}
	},
	macos: {
		chromium: {
			title: 'Install on macOS',
			description: 'Chrome and Edge on macOS can install this site as a standalone app.',
			steps: [
				'Click the install icon in the address bar or open the browser menu.',
				'Select Install app, Install Museum Azman, or Apps > Install this site as an app.',
				'Confirm to add it to Applications and launch it like a native app.'
			],
			note: 'Installed apps can also be kept in the Dock for quick access.'
		},
		safari: {
			title: 'Install on macOS',
			description: 'Safari uses Add to Dock instead of the standard install prompt.',
			steps: [
				'Open this site in Safari.',
				'From the menu bar, choose File > Add to Dock.',
				'Confirm the app name, then click Add.'
			],
			note: 'After that, Museum Azman opens from the Dock like a separate app.'
		},
		firefox: {
			title: 'Install on macOS',
			description: 'Firefox on macOS does not provide a native app-install flow for this site.',
			steps: [
				'Open this site in Safari, Chrome, or Edge.',
				'Use Safari File > Add to Dock, or the install option in Chrome or Edge.',
				'Confirm the install to create a standalone app.'
			],
			note: 'Safari and Chromium browsers are the supported install paths on macOS.'
		}
	},
	linux: {
		chromium: {
			title: 'Install on Linux',
			description: 'Chromium-based browsers on Linux can install this site as an app window.',
			steps: [
				'Click the install icon in the address bar or open the browser menu.',
				'Select Install app, Install Museum Azman, or More tools > Create shortcut / Install.',
				'Confirm to create the launcher entry for your desktop environment.'
			],
			note: 'Chrome, Edge, Brave, and Chromium usually support this on Linux.'
		},
		firefox: {
			title: 'Install on Linux',
			description: 'Firefox on Linux does not provide a full PWA install prompt for this site.',
			steps: [
				'Open this site in Chrome, Chromium, Edge, or Brave.',
				'Use the install action from the address bar or browser menu.',
				'Confirm to add the app to your launcher.'
			],
			note: 'Firefox can bookmark the site, but app install is better in Chromium-based browsers.'
		}
	},
	default: {
		chromium: {
			title: 'Install Museum Azman',
			description: 'Your browser should support installing this site as an app.',
			steps: [
				'Look for an install icon in the address bar.',
				'Or open the browser menu and choose Install app or Install this site as an app.',
				'Confirm the prompt to finish installation.'
			],
			note: 'If you do not see an install option, try Chrome or Edge.'
		},
		safari: {
			title: 'Install Museum Azman',
			description: 'Safari uses Add to Dock instead of the standard install prompt.',
			steps: [
				'Open the site in Safari.',
				'Choose File > Add to Dock.',
				'Confirm to finish installation.'
			],
			note: 'The app will then open in its own window.'
		},
		firefox: {
			title: 'Install Museum Azman',
			description: 'Firefox desktop usually does not expose a native install prompt for PWAs.',
			steps: [
				'Open this site in Chrome, Edge, Safari, or another supported browser.',
				'Use that browser\'s install or Add to Dock action.',
				'Confirm the prompt to create the app.'
			],
			note: 'A browser bookmark still works if you do not need app-style install.'
		}
	}
};

function initInstallExperience() {
	const triggers = Array.from(document.querySelectorAll('[data-install-trigger]'));
	const modal = document.getElementById('installModal');
	const modalTitle = document.getElementById('installModalTitle');
	const modalDescription = document.getElementById('installModalDescription');
	const modalSteps = document.getElementById('installModalSteps');
	const modalNote = document.getElementById('installModalNote');
	const closeButtons = [
		document.getElementById('installModalClose'),
		document.getElementById('installModalDone'),
	];

	if (!triggers.length || !modal || !modalTitle || !modalDescription || !modalSteps || !modalNote) {
		return;
	}

	let deferredPrompt = null;

	const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

	const getPlatform = () => {
		const userAgent = window.navigator.userAgent.toLowerCase();
		const platform = (window.navigator.userAgentData?.platform || window.navigator.platform || '').toLowerCase();

		if (platform.includes('mac') || userAgent.includes('mac os x')) {
			return 'macos';
		}

		if (platform.includes('win') || userAgent.includes('windows')) {
			return 'windows';
		}

		if (platform.includes('linux') || userAgent.includes('linux') || userAgent.includes('x11')) {
			return 'linux';
		}

		return 'default';
	};

	const getBrowser = () => {
		const userAgent = window.navigator.userAgent.toLowerCase();

		if (userAgent.includes('firefox')) {
			return 'firefox';
		}

		if (userAgent.includes('safari') && !userAgent.includes('chrome') && !userAgent.includes('crios') && !userAgent.includes('edg')) {
			return 'safari';
		}

		return 'chromium';
	};

	const getInstructions = () => {
		const platform = getPlatform();
		const browser = getBrowser();
		const platformConfig = INSTALL_STEPS[platform] || INSTALL_STEPS.default;
		return platformConfig[browser] || INSTALL_STEPS.default.chromium;
	};

	const renderInstructions = () => {
		const instructions = getInstructions();
		modalTitle.textContent = instructions.title;
		modalDescription.textContent = instructions.description;
		modalSteps.innerHTML = instructions.steps.map((step) => `<li>${step}</li>`).join('');
		modalNote.textContent = instructions.note;
	};

	const syncTriggerState = () => {
		const installed = isStandalone();
		const label = deferredPrompt ? 'Install now' : 'Install App';

		triggers.forEach((trigger) => {
			trigger.classList.toggle('is-hidden', installed);
			trigger.disabled = installed;
			trigger.setAttribute('aria-hidden', installed ? 'true' : 'false');
			trigger.title = deferredPrompt ? 'Install now' : 'Install app';
			trigger.setAttribute('aria-label', deferredPrompt ? 'Install now' : 'Install app');

			const labelNode = trigger.querySelector('[data-install-label]');
			if (labelNode) {
				labelNode.textContent = label;
			}
		});
	};

	const openModal = () => {
		renderInstructions();
		modal.classList.add('active');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('museum-install-open');
	};

	const closeModal = () => {
		modal.classList.remove('active');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('museum-install-open');
	};

	const handleInstall = async () => {
		if (isStandalone()) {
			syncTriggerState();
			return;
		}

		if (!deferredPrompt) {
			openModal();
			return;
		}

		deferredPrompt.prompt();

		try {
			await deferredPrompt.userChoice;
		} finally {
			deferredPrompt = null;
			syncTriggerState();
		}
	};

	triggers.forEach((trigger) => {
		trigger.addEventListener('click', handleInstall);
	});

	closeButtons.forEach((button) => {
		button?.addEventListener('click', closeModal);
	});

	modal.addEventListener('click', (event) => {
		if (event.target === modal) {
			closeModal();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && modal.classList.contains('active')) {
			closeModal();
		}
	});

	window.addEventListener('beforeinstallprompt', (event) => {
		event.preventDefault();
		deferredPrompt = event;
		syncTriggerState();
	});

	window.addEventListener('appinstalled', () => {
		deferredPrompt = null;
		closeModal();
		syncTriggerState();
	});

	syncTriggerState();
}
