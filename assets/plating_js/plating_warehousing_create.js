$(function(){
  $("#automotive_management").addClass("open");
  $("#plating").addClass("active");
  if($("#automotive_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#plating").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});
$("#customer_code").on("click", function(){
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#search_btn").click();
  }
});

$("#modal_back").on("click", function(){
  modal_off();
});
function modal_off(){
  $("#search_modal").fadeOut(300);
  $("#modal_back").fadeOut(300);
}
$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();
  if(search_text == ""){
    alert("품명을 입력해주세요");
    return;
  }else{
    auto_search(search_text);
  }
});


function auto_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/master/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        paging: "false",
        search: search
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");
      var text = "<tr>";
          text += "<th>품번</th>";
          text += "<th>차종</th>";
          text += "</tr>";

      for(var i in jsonResult){
        text +='<tr data-customer='+jsonResult[i].customer+' data-supplier='+jsonResult[i].supplier+' data-product_name='+jsonResult[i].name.replace(/\s/gi, "_")+' data-product_id='+jsonResult[i].id+' data-brand_code='+jsonResult[i].brand_code+' data-car_code='+jsonResult[i].car_code+' data-customer_code='+jsonResult[i].customer_code+'>';
        text +="<td>"+jsonResult[i].customer_code+"</td>";
        text +="<td>"+jsonResult[i].car_code+"</td>";
        text +="</tr>";
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var customer_data = $(this).data("customer");
        var supplier_data = $(this).data("supplier");
        var product_name = $(this).data("product_name");
        var product_id = $(this).data("product_id");
        var customer_code = $(this).data("customer_code");
        var brand_car = $(this).data("brand_code") +"/"+ $(this).data("car_code");
        $("#customer").val(customer_data);
        $("#supplier").val(supplier_data);
        $("#product_name").val(product_name);
        $("#product_id").val(product_id);
        $("#brand_car").val(brand_car);
        $("#customer_code").val(customer_code);
        $("#search_modal").fadeOut("300");
        $("#modal_back").fadeOut("300");
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#plating_create").on("click", function(){
  var product_id = parseInt($("#product_id").val());
  var mfr_date = $("#mfr_date").val();
  var store_qty = parseInt($("#store_qty").val());
  var input_date = $("#input_date").val();

  if(product_id == ""){alert("품명을 선택해주세요");return;}
  if(mfr_date == ""){alert("제조일자를 선택해주세요");return;}
  if(store_qty == ""){alert("수량을 입력해주세요");return;}
  if(input_date == ""){alert("입고일자를 선택해주세요");return;}

  $.ajax({
      type    : "POST",
      url        : "../api/automobile/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : JSON.stringify({
        product_id: product_id,
        mfr_date: mfr_date,
        store_qty: store_qty,
        input_date: input_date
      })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("입고 등록 되었습니다");
      location.href="../automotive_management/plating_warehousing.html";
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
});
