$(function(){
  $("#product_history").addClass("open");
  $("#injection").addClass("active");

  injection_status(page_no, per_page, sort, order);
});
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
$("#basicSelect").on("change", function(){
  if($(this).val() == "date1"){
    sort = "order_date";
    order = "desc";
    injection_status(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "order_date";
    order = "asc";
    injection_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "process_percent";
    order = "asc";
    injection_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "process_percent";
    order = "desc";
    injection_status(page_no, per_page, sort, order);
  }
});

function injection_status(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/injection/main/index.php",
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
        text +='<tr>';
        text +='  <th>'+jsonResult[i].RNUM+'</th>';
        text +='  <td>'+jsonResult[i].asset_name+'</td>';
        text +='  <td>'+jsonResult[i].ord+'</td>';
        text +='  <td>'+jsonResult[i].order_no+'</td>';
        text +='  <td>'+jsonResult[i].jaje_code+'</td>';
        text +='  <td>'+jsonResult[i].mold_code+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td>'+jsonResult[i].order_date+'</td>';
        text +='  <td>'+jsonResult[i].request_date+'</td>';
        text +='  <td>'+jsonResult[i].process_qty+'</td>';
        text +='  <td>'+jsonResult[i].product_qty+'</td>';
        text +='  <td>';
        text +='    <div class="progress progress-sm mb-0 box-shadow-2">';
        text +='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
        text +='    </div>'+jsonResult[i].process_percent+'%';
        text +='  </td>';
        text +='</tr>';
      }
      $("#injection_status_list").empty();
      $("#injection_status_list").append(text);

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
    text +='<a class="page-link" href="./injection_status.html?page_no='+pre_no+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./injection_status.html?page_no='+k+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./injection_status.html?page_no='+k+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./injection_status.html?page_no='+next_no+'&sort='+sort+'&order='+order+'&sort_select='+$("#basicSelect").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

 $("#search_btn").on("click", function(){
   location.href="./injection_status.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
 });
