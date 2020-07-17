get_defect_group();

function get_defect_group() {
    $.ajax({
        type    : "GET",
        url        : "../api/defect/group/index.php",
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
    }).done(function (result, textStatus, xhr) {
        var defectGroup = result.data;
        var text = "";

        for (var i = 0; i < defectGroup.length; i++) {
        text += '<tr>'
        text +=   '<td>'+defectGroup[i].RNUM+'</td>'
        text +=   '<td>'+defectGroup[i].name+'</td>'
        text +=   '<td>'
        text +=     '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">'
        text +=       '<a href="../system_management/defect_management.html?defect_group='+defectGroup[i].id+'">'
        text +=        '<button type="button" class="btn btn-warning">유형관리</button>'
        text +=       '</a>'
        text +=      '</div>'
        text +=   '</td>'
        text +=  '</tr>'
        }

        $("#defect_group").empty();
        $("#defect_group").append(text);

    }).fail(function(response, textStatus, errorThrown){
        console.log("전송 실패");
    });
}
