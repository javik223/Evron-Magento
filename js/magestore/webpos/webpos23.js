var webposArea = Class.create(
        {
            initialize: function (elementId, startX, startY, finishX, finishY)
            {
                this.elementId = elementId;
                this.startPos = {left: startX, top: startY, zIndex: '0'};
                this.finishPos = {left: finishX, top: finishY};
                this.hideArea();
            },
            showArea: function ()
            {
                this.showing = true;
                if ($D('#' + this.elementId)) {
                    var newwidth = $D('#product-left').width();
                    $D('#' + this.elementId).css({zIndex: '10', width: newwidth});
                    $D('#' + this.elementId).animate(this.finishPos);
                    showMediumOverlay();
                }
            },
            hideArea: function ()
            {
                this.showing = false;
                if ($D('#' + this.elementId)) {
                    $D('#' + this.elementId).animate(this.startPos);
                    hideMediumOverlay();
                }
            },
            toggleArea: function ()
            {
                if (this.showing == true)
                    this.hideArea();
                else
                    this.showArea();
            },
        });

var webposHoldOrder = Class.create(
        {
            initialize: function ()
            {
                this.items = JSON.parse(localGet('holded_orders'));
                if (this.items == null) {
                    this.items = {};
                }
            },
            refreshData: function ()
            {
                this.items = JSON.parse(localGet('holded_orders'));
                if (this.items == null) {
                    this.items = {};
                }
            },
            add: function (order_data)
            {
                this.refreshData();
                this.items = JSON.parse(localGet('holded_orders'));
                var holded_orders = {};
                if ($D.jStorage.get('holded_orders') != null) {
                    holded_orders = JSON.parse($D.jStorage.get('holded_orders'));
                    if (holded_orders['user_' + currentUserId] != null) {
                        var orderArr = $D.map(holded_orders['user_' + currentUserId], function (value, index) {
                            return [value];
                        });
                        var numberOrderHolded = orderArr.length;
                    } else
                        numberOrderHolded = 0;
                    var newnumber = numberOrderHolded + 1;
                    if (holded_orders['user_' + currentUserId] == null)
                        holded_orders['user_' + currentUserId] = {};
                    while (holded_orders['user_' + currentUserId]['order_' + newnumber] != null) {
                        newnumber++;
                    }
                    holded_orders['user_' + currentUserId]['order_' + newnumber] = order_data;
                    $D.jStorage.set('holded_orders', JSON.stringify(holded_orders));
                    localSet('last_holded_order', 'order_' + newnumber);
                } else {
                    var holded_orders = {};
                    holded_orders['user_' + currentUserId] = {'order_0': order_data};
                    $D.jStorage.set('holded_orders', JSON.stringify(holded_orders));
                    localSet('last_holded_order', 'order_0');
                }
            },
            fillAll: function ()
            {
                this.refreshData();
                if (this.items['user_' + currentUserId]) {
                    if ($('holded_orders_section') && $('holded_orders_section').down('.content')) {
                        var contentArea = $('holded_orders_section').down('.content');
                        contentArea.innerHTML = '';
                        this.items = this.items['user_' + currentUserId];
                        for (var i in this.items) {
                            contentArea.innerHTML += this.getHoldedItemHtml(i, this.items[i]);
                        }
                    }
                }
            },
            getHoldedItemHtml: function (key, item) {
                var customer_name = item.customer_name;
                var short_description = item.short_description;
                var full_description = item.full_description;
                var items = JSON.parse(item.items);
                var html = "";
                html += "\
                    <div id='holded_" + key + "' class='item' onmouseover='checkShowDetailIn(this)' onmouseout='checkShowDetailOut(this)' onclick='showDetailHoldedOrder(this)'>\
                        <div class='customer_name'>" + customer_name + "</div>\
                        <div class='short_description'>" + short_description + "</div>\
                        <div class='buttons'>\
                            <div class='bt_reload_order' onclick=\"reloadOrder('" + key + "')\">" + bt_reload_order_label + "</div>\
                            <div class='bt_delete_holded_order' onclick=\"deleteHoldedOrder('" + key + "')\">" + bt_delete_holded_order_label + "</div>\
                        </div>\
                        <div class='detail hide'>\
                            <div class='products'>\
                                <ul>";
                if (items && items.length > 0) {
                    for (var i in items) {
                        if (typeof items[i] == 'string') {
                            html += "<li>" + items[i] + "</li>";
                        }
                    }
                }
                html += "</ul>\
                            </div>\
                            <div class='full_description'>\
                                " + full_description + "\
                            </div>\
                        </div>\
                    </div>\
                ";
                return html;
            },
            removeByKey: function (key) {
                var holded_orders = {};
                if ($D.jStorage.get('holded_orders') != null) {
                    holded_orders = JSON.parse($D.jStorage.get('holded_orders'));
                    if (holded_orders['user_' + currentUserId][key] != null) {
                        var order_id = holded_orders['user_' + currentUserId][key].order_id;
                        delete  holded_orders['user_' + currentUserId][key];
                        var canceled = localGet('canceled');
                        localSet('canceled', 'false');
                        if (isRealOffline() == false && order_id != '' && canceling_holded_order == false && canceled == 'false') {
                            canceling_holded_order = true;
                            hasAnotherRequest = true;
                            if ($('bt_hold_order') && $('bt_hold_order').hasClassName('unhold')) {
                                showColrightAjaxloader();
                            }else{
								showHoldedListAjaxloader();
							}
                            var request = new Ajax.Request(cancel_holded_order_url, {
                                method: 'get',
                                parameters: {order_id: order_id},
                                onSuccess: function (transport) {
                                    if (transport.status == 200) {
                                        var response = JSON.parse(transport.responseText);
                                        if (response.success) {

                                        }
                                        if (response.grandTotal) {
                                            $('cashin_fullamount').innerHTML = response.grandTotal;
                                            if ($('remain_value_label'))
                                                $('remain_value_label').innerHTML = response.grandTotal;
                                            if ($('remain_value'))
                                                $('remain_value').innerHTML = response.grandTotal;
                                        }
                                        if (response.downgrandtotal)
                                            $('round_down_cashin').innerHTML = response.downgrandtotal;
                                        if (response.upgrandtotal)
                                            $('round_up_cashin').innerHTML = response.upgrandtotal;
                                        if (response.grand_total)
                                            $('webpos_subtotal_button').innerHTML = response.grand_total;
                                        if (response.payment_method && $('payment_method'))
                                            $('payment_method').update(response.payment_method);
                                        if (response.shipping_method && $('shipping_method'))
                                            $('shipping_method').update(response.shipping_method);
                                        if (response.pos_items && $('webpos_cart'))
                                            $('webpos_cart').update(response.pos_items);
                                        if (response.totals && $('pos_totals'))
                                            $('pos_totals').update(response.totals);
                                        if (response.number_item && $('total_number_item')) {
                                            $('total_number_item').update(response.number_item);

                                        }
                                        if (response.errorMessage && response.errorMessage != '') {
                                            showToastMessage('danger', 'Error', response.errorMessage);
                                        }
                                    }
                                    canceling_holded_order = false;
                                    hasAnotherRequest = false;
                                    hideColrightAjaxloader();
                                },
                                onComplete: function () {
                                    canceling_holded_order = false;
                                    hasAnotherRequest = false;
                                    hideColrightAjaxloader();
									hideHoldedListAjaxloader();
                                    if ($('bt_hold_order') && $('bt_hold_order').hasClassName('unhold')) {
                                        $('bt_hold_order').removeClassName('unhold');
                                        buttonUnholdToHold();
                                        disableCheckout();
                                        deleteCustomerJs();
                                        hideHoldButton();
                                        if ($('main_container').hasClassName('hideCategory'))
                                            showCategory();
                                    }
									showToastMessage('success', 'Message', cancel_hold_order_success_message);
									reloadHoldedList();
                                },
                                onFailure: function () {
                                    canceling_holded_order = false;
                                    hasAnotherRequest = false;
                                    hideColrightAjaxloader();
									hideHoldedListAjaxloader();
									showToastMessage('danger', 'Message', cancel_hold_order_error_message);
                                }
                            });
                        }
                    }
                    $D.jStorage.set('holded_orders', JSON.stringify(holded_orders));
                }
            },
            removeAll: function () {
                localDelete('holded_orders');
            },
            count: function () {
                this.refreshData();
                return this.items.length;
            },
            reload: function (key) {
                this.refreshData();
                if (this.items['user_' + currentUserId]) {
                    this.items = this.items['user_' + currentUserId];
                    if (this.items[key] != null) {
                        var order_data = this.items[key];
                        var carthtml = order_data.carthtml;
                        var shipping_method = order_data.shipping_method;
                        var payment_method = order_data.payment_method;
                        var customer_name = order_data.customer_name;
                        var customer_id = order_data.customerid;
                        var customer_email = order_data.customer_email;
                        var order_id = order_data.order_id;
                        $('webpos_cart').innerHTML = carthtml;
                        if (isRealOffline() == true) {
                            if (customer_name) {
                                $('add-customer').innerHTML = '<p>' + customer_name + "</p><span>" + customer_email + "</span>";
                                $D('#add-customer').attr('onclick', 'showAddCustomer()');
                                $D('#add-customer').addClass('active');
                                $D('#popup-customer').hide();
                                $D('.add-customer').removeClass('active');
                                $D('#remove-customer').show();
                            }
                            if (typeof holded_order_section == 'object')
                                holded_order_section.hideArea();
                            localSet('reloading_holded_key', key);
                            buttonHoldToUnhold(key, order_id);
                        } else {
                            if (reloading_order == false) {
                                if ($('showmenu_icon')) {
                                    $('showmenu_icon').click();
                                }
                                if (typeof holded_order_section == 'object')
                                    holded_order_section.hideArea();
                                reloading_order = true;
                                hasAnotherRequest = true;
                                showColrightAjaxloader();
                                var request = new Ajax.Request(reload_order_url, {
                                    method: 'post',
                                    parameters: {order_id: order_id, customer_id: customer_id, holded_key: key},
                                    onSuccess: function (transport) {
                                        if (transport.status == 200) {
                                            var response = JSON.parse(transport.responseText);
                                            if (response.errorMessage && response.errorMessage != '') {
                                                showToastMessage('danger', 'Error', response.errorMessage);
                                            }
                                            if (response.customer_name) {
                                                $('add-customer').innerHTML = '<p>' + response.customer_name + "</p><span>" + response.customer_email + "</span>";
                                                $D('#add-customer').attr('onclick', 'showAddCustomer()');
                                                $D('#add-customer').addClass('active');
                                                $D('#popup-customer').hide();
                                                $D('.add-customer').removeClass('active');
                                                $D('#remove-customer').show();
                                            }
                                        }
                                        reloading_order = false;
                                        hasAnotherRequest = false;
                                    },
                                    onComplete: function () {
                                        reloading_order = false;
                                        hasAnotherRequest = false;
                                        buttonHoldToUnhold(key, order_id);
                                        reloadAllBlock();
                                    },
                                    onFailure: function () {
                                        hideColrightAjaxloader();
                                        reloading_order = false;
                                        hasAnotherRequest = false;
                                    }
                                });
                            }
                        }
                        enableCheckout();
                    }
                }
            },
            reloadByOrderId: function (order_id) {
                $('checkout').click();
                this.refreshData();
                var key = this.getHoldedKeyByOrderId(order_id);
                if (key != '') {
                    this.reload(key);

                } else {
                    if (isRealOffline() == true) {

                    } else {
                        if (reloading_order == false) {
                            if (typeof holded_order_section == 'object')
                                holded_order_section.hideArea();
                            reloading_order = true;
                            hasAnotherRequest = true;
                            showColrightAjaxloader();
                            var request = new Ajax.Request(reload_order_url, {
                                method: 'post',
                                parameters: {order_id: order_id},
                                onSuccess: function (transport) {
                                    if (transport.status == 200) {
                                        var response = JSON.parse(transport.responseText);
                                        if (response.errorMessage && response.errorMessage != '') {
                                            showToastMessage('danger', 'Error', response.errorMessage);
                                        }
                                        if (response.customer_name) {
                                            $('add-customer').innerHTML = '<p>' + response.customer_name + "</p><span>" + response.customer_email + "</span>";
                                            $D('#add-customer').attr('onclick', 'showAddCustomer()');
                                            $D('#add-customer').addClass('active');
                                            $D('#popup-customer').hide();
                                            $D('.add-customer').removeClass('active');
                                            $D('#remove-customer').show();
                                        }
                                    }
                                    reloading_order = false;
                                    hasAnotherRequest = false;
                                },
                                onComplete: function () {
                                    reloading_order = false;
                                    hasAnotherRequest = false;
                                    buttonHoldToUnhold(key, order_id);
                                    reloadAllBlock();
                                },
                                onFailure: function () {
                                    hideColrightAjaxloader();
                                    reloading_order = false;
                                    hasAnotherRequest = false;
                                }
                            });
                        }
                    }
                    enableCheckout();
                }
            },
            removeByOrderId: function (order_id) {            
                this.refreshData();
                var key = this.getHoldedKeyByOrderId(order_id);
                if (key != '') {
                    this.removeByKey(key);
                } else {
                    var canceled = localGet('canceled');
                    localSet('canceled', 'false');
                    if (isRealOffline() == false && order_id != '' && canceling_holded_order == false && canceled == 'false') {
                        canceling_holded_order = true;
                        hasAnotherRequest = true;
                        if ($('bt_hold_order') && $('bt_hold_order').hasClassName('unhold')) {
                            showColrightAjaxloader();
                        }else{
							showHoldedListAjaxloader();
						}
                        var request = new Ajax.Request(cancel_holded_order_url, {
                            method: 'get',
                            parameters: {order_id: order_id},
                            onSuccess: function (transport) {
                                if (transport.status == 200) {
                                    var response = JSON.parse(transport.responseText);
                                    if (response.success) {

                                    }
                                    if (response.grandTotal) {
                                        $('cashin_fullamount').innerHTML = response.grandTotal;
                                        if ($('remain_value_label'))
                                            $('remain_value_label').innerHTML = response.grandTotal;
                                        if ($('remain_value'))
                                            $('remain_value').innerHTML = response.grandTotal;
                                    }
                                    if (response.downgrandtotal)
                                        $('round_down_cashin').innerHTML = response.downgrandtotal;
                                    if (response.upgrandtotal)
                                        $('round_up_cashin').innerHTML = response.upgrandtotal;
                                    if (response.grand_total)
                                        $('webpos_subtotal_button').innerHTML = response.grand_total;
                                    if (response.payment_method && $('payment_method'))
                                        $('payment_method').update(response.payment_method);
                                    if (response.shipping_method && $('shipping_method'))
                                        $('shipping_method').update(response.shipping_method);
                                    if (response.pos_items && $('webpos_cart'))
                                        $('webpos_cart').update(response.pos_items);
                                    if (response.totals && $('pos_totals'))
                                        $('pos_totals').update(response.totals);
                                    if (response.number_item && $('total_number_item')) {
                                        $('total_number_item').update(response.number_item);

                                    }
                                    if (response.errorMessage && response.errorMessage != '') {
                                        showToastMessage('danger', 'Error', response.errorMessage);
                                    }
                                }
                                canceling_holded_order = false;
                                hasAnotherRequest = false;
                                hideColrightAjaxloader();
                            },
                            onComplete: function () {
                                canceling_holded_order = false;
                                hasAnotherRequest = false;
                                hideColrightAjaxloader();
								hideHoldedListAjaxloader();
                                if ($('bt_hold_order') && $('bt_hold_order').hasClassName('unhold')) {
                                    $('bt_hold_order').removeClassName('unhold');
                                    buttonUnholdToHold();
                                    disableCheckout();
                                    deleteCustomerJs();
                                    hideHoldButton();
                                    if ($('main_container').hasClassName('hideCategory'))
                                        showCategory();
                                }
								showToastMessage('success', 'Message', cancel_hold_order_success_message);
								reloadHoldedList();
								collectCartTotal();
                            },
                            onFailure: function () {
                                canceling_holded_order = false;
                                hasAnotherRequest = false;
                                hideColrightAjaxloader();
								hideHoldedListAjaxloader();
								showToastMessage('danger', 'Message', cancel_hold_order_error_message);
                            }
                        });
                    }
                }
            },
            getHoldedKeyByOrderId: function (order_id) {
                this.refreshData();
                if (this.items['user_' + currentUserId]) {
                    this.items = this.items['user_' + currentUserId];
                    if (!$D.isEmptyObject(this.items)) {
                        for (var key in this.items) {
                            if (this.items[key].order_id == order_id) {
                                return key;
                            }
                        }
                    }
                }
                return '';
            },
            previewByOrderId: function (order_id) {
                showHoldedOrdersDetail(order_id);
            }
        });

function showActiveMenu(el) {
	hideHoldedOrdersDetail();
	hideBoxLogout();
    hideDropdownCategory();
    var menu_items = $$(".menu_item");
    if (menu_items.length > 0) {
        menu_items.each(function (other_el) {
            if (other_el != el) {
                other_el.removeClassName('menuactive');
                if (other_el.id != '' && other_el.id != 'reports' && $(other_el.id + '_area')) {
                    $(other_el.id + '_area').removeClassName('showing');
                    hideContainerArea(other_el.id + '_area');
                }
                if ($(other_el.id + '_area') && other_el.hasClassName('menuactive')) {
                    $(other_el.id + '_area').removeClassName('showing');
                    hideContainerArea(other_el.id + '_area');
                }
            }
        });
    }
    el.addClassName('menuactive');
    if (el.id != '' && el.id != 'reports' && $(el.id + '_area')) {
        if ($(el.id + '_area').hasClassName('showing')) {
            hideContainerArea(el.id + '_area');
            $(el.id + '_area').removeClassName('showing');
            $('checkout').addClassName('menuactive');
            el.removeClassName('menuactive');
        } else {
            showContainerArea(el.id + '_area');
            $(el.id + '_area').addClassName('show');
            $(el.id + '_area').addClassName('showing');
            $(el.id + '_area').addClassName('showMainContainer');
            el.addClassName('menuactive');
        }
    }
}

function menuClickNew(el) {
    showActiveMenu(el);
    var menuId = el.id;
    if (menuId == 'holded_orders') {
        if (holded_order_section.showing == true) {
            $('checkout').click();
        } else {
            if (typeof cashdrawer_section == 'object' && cashdrawer_section.showing == true)
                cashdrawer_section.hideArea();
            if (typeof reports_section == 'object' && reports_section.showing == true)
                reports_section.hideArea();
            holded_order_section.showArea();
            if (isRealOffline() == true) {
                var hold_order_object = new webposHoldOrder();
                hold_order_object.fillAll();
            } else {
                reloadHoldedList();
            }
        }
    }
    if (menuId == 'cash_drawer') {
        if (cashdrawer_section.showing == true) {
            $('checkout').click();
        } else {
            if (typeof holded_order_section == 'object' && holded_order_section.showing == true)
                holded_order_section.hideArea();
            if (typeof reports_section == 'object' && reports_section.showing == true)
                reports_section.hideArea();
            cashdrawer_section.showArea();
            if (isRealOffline() == true) {
            } else {
                showTransactionList();
            }
        }
    }
    if (menuId == 'reports') {
        if (reports_section.showing == true) {
            $('checkout').click();
        } else {
            reports_section.showArea();
            if (typeof holded_order_section == 'object' && holded_order_section.showing == true)
                holded_order_section.hideArea();
            if (typeof cashdrawer_section == 'object' && cashdrawer_section.showing == true)
                cashdrawer_section.hideArea();
            if (isRealOffline() == true) {
            } else {
                refreshReportSection();
            }
        }
    }
}

function showDetailHoldedOrder(el) {
    /*
     var detail = el.down('.detail');
     if (detail && detail.hasClassName('hide') == false) {
     detail.addClassName('hide');
     } else {
     detail.removeClassName('hide');
     }
     
     var items = $$('#holded_orders_section .item');
     if (items.length > 0) {
     items.each(function(other_item) {
     if (other_item != el) {
     other_item.down('.detail').addClassName('hide');
     }
     });
     }
     */
}

function checkShowDetailIn(el) {
    /*
     var detail = el.down('.detail');
     if (detail) {
     detail.removeClassName('hide');
     }
     var items = $$('#holded_orders_section .item');
     if (items.length > 0) {
     items.each(function(other_item) {
     if (other_item != el) {
     other_item.down('.detail').addClassName('hide');
     }
     });
     }
     */
}
function checkShowDetailOut(el) {
    /*
     var detail = el.down('.detail');
     if (detail) {
     detail.addClassName('hide');
     }
     */
}

function showBeforeHoldPopup() {
	var productElements = $$('#webpos_cart .needupdate');
	if (productElements.length > 0 && isOffline() == false) {
		localSet('hold_after_save_cart','true');
		saveCart();
	}else{
		if ($('before_hold_popup')) {
        $('before_hold_popup').removeClassName('hide');
        if ($('webpos_dark_overlay'))
            $('webpos_dark_overlay').show();
        $D('#before_hold_popup').animate({top: '20vh'});
        if ($('hold_order_descreption'))
            $('hold_order_descreption').focus();
		}
	}
}
function hideBeforeHoldPopup() {
    if ($('before_hold_popup')) {
        if ($('webpos_dark_overlay'))
            $('webpos_dark_overlay').hide();
        $D('#before_hold_popup').animate({top: '120vh'});
    }
}

function holdOrder() {
    $('hold_order_descreption_hidden').innerHTML = $('hold_order_descreption').value;
    var customer_name = customer_email = '';
    var customerid = 0;
    var items = [];
    if (typeof defaultCustomerConfig == 'object')
        customerid = defaultCustomerConfig.customer_id;
    var customerInCart = localGet('customerInCart');
    if (typeof customerInCart == 'object' && customerInCart != null && customerInCart != '') {
        var firstname = customerInCart.firstname;
        var lastname = customerInCart.lastname;
        var telephone = customerInCart.telephone;
        customer_email = customerInCart.email;
        customerid = customerInCart.customerid;
        customer_name = firstname + ' ' + lastname;
    }
    var shipping_form = $('webpos_shipping_method_form');
    var payment_form = $('webpos_payment_method_form');
    var shipping_method = $RF(shipping_form, 'shipping_method');
    var payment_method = $RF(payment_form, 'payment[method]');
    var productElements = $$('#webpos_cart .product');
    if (productElements.length > 0) {
        productElements.each(function (productEl) {
            var product_name = productEl.down('.product_name').innerHTML;
            var qty = parseFloat(productEl.down('.number').innerHTML);
            items.push(product_name + ' | Qty: ' + qty);
        });
    }
    if (isRealOffline() == true) {
        var order_data = {};
        order_data.items = JSON.stringify(items);
        order_data.order_id = '0';
        order_data.customerid = customerid;
        order_data.shipping_method = shipping_method;
        order_data.payment_method = payment_method;
        order_data.customer_name = customer_name;
        order_data.customer_email = customer_email;
        order_data.carthtml = $('webpos_cart').innerHTML;
        order_data.full_description = $D('#hold_order_descreption_hidden').text();
        order_data.short_description = $D('#hold_order_descreption_hidden').text().substring(0, 60);
        var hold_order_object = new webposHoldOrder();
        hold_order_object.add(order_data);
        if ($('hold_order_descreption'))
            $('hold_order_descreption').value = '';
        hideBeforeHoldPopup();
        showToastMessage('danger', 'Message', hold_order_success_message);
        if ($('customer_loader'))
            $('customer_loader').click()
        return;
    } else {
        if (hoding_order == false) {
            hoding_order = true;
            showBeforeHoldAjaxloader();
            var request = new Ajax.Request(hold_order_url, {
                method: 'get',
                parameters: {shipping_method: shipping_method, comment: $D('#hold_order_descreption_hidden').text()},
                onSuccess: function (transport) {
                    if (transport.status == 200) {
                        var response = JSON.parse(transport.responseText);
                        if (response.order_id) {
                            var order_data = {};
                            order_data.items = JSON.stringify(items);
                            order_data.order_id = response.order_id;
                            order_data.customerid = customerid;
                            order_data.shipping_method = shipping_method;
                            order_data.payment_method = payment_method;
                            order_data.customer_name = customer_name;
                            order_data.customer_email = customer_email;
                            order_data.carthtml = $('webpos_cart').innerHTML;
                            order_data.full_description = $D('#hold_order_descreption_hidden').text();
                            order_data.short_description = $D('#hold_order_descreption_hidden').text().substring(0, 60);
                            var hold_order_object = new webposHoldOrder();
                            hold_order_object.add(order_data);
                            if ($('hold_order_descreption'))
                                $('hold_order_descreption').value = '';
                            hideBeforeHoldPopup();
                            if (response.grandTotal) {
                                $('cashin_fullamount').innerHTML = response.grandTotal;
                                if ($('remain_value_label'))
                                    $('remain_value_label').innerHTML = response.grandTotal;
                                if ($('remain_value'))
                                    $('remain_value').innerHTML = response.grandTotal;
                            }
                            if (response.downgrandtotal)
                                $('round_down_cashin').innerHTML = response.downgrandtotal;
                            if (response.upgrandtotal)
                                $('round_up_cashin').innerHTML = response.upgrandtotal;
                            if (response.grand_total)
                                $('webpos_subtotal_button').innerHTML = response.grand_total;
                            if (response.payment_method && $('payment_method'))
                                $('payment_method').update(response.payment_method);
                            if (response.shipping_method && $('shipping_method'))
                                $('shipping_method').update(response.shipping_method);
                            if (response.pos_items && $('webpos_cart'))
                                $('webpos_cart').update(response.pos_items);
                            if (response.totals && $('pos_totals'))
                                $('pos_totals').update(response.totals);
                            if (response.number_item && $('total_number_item'))
                                $('total_number_item').update(response.number_item);
                            if (response.errorMessage && response.errorMessage != '') {
                                showToastMessage('danger', 'Error', response.errorMessage);
                            }
                        }
                    }
                    hoding_order = false;
                },
                onComplete: function () {
                    showToastMessage('success', 'Message', hold_order_success_message);
                    hideBeforeHoldAjaxloader();
                    hoding_order = false;
                    disableCheckout();
                    showCategory();
                    deleteCustomerJs();
                    hideHoldButton();
                    emptyCart(empty_cart_url);
                },
                onFailure: function () {
                    showToastMessage('danger', 'Message', hold_order_error_message);
                    hoding_order = false;
                }
            });
        }
    }
}
function previewHoldedOrder(order_id) {
    var hold_order_object = new webposHoldOrder();
    hold_order_object.previewByOrderId(order_id);
}
function deleteHoldedOrder(key) {
    var hold_order_object = new webposHoldOrder();
    hold_order_object.removeByKey(key);
    $('holded_' + key).remove();
}
function deleteHoldedOrderByOrderId(order_id) {
    var hold_order_object = new webposHoldOrder();
    hold_order_object.removeByOrderId(order_id);
}
function reloadOrder(key) {
    var hold_order_object = new webposHoldOrder();
    hold_order_object.reload(key);
}
function reloadOrderByOrderId(orderId) {
    var hold_order_object = new webposHoldOrder();
    hold_order_object.reloadByOrderId(orderId);
}
function hideHoldButton() {
    if ($D('#bt_hold_order')) {
        $D('#bt_hold_order').animate({bottom: '-100%'});
        if ($D('#bt_checkout'))
            $D('#bt_checkout').animate({width: '96%'});
        if ($D('#footer_right_overlay'))
            $D('#footer_right_overlay').css({width: '100%'});
    }
}
function showHoldButton() {
    if ($D('#bt_hold_order') && isRealOffline() == false) {
        $D('#bt_hold_order').animate({bottom: '0'});
        if ($D('#bt_checkout'))
            $D('#bt_checkout').animate({width: '66%'});
        if ($D('#footer_right_overlay'))
            $D('#footer_right_overlay').css({width: '70%'});
    }
}

function buttonHoldToUnhold(key, order_id) {
    if ($('bt_hold_order')) {
        $('bt_hold_order').innerHTML = unhold_label;
        if (key != '') {
            $('bt_hold_order').setAttribute('onclick', "deleteHoldedOrder('" + key + "')");
        } else {
            $('bt_hold_order').setAttribute('onclick', "deleteHoldedOrderByOrderId('" + order_id + "')");
        }
        $('bt_hold_order').addClassName('unhold');
    }
}
function buttonUnholdToHold() {
    if ($('bt_hold_order')) {
        $('bt_hold_order').innerHTML = hold_label;
        $('bt_hold_order').setAttribute('onclick', "showBeforeHoldPopup()");
        $('bt_hold_order').removeClassName('unhold');
    }
}

function reloadHoldedList() {
    if (reloadinng_holded_list == false) {
        reloadinng_holded_list = true;
        showHoldedListAjaxloader();
        var request = new Ajax.Request(get_holded_order_url, {
            method: 'get',
            parameters: {},
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response && $('holded_orders_list_grid')) {
                        $('holded_orders_list_grid').update(response);
                        if ($('holded_orders_list_grid'))
                            $('holded_orders_list_grid').scrollTop = 0;
                    }
                }
                reloadinng_holded_list = false;
            },
            onComplete: function () {
                hideHoldedListAjaxloader();
                reloadinng_holded_list = false;
                showCategory();
            },
            onFailure: function () {
                reloadinng_holded_list = false;
            }
        });
    }
}

function showHoldedListAjaxloader() {
    if ($('holded_orders_section_loader'))
        $('holded_orders_section_loader').style.display = 'block';
}
function hideHoldedListAjaxloader() {
    if ($('holded_orders_section_loader'))
        $('holded_orders_section_loader').style.display = 'none';
}

function selectTill(till_id, till_name) {
    var till_data = {till_id: till_id, till_name: till_name};
    localSet('webpos_till', till_data);
    if ($('till_info')) {
        $('till_info').innerHTML = till_name;
        $('till_info').removeClassName('hide');
    }
    if (saving_till == false && isRealOffline() == false) {
        saving_till = hasAnotherRequest = true;
        $('till_area').down('.webpos_popup_loader').style.display = 'block';
        var request = new Ajax.Request(saving_till_url, {
            method: 'get',
            parameters: {till_id: till_id},
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.errorMessage) {
                        showToastMessage('danger', 'Message', response.errorMessage);
                    } else {
                        hideTillSelectPopup();
                    }
                }
                saving_till = false;
            },
            onComplete: function () {
                $('till_area').down('.webpos_popup_loader').style.display = 'none';
                saving_till = hasAnotherRequest = false;
            },
            onFailure: function () {
                saving_till = hasAnotherRequest = false;
            }
        });
    }
}

function showTillSelectPopup() {
    if ($D('#till_area')) {
        $D('#till_area').animate({top: '10%'});
        if ($D('#webpos_fixed_overlay')) {
            $D('#webpos_fixed_overlay').css({display: 'block'});
        }
        var till_id = getCurrentTillId();
        if ($('till_' + till_id)) {
            var tills = $$('.till_item');
            if (tills.length > 0) {
                tills.each(function (el) {
                    el.removeClassName('selected-till');
                });
                $('till_' + till_id).addClassName('selected-till');
            }
        }
    }
}
function hideTillSelectPopup() {
    if ($D('#till_area')) {
        $D('#till_area').animate({top: '-100vh'});
        if ($D('#webpos_fixed_overlay')) {
            $D('#webpos_fixed_overlay').css({display: 'none'});
        }
    }
}

function showTransactionList() {
    if (isRealOffline() == true) {

    } else {
        showTransactionListAjaxloader();
        $D.ajax({
            url: get_transaction_grid_url,
            method: 'get',
            data: {till_id: getCurrentTillId()},
            success: function (response) {
                loadCurrentBalance();
                $D('#transaction_grid').html(response);
                hideTransactionListAjaxloader();
				resizeArea();
            }
        });
    }
}

function loadCurrentBalance() {
    if (isRealOffline() == true) {

    } else {
        showCurrentBalanceAjaxloader();
        $D.ajax({
            type: "GET",
            dataType: "json",
            data: {store_id: store_id, till_id: getCurrentTillId()},
            url: get_currentbalance_url
        }).done(function (data) {
            $D(".current_balance").text(data.msg);
            $D(".current_balance_fake").text(data.msg);
            hideCurrentBalanceAjaxloader();
        });
    }
}

function showMediumOverlay() {
    if ($('webpos_medium_overlay'))
        $('webpos_medium_overlay').style.display = 'block';
}
function hideMediumOverlay() {
    if ($('webpos_medium_overlay'))
        $('webpos_medium_overlay').style.display = 'none';
}
function showTransactionListAjaxloader() {
    if ($('cashdrawer_section_loader'))
        $('cashdrawer_section_loader').style.display = 'block';
}
function hideTransactionListAjaxloader() {
    if ($('cashdrawer_section_loader'))
        $('cashdrawer_section_loader').style.display = 'none';
}
function showCurrentBalanceAjaxloader() {
    if ($('current_balance_loader'))
        $('current_balance_loader').style.display = 'block';
}
function hideCurrentBalanceAjaxloader() {
    if ($('current_balance_loader'))
        $('current_balance_loader').style.display = 'none';
}
function showReportsAjaxloader() {
    if ($('reports_section_loader'))
        $('reports_section_loader').style.display = 'block';
}
function hideReportsAjaxloader() {
    if ($('reports_section_loader'))
        $('reports_section_loader').style.display = 'none';
}
function showBeforeHoldAjaxloader() {
    if ($('before_hold_popup_loader'))
        $('before_hold_popup_loader').style.display = 'block';
}
function hideBeforeHoldAjaxloader() {
    if ($('before_hold_popup_loader'))
        $('before_hold_popup_loader').style.display = 'none';
}
function showHoldedOrderDetailAjaxloader() {
    if ($('holded_orders_detail_loader'))
        $('holded_orders_detail_loader').style.display = 'block';
}
function hideHoldedOrderDetailAjaxloader() {
    if ($('holded_orders_detail_loader'))
        $('holded_orders_detail_loader').style.display = 'none';
}

function transactionInputAmountOnfocus() {
    $D('#transaction_note_wapper').show();
}
function transactionClearBox() {
    $D('#transaction_note').val('');
    $D('#transaction_amount').val('');
}
function transactionNoteAfterComplete() {
    $D('#transaction_note_wapper').hide();
    $D('#transaction_note').val('');
}
function transactionHideBox() {
    $D('#transaction_note_wapper').hide();
}

function newTransaction() {
    if (isRealOffline() == true) {

    } else {
        showTransactionListAjaxloader();
        $D.ajax({
            type: "GET",
            dataType: "json",
            url: new_transaction_url,
            data: {
                amount: $D("#transaction_amount").val(),
                type: $D("#transaction_type").val(),
                note: $D("#transaction_note").val(),
                till_id: getCurrentTillId()
            }
        }).done(function (data) {
            if (data.error) {
                showToastMessage('danger', 'Message', data.msg);
            } else {
                showToastMessage('success', 'Message', data.msg);
                showTransactionList();
            }
            $D('#transaction_amount').val('');
            transactionNoteAfterComplete();
            /*
             var convert_flag = $D('#set_transac_flag').val();
             if (convert_flag == 1) {
             var _url = set_transactionflag_url;
             $D.ajax({
             type: "GET",
             dataType: "json",
             url: _url,
             data: {
             store_id: store_id,
             till_id: getCurrentTillId()
             }
             }).done(function (data) {
             var transfer = $D('#transfer_val').val();
             $D("#transaction_amount").val(transfer);
             $D("#transaction_type").val('in');
             $D("#transaction_note").val('Cash Transfer');
             newTransaction();
             })
             .fail(function (data) {
             });
             $D('#set_transac_flag').val(0);
             }
             */
            hideTransactionListAjaxloader();
        })
                .fail(function (data) {
                    showToastMessage('danger', 'Message', transaction_not_saved_message);
                    hideTransactionListAjaxloader();
                });
    }
}

function getCurrentTillId() {
    var till_id = 0;
    if (enable_till == true) {
        var till_data = localGet('webpos_till');
        if (till_data != null) {
            return till_data.till_id;
        }
    }
    return till_id;
}

function loadReport(report_type) {
    if (report_type == '') {
        if ($('report_grid')) {
            $('report_grid').innerHTML = '';
        }
        if ($('reports_title'))
            $('reports_title').innerHTML = reports_label;
        return false;
    }
    var request_url = '';
    var report_name = reports_label;
    switch (report_type) {
        case 'x_report':
            request_url = x_report_url;
            report_name = x_report_name;
            break;
        case 'z_report':
            request_url = z_report_url;
            report_name = z_report_name;
            break;
        case 'eod_report':
            request_url = eod_report_url;
            report_name = eod_report_name;
            break;
    }

    if (request_url != '') {
        if (isRealOffline() == true) {

        } else {
            if ($('reports_title'))
                $('reports_title').innerHTML = report_name;
            showReportsAjaxloader();
            $D.ajax({
                url: request_url,
                method: 'get',
                data: {till_id: getCurrentTillId()},
                success: function (response) {
                    $D('#report_grid').html(response);
                    hideReportsAjaxloader();
                    if ($D('#btn_clear'))
                        $D('#btn_clear').click();
					resizeArea();
					if($D('#btn_clear')){
						$D('#btn_clear').click();
					}
                }
            });
        }
    }
}

function refreshReportSection() {
    if ($('report_type')) {
        $selected_report = $('report_type').value;
        if ($selected_report != '') {
            loadReport($selected_report);
        } else {
            if ($('report_grid')) {
                $('report_grid').innerHTML = '';
            }
            if ($('reports_title'))
                $('reports_title').innerHTML = reports_label;
        }
    }
}
function isInt(n) {
    return n % 1 === 0;
}
function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function calTotalManual(diff_value) {
    var sum = 0;
    if ($D('#other_payment').val() != "" && $D('#checkmo').val() != "" && $D('#cashpayment').val() != "") {
        var other_payment = parseFloat($D('#other_payment').val());
        var checkmo = parseFloat($D('#checkmo').val());
        var cash = parseFloat($D('#cashpayment').val());
        var ccsave = parseFloat($D('#ccsave').val());
        if (isNumber(other_payment))
            sum += other_payment;
        if (isNumber(checkmo))
            sum += checkmo;
        if (isNumber(cash))
            sum += cash;
        if (isNumber(ccsave))
            sum += ccsave;
        if (other_payment != "NaN" && checkmo != "NaN" && cash != "NaN" && ccsave != "NaN")
            $D('#total_value_report').html(sum.toFixed(2));
        var other_payment_diff = parseFloat($D('#other_payment_diff').text());
        var checkmo_diff = parseFloat($D('#checkmo_diff').text());
        var cashpayment_diff = parseFloat($D('#cashpayment_diff').text());
        var ccsave_diff = parseFloat($D('#ccsave_diff').text());
        var sum1 = 0;
        if (isNumber(other_payment_diff))
            sum1 += other_payment_diff;
        if (isNumber(checkmo_diff))
            sum1 += checkmo_diff;
        if (isNumber(cashpayment_diff))
            sum1 += cashpayment_diff;
        if (isNumber(ccsave_diff))
            sum1 += ccsave_diff;
        $D('#total_value_diff').html(sum1.toFixed(2));
    }

    $D('#total_value_diff').html(diff_value);
}

function cashCounted(el) {
    var class_element = el.getAttribute('class');
    var text = el.value;
    if (text != "") {
        var number = parseFloat(text);
        if (isNumber(number))
            el.value = number.toFixed(2);
        if (class_element.match('payment')) {
            var name = el.getAttribute('id');
            var id_sys = name + "_money_system";
            var manual_val = parseFloat(getPriceFromString($D('#' + name).val()));
            var system_val = parseFloat(getPriceFromString($D('#' + id_sys).text()));
            var diff_value = parseFloat(manual_val - system_val).toFixed(2);
            $D('#' + name + '_diff').html(diff_value);
            calTotalManual(diff_value);
        }
    }
}

function countTotalCash() {
    var total = 0;
    $D(".sum_value").each(function () {
        var this_value = parseFloat($D(this).val());
        if (isNumber(this_value)) {
            total += this_value;
        }
    });
    $D('#total_count').val(parseFloat(total).toFixed(2));
    $D('#btn_total').html('Total ' + parseFloat(total).toFixed(2))
}

function showCountCashArea() {
    if ($D('#count_cash_area')) {
        $D('#count_cash_area').animate({top: '10vh'});
        if ($D('#webpos_fixed_overlay')) {
            $D('#webpos_fixed_overlay').css({display: 'block'});
            $('webpos_fixed_overlay').setAttribute('onclick', 'hideCountCashArea()');
        }
    }
}

function hideCountCashArea() {
    if ($D('#count_cash_area')) {
        $D('#count_cash_area').animate({top: '-100vh'});
        if ($D('#webpos_fixed_overlay')) {
            $D('#webpos_fixed_overlay').css({display: 'none'});
            $('webpos_fixed_overlay').removeAttribute('onclick');
        }
    }
}

function saveZreport() {
    var checkInput = true;
    $D(".payment1").each(function () {
        var amount_count = $D(this).val();
        if (!isNumber(amount_count)) {
            alert('Enter Count Value !');
            checkInput = false;
            return false;
        }

    });
    if (checkInput) {
        var transfer = parseFloat($D('#cash_report').val()).toFixed(2);
        if (!isNumber(transfer)) {
            alert('Enter Amount Transfer !');
            return false;
        }
        if (transfer >= 0 && isNumber(transfer)) {
            var till_current_balance = parseFloat($D('#till_current_balance').val());
            if (!isNumber(till_current_balance)) {
                till_current_balance = 0;
            }
            $D("#transaction_amount").val(till_current_balance);
            $D("#transaction_type").val('out');
            $D("#transaction_note").val('Close Store');
            $D('#set_transac_flag').val(1);
            $D('#transfer_val').val(transfer);
        } else
            transfer = 0;

        var cashier_id = $D('#userid').val();
        var tax_amount = parseFloat(getPriceFromString($D('#tax_system').text()));
        var discount_amount = parseFloat(getPriceFromString($D('#discount_system').text()));
        var cash_system = parseFloat(getPriceFromString($D('#cashforpos_money_system').text()));
        var cash_count = parseFloat($D('#cashforpos').val());
        var cc_system = parseFloat(0);
        var cc_count = parseFloat(0);
        var other_system = parseFloat(0);
        var other_count = parseFloat(0);
        if ($('ccforpos_money_system')) {
            cc_system = parseFloat($D('#ccforpos_money_system').text());
        }
        showReportsAjaxloader();
        if (isRealOffline() == true) {

        } else {
            $D.ajax({
                type: "GET",
                dataType: "json",
                url: new_transaction_url,
                data: {
                    amount: $D("#transaction_amount").val(),
                    type: $D("#transaction_type").val(),
                    note: $D("#transaction_note").val(),
                    till_id: getCurrentTillId()
                }
            }).done(function (data) {
                var params = {
                    order_total: $D('#num_order_total').text(),
                    amount_total: getPriceFromString($D('#grand_system').text()),
                    till_id: getCurrentTillId(),
                    cashier_id: cashier_id,
                    transfer_amount: transfer,
                    tax_amount: tax_amount,
                    discount_amount: discount_amount,
                    cash_system: cash_system,
                    cash_count: cash_count,
                    cc_system: cc_system,
                    cc_count: cc_count,
                    other_system: other_system,
                    other_count: other_count
                };
                if (transfer > 0 && isNumber(transfer)) {
                    params['amount'] = transfer;
                    params['type'] = 'in';
                    params['note'] = 'Opening Balance in Drawer';
                }
                $D('#transaction_amount').val('');
                transactionNoteAfterComplete();
                $D.ajax({
                    type: "POST",
                    dataType: "json",
                    url: save_zreport_url,
                    data: params
                }).done(function (data) {
                    hideReportsAjaxloader();

                })
                        .fail(function (data) {
                            showToastMessage('danger', 'Message', report_not_saved_message);
                            hideReportsAjaxloader();
                        });
                var diff_total = parseFloat($D('#total_value_diff').text()).toFixed(2);
                zReportPrint(transfer, diff_total);
                $D('#checkout').trigger('click');
				if($D('#btn_clear')){
					$D('#btn_clear').click();
				}
            })
        }
    }
}

function showContainerArea(containerId) {
    if ($D('#' + containerId)) {
        var width = $D(window).width();
        var newWidth = width - 100;
        $D('#' + containerId).animate({left: '100px', width: newWidth + "px"});
    }
}
function hideContainerArea(containerId) {
    if ($D('#' + containerId)) {
        $D('#' + containerId).animate({left: '-100vw'});
    }
}

function resizeArea() {
    var productListHeight = $D(window).height() - 105;
    $D('#content').css({height: productListHeight + 'px'});
    var newwidth = $D(window).width();
    if ($$('.mainContainer').length > 0) {
        $$('.mainContainer').each(function (container) {
            var container_id = container.getAttribute('id');
            $D('#' + container_id).css({width: newwidth});
        });
    }
    if ($D('#main_container')) {
        var mainWidth = newwidth - 100;
        $D('#main_container').css({width: mainWidth});
    }
	if($D('#report_grid')){
		var newheight = $D(window).height() - 90;
		$D('#report_grid').css({maxHeight:newheight+'px'});
	}	
	if($D('#transaction_grid')){
		var newheight = $D(window).height() - 300;
		$D('#transaction_grid').css({maxHeight:newheight+'px'});
	}
}

function toogleTax() {
    showColrightAjaxloader();
    showCheckoutAjaxloader();
    var request = new Ajax.Request(toogle_tax_url, {
        method: 'get',
        parameters: {},
        onSuccess: function (transport) {
            if (transport.status == 200) {
                reloadAllBlock();
            }
        },
        onComplete: function () {

        },
        onFailure: function () {
            hideColrightAjaxloader();
            hideCheckoutAjaxloader();
        }
    });
}

function showHoldedOrdersDetail(orderId){
	if($D('#holded_orders_detail')){
		var increment_id = ''
		if($('orderlist_row_'+orderId)){
			increment_id = $('orderlist_row_'+orderId).getAttribute('increment_id');
			$D('#holded_order_grid .active').removeClass('active');
			$('orderlist_row_'+orderId).addClassName('active');
		}		
		if($D('#holded_orders_detail .order_id')) $D('#holded_orders_detail .order_id').html(increment_id);
		var newwidth = $D('#total-right').width() + 1;
        $D('#holded_orders_detail').css({width: newwidth});
		$D('#holded_orders_detail').animate({right:'0px'});
		if(isRealOffline() == true){
			
		}else{
			hasAnotherRequest = true;
			showHoldedOrderDetailAjaxloader();
			var request = new Ajax.Request(viewOrderUrl, {
				method: 'get',
				parameters: {order_id: orderId},
				onSuccess: function (transport) {
					if (transport.status == 200) {
						if ($('holded_orders_detail_content'))
							$('holded_orders_detail_content').innerHTML = transport.responseText;
					}
					hideHoldedOrderDetailAjaxloader();
					hasAnotherRequest = false;
				},
				onFailure: function (transport) {
					hideHoldedOrderDetailAjaxloader();
					hasAnotherRequest = false;
				}
			});
		}
	}
}

function hideHoldedOrdersDetail(){
	if($D('#holded_orders_detail')){
		var newwidth = $D('#total-right').width() + 1;
        $D('#holded_orders_detail').css({width: newwidth});
		$D('#holded_orders_detail').animate({right:'-100vw'});
	}
}

function addTempClass(el,classname){
	if(el){
		el.addClassName(classname);
	}
}
function removeTempClass(el,classname){
	if(el){
		el.removeClassName(classname);
	}
}