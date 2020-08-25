
$(function(){
  $("#product_history").addClass("open");
  $("#injection").addClass("active");

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
var asset_id = getParam("asset_id");

if(asset_id != ""){
  $("#asset_id").val(asset_id);
}
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
    injection_start(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "date";
    order = "asc";
    injection_start(page_no, per_page, sort, order);
  }else if($(this).val() == "asset1"){
    sort = "asset";
    order = "asc";
    injection_start(page_no, per_page, sort, order);
  }else if($(this).val() == "asset2"){
    sort = "asset";
    order = "desc";
    injection_start(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "asset";
    order = "asc";
    injection_start(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "asset";
    order = "desc";
    injection_start(page_no, per_page, sort, order);
  }
});


function injection_start(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/complete/index.php",
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
        asset_id: asset_id,
        search_text: search_text,
        start_date: start_date,
        end_date: end_date
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);

      for(var i in jsonResult){
        text+='<tr>';
        text+='  <td>'+jsonResult[i].RNUM+'</td>';
        text+='  <td>'+jsonResult[i].process_date+'</td>';
        text+='  <td>'+jsonResult[i].asset_name+'</td>';
        text+='  <td>'+jsonResult[i].order_no+'</td>';
        text+='  <td>'+jsonResult[i].product_name+'</td>';
        text+='  <td>'+jsonResult[i].material_name+'</td>';
        text+='  <td>'+jsonResult[i].jaje_code+'</td>';
        text+='  <td>'+jsonResult[i].product_qty+'</td>';
        text+='  <td>';
        text+='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text+='      <a href="../product_history/injection_end_print.html?id='+jsonResult[i].id+'"><button type="button" class="btn btn-bg-gradient-x-purple-blue">출력</button></a>&nbsp;';
        text+='      <a href="../product_history/injection_end_detail.html?id='+jsonResult[i].id+'"><button type="button" class="btn btn-warning">상세보기</button></a>';
        text+='    </div>';
        text+='  </td>';
        text+='</tr>';
      }
      $("#injection_start_list").empty();
      $("#injection_start_list").append(text);

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
    text +='<a class="page-link" href="./injection_end.html?page_no='+pre_no+'&search_text+'+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./injection_end.html?page_no='+k+'&search_text+'+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./injection_end.html?page_no='+k+'&search_text+'+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./injection_end.html?page_no='+next_no+'&search_text+'+search_text+'&start_date='+start_date+'&end_date='+end_date+'&asset_id='+asset_id+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="../product_history/injection_end.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val()+"&asset_id="+$("#asset_id").val();
});