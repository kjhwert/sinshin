function login(){
  var user_id = $("#user_id").val();
  var user_pw = $("#user_pw").val();

  if(user_id == ""){
    alert("아이디를 입력해주세요");
    return;
  }
  if(user_pw == ""){
    alert("비밀번호를 입력해주세요");
    return;
  }

  $.ajax({
      type    : "POST",
      url        : "../api/login/index.php",
      contentType: "appxlication/json",
      dataType:"json",
      data     : JSON.stringify({
      user_id: user_id,
      user_pw: user_pw
    })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      setCookie("user_data",JSON.stringify(result.data), 1);
      if(result.data.dept_id == 12){
        location.href="./automotive_management/plating_status.html";
      }else{
        location.href="./main/main.html";
      }
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

function domain(){
	$.ajax({
      type    : "GET",
      url        : "./api/domain/index.php",
      headers : {
        "content-type": "application/json"
      },
      dataType:"json",
  }).done(function (result, textStatus, xhr) {
		var domain = result.data.url;
    localStorage.setItem("domain", domain);
	}).fail(function (result, textStatus, errorThrown) {
		alert(result.message);
	})
}

$("#user_pw").keydown(function(key) {
  if (key.keyCode == 13) {
    login();
  }
});
