var remain = 0;

$(document).ready(function(){
    $("#automotive_management").addClass("open");
    $("#plating").addClass("active");
    get_product();
    getReleasable();
    if($("#automotive_management").css("display") == "none"){
      alert("페이지 접근 권한이 없습니다");
      history.back();
    }
    if($("#plating").find("a").css("display") == "none"){
      alert("페이지 접근 권한이 없습니다");
      history.back();
    }

    $("#release_btn").on("click", function () {
        var release_qty = parseInt($("#release_qty").val());

        if (release_qty > remain) {
            alert('출고가능량을 넘을 수 없습니다.');
            return;
        }

        var msg = confirm('출고 하시겠습니까?');
        if(msg) { //yes location.replace('index.php'); } else { //no }
          $.ajax({
              type    : "POST",
              url        : "../api/automobile/release/log/index.php",
              headers : {
                  "content-type": "application/json",
                  Authorization : user_data.token,
              },
              dataType:"json",
              data: JSON.stringify({
                  release_qty : release_qty,
                  product_id : parseInt(getParam("id"))
              })
          }).done(function (data, textStatus, xhr) {
              if (data.status != 200) {
                  alert(data.message);
                  return;
              }

              alert(data.message);
              location.href="plating_stock.html";

          }).fail(function(data, textStatus, errorThrown){
              console.log("전송 실패");
          });
        }


    })
});

function get_product() {
    $.ajax({
        type    : "GET",
        url        : "../api/automobile/master/index.php",
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
        data:{
            id : getParam("id")
        }
    }).done(function (data, textStatus, xhr) {
        if (data.status != 200) {
            alert(data.message);
            return;
        }

        var result = data.data[0];
        $("#product_name").val(result.name);
        $("#customer").val(result.customer);
        $("#supplier").val(result.supplier);
        $("#customer_code").val(result.customer_code);
        $("#car_code").val(result.brand_code + "/" + result.car_code);

    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
}

function getReleasable() {
    $.ajax({
        type    : "GET",
        url        : "../api/automobile/release/log/index.php",
        headers : {
            "content-type": "application/json",
            Authorization : user_data.token,
        },
        dataType:"json",
        data:{
            id : getParam("id"),
            type : "releasable"
        }
    }).done(function (data, textStatus, xhr) {
        if (data.status != 200) {
            alert(data.message);
            return;
        }

        $("#remain_qty").val(comma(data.data.remain_qty));
        remain = data.data.remain_qty;

    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
}
