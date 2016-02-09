/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

var dashboardController = new Class.create();

dashboardController.prototype = {
    initialize: function () {

    },
    addDashboard: function () {
        if ($('add_tab')) {
            if (document.getElementById('add_tab').style.display == 'none') {
                //                    document.getElementById('add_tab').style.display='block'; 
                $_('#add_tab').fadeIn(20);
                if ($('add_tab_chart')) {
                    if (document.getElementById('add_tab_chart').style.display == 'block'
                            || document.getElementById('add_tab_chart').style.display == '') {
                        //                            document.getElementById('add_tab_chart').style.display='none'; 
                        $_('#add_tab_chart').fadeOut(20);
                    }
                }
                if ($('edit_tab')) {
                    if (document.getElementById('edit_tab').style.display == 'block'
                            || document.getElementById('edit_tab').style.display == '') {
                        //                            document.getElementById('edit_tab').style.display='none'; 
                        $_('#edit_tab').fadeOut(20);
                    }
                }
                if ($('edit_item_form')) {
                    $('edit_item_form').innerHTML = '';
                }
            } else {
                //                    document.getElementById('add_tab').style.display='none'; 
                $_('#add_tab').fadeOut(20);
            }
        }
    },
    addDashboardChart: function () {
        if ($('add_tab_chart')) {
            if (document.getElementById('add_tab_chart').style.display == 'none') {
                //                    document.getElementById('add_tab_chart').style.display='block';
                $_('#add_tab_chart').fadeIn(20);
                if ($('edit_item_form')) {
                    if (document.getElementById('edit_item_form').style.display == 'block'
                            || document.getElementById('edit_item_form').style.display == '') {
                        //                            document.getElementById('add_tab_chart').style.display='none';
                        $_('#edit_item_form').fadeOut(20);
                    }
                }
                if ($('add_tab')) {
                    if (document.getElementById('add_tab').style.display == 'block'
                            || document.getElementById('add_tab').style.display == '') {
                        //                            document.getElementById('add_tab').style.display='none'; 
                        $_('#add_tab').fadeOut(20);
                    }
                }
                if ($('edit_tab')) {
                    if (document.getElementById('edit_tab').style.display == 'block'
                            || document.getElementById('edit_tab').style.display == '') {
                        //                            document.getElementById('edit_tab').style.display='none'; 
                        $_('#edit_tab').fadeOut(20);
                    }
                }
                if ($('edit_item_form')) {
                    $('edit_item_form').innerHTML = '';
                }
            } else {
                //                    document.getElementById('add_tab_chart').style.display='none';
                $_('#add_tab_chart').fadeOut(20);
            }
        }
    },
    editDashboard: function () {
        if ($('edit_tab')) {
            if (document.getElementById('edit_tab').style.display == 'none') {
                //                    document.getElementById('edit_tab').style.display='block';
                $_('#edit_tab').fadeIn(20);
                if ($('add_tab_chart')) {
                    if (document.getElementById('add_tab_chart').style.display == 'block'
                            || document.getElementById('add_tab_chart').style.display == '') {
                        //                            document.getElementById('add_tab_chart').style.display='none';
                        $_('#add_tab_chart').fadeOut(20);
                    }
                }
                if ($('add_tab')) {
                    if (document.getElementById('add_tab').style.display == 'block'
                            || document.getElementById('add_tab').style.display == '') {
                        //                            document.getElementById('add_tab').style.display='none';
                        $_('#add_tab').fadeOut(20);
                    }
                }
                if ($('edit_item_form')) {
                    $('edit_item_form').innerHTML = '';
                }
            } else {
                //                    document.getElementById('edit_tab').style.display='none';
                $_('#edit_tab').fadeOut(20);
            }
        }
    },
    submitAddTabForm: function (element) {
        if (!$('dashboard_name') || !$('dashboard_name').value || $('dashboard_name').value == '' || $('dashboard_name').value == '') {
            $('advice-required-entry-add-dashboard-name').style.display = 'block';
        } else {
            if ($('advice-required-entry-add-dashboard-name').style.display == 'block') {
                $('advice-required-entry-add-dashboard-name').style.display = 'none';
            }
            element.addClassName('button-grey');
            element.disable();
            $('add_tab_form').submit();
        }
    },
    changeSelectGroupType: function (id) {
        if (id.value == "sales") {
            $('sales_report').style.display = "inline-block";
        }
        else {
            $("sales_report").style.display = "none";
            $("sales_report").className = "";
        }
        if (id.value == 'purchaseorder') {
            $("purchaseorder_report").style.display = "inline-block";
            $("purchaseorder_report").className = "required-entry required-entry select";
            $("attribute_sales_report").style.display = "none";
            $("attribute_sales_report").className = "";
        }
        else {
            $("purchaseorder_report").style.display = "none";
            $("purchaseorder_report").className = "";
        }
        if (id.value == 'stockonhand') {
            $("stockonhand_report").style.display = "inline-block";
            $("stockonhand_report").className = "required-entry required-entry select";
            $("attribute_sales_report").style.display = "none";
            $("attribute_sales_report").className = "";
        }
        else {
            $("stockonhand_report").style.display = "none";
            $("stockonhand_report").className = "";
        }
        if (id.value == 'stockmovement') {
            $("stockmovement_report").style.display = "inline-block";
            $("stockmovement_report").className = "required-entry required-entry select";
            $("attribute_sales_report").style.display = "none";
            $("attribute_sales_report").className = "";
        }
        else {
            $("stockmovement_report").style.display = "none";
            $("stockmovement_report").className = "";
        }
        if (id.value == 'customer') {
            $("customer_report").style.display = "inline-block";
            $("customer_report").className = "required-entry required-entry select";
            $("attribute_sales_report").style.display = "none";
            $("attribute_sales_report").className = "";
        }
        else {
            $("customer_report").style.display = "none";
            $("customer_report").className = "";
        }
    },
    changeSalesChartType: function (url) {
        if ($("sales_report") && $("sales_report").value) {
            $("default_chart_type").style.display = "none";
            $("default_chart_type").checked = false;
            if ($('sales_report').value == 'order_attribute') {
                $('attribute_sales_report').style.display = "inline-block";
                $('attribute_sales_report').className = "required-entry required-entry select";
            }
            else {
                $('attribute_sales_report').style.display = "none";
                $("attribute_sales_report").className = "";
            }
            var reportType = $('sales_report').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type'))
                            $('select_chart_type').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changePurchaseorderChartType: function (url) {
        if ($("purchaseorder_report") && $("purchaseorder_report").value) {
            $("default_chart_type").style.display = "none";
            $("default_chart_type").checked = false;
            var reportType = $('purchaseorder_report').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type'))
                            $('select_chart_type').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeStockonhandChartType: function (url) {
        if ($("stockonhand_report") && $("stockonhand_report").value) {
            $("default_chart_type").style.display = "none";
            $("default_chart_type").checked = false;
            var reportType = $('stockonhand_report').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type'))
                            $('select_chart_type').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeStockmovementChartType: function (url) {
        if ($("stockmovement_report") && $("stockmovement_report").value) {
            $("default_chart_type").style.display = "none";
            $("default_chart_type").checked = false;
            var reportType = $('stockmovement_report').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type'))
                            $('select_chart_type').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeCustomerChartType: function (url) {
        if ($("customer_report") && $("customer_report").value) {
            $("default_chart_type").style.display = "none";
            $("default_chart_type").checked = false;
            var reportType = $('customer_report').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type'))
                            $('select_chart_type').innerHTML = stringre;
                    }
                }
            });
        }
    },
    submitAddChartForm: function () {
        var add_chart_form = new varienForm('add_chart_form', '');
        if (add_chart_form.validate()) {
            $('add_chart_form').submit();
        } else {

        }
    },
    submitEditTabForm: function () {
        if (!$('edit_dashboard_name') || !$('edit_dashboard_name').value || $('edit_dashboard_name').value == '' || $('edit_dashboard_name').value == '') {
            $('advice-required-entry-edit-dashboard-name').style.display = 'block';
        } else {
            if ($('advice-required-entry-edit-dashboard-name').style.display == 'block') {
                $('advice-required-entry-edit-dashboard-name').style.display = 'none';
            }
            $('edit_tab_form').submit();
        }
    },
    deleteDashboard: function (message, url) {
        var r = confirm(message);
        if (r == true) {
            setLocation(url);
        }
    },
    submitEditChartForm: function () {
        var edit_chart_form = new varienForm('edit_chart_form', '');
        if (edit_chart_form.validate()) {
            $('edit_chart_form').submit();
        }
    },
    cancelEditChartForm: function () {
        if ($('edit_item_form'))
            $('edit_item_form').innerHTML = '';
    },
    deleteChart: function (id, message, url, textNode) {
        var r = confirm(message);
        if (r == true) {
            if ($('edit_item_form')) {
                if (document.getElementById('edit_item_form').style.display == 'block'
                        || document.getElementById('edit_item_form').style.display == '') {
                    $_('#edit_item_form').fadeOut(20);
                }
            }
            var deleteChartUrl = url;
            deleteChartUrl += 'item_id/' + id;
            new Ajax.Request(deleteChartUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if ($('data-view-id-' + id))
                        $('data-view-id-' + id).style.display = 'none';

                    var messageDiv = document.getElementById('messages');
                    messageDiv.innerHTML = '';
                    var ulMessage = document.createElement('UL');
                    ulMessage.className = 'messages';
                    var liMessage = document.createElement('LI');
                    liMessage.className = 'success-msg';
                    var textnode = document.createTextNode(textNode);
                    liMessage.appendChild(textnode);
                    ulMessage.appendChild(liMessage);
                    messageDiv.appendChild(ulMessage);
                    return false;
                }
            });
        }
    },
    editItem: function (itemId, url) {
        if (itemId) {
            if ($('edit_item_form')) {
                if ($('add_tab_chart')) {
                    if (document.getElementById('add_tab_chart').style.display == 'block'
                            || document.getElementById('add_tab_chart').style.display == '') {
                        $_('#add_tab_chart').fadeOut(20);
                    }
                }
                if ($('edit_tab')) {
                    if (document.getElementById('edit_tab').style.display == 'block'
                            || document.getElementById('edit_tab').style.display == '') {
                        //                        document.getElementById('edit_tab').style.display='none'; 
                        $_('#edit_tab').fadeOut(20);
                    }
                }
                if ($('add_tab')) {
                    if (document.getElementById('add_tab').style.display == 'block'
                            || document.getElementById('add_tab').style.display == '') {
                        //                        document.getElementById('add_tab').style.display='none';
                        $_('#add_tab').fadeOut(20);
                    }
                }
                var editItemTypeUrl = url;
                editItemTypeUrl += 'item_id/' + itemId;
                new Ajax.Request(editItemTypeUrl, {
                    method: 'get',
                    parameters: '',
                    onFailure: '',
                    onSuccess: function (transport) {
                        if (transport.status == 200) {
                            var stringre = transport.responseText;
                            $('edit_item_form').innerHTML = stringre;
                            $_('#edit_item_form').fadeIn(20);
                            this.scrollTo(0, 1000);
                        }
                    }.bind(this)
                });
            }
        }
    },
    changeSelectGroupTypeEdit: function (id) {
        if (id.value == "sales") {
            $('sales_report_edit').style.display = "inline-block";
        }
        else {
            $("sales_report_edit").style.display = "none";
            $("sales_report_edit").className = "";
        }
        if (id.value == 'purchaseorder') {
            $("purchaseorder_report_edit").style.display = "inline-block";
            $("purchaseorder_report_edit").className = "required-entry required-entry select";
            $("attribute_sales_report_edit").style.display = "none";
            $("attribute_sales_report_edit").className = "";
        }
        else {
            $("purchaseorder_report_edit").style.display = "none";
            $("purchaseorder_report_edit").className = "";
        }
        if (id.value == 'stockonhand') {
            $("stockonhand_report_edit").style.display = "inline-block";
            $("stockonhand_report_edit").className = "required-entry required-entry select";
            $("attribute_sales_report_edit").style.display = "none";
            $("attribute_sales_report_edit").className = "";
        }
        else {
            $("stockonhand_report_edit").style.display = "none";
            $("stockonhand_report_edit").className = "";
        }
        if (id.value == 'stockmovement') {
            $("stockmovement_report_edit").style.display = "inline-block";
            $("stockmovement_report_edit").className = "required-entry required-entry select";
            $("attribute_sales_report_edit").style.display = "none";
            $("attribute_sales_report_edit").className = "";
        }
        else {
            $("stockmovement_report_edit").style.display = "none";
            $("stockmovement_report_edit").className = "";
        }
        if (id.value == 'customer') {
            $("customer_report_edit").style.display = "inline-block";
            $("customer_report_edit").className = "required-entry required-entry select";
            $("attribute_sales_report_edit").style.display = "none";
            $("attribute_sales_report_edit").className = "";
        }
        else {
            $("customer_report_edit").style.display = "none";
            $("customer_report_edit").className = "";
        }
    },
    changeSalesChartTypeEdit: function (url) {
        if ($("sales_report_edit") && $("sales_report_edit").value) {
            $("default_chart_type_edit").style.display = "none";
            $("default_chart_type_edit").checked = false;
            if ($("sales_report_edit").value == 'order_attribute') {
                $('attribute_sales_report_edit').style.display = "inline-block";
                $('attribute_sales_report_edit').className = "required-entry required-entry select";
            }
            else {
                $('attribute_sales_report_edit').style.display = "none";
                $("attribute_sales_report_edit").className = "";
            }
            var reportType = $('sales_report_edit').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type_edit'))
                            $('select_chart_type_edit').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changePurchaseorderChartTypeEdit: function (url) {
        if ($("purchaseorder_report_edit") && $("purchaseorder_report_edit").value) {
            $("default_chart_type_edit").style.display = "none";
            $("default_chart_type_edit").checked = false;
            var reportType = $('purchaseorder_report_edit').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type_edit'))
                            $('select_chart_type_edit').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeStockonhandChartTypeEdit: function (url) {
        if ($("stockonhand_report_edit") && $("stockonhand_report_edit").value) {
            $("default_chart_type_edit").style.display = "none";
            $("default_chart_type_edit").checked = false;
            var reportType = $('stockonhand_report_edit').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type_edit'))
                            $('select_chart_type_edit').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeStockmovementChartTypeEdit: function (url) {
        if ($("stockmovement_report_edit") && $("stockmovement_report_edit").value) {
            $("default_chart_type_edit").style.display = "none";
            $("default_chart_type_edit").checked = false;
            var reportType = $('stockmovement_report_edit').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type_edit'))
                            $('select_chart_type_edit').innerHTML = stringre;
                    }
                }
            });
        }
    },
    changeCustomerChartTypeEdit: function (url) {
        if ($("customer_report_edit") && $("customer_report_edit").value) {
            $("default_chart_type_edit").style.display = "none";
            $("default_chart_type_edit").checked = false;
            var reportType = $('customer_report_edit').value;
            var changeSelectChartTypeUrl = url;
            changeSelectChartTypeUrl += 'report_code/' + reportType;
            new Ajax.Request(changeSelectChartTypeUrl, {
                method: 'get',
                parameters: '',
                onFailure: '',
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var stringre = transport.responseText;
                        if ($('select_chart_type_edit'))
                            $('select_chart_type_edit').innerHTML = stringre;
                    }
                }
            });
        }
    },
    // Element to move, time in ms to animate
    scrollTo: function (element, duration) {
        var e = document.documentElement;
        if (e.scrollTop === 0) {
            var t = e.scrollTop;
            ++e.scrollTop;
            e = t + 1 === e.scrollTop-- ? e : document.body;
        }
        this.scrollToC(e, e.scrollTop, element, duration);
    },
    // Element to move, element or px from, element or px to, time in ms to animate
    scrollToC: function (element, from, to, duration) {
        if (duration < 0)
            return;
        if (typeof from === "object")
            from = from.offsetTop;
        if (typeof to === "object")
            to = to.offsetTop;

        this.scrollToX(element, from, to, 0, 1 / duration, 20, this.easeOutCuaic);
    },
    scrollToX: function (element, x1, x2, t, v, step, operacion) {
        if (t < 0 || t > 1 || v <= 0)
            return;
        element.scrollTop = x1 - (x1 - x2) * operacion(t);
        t += v * step;

        setTimeout(function () {
            this.scrollToX(element, x1, x2, t, v, step, operacion);
        }.bind(this), step);
    },
    easeOutCuaic: function (t) {
        t--;
        return t * t * t + 1;
    }
}