
$(function(){
  $("#production_management").addClass("open");
  $("#injection2").addClass("active");
  if($("#production_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#injection2").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  injection_start(page_no, per_page, sort, order);
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
if(getParam("search_text") != ""){
  $("#search_text").val(search_text);
}
if(getParam("sort_select") != ""){
  $("#basicSelect").val(sort_select);
}
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
if(getParam("sort") == ""){
  sort = "date";
}
if(getParam("order") == ""){
  order = "desc";
}


function injection_start(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/product/manage/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        type: "injection",
        search: search_text,
        start_date: start_date,
        end_date: end_date
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);

      for(var i in jsonResult){
        text +='<tr>';
        text +='  <td>'+jsonResult[i].RNUM+'</td>';
        text +='  <td>'+jsonResult[i].priority+'</td>';
        text +='  <td>'+jsonResult[i].machineNo+'</td>';
        text +='  <td>'+jsonResult[i].code+'</td>';
        text +='  <td>'+jsonResult[i].name+'</td>';
        text +='  <td>'+jsonResult[i].requestDeliveryDate+'</td>';
        text +='  <td>'+comma(jsonResult[i].quantity)+'</td>';
        text +='  <td>'+comma(jsonResult[i].completeQuantity)+'</td>';
        text +='  <td>'+comma(jsonResult[i].restQuantity)+'</td>';
        text +='  <td>'+jsonResult[i].capacity+'</td>';
        text +='  <td>'+jsonResult[i].cycleTime+'</td>';
        text +='  <td>'+jsonResult[i].spendTime+'</td>';
        text +='</tr>';
      }
      $("#injection_list").empty();
      $("#injection_list").append(text);

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
    text +='<a class="page-link" href="./injection.html?page_no='+pre_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./injection.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./injection.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./injection.html?page_no='+next_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="../production_management/injection.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#search_btn").click();
  }
});
