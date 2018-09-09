<!-- BEGIN: main -->
<form action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th class="w100">{LANG.weight}</th>
					<th>{LANG.smtp_host}</th>
					<th class="w150">{LANG.smtp_port}</th>
					<th class="w150">{LANG.smtp_encrypted}</th>
					<th class="w250">{LANG.smtp_username}</th>
					<th class="w150 text-center">{LANG.sendlimit}</th>
					<th class="w100 text-center">{LANG.active}</th>
					<th class="w150">&nbsp;</th>
				</tr>
			</thead>
			<!-- BEGIN: generate_page -->
			<tfoot>
				<tr>
					<td class="text-center" colspan="8">{NV_GENERATE_PAGE}</td>
				</tr>
			</tfoot>
			<!-- END: generate_page -->
			<tbody>
				<!-- BEGIN: loop -->
				<tr>
					<td><select class="form-control" id="id_weight_{VIEW.id}" onchange="nv_change_weight('{VIEW.id}');">
							<!-- BEGIN: weight_loop -->
							<option value="{WEIGHT.key}"{WEIGHT.selected}>{WEIGHT.title}</option>
							<!-- END: weight_loop -->
					</select></td>
					<td>{VIEW.smtp_host}</td>
					<td>{VIEW.smtp_port}</td>
					<td>{VIEW.smtp_encrypted}</td>
					<td>{VIEW.smtp_username}</td>
					<td class="text-center">{VIEW.sendlimit}</td>
					<td class="text-center"><input type="checkbox" name="status" id="change_status_{VIEW.id}" value="{VIEW.id}" {CHECK} onclick="nv_change_status({VIEW.id});" /></td>
					<td class="text-center"><i class="fa fa-edit fa-lg">&nbsp;</i> <a href="{VIEW.link_edit}#edit">{LANG.edit}</a> - <em class="fa fa-trash-o fa-lg">&nbsp;</em> <a href="{VIEW.link_delete}" onclick="return confirm(nv_is_del_confirm[0]);">{LANG.delete}</a></td>
				</tr>
				<!-- END: loop -->
			</tbody>
		</table>
	</div>
</form>

<!-- BEGIN: error -->
<div class="alert alert-warning">{ERROR}</div>
<!-- END: error -->

<form class="form-horizontal" action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">{LANG.mailsend}</div>
		<div class="panel-body">
			<input type="hidden" name="id" value="{ROW.id}" />
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.smtp_host}</strong> <span class="red">(*)</span></label>
				<div class="col-sm-19 col-md-21">
					<input class="form-control" type="text" name="smtp_host" value="{ROW.smtp_host}" oninvalid="setCustomValidity( nv_email )" oninput="setCustomValidity('')" required="required" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.smtp_port}</strong> <span class="red">(*)</span></label>
				<div class="col-sm-19 col-md-21">
					<input class="form-control" type="text" name="smtp_port" value="{ROW.smtp_port}" oninvalid="setCustomValidity( nv_email )" oninput="setCustomValidity('')" required="required" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.smtp_encrypted}</strong></label>
				<div class="col-sm-19 col-md-21">
					<select name="smtp_encrypted" class="form-control">
						<!-- BEGIN: smtp_encrypted -->
						<option value="{SMTP_EMCRYPTED.index}"{SMTP_EMCRYPTED.selected}>{SMTP_EMCRYPTED.value}</option>
						<!-- END: smtp_encrypted -->
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.sendlimit}</strong></label>
				<div class="col-sm-19 col-md-21">
					<input class="form-control" type="number" name="sendlimit" value="{ROW.sendlimit}" />
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">{LANG.accountsend}</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.smtp_username}</strong></label>
				<div class="col-sm-19 col-md-21">
					<input class="form-control" type="text" name="smtp_username" value="{ROW.smtp_username}" autocomplete="off" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-5 col-md-3 control-label"><strong>{LANG.smtp_password}</strong></label>
				<div class="col-sm-19 col-md-21">
					<input class="form-control" type="password" name="smtp_password" value="{ROW.smtp_password}" autocomplete="off" />
				</div>
			</div>
		</div>
	</div>
	<div class="form-group text-center">
		<input class="btn btn-primary loading" name="submit" type="submit" value="{LANG.save}" />
	</div>
</form>

<script type="text/javascript">
//<![CDATA[
	function nv_change_weight(id) {
		var nv_timer = nv_settimeout_disable('id_weight_' + id, 5000);
		var new_vid = $('#id_weight_' + id).val();
		$.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=mailserver&nocache=' + new Date().getTime(), 'ajax_action=1&id=' + id + '&new_vid=' + new_vid, function(res) {
			var r_split = res.split('_');
			if (r_split[0] != 'OK') {
				alert(nv_is_change_act_confirm[2]);
			}
			window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=mailserver';
			return;
		});
		return;
	}


	function nv_change_status(id) {
		var new_status = $('#change_status_' + id).is(':checked') ? true : false;
		if (confirm(nv_is_change_act_confirm[0])) {
			var nv_timer = nv_settimeout_disable('change_status_' + id, 5000);
			$.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=mailserver&nocache=' + new Date().getTime(), 'change_status=1&id='+id, function(res) {
				var r_split = res.split('_');
				if (r_split[0] != 'OK') {
					alert(nv_is_change_act_confirm[2]);
				}
			});
		}
		else{
			$('#change_status_' + id).prop('checked', new_status ? false : true );
		}
		return;
	}
//]]>
</script>
<!-- END: main -->