/**
 * Handles the popup functionality and video autoplay behavior
 * when switching tabs in GutenKit.
 */
(function () {
	document.addEventListener('click', function (e) {
		// Check if the click is on a GutenKit tab link inside video autoplay area
		if (e.target.closest('.video-should-autoplay .gkit-nav-link')) {
			setTimeout(() => {
				// Wait a tick for tab activation to apply
				document.querySelectorAll('.video-should-autoplay').forEach(wrapper => {
					const activeTab = wrapper.querySelector('.gkit-tab-content .wp-block-gutenkit-advanced-tab-item.active');
					if (activeTab) {
						const video = activeTab.querySelector('video');
						if (video) {
							video.currentTime = 0;
							video.play();
						}
					}
				});
			}, 50); // Slight delay ensures `.active` class is applied
		}
	});
})();

/**
 * Next custom css add here.
 */
(function () {
	
})();