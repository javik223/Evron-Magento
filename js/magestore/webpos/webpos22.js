var priceFormat = '';
var currency_symbol = '';
var countDiscountAmount = 0;
var hisDiscountAmount = [""];
var groupSymbolNumber;
var decimalSymbolNumber;

var showPendingAjaxloader = function () {
    if ($('pending_orders_loader'))
        $('pending_orders_loader').style.display = 'block';
}

var hidePendingAjaxloader = function () {
    if ($('pending_orders_loader'))
        $('pending_orders_loader').style.display = 'none';
}

var showFirsttimeAjaxloader = function () {
    if ($('col_left_firsttime_loader'))
        $('col_left_firsttime_loader').style.display = 'block';
}

var hideFirsttimeAjaxloader = function () {
    if ($('col_left_firsttime_loader'))
        $('col_left_firsttime_loader').style.display = 'none';
}

var showAjaxloader = function () {
    if ($('bg_ajax_loader'))
        $('bg_ajax_loader').style.display = 'block';
}

var hideAjaxloader = function () {
    if ($('bg_ajax_loader'))
        $('bg_ajax_loader').style.display = 'none';
}

var showOrderlistAjaxloader = function () {
    if ($('order_list_loader'))
        $('order_list_loader').style.display = 'block';
}

var hideOrderlistAjaxloader = function () {
    if ($('order_list_loader'))
        $('order_list_loader').style.display = 'none';
}

var showOrderDetailAjaxloader = function () {
    if ($('order_detail_loader'))
        $('order_detail_loader').style.display = 'block';
}

var hideOrderDetailAjaxloader = function () {
    if ($('order_detail_loader'))
        $('order_detail_loader').style.display = 'none';
}

var showColrightAjaxloader = function () {
    if ($('col_right_loader'))
        $('col_right_loader').style.display = 'block';
}

var hideColrightAjaxloader = function () {
    if ($('col_right_loader'))
        $('col_right_loader').style.display = 'none';
}

var showCheckoutAjaxloader = function () {
    if ($('checkout_loader'))
        $('checkout_loader').style.display = 'block';
}

var hideCheckoutAjaxloader = function () {
    if ($('checkout_loader'))
        $('checkout_loader').style.display = 'none';
}

var showColleftAjaxloader = function () {
    if ($('col_left_loader'))
        $('col_left_loader').style.display = 'block';
}

var hideColleftAjaxloader = function () {
    if ($('col_left_loader'))
        $('col_left_loader').style.display = 'none';
}

var showSearchOrderForm = function () {
    if ($D('#orderlist_top_bar'))
        $D('#orderlist_top_bar').hide();
    if ($D('#orderlist-search-form'))
        $D('#orderlist-search-form').show();
    if ($('order-id'))
        $('order-id').focus();
}

var hideSearchOrderForm = function () {
    if ($D('#orderlist-search-form'))
        $D('#orderlist-search-form').hide();
    if ($D('#orderlist_top_bar'))
        $D('#orderlist_top_bar').show();
    orderlistSearch(false, '');
}



var showHideMenu = function (mainContainer) {
    if ($D('#menu').hasClass('menuHide')) {
        $D('#menu').removeClass('menuHide');
        if (mainContainer == 'orders')
            $D('#orders_area').removeClass('left0px');
        if (mainContainer == 'settings')
            $D('#settings_area').removeClass('left0px');
        $D('#main_container').addClass('marginLeft5vw');

    } else {
        //$D('#menu').addClass('menuHide');
        //if (mainContainer == 'orders')
        //$D('#orders_area').addClass('left0px');
        //if (mainContainer == 'settings')
        //$D('#settings_area').addClass('left0px');
        //$('main_container').removeClassName('marginLeft5vw');
    }
}

var hideCategory = function () {
    resizeArea();
    if ($D('#bt_remove_tax')) {
        $D('#bt_remove_tax').animate({top: '5px'});
    }
    $D('#customer_loader').addClass('hide');
    $D('#continue_select_product').removeClass('hide');
    if ($D('#bt_checkout')) {
        $D('#bt_checkout').animate({bottom: '-200px'});
    }
    if ($D('#main_container')) {
        $D('#main_container').addClass('hideCategory');
    }
    $D('#order_info').removeClass('orderHide');
    $D('#webpos_cart_area').addClass('opacity8');
    $D('#webpos_cart_overlay').removeClass('hide');
    var deleteBts = $$('#webpos_cart .product .delete');
    if (deleteBts.length > 0) {
        deleteBts.each(function (el) {
            el.addClassName('hide');
        });
    }
    var priceEls = $$('#webpos_cart .product .price');
    if (priceEls.length > 0) {
        priceEls.each(function (el) {
            el.addClassName('width40');
        });
    }
    if ($('add-discount'))
        $('add-discount').hide();
    if ($('grand_total_wp_show_hide'))
        $('grand_total_wp_show_hide').show();
    hideHoldButton();
}

var showCategory = function () {
    resizeArea();
    if ($D('#bt_remove_tax')) {
        $D('#bt_remove_tax').animate({top: '-100vh'});
    }
    $D('#customer_loader').removeClass('hide');
    $D('#continue_select_product').addClass('hide');
    $D('#main_container').removeClass('hideCategory');
    if ($D('#bt_checkout')) {
        $D('#bt_checkout').animate({bottom: '-0'});
    }
    $D('#order_info').addClass('orderHide');
    $D('#webpos_cart_area').removeClass('opacity8');
    $D('#webpos_cart_overlay').addClass('hide');
    var deleteBts = $$('#webpos_cart .product .delete');
    if (deleteBts.length > 0) {
        deleteBts.each(function (el) {
            el.removeClassName('hide');
        });
    }
    var priceEls = $$('#webpos_cart .product .price');
    if (priceEls.length > 0) {
        priceEls.each(function (el) {
            el.removeClassName('width40');
        });
    }
    if ($('add-discount'))
        $('add-discount').show();
    if ($('grand_total_wp_show_hide'))
        $('grand_total_wp_show_hide').hide();

    var productElements = $$('#webpos_cart .needupdate');
    if (productElements.length > 0 && isOffline() == false) {
        hideHoldButton();
    } else {
        if (isOffline() == false && $$('#webpos_cart .product').length > 0) {
            setTimeout(function () {
                showHoldButton();
            }, 500);
        }
    }
}

var showSearchForm = function () {
    if ($D('#product_search')) {
        $D('#product_search').fadeIn();
        $D('#product_search').removeClass('hide');
    }
    if ($D('#product_search_area')) {
        $D('#product_search_area').removeClass('width10percentage');
        $D('#product_search_area').css('width', '95%');
    }
    if ($D('#search_icon'))
        $D('#search_icon').addClass('hide');
    if ($D('#cancel_search_icon'))
        $D('#cancel_search_icon').removeClass('hide');
    if ($D('#category_dropdown')) {
        $D('#category_dropdown').removeClass('show');
        $D('#category_dropdown').addClass('hide');
    }
    if ($('product_search_keyword'))
        $('product_search_keyword').focus();
}

var hideSearchForm = function () {
    if ($D('#product_search'))
        $D('#product_search').hide();
    if ($D('#search_icon'))
        $D('#search_icon').removeClass('hide');
    if ($D('#product_search_area'))
        $D('#product_search_area').addClass('width10percentage');
    if ($D('#cancel_search_icon'))
        $D('#cancel_search_icon').addClass('hide');
    if ($D('#category_dropdown')) {
        $D('#category_dropdown').removeClass('hide');
        $D('#category_dropdown').addClass('show');
    }
    if ($D('#product_search_area'))
        $D('#product_search_area').css('width', '50%');

}
function showCustomersale() {
    var offline = isOffline();
    if (offline) {
        showToastMessage('danger', 'Error', 'This feature is not available for offline mode');
        return
    }
    if (!$D('#customer-sale-btn').hasClass('active')) {
        $D('#customer-sale-btn').addClass('active');
        if ($('add_to_cart_wp'))
            $('add_to_cart_wp').disabled = true;
        $D('#customer-sale-btn').hide();
        $D('#productPager').hide();
        $D('#numberProduct').hide();
        $D('#status').hide();
        $D('#offline').hide();
        if ($D('#till_info'))
            $D('#till_info').hide();
        $D('#cancel-sale-btn').show();
        $D('.customer-sale').fadeIn();
        $('custom_price').value = '';
        $('name_custom_sale').value = '';
        hisCustom = [""];
        count = 0;
    }
}
function cancelCustomersale() {
    if ($D('#customer-sale-btn').hasClass('active')) {
        $D('.customer-sale').fadeOut();
        $D('#customer-sale-btn').removeClass('active');
        $D('#cancel-sale-btn').hide();
        $D('#customer-sale-btn').show();
        $D('#productPager').show();
        $D('#numberProduct').show();
        $D('#status').show();
        $D('#offline').show();
        if ($D('#till_info'))
            $D('#till_info').show();
    }
}
function showDiscount() {
    var offline = isOffline();
    if (offline) {
        showToastMessage('danger', 'Error', 'This feature is not available for offline mode');
        return
    }
    if (!$D('#add-discount').hasClass('active')) {
        if ($$('#webpos_cart .needupdate').length == 0 && $$('#webpos_cart .product').length == 0) {
            showToastMessage('danger', 'Error', 'Please add product to cart to add cart discount');
        } else {
            $D('#add-discount').addClass('active');
            $D('#discount-webpos').fadeIn();
            $D('#custome-discount').click();
            if ($('current_discount').value.split('%').length == 2)
                $('btn-percentx').click();
            if ($('current_discount').value.split('%').length == 1)
                $('btn-dollorx').click();
            //        $D('#btn-dollorx').click();
            //        $('discount_name').value = '';
            //        $('apply_discount_wp').value = '';
            hisCustomCart = [""];
            countCart = 0;
            if ($('webpos_overlay'))
                $('webpos_overlay').show();
        }
    }
}
function cancelDiscount() {
    if ($D('#add-discount').hasClass('active')) {
        $D('#discount-webpos').fadeOut();
        $D('#add-discount').removeClass('active');
        if ($('webpos_overlay'))
            $('webpos_overlay').hide();
    }
}
function showCashIn() {
    if (!$D('#bt_cashin').hasClass('active')) {
        $D('#bt_cashin').addClass('active');
        $D('#webpos_payment_method_form').hide();
        $D('#form-cashin').show();
        $('cash_remain').addClassName('hide');
        $D('#shipping_area .panel-body').slideUp();
        $D('#shipping_area .panel-body').addClass('hidding');
        $D('#payment_area .panel-body').slideDown();
        $('bt_cashin').innerHTML = 'Payment methods';
    } else {
        $('cashin_value_label').innerHTML = $('cashin_value').value;
        var payment_form = $('webpos_payment_method_form');
        var payment_method = $RF(payment_form, 'payment[method]');
        if (payment_method == 'cashforpos') {
            $('remain_value_label').innerHTML = $('remain_value').innerHTML;
        } else {
            $('remain_value_label').innerHTML = 0;
        }
        if ($('cashin_value').value != '')
            $('cash_remain').removeClassName('hide');
        else
            $('cash_remain').addClassName('hide');
        $D('#form-cashin').hide();
        $D('#webpos_payment_method_form').show();
        $D('#bt_cashin').removeClass('active');
        $('bt_cashin').innerHTML = 'Amount Tendered';
    }
}
function selectDiscount() {
    if (!$D('#coupon-discount').hasClass('active')) {
        $D('.forms-discount').show();
        $D('#coupon-discount').addClass('active');
        $D('.form-coupon').hide();
        $D('.form-coupon').removeClass('active');
    }
}
function selectCoupon() {
    if (!$D('.form-coupon').hasClass('active')) {
        $D('.forms-discount').hide();
        $D('#coupon-discount').removeClass('active');
        $D('.form-coupon').addClass('active');
        $D('.form-coupon').show();
    }
}
function hidePopupCustomer() {
    $D('#create-customer').fadeOut();
    $D('#btn-create').removeClass('active');
    $D('.bg-create-customer').fadeOut();
}
var loadProductImage = function () {
    var imgs = $$('.product .item .img-product .product_image');
    imgs.each(function (img) {
        img.setAttribute('src', img.getAttribute('imgpath'));
    });
}

var addToCartByJs = function (productId, tax_amount, productPrice, imgPath, options, optionsValues, optionsLabels, editing) {
    var qty = 1;
    if (typeof productId === 'object') {
        var productIds = productId;
        for (var productId in productIds) {
            var qty = productIds[productId];
            productPrice = $('grouped_child_price_' + productId).value;
            imgPath = $('grouped_child_imgpath_' + productId).getAttribute('src');
            var oldOptionValues = '';
            if ($(productId + '_options_popup'))
                oldOptionValues = $(productId + '_options_popup').getAttribute('oldOptionValues');
            addOneProductToCartByJs(productId, tax_amount, qty, productPrice, imgPath, options, optionsValues, optionsLabels, editing, oldOptionValues);
            var custom_price = $D('.product[prdid=' + productId + ']').attr('custom_price');
            if (custom_price != '') {
                var product_price = parseFloat($D('.product[prdid=' + productId + ']').attr('product_price'));
                var product_qty = parseFloat($D('.product[prdid=' + productId + ']').find('.number').html());
                custom_price = parseFloat(custom_price);
                var price_edit = custom_price * product_qty;
                var regular_price = product_price * product_qty;
                $D('.product[prdid=' + productId + ']').find('.webpos_item_subtotal .price').html(formatCurrency(price_edit.toFixed(2), priceFormat));
                $D('.product[prdid=' + productId + ']').find('.webpos_item_original').html('Reg: ' + formatCurrency(regular_price.toFixed(2), priceFormat));
                collectTotalJS();
                collectGrandTotalJS();
            }
        }
    } else {
        if ($('prd_' + productId))
            qty = $('prd_' + productId).getAttribute('qtyinc');
        var oldOptionValues = '';
        if ($(productId + '_options_popup'))
            oldOptionValues = $(productId + '_options_popup').getAttribute('oldOptionValues');
        addOneProductToCartByJs(productId, tax_amount, qty, productPrice, imgPath, options, optionsValues, optionsLabels, editing, oldOptionValues);
        if ($D('#editpopup_product_qty').is(":visible")) {
            var productObject = getProductObject();
            var qtyProduct = $D('#editpopup_product_qty').val();
            productObject.find('.number').html(qtyProduct);
            productObject.attr('qty', qtyProduct);
            if (qtyProduct > 1) {
                productObject.find('.number').removeClass('hide');
                var newPrice = qtyProduct * productPrice;
                productObject.find('.webpos_item_subtotal .price').html(formatCurrency(newPrice, priceFormat));
                collectTotalJS();
                collectGrandTotalJS();
            }
            $D('#edit_cart_item_popup').fadeOut();
            calculatePriceJS();

        }
        var custom_price = $D('.product[prdid=' + productId + ']').attr('custom_price');
        if (custom_price != '') {
            var product_price = parseFloat($D('.product[prdid=' + productId + ']').attr('product_price'));
            var product_qty = parseFloat($D('.product[prdid=' + productId + ']').find('.number').html());
            custom_price = parseFloat(custom_price);
            var price_edit = custom_price * product_qty;
            var regular_price = product_price * product_qty;
            $D('.product[prdid=' + productId + ']').find('.webpos_item_subtotal .price').html(formatCurrency(price_edit.toFixed(2), priceFormat));
            $D('.product[prdid=' + productId + ']').find('.webpos_item_original').html('Reg: ' + formatCurrency(regular_price.toFixed(2), priceFormat));
            collectTotalJS();
            collectGrandTotalJS();
        }
    }
    if ($('product_search_keyword')) {
        $('product_search_keyword').value = '';
    }

}
var addOneProductToCartByJs = function (productId, tax_amount, qty, productPrice, imgPath, options, optionsValues, optionsLabels, editing, oldOptionValues) {
    var ornId = productId;
    var webpos_cart = $('webpos_cart');
    var newitem = prepareCartItem(productId, tax_amount, qty, productPrice, imgPath, options, optionsValues, optionsLabels, editing, oldOptionValues);
    var new_item_tax_amount = newitem.getAttribute('tax_amount');
    var rateByCustomer = getTaxRateByCustomer();
    var added = false;
    var itemid = '';
    var oldId = '';
    var oldPrice = newprice = old_item_tax_amount = 0;
    var productElements = $$('#webpos_cart .product');
    if (productElements.length > 0) {
        productElements.each(function (productEl) {
            var prd_id = productEl.getAttribute('prdid');
            var el_itemid = productEl.getAttribute('itemid');
            var el_id = productEl.getAttribute('id');
            var selected_option = productEl.getAttribute('selected_option');
            if (prd_id == productId || el_itemid == productId || ((oldOptionValues == selected_option) && selected_option != null && selected_option != '')) {
                if (optionsValues != '') {
                    if (((optionsValues == selected_option) && editing == '') || editing != '') {
                        if (el_id == 'cart_prd_' + productId + '_' + oldOptionValues || oldOptionValues == selected_option) {
                            added = true;
                            itemid = productEl.getAttribute('itemid');
                            if (editing != '') {
                                oldId = productEl.getAttribute('id');
                                old_item_tax_amount = productEl.getAttribute('tax_amount');
                                oldPrice = productEl.down('.price').down('.webpos_item_subtotal').down('.price').innerHTML;
                            }
                            return false;
                        }
                    }
                } else {
                    if (editing != '') {
                        oldId = productEl.getAttribute('id');
                        old_item_tax_amount = productEl.getAttribute('tax_amount');
                        oldPrice = productEl.down('.price').down('.webpos_item_subtotal').down('.price').innerHTML;
                    }
                    added = true;
                    itemid = productEl.getAttribute('itemid');
                    return false;
                }
            }
        });
    }
    var products = $$('.item');
    if (products.length > 0) {
        products.each(function (product) {
            product.removeClassName('activeitem');
        });
    }

    newitem.addClassName('needupdate');
    if (added == true) {
        if (itemid != '') {
            newitem.setAttribute('id', 'cart_prd_' + itemid);
            webpos_cart.replaceChild(newitem, $('cart_prd_' + itemid));
        } else {
            if (optionsValues != '' && editing == '') {
                productId = productId + '_' + optionsValues;
                webpos_cart.replaceChild(newitem, $('cart_prd_' + productId));
            } else if (editing != '')
                webpos_cart.replaceChild(newitem, $(oldId));
        }
    } else {
        webpos_cart.appendChild(newitem);
    }
    var items = $$('#webpos_cart .product');
    if (items.length > 0) {
        items.each(function (item) {
            item.removeClassName('active');
        });
    }
    tax_amount = parseFloat(productPrice) * parseFloat(rateByCustomer) / 100;
    if (isOffline() == false) {
        new_item_tax_amount = old_item_tax_amount = tax_amount = 0;
    } else {
        var online_totals = $$('.online_totals');
        if (online_totals.length > 0) {
            online_totals.each(function (total) {
                total.remove();
            });
        }
    }
    if (editing != '')
        $(ornId + '_options_popup').removeClassName('editing');
    var oldSubtotals = getStringPriceFromString($('webpos_cart_subtotals').down('.price').innerHTML);
    oldSubtotals = parseFloat(convertLongNumber(oldSubtotals));
    if (editing != '') {
        newPrice = newitem.down('.price').down('.webpos_item_subtotal').down('.price').innerHTML;
        var newSubtotals = oldSubtotals + getPriceFromString(newPrice) - getPriceFromString(oldPrice.toString());
    } else
        var newSubtotals = oldSubtotals + parseFloat(productPrice);
    $('webpos_cart_subtotals').innerHTML = getPriceFormatedHtml(parseFloat(newSubtotals));

    if (isOffline() == false) {
        var oldSubtotals = getStringPriceFromString($('webpos_subtotal_button').down('.price').innerHTML);
        oldSubtotals = parseFloat(convertLongNumber(oldSubtotals));
        var shipping_amount = 0;
    } else {
        updateTaxTotals(0, '');
        if ($('webpos_cart_tax') && price_includes_tax == false)
            tax_amount = convertLongNumber(getStringPriceFromString($('webpos_cart_tax').down('.price').innerHTML));
        else
            tax_amount = 0;
        var shipping_amount = getShippingPrice();
    }
    if (editing != '') {
        newPrice = newitem.down('.price').down('.webpos_subtotal_button').down('.price').innerHTML;
        var newSubtotals = oldSubtotals + parseFloat(new_item_tax_amount) - parseFloat(old_item_tax_amount) + getPriceFromString(newPrice) - getPriceFromString(oldPrice.toString());
    } else
        var newSubtotals = oldSubtotals + parseFloat(productPrice) + parseFloat(tax_amount) + shipping_amount;

    var discount = 0;
    if ($('all_discount_wp'))
        discount = parseFloat(convertLongNumber(getStringPriceFromString($('all_discount_wp').down('.price').innerHTML)));
    else
        discount = 0;
    if (discount != 0 && $('btn-dollorx') && $('btn-dollorx').hasClassName('btn2'))
        discount = 0;
    $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(newSubtotals) + discount);
    if (discount != 0 && $('btn-dollorx') && $('btn-percentx').hasClassName('btn2')) {
        discount = -parseFloat(convertLongNumber(getStringPriceFromString($('webpos_cart_subtotals').down('.price').innerHTML))) * parseFloat(convertLongNumber(getStringPriceFromString($('apply_discount_wp').value))) / 100;
        $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(convertLongNumber(getStringPriceFromString($('webpos_cart_subtotals').down('.price').innerHTML))) + discount);
        $('all_discount_wp').innerHTML = getPriceFormatedHtml(discount);
    }
    collectCartTotal();
    enableCheckout();
    if ($D('#cart_webpos_area'))
        $D('#cart_webpos_area').scrollTop($D('#cart_webpos_area')[0].scrollHeight);
    showHoldButton();
}
/*Mr Jack add product to cart*/
var addToCartByJsCp = function (productId, productPrice, imgPath, productName) {
    var webpos_cart = $('webpos_cart');
    newitem = prepareCartItemCp(productId, productPrice, imgPath, productName);
    webpos_cart.appendChild(newitem);
    var oldSubtotals = getStringPriceFromString($('webpos_cart_subtotals').down('.price').innerHTML);
    oldSubtotals = parseFloat(convertPrice(oldSubtotals));
    var newSubtotals = oldSubtotals + parseFloat(productPrice);
    $('webpos_cart_subtotals').innerHTML = getPriceFormatedHtml(parseFloat(newSubtotals));
    $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(newSubtotals));
    collectCartTotal();
    enableCheckout();
}
var prepareCartItemCp = function (productId, productPrice, imgPath, productName) {
    var newitem = true;
    var cart_item_sample = $('cart_item_sample');
    if (cart_item_sample)
        var item_sample = cart_item_sample.down('.product');
    var cartItem = item_sample.cloneNode(true);
    if ($('cart_prd_' + productId)) {
        cartItem = $('cart_prd_' + productId);
        newitem = false;
    }
    cartItem.setAttribute('prdid', productId);
    cartItem.setAttribute('id', 'cart_prd_' + productId);
    var itemChilds = cartItem.children;
    for (var j = 0; j < itemChilds.length; j++) {
        if (!itemChilds[j].hasClassName('delete'))
            itemChilds[j].setAttribute('onclick', "showEditPopupCp('" + productId + "','" + imgPath + "','" + productPrice + "')");
        if (itemChilds[j].hasClassName('img-product')) {
            itemChilds[j].down('img').setAttribute('src', imgPath);
            var oldQty = parseFloat(itemChilds[j].down('.number').innerHTML);
            var newqty = oldQty + 1;
            itemChilds[j].down('.number').innerHTML = newqty;
            if (newqty > 1)
                itemChilds[j].down('.number').removeClassName('hide');
            else
                itemChilds[j].down('.number').addClassName('hide');
        }
        if (itemChilds[j].hasClassName('name-product')) {
            itemChilds[j].innerHTML = productName;
        }
        if (itemChilds[j].hasClassName('price')) {
            if (newitem) {
                itemChilds[j].down('.webpos_item_subtotal').innerHTML = getPriceFormatedHtml(productPrice);
            } else {
                var oldPrice = getStringPriceFromString(itemChilds[j].down('.webpos_item_subtotal').down('.price').innerHTML);
                var newPrice = parseFloat(convertPrice(oldPrice)) + productPrice;
                itemChilds[j].down('.webpos_item_subtotal').innerHTML = getPriceFormatedHtml(newPrice);
            }
        }
        if (itemChilds[j].hasClassName('delete')) {
            itemChilds[j].setAttribute('onclick', "deleteProduct('" + productId + "')");
            //itemChilds[j].addClassName('hide');
        }
    }
    return cartItem;
}
/**/
var prepareCartItem = function (productId, tax_amount, qty, productPrice, imgPath, options, optionsValues, optionsLabels, editing, oldOptionValues) {
    productPrice = parseFloat(productPrice);
    var productName = '';
    if ($('prd_fullname_' + productId))
        productName = $('prd_fullname_' + productId).innerHTML;
    var newitem = true;
    var cart_item_sample = $('cart_item_sample');
    if (cart_item_sample)
        var item_sample = cart_item_sample.down('.product');
    var cartItem = item_sample.cloneNode(true);

    var itemid = '';
    var productElements = $$('#webpos_cart .product');
    if (productElements.length > 0) {
        productElements.each(function (productEl) {
            var prd_id = productEl.getAttribute('prdid');
            var el_itemid = productEl.getAttribute('itemid');
            var selected_option = productEl.getAttribute('selected_option');
            if (prd_id == productId || el_itemid == productId || ((oldOptionValues == selected_option) && selected_option != null && selected_option != '')) {
                if (optionsValues != '') {
                    if (((optionsValues == selected_option) && editing == '') || (editing != '' && oldOptionValues == selected_option)) {
                        newitem = false;
                        itemid = productEl.getAttribute('itemid');
                    }
                } else {
                    newitem = false;
                    itemid = productEl.getAttribute('itemid');
                }
            }
        });
    }

    if ($('prd_' + productId)) {
        qtyinc = $('prd_' + productId).getAttribute('qtyinc');
        cartItem.setAttribute('qtyinc', qtyinc);
    }
    cartItem.setAttribute('prdid', productId);
    if ($('cart_prd_' + productId)) {
        var selected_option = $('cart_prd_' + productId).getAttribute('selected_option');
        if (optionsValues == '') {
            cartItem = $('cart_prd_' + productId);
            newitem = false;
        } else {
            if (optionsValues == selected_option || editing != '') {
                cartItem = $('cart_prd_' + productId);
                newitem = false;
            } else
                newitem = true;
        }
    } else if ($('cart_prd_' + itemid)) {
        var selected_option = $('cart_prd_' + itemid).getAttribute('selected_option');
        if (optionsValues == '') {
            cartItem = $('cart_prd_' + itemid);
            newitem = false;
            productId = itemid;
        } else {
            if (optionsValues == selected_option || editing != '') {
                cartItem = $('cart_prd_' + itemid);
                newitem = false;
                productId = itemid;
            } else
                newitem = true;
        }
    } else if ($('cart_prd_' + productId + '_' + optionsValues)) {
        var selected_option = $('cart_prd_' + productId + '_' + optionsValues).getAttribute('selected_option');
        if (optionsValues == '') {
            cartItem = $('cart_prd_' + productId + '_' + optionsValues);
            newitem = false;
        } else {
            if (optionsValues == selected_option || editing != '') {
                cartItem = $('cart_prd_' + productId + '_' + optionsValues);
                newitem = false;
            } else
                newitem = true;
        }
    }
    cartItem.addClassName('prd_' + productId);
    if (optionsValues.indexOf('_index_') >= 0) {
        var index = optionsValues.substr(optionsValues.indexOf('_index_'), optionsValues.length).replace('_index_', '');
        cartItem.setAttribute('custom_option_index', 'prd_' + index);
    }
    cartItem.setAttribute('selected_option', optionsValues);
    cartItem.setAttribute('product_price', productPrice);
    cartItem.setAttribute('itemid', itemid);
    if (optionsValues != '' && itemid == '')
        productId = productId + '_' + optionsValues;
    cartItem.setAttribute('id', 'cart_prd_' + productId);
    if (options != '')
        cartItem.setAttribute('options', options);
    var itemChilds = cartItem.children;
    for (var j = 0; j < itemChilds.length; j++) {
        if (!itemChilds[j].hasClassName('delete'))
            itemChilds[j].setAttribute('onclick', "showEditPopup('" + productId + "','" + imgPath + "','" + productPrice + "')");
        if (itemChilds[j].hasClassName('img-product')) {
            itemChilds[j].down('img').setAttribute('src', imgPath);
            var oldQty = parseFloat(itemChilds[j].down('.number').innerHTML);
            if (editing != '' && editing == 'yes')
                var newqty = oldQty;
            else {
                var newqty = oldQty + parseFloat(qty);
            }
            /* 20150904 - Decimal Qty */
            itemChilds[j].down('.number').innerHTML = parseFloat(newqty);
            /* 20150904 - Decimal Qty */
            if (newqty > 1)
                itemChilds[j].down('.number').removeClassName('hide');
            else
                itemChilds[j].down('.number').addClassName('hide');
            cartItem.setAttribute('qty', newqty);
            if (cartItem.hasClassName('hide')) {
                cartItem.removeClassName('hide');
            }
        }
        if (itemChilds[j].hasClassName('name-product')) {
            if (optionsLabels != '')
                itemChilds[j].down('.product_name').addClassName('hasoptions');
            itemChilds[j].down('.product_name').innerHTML = productName;
            itemChilds[j].down('.product_options').innerHTML = optionsLabels;

        }
        if (itemChilds[j].hasClassName('price')) {
            var rateByCustomer = getTaxRateByCustomer();
            if (newitem || editing != '') {
                var newPrice = productPrice;
                itemChilds[j].down('.webpos_item_subtotal').innerHTML = getPriceFormatedHtml(productPrice * qty);
            } else {
                var oldPrice = getStringPriceFromString(itemChilds[j].down('.webpos_item_subtotal').down('.price').innerHTML);
                var newPrice = parseFloat(convertPrice(oldPrice)) + productPrice * qty;
                itemChilds[j].down('.webpos_item_subtotal').innerHTML = getPriceFormatedHtml(newPrice);
            }
            var item_tax_amount = parseFloat(newPrice) * parseFloat(rateByCustomer) / 100;
            var tax_amount = parseFloat(productPrice) * parseFloat(rateByCustomer) / 100;
            if (isOffline() == true) {
                updateTaxTotals(tax_amount, 'inc');
            }
            cartItem.setAttribute('tax_amount', item_tax_amount);
        }
        if (itemChilds[j].hasClassName('delete')) {
            itemChilds[j].setAttribute('onclick', "deleteProduct('" + productId + "')");
        }
    }
    return cartItem;
}


var prepareEditPopup = function (productId, imgPath, productPrice, itemId) {
    var productName = '';
    var cartItem = $('cart_prd_' + productId);
    if (!cartItem)
        cartItem = $('cart_prd_' + itemId);
    var prdid = cartItem.getAttribute('prdid');

    productName = cartItem.down('.name-product').down('.product_name').innerHTML;
    $('edit_cart_item_popup').setAttribute('prdid', prdid);
    $('edit_cart_item_popup').setAttribute('itemid', itemId);
    $('edit_cart_item_popup').setAttribute('product_price', productPrice);
    $('edit_cart_item_popup').setAttribute('has_change', 'no');
    $('edit_cart_item_popup').setAttribute('cart_item', 'cart_prd_' + productId);
    var discountAmount;
    var reduceType;
    var discountType;


    if (typeof itemId == 'undefined')
        itemId = cartItem.getAttribute('itemid');
    if (itemId > 0) {
        if ($D.jStorage.get('customInfo') != null) {
            var customInfo = JSON.parse($D.jStorage.get('customInfo'));
            if (typeof customInfo[itemId] != 'undefined') {
                var cartItemInfo = customInfo[itemId];

                discountAmount = cartItemInfo.discountAmount;
                reduceType = cartItemInfo.reduceType;
                discountType = cartItemInfo.editPrice;
            }
        }
    } else {
        discountAmount = cartItem.getAttribute('discount-amount');
        discountType = cartItem.getAttribute('discount-type');
        reduceType = cartItem.getAttribute('reduce-type');
    }
    //vietdq
    var currency = $D('#edit_cart_item_popup #btn-dollor').html();
    if (discountType != null) {
        if (discountType == 'discount') {

            $D('#edit_cart_item_popup').find('#btn-discount').removeClass('nochoose');
            $D('#edit_cart_item_popup').find('#btn-discount').addClass('choose');
            $D('#edit_cart_item_popup').find('#btn-custom').removeClass('choose');
            $D('#edit_cart_item_popup').find('#btn-custom').addClass('nochoose');
            $D('.label-type').each(function () {
                $D(this).html('Discount');
            });
        } else {

            $D('#edit_cart_item_popup').find('#btn-custom').removeClass('nochoose');
            $D('#edit_cart_item_popup').find('#btn-custom').addClass('choose');
            $D('#edit_cart_item_popup').find('#btn-discount').removeClass('choose');
            $D('#edit_cart_item_popup').find('#btn-discount').addClass('nochoose');
            $D('.label-type').each(function () {
                $D(this).html('Custom Price');
            });
        }
    }
    if (discountAmount != null) {
        if (reduceType == 'dollar') {
            $D('#edit_cart_item_popup .webpos_item_subtotal').html(formatCurrency((parseFloat(discountAmount)), priceFormat));
            $D('#edit_cart_item_popup #discount-amount').val(formatCurrency((parseFloat(discountAmount)), priceFormat));
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn2');

            $D('#edit_cart_item_popup').find('#btn-percent').addClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').addClass('btn2');

        } else {
            var number = formatCurrency((parseFloat(discountAmount)), priceFormat);
            $D('#edit_cart_item_popup .webpos_item_subtotal').html(getStringPriceFromString(number) + '%');
            $D('#edit_cart_item_popup #discount-amount').val(getStringPriceFromString(number) + '%');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn2');

            $D('#edit_cart_item_popup').find('#btn-percent').addClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-dollor').addClass('btn1');
        }
    }

    //end
    if ($D('#mode_status').hasClass('nowonline')) {
        $D('.custom_price').removeClass('hide');
        $D('.edit-price').removeClass('hide');
    } else {
        $D('.custom_price').addClass('hide');
        $D('.edit-price').addClass('hide');
    }
    $('editpopup_product_name').innerHTML = productName;
    $('editpopup_product_image').setAttribute('src', imgPath);

    var itemChilds = cartItem.children;
    for (var j = 0; j < itemChilds.length; j++) {
        if (itemChilds[j].hasClassName('img-product')) {
            var qty = parseFloat(itemChilds[j].down('.number').innerHTML);
            $('editpopup_product_qty').value = qty;
            break;
        }
    }
    var hasLocalOption = false;
    var bundleOptionCheck1 = getBundleOption(prdid);
    var bundleOptionCheck2 = getBundleOption(itemId);
    var customOptionCheck1 = getCustomOption(prdid);
    var customOptionCheck2 = getCustomOption(itemId);
    if (bundleOptionCheck1 != null || bundleOptionCheck2 != null || customOptionCheck1 != null || customOptionCheck2 != null) {
        hasLocalOption = true;
    }
    var selected_option = cartItem.getAttribute('selected_option');
    if (selected_option != '' && selected_option != '0' || hasLocalOption == true) {
        $('edit_cart_item_popup').down('.edit-option').removeClassName('hide');
    } else {
        $('edit_cart_item_popup').down('.edit-option').addClassName('hide');
    }
    if (itemId == null || itemId == '')
        $('edit_cart_item_popup').down('.edit-option').setAttribute('onclick', "showEditOptionsTab('" + productId + "')");
    else
        $('edit_cart_item_popup').down('.edit-option').setAttribute('onclick', "showEditOptionsTab('" + itemId + "')");
}
/*Mr Jack*/
var prepareEditPopupCp = function (productId, imgPath, productPrice) {
    var productName = '';
    if ($('prd_name_' + productId))
        productName = $('prd_name_' + productId).innerHTML;
    $('edit_cart_item_popup').setAttribute('itemid', productId);
    $('edit_cart_item_popup').setAttribute('product_price', productPrice);
    $('editpopup_product_name').innerHTML = productName;
    $('editpopup_product_image').setAttribute('src', imgPath);
    var itemChilds = $('cart_prd_' + productId).children;
    for (var j = 0; j < itemChilds.length; j++) {
        if (itemChilds[j].hasClassName('img-product')) {
            var qty = parseFloat(itemChilds[j].down('.number').innerHTML);
            $('editpopup_product_qty').value = qty;
            break;
        }
    }
    var itemId = $('cart_prd_' + productId).getAttribute('itemid');
    var discountAmount;
    var reduceType;
    var discountType;
    if (itemId > 0) {
        if ($D.jStorage.get('customInfo') != null) {
            var customInfo = JSON.parse($D.jStorage.get('customInfo'));



            if (typeof customInfo[itemId] != 'undefined') {
                var cartItemInfo = customInfo[itemId];


                discountAmount = cartItemInfo.discountAmount;
                reduceType = cartItemInfo.reduceType;
                discountType = cartItemInfo.editPrice;
            }

        }
    } else {
        discountAmount = cartItem.getAttribute('discount-amount');
        discountType = cartItem.getAttribute('discount-type');
        reduceType = cartItem.getAttribute('reduce-type');
    }
    var currency = $D('#edit_cart_item_popup #btn-dollor').html();
    if (discountType != null) {
        if (discountType == 'discount') {
            $D('#edit_cart_item_popup').find('#btn-discount').removeClass('nochoose');
            $D('#edit_cart_item_popup').find('#btn-discount').addClass('choose');
            $D('#edit_cart_item_popup').find('#btn-custom').removeClass('choose');
            $D('#edit_cart_item_popup').find('#btn-custom').addClass('nochoose');
            $D('.label-type').each(function () {
                $D(this).html('Discount');
            });
        } else {

            $D('#edit_cart_item_popup').find('#btn-custom').removeClass('nochoose');
            $D('#edit_cart_item_popup').find('#btn-custom').addClass('choose');
            $D('#edit_cart_item_popup').find('#btn-discount').removeClass('choose');
            $D('#edit_cart_item_popup').find('#btn-discount').addClass('nochoose');
            $D('.label-type').each(function () {
                $D(this).html('Custom Price');
            });
        }
    }
    if (discountAmount != null) {
        if (reduceType == 'dollar') {
            $D('#edit_cart_item_popup .webpos_item_subtotal').html(formatCurrency((parseFloat(discountAmount)), priceFormat));
            $D('#edit_cart_item_popup #discount-amount').val(formatCurrency((parseFloat(discountAmount)), priceFormat));
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn2');

            $D('#edit_cart_item_popup').find('#btn-percent').addClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').addClass('btn2');

        } else {
            var number = formatCurrency((parseFloat(discountAmount)), priceFormat);
            $D('#edit_cart_item_popup .webpos_item_subtotal').html(getStringPriceFromString(number) + '%');
            $D('#edit_cart_item_popup #discount-amount').val(getStringPriceFromString(number) + '%');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-dollor').removeClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn1');
            $D('#edit_cart_item_popup').find('#btn-percent').removeClass('btn2');

            $D('#edit_cart_item_popup').find('#btn-percent').addClass('btn2');
            $D('#edit_cart_item_popup').find('#btn-dollor').addClass('btn1');
        }
    }

    //end
}
/**/

var overlayClicked = function (productId) {
    var isEdit = false;
    var productOptions = $$('.product_options');
    if (productOptions.length > 0)
        productOptions.each(function (el) {
            $D('#' + el.id).fadeOut();
            if (el.hasClassName('editing'))
                isEdit = false;
            el.removeClassName('editing');
        });
    var products = $$('.item');
    if (products.length > 0) {
        products.each(function (product) {
            product.removeClassName('activeitem');
        });
    }
    if ($('webpos_overlay') && isEdit == false) {
        $('webpos_overlay').hide();
    }
    if ($D('#edit_cart_item_popup') && isEdit == false) {
        $D('#edit_cart_item_popup').fadeOut();
        var items = $$('#webpos_cart .product');
        if (items.length > 0) {
            items.each(function (item) {
                item.removeClassName('active');
            });
        }
    }

    if ($D('#discount-webpos'))
        $D('#discount-webpos').fadeOut();
    if ($D('#add-discount'))
        $D('#add-discount').removeClass('active');
    if ($D('#order-comment')) {
        $D('#order-comment').removeClass('active');
        $D('#order-comment').attr('style', 'display:none');
    }
    if ($D('#edit_cart_item_popup').css('display') == 'block') {
        var productObject = getProductObject();
        var oldQty = parseFloat(productObject.find(".number").html());
        var newQty = parseFloat($D('#editpopup_product_qty').val());
        if (newQty != oldQty) {
            $('edit_cart_item_popup').setAttribute('has_change', 'yes');
        }
        if ($('webpos_overlay') && isEdit == false)
            $('webpos_overlay').hide();


        if ($('edit_cart_item_popup').getAttribute('has_change') == 'yes') {
            $('edit_cart_item_popup').setAttribute('has_change', 'no');
            applyDiscount();
        }
        resetPopup();
    }
    hideBgOverlay();
    hidePendingOrdersPopup();
    if ($('categories_list')) {
        $('categories_list').addClassName('hide');
        $('categories_list').removeClassName('show');
    }
    hideBeforeHoldPopup();
    $D('#category_area button').removeClass('active');
}

var showEditPopup = function (productId, imgPath, productPrice, itemId) {
    var positon;
    var cartItem = $D('#cart_prd_' + productId);
    if (itemId)
        cartItem = $D('#cart_prd_' + itemId);
    cartItem.addClass('active');
    positon = cartItem.offset();
    if ($('webpos_overlay'))
        $('webpos_overlay').show();
    if ($D('#edit_cart_item_popup')) {
        prepareEditPopup(productId, imgPath, productPrice, itemId);
        var windowHeight = $D(window).height();
        var topPos = ((positon.top - 70) <= (windowHeight - 430)) ? (positon.top - 70) : (windowHeight - 430);
        if (positon.top)
            $D('#edit_cart_item_popup').css({top: (topPos)});
        if (positon.left) {
            $D('#edit_cart_item_popup').css({left: (positon.left - 139)});
        }
        $D('#edit_cart_item_popup').show();
    }
}
/*Mr Jack*/
var showEditPopupCp = function (productId, imgPath, productPrice) {
    var positon;
    if ($D('#cart_prd_' + productId))
        positon = $D('#cart_prd_' + productId).offset();
    $D('#cart_prd_' + productId).addClass('active');
    if ($('webpos_overlay'))
        $('webpos_overlay').show();
    if ($D('#edit_cart_item_popup')) {
        prepareEditPopupCp(productId, imgPath, productPrice);
        var windowHeight = $D(window).height();
        var topPos = ((positon.top - 100) <= (windowHeight - 340)) ? (positon.top - 100) : (windowHeight - 360);
        if (positon.top)
            $D('#edit_cart_item_popup').css({top: (topPos)});
        if (positon.left)
            $D('#edit_cart_item_popup').css({left: (positon.left - 145)});
        $D('#edit_cart_item_popup').fadeIn();
    }
}
/**/

var showCustomPriceTab = function () {
    if ($D('#edit_cart_item_popup .custom_price_tab'))
        $D('#edit_cart_item_popup .custom_price_tab').animate({right: '0%'}, 500);
    customHeight = $D('#edit_cart_item_popup').find('.custom_price_tab').height();
    $D('#edit_cart_item_popup').find('.edit_cart_item_popup').height(customHeight);
};

var hideCustomPriceTab = function () {
    if ($D('#edit_cart_item_popup .custom_price_tab'))
        $D('#edit_cart_item_popup .custom_price_tab').animate({right: '-100%'}, 500);
    var height = $D('#edit_cart_item_popup').find('.custom_price_tab').height();
    $D('#edit_cart_item_popup').find('.edit_cart_item_popup').css('height', 'auto');
};

var showEditOptionsTab = function (productId) {
    if ($('webpos_overlay'))
        $('webpos_overlay').show();
    var prdid = productId;
    var selected_option = '';
    if ($('cart_prd_' + productId)) {
        prdid = $('cart_prd_' + productId).getAttribute('prdid');
        selected_option = $('cart_prd_' + productId).getAttribute('selected_option');
    }
    var optionPopup = $D('#' + productId + '_options_popup');
    if (!$(productId + '_options_popup')) {
        prdid = $('cart_prd_' + productId).getAttribute('prdid');
        optionPopup = $D('#' + prdid + '_options_popup');
    }
    optionPopup.attr('oldOptionValues', selected_option);
    var options = getConfigurableOptions(prdid + '_' + selected_option);
    if (options != '') {
        for (var code in options) {
            var optionValue = options[code].value;
            var optionLabel = options[code].label;
            selectProductOption(prdid, code, optionValue, optionLabel);
        }
    }
    fillCustomData(prdid);
    optionPopup.addClass('editing');
    optionPopup.removeClass('hide');
    optionPopup.fadeIn();
    //if($D('#edit_cart_item_popup #edit_options_tab')) $D('#edit_cart_item_popup #edit_options_tab').animate({right:'0%'},500);
}

var hideEditOptionsTab = function (productId) {
    if ($('webpos_overlay'))
        $('webpos_overlay').show();
    var optionPopup = $D('#' + productId + '_options_popup');
    if (!$(productId + '_options_popup')) {
        var prdid = $('cart_prd_' + productId).getAttribute('prdid');
        optionPopup = $D('#' + prdid + '_options_popup');
    }
    optionPopup.fadeOut();
    optionPopup.addClass('hide');
    //if($D('#edit_cart_item_popup #edit_options_tab')) $D('#edit_cart_item_popup #edit_options_tab').animate({right:'-100%'},500);
}

var deleteProductByJs = function (productId) {
    var productPrice = tax_amount = 0;
    var qty = 1;
    if ($('cart_prd_' + productId)) {
        var productPriceString = $('cart_prd_' + productId).down('.price').down('.webpos_item_subtotal').down('.price').innerHTML;
        productPrice = getStringPriceFromString(productPriceString);
        tax_amount = $('cart_prd_' + productId).getAttribute('tax_amount');
        qty = $('cart_prd_' + productId).down('.img-product').down('.number').innerHTML;
        $('cart_prd_' + productId).remove();
    }
    var items = $$('#webpos_cart .product');
    if (items.length > 0) {
        items.each(function (el) {
            if (el.getAttribute('prdid') == productId) {
                if (el.getAttribute('itemid') == '') {
                    tax_amount = el.getAttribute('tax_amount');
                    qty = el.down('.img-product').down('.number').innerHTML;
                    el.remove();
                }
            }
        });
    }
    if (isOffline() == true) {
        if (tax_amount == null || tax_amount == '' || typeof tax_amount == 'undefined')
            tax_amount = 0;
        var tax_amount = parseFloat(tax_amount);
        updateTaxTotals(tax_amount, 'desc');
    } else {
        tax_amount = 0;
    }
    var items = $$('#webpos_cart .product');
    if (items.length > 0) {
        var oldSubtotals = getStringPriceFromString($('webpos_cart_subtotals').down('.price').innerHTML);
        oldSubtotals = parseFloat(convertPrice(oldSubtotals));
        var oldGrandotals = getStringPriceFromString($('webpos_subtotal_button').down('.price').innerHTML);
        oldGrandotals = parseFloat(convertPrice(oldGrandotals));
        var newSubtotals = oldSubtotals - parseFloat(convertPrice(productPrice));
        var newGrandotals = oldGrandotals - parseFloat(convertPrice(productPrice)) - tax_amount;
        $('webpos_cart_subtotals').innerHTML = getPriceFormatedHtml(parseFloat(newSubtotals));
        $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(newGrandotals));
    } else {
        $('webpos_cart_subtotals').innerHTML = getPriceFormatedHtml(parseFloat(0));
        $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(0));
        if ($('offline_tax'))
            $('offline_tax').remove();
        if ($('offline_shipping'))
            $('offline_shipping').remove();
    }

    collectCartTotal();

    var productElements = $$('#webpos_cart .product');
    if (productElements.length == 0) {
        hideHoldButton();
        showCategory();
        disableCheckout();
    }
}

var webpos_save_data = function (save_data_url, type) {

    var shipping_form = $('webpos_shipping_method_form');
    var payment_form = $('webpos_payment_method_form');
    var shipping_method = $RF(shipping_form, 'shipping_method');
    var payment_method = $RF(payment_form, 'payment[method]');
    /*
     if (payment_method == 'cashforpos' && type == 'payment')
     showCashIn();
     */
    var parameters = {
        shipping_method: shipping_method,
        payment_method: payment_method
    };

    if ($('cashin_value') && $('cashin_value').value != '')
        parameters['cashin'] = convertLongNumber(getStringPriceFromString($('cashin_value').value));
    if ($D('#shipping_area .panel-body') && type == 'payment') {
        $D('#shipping_area .panel-body').slideUp();
        $D('#shipping_area .panel-body').addClass('hidding');
    }

    if (type == 'payment') {
        var payment_method_dts = $$('#checkout-payment-method-load dt');
        if (payment_method_dts.length > 0) {
            payment_method_dts.each(function (dt) {
                if (dt.getAttribute('code') == payment_method)
                    dt.addClassName('active');
                else
                    dt.removeClassName('active');
            });
        }
    }

    var paymentSelectedIcons = $$('.paymentSelectedIcon');
    if (paymentSelectedIcons.length > 0) {
        paymentSelectedIcons.each(function (icon) {
            if (icon.id == payment_method + '_selected_icon' || ($('cashin_value') && $('cashin_value').value != '' && icon.id == 'cashforpos_selected_icon'))
                icon.removeClassName('hide');
            else
                icon.addClassName('hide');
        });
    }

    if ($$('.need_pay_amount').length > 0) {
        $$('.need_pay_amount').each(function (el) {
            if ((payment_method == 'cashforpos' && $D('#cashin_fullamount .price') && el.id == 'cashforpos_need_pay_amount')){
                el.innerHTML = $D('#cashin_fullamount .price').html();
            } else if (el.id == payment_method + '_need_pay_amount') {
                if ($('cashforpos_need_pay_amount') && $('cashin_value_label').innerHTML != '' && $('cashin_value_label').innerHTML != '0') {
                    $('cashforpos_need_pay_amount').innerHTML = $('cashin_value_label').innerHTML;
                }
                if(payment_method != 'cashforpos' && getRemainValue() < 0){
                    $('delete_cashin').click();
                    $D('#bt_cashin').addClass('active')
                    showCashIn();
                }
                el.innerHTML = (getRemainValue > 0)?$D('#remain_value .price').html():getPriceFormatedNoHtml(0);
                $D('#remain_value_label .price').html(0);
                $D('#remain_value_label .price').attr('paid', el.innerHTML);
            } else {
                if (el.id == 'cashforpos_need_pay_amount' && convertLongNumber(getStringPriceFromString($('cashin_value').value)) != 0) {
                    el.innerHTML = getPriceFormatedNoHtml(convertLongNumber(getStringPriceFromString($('cashin_value').value)));
                } else {
                    el.innerHTML = "";
                }
            }
        });
    }
    var shippingSelectedIcons = $$('.shippingSelectedIcon');
    if (shippingSelectedIcons.length > 0) {
        shippingSelectedIcons.each(function (icon) {
            if (icon.id == shipping_method + '_selected_icon')
                icon.removeClassName('hide');
            else
                icon.addClassName('hide');
        });
    }
    canPlaceOrder();
    if (isOffline() == true) {
        return;
    }

    var items = $$('#payment_form_' + payment_method + ' input[name^=payment]', '#payment_form_' + payment_method + ' select[name^=payment]');
    if (items.length > 0)
        items.each(function (el) {
            if (el.getAttribute('type') == 'radio' || el.getAttribute('type') == 'checkbox') {
                if (el.checked == true)
                    parameters[el.name] = el.value;
            } else
                parameters[el.name] = el.value;
        });

    hasAnotherRequest = true;
    showColrightAjaxloader();
    var request = new Ajax.Request(save_data_url, {
        method: 'post',
        parameters: parameters,
        onFailure: function () {
            hideColrightAjaxloader();
            hasAnotherRequest = false;
        },
        onSuccess: function (transport) {
            if (transport.status == 200) {
                var response = JSON.parse(transport.responseText);
                if (response.errorMessage && response.errorMessage != '') {
                    /*
                     if (type == 'payment')
                     showToastMessage('danger', 'Error', response.errorMessage);
                     */
                } else {
                    if (response.payment_method && type != 'payment') {
                        $('payment_method').update(response.payment_method);
                        if ($RF(payment_form, 'payment[method]') != null) {
                            try {
                                var payment_method = $RF(payment_form, 'payment[method]');
                                $('container_payment_method_' + payment_method).show();
                                $('payment_form_' + payment_method).show();
                            } catch (err) {

                            }
                        }
                    }

                }
                if (response.shipping_method && $('shipping_method'))
                    $('shipping_method').update(response.shipping_method);
                if (response.totals && $('pos_totals'))
                    $('pos_totals').update(response.totals);
                if (response.grandTotals && $('webpos_subtotal_button')) {
                    $('webpos_subtotal_button').update(response.grandTotals);
                    if ($('cashin_fullamount'))
                        $('cashin_fullamount').innerHTML = response.grandTotals;
                    if ($('remain_value_label'))
                        $('remain_value_label').innerHTML = response.grandTotals;
                    if ($('remain_value'))
                        $('remain_value').innerHTML = response.grandTotals;
                }
                if (response.downgrandtotal && $('round_down_cashin'))
                    $('round_down_cashin').innerHTML = response.downgrandtotal;
                if (response.upgrandtotal && $('round_up_cashin'))
                    $('round_up_cashin').innerHTML = response.upgrandtotal;
                calculateRemain();
            }
            hideColrightAjaxloader();
            hasAnotherRequest = false;
        }
    });
}

var showToastMessage = function (type, toast_title, toast_message) {
    $D.toaster({priority: type, title: toast_title, message: toast_message});
}

var emptyCartDataByJS = function () {
    if ($('webpos_cart'))
        $('webpos_cart').update('');
    if ($('webpos_cart_discount'))
        $('webpos_cart_discount').update(getPriceFormatedHtml(0));
    if ($('webpos_cart_tax'))
        $('webpos_cart_tax').update(getPriceFormatedHtml(0));
    if ($('webpos_cart_subtotals'))
        $('webpos_cart_subtotals').update(getPriceFormatedHtml(0));
    if ($('webpos_subtotal_button'))
        $('webpos_subtotal_button').update(getPriceFormatedHtml(0));
    if ($D('#all_discount_wp'))
        $D('#all_discount_wp').parent().remove();
    collectCartTotal();
    hideHoldButton();
}

var selectProductOption = function (productId, optionCode, optionValue, optionLabel) {
    var inputId = productId + '_' + optionCode;
    if ($(inputId + '_value')) {
        $(inputId + '_value').value = optionValue;
        $(inputId + '_value').setAttribute('optionLabel', optionLabel);
    }
    if ($(inputId + '_' + optionValue + '_selected_icon'))
        $(inputId + '_' + optionValue + '_selected_icon').removeClassName('hide');
    var prd_options_selected_icons = $$('.prd_options_selected_icon_' + optionCode);
    if (prd_options_selected_icons.length > 0)
        prd_options_selected_icons.each(function (icon) {
            if (icon && $(inputId + '_' + optionValue + '_selected_icon') && inputId + '_' + optionValue + '_selected_icon' != icon.id)
                icon.addClassName('hide');
        });
    updateConfigurablePrice(productId);
}

var showProductOptions = function (productId, optionCode) {
    var options_label = $$('.' + productId + '_options_label');
    if (options_label.length > 0)
        options_label.each(function (option_label) {
            option_label.removeClassName('option_active');
        });
    if ($(productId + '_' + optionCode)) {
        $(productId + '_' + optionCode).addClassName('option_active');
        var option_values = $$('.' + productId + '_option_values');
        if (option_values.length > 0)
            option_values.each(function (optionContainer) {
                optionContainer.addClassName('hide');
            });
        var optionsContainer = $(productId + '_' + optionCode + '_values');
        if (optionsContainer)
            optionsContainer.removeClassName('hide');
    }
    if ($(productId + '_co_' + optionCode)) {
        $(productId + '_co_' + optionCode).addClassName('option_active');
        var option_values = $$('.' + productId + '_option_values');
        if (option_values.length > 0)
            option_values.each(function (optionContainer) {
                optionContainer.addClassName('hide');
            });
        var optionsContainer = $(productId + '_co_' + optionCode + '_values');
        if (optionsContainer)
            optionsContainer.removeClassName('hide');
    }
    if ($(productId + '_grouped_' + optionCode)) {
        $(productId + '_grouped_' + optionCode).addClassName('option_active');
        var option_values = $$('.' + productId + '_option_values');
        if (option_values.length > 0)
            option_values.each(function (optionContainer) {
                optionContainer.addClassName('hide');
            });
        var optionsContainer = $(productId + '_grouped_qtys');
        if (optionsContainer)
            optionsContainer.removeClassName('hide');
    }
    if ($(productId + '_bundle_' + optionCode)) {
        $(productId + '_bundle_' + optionCode).addClassName('option_active');
        var option_values = $$('.' + productId + '_option_values');
        if (option_values.length > 0)
            option_values.each(function (optionContainer) {
                optionContainer.addClassName('hide');
            });
        var optionsContainer = $(productId + '_bundle_' + optionCode + '_values');
        if (optionsContainer)
            optionsContainer.removeClassName('hide');
    }
    if ($('prd_' + productId))
        optionChangeSelection($('prd_' + productId).down('.item'));
    updateConfigurablePrice(productId);
}

var selectShippingMethod = function (save_data_url, methodCode, carrierName) {
    if ($('s_method_' + methodCode))
        $('s_method_' + methodCode).checked = true;
    if ($('shipping_area'))
        $('shipping_area').down('.panel-heading').innerHTML = 'Shipping:    ' + carrierName;
    webpos_save_data(save_data_url, 'shipping');
}
/*Mr.Jack validate input payment*/
var allInserted = function (allInput, allSelect) {
    var inserted = true;
    for (var i = 0; i < allInput.length; i++) {
        if (allInput[i].value == '')
            inserted = false;
    }
    for (var i = 0; i < allSelect.length; i++) {
        if (allSelect[i].value == '')
            inserted = false;
    }
    return inserted;
}
var canPlaceOrder = function (el) {
    /*
     var isForCashActive = false;
     $$('.payment_label').each(function(element) {
     if (element.getAttribute('code') == 'cashforpos' && element.hasClassName('active'))
     isForCashActive = true;
     });
     var remainValue = convertLongNumber(getStringPriceFromString($('remain_value').down('.price').innerHTML));
     var disabled = true;
     if (!isForCashActive) {
     $$('.payment_label').each(function(element) {
     if (element.getAttribute('code') != 'cashforpos' && element.hasClassName('active')) {
     if ($('payment_form_' + element.getAttribute('code')) && $('payment_form_' + element.getAttribute('code')).getElementsByClassName('input-text').length) {
     var allInput = $('payment_form_' + element.getAttribute('code')).getElementsByClassName('input-text');
     var allSelect = $('payment_form_' + element.getAttribute('code')).getElementsByTagName('select');
     var inserted = false;
     inserted = allInserted(allInput, allSelect);
     if (inserted)
     disabled = false;
     else
     disabled = true;
     $('bt_place_order').disabled = disabled;
     for (var i = 0; i < allInput.length; i++) {
     allInput[i].observe('keypress', function(e) {
     inserted = allInserted(allInput, allSelect);
     if (inserted)
     disabled = false;
     else
     disabled = true;
     $('bt_place_order').disabled = disabled;
     if (remainValue < 0)
     $('bt_place_order').disabled = false;
     });
     allInput[i].observe('keydown', function(e) {
     inserted = allInserted(allInput, allSelect);
     if (inserted)
     disabled = false;
     else
     disabled = true;
     if (e.target.value.split('').length == 1)
     disabled = true;
     $('bt_place_order').disabled = disabled;
     if (remainValue < 0)
     $('bt_place_order').disabled = false;
     });
     }
     for (var i = 0; i < allSelect.length; i++) {
     allSelect[i].observe('change', function(e) {
     inserted = allInserted(allInput, allSelect)
     if (inserted)
     disabled = false;
     else
     disabled = true;
     $('bt_place_order').disabled = disabled;
     if (remainValue < 0)
     $('bt_place_order').disabled = false;
     });
     }
     }
     else
     disabled = false;
     
     }
     });
     $('bt_place_order').disabled = disabled;
     }
     if ((parseFloat(remainValue) <= 0)) {
     $('bt_place_order').disabled = false;
     }
     else {
     if (!disabled)
     $('bt_place_order').disabled = disabled;
     else
     $('bt_place_order').disabled = true;
     }
     */
}
/**/
var applyCashin = function (el) {
    if (el != '')
        $('cashin_value').value = el.down('.price').innerHTML.replace('&nbsp;', ' ');
    calculateRemain();
    /*disabled place order button if doesn't pay by full amount Mr.Jack*/
    canPlaceOrder();
}

var showContentOptionsAfterPlace = function (orderId, orderIncrementId, printLink, customerEmail, grandTotal, autoPrint) {
    if ($D('#remove-customer'))
        $D('#remove-customer').hide();
    if ($D('#continue_select_product'))
        $D('#continue_select_product').addClass('hide');
    if ($('shipping_payment_wrapper'))
        $('shipping_payment_wrapper').hide();
    if ($('success_orderGrandTotal'))
        $('success_orderGrandTotal').innerHTML = grandTotal;
    if ($('success_orderIncrementId'))
        $('success_orderIncrementId').innerHTML = '#' + orderIncrementId;
    if ($('success_customerEmail'))
        $('success_customerEmail').value = customerEmail;
    if ($('success_print')) {
        $('success_print').setAttribute('onclick', "printOrder('" + printLink + "')");
        if (autoPrint) {
            $('success_print').addClassName('autoPrint');
        } else {
            $('success_print').removeClassName('autoPrint');
        }
    }
    if ($('success_order_id'))
        $('success_order_id').value = orderId;
    if ($('order-success'))
        $('order-success').removeClassName('hide');

    if ($('webpos_customer_overlay'))
        $('webpos_customer_overlay').removeClassName('hide');
}

var hideBgOverlay = function () {
    if ($('webpos_dark_overlay')) {
        $D('#webpos_dark_overlay').css({display: 'none'});
        $('webpos_dark_overlay').hide();
    }
}

var showBgOverlay = function () {
    if ($('webpos_dark_overlay')) {
        $D('#webpos_dark_overlay').css({display: 'block'});
        $('webpos_dark_overlay').show();
    }
}

var disableCheckout = function () {
    if ($('footer_right_overlay'))
        $('footer_right_overlay').removeClassName('hide');
}

var enableCheckout = function () {
    if ($('footer_right_overlay'))
        $('footer_right_overlay').addClassName('hide');
}

var startNewOrder = function () {
    updateNumberPendingOrder();
    resetCustomerInfo();
    if ($('offline_tax'))
        $('offline_tax').remove();
    if ($('offline_shipping'))
        $('offline_shipping').remove();
    if ($('cashin_value'))
        $('cashin_value').value = '';
    if ($('success_order_id'))
        $('success_order_id').value = '';
    if ($('shipping_payment_wrapper'))
        $('shipping_payment_wrapper').show();
    if ($('order-success'))
        $('order-success').addClassName('hide');
    if ($('webpos_customer_overlay'))
        $('webpos_customer_overlay').addClassName('hide');
    if ($('bt_place_order'))
        $('bt_place_order').disabled = false;
    disableCheckout();
    showCategory();
}

var loginWebpos = function () {
    var validator = new Validation('webpos_login');
    if (validator.validate()) {
        hasAnotherRequest = true;
        if ($('login_loader'))
            $('login_loader').removeClassName('hide');
        var username = $('login_username').value;
        var password = $('login_password').value;
        var parameters = {username: username, password: password};
        var request = new Ajax.Request(loginUrl, {
            method: 'post',
            parameters: parameters,
            onFailure: '',
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var response = JSON.parse(transport.responseText);
                    if (response.menu && $('menu'))
                        $('menu').update(response.menu);
                    if (response.orders_area && $('orders_area'))
                        $('orders_area').update(response.orders_area);
                    if (response.settings_area && $('settings_area'))
                        $('settings_area').update(response.settings_area);
                    if (response.webpos_popups && $('webpos_popups'))
                        $('webpos_popups').update(response.webpos_popups);
                    if (response.errorMessage && response.errorMessage != '') {
                        showToastMessage('danger', 'Error', response.errorMessage);
                    } else {
                        if ($('login-webpos'))
                            $('login-webpos').addClassName('hide');
                        if ($('login_overlay'))
                            $('login_overlay').addClassName('hide');
                        if (localGet('productlist') == null)
                            reloadListProduct('firstTime', 0, 'All Categories');
                        else
                        if ($('product_content')) {
                            $('product_content').update(localGet('productlist'));
                            loadProductImage();
                        }
                    }
                    if (response.userid != null) {
                        currentUserId = response.userid;
                        updateNumberPendingOrder();
                    }
                    var till_data = localGet('webpos_till');
                    if (response.enable_till) {
                        if (till_data == null) {
                            showTillSelectPopup();
                        } else {
                            selectTill(till_data.till_id, till_data.till_name);
                        }
                    }
                }
                hasAnotherRequest = false;
            },
            onComplete: function () {
                if ($D('#holded_orders_section')) {
                    holded_order_section = new webposArea('holded_orders_section', '-100vw', '0px', '99px', '0px');
                }
                if ($D('#cashdrawer_area')) {
                    cashdrawer_section = new webposArea('cashdrawer_area', '-100vw', '0px', '99px', '0px');
                }
                if ($D('#reports_area')) {
                    var reports_section = new webposArea('reports_area', '-100vw', '0px', '99px', '0px');
                }
                if ($('login_loader'))
                    $('login_loader').addClassName('hide');
                hasAnotherRequest = false;
            }
        });
    }
}

var calculateRemain = function () {
    var cashin = remain = symbol = grandTotal = '';
    if ($('cashin_value')) {
        cashin = $('cashin_value').value;
        grandTotal = $('cashin_fullamount').down('.price').innerHTML;
        cashin = parseFloat(convertPrice(getStringPriceFromString(cashin)));
        grandTotal = parseFloat(convertPrice(getStringPriceFromString(grandTotal)));

        if (!$('cashin_value').value) {
            remain = grandTotal;
        } else {
            remain = grandTotal - cashin;
        }
        if ($('remain_value'))
            $('remain_value').innerHTML = getPriceFormatedHtml(remain);
        if ($('cashin_value_label'))
            $('cashin_value_label').innerHTML = $('cashin_value').value;
        if ($('remain_value_label')) {
            var payment_form = $('webpos_payment_method_form');
            var payment_method = $RF(payment_form, 'payment[method]');
            if (payment_method == 'cashforpos') {
                $('remain_value_label').innerHTML = $('remain_value').innerHTML;
            } else {
                $('remain_value_label').innerHTML = 0;
            }
        }

        if ($('cashforpos_need_pay_amount')) {
            var payment_form = $('webpos_payment_method_form');
            var payment_method = $RF(payment_form, 'payment[method]');
            if (payment_method != 'cashforpos') {
                $('cashforpos_selected_icon').addClassName('hide');
                $('cashforpos_need_pay_amount').innerHTML = $('cashin_value').value;
            } else {
                $('cashforpos_need_pay_amount').innerHTML = $('cashin_fullamount').down('.price').innerHTML;
            }
            if (getStringPriceFromString($('cashin_value').value) != '') {
                $('cashforpos_selected_icon').removeClassName('hide');
                $('cashforpos_need_pay_amount').innerHTML = $('cashin_value').value;
            }
        }
    }
    var paymentSelectedIcons = $$('.paymentSelectedIcon');
    if (paymentSelectedIcons.length > 0) {
        paymentSelectedIcons.each(function (icon) {
            var method_code = icon.getAttribute('method_code');
            if (!icon.hasClassName('hide') && method_code != 'cashforpos') {
                if ($(method_code + '_need_pay_amount')) {
                    $(method_code + '_need_pay_amount').innerHTML = (getRemainValue() > 0)?$('remain_value').innerHTML:getPriceFormatedNoHtml(0);
                }
            }
        });
    }
}

var saveOrderComment = function () {
    if ($('success_order_id') && $('success_order_id').value != '') {
        var orderId = $('success_order_id').value;
        var orderComment = $('order-comment-content').value;
        var params = {order_id: orderId, order_comment: orderComment};
        hasAnotherRequest = true;
        var request = new Ajax.Request(saveOrderCommentUrl, {
            method: 'get',
            parameters: params,
            onSuccess: function (transport) {
                var response = getResponseText(transport);
                showToastMessage('success', 'Message', 'Order comment has been saved successfully!');
                hasAnotherRequest = false;
            },
            onComplete: function () {
            },
            onFailure: function () {
                hasAnotherRequest = false;
            }
        });
    } else {
        $D('#order-comment').removeClass('active');
        $D('#order-comment').attr('style', 'display:none');
        $('webpos_overlay').hide();
        var orderComment = $('order-comment-content').value;
        localSet('orderComment', orderComment);
        showToastMessage('success', 'Message', 'Order comment has been saved successfully!');
    }
}

var fillDataFromLocal = function () {
    $('order-comment-content').value = localGet('orderComment');
    /*
     var totalsProduct = 0;
     if(localGet('totalsProduct') != null) totalsProduct = localGet('totalsProduct');
     var savedNumber = getNumberProductSaved();
     showStatus(savedNumber,totalsProduct,'');
     */
}

var deleteDataFromLocal = function () {
    localDelete('orderComment');
    localDelete('custom_options');
    localDelete('bundle_options');
    localDelete('bundle_options_qty');
    localDelete('configurable_options');
    localDelete('webpos_cart_data');
    localDelete('customerInCart');
    localDelete('webpos_custom_options');
}

function convertPrice(oldPrice) {
    var newPrice = "";
    var indexOfc = oldPrice.indexOf('.');
    var indexOfp = oldPrice.indexOf(',');
    var arrays = oldPrice.split('&nbsp;');
    while (arrays.length > 1) {
        oldPrice = oldPrice.replace('&nbsp;', '');
        arrays = oldPrice.split('&nbsp;');
    }
    if (indexOfp == -1 && indexOfc != -1) {
        var array = oldPrice.split('.');
        if (array.length >= 3) {
            newPrice = oldPrice.replace('.', '');
            var array2 = newPrice.split('.');
            while (array2.length > 1) {
                newPrice = newPrice.replace('.', '');
                array2 = newPrice.split('.');
            }
            return newPrice;
        }
    }

    if (indexOfp != -1 && indexOfc == -1) {
        var array = oldPrice.split(',');
        if (array.length >= 3) {
            newPrice = oldPrice.replace('.', '');
            var array2 = newPrice.split(',');
            while (array2.length > 1) {
                newPrice = newPrice.replace(',', '');
                array2 = newPrice.split(',');
            }
            return newPrice;
        }
    }

    if (indexOfc != -1 && indexOfp != -1) {
        if (indexOfp > indexOfc) {
            return newPrice = oldPrice.replace('.', '').replace(',', '.');
        } else {
            return newPrice = oldPrice.replace(',', '');
        }
    }
    if (indexOfp != -1 && indexOfc == -1) {
        return newPrice = oldPrice.replace(',', '.');
    }

    return oldPrice;
}

var getPriceFormatedHtml = function (price) {
    return "<span class='price'>" + formatCurrency((parseFloat(price)), priceFormat) + "</span>";
}
var getPriceFormatedNoHtml = function (price) {
    return formatCurrency((parseFloat(price)), priceFormat);
}

var getPriceFromString = function (priceString) {
    return convertLongNumber(priceString.replace(currency_symbol, ''));
}

var getStringPriceFromString = function (priceString) {
    return priceString.replace(currency_symbol, '');
}

var showStatus = function (currentValue, maxValue, loading) {
    var percent = 100;
    if (maxValue != 0) {
        percent = parseFloat(currentValue / maxValue * 100);
    }
    percent = percent.toFixed(0);
    if (loading == 'firstTime')
        var status = "<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='1' aria-valuemin='0' aria-valuemax='100' style='width:100%'>Loading product information</div></div>";
    else
        var status = "<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='" + currentValue + "' aria-valuemin='0' aria-valuemax='" + maxValue + "' style='width:" + percent + "%'>" + currentValue + "/" + maxValue + " product(s) saved (" + percent + "%) " + loading + " </div></div>";
    if (loading == 'customer')
        var status = "<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='" + currentValue + "' aria-valuemin='0' aria-valuemax='" + maxValue + "' style='width:" + percent + "%'>" + currentValue + "/" + maxValue + " customer(s) saved (" + percent + "%) " + loading + " </div></div>";
    $('numberProduct').innerHTML = status;
    if (currentValue == maxValue && loading != 'firstTime') {
        setTimeout(function () {
            if (loading != 'customer')
                $('numberProduct').innerHTML = currentValue + "/" + maxValue + " product(s) saved (" + percent + "%)";
            else
                $('numberProduct').innerHTML = currentValue + "/" + maxValue + " customer(s) saved (" + percent + "%)";
        }, 5000);
    }
}

var saveCustomInfo = function (productId, productData) {
    var productsInfo = {};
    if ($D.jStorage.get('customInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('customInfo'));
        productsInfo[productId] = productData;
        $D.jStorage.set('customInfo', JSON.stringify(productsInfo));
    } else {
        productsInfo = {};
        productsInfo[productId] = productData;
        $D.jStorage.set('customInfo', JSON.stringify(productsInfo));
    }
};
var collectTotalJS = function () {
    var subTotal = 0;
    $D('.product .row_total .price').each(function () {
        var rowTotal = convertLongNumber(getStringPriceFromString(this.innerHTML));
        subTotal = parseFloat(subTotal) + parseFloat(rowTotal);
    });
    $('webpos_cart_subtotals').innerHTML = getPriceFormatedHtml(parseFloat(subTotal));

};
var collectTax = function () {
    updateTaxTotals(0, '');
};

var collectTaxWithoutHtml = function () {
    var totalTax = 0;
    $D('#webpos_cart .product').each(function () {
        var rowTaxTotal = parseFloat($D(this).attr('tax_amount')) * parseFloat($D(this).attr('qty'));
        totalTax = totalTax + rowTaxTotal;

    });
    return parseFloat(totalTax);

};
var collectGrandTotalJS = function () {
    var subTotal = 0;
    $D('.product .row_total .price').each(function () {
        var rowTotal = convertLongNumber(getStringPriceFromString(this.innerHTML));
        subTotal = subTotal = parseFloat(subTotal) + parseFloat(rowTotal);
    });

    var discount = 0;
    if ($('all_discount_wp')) {
        if (!isNaN($('all_discount_wp').down('.price').innerHTML))
            discount = parseFloat($('all_discount_wp').down('.price').innerHTML);
        else
            discount = parseFloat(convertLongNumber(getStringPriceFromString($('all_discount_wp').down('.price').innerHTML)));
    } else
        discount = 0;
    subTotal = subTotal + discount;
    if (isOffline())
        collectTax();
    if ($D('#webpos_cart_tax .price') && $D('#webpos_cart_tax .price').is(":visible")) {
        subTotal = subTotal + collectTaxWithoutHtml();
    }

    $('webpos_subtotal_button').innerHTML = getPriceFormatedHtml(parseFloat(subTotal));
};
var loadMoreProduct = function () {
    if (($('mode_status') && $('mode_status').value == 'off') || useLocalSearch == true) {
        if ($D('#product-left #content')) {
            var scrollTop = $D('#product-left #content').scrollTop();
            var offsetHeight = $D('#product-left #content').height();
            var scrollHeight = $D('#product-left #content').prop("scrollHeight");
            if ((scrollTop + offsetHeight) >= (scrollHeight - 40)) {
                loadmoreProductByCategory();
            }
        }
    }
}

var loadMoreCustomer = function () {
    if (($('mode_status') && $('mode_status').value == 'off') || useLocalSearch == true) {
        if ($D('#popup-customer #customer_list')) {
            var scrollTop = $D('#popup-customer #customer_list').scrollTop();
            var offsetHeight = $D('#popup-customer #customer_list').height();
            var scrollHeight = $D('#popup-customer #customer_list').prop("scrollHeight");
            if ((scrollTop + offsetHeight) >= (scrollHeight - 40)) {
                loadMoreCustomerFromLocal();
            }
        }
    }
}

var searchCustomerOffline = function (searchInput) {
    if (($('mode_status') && $('mode_status').value == 'off') || useLocalSearch == true) {
        var keyword = searchInput.value;
        searchCustomerFromLocal(keyword);
    }
}


var convertLongNumber = function (numberString) {
    decimalSymbolNumber = priceFormat.decimalSymbol;
    groupSymbolNumber = priceFormat.groupSymbol;
    result = accounting.unformat(numberString, decimalSymbolNumber);
    return result;
};

function placeOrderOffline(parameters, successMessage) {
    var viewData = {};
    var cartData = localGet('webpos_cart_data');
    var customerInCart = localGet('customerInCart');
    var orderId = orderIncrementId = "############";
    var printLink = customerEmail = grandTotal = isVirtual = 0;
    showToastMessage('success', 'Message', successMessage);
    if (isVirtual) {
        $$('#options_after_place_order .option')[0].hide();
        $('create_shipment').checked = false;
    } else {
        $$('#options_after_place_order .option')[0].show();
    }
    if (parameters.remain != null && parseFloat(parameters.remain) <= 0) {
        $$('#options_after_place_order .option')[0].show();
        $$('#options_after_place_order .option')[1].hide();
        if ($D('#create_shipment')) {
            $('create_shipment').checked = false;
            $D('#create_shipment').prop('checked', false).change();
        }
        if ($D('#create_invoice')) {
            $('create_invoice').checked = false;
            $D('#create_invoice').prop('checked', false).change();
        }
    } else {
        $$('#options_after_place_order .option')[0].show();
        $$('#options_after_place_order .option')[1].show();
        if ($D('#create_shipment')) {
            $('create_shipment').checked = true;
            $D('#create_shipment').prop('checked', true).change();
        }
        if ($D('#create_invoice')) {
            $('create_invoice').checked = true;
            $D('#create_invoice').prop('checked', true).change();
        }
    }
    localSet('lastOrderDate', getCurrentDate());
    localSet('lastOrderTime', getCurrentTime());

    var items_table = [];
    var items = $$('#webpos_cart .product');
    if (items.length > 0)
        items.each(function (el) {
            var qty = el.down('.img-product').down('.number').innerHTML;
            var price = el.down('.webpos_item_subtotal ').down('.price').innerHTML;
            var subtotal = el.down('.webpos_item_subtotal ').down('.price').innerHTML;
            var name = el.down('.product_name').innerHTML;
            var options = el.down('.product_options').innerHTML;
            var item = {qty: qty, price: price, name: name, options: options, subtotal: subtotal};
            items_table.push(item);
        });

    localSet('lastorder_items_table', items_table);
    grandTotal = $('webpos_subtotal_button').down('.price').innerHTML;
    localSet('lastorder_grandTotal', grandTotal);
    if (customerInCart != null)
        customerEmail = customerInCart.email;
    parameters.cartData = cartData;
    parameters.customerInCart = customerInCart;
    if ($('username'))
        parameters.posUserDisplayName = $('username').value;
    if ($('userid'))
        parameters.posUserId = $('userid').value;
    viewData.cashin = $('cashin_value').value;
    viewData.items = items_table;
    viewData.grandTotal = $('webpos_subtotal_button').down('.price').innerHTML;
    viewData.shippingLabel = $$("label[for='s_method_" + parameters.shipping_method + "']")[0].innerHTML;
    viewData.paymentLabel = $$("label[for='p_method_" + parameters.payment_method + "']")[0].innerHTML;
    parameters.viewData = viewData;
	parameters.till_id = getCurrentTillId();
    saveOrderToLocal(parameters);
    showContentOptionsAfterPlace(orderId, orderIncrementId, printLink, customerEmail, grandTotal, auto_print_receipt);
    var reloading_holded_key = localGet('reloading_holded_key');
    if (reloading_holded_key && reloading_holded_key != null && reloading_holded_key != '') {
        localDelete('reloading_holded_key');
        var hold_order_object = new webposHoldOrder();
        hold_order_object.removeByKey(reloading_holded_key);
        $('holded_' + reloading_holded_key).remove();
    }
}
;

var getProductObject = function () {
    var productId = $('edit_cart_item_popup').getAttribute('prdid');
    var itemId = $('edit_cart_item_popup').getAttribute('itemid');
    var productObjectById = $D('.product[prdid=' + productId + ']');

    //25/7/2015
    if ($D('.product[prdid=' + productId + ']').length >= 2) {
        $D('.product[prdid=' + productId + ']').each(function () {
            if ($D('#edit_cart_item_popup').attr('cart_item') == $D(this).attr('id')) {
                productObjectById = $D(this);
            }
        });


    }
    var productObjectByItem = $D('.product[itemid=' + itemId + ']');
    var productObject;
    if (itemId == "undefined") {
        productObject = productObjectById;
    } else {
        productObject = productObjectByItem;
    }
    return productObject;

};

var collectCartTotal = function () {
    var numberTotal = 0;
    $D('#webpos_cart .product').each(function () {
        numberTotal = numberTotal + parseInt(($D(this).find('.number').html()));

    });
    $D('#total_number_item').html(numberTotal);
};


var checkNetwork = function (errorMessage, successMessage) {
    if (checkingNetwork == true || hasAnotherRequest == true || checkingStock == true)
        return;
    checkingNetwork = true;
    var request = $D.ajax({
        type: 'GET',
        url: checkNetworkUrl,
        timeout: 30000,
        success: function (data) {
            checkingNetwork = false;
            number_pending_order = parseInt(getNumberPendingOrders());
            if ((isOffline() == true && offlineManual == false && hasAnotherRequest == false) || number_pending_order > 0)
                showToastMessage('success', 'Message', successMessage);
            $('network_status').value = 'on';
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            checkingNetwork = false;
            if (isOffline() == false && hasAnotherRequest == false)
                gotoOffline(errorMessage);
            $('network_status').value = 'off';
        }
    });
}



var updateNumberPendingOrder = function () {
    number_pending_order = parseInt(getNumberPendingOrders());
    if ($('number_order_pending'))
        $('number_order_pending').innerHTML = parseInt(getNumberPendingOrders());
}

function getCurrentDate() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();
    if (dd < 10) {
        dd = '0' + dd
    }
    if (mm < 10) {
        mm = '0' + mm
    }
    return mm + '/' + dd + '/' + yyyy;
}

function getCurrentTime() {
    var today = new Date();
    var hh = today.getHours();
    var mm = today.getMinutes(); //January is 0!
    var ss = today.getSeconds();
    if (hh < 10) {
        hh = '0' + hh
    }
    if (mm < 10) {
        mm = '0' + mm
    }
    return hh + ':' + mm + ':' + ss;
}

var calculatePriceJS = function () {
    var discountAmount = parseFloat(reFormatPrice());
    var productObject = getProductObject();
    var productPrice = parseFloat($D('#edit_cart_item_popup').attr('product_price'));
    var qty = parseFloat($D('#editpopup_product_qty').val());

    var newCustomPrice;
    var newRegularPrice;
    newCustomPrice = qty * parseFloat(currentCustomPrice());
    newRegularPrice = qty * productPrice;
    var itemId = productObject.attr('itemid');
    var editPriceType = getEditPriceType();
    var reduceType = getChangeType();
    var object = '{' + '"editPrice" :"' + editPriceType + '","reduceType"  :"' + reduceType + '","discountAmount"  :"' + discountAmount + '"}';

    //var test=JSON.parse($D.jStorage.get('customInfo'));
    saveCustomInfo(itemId, JSON.parse(object));


    if ($D('#discount-amount').val() != '') {
        if (getEditPriceType() == 'discount') {
            if (discountAmount == 0) {
                productObject.attr('custom_price', '');
                productObject.attr('discount-amount', discountAmount);
                productObject.attr('discount-type', 'discount');
                productObject.find('.webpos_item_subtotal .price').html(formatCurrency(newRegularPrice.toFixed(2), priceFormat));
                productObject.find('.webpos_item_original').html('');
            } else {
                productObject.attr('custom_price', parseFloat(currentCustomPrice()));
                productObject.attr('discount-amount', discountAmount);
                productObject.attr('discount-type', 'discount');
                if (reduceType == 'dollar') {
                    productObject.attr('reduce-type', 'dollar');
                } else {
                    productObject.attr('reduce-type', 'percent');
                }
                productObject.find('.webpos_item_subtotal .price').html(formatCurrency(newCustomPrice.toFixed(2), priceFormat));
                productObject.find('.webpos_item_original').html('Reg: ' + formatCurrency(newRegularPrice.toFixed(2), priceFormat));
            }
        } else {
            if (productPrice != parseFloat(currentCustomPrice())) {
                productObject.attr('custom_price', parseFloat(currentCustomPrice()));
                productObject.attr('discount-type', 'custom');
                productObject.attr('discount-amount', discountAmount);
                if (reduceType == 'dollar') {
                    productObject.attr('reduce-type', 'dollar');
                } else {
                    productObject.attr('reduce-type', 'percent');
                }
                productObject.find('.webpos_item_subtotal .price').html(formatCurrency(newCustomPrice.toFixed(2), priceFormat));
                productObject.find('.webpos_item_original').html('Reg: ' + formatCurrency(newRegularPrice.toFixed(2), priceFormat));
            }
        }
    } else {
        if (getEditPriceType() != 'customPrice') {
            productObject.attr('custom_price', '');
            productObject.find('.webpos_item_subtotal .price').html(formatCurrency(newRegularPrice.toFixed(2), priceFormat));
            productObject.find('.webpos_item_original').html('');
        } else {
            productObject.attr('custom_price', '0');
            productObject.find('.webpos_item_subtotal .price').html(formatCurrency(0, priceFormat));
            productObject.find('.webpos_item_original').html('Reg: ' + formatCurrency(newRegularPrice.toFixed(2), priceFormat));
        }
    }
    collectTotalJS();
    collectGrandTotalJS();



};

var currentCustomPrice = function () {
    var editPriceType;
    if ($D('#btn-discount').hasClass('choose')) {
        editPriceType = 'discount';
    } else {
        editPriceType = 'customPrice';
    }
    var changeType;
    if ($D('#btn-percent').hasClass('btn2')) {
        changeType = 'percent';
    } else {
        changeType = 'dollar';
    }
    var productPrice;
    var productObject = getProductObject();
    productPrice = parseFloat($D('#edit_cart_item_popup').attr('product_price'));
    var discountAmount;
    discountAmount = parseFloat(reFormatPrice());
    var price;
    price = calculateCustomPrice(editPriceType, changeType, productPrice, discountAmount);
    return price;
};
var getEditPriceType = function () {
    var editPriceType;
    if ($D('#btn-discount').hasClass('choose')) {
        editPriceType = 'discount';
    } else {
        editPriceType = 'customPrice';
    }
    return editPriceType;
};

var calculateCustomPrice = function (editType, changeType, productPrice, customPrice) {
    if (editType == 'discount') {
        if (changeType == 'dollar') {
            price = productPrice - customPrice;
            if (price <= 0)
                price = 0;
        } else {
            price = productPrice * (100 - customPrice) / 100;
            if (price <= 0)
                price = 0;
        }
    } else {
        if (changeType == 'dollar') {
            price = customPrice;
        } else {
            price = productPrice * (customPrice) / 100;
        }
    }
    return price.toFixed(2);
};
var reFormatPrice = function () {

    var splitPrice;
    var result;
    var realNumber;
    if ($D('#btn-percent').hasClass('btn2')) {
        splitPrice = getStringPriceFromString($('discount-amount').value);
        realNumber = splitPrice;
    } else {
        splitPrice = getStringPriceFromString($('discount-amount').value);
        realNumber = splitPrice;
    }
    result = convertLongNumber(getStringPriceFromString(realNumber));
    return result;

};

function checkOutOfStock() {
    if (checkingStock == true || checkingNetwork == true || useLocalSearch == false)
        return;
    checkingStock = true;
    var last_updated_time = localGet('updated_time');
    var parameters = {last_updated_time: last_updated_time};
    hasAnotherRequest = true;
    var request = new Ajax.Request(checkStockUrl, {
        method: 'get',
        parameters: parameters,
        onFailure: function () {
            hasAnotherRequest = false;
        },
        onSuccess: function (transport) {
            if (transport.status == 200) {
                var response = JSON.parse(transport.responseText);
                if (response.productIds != null) {
                    saveOutofstockProduct(response.productIds);
                }
                if (response.product_updated_data) {
                    saveUpdatedProduct(response.product_updated_data);
                    localSet('updated_time', response.updated_time);
                }
                if (response.customer_updated_data) {
                    saveUpdatedCustomer(response.customer_updated_data);
                    localSet('updated_time', response.updated_time);
                }
            }
            hasAnotherRequest = false;
            checkingStock = false;
        },
        onException: function () {
            hasAnotherRequest = false;
            checkingStock = false;
        }
    });
}

function hidePendingOrdersPopup() {
    hideBgOverlay();
    if ($D('#pending_orders_popup')) {
        $D('#pending_orders_popup').animate({top: '-1000px'}, 500);
        $D('#pending_orders_popup').css({top: '-1000px'});
    }
}

function selectAllPendingOrders(allCheckbox) {
    var selected_orders = $$('.selected_order');
    if (selected_orders.length > 0)
        selected_orders.each(function (checkbox) {
            checkbox.checked = (allCheckbox.checked == true) ? true : false;
        });
}

function deletePendingOrderAtPopup(pendingKey) {
    if ($('pending_row_' + pendingKey))
        $('pending_row_' + pendingKey).remove();
    deletePendingOrder(pendingKey);
    updateNumberPendingOrder();
}
/* Daniel - updated - Customer address dropdown 20151118 */
function changeBilling() {
    var isNew = $('billing-address-select').value ? false : true;
    if (isNew == true) {
        if ($('billing_form'))
            $('billing_form').removeClassName('hide');
    } else {
        if ($('billing_form'))
            $('billing_form').addClassName('hide');
    }
}
function changeShipping() {
    var isNew = $('shipping-address-select').value ? false : true;
    if (isNew == true) {
        if ($('shipping_form'))
            $('shipping_form').removeClassName('hide');
    } else {
        if ($('shipping_form'))
            $('shipping_form').addClassName('hide');
    }
}
/* Daniel - updated - Customer address dropdown 20151118 */

function getRemainValue(){
    var cashin = remain = symbol = grandTotal = '';
    if ($('cashin_value')) {
        cashin = $('cashin_value').value;
        grandTotal = $('cashin_fullamount').down('.price').innerHTML;
        cashin = parseFloat(convertPrice(getStringPriceFromString(cashin)));
        grandTotal = parseFloat(convertPrice(getStringPriceFromString(grandTotal)));

        if (!$('cashin_value').value) {
            remain = grandTotal;
        } else {
            remain = grandTotal - cashin;
        }
        return remain;
    }
    return '';
}