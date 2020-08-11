$(function(){
  $("#automotive_management").addClass("open");
  $("#plating").addClass("active");
});

var page_no = "";
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}

material_list(page_no, per_page);

function material_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/stock/log/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        page: page_no,
        perPage: per_page,
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      var text = "";

      for(var i in jsonResult){
        text +='<tr>';
        text +='  <th>'+jsonResult[i].RNUM+'</th>';
        text +='  <td>'+jsonResult[i].customer_code+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td>'+jsonResult[i].car_code+'</td>';
        text +='  <td align="right">'+comma(jsonResult[i].remain_qty)+'</td>';
        text +='  <td>'+jsonResult[i].customer+'</td>';
        text +='  <td>'+jsonResult[i].supplier+'</td>';
        text +='  <td>'+jsonResult[i].name+'</td>';
        text +='  <td>';
        text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text +='      <a href="../automotive_management/plating_material_detail.html?id='+jsonResult[i].id +'">';
        text +='        <button type="button" class="btn btn-warning">상세보기</button>';
        text +='      </a>';
        text +='    </div>';
        text +='  </td>';
        text +='</tr>';
      }

      $("#material_list").empty();
      $("#material_list").append(text);

      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
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
    text +='<a class="page-link" href="./user_list.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./user_list.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}
