function ajustar(tam, num) {
  //rellena con 0 a la izquierda
  //
  if (num.toString().length <= tam-1) return ajustar(tam, "0" + num)
  else return num;
}

function pad(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
}

function format_fecha_d(fecha, fmt) {
  var fecha_f; 
  var f = new Date(fecha);
  
  if(fmt=="dd-mm-yyyy"){
    fecha_f = ajustar(2, (f.getDate())) + '-' + ajustar(2, (f.getMonth() + 1)) + '-' + f.getFullYear();
  }
  else{
    fecha_f = f.getFullYear() + '-' + ajustar(2, (f.getMonth() + 1)) + '-' + ajustar(2, (f.getDate()));
  }
  
  return fecha_f;
}


function format_fecha(fecha, fmt) {
  //la fecha de entrada es ISO (yyyy-mm-dd)
  if (fecha==undefined) return '';
  var fecha_f; 
  var res = fecha.split("-");
  if(fmt=="mm-dd-yyyy"){
    fecha_f = ajustar(2, res[1]) + '-' + ajustar(2, (res[2])) + '-' + res[0];
  }else{
    fecha_f = res[0] + '-' + ajustar(2, res[1]) + '-' + ajustar(2, (res[2]));
  }
  return fecha_f  
}

function fecha_en_texto(fecha) {
  var meses = new Array ("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
  var diasSemana = new Array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
  var res = fecha.split("-");
  var fecha_f = res[1] + '-' + ajustar(2, res[2]) + '-' + ajustar(2, (res[0]));
  var f=new Date(fecha_f);
  return diasSemana[f.getDay()] + ", " + f.getDate() + " de " + meses[f.getMonth()] + " de " + f.getFullYear();
}

function removeDuplicates(originalArray, prop) {
     var newArray = [];
     var lookupObject  = {};
     var pepe=["aaa","bbb"];

     for(var i in originalArray) {
        lookupObject[originalArray[i][prop]] = originalArray[i];
        
        // lookupObject[originalArray[i][prop]].profe = originalArray[i].user;
        
        // console.log("pijo")
        // console.log(originalArray[i])
      }

     for(i in lookupObject) {
         newArray.push(lookupObject[i]);
     }
      return newArray;
 }
