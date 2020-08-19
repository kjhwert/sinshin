$(function(){
  $("#product_history").addClass("open");
  $("#injection").addClass("active");

  injection_status();
});


function injection_status(page_no, per_page, sort, order){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/start/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{

      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text +='<tr>';
        text +='  <th>'+jsonResult[i].RNUM+'</th>';
        text +='  <td>4502496197</td>';
        text +='  <td>R-IN-300</td>';
        text +='  <td>LG1392</td>';
        text +='  <td>후천기단화현아이/크림캡 25ML 명판</td>';
        text +='  <td>10-21</td>';
        text +='  <td>10-25</td>';
        text +='  <td>1,100</td>';
        text +='  <td>1,000</td>';
        text +='  <td>500</td>';
        text +='  <td>';
        text +='    <div class="progress progress-sm mb-0 box-shadow-2">';
        text +='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: 50%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
        text +='    </div>50%';
        text +='  </td>';
        text +='</tr>';
      }
      $("#injection_status_list").empty();
      $("#injection_status_list").append(text);

    //  paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}


// function paging(end, start, total){
//   var paging_init_num = parseInt(start);
//   var paging_end_num = parseInt(end);
//   var total_paging_cnt = parseInt(total);
//   var pre_no = parseInt(page_no) - 1;
//   var next_no = parseInt(page_no) + 1;
//   var text = '';
//   if (total_paging_cnt == 0 || total_paging_cnt == 1 || pre_no == 0)
//   {
//   }else{
//     text +='<li class="page-item">';
//     text +='<a class="page-link" href="./user_list.html?page_no='+pre_no+'" aria-label="Previous">';
//     text +=' <span aria-hidden="true">Prev</span>';
//     text +=' <span class="sr-only">Previous</span>';
//     text +='</a>';
//     text +='</li>';
//   }
//   for( var k = paging_init_num; k <= paging_end_num; k++){
//     if (parseInt(page_no) == k)
//     {
//       text +='<li class="page-item active"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
//     }else{
//       text +='<li class="page-item"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
//     }
//   }
//   if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
//   {
//   }else{
//     text +='<li class="page-item">';
//     text +='  <a class="page-link" href="./user_list.html?page_no='+next_no+'" aria-label="Next">';
//     text +='    <span aria-hidden="true">Next</span>';
//     text +='    <span class="sr-only">Next</span>';
//     text +='  </a>';
//     text +='</li>';
//   }
//
//   $("#pagination").empty();
//   $("#pagination").append(text);
// }
//
// $("#search_btn").on("click", function(){
//   location.href="../automotive_management/plating_status.html?start_date="+$("#start_date").val()+"&end_date="+$("#end_date").val()+"&search_text="+$("#search_text").val();
// });
