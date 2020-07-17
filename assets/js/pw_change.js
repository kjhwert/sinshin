$("#change_btn").on("click", function(){
  var user_pw = $("#user_pw").val();
  var new_pw = $("#new_pw").val();
  var new_pw2 = $("#new_pw2").val();

  if(user_pw == ""){
    alert("현재 비밀번호를 입력해주세요");
    return;
  }
  if(new_pw == ""){
    alert("새 비밀번호를 입력해주세요");
    return;
  }
  if(new_pw2 == ""){
    alert("새 비밀번호를 한번 더 입력해주세요");
    return;
  }
  if(new_pw != new_pw2){
    alert("새 비밀번호를 확인해주세요");
    return;
  }
  if(new_pw.length < 4){
    alert("비밀번호는 4자리 이상 입력해주세요");
    return;
  }

  pw_change(user_pw, new_pw);
});

function pw_change(user_pw, new_pw){
    $.ajax({
        type    : "POST",
        url        : "http://sinshin.hlabpartner.com/api/user/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data     : JSON.stringify({
        type: "pw",
        pre_pw: user_pw,
        change_pw: new_pw
      })
    }).done(function (result, textStatus, xhr) {
      if(result.status == 200){
        alert("비밀번호가 변경되었습니다");
        return;
      }else{
        alert(result.message);
      }
    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
}
