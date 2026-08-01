
var ddate = "";
var Restime = ["10:00:15","10:15:15","10:30:15","10:45:15","11:00:15","11:15:15","11:30:15","11:45:15","12:00:15","12:15:15","12:30:15","12:45:15","13:00:15","13:15:15","13:30:15","13:45:15","14:00:15","14:15:15","14:30:15","14:45:15","15:00:15","15:15:15","15:30:15","15:45:15","16:00:15","16:15:15","16:30:15","16:45:15","17:00:15","17:15:15","17:30:15","17:45:15","18:00:15","18:15:15","18:30:15","18:45:15","19:00:15","19:15:15","19:30:15","19:45:15","20:00:15","20:15:15","20:30:15","20:45:15","21:00:15","21:15:15","21:30:15","21:45:15","22:00:15","22:15:15","22:30:15","22:45:15","23:00:00","23:15:15","23:30:15","23:45:15","00:00:15"];
 var Resk = ["10:00","10:15","10:30","10:45","11:00","11:15","11:30","11:45","12:00","12:15","12:30","12:45","13:00","13:15","13:30","13:45","14:00","14:15","14:30","14:45","15:00","15:15","15:30","15:45","16:00","16:15","16:30","16:45","17:00","17:15","17:30","17:45","18:00","18:15","18:30","18:45","19:00","19:15","19:30","19:45","20:00","20:15","20:30","20:45","21:00","21:15","21:30","21:45","22:00","22:15","22:30","22:45","23:00","23:15","23:30","23:45","00:00"];
 var aptime = ["10:00 AM","10:15 AM","10:30 AM","10:45 AM","11:00 AM","11:15 AM","11:30 AM","11:45 AM","12:00 PM","12:15 PM","12:30 PM","12:45 PM","01:00 PM","01:15 PM","01:30 PM","01:45 PM","02:00 PM","02:15 PM","02:30 PM","02:45 PM","03:00 PM","03:15 PM","03:30 PM","03:45 PM","04:00 PM","04:15 PM","04:30 PM","04:45 PM","05:00 PM","05:15 PM","05:30 PM","05:45 PM","06:00 PM","06:15 PM","06:30 PM","06:45 PM","07:00 PM","07:15 PM","07:30 PM","07:45 PM","08:00 PM","08:15 PM","08:30 PM","08:45 PM","09:00 PM","09:15 PM","09:30 PM","09:45 PM","10:00 PM","10:15 PM","10:30 PM","10:45 PM","11:00 PM","11:15 PM","11:30 PM","11:45 PM","12:00 AM"];



$(document).ready(function(){ 
    // $('#datepicker').val(new Date().toDateInputValue());
    $( "#datepicker" ).datepicker( "option", "dateFormat", 'yy-mm-dd' );
    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear() + "-" + (month) + "-" + (day);
    $('#datepicker').val(today);
    //  condon();
      showTime(); 
     ppdate();
      mname();
     window.addEventListener('popstate',() =>{
       location.reload();
     }, false);  
     $('#datepicker').datepicker({dateFormat: 'yy-mm-dd'});
     
   });

//    Date.prototype.toDateInputValue = (function() {
//     var local = new Date(this);
//     local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
//     return local.toJSON().slice(0,10);
// });



function zzdate(A) {
    var today = new Date();
  var dd = (today.getDate() - A).toString();
  var mm = (today.getMonth() + 1).toString(); //January is 0!
  // dd = dd - 1;
  var yyyy = today.getFullYear().toString();
  if (dd < 10) {
    dd = "0" + dd;
  } 
  if (mm < 10) {
    mm = '0' + mm;
  } 
 var hdate = yyyy + '-' + mm + '-' + dd;
  var pdate = hdate.split("-");
  var qdate = pdate[2]+"_"+pdate[1]+"_"+pdate[0]; 
  return qdate;
  }




   function mname() {
    var resv = '';
    $.ajax({
      type: 'POST',
      url: "messup.php",
    //   async: true,
    //   caches: true,
      data: {
          q: "massup"
      }, 
      success: function(response) {
        resv = response;
         console.log(resv); 
      },      
  });
  var cc = "";
  var resv1 = resv.trim();
  if(resv == null || resv1 == ""){
  cc = "Disclaimer: viewing this website is on your own risk.All the information here is based on numeric astrology n is not related to any type of gambling . We warn you that gambling in our country may be banned or illegal .. We are not responsible for any issue or scam.. We respect all country rules/laws..if you not agree with our site disclaimer..please quit our site right now . ";
  }else{
  cc = resv.toString();
  } 
  //console.log(cc);
  document.getElementById('marqu').innerHTML = '<marquee direction="left">'+cc+'</marquee>'; 
  }




 function chartres(){
 document.getElementById("qdiv").style.display = "flex";
    var sdate = []; var zdatee = [];var resxx = [];var ccx = [];
    for (let i = 0; i < aptime.length; i++) { 
    ccx.splice(0,0,aptime[i]+",--- = --");         
    }
    ccx.reverse();
   sdate.splice(0,0,zzdate(0));
   sdate.splice(0,0,zzdate(1));
   sdate.splice(0,0,zzdate(2));
   sdate.splice(0,0,zzdate(3));
   sdate.splice(0,0,zzdate(4));  
for (let i = 0; i < sdate.length; i++) {
    var mid = sdate[i];
    $.ajax({
        type: 'POST',
        url: "allres.php",
        async: false,
        data: {
      dd: mid,
        }, 
        success: function(response) {
            var wlm = response.toString();
            console.log(wlm);
            var rxx = wlm.substring(0, 2);
            if(rxx != "<b" ){
         var resw = wlm.substring(0, wlm.length-1);
            var tmxx = resw.split("-");
        for (let i = 0; i < tmxx.length; i++) {
               var tma = tmxx[i].split(",");
               if(tmxx[i].length == 5){
                var trx = nxtime(tmxx[i]);
                resxx.splice(0,0,trx+",--- = --");  
               }else{
            if(tma.length == 2 ){         
               resxx.splice(0,0,tmxx[i]);
            }else{
              var trx = nxtime(tmxx[i]);
              resxx.splice(0,0,trx+",--- = --");  
            }}

        }
              }else{
                for (let i = 0; i < ccx.length; i++) {
                  resxx.splice(0,0,ccx[i]);                    
                }
              }  
              var qdate = mid.split("_");
              var zdate = qdate[0]+"-"+qdate[1]+"-"+qdate[2];
              zdatee.splice(0,0,zdate); 
            },      
        }); 
    }     
      resxx.reverse();
      // console.log(resxx.length+" AAAAA "+resxx);
      var jres0 = []; var jres1 = []; var jres2 = []; var jres3 = []; var jres4 = [];
      for (let i = 0; i < resxx.length; i++) {
        var dz = 57;
        for (let j = 0; j < 57; j++) {
        if(i == dz*0){
       jres0.splice(0,0,resxx[j]);
        }
        if(i == dz*1){
            jres1.splice(0,0,resxx[(dz*1)+j]);
             }
             if(i == dz*2){
                jres2.splice(0,0,resxx[(dz*2)+j]);
                 }
                 if(i == dz*3){
                    jres3.splice(0,0,resxx[(dz*3)+j]);
                     }
                     if(i == dz*4){
                        jres4.splice(0,0,resxx[(dz*4)+j]);
                         }
      }}
 var zres = [];
 for (let i = 0; i < jres0.length; i++) {
    var tma4 = jres4[i].split(",");
    zres.splice(0,0,tma4[1]);
    var trx = nxtime(tma4[0]);
        zres.splice(0,0,trx);
    var tma3 = jres3[i].split(",");
    zres.splice(0,0,tma3[1]);
    var trx = nxtime(tma3[0]);
    zres.splice(0,0,trx);
    var tma2 = jres2[i].split(",");
    zres.splice(0,0,tma2[1]);
    var trx = nxtime(tma2[0]);
    zres.splice(0,0,trx);
    var tma1 = jres1[i].split(",");
    zres.splice(0,0,tma1[1]);
    var trx = nxtime(tma1[0]);
    zres.splice(0,0,trx);
      var tma = jres0[i].split(",");
        zres.splice(0,0,tma[1]);
        var trx = nxtime(tma[0]);
        zres.splice(0,0,trx);               
 }
        var td2 = ''; var row2 = '';
        td2 += '<td id="tdmxx" >'+zdatee[4]+'</td>'; 
        td2 += '<td id="tdmxx" >'+zdatee[3]+'</td>';
        td2 += '<td id="tdmxx" >'+zdatee[2]+'</td>'; 
        td2 += '<td id="tdmxx" >'+zdatee[1]+'</td>'; 
        td2 += '<td id="tdmxx" >'+zdatee[0]+'</td>'; 
      
row2 += '<tr Id="trm">'+td2+'</tr>';
document.getElementById('qdiv').innerHTML = '<table Id="tbm" border="1" style="width: 100%"><tbody id="bbz">'+row2+'</tbody></table>';
var row1 = '';var td1 = '';
td1 += '<td id="tdmx" >'+"Time"+'</td>';
td1 += '<td id="tdmx" >'+'Result'+'</td>'; 
td1 += '<td id="tdmx" >'+'Time'+'</td>';
td1 += '<td id="tdmx" >'+'Result'+'</td>'; 
td1 += '<td id="tdmx" >'+'Time'+'</td>'; 
td1 += '<td id="tdmx" >'+'Result'+'</td>'; 
td1 += '<td id="tdmx" >'+'Time'+'</td>'; 
td1 += '<td id="tdmx" >'+'Result'+'</td>';
td1 += '<td id="tdmx" >'+'Time'+'</td>'; 
td1 += '<td id="tdmx" >'+'Result'+'</td>'; 
row1 += '<tr Id="trm">'+td1+'</tr>';
for(let i = 0;i<57;i++){
  var  td = '';
  var nh = i*10;
for(let j = 0; j <10;j++){
    td += '<td id="tdmm" >'+zres[nh+j]+'</td>';    
}
row1 += '<tr Id="trmm">'+td+'</tr>';
}
document.getElementById('xdiv').innerHTML = '<table Id="tbm" border="1" style="width: 100%"><tbody id="bbz">'+row1+'</tbody></table>';  
}
   
   

function nxtime(ntime) {
  var ttime = "";
  for (let i = 0; i < Resk.length; i++) {
    if(ntime == Resk[i]){
      ttime = aptime[i];
    }   
  }
  return ttime;
}






function ppdate() {
  var today = new Date();
var dd = today.getDate().toString();
var mm = (today.getMonth() + 1).toString(); //January is 0!
// dd = dd - 1;
var yyyy = today.getFullYear().toString();
if (dd < 10) {
  dd = "0" + dd;
} 
if (mm < 10) {
  mm = '0' + mm;
} 
ddate = yyyy + '-' + mm + '-' + dd;
var pdate = ddate.split("-");
var qdate = pdate[2]+"_"+pdate[1]+"_"+pdate[0]; 
restable(qdate);
}





function showTime(){
  var Rtime = ["11:01:30","12:01:30","13:01:30","14:01:30","15:01:30","16:01:30","17:01:30","18:01:30","19:01:30","20:01:30","21:01:30"];

  var date = new Date();
  var h = date.getHours(); // 0 - 23
  var m = date.getMinutes(); // 0 - 59
  var s = date.getSeconds(); // 0 - 59
  var session = "AM";

  var hh = h;
  var mm = m;
  var ss = s;
  hh = (hh < 10) ? "0" + hh : hh;
  mm = (mm < 10) ? "0" + mm : mm;
  ss = (ss < 10) ? "0" + ss : ss;
  var ttime = hh + ":" + mm + ":" + ss;
  document.getElementById("xtime").value = ttime;

  for(let i=0;i<Restime.length;i++){
    if(Restime[i] === ttime){
 //    ressend(Restime[i]);
         break;
       }
    }
       for (let i = 0; i < Rtime.length; i++) {
        if(ttime === Rtime[i]){
        window.location.reload();
        }  
       }
  setTimeout(showTime, 1000);
}





function frefesh() {
  window.location.reload(); 
}





function iaaa(ass){
  for (let i = 0; i < Restime.length; i++) {
      var xxzz = Restime[i].toString();
      var cczz = xxzz.substring(0,5);
    if(cczz == ass){
return i;
    }
      
  }
}








function restable(adate) {
  document.getElementById("qdiv").style.display = "none";
  var resv = [];var tmxx = [];var resxx = [];var ccx = [];
  var qdate = adate.split("_");
  var qq = qdate[0]+"_"+qdate[1]+"_"+qdate[2];
  for (let i = 0; i < Resk.length; i++) { 
    ccx.splice(0,0,Resk[i]+",--- = --");         
    }
  $.ajax({
      type: 'POST',
      url: "allres.php",
      data: {
          dd: qq
      }, 
      success: function(response) {
          var wlm = response.toString();
          console.log(wlm);
          var rxx = wlm.substring(0, 3);
          // console.log(rxx);
          if(rxx != "<br" ){
       var resw = wlm.substring(0, wlm.length-1);
           tmxx = resw.split("-");
      for (let i = 0; i < tmxx.length; i++) {
          if(tmxx[i].length > 7){
          var tma = tmxx[i].split(",");
           var trx = nxtime(tma[0]);
          resxx.splice(0,0,trx);    
          resxx.splice(0,0,tma[1]);
          }else{
           var trx = nxtime(tmxx[i]);
              resxx.splice(0,0,trx);
              resxx.splice(0,0,"----");
          }   
      }
            }else{
              for (let i = 0; i < ccx.length; i++) {
                var tma = ccx[i].split(",");
                resxx.splice(0,0,tma[1]);
                 var trx = nxtime(tma[0]);
          resxx.splice(0,0,trx);                
              }
              resxx.reverse();
            }
            var zdate = qdate[0]+"-"+qdate[1]+"-"+qdate[2];
            resxx.splice(0,0,"----");
            resxx.splice(0,0,"----");
    resxx.reverse();
      var resv1 = [];
    //   resv1.splice(0,0,"Result");
    //      resv1.splice(0,0,"Time");
        resv1.splice(0,0,"Result");
        resv1.splice(0,0,"Time");
      resv1.splice(0,0,"Result");
        resv1.splice(0,0,"Time");
        resv1.splice(0,0,"Result");
        resv1.splice(0,0,"Time");
        resv1.splice(0,0,"No");
      var row1 = '';var td1 = '';var td2 = '';
td1+= '<div id="rdate">'+zdate+'</div>';
row1 += '<tr Id="trm">'+td1+'</tr>';
td2 += '<td id="tdmx" >'+resv1[0]+'</td>'; 
td2 += '<td id="tdmx" >'+resv1[1]+'</td>'; 
td2 += '<td id="tdmx" >'+resv1[2]+'</td>'; 
td2 += '<td id="tdmx" >'+resv1[3]+'</td>'; 
td2 += '<td id="tdmx" >'+resv1[4]+'</td>';
td2 += '<td id="tdmx" >'+resv1[5]+'</td>'; 
td2 += '<td id="tdmx" >'+resv1[6]+'</td>'; 
// td2 += '<td id="tdmx" >'+resv1[7]+'</td>'; 
// td2 += '<td id="tdmx" >'+resv1[8]+'</td>'; 
row1 += '<tr Id="trmx">'+td2+'</tr>';
for(let i = 0;i<19;i++){
var  td = '';
var nh = i*6;
for(let j = 0; j <6;j++){
if(j == 0){
  td += '<td id="tdm" >'+(i+1)+'</td>';   
}
  td += '<td id="tdm" >'+resxx[nh+j]+'</td>';    
}
row1 += '<tr Id="trm">'+td+'</tr>';
}
document.getElementById('xdiv').innerHTML = '<table Id="tbm" border="1" style="width: 100%"><tbody id="bbz">'+row1+'</tbody></table>';  

      },      
  });   
}


function getdat() {
    var edate = document.getElementById("datepicker").value;
    var pdate = edate.split("-");
    var qdate = pdate[2]+"_"+pdate[1]+"_"+pdate[0]; 
    // console.log(qdate);
    restable(qdate);
}














