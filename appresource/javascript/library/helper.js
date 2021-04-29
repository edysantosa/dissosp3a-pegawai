
// export default Helper;

export function getSelectedDataTable(datatable, column){

    let split = column.split('.');

    var data = datatable.rows({selected:  true}).data();
    var result=[];       
    for (var i = 0; i < data.length; i++) {
        let dataToPush = data[i];
        for (var j = 0; j < split.length; j++) {
            dataToPush = dataToPush[split[j]];
        }
        result.push(dataToPush);
    }
     
    return result;
}

export function getAllDataTable(datatable, column){

    var data = datatable.data();
    var result=[];       
    for (var i = 0; i < data.length; i++) {
        result.push(data[i][column]);
    }
     
    return result;
}

export function isFormEmpty(form){
    let empty = true;
    $(form).find(":text, :file, :checkbox, select, textarea").each(function() {
        var $element = $(this);
        if ($element.is(':text')) {
            if ($element.hasClass('multiselect-search')) {
            } else {
                if($element.val())
                    empty = false;
            }
        } else if ($element.is(':checkbox')) {
            if ($element.prop('checked') == true)
                empty = false;            
        }
    });

    return empty;
}

export function formatNumberIndonesia(n) {
    // format number 1000000 to 1,234,567
    if(n == null || typeof n == 'undefined') {
        return 0;
    }
    return parseFloat(n).toLocaleString('id-ID');
}


// export function getNumber({ value, defaultValue }) {
//   const num = parseInt(value, 10);
//   return isNaN(num) ? defaultValue : num;
// }
export function int(value) {
  const num = parseInt(value, 10);
  return isNaN(num) ? 0 : num;
}