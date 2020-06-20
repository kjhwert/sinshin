var user_id = $("#user_id").val();
var user_pw = $("#user_pw").val();

$("#login_btn").on("click", function(){
  alert(user_id);
  return;
  $.post("./api/login",
			{
				user_id: user_id,
        user_pw: user_pw
			},function(result){
			  alert(result);
		})
});
