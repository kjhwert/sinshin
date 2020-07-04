

user_list();
function user_list(){
  $.ajax({
      type    : "GET",
      url        : "../api/user/index.php?page="+1+"&perPage="+10,
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json"
  }).done(function (data, textStatus, xhr) {
    var text = '';
    if(data.status == 200){
      for(var i in data.data){
        text +='<tr>';
        text +='<th scope="row">3</th>';
        text +='<td>'+data.data[i].user_id+'</td>';
        text +='<td>'+data.data[i].email+'</td>';
        text +='<td>'+data.data[i].name+'</td>';
        text +='<td>'+data.data[i].dept+'</td>';
        text +='  <td>'+data.data[i].position+'</td>';
        text +='  <td>'+data.data[i].duty+'</td>';
        text +='  <td>'+data.data[i].tel+'</td>';
        text +='  <td>'+data.data[i].created_at+'</td>';
        text +='  <td></td>';
        text +='  <td>';
        text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text +='      <a href="../system_management/user_update.html?user_no='+data.data[i].id+'">';
        text +='        <button type="button" class="btn btn-warning">수정</button></a>';
        text +='    </div>';
        text +='  </td>';
        text +='</tr>';
      }
      $("#user_list_tbody").empty();
      $("#user_list_tbody").append(text);
    }else{
      alert(data.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
