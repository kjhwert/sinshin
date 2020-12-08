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

var page_no = getParam("page_no");
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
var start_date = getParam("start_date");
var end_date = getParam("end_date");
var sort = getParam("sort");//date
var order = getParam("order");//desc
var sort_select = getParam("sort_select");

if(start_date != ""){
  $("#start_date").val(start_date);
}
if(end_date != ""){
  $("#end_date").val(end_date);
}
if(search_text != ""){
  $("#search_text").val(search_text);
}
if(getParam("sort_select") != ""){
  $("#basicSelect").val(sort_select);
}
if(getParam("page_no") == ""){
  page_no = 1;
}
if(getParam("sort") == ""){
  sort = "date";
}
if(getParam("order") == ""){
  order = "desc";
}
warehousing(page_no, per_page, sort, order);

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#search_btn").click();
  }
});

$("#basicSelect").on("change", function(){
  if($(this).val() == "date1"){
    sort = "date";
    order = "desc";
    warehousing(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "date";
    order = "asc";
    warehousing(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "product";
    order = "asc";
    warehousing(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "product";
    order = "desc";
    warehousing(page_no, per_page, sort, order);
  }
});

function warehousing(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        search: search_text,
        start_date: start_date,
        end_date: end_date,
        sort: sort,
        order: order
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      for(var i in jsonResult){
        text +='<tr>';
        text +='  <th class="text-center">'+jsonResult[i].RNUM+'</th>';
        text +='  <td>'+jsonResult[i].customer_code+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].brand_code+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].car_code+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].store_qty)+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].loss)+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].customer+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].supplier+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].charger+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].type+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].created_at+'</td>';
        text +='  <td>';
        if(jsonResult[i].type == "미처리"){
          text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
          text +='      <a href="../automotive_management/plating_warehousing_update.html?id='+jsonResult[i].id+'">';
          text +='        <button type="button" class="btn btn-warning">검사 및 수정</button>';
          text +='      </a>';
          text +='    </div>';
        }
        text +='  </td>';
        text +='</tr>';
      }
      $("#warehousing_list").empty();
      $("#warehousing_list").append(text);
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
    text +='<a class="page-link" href="./plating_warehousing.html?page_no='+pre_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./plating_warehousing.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./plating_warehousing.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./plating_warehousing.html?page_no='+next_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}
$("#search_btn").on("click", function(){
  location.href="../automotive_management/plating_warehousing.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
});
