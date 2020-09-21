$(function(){
  $("#product_history").addClass("open");
  $("#painting").addClass("active");

  painting_status(page_no, per_page, sort, order);
  painting_status_cnt();
  painting_stock();
});
setDateBox();

var page_no = getParam("page_no");
var per_page = 15;
var sort = getParam("sort");//date
var order = getParam("order");//desc
var sort_select = getParam("sort_select");

if(getParam("sort_select") != ""){
  $("#basicSelect").val(sort_select);
}
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
if(getParam("sort") == ""){
  sort = "order_date";
}
if(getParam("order") == ""){
  order = "desc";
}
function setDateBox(){
  var dt = new Date();
  var year = "";
  var com_year = dt.getFullYear();
  // 발행 뿌려주기
  $("#years_select").append("<option value=''>년도</option>");
  // 올해 기준으로 -1년부터 +5년을 보여준다.
  for(var y = (com_year); y >= (com_year-15); y--){
      $("#years_select").append("<option value='"+ y +"'>"+ y + " 년" +"</option>");
  }
  // 월 뿌려주기(1월부터 12월)
  var month;
  $("#monthly_select").append("<option value=''>월</option>");
  for(var i = 1; i <= 12; i++){
      $("#monthly_select").append("<option value='"+ i +"'>"+ i + " 월" +"</option>");
  }

  let today = new Date();

  let today_year = today.getFullYear(); // 년도
  let today_month = today.getMonth() + 1;  // 월
  let today_date = today.getDate();  // 날짜
  let today_day = today.getDay();  // 요일

  $("#years_select").val(today_year);
  $("#monthly_select").val(today_month);
}

$("#search_btn_cnt").on("click", function(){
  painting_status_cnt();
});
function painting_status_cnt(){
  var years_select = $("#years_select").val();
  var monthly_select = $("#monthly_select").val();

  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "product",
        year: years_select,
        month: monthly_select
      }
  }).done(function (result, textStatus, xhr) {
    console.log(result);
    var jsonResult = result.data;
    if(result.status == 200){
      $("#stock_qty").text(comma(jsonResult.stock_qty));
      $("#start_qty").text(comma(jsonResult.start_qty));
      $("#complete_qty").text(comma(jsonResult.complete_qty));
      $("#defect_qty").text(comma(jsonResult.defect_qty));
      $("#release_qty").text(comma(jsonResult.release_qty));
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

$("#basicSelect").on("change", function(){
  if($(this).val() == "date1"){
    sort = "order_date";
    order = "desc";
    painting_status(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "order_date";
    order = "asc";
    painting_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "process_percent";
    order = "asc";
    painting_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "process_percent";
    order = "desc";
    painting_status(page_no, per_page, sort, order);
  }
});
function painting_stock(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "stock",
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      if(jsonResult.length == 0){
        text +="<li>현재 등록된 재고가 없습니다</li>";
      }else{
        for(var i in jsonResult){
          text +='<li>';
          text +='  <img src="../assets/images/pallet.png">';
          text +='  <p class="stock_name">'+jsonResult[i].product_name+'</p>';
          text +='  <p class="stock_cnt">'+jsonResult[i].box_qty+'box '+comma(jsonResult[i].product_qty)+'ea</p>';
          text +='</li>';
        }
      }
      $("#stock_ul").empty();
      $("#stock_ul").append(text);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
function painting_status(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        sort: sort,
        order: order
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text+='<tr>';
        text+='  <th>3</th>';
        text+='  <td>'+jsonResult[i].order_no+'</td>';
        text+='  <td>'+jsonResult[i].jaje_code+'</td>';
        text+='  <td>'+jsonResult[i].type+'</td>';
        text+='  <td>'+jsonResult[i].product_name+'</td>';
        text+='  <td>'+jsonResult[i].order_date+'</td>';
        text+='  <td>'+jsonResult[i].request_date+'</td>';
        text+='  <td>'+comma(jsonResult[i].process_qty)+'</td>';
        text+='  <td>'+comma(jsonResult[i].product_qty)+'</td>';
        text+='  <td>';
        text+='    <div class="progress progress-sm mb-0 box-shadow-2">';
        text+='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
        text+='    </div>'+jsonResult[i].process_percent+'%';
        text+='  </td>';
        text+='  <td>'+jsonResult[i].work_qty+'</td>';
        text+='  <td>'+jsonResult[i].humidity_max+'</td>';
        text+='  <td>'+jsonResult[i].humidity_min+'</td>';
        text+='  <td>'+jsonResult[i].humidity_average+'</td>';
        text+='  <td>'+jsonResult[i].conveyor_speed+'</td>';
        text+='  <td>';
        text+='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text+='      <a href="../product_history/painting_update.html?id='+jsonResult[i].id+'">';
        text+='        <button type="button" class="btn btn-warning">관리</button>';
        text+='      </a>';
        text+='    </div>';
        text+='  </td>';
        text+='</tr>';
      }
      $("#painting_status_list").empty();
      $("#painting_status_list").append(text);

      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}


function paging(end, start, total){
  var paging_init_num = parseInt(start);
  var paging_end_num = parseInt(end);
  var total_paging_cnt = parseInt(total);
  var pre_no = parseInt(page_no) - 1;
  var next_no = parseInt(page_no) + 1;
  var text = '';
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || pre_no == 0)
  {
  }else{
    text +='<li class="page-item">';
    text +='<a class="page-link" href="./painting_status.html?page_no='+pre_no+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./painting_status.html?page_no='+k+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./painting_status.html?page_no='+k+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./painting_status.html?page_no='+next_no+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

 $("#search_btn").on("click", function(){
   location.href="./painting_status.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
 });
