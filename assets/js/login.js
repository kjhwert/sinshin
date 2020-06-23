
$("#login_btn").on("click", function(){
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
  login(user_id, user_pw);
});

function login(user_id, user_pw){
    $.ajax({
        type    : "POST",
        url        : "./api/login/",
        contentType: "application/json",
        dataType:"json",
        data     : JSON.stringify({
          user_id: user_id,
          user_pw: user_pw
        })
    }).done(function (data, textStatus, xhr) {
      if(data.status == 200){
        alert("로그인 성공");
      }else{
        alert(data.message);
      }
      console.log(data);
    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
        callback(data);
    });
}
