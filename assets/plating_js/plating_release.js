var page_no = "";
var per_page = 15;
if(getParam("page_no") == ""){
    page_no = 1;
}else{
    page_no = getParam("page_no");
}

$(document).ready(function(){
    release_list(page_no, per_page);
    $("#automotive_management").addClass("open");
    $("#plating").addClass("active");
});

function release_list (page_no, per_page) {
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
            type: "release_list"
        }
    }).done(function (data, textStatus, xhr) {
        if (data.status != 200) {
            alert(result.message);
            return;
        }

        var results = data.data;
        var text = '';

        console.log(results);

        for (i in results) {
            text += '<tr>';
            text += '    <th>'+results[i].RNUM+'</th>';
            text += '    <td>'+results[i].customer_code+'</td>';
            text += '    <td>'+results[i].product_name+'</td>';
            text += '    <td>'+results[i].car_code+'</td>';
            text += '    <td>'+results[i].customer+'</td>';
            text += '    <td>'+results[i].supplier+'</td>';
            text += '    <td align="right">'+comma(results[i].release_qty)+'</td>';
            text += '    <td>'+results[i].created_at+'</td>';
            text += '    <td>'+results[i].name+'</td>';
            text += '</tr>';
        }

        $("#release_list").empty();
        $("#release_list").append(text);

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
        text +='<a class="page-link" href="./plating_release.html?page_no='+pre_no+'" aria-label="Previous">';
        text +=' <span aria-hidden="true">Prev</span>';
        text +=' <span class="sr-only">Previous</span>';
        text +='</a>';
        text +='</li>';
    }
    for( var k = paging_init_num; k <= paging_end_num; k++){
        if (parseInt(page_no) == k)
        {
            text +='<li class="page-item active"><a class="page-link" href="./plating_release.html?page_no='+k+'">'+k+'</a></li>';
        }else{
            text +='<li class="page-item"><a class="page-link" href="./plating_release.html?page_no='+k+'">'+k+'</a></li>';
        }
    }
    if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
    {
    }else{
        text +='<li class="page-item">';
        text +='  <a class="page-link" href="./plating_release.html?page_no='+next_no+'" aria-label="Next">';
        text +='    <span aria-hidden="true">Next</span>';
        text +='    <span class="sr-only">Next</span>';
        text +='  </a>';
        text +='</li>';
    }

    $("#pagination").empty();
    $("#pagination").append(text);
}
