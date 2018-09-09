<!-- BEGIN: main -->
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2-bootstrap.min.css">
<form action="" method="post" class="form-horizontal">
    <div class="panel panel-default">
        <div class="panel-heading">{LANG.config_general}</div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-4 text-right"><strong>{LANG.config_allow_declined}</strong></label>
                <div class="col-sm-20">
                    <label><input type="checkbox" name="allow_declined" value="1" {DATA.ck_allow_declined} />{LANG.config_allow_declined_note}</label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 text-right"><strong>{LANG.config_stoperror}</strong></label>
                <div class="col-sm-20">
                    <label><input type="checkbox" name="stoperror" value="1" {DATA.ck_stoperror} />{LANG.config_stoperror_note}</label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label"><strong>{LANG.config_numsend}</strong></label>
                <div class="col-sm-20">
                    <input type="number" name="numsend" class="form-control" value="{DATA.numsend}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label"><strong>{LANG.config_new_customer_group}</strong></label>
                <div class="col-sm-20" style="border: 1px solid #ddd; padding: 10px; height: 200px; overflow: scroll;">
                    <!-- BEGIN: group -->
                    <label class="show"><input type="checkbox" name="new_customer_group[]" value="{GROUP.id}" {GROUP.checked} />{GROUP.title}</label>
                    <!-- END: group -->
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">{LANG.customer}</div>
        <div class="panel-body">
            <!-- BEGIN: customer -->
            <div class="form-group">
                <label class="col-sm-4 text-right"><strong>{LANG.config_requiredfullname}</strong></label>
                <div class="col-sm-20">
                    <!-- BEGIN: customer_data -->
                    <label><input type="radio" name="customer_data" value="{CUSTOMER_DATA.index}" {CUSTOMER_DATA.checked} />{CUSTOMER_DATA.value}</label>
                    <!-- END: customer_data -->
                </div>
            </div>
            <!-- END: customer -->
            <div id="customer_data_1" {DATA.ds_customer_data_1}>
                <div class="form-group">
                    <label class="col-sm-4 text-right"><strong>{LANG.config_requiredfullname}</strong></label>
                    <div class="col-sm-20">
                        <label><input type="checkbox" name="requiredfullname" value="1" {DATA.ck_requiredfullname} />{LANG.config_requiredfullname_note}</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 text-right"><strong>{LANG.config_show_undefine}</strong></label>
                    <div class="col-sm-20">
                        <label><input type="checkbox" name="show_undefine" value="1" {DATA.ck_show_undefine} />{LANG.config_show_undefine_note}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <input type="submit" class="btn btn-primary" value="{LANG.save}" name="savesetting" />
    </div>
</form>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<script>
    $('.select2').select2({
        theme : 'bootstrap',
        tags : true
    });
    
    $('input[name="customer_data"]').change(function() {
        if ($(this).val() == 1) {
            $('#customer_data_1').show();
        } else {
            $('#customer_data_1').hide();
        }
    });
</script>
<!-- BEGIN: main -->