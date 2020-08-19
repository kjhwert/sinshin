$(function(){
  $("#automotive_management").addClass("open");
  $("#plating").addClass("active");
});
var page_no = "";
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
var start_date = getParam("start_date");
var end_date = getParam("end_date");
var sort = "date";
var order = "desc";

if(start_date != ""){
  $("#start_date").val(start_date);
}
if(end_date != ""){
  $("#end_date").val(end_date);
}
if(search_text != ""){
  $("#search_text").val(search_text);
}

if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
plating_status(page_no, per_page, sort, order);

$("#basicSelect").on("change", function(){
  if($(this).val() == "date1"){
    sort = "date";
    order = "desc";
    plating_status(page_no, per_page, sort, order);
  }else if($(this).val() == "date2"){
    sort = "date";
    order = "asc";
    plating_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product1"){
    sort = "product";
    order = "asc";
    plating_status(page_no, per_page, sort, order);
  }else if($(this).val() == "product2"){
    sort = "product";
    order = "desc";
    plating_status(page_no, per_page, sort, order);
  }
});

function plating_status(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/process/index.php",
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
      console.log(jsonResult);
      for(var i in jsonResult){
        if(jsonResult[i].type == "mutable"){
          text +='<tr>';
        }else{
          text +='<tr class="inmutable">';
        }
        text +='  <th>'+jsonResult[i].RNUM+'</th>';
        text +='  <td>'+jsonResult[i].lot_no+'</td>';
        text +='  <td>'+jsonResult[i].customer_code+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td>'+jsonResult[i].car_code+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].input)+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].output)+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].defect)+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].loss)+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].drop_qty)+'</td>';
        text +='  <td>'+jsonResult[i].charger+'</td>';
        text +='  <td>'+jsonResult[i].created_at+'</td>';
        text +='  <td>';
        text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text +='      <a href="../automotive_management/plating_read.html?id='+jsonResult[i].id+'">';
        text +='        <button type="button" class="btn btn-warning">상세보기</button>';
        text +='      </a>&nbsp;';
        if(jsonResult[i].memo == ""){
          text +='      <a onclick="memo_modal('+jsonResult[i].id+');">';
          text +='        <button type="button" class="btn btn-light">메모</button>';
          text +='      </a>';
        }else{
          text +='      <a onclick="memo_modal('+jsonResult[i].id+');">';
          text +='        <button type="button" class="btn btn-info">메모</button>';
          text +='      </a>';
        }
        text +='    </div>';
        text +='  </td>';
        text +='</tr>';
      }
      $("#plating_status_list").empty();
      $("#plating_status_list").append(text);

      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function memo_modal(id){
  $("#modal_back").fadeIn("300");
  $("#memo_modal").fadeIn("300");
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/process/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: id
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      $("#memo").val(result.data.memo);
      $("#memo_id").val(id);
    }else{
      alert(result.message);
    }
  });
}

function modal_off(){
  $("#modal_back").fadeOut("300");
  $("#memo_modal").fadeOut("300");
}

function memo_save(){
  $.ajax({
      type    : "PUT",
      url        : "../api/automobile/process/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data: JSON.stringify({
        id: $("#memo_id").val(),
        memo: $("#memo").val()
      })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("저장 되었습니다");
      location.reload();
    }else{
      alert(result.message);
    }
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
    text +='<a class="page-link" href="./plating_status.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./plating_status.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./plating_status.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./plating_status.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="../automotive_management/plating_status.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
});
