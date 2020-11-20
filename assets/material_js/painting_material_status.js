$(document).ready(function(){
  $("#product_history").addClass("open");
  $("#material").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#material").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});
var page_no = "";
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
var start_date = getParam("start_date");
var end_date = getParam("end_date");
let today = new Date();


if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
if(search_text != ""){
  $("#search_text").val(search_text);
}
if(start_date != ""){
  $("#start_date").val(start_date);
}
if(end_date != ""){
  $("#end_date").val(end_date);
}

stock_list(page_no, per_page, search_text);

function stock_list(page_no, per_page, search_text){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        type: "warehouse",
        page: page_no,
        perPage: per_page,
        material_type: "CO",
        search: search_text,
        start_date: start_date,
        end_date: end_date
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      var text = '';
      for(var i in jsonResult){
        text+='<tr>';
        text+='  <td class="text-center">'+jsonResult[i].RNUM+'</td>';
        text+='  <td class="text-center">'+jsonResult[i].code+'</td>';
        text+='  <td>'+jsonResult[i].name+'</td>';
        text+='  <td class="text-right">'+comma(jsonResult[i].total)+'</td>';
        text+='  <td class="text-center">'+jsonResult[i].unit+'</td>';
        text+='  <td class="text-center">'+jsonResult[i].stock_date+'</td>';
        text+='  <td class="text-center">'+jsonResult[i].manager+'</td>';
        text+='</tr>';
      }
      $("#search_list").empty();
      $("#search_list").append(text);

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
    text +='<a class="page-link" href="./painting_material_status.html?page_no='+pre_no+'&start_date='+start_date+'&end_date='+end_date+'&search_text='+search_text+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./painting_material_status.html?page_no='+k+'&start_date='+start_date+'&end_date='+end_date+'&search_text='+search_text+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./painting_material_status.html?page_no='+k+'&start_date='+start_date+'&end_date='+end_date+'&search_text='+search_text+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./painting_material_status.html?page_no='+next_no+'&start_date='+start_date+'&end_date='+end_date+'&search_text='+search_text+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="../product_history/painting_material_status.html?search_text="+$("#search_text").val()+"&start_date="+$("#start_date").val()+'&end_date='+$("#end_date").val();
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#search_btn").click();
  }
});

$("#material_type").on("change", function(){
  if($(this).val() == "IN"){
    location.href="./material_status.html";
  }
});
