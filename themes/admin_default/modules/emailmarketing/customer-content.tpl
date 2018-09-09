<!-- BEGIN: main -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<!-- BEGIN: error -->
<div class="alert alert-warning">{ERROR}</div>
<!-- END: error -->
<div class="panel panel-default">
    <div class="panel-body">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab1" aria-controls="tab1" role="tab" data-toggle="tab">{LANG.customer_field}</a></li>
        </ul>
        <div class="tab-content" style="padding: 20px">
            <div role="tabpanel" class="tab-pane active" id="tab1">
                <form class="form-horizontal" action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}&amp;id={ROW.id}" method="post">
                    <input type="hidden" name="id" value="{ROW.id}" />
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 control-label"><strong>{LANG.fullname}</strong> <!-- BEGIN: requiredfullname1 --> <span class="red">(*)</span> <!-- END: requiredfullname1 --></label>
                        <div class="col-sm-19 col-md-21">
                            <input class="form-control" type="text" name="fullname" value="{ROW.fullname}"
                            <!-- BEGIN: requiredfullname2 -->
                            required="required" oninvalid="setCustomValidity( nv_required )" oninput="setCustomValidity('')"
                            <!-- END: requiredfullname2 -->
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 control-label"><strong>{LANG.email}</strong> <span class="red">(*)</span></label>
                        <div class="col-sm-19 col-md-21">
                            <input class="form-control" type="email" name="email" value="{ROW.email}" oninvalid="setCustomValidity( nv_email )" oninput="setCustomValidity('')" required="required" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 text-right"><strong>{LANG.gender}</strong></label>
                        <div class="col-sm-19 col-md-21">
                            <!-- BEGIN: gender -->
                            <label><input type="radio" name="gender" value="{GENDER.index}" {GENDER.checked} />{GENDER.value}</label>&nbsp;&nbsp;
                            <!-- END: gender -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 control-label"><strong>{LANG.birthday}</strong></label>
                        <div class="col-sm-19 col-md-21">
                            <div class="input-group">
                                <input class="form-control datepicker" value="{ROW.birthday}" type="text" name="birthday" readonly="readonly" /> <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <em class="fa fa-calendar fa-fix">&nbsp;</em>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 control-label"><strong>{LANG.phone}</strong></label>
                        <div class="col-sm-19 col-md-21">
                            <input type="text" name="phone" value="{ROW.phone}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 text-right"><strong>{LANG.customer_groups}</strong> <span class="red">(*)</span></label>
                        <div class="col-sm-19 col-md-21">
                            <div style="height: 200px; overflow: scroll; border: solid 1px #ddd; padding: 10px">
                                <!-- BEGIN: customer_group -->
                                <label class="show"><input type="checkbox" name="customer_group[]" value="{CUSTOMER_GROUP.id}"{CUSTOMER_GROUP.checked}>{CUSTOMER_GROUP.title}</label>
                                <!-- END: customer_group -->
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <input class="btn btn-primary loading" name="submit" type="submit" value="{LANG.save}" />
                    </div>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab2">
                <div class="well">
                    <form action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" id="frm-import" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="file_name" disabled> <span class="input-group-btn">
                                        <button class="btn btn-default" onclick="$('#upload_fileupload').click();" type="button">
                                            <em class="fa fa-folder-open-o fa-fix">&nbsp;</em>
                                        </button>
                                    </span>
                                </div>
                                <input type="file" name="upload_fileupload" id="upload_fileupload" style="display: none" />
                            </div>
                            <div class="col-xs-20">
                                <button type="submit" class="btn btn-primary btn-sm" name="read" id="btn-read" disabled="disabled">
                                    <em class="fa fa-check">&nbsp;</em>{LANG.readdata}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- BEGIN: tmp -->
                <!-- BEGIN: error -->
                <div class="alert alert-danger text-center">{IMPORT_ERROR}</div>
                <!-- END: error -->
                <div class="table-responsive m-bottom" style="max-height: 700px; overflow: scroll;">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center w50">{LANG.number}</th>
                                <th>{LANG.fullname}</th>
                                <th>{LANG.gender}</th>
                                <th>{LANG.birthday}</th>
                                <th>{LANG.phone}</th>
                                <th>{LANG.email}</th>
                                <th>{LANG.status}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- BEGIN: loop -->
                            <tr>
                                <td class="text-center">{TMP.number}</td>
                                <td>{TMP.fullname}</td>
                                <td>{TMP.gender}</td>
                                <td>{TMP.birthday}</td>
                                <td>{TMP.phone}</td>
                                <td><a href="mailto:{TMP.email}" title="{TMP.email}">{TMP.email}</a></td>
                                <td>
                                    <!-- BEGIN: vaild --> <span class="text-success"><em class="fa fa-check-circle-o">&nbsp;</em>{LANG.vaild}</span> <!-- END: vaild --> <!-- BEGIN: error --> <span class="text-danger">{TMP.error}</span> <!-- END: error -->
                                </td>
                            </tr>
                            <!-- END: loop -->
                        </tbody>
                    </table>
                </div>
                <form class="form-horizontal" action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 text-right"><strong>{LANG.addcustomer}</strong></label>
                        <div class="col-sm-19 col-md-21">
                            <div style="height: 200px; overflow: scroll; border: solid 1px #ddd; padding: 10px;">
                                <!-- BEGIN: customer_group -->
                                <label class="show"><input type="checkbox" name="customer_group[]" value="{CUSTOMER_GROUP.id}"{CUSTOMER_GROUP.checked}>{CUSTOMER_GROUP.title}</label>
                                <!-- END: customer_group -->
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN: error_skip_error -->
                    <div class="form-group">
                        <label class="col-sm-5 col-md-3 text-right"><strong>{LANG.skip}</strong></label>
                        <div class="col-sm-19 col-md-21">
                            <label><input type="checkbox" value="1" id="skip_error" />{LANG.skip_error}</label>
                        </div>
                    </div>
                    <!-- END: error_skip_error -->
                    <div class="text-center">
                        <input id="btn-import" type="submit" name="import" class="btn btn-primary"
                        <!-- BEGIN: error_btn -->
                        disabled="disabled"
                        <!-- END: error_btn -->
                        value="{LANG.save_data}" />
                    </div>
                </form>
                <!-- END: tmp -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<script>
    $(".datepicker").datepicker({
        dateFormat : "dd/mm/yy",
        changeMonth : !0,
        changeYear : !0,
        showOtherMonths : !0,
        showOn : "focus",
        yearRange : "-90:+0"
    });
    
    $('a[data-toggle="tab"]').on('click', function() {
        if ($(this).parent('li').hasClass('disabled')) {
            return false;
        }
    });
    
    $('#upload_fileupload').change(function() {
        $('#file_name').val($(this).val().match(/[-_\w]+[.][\w]+$/i)[0]);
        $('#btn-read').prop('disabled', false);
    });
    
    $('#frm-import').submit(function(e) {
        e.preventDefault();
        var $this = $('#btn-read');
        $.ajax({
            url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=customer-content&read=1&nocache=' + new Date().getTime(),
            type : 'post',
            data : new FormData($('#frm-import')[0]),
            processData : false,
            contentType : false,
            beforeSend : function() {
                $this.find('em').replaceWith('<em class="fa fa-circle-o-notch fa-spin">&nbsp;</em>');
                $this.find('em').prop('disabled', true);
            },
            complete : function() {
                $this.find('em').replaceWith('<em class="fa fa-check">&nbsp;</em>');
                $this.prop('disabled', true);
                $('#file_name').val('');
            },
            success : function(res) {
                var r_split = res.split('_');
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(r_split[1]);
                }
            }
        });
    });
    
    $('#skip_error').change(function() {
        if ($(this).is(':checked')) {
            $('#btn-import').prop('disabled', false);
        } else {
            $('#btn-import').prop('disabled', true);
        }
    });
    
    $('#btn-import').click(function() {
        var skip_error = $('skip_error').is(':checked') ? 1 : 0;
        $.ajax({
            url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=customer-content&import=1&nocache=' + new Date().getTime(),
            type : 'post',
            data : 'skip_error=' + skip_error,
            beforeSend : function() {
                $this.find('em').replaceWith('<em class="fa fa-circle-o-notch fa-spin">&nbsp;</em>');
                $this.find('em').prop('disabled', true);
            },
            success : function(res) {
                var r_split = res.split('_');
                if (r_split[0] == 'OK') {
                    window.location.href = window.location.href;
                } else {
                    alert(r_split[1]);
                }
            }
        });
    })
</script>
<!-- END: main -->