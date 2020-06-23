function getHeader(){
	var url;
		url = '../include/header.html'
		$.ajax({
			url:url,
			type:'GET',
			async:false
		}).done(function(result){
			document.write(result);
		});
}

function cancel(){
	history.back();
}
