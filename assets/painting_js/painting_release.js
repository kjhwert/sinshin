
$(function(){
  $("#product_history").addClass("open");
  $("#painting").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#painting").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  painting_end(page_no, per_page, sort, order);
});

setInterval(function() {
  painting_end(page_no, per_page, sort, order);
}, reload_cycle_time);

var page_no = getParam("page_no");
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
var start_date = getParam("start_date");
var end_date = getParam("end_date");
var sort = getParam("sort");//date
var order = getParam("order");//desc
var sort_select = getParam("sort_select");
var asset_id = getParam("asset_id");
let today = new Date();

let year = today.getFullYear(); // 년도
let month = today.getMonth() + 1;  // 월
let y_month = today.getMonth();


let date = today.getDate();  // 날짜
let day = today.getDay();  // 요일

var range_date1 = (year + '-' + (("00"+y_month.toString()).slice(-2)) + '-' + date); //한달전
var range_date2 = (year + '-' + month + '-' + date); //오늘

if(asset_id != ""){
  $("#asset_id").val(asset_id);
}
if(start_date != ""){
  $("#start_date").val(start_date);
}else{
  $("#start_date").val(range_date1);
  start_date = range_date1;
}
if(end_date != ""){
  $("#end_date").val(end_date);
}else{
  $("#end_date").val(range_date2);
  end_date = range_date2;
}
if(search_text != ""){
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
$("#basicSelect").on("change", function(){
  if($(this).val() == "date1"){
    sort = "date";
    order = "desc";
    painting_end(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "date";
    order = "asc";
    painting_end(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "product";
    order = "asc";
    painting_end(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "product";
    order = "desc";
    painting_end(page_no, per_page, sort, order);
  }
});


function painting_end(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/release/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        sort: sort,
        order: order,
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
        text +='  <td class="text-center">'+jsonResult[i].RNUM+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].order_no+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td class="text-right">'+comma(jsonResult[i].box_qty)+'</td>';
        text +='  <td class="text-right">'+comma(jsonResult[i].product_qty)+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].process_date+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].to_name+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].manager+'</td>';
        text +='</tr>';
      }
      $("#painting_release_list").empty();
      $("#painting_release_list").append(text);

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
    text +='<a class="page-link" href="./painting_release.html?page_no='+pre_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./painting_release.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./painting_release.html?page_no='+k+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./painting_release.html?page_no='+next_no+'&search_text='+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="../product_history/painting_release.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val()+"&asset_id="+$("#asset_id").val();
});
