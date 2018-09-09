<!-- BEGIN: main -->
<div class="sendding">
    <ul class="control list-inline pull-right">
        <li><a href="{BUTTON.send_test}" class="btn btn-default btn-xs" onclick="send_test_email(); return !1;"><em class="fa fa-send">&nbsp;</em>{LANG.send_test_email}</a></li>
        <li><a href="{BUTTON.edit}" class="btn btn-default btn-xs <!-- BEGIN: sendstatus_disabled -->disabled<!-- END: sendstatus_disabled -->"><em class="fa fa-edit">&nbsp;</em>{LANG.edit}</a></li>
        <li><a href="{BUTTON.delete}" class="btn btn-danger btn-xs" onclick="return confirm(nv_is_del_confirm[0]);"><em class="fa fa-trash-o">&nbsp;</em>{LANG.delete}</a></li>
    </ul>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-xs-24 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">{LANG.info}</div>
                <div class="panel-body">
                    <!-- BEGIN: btn_control -->
                    <p class="text-center" id="btn-control">
                        <em title="{LANG.start}" class="fa fa-play-circle fa-5x fa-pointer" onclick="nv_sendmail({ROWSID}, {NEXTCUSTOMERID}); return false;">&nbsp;</em>
                    </p>
                    <!-- END: btn_control -->
                    <div class="progress">
                        <div class="progress-bar" id="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: {PERCENT">
                            <span id="totalsend">{TOTALSENDER}</span>/{TOTAL} <span class="text-lowercase">{LANG.sendstatus_5}</span>
                        </div>
                    </div>
                    <div class="row send-count">
                        <div class="col-xs-24 col-sm-12 text-center">
                            <label class="text-uppercase"><strong>{LANG.sendstatus_1}</strong></label> <span class="show" id="countsuccess">{COUNTSUCCESS}</span>
                        </div>
                        <div class="col-xs-24 col-sm-12 text-center">
                            <label class="text-uppercase"><strong>{LANG.sendstatus_3}</strong></label> <span class="show" id="counterror">{COUNTERROR}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- BEGIN: openstatics -->
            <div class="panel panel-default">
                <div class="panel-heading">{LANG.openstatics}</div>
                <div class="panel-body">
                    <script src="{NV_BASE_SITEURL}themes/{TEMPLATE}/js/emailmarketing_hightcharts.js"></script>
                    <div id="openstatics" style="height: 300px; margin: 0 auto"></div>
                    <script>
					var chart;

					function requestData() {
					    $.ajax({
					        url: script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=send&openstatics=1&nocache=' + new Date().getTime(),
					       	data: 'id=' + {ROWSID},
					        dataType: 'json',
					        success: function(point) {
					        	chart.series[0].setData(point);
					            
					            // call it again after 3 second
					            setTimeout(requestData, 3000);    
					        },
					        cache: false
					    });
					}
					
					$(document).ready(function() {
						chart = new Highcharts.chart('openstatics', {
					        chart: {
					            plotBackgroundColor: null,
					            plotBorderWidth: null,
					            plotShadow: false,
					            type: 'pie',
					            events: {
					                load: requestData,
					            }
					        },
					        title: {
					            text: ''
					        },
					        tooltip: {
					            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
					        },
					        credits: {
					        	enabled: false
					        },
					        plotOptions: {
					            pie: {
					            	cursor: 'pointer',
					                dataLabels: {
					                    enabled: true,
					                    format: '{point.y:.1f}% - {point.x:.-1f} {LANG.turn}',
					                    style: {
					                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
					                    }
					                },
					                showInLegend: true,
					            }
					        },
					        series: [{
					            data: [],
					            point: {
					            	events: {
					            		click: function () {
					            			nv_filer_emaillist(this.openedlist);
					            			$('#title-emaillist').text(this.title);
				            			}
					            	}
					            }
					        }]
					    });
					});
					</script>
                </div>
            </div>
            <!-- END: openstatics -->
            <!-- BEGIN: linkstatics -->
            <div class="panel panel-default">
                <div class="panel-heading">{LANG.linkstatics}</div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">{LANG.number}</th>
                            <th>{LANG.link}</th>
                            <th class="w150 text-center">{LANG.countclick}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- BEGIN: loop -->
                        <tr>
                            <td class="text-center">{LINK.number}</td>
                            <td><a href="{LINK.link}" title="{LINK.text}">{LINK.text}</a></td>
                            <td class="text-center"><span id="countclick_{LINK.index}" class="badge pointer countclick" data-listclick="" data-text="{LINK.text}"></span></td>
                        </tr>
                        <!-- END: loop -->
                    </tbody>
                </table>
            </div>
            <script>
				getlinkstatics();
				function getlinkstatics()
				{
				    $.ajax({
				        url: script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=send&linkstatics=1&nocache=' + new Date().getTime(),
		        		data: 'id=' + {ROWSID},
				        dataType: 'json',
				        success: function(json) {
				        	$.each(json, function(index, value){
				        		$('#countclick_' + index).attr('data-listclick', value.listclick).html(value.countclick);
				        	});
				            setTimeout(getlinkstatics, 3000);    
				        },
				        cache: false
				    });
				}
				
				$('.countclick').click(function(){
					var listclick = $(this).data('listclick');
					listclick = [listclick];
					nv_filer_emaillist(listclick);
					$('#title-emaillist').text('{LANG.emaillist_clicklist}' + $(this).data('text'));
				});
			</script>
            <!-- END: linkstatics -->
        </div>
        <div class="col-xs-24 col-sm-16">
            <div class="panel panel-default">
                <div class="panel-heading" id="title-emaillist">{LANG.emaillist}</div>
                <div class="table-responsive" style="max-height: 700px; overflow: scroll;">
                    <table class="table table-striped table-bordered table-hover" id="table-emaillist">
                        <thead>
                            <tr>
                                <th width="70" class="text-center">{LANG.number}</th>
                                <th>Email</th>
                                <th class="w200">{LANG.sendstatus}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- BEGIN: loop -->
                            <tr id="row_{DATA.id}" data-customerid="{DATA.id}">
                                <td class="text-center number">{DATA.number}</td>
                                <td><a href="mailto:{DATA.email}" title="Mail to: {DATA.email}">{DATA.email}</a></td>
                                <td id="sendstatus_{DATA.id}">
                                    <!-- BEGIN: sendsuccess --> <em class="fa fa-check-circle fa-lg text-success">&nbsp;</em> <!-- END: sendsuccess --> <!-- BEGIN: senderror --> <em class="fa fa-exclamation-circle fa-lg text-danger">&nbsp;</em> <!-- END: senderror --> {DATA.sendstatus_str}
                                </td>
                            </tr>
                            <!-- END: loop -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
	var exit = 0;
	var currentcustomerid = {FIRSTCUSTOMERID};

	function nv_sendmail(id, customerid)
	{
		exit = 0;
		$('#btn-control').html('<em title="{LANG.stop}" class="fa fa-stop-circle fa-5x fa-pointer" onclick="nv_send_exit(); return false;">&nbsp;</em>');
		nv_sendmail_action(id, customerid);
	}
	
	function nv_sendmail_action(id, customerid) {
		if(exit){
			nv_send_exit();
			return !1;
		}
		
		if(customerid == undefined){
			customerid = currentcustomerid;
		}
		
		$.ajax({
			url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=send&nocache=' + new Date().getTime(),
			type : 'post',
			data : 'send=1&id=' + id,
			dataType : 'json',
			beforeSend : function() {
				$('#sendstatus_' + customerid).html('<em class="fa fa-circle-o-notch fa-spin fa-lg text-info">&nbsp;</em> {LANG.sendstatus_4}...');
			},
			success : function(json) {
				currentcustomerid = json.nextcustomerid;
				if (json.status == 'success' || json.status == 'error') {
					if(json.status == 'success'){
						$('#sendstatus_' + customerid).html('<em class="fa fa-check-circle fa-lg text-success">&nbsp;</em> {LANG.sendstatus_success}');
					}else{
						$('#sendstatus_' + customerid).html('<em class="fa fa-exclamation-circle fa-lg text-danger">&nbsp;</em> {LANG.sendstatus_error} ' + json.messenger);
						if(json.exit == 1){
							exit = 1;
						}
					}
					$('#totalsend').text(json.totalsend);
					$('#countsuccess').text(json.countsuccess);
					$('#counterror').text(json.counterror);
					$('#progress-bar').css('width', ((json.totalsend * 100)/{TOTAL}) + '%');
					if (json.nextcustomerid > 0) {
						setTimeout(function() {
							nv_sendmail_action(json.rowsid, json.nextcustomerid);
						}, 1000);
					}else{
						$('#btn-control').slideUp();
					}
					return false;
				} else {
					return false;
				}
			}
		});
	}
	
	function nv_send_exit()
	{
		exit = 1;
		$('#btn-control').html('<em title="{LANG.start}" class="fa fa-play-circle fa-5x fa-pointer" onclick="nv_sendmail({ROWSID}, ' + currentcustomerid + '); return false;">&nbsp;</em>');
	}
	
	function nv_filer_emaillist(customerlist)
	{
		$('#table-emaillist > tbody  > tr').each(function(){
			if(customerlist.indexOf($(this).data('customerid')) >= 0){
				$(this).removeClass('hidden');
			}else{
				$(this).addClass('hidden');
			}
		});
		$('#table-emaillist > tbody  > tr:not(".hidden") > td.number').addNumber();
	}
	
	function send_test_email(){
	    if(confirm('{LANG.send_test_email_confirm}')){
	        $.ajax({
	        	type : 'POST',
	        	url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=send&nocache=' + new Date().getTime(),
	        	data : 'send_test_email=1&id={ROWSID}',
	        	success : function(res) {
	        		alert(res);
	        	}
	        });
	    }
	    return !1;
	}
</script>
<!-- END: main -->