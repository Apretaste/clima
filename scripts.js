$(document).ready(function () {
  $('.fixed-action-btn').floatingActionButton({
    direction: 'top',
    hoverEnabled: false
  });
});

function pad(n, width, z) {
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function ucwords(str) {
  return (str + '').replace(/^(.)|\s(.)/g, function ($1) {
    return $1.toUpperCase();
  });
}

function formatDate(dateStr) {
  var date = new Date(dateStr);
  var year = date.getFullYear();
  var month = pad(1 + date.getMonth(), 2);
  var day = pad(date.getDay(), 2);
  return day + '/' + month + '/' + year;
}

function formatTime(dateStr) {
  var date = new Date(dateStr);
  var hour = (date.getHours() < 12) ? date.getHours() : date.getHours() - 12;
  var minutes = pad(date.getMinutes(), 2);
  var amOrPm = (date.getHours() < 12) ? "am" : "pm";
  return hour + ':' + minutes + amOrPm;
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