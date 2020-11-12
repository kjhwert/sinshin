$(document).ready(function(){
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
    stock_list(page_no, per_page);
});

var page_no = "";
var per_page = 15;
if(getParam("page_no") == ""){
    page_no = 1;
}else{
    page_no = getParam("page_no");
}

function stock_list (page_no, per_page) {
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
            perPage: per_page
        }
    }).done(function (data, textStatus, xhr) {
        if (data.status != 200) {
            alert(result.message);
            return;
        }

        var results = data.data;
        var text = '';

        for (i in results) {
            text += '<tr>'
            text += '    <td class="text-center">'+results[i].RNUM+'</td>'
            text += '    <td>'+results[i].customer_code+'</td>'
            text += '    <td>'+results[i].product_name+'</td>'
            text += '    <td class="text-center">'+results[i].car_code+'</td>'
            text += '    <td class="text-center">'+results[i].customer+'</td>'
            text += '    <td class="text-center">'+results[i].supplier+'</td>'
            text += '    <td align="right">'+comma(results[i].remain_qty)+'</td>'
            text += '    <td class="text-center">'+results[i].name+'</td>'
            text += '    <td>'
            text += '       <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">'
            text += '           <a href="../automotive_management/plating_stock_detail.html?id='+results[i].id+'">'
            text += '               <button type="button" class="btn btn-warning">상세보기</button>'
            text += '           </a>&nbsp;'
            text += '           <a href="../automotive_management/plating_release_create.html?id='+results[i].id+'">'
            text += '               <button type="button" class="btn btn-info">출고</button>'
            text += '           </a>'
            text += '       </div>'
            text += '    </td>'
            text += '</tr>'
        }

        $("#stock_list").empty();
        $("#stock_list").append(text);

        paging(data.paging.end_page, data.paging.start_page, data.paging.total_page);

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
        text +='<a class="page-link" href="./plating_stock.html?page_no='+pre_no+'" aria-label="Previous">';
        text +=' <span aria-hidden="true">Prev</span>';
        text +=' <span class="sr-only">Previous</span>';
        text +='</a>';
        text +='</li>';
    }
    for( var k = paging_init_num; k <= paging_end_num; k++){
        if (parseInt(page_no) == k)
        {
            text +='<li class="page-item active"><a class="page-link" href="./plating_stock.html?page_no='+k+'">'+k+'</a></li>';
        }else{
            text +='<li class="page-item"><a class="page-link" href="./plating_stock.html?page_no='+k+'">'+k+'</a></li>';
        }
    }
    if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
    {
    }else{
        text +='<li class="page-item">';
        text +='  <a class="page-link" href="./plating_stock.html?page_no='+next_no+'" aria-label="Next">';
        text +='    <span aria-hidden="true">Next</span>';
        text +='    <span class="sr-only">Next</span>';
        text +='  </a>';
        text +='</li>';
    }

    $("#pagination").empty();
    $("#pagination").append(text);
}
