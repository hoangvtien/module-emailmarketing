/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Sat, 15 Oct 2016 03:30:10 GMT
 */

$(document).ready(function(){
	
	$('.loading').click(function() {
		if($.validator){
			var valid = $(this).closest('form').valid();
			if(valid){
				$('body').append('<div class="ajax-load-qa"></div>');
			}
		}else{
			var valid = $(this).closest('form').find('input:invalid').length;
			if(valid == 0){
				$('body').append('<div class="ajax-load-qa"></div>');
			}
		}
	});
	
	$(".select_all").change(function(){ 
	    var status = this.checked;
	    var mod = $(this).data('mod');
	    $('.' + mod + ' input[type="checkbox"]').each(function(){
	        this.checked = status;
	    });
	});
	
	$.fn.addNumber = function() {
		$(this).each(function(index){
	    	$(this).text(index + 1);
	    });
	    return !1;
	};
	
});

function nv_list_action( action, url_action, del_confirm_no_post )
{
	var listall = [];
	$('input.post:checked').each(function() {
		listall.push($(this).val());
	});
	if (listall.length < 1) {
		alert( del_confirm_no_post );
		return false;
	}
	if( action == 'delete_list_id' )
	{
		if (confirm(nv_is_del_confirm[0])) {
			$.ajax({
				type : 'POST',
				url : url_action,
				data : 'delete_list=1&listall=' + listall,
				success : function(data) {
					var r_split = data.split('_');
					if( r_split[0] == 'OK' ){
						window.location.href = window.location.href;
					}
					else{
						alert( nv_is_del_confirm[2] );
					}
				}
			});
		}
	}else{
		if (confirm(nv_is_change_act_confirm[0])) {
			$.ajax({
				type : 'POST',
				url : url_action,
				data : 'customerlist=1&action=' + action + '&listall=' + listall,
				success : function(data) {
					var r_split = data.split('_');
					if( r_split[0] == 'OK' ){
						window.location.href = window.location.href;
					}
					else{
						alert( nv_is_change_act_confirm[2] );
					}
				}
			});
		}
	}
	
	return false;
}