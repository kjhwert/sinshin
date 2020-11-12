$(function(){
  $("#system_management").addClass("open");
  $("#data_management").addClass("active");
  if($("#system_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#data_management").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});

function automotive_insert(){
  var customer = $("#customer").val();
  var supplier = $("#supplier").val();
  var customer_code = $("#customer_code").val();
  var supply_code = $("#supply_code").val();
  var car_code = $("#car_code").val();
  var name = $("#name").val();
  var brand_code = $("#brand_code").val();
  var product_price = $("#product_price").val();
  var plating_price = $("#plating_price").val();
  var supply_price = $("#supply_price").val();
  var note1 = $("#note1").val();
  var note2 = $("#note2").val();

  if(customer == ""){alert("아이디를 입력해주세요");return;};
  if(supplier == ""){alert("비밀번호를 입력해주세요");return;};
  if(customer_code == ""){alert("비밀번호 확인을 입력해주세요");return;};
  if(supply_code == ""){alert("이름을 입력해주세요");return;};
  if(name == ""){alert("부서그룹을 선택해주세요");return;};
  if(brand_code == ""){alert("부서를 선택해주세요");return;};
  if(product_price == ""){alert("휴대폰번호를 입력해주세요");return;};
  if(plating_price == ""){alert("이메일을 입력해주세요");return;};
  if(supply_price == ""){alert("직위를 선택해주세요");return;};


  $.ajax({
      type    : "POST",
      url        : "../api/automobile/master/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        customer : customer,
        supplier : supplier,
        customer_code : customer_code,
        supply_code : supply_code,
        car_code : car_code,
        name : name,
        brand_code : brand_code,
        product_price : product_price,
        plating_price : plating_price,
        supply_price : supply_price,
        note1 : note1,
        note2 : note2
      }),
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert(result.message);
      history.back();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(result, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
