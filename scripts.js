$(document).ready(function () {
	$('.fixed-action-btn').floatingActionButton({
		direction: 'top',
		hoverEnabled: false
	});
});

function ucwords(str) {
	return (str + '').replace(/^(.)|\s(.)/g, function ($1) {
		return $1.toUpperCase();
	});
}

function formatTime(dateStr) {
	return moment(dateStr).format('hh:mm:ss');
}

function showModal(text) {
	// open the modal
	$("#modalText .modal-content").html(text);
	var popup = document.getElementById('modalText');
	var modal = M.Modal.init(popup);
	modal.open();
}

function showOptions() {
	var popup = document.getElementById('modalOptions');
	var modal = M.Modal.init(popup);
	modal.open();
}

function showProvinces() {
	var popup = document.getElementById('modalProvinces');
	var modal = M.Modal.init(popup);
	modal.open();
}
