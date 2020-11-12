var page_no = "";
var per_page = 15;
if(getParam("page_no") == ""){
    page_no = 1;
}else{
    page_no = getParam("page_no");
}

$(document).ready(function(){
    stock_detail_list(page_no, per_page);
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

function stock_detail_list (page_no, per_page) {
    $.ajax({
        type    : "GET",
        url        : "../api/automobile/release/log/index.php",
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
        data:{
            page: page_no,
            perPage: per_page,
            id: getParam("id")
        }
    }).done(function (data, textStatus, xhr) {
        if (data.status != 200) {
            alert(result.message);
            return;
        }

        var results = data.data;
        var text = '';

        for (i in results) {
            text += '<tr>';
            text += '    <th class="text-center">'+results[i].RNUM+'</th>';
            text += '    <td class="text-center">'+results[i].car_code+'</td>';
            text += '    <td>'+results[i].customer_code+'</td>';
            text += '    <td>'+results[i].product_name+'</td>';
            text += '    <td class="text-center">'+results[i].customer+'</td>';
            text += '    <td class="text-center">'+results[i].supplier+'</td>';
            text += '    <td class="text-center">'+results[i].type+'</td>';
            if (results[i].change_qty < 0) {
                text += '<td class="danger" align="right">'+comma(results[i].change_qty)+'</td>';
            } else {
                text += '<td class="info" align="right">'+comma(results[i].change_qty)+'</td>';
            }
            text += '    <td align="right">'+comma(results[i].remain_qty)+'</td>';
            text += '    <td class="text-center">'+results[i].created_at+'</td>';
            text += '    <td class="text-center">'+results[i].name+'</td>';
            if(results[i].memo == ""){
              text +='      <td><a onclick="memo_modal('+results[i].id+');">';
              text +='        <button type="button" class="btn btn-light">메모</button>';
              text +='      </a></td>';
            }else{
              text +='      <td><a onclick="memo_modal('+results[i].id+');">';
              text +='        <button type="button" class="btn btn-info">메모</button>';
              text +='      </a></td>';
            }
            text += '</tr>';
        }

        $("#stock_detail_list").empty();
        $("#stock_detail_list").append(text);

        paging(data.paging.end_page, data.paging.start_page, data.paging.total_page);

    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
}

function memo_modal(id){
  $("#modal_back").fadeIn("300");
  $("#memo_modal").fadeIn("300");
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/release/log/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: id,
        type: "memo"
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
      url        : "../api/automobile/release/log/index.php",
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
