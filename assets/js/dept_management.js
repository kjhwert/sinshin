$(function(){
  $("#system_management").addClass("open");
  $("#authority_management").addClass("active");

  if($("#system_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#authority_management").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  dept_management();
  dept_group();
});


function auth_modal_on(){
  $("#auth_modal").fadeIn(300);
  $("#modal_back").fadeIn(300);
}

function dept_management(){
  $.ajax({
      type    : "GET",
      url        : "../api/auth/list/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data: {
        id: getParam("id")
      }
  }).done(function (response, textStatus, xhr) {
    var text1 = '';
    var text2 = '';
    var text3 = '';
    var text4 = '';
    var text5 = '';
    var text6 = '';
    if(response.status == 200){

      for(var i in response.data){
        if(response.data[i].menu == "제품이력관리"){
          if(response.data[i].has == "YES"){
            text1 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text1 += response.data[i].function;
            text1 +='</span>';
          }else{
            text1 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text1 += response.data[i].function;
            text1 +='</span>';
          }
        }else if(response.data[i].menu == "생산관리"){
          if(response.data[i].has == "YES"){
            text2 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text2 += response.data[i].function;
            text2 +='</span>';
          }else{
            text2 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text2 += response.data[i].function;
            text2 +='</span>';
          }
        }else if(response.data[i].menu == "품질관리"){
          if(response.data[i].has == "YES"){
            text3 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text3 += response.data[i].function;
            text3 +='</span>';
          }else{
            text3 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text3 += response.data[i].function;
            text3 +='</span>';
          }
        }else if(response.data[i].menu == "모니터링"){
          if(response.data[i].has == "YES"){
            text4 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text4 += response.data[i].function;
            text4 +='</span>';
          }else{
            text4 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text4 += response.data[i].function;
            text4 +='</span>';
          }
        }else if(response.data[i].menu == "자동차공정"){
          if(response.data[i].has == "YES"){
            text5 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text5 += response.data[i].function;
            text5 +='</span>';
          }else{
            text5 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text5 += response.data[i].function;
            text5 +='</span>';
          }
        }else if(response.data[i].menu == "시스템관리"){
          if(response.data[i].has == "YES"){
            text6 +='<span class="tag btn-info" id='+response.data[i].id+'>';
            text6 += response.data[i].function;
            text6 +='</span>';
          }else{
            text6 +='<span class="tag btn-light" id='+response.data[i].id+'>';
            text6 += response.data[i].function;
            text6 +='</span>';
          }
        }
      }

      $("#dept_menu1").empty();
      $("#dept_menu2").empty();
      $("#dept_menu3").empty();
      $("#dept_menu4").empty();
      $("#dept_menu5").empty();
      $("#dept_menu6").empty();
      $("#dept_menu1").append(text1);
      $("#dept_menu2").append(text2);
      $("#dept_menu3").append(text3);
      $("#dept_menu4").append(text4);
      $("#dept_menu5").append(text5);
      $("#dept_menu6").append(text6);

      $("#dept_management span").on("click", function(){
        if($(this).hasClass("btn-info") == true){
          $(this).removeClass("btn-info");
          $(this).addClass("btn-light");
        }else{
          $(this).addClass("btn-info");
          $(this).removeClass("btn-light");
        }
      });
    }else{
      alert(data.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

$("#modify_btn").on("click", function(){
  var activeNum = "";
  $("span.btn-info").each(function() {
    var id = this.id + ",";
    activeNum += id;
  });

  dept_modify(activeNum);
});
function dept_modify(activeNum){
  $.ajax({
      type    : "PUT",
      url        : "../api/auth/list/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data: JSON.stringify({
        auth_master_id: activeNum,
        auth_group_id: getParam("id")
      })
  }).done(function (response, textStatus, xhr) {
    if(response.status == 200){
      alert('등록되었습니다.');
      location.reload();
    }
  })
}

function dept_group() {
  $.ajax({
    type    : "GET",
    url        : "../api/auth/group/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data: {
      id: getParam("id"),
    }
  }).done(function (response, textStatus, xhr) {
    $("#group_name").val(response.data[0].name);
  })
}
