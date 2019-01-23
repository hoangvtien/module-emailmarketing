<!-- BEGIN: main -->
<div class="panel panel-default">
    <div class="panel-heading">
        {VIEW.fullname}
        <ul class="pull-right list-inline">
            <li><a class="btn btn-primary btn-xs" href="{VIEW.link_add}"><em class="fa fa-plus">&nbsp;</em>{LANG.add}</a></li>
            <li><a class="btn btn-primary btn-xs" href="{VIEW.link_edit}"><em class="fa fa-trash-o">&nbsp;</em>{LANG.edit}</a></li>
            <li><a class="btn btn-danger btn-xs" href="{VIEW.link_delete}"><em class="fa fa-trash-o">&nbsp;</em>{LANG.delete}</a></li>
        </ul>
    </div>
    <table class="table table-striped table-bordered table-hover">
        <tbody>
            <tr>
                <th width="200">{LANG.fullname}</th>
                <td>{VIEW.fullname}</td>
                <th>{LANG.gender}</th>
                <td>{VIEW.gender}</td>
            </tr>
            <tr>
                <th width="200">{LANG.birthday}</th>
                <td>{VIEW.birthday}</td>
                <th>{LANG.phone}</th>
                <td>{VIEW.phone}</td>
            </tr>
            <tr>
                <th width="200">{LANG.email}</th>
                <td>{VIEW.email}</td>
                <th>{LANG.customer_groups}</th>
                <td>{VIEW.customer_groups}</td>
            </tr>
            <tr>
                <th width="200">{LANG.addtime}</th>
                <td>{VIEW.addtime}</td>
                <th>{LANG.status}</th>
                <td class="text-center">
                    <input type="checkbox" name="status" id="change_status_{VIEW.id}" value="{VIEW.id}" {CHECK} onclick="nv_change_status({VIEW.id});" />
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script>
    function nv_change_status(id) {
        var new_status = $('#change_status_' + id).is(':checked') ? true : false;
        if (confirm(nv_is_change_act_confirm[0])) {
            var nv_timer = nv_settimeout_disable('change_status_' + id, 5000);
            $.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=customer&nocache=' + new Date().getTime(), 'change_status=1&id=' + id, function(res) {
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
</script>
<!-- END: main -->
