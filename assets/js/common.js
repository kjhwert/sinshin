var user_data = JSON.parse(getCookie("user_data"));
var path = $(location).attr('href');

// user_log();

function getHeader(){
	var url;
		url = '../include/header.html'
		$.ajax({
			url:url,
			type:'GET',
			async:false
		}).done(function(result){
			document.write(result);
		});
}

function getParam(sname) {
    var params = location.search.substr(location.search.indexOf("?") + 1);
    var sval = "";
    params = params.split("&");
    for (var i = 0; i < params.length; i++) {
        temp = params[i].split("=");
        if ([temp[0]] == sname) { sval = temp[1]; }
    }
    return sval;
}
function comma(str) {
    str = String(str);
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}
function uncomma(str) {
    str = String(str);
    return str.replace(/[^\d]+/g, '');
}
function cancel(){
	history.back();
}

function setCookie(cookie_name, value, days) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + days);
  // 설정 일수만큼 현재시간에 만료값으로 지정

  var cookie_value = escape(value) + ((days == null) ? '' : ';    expires=' + exdate.toUTCString());
  document.cookie = cookie_name + '=' + cookie_value+"; path=/";
}
///////////////////////////////////////////////////////////////

function deleteCookie(cookieName){
   var expireDate = new Date();
   expireDate.setDate(expireDate.getDate() - 1);
   document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
	 location.href="/login.html";
}

function getCookie(cookieName) {
   cookieName = cookieName + '=';
   var cookieData = document.cookie;
   var start = cookieData.indexOf(cookieName);
   var cookieValue = '';
   if(start != -1){
      start += cookieName.length;
      var end = cookieData.indexOf(';', start);
      if(end == -1)end = cookieData.length;
      cookieValue = cookieData.substring(start, end);
   }
   return unescape(cookieValue);
}

function logout(){
	deleteCookie("user_data");
}

function user_log(){
	$.ajax({
      type    : "POST",
      url        : "../api/user/log/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        path: path
      }),
  }).done(function (result, textStatus, xhr) {
		if(result.status == 200){
			console.log("로그기록성공");
		}else if(result.status == 401){
			alert(result.message);
			location.href="/login.html";
		}else{
			console.log(result.message);
		}
	}).fail(function (result, textStatus, errorThrown) {
		alert(result.message);
	})
}
