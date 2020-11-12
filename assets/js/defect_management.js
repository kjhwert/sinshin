var group_id = getParam("defect_group");

$(function () {
  $("#system_management").addClass("open");
  $("#data_management").addClass("active");
  if($("#system_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#data_management").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  get_defect_list();

    $('#add_btn').on("click",function () {

        var name = $("#defect_name").val();
        var name_en = $("#defect_name_en").val();

        $.ajax({
            type    : "POST",
            url        : "../api/defect/index.php",
            headers : {
                "content-type": "application/json",
                Authorization : user_data.token,
            },
            dataType:"json",
            data : JSON.stringify({
                group_id : group_id,
                name : name,
                name_en : name_en
            })
        }).done(function (result, textStatus, xhr) {
            if(result.status == 200) {
                alert(result.message);
                return location.reload();
            }

            return alert(result.message);

        }).fail(function(response, textStatus, errorThrown){
            console.log("전송 실패");
        });

    })

})

function get_defect_list() {

    $.ajax({
        type    : "GET",
        url        : "../api/defect/index.php?group_id="+group_id,
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
    }).done(function (result, textStatus, xhr) {
        var defects = result.data;
        var text = "";

        $("#group_name").val(defects[0].group_name);

        for(var i = 0; i < defects.length; i++) {
            text += '<span class="tag btn-info" onclick="remove_list('+defects[i].id+');">'
            if (defects[i].name_en) {
                text += defects[i].name+" (" + defects[i].name_en + ")"
            } else {
                text += defects[i].name
            }
            text += '<a role="button" class="tag-i">×</a>'
            text += '</span>'
        }

        $("#defect_list").empty();
        $("#defect_list").append(text);

    }).fail(function(response, textStatus, errorThrown){
        console.log("전송 실패");
    });
}

function remove_list(id) {

    var result = confirm('정말 삭제하시겠습니까?');

    if(!result) {
        return;
    }

    $.ajax({
        type    : "DELETE",
        url        : "../api/defect/index.php",
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
        data : JSON.stringify({
            id : id
        })
    }).done(function (result, textStatus, xhr) {
        if(result.status == 200) {
            alert(result.message);
            return location.reload();
        }

        return alert(result.message);

    }).fail(function(response, textStatus, errorThrown){
        console.log("전송 실패");
    });
}
