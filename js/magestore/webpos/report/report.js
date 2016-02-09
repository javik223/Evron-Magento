var showOverlay = function(){
	if($D('#overlay'))$D('#overlay').animate({top:'0px'},300);
}

var hideOverlay = function(){
	if($D('#overlay')) $D('#overlay').animate({top:'2000px'},300);
}

var showReportsFilterPopup = function(){
	if($D('#reports_filter_form')) $D('#reports_filter_form').animate({left:'50%'},300);
	$('reports_filter_form').addClassName('showing');
}

var hideReportsFilterPopup = function(){
	if($D('#reports_filter_form')) $D('#reports_filter_form').animate({left:'-100%'},300);
	$('reports_filter_form').removeClassName('showing');
}

var showChart = function(){
	$D('#chartContainer').fadeIn();
	$('chartContainer').addClassName('showing');
}

var hideChart = function(){
	$D('#chartContainer').fadeOut();
	$('chartContainer').removeClassName('showing');
}

var showAjaxLoaderFilterPopup = function(){
	if($('report_filter_popup_ajax_loader')) $('report_filter_popup_ajax_loader').style.display = 'block';
}

var hideAjaxLoaderFilterPopup = function(){
	if($('report_filter_popup_ajax_loader')) $('report_filter_popup_ajax_loader').style.display = 'none';
}

var resetFilter = function(){
	if($('filter_period')) $('filter_period').value = 'week';
	if($('filter_time_from')) $('filter_time_from').value = '';
	if($('filter_time_to')) $('filter_time_to').value = '';
        if($('filter_showLead'))
            $('filter_showLead').checked = true;
        if($('filter_showSales'))
            $('filter_showSales').checked = true;
        if($('filter_showRate'))
            $('filter_showRate').checked = false;
        if($('filter_showEmpty'))
            $('filter_showEmpty').checked = true;
	if($('reports_filter_form').hasClassName('flipX')) $('reports_filter_form').removeClassName('flipX');
	else $('reports_filter_form').addClassName('flipX');
	$('filter_period_error_message').style.display = 'none';
	$('filter_time_from_error_message').style.display = 'none';
	$('filter_time_to_error_message').style.display = 'none';
}

var showReport = function(filterUrl){
	var error = checkFormFilterEmptyValue();
	if(error) return;
	
	var parameters = {};
	if($('filter_period')) parameters['period'] = $('filter_period').value;
	parameters['from'] = $('filter_time_from').value;
	parameters['to'] = $('filter_time_to').value;
        if($('filter_by'))
            parameters['filterBy'] = $('filter_by').value;
        if($('filter_by'))
            parameters['selectRange'] = $('filter_range').value;
        if($('filter_by'))
            parameters['order_status[]'] = $D('#wp_report_order_statuses').val();
        if($('wp_report_show_order_statuses'))
            parameters['order_type'] = $('wp_report_show_order_statuses').value;
        if($('filter_showLead'))
            parameters['showLead'] = ($('filter_showLead').checked == true)?true:false;
        if($('filter_showSales'))
            parameters['showSales'] = ($('filter_showSales').checked == true)?true:false;
        if($('filter_showRate'))
            parameters['showRate'] = ($('filter_showRate').checked == true)?true:false;
        if($('filter_showEmpty'))
            parameters['showEmpty'] = ($('filter_showEmpty').checked == true)?true:false;
		if($('filter_customertype'))
            parameters['groupByCustomer'] = ($('filter_customertype').checked == true)?true:false;
		if($('filter_agent'))
            parameters['groupByAgent'] = ($('filter_agent').checked == true)?true:false;
	
	showAjaxLoaderFilterPopup();
	if($('reportGrid') && $('reportGrid').down('.grid')){
		$('reportGrid').down('.grid').addClassName('zoom-out');
	}
	var request = new Ajax.Request(filterUrl, {
        method: 'post',
        parameters: parameters,
        loaderArea: false,
        onSuccess: function(transport) {
			var response = JSON.parse(transport.responseText);
			if(response.success){
				if($('reportGrid') && $('reportGrid').down('.grid')){
					$('reportGrid').down('.grid').removeClassName('zoom-out');
				}
				if($('reportGrid')) $('reportGrid').update(response.report_grid);
				hideAjaxLoaderFilterPopup();
				hideReportsFilterPopup();
				hideOverlay();  
				if($('filter_showRate') && $('filter_showRate').checked == true && $('filter_showSales') && $('filter_showSales').checked == true && $('filter_showLead') && $('filter_showLead').checked == true)
					$('scroll_buttons').removeClassName('hideElement');
				else 
					$('scroll_buttons').addClassName('hideElement');     
				if(response.totalsFieldData){
					
				}
			}
			if(response.error){
				hideAjaxLoaderFilterPopup();
				alert(response.error);
			}
                        /*Mr.Jack*/
                        var i = 0;
                        if(response.totalRow){
                            reportBy = 'location';
                            totalLocation = response.totalRow;
                            for (var key in totalLocation) {
                                if(!isNaN(totalLocation[key])){
                                    total_datas[i] = {label:key,y: parseFloat(totalLocation[key])?parseFloat(totalLocation[key]):0};
                                    i++;
                                }
                            }
                        }
                        if(response.totalRowByUser){
                            reportBy = 'user';
                            totalLocationByUser = response.totalRowByUser;
                            for (var key in totalLocationByUser) {
                                if(!isNaN(totalLocationByUser[key])){
                                    total_datasByUser[i] = {label:key,y: parseFloat(totalLocationByUser[key])?parseFloat(totalLocationByUser[key]):0};
                                    i++;
                                }
                            }
                        }
                        /**/
        },
		onComplete:function(transport){
			if($('reportGrid_table').down('tbody').innerHTML.replace(/ /g,'').replace(/\r?\n|\r/g,'') == '' ){
				$('reportGrid_table').down('tbody').innerHTML = "<tr><td colspan='100' class='totals_column' style='padding:5px;text-align:center'>No record found</td></tr>";
			}
		},
        onFailure: ''
    });
}

var checkFormFilterEmptyValue = function(){
	var error = false;
        if($('filter_period_error_message'))
            $('filter_period_error_message').style.display = ($('filter_period').value =='')?'block':'none';
	$('filter_time_from_error_message').style.display = ($('filter_time_from').value =='' && $('filter_period') && $('filter_period').value != '4' && $('filter_period').value != '5' && $('filter_period').value != '6' && $('filter_period').value != '7' )?'block':'none';
	$('filter_time_to_error_message').style.display = ($('filter_time_to').value =='' && $('filter_period') && $('filter_period').value != '4' && $('filter_period').value != '5' && $('filter_period').value != '6' && $('filter_period').value != '7' )?'block':'none';
	if($('filter_period') && $('filter_period').value =='') error = true;
	if($('filter_time_from').value =='' && $('filter_period') && $('filter_period').value != '4' && $('filter_range').value == '5' && $('filter_period').value != '5' && $('filter_period').value != '6' && $('filter_period').value != '7' ) error = true;
	if($('filter_time_to').value =='' && $('filter_period') && $('filter_period').value != '4' && $('filter_range').value == '5' && $('filter_period').value != '5' && $('filter_period').value != '6' && $('filter_period').value != '7' ) error = true;

	return error;
}

var filterReport = function(){
	if(!$('reports_filter_form').hasClassName('showing')){
		showOverlay();
		showReportsFilterPopup();
	}else{
		hideOverlay();
		hideReportsFilterPopup();
	}
}

var scrollGrid = function(gridScrollElement,rightOrLeft,isDblclick){
	var cleftPos = gridScrollElement.scrollLeft();
	var minValue = 300;
	var rangeValue = gridScrollElement.width()*20/100;
	if(rangeValue < minValue){
		rangeValue = minValue;
	}
	
	switch(rightOrLeft){
		case 'left':
			if(rangeValue > cleftPos) rangeValue = cleftPos;
			var nleftPos = cleftPos - rangeValue;
			if(isDblclick) gridScrollElement.animate({scrollLeft:0},300);
			else gridScrollElement.animate({scrollLeft:nleftPos},300);
			break;
		case 'right':
			var nleftPos = cleftPos + rangeValue;
			if(isDblclick) gridScrollElement.animate({scrollLeft:10000},2000);
			else gridScrollElement.animate({scrollLeft:nleftPos},300);
			break;
	}
}


var drawSegment = function(canvas, context, i, labels, data, colors) {
    context.save();
    var centerX = Math.floor(canvas.width / 2);
    var centerY = Math.floor(canvas.height / 2);
    radius = Math.floor(canvas.width / 2);

    var startingAngle = degreesToRadians(sumTo(data, i));
    var arcSize = degreesToRadians(data[i]);
    var endingAngle = startingAngle + arcSize;

    context.beginPath();
    context.moveTo(centerX, centerY);
    context.arc(centerX, centerY, radius, 
                startingAngle, endingAngle, false);
    context.closePath();

    context.fillStyle = colors[i];
    context.fill();

    context.restore();

    drawSegmentLabel(canvas, context, i, labels, data);
}

var degreesToRadians = function(degrees) {
    return (degrees * Math.PI)/180;
}

var sumTo = function(a, i) {
    var sum = 0;
    for (var j = 0; j < i; j++) {
      sum += a[j];
    }
    return sum;
}

var drawSegmentLabel = function(canvas, context, i, labels, data) {
	if(data[i] != 'undefined' && data[i] != 0){
	   context.save();
	   var x = Math.floor(canvas.width / 2);
	   var y = Math.floor(canvas.height / 2);
	   var angle = degreesToRadians(sumTo(data, i));

	   context.translate(x, y);
	   context.rotate(angle);
	   var dx = Math.floor(canvas.width * 0.5) - 10;
	   var dy = Math.floor(canvas.height * 0.05);

	   context.textAlign = "right";
	   var fontSize = Math.floor(canvas.height / 25);
	   context.font = fontSize + "pt Helvetica";

	   context.fillText(labels[i], dx, dy);

	   context.restore();
	}
}

var showOrHideChartBySettings = function(){
	var hideAll = true;
	
	if($('filter_showLead') && $('filter_showLead').checked == false && $('bt_download_lead_charts')) $('bt_download_lead_charts').hide();
	else if($('bt_download_lead_charts')){ $('bt_download_lead_charts').show();hideAll = false;}
	
	if($('filter_showSales') && $('filter_showSales').checked == false && $('bt_download_sales_charts')) $('bt_download_sales_charts').hide();
	else{ 
		if($('bt_download_lead_charts')) $('bt_download_lead_charts').show();
		if($('bt_download_sales_charts')) $('bt_download_sales_charts').show(); 
		hideAll = false;
	}
		
	if($('filter_showRate') && $('filter_showRate').checked == false){
		if($('bt_show_converted_chart')) $('bt_show_converted_chart').hide();
		if($('bt_download_rates_charts')) $('bt_download_rates_charts').hide();
	}else{
		if($('bt_download_lead_charts')) $('bt_download_lead_charts').show();
		$('bt_download_sales_charts').show(); 
		if($('bt_show_converted_chart')) $('bt_show_converted_chart').show();
		if($('bt_download_rates_charts')) $('bt_download_rates_charts').show();
		hideAll = false;
	}
	return hideAll;
}
