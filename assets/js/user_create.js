dept_group();
rank();
user_auth();

$("#deptgroup").on("change", function(){
  var group_id = $(this).val();
  dept(group_id);
});

function dept_group(){
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
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function dept(group_id){
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
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function rank(){
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
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function user_insert(){
  var user_id = $("#user_id").val();//ID
  var user_pw = $("#user_pw").val();//비밀번호
  var user_pw2 = $("#user_pw2").val();//비밀번호확인
  var user_name = $("#user_name").val();//이름
  var user_dept_group = $("#deptgroup").val();//부서그룹
  var user_dept = $("#dept").val();//부서
  var user_tel = $("#user_tel").val();//핸드폰번호
  var user_email = $("#user_email").val();//이메일주소
  var user_rank = $("#rank").val();//직위
  var user_duty = $("#duty").val();//직책

  if(user_id == ""){alert("아이디를 입력해주세요");return;};
  if(user_pw == ""){alert("비밀번호를 입력해주세요");return;};
  if(user_pw2 == ""){alert("비밀번호 확인을 입력해주세요");return;};
  if(user_name == ""){alert("이름을 입력해주세요");return;};
  if(user_dept_group == null){alert("부서그룹을 선택해주세요");return;};
  if(user_dept == null){alert("부서를 선택해주세요");return;};
  if(user_tel == ""){alert("휴대폰번호를 입력해주세요");return;};
  if(user_email == ""){alert("이메일을 입력해주세요");return;};
  if(user_rank == null){alert("직위를 선택해주세요");return;};
  if(user_duty == ""){alert("직책을 입력해주세요");return;};

  if(user_pw != user_pw2){alert("비밀번호가 서로 다릅니다");return;};
  if(user_pw.length < 4){alert("비밀번호는 최소 4자 이상 입니다");return;};

  var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
  if (!filter.test(user_email)) {alert("유효하지 않은 이메일형식 입니다");return;};

  $.ajax({
      type    : "POST",
      url        : "../api/user/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        user_id: user_id,
        user_pw: user_pw,
        name: user_name,
        dept_id: user_dept,
        tel: user_tel,
        email: user_email,
        position: user_rank,
        duty: user_duty
      }),
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      auth_update(result.data.id);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(result, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function user_auth(){
  $.ajax({
      type    : "GET",
      url        : "../api/user/auth/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json"
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      var text = '';
      console.log(jsonResult);
      for(var i in jsonResult){
        if(jsonResult[i].has == "YES"){
          text +='<span class="tag btn-info" id="'+jsonResult[i].id+'">'+jsonResult[i].name+'</span>';
        }else{
          text +='<span class="tag btn-light" id="'+jsonResult[i].id+'">'+jsonResult[i].name+'</span>';
        }
      }
    }else{
      alert(result.message);
      return;
    }
    $("#auth_group").empty();
    $("#auth_group").append(text);

    $("#auth_group span").on("click", function(){
      if($(this).hasClass("btn-info") == true){
        $(this).removeClass("btn-info");
        $(this).addClass("btn-light");
      }else{
        $(this).addClass("btn-info");
        $(this).removeClass("btn-light");
      }
    });

  })
}

function auth_update(id){
  var activeNum = "";
  $("span.btn-info").each(function() {
    var id = this.id + ",";
    activeNum += id;
  });
  $.ajax({
      type    : "PUT",
      url        : "../api/user/auth/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        id: id,
        auth_group_id: activeNum
      })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("등록되었습니다");
      location.href="../system_management/user_list.html";
    }else{
      alert(result.message);
      return;
    }
  })
}
