function ucwords(str){
    return(str+'').replace(/^(.)|\s(.)/g,function($1){
        return $1.toUpperCase();
    });
}

function formatDate(dateStr) {
  var date = new Date(dateStr);
  var year = date.getFullYear();
  var month = (1 + date.getMonth()).toString().padStart(2, '0');
  var day = date.getDate().toString().padStart(2, '0');
  return day + '/' + month + '/' + year;
}

function formatTime(dateStr) {
  var date = new Date(dateStr);
  var hour = (date.getHours() < 12) ? date.getHours() : date.getHours() - 12;
  var minutes = String(date.getMinutes()).padStart(2, "0");
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

$(function(){
  alert('CLIMA');
});