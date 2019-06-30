function formatDate(dateStr) {
  var date = new Date(dateStr);
  var year = date.getFullYear();
  var month = (1 + date.getMonth()).toString().padStart(2, '0');
  var day = date.getDate().toString().padStart(2, '0');
  return day + '/' + month + '/' + year;
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