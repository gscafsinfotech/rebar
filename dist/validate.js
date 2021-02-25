$(document).ready(function(){
  
  /*========= Allow Number validation =========*/
  $(".number").keydown(function (e) {
      if($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
         (e.keyCode == 65 && e.ctrlKey === true) || (e.keyCode == 67 && e.ctrlKey === true) ||(e.keyCode == 88 && e.ctrlKey === true) ||
         (e.keyCode == 86 && e.ctrlKey === true) || (e.keyCode >= 35 && e.keyCode <= 39)) {
        return;
      }
      if((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
          e.preventDefault();
      }
  });
  // Copy Paste Omit text
  $(".number").bind('keyup', function(e) {
      this.value = this.value.replace(/[^0-9_.]/g,'');
  });

  /*========= Allow text validation =========*/
  $(".alpha").on("keydown", function(event){

      var arr = [8,9,16,17,20,32,35,36,37,38,39,40,45,46];
      for(var i = 65; i <= 90; i++){
        arr.push(i);
      }
      if(jQuery.inArray(event.which, arr) === -1){
        event.preventDefault();
      }
  });
  /*========= Allow text and Number with few special charater =========*/
  $('.alphanum').keypress(function (e) {
      var regex = new RegExp("^[a-zA-Z0-9.,/_:-\\s]+$");
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (regex.test(str)) {
        return true;
      }else{
        e.preventDefault();
        return false;
      }
   });
});
