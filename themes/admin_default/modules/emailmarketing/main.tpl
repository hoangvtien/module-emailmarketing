<!-- BEGIN: main -->
<div class="well">
	<form action="{NV_BASE_ADMINURL}index.php" method="get">
		<input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}" /> <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}" /> <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}" />
		<div class="row">
			<div class="col-xs-24 col-md-4">
				<div class="form-group">
					<input class="form-control" type="text" value="{SEARCH.q}" name="q" maxlength="255" placeholder="{LANG.search_title}" />
				</div>
			</div>
			<div class="col-xs-24 col-md-4">
				<div class="form-group">
					<select name="status" class="form-control">
						<option value="">---{LANG.status_select}---</option>
						<!-- BEGIN: status -->
						<option value="{STATUS.index}" {STATUS.selected}>{STATUS.value}</option>
						<!-- END: status -->
					</select>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="form-group">
					<input class="btn btn-primary" type="submit" value="{LANG.search_submit}" />
				</div>
			</div>
		</div>
	</form>
</div>
<form action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th class="text-center w50"><input name="check_all[]" type="checkbox" value="yes" onclick="nv_checkAll(this.form, 'idcheck[]', 'check_all[]',this.checked);"></th>
					<th>{LANG.title}</th>
					<th class="w150 text-center">{LANG.totalemailsend}</th>
					<th width="170" class="text-center">{LANG.totalmailsuccess}</th>
					<th class="w150">{LANG.addtime}</th>
					<th class="w150">{LANG.begintime}</th>
					<th class="w150">{LANG.status}</th>
					<th class="w200">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<!-- BEGIN: loop -->
				<tr>
					<td class="text-center"><input type="checkbox" onclick="nv_UncheckAll(this.form, 'idcheck[]', 'check_all[]', this.checked);" value="{VIEW.id}" name="idcheck[]" class="post"></td>
					<td>{VIEW.title}</td>
					<td class="text-center">{VIEW.totalemailsend}</td>
					<td class="text-center">{VIEW.totalmailsuccess}</td>
					<td>{VIEW.addtime}</td>
					<td>{VIEW.begintime}</td>
					<td>{VIEW.sendstatusf}</td>
					<td class="text-center">
						<!-- BEGIN: update -->
						<i class="fa fa-play fa-lg">&nbsp;</i><a href="{VIEW.link_statics}">{LANG.sends}</a> -
						<i class="fa fa-edit fa-lg">&nbsp;</i> <a href="{VIEW.link_edit}">{LANG.edit}</a> - 
						<!-- END: update -->
						<!-- BEGIN: statics -->
						<em class="fa fa-bar-chart fa-lg">&nbsp;</em><a href="{VIEW.link_statics}">{LANG.statics}</a> - 
						<!-- END: statics -->
						<em class="fa fa-trash-o fa-lg">&nbsp;</em> <a href="{VIEW.link_delete}" onclick="return confirm(nv_is_del_confirm[0]);">{LANG.delete}</a>
					</td>
				</tr>
				<!-- END: loop -->
			</tbody>
		</table>
	</div>
</form>

<!-- BEGIN: generate_page -->
<div class="pull-right">{NV_GENERATE_PAGE}</div>
<!-- END: generate_page -->

<form class="form-inline m-bottom pull-left">
	<select class="form-control" id="action">
		<!-- BEGIN: action -->
		<option value="{ACTION.key}">{ACTION.value}</option>
		<!-- END: action -->
	</select>
	<button class="btn btn-primary" onclick="nv_list_action( $('#action').val(), '{BASE_URL}', '{LANG.error_empty_data}' ); return false;">{LANG.perform}</button>
</form>

<!-- END: main -->