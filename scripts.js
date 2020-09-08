$(document).ready(function () {
	$('.fixed-action-btn').floatingActionButton({
		direction: 'top',
		hoverEnabled: false
	});
	$('.modal').modal();
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

var share;

function init(command, text, data, toastText) {

	if (typeof data == 'undefined') data = {};
	if (typeof command == 'undefined') command = 'CLIMA';
	if (typeof text == 'undefined') text = 'Informaci&oacute;n del clima';
	if (typeof toastText == 'undefined') toast = 'Se ha compartido la informaci&oacute;n del clima en Pizarra';

	share = {
		text: text,
		icon: 'cloud-sun',
		send: function () {
			apretaste.send({
				command: 'PIZARRA PUBLICAR',
				redirect: false,
				callback: {
					name: 'toast',
					data: toastText
				},
				data: {
					text: $('#message').val(),
					image: '',
					link: {
						command: btoa(JSON.stringify({
							command: command,
							data: data
						})),
						icon: share.icon,
						text: share.text
					}
				}
			})
		}
	};
}

function toast(message){
	M.toast({html: message});
}