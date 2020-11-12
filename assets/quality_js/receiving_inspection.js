$(function(){
  $("#quality_management").addClass("open");
  $("#receiving_inspection").addClass("active");
  if($("#quality_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#receiving_inspection").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  user_list(page_no, per_page);
});

var page_no = "";
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
var type_id = getParam("type");

if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
if(getParam("search_text") == ""){
  search_text = "";
}else{
  $("#search_text").val(search_text);
}
if(getParam("type") == ""){
  type_id = "";
}else{
  $("#type_id").val(type_id);
}


function user_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/import/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        pass: "Y"
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    var jsonResult = result.data;
    console.log(jsonResult);
    if(result.status == 200){
      for(var i in jsonResult){
        text +='<tr>';
        text +='  <td>'+jsonResult[i].RNUM+'</td>';
        text +='  <td>'+jsonResult[i].type+'</td>';
        text +='  <td>'+jsonResult[i].testDate+'</td>';
        text +='  <td>'+jsonResult[i].manager+'</td>';
        text +='  <td></td>';
        text +='  <td>'+jsonResult[i].companyName+'</td>';
        text +='  <td>'+jsonResult[i].code+'</td>';
        text +='  <td>'+jsonResult[i].modelName+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td>'+comma(jsonResult[i].ipgoQuantity)+'</td>';
        text +='  <td>'+comma(jsonResult[i].testQuantity)+'</td>';
        text +='  <td>'+comma(jsonResult[i].defectQuantity)+'</td>';
        text +='  <td>'+jsonResult[i].defectPercent+'</td>';
        text +='  <td>'+jsonResult[i].pass+'</td>';
        text +='</tr>';
      }
      $("#inspection_list").empty();
      $("#inspection_list").append(text);

      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(result, textStatus, errorThrown){
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
    text +='<a class="page-link" href="./receiving_inspection.html?page_no='+pre_no+'&search_text='+$("#search_text").val()+'&type='+$("#type_id").val()+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./receiving_inspection.html?page_no='+k+'&search_text='+$("#search_text").val()+'&type='+$("#type_id").val()+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./receiving_inspection.html?page_no='+k+'&search_text='+$("#search_text").val()+'&type='+$("#type_id").val()+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./receiving_inspection.html?page_no='+next_no+'&search_text='+$("#search_text").val()+'&type='+$("#type_id").val()+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
    $.ajax({
        type    : "GET",
        url        : "../api/user/index.php?page="+page_no+"&perPage="+per_page,
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json"
    }).done(function (data, textStatus, xhr) {

      if(data.status == 200){
        location.href="../system_management/customer_list.html?search_text="+$("#search_text").val()+"&type="+$("#type_id").val();
      }else{
        alert(data.message);
      }
    })
})
