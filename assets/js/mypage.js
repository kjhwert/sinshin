//getParam("user_no");
user_select();

$("#deptgroup").on("change", function(){
  var group_id = $(this).val();
  dept(group_id);
});

function dept_group(group_id){
  $.ajax({
      type    : "GET",
      url        : "../api/code/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "dept-group"
      },
  }).done(function (response, textStatus, xhr) {
    var text = '<option selected disabled hidden>부서그룹을 선택해주세요</option>';
    if(response.status == 200){
      var jsonResult = response.data;
      for(var i in jsonResult){
        text +='<option value="'+jsonResult[i].id+'">'+jsonResult[i].name+'</option>';
      }
      $("#deptgroup").empty();
      $("#deptgroup").append(text);
      $("#deptgroup").val(group_id);
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function dept(group_id, select_id){
  $.ajax({
      type    : "GET",
      url        : "../api/code/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "dept",
        group_id: group_id
      },
  }).done(function (response, textStatus, xhr) {
    var text = '<option selected disabled hidden>부서를 선택해주세요</option>';
    if(response.status == 200){
      var jsonResult = response.data;
      for(var i in jsonResult){
        text +='<option value="'+jsonResult[i].id+'">'+jsonResult[i].name+'</option>';
      }
      $("#dept").empty();
      $("#dept").append(text);
      $("#dept").val(select_id);
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function rank(rank_id){
  $.ajax({
      type    : "GET",
      url        : "../api/code/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "code",
        group_id: 1
      },
  }).done(function (response, textStatus, xhr) {
    var text = '<option selected disabled hidden>직위를 선택해주세요</option>';
    if(response.status == 200){
      var jsonResult = response.data;
      for(var i in jsonResult){
        text +='<option value="'+jsonResult[i].id+'">'+jsonResult[i].name+'</option>';
      }
      $("#rank").empty();
      $("#rank").append(text);
      $("#rank").val(rank_id);
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function user_select(){
  $.ajax({
      type    : "GET",
      url        : "../api/user/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: user_data.id
      },
  }).done(function (response, textStatus, xhr) {
    if(response.status == 200){
      var jsonResult = response.data;
      console.log(jsonResult);
      $("#user_id").val(response.data.user_id);
      $("#user_name").val(response.data.name);
      $("#user_tel").val(response.data.tel);
      $("#user_email").val(response.data.email);
      $("#duty").val(response.data.duty);
      dept_group(response.data.dept_group_id);
      dept(response.data.dept_group_id, response.data.dept_id);
      rank(response.data.position);
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function user_update(){
  var user_id = $("#user_id").val();//ID
  var user_name = $("#user_name").val();//이름
  var user_dept_group = $("#deptgroup").val();//부서그룹
  var user_dept = $("#dept").val();//부서
  var user_tel = $("#user_tel").val();//핸드폰번호
  var user_email = $("#user_email").val();//이메일주소
  var user_rank = $("#rank").val();//직위
  var user_duty = $("#duty").val();//직책

  if(user_id == ""){alert("아이디를 입력해주세요");return;};
  if(user_name == ""){alert("이름을 입력해주세요");return;};
  if(user_dept_group == null){alert("부서그룹을 선택해주세요");return;};
  if(user_dept == null){alert("부서를 선택해주세요");return;};
  if(user_tel == ""){alert("휴대폰번호를 입력해주세요");return;};
  if(user_email == ""){alert("이메일을 입력해주세요");return;};
  if(user_rank == null){alert("직위를 선택해주세요");return;};
  if(user_duty == ""){alert("직책을 입력해주세요");return;};

  var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
  if (!filter.test(user_email)) {alert("유효하지 않은 이메일형식 입니다");return;};

  $.ajax({
      type    : "PUT",
      url        : "../api/user/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        id: user_data.id,
        user_id: user_id,
        name: user_name,
        dept_id: user_dept,
        tel: user_tel,
        email: user_email,
        position: user_rank,
        duty: user_duty
      }),
  }).done(function (response, textStatus, xhr) {
    if(response.status == 200){
      alert("수정되었습니다");
      history.back();
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function user_delete(){
  var result = confirm('탈퇴 하시겠습니까?');
  if(result) { //yes location.replace('index.php'); } else { //no }
    $.ajax({
        type    : "DELETE",
        url        : "../api/user/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:JSON.stringify({
          id: user_data.id
        })
    }).done(function (data, textStatus, xhr) {
      var text = '';
      if(data.status == 200){
        alert("탈퇴 되었습니다");
        deleteCookie("user_data");
        location.href="/login.html";
      }else{
        alert(data.message);
        return;
      }
    }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
    });
  }else{
    return;
  }
}
