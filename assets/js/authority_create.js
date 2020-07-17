$("#auth_create").on("click", function(){
  var group_name = $("#group_name").val();

  if(group_name == ""){
    alert("그룹명을 입력해주세요");
    return;
  }
  auth_create(group_name);
});

function auth_create(group_name){
    $.ajax({
        type    : "POST",
        url        : "../api/auth/group/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data     : JSON.stringify({
        name: group_name
      })
    }).done(function (data, textStatus, xhr) {
      if(data.status == 200){
        alert("그룹이 등록되었습니다");
        location.href="../system_management/authority_list.html";
      }else{
        alert(data.message);
        return;
      }
      console.log(data);
    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
}
