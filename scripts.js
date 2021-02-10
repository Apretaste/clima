// load modal and sidenav
//
$(document).ready(function () {
	$('.modal').modal();
	$('.sidenav').sidenav();
});

function showImage(path) {
	if (typeof apretaste.showImage != 'undefined') {
		apretaste.showImage(path)
	}
}