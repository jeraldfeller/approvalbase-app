function getNewAlertCount(){
  $.ajax({
    url: '/leads/getNewAlertCount',
    type: 'POST',
    data: {ajax:true},
    dataType: 'json',
  }).done(function (responseData) {
    $('.new-alerts').html(responseData);
  });
}

function refreshGetAlertCount(){
  setTimeout(function(){
    getNewAlertCount();
  }, 360000)
}

function formatNumber(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function addslashes(str) {
  str = str.replace(/\\/g, '\\\\');
  str = str.replace(/\'/g, '\\\'');
  str = str.replace(/\"/g, '\\"');
  str = str.replace(/\0/g, '\\0');
  return str;
}


function shortizeNumber (x) {
  var value = 0;
  var output = '';
  // Nine Zeroes for Billions
  if(Math.abs(Number(x)) >= 1.0e+9){
    value =  Math.abs(Number(x)) / 1.0e+9;
    output = value.toFixed(1) + "B";
  }else if(Math.abs(Number(x)) >= 1.0e+6){
    value =  Math.abs(Number(x)) / 1.0e+6;
    output = value.toFixed(1) + "M";
  }else if(Math.abs(Number(x)) >= 1.0e+3){
    value =  Math.abs(Number(x)) / 1.0e+3;
    output = value.toFixed(1) + "K";
  }else{
    output = Math.abs(Number(x));
  }
  return output;

}

function downloadPdfZip($btn, dasId, $part, $files = [], $index = 0){

  return new Promise((resolve, reject)=>{
    $.ajax({
      url: '/pdf/downloadPdf?ajax=1',
      type: 'POST',
      data: {
        id: dasId,
        part: $part,
        file: $files,
        index: $index,
      },
      dataType: 'json'
    }).done(function (response) {
      if($part == 1){
        if(response.length > 0){
          resolve(response);
        }else{
          showNotification('No documents available.', 'info');
          $btn.html('<i class="fa fa-download font-size-20"></i>');
          $btn.attr('disabled', false);
        }
      }else if($part == 2){
          resolve(response);
      }else{
        if(response != false){
          if(response.s == 1){
             location.href = '/pdf/download?file='+encodeURI(response.file);
          }else{
            if(response.links.length > 0){
              for(var l = 0; l < response.links.length; l++){
                window.open(response.links[l], '_blank');
              }
            }
          }

        }else{
          showNotification('', 'error');
        }

        resolve(true);
        setTimeout(function(){
          $btn.html('<i class="fa fa-download font-size-20"></i>');
          $btn.attr('disabled', false);
        }, 1000);

      }



    });
  })

}