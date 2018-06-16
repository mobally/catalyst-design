
function bps_clear_radio(container) {

	container = document.getElementById (container);

	var radioButtons = container.getElementsByTagName ('input');
	for (i = 0; i < radioButtons.length; i++) {
		radioButtons[i].checked = '';
	}
}
