<!-- BEGIN: main -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<!-- BEGIN: error -->
<div class="alert alert-warning">{ERROR}</div>
<!-- END: error -->
<form class="form-horizontal" action="{NV_BASE_ADMINURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
    <input type="hidden" name="id" value="{ROW.id}" />
    <div class="row">
        <div class="col-xs-24 col-sm-19">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-24 col-sm-8">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    {LANG.usergroup} <span class="pull-right"><label><input type="checkbox" class="select_all" data-mod="usergroup" />{LANG.selectall}</label></span>
                                </div>
                                <div class="panel-body select usergroup">
                                    <!-- BEGIN: usergroup -->
                                    <label class="show"><input type="checkbox" name="usergroup[]" value="{USERGROUP.id}" {USERGROUP.checked} />{USERGROUP.title}</label>
                                    <!-- END: usergroup -->
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-24 col-sm-8">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    {LANG.customergroup} <span class="pull-right"><label><input type="checkbox" class="select_all" data-mod="customergroup" />{LANG.selectall}</label></span>
                                </div>
                                <div class="panel-body select customergroup">
                                    <!-- BEGIN: customergroup -->
                                    <label class="show"><input type="checkbox" name="customergroup[]" value="{CUSGROUP.id}" {CUSGROUP.checked} />{CUSGROUP.title}</label>
                                    <!-- END: customergroup -->
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-24 col-sm-8">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    Email <em class="pull-right">({LANG.emaillist_note})</em>
                                </div>
                                <div class="panel-body select">
                                    <textarea {ROW.emaillist_disabled} class="form-control" name="emaillist" style="height: 100%">{ROW.emaillist}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-24 col-sm-12">
                                <label><strong>{LANG.sender}</strong></label> <select class="form-control" name="idsender">
                                    <!-- BEGIN: sender -->
                                    <option value="{SENDER.id}"{SENDER.selected}>{SENDER.email}
                                        <!-- BEGIN: name --> ({SENDER.name})
                                        <!-- END: name --></option>
                                    <!-- END: sender -->
                                </select>
                            </div>
                            <div class="col-xs-24 col-sm-12">
                                <label><strong>{LANG.replyto}</strong></label> <select class="form-control" name="idreplyto">
                                    <option value="0">---{LANG.replyto_select}---</option>
                                    <!-- BEGIN: replyto -->
                                    <option value="{REPLYTO.id}"{REPLYTO.selected}>{REPLYTO.email}
                                        <!-- BEGIN: name --> ({REPLYTO.name})
                                        <!-- END: name --></option>
                                    <!-- END: replyto -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><strong>{LANG.title}</strong> <em class="fa fa-question-circle fa-pointer text-info" data-toggle="tooltip" data-original-title="{LANG.title_note}">&nbsp;</em></label> <input type="text" class="form-control" name="title" value="{ROW.title}" placeholder="{LANG.title}">
                    </div>
                    <div class="form-group">
                        <label><strong>{LANG.content}</strong></label> {ROW.content}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-24 col-sm-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {LANG.begintime}&nbsp;&nbsp;<span data-toggle="tooltip" data-placement="top" title="" data-original-title="{LANG.typetime_note}"><em class="fa fa-info-circle fa-pointer text-info">&nbsp;</em></span>
                </div>
                <div class="panel-body">
                    <!-- BEGIN: typetime -->
                    <label><input class="typetime" type="radio" name="typetime" value="{TYPETIME.index}" {TYPETIME.checked} />{TYPETIME.value}</label>&nbsp;
                    <!-- END: typetime -->
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">{LANG.option}</div>
                <div class="panel-body">
                    <label class="show"><input type="checkbox" name="linkstatics" value="1" {ROW.ck_linkstatics} />{LANG.linkstatics}</label> <label class="show"><input type="checkbox" name="openstatics" value="1" {ROW.ck_openstatics} />{LANG.openstatics}</label>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group text-center">
        <input class="btn btn-warning loading" name="draft" type="submit" value="{LANG.adddraft}" /> <input class="btn btn-primary loading" name="submit" type="submit" value="{LANG.campaign_add}" />
    </div>
</form>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
    //<![CDATA[
    $("#begintime").datepicker({
        dateFormat : "dd/mm/yy",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        showOn : "focus",
        yearRange : "-90:+0",
    });
    
    $('input.typetime').change(function() {
        if ($(this).val() == 0) {
            $('#div-begintime').hide();
        } else if ($(this).val() == 1) {
            $('#div-begintime').show();
        }
    });

    //]]>
</script>
<!-- END: main -->