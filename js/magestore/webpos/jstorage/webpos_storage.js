function isOffline() {
    if (enableOffline == false)
        return false;
    var status = true;
    if ($('mode_status').value == 'on') {
        status = false;
    }
    return status;
}

function isRealOffline() {
    var status = true;
    if ($('network_status').value == 'on') {
        status = false;
    }
    return status;
}

var getLocalSize = function () {
    return $D.jStorage.storageSize();
}

var localFlush = function () {
    $D.jStorage.flush();
}

var localDelete = function (key) {
    $D.jStorage.deleteKey(key);
}

var localGet = function (key) {
    return $D.jStorage.get(key);
}

var localSet = function (key, value) {
    $D.jStorage.set(key, value);
}

var saveStorecreditOption = function (productId, amount) {
    var storecreditData = {};
    if ($D.jStorage.get('storecreditData') != null) {
        storecreditData = JSON.parse($D.jStorage.get('storecreditData'));
        storecreditData[productId] = amount;
        $D.jStorage.set('storecreditData', JSON.stringify(storecreditData));
    } else {
        var storecreditData = {};
        storecreditData[productId] = amount;
        $D.jStorage.set('storecreditData', JSON.stringify(storecreditData));
    }
}
var getAllStorecreditOption = function () {
    $D.jStorage.reInit();
    var storecreditData = {};
    if ($D.jStorage.get('storecreditData') != null) {
        storecreditData = JSON.parse($D.jStorage.get('storecreditData'));
        return storecreditData;
    }
    return '';
}
var saveCustomOption = function (productId, optionId, optionValue) {
    var custom_options = {};
    if ($D.jStorage.get('custom_options') != null) {
        custom_options = JSON.parse($D.jStorage.get('custom_options'));
        if (custom_options[productId] == null)
            custom_options[productId] = {};
        custom_options[productId][optionId] = optionValue;
        $D.jStorage.set('custom_options', JSON.stringify(custom_options));
    } else {
        var custom_options = {};
        custom_options[productId] = {};
        custom_options[productId][optionId] = optionValue;
        $D.jStorage.set('custom_options', JSON.stringify(custom_options));
    }
}

var getCustomOption = function (productId) {
    var custom_options = {};
    if ($D.jStorage.get('custom_options') != null) {
        custom_options = JSON.parse($D.jStorage.get('custom_options'));
        if (typeof custom_options[productId] != 'undefined' && custom_options[productId] != null)
            return custom_options[productId];
    }
    return '';
}

var getCustomOptionValue = function (productId, optionId) {
    var custom_options = {};
    if ($D.jStorage.get('custom_options') != null) {
        custom_options = JSON.parse($D.jStorage.get('custom_options'));
        if (typeof custom_options[productId] != 'undefined' && typeof custom_options[productId][optionId] != 'undefined' && custom_options[productId] != null && custom_options[productId][optionId] != null)
            return custom_options[productId][optionId];
    }
    return '';
}

var getAllCustomOption = function () {
    $D.jStorage.reInit();
    var custom_options = {};
    if ($D.jStorage.get('custom_options') != null) {
        custom_options = JSON.parse($D.jStorage.get('custom_options'));
        return custom_options;
    }
    return '';
}

var fillCustomData = function (productId) {
    var custom_options = getCustomOption(productId);
    for (var optionId in custom_options) {
        if (typeof custom_options[optionId] === 'object') {
            var optionObj = custom_options[optionId];
            for (var optionKey in optionObj) {
                if ($(productId + '_co_' + optionId + '_value_' + optionKey))
                    $(productId + '_co_' + optionId + '_value_' + optionKey).value = optionObj[optionKey];
            }
        } else
        if (typeof custom_options[optionId] === 'array') {
            custom_options[optionId] = custom_options[optionId];
            if ($(productId + '_co_' + optionId + '_value')) {
                var options = $(productId + '_co_' + optionId + '_value').options;
                options.each(function (op) {
                    if (custom_options[optionId].indexOf(op.value) >= 0)
                        op.setAttribute('selected', 'selected');
                });
            }
        } else
        if ($(productId + '_co_' + optionId + '_value'))
            $(productId + '_co_' + optionId + '_value').value = custom_options[optionId];

    }
}

var saveTaxCalculationInfo = function (calculationId, taxCalculationData) {
    var taxCalculations = {};
    if ($D.jStorage.get('taxCalculations') != null) {
        taxCalculations = JSON.parse($D.jStorage.get('taxCalculations'));
        taxCalculations[calculationId] = taxCalculationData;
        $D.jStorage.set('taxCalculations', JSON.stringify(taxCalculations));
    } else {
        var taxCalculations = {};
        taxCalculations[calculationId] = taxCalculationData;
        $D.jStorage.set('taxCalculations', JSON.stringify(taxCalculations));
    }
}

var getTaxCalculationInfo = function (calculationId) {
    var taxCalculations = {};
    if ($D.jStorage.get('taxCalculations') != null) {
        taxCalculations = JSON.parse($D.jStorage.get('taxCalculations'));
        if (taxCalculations[calculationId] != null)
            return taxCalculations[calculationId];
    }
    return '';
}

var getAllTaxCalculationInfo = function () {
    var taxCalculations = {};
    if ($D.jStorage.get('taxCalculations') != null) {
        taxCalculations = JSON.parse($D.jStorage.get('taxCalculations'));
        return taxCalculations;
    }
    return '';
}

var getTaxRate = function (countryId, regionId, postcode) {
    var taxCalculations = {};
    if ($D.jStorage.get('taxCalculations') != null) {
        taxCalculations = JSON.parse($D.jStorage.get('taxCalculations'));
        for (var calculationId in taxCalculations) {
            var thisRate = true;
            var taxCalculationData = taxCalculations[calculationId];
            if (taxCalculationData.tax_country_id != countryId)
                thisRate = false;
            if (taxCalculationData.tax_region_id != regionId)
                thisRate = false;
            if (taxCalculationData.tax_postcode != '*' && taxCalculationData.tax_postcode != postcode)
                thisRate = false;
            if (taxCalculationData.zip_from != null && taxCalculationData.zip_from < postcode)
                thisRate = false;
            if (taxCalculationData.zip_to != null && taxCalculationData.zip_to > postcode)
                thisRate = false;
            if (thisRate == true)
                return taxCalculationData.rate;
        }
    }
    return 0;
}

var getTaxRateByCustomer = function () {
    if (isOffline() == false)
        return;
    var rateByCustomer = 0;
    var customerInCart = localGet('customerInCart');
    if (customerInCart != null) {
        rateByCustomer = customerInCart.taxRate;
        /* get tax rate by address */
        var billingAddress = customerInCart.billingAddress;
        var shippingAddress = customerInCart.shippingAddress;
        if (shippingAddress != null) {
            var country_id = shippingAddress['country_id'];
            var region_id = shippingAddress['region_id'];
            var postcode = shippingAddress['postcode'];
            rateByCustomer = getTaxRate(country_id, region_id, postcode);
        } else
        if (billingAddress != null) {
            var country_id = billingAddress['country_id'];
            var region_id = billingAddress['region_id'];
            var postcode = billingAddress['postcode'];
            rateByCustomer = getTaxRate(country_id, region_id, postcode);
        } else {
            var country_id = defaultCustomerConfig['country_id'];
            var region_id = defaultCustomerConfig['region_id'];
            var postcode = defaultCustomerConfig['postcode'];
            rateByCustomer = getTaxRate(country_id, region_id, postcode);
        }

        /**/
    } else {
        var customer_id = defaultCustomerConfig['customer_id'];
        var defaultCustomerData = getCustomerInfo(customer_id);
        if (defaultCustomerData != '' && defaultCustomerData != null) {
            localSet('customerInCart', defaultCustomerData);
            rateByCustomer = defaultCustomerData.taxRate;
            var billingAddress = defaultCustomerData.billingAddress;
            var shippingAddress = defaultCustomerData.shippingAddress;
            if (shippingAddress != null) {
                var country_id = shippingAddress['country_id'];
                var region_id = shippingAddress['region_id'];
                var postcode = shippingAddress['postcode'];
                rateByCustomer = getTaxRate(country_id, region_id, postcode);
            } else
            if (billingAddress != null) {
                var country_id = billingAddress['country_id'];
                var region_id = billingAddress['region_id'];
                var postcode = billingAddress['postcode'];
                rateByCustomer = getTaxRate(country_id, region_id, postcode);
            } else {
                var country_id = defaultCustomerConfig['country_id'];
                var region_id = defaultCustomerConfig['region_id'];
                var postcode = defaultCustomerConfig['postcode'];
                rateByCustomer = getTaxRate(country_id, region_id, postcode);
            }
        } else {
            var country_id = defaultCustomerConfig['country_id'];
            var region_id = defaultCustomerConfig['region_id'];
            var postcode = defaultCustomerConfig['postcode'];
            rateByCustomer = getTaxRate(country_id, region_id, postcode);
        }
    }
    return rateByCustomer;
}

var saveProductsInfo = function (productId, productData) {
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        productsInfo[productId] = productData;
        $D.jStorage.set('productsInfo', JSON.stringify(productsInfo));
    } else {
        var productsInfo = {};
        productsInfo[productId] = productData;
        $D.jStorage.set('productsInfo', JSON.stringify(productsInfo));
    }
}

var getProductInfo = function (productId) {
    if (typeof webposDB != 'undefined') {
        webposDB.getProductById(productId);
        return localGet('product_result');
    }
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        if (productsInfo[productId] != null)
            return productsInfo[productId];
    }
    return '';
}

var getAllProductsInfo = function () {
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        var results = {};
        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var outofstock = productData.outofstock;
            if (outofstock == false || (show_outofstock == '1' && outofstock == true)) {
                results[productId] = {};
                results[productId]['category'] = productData.category;
                results[productId]['searchstring'] = productData.searchstring;
                results[productId]['html'] = productData.html;
            }
        }
        return results;
    }
    return '';
}

var updateTotalProduct = function () {
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        var results = [];
        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var outofstock = productData.outofstock;
            if (outofstock == false || (show_outofstock == '1' && outofstock == true)) {
                results.push(productId);
            }
        }
        updateNumberProduct(results.length);
    }
}

var saveOutofstockProduct = function (productIds) {
    if (productIds == null || productIds == '')
        return;
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var outofstock = productData.outofstock;
            if (productIds.indexOf(productId) >= 0 && outofstock == false) {
                productsInfo[productId]['outofstock'] = true;
            } else if (productIds.indexOf(productId) < 0) {
                productsInfo[productId]['outofstock'] = false;
            }
        }
        $D.jStorage.set('productsInfo', JSON.stringify(productsInfo));
        searchProductByCategory(0);
        updateTotalProduct();
    }
}

var saveUpdatedProduct = function (product_updated_data) {
    if (product_updated_data == null || product_updated_data == '')
        return;
    var productsInfo = {};
    if ($D.jStorage.get('productsInfo') != null) {
        productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        for (var productId in product_updated_data) {
            if (product_updated_data[productId] != null) {
                if (productsInfo[productId] == null)
                    productsInfo[productId] = {};
                productsInfo[productId] = product_updated_data[productId];
            }
        }
        $D.jStorage.set('productsInfo', JSON.stringify(productsInfo));
        searchProductByCategory(0);
        updateTotalProduct();
    }
}

var saveUpdatedCustomer = function (customer_updated_data) {
    if (customer_updated_data == null || customer_updated_data == '')
        return;
    var customersInfo = {};
    if ($D.jStorage.get('customersInfo') != null) {
        customersInfo = JSON.parse($D.jStorage.get('customersInfo'));
        for (var customerId in customer_updated_data) {
            if (customer_updated_data[customerId] != null) {
                if (customersInfo[customerId] == null)
                    customersInfo[customerId] = {};
                customersInfo[customerId] = customer_updated_data[customerId];
            }
        }
        $D.jStorage.set('customersInfo', JSON.stringify(customersInfo));
        searchCustomerFromLocal('');
    }
}

var getNumberProductSaved = function () {
    if ($D.jStorage.get('productsInfo') != null) {
        var productsInfo = JSON.parse($D.jStorage.get('productsInfo'));
        var prdArr = $D.map(productsInfo, function (value, index) {
            return [value];
        });
        return prdArr.length;
    }
    return 0;
}

var saveCustomersInfo = function (customerId, customerData) {
    var customersInfo = {};
    if ($D.jStorage.get('customersInfo') != null) {
        customersInfo = JSON.parse($D.jStorage.get('customersInfo'));
        customersInfo[customerId] = customerData;
        $D.jStorage.set('customersInfo', JSON.stringify(customersInfo));
    } else {
        var customersInfo = {};
        customersInfo[customerId] = customerData;
        $D.jStorage.set('customersInfo', JSON.stringify(customersInfo));
    }
}

var getCustomerInfo = function (customerId) {
    if (typeof webposDB != 'undefined') {
        webposDB.getCustomerById(customerId);
        return localGet('customer_result');
    }
    var customersInfo = {};
    if ($D.jStorage.get('customersInfo') != null) {
        customersInfo = JSON.parse($D.jStorage.get('customersInfo'));
        if (customersInfo[customerId] != null)
            return customersInfo[customerId];
    }
    return '';
}

var getAllCustomersInfo = function () {
    var productsInfo = {};
    if ($D.jStorage.get('customersInfo') != null) {
        customersInfo = JSON.parse($D.jStorage.get('customersInfo'));
        return customersInfo;
    }
    return '';
}

var getNumberCustomerSaved = function () {
    if ($D.jStorage.get('customersInfo') != null) {
        var customersInfo = JSON.parse($D.jStorage.get('customersInfo'));
        var prdArr = $D.map(customersInfo, function (value, index) {
            return [value];
        });
        return prdArr.length;
    }
    return 0;
}

var flushProductCache = function () {
    if (reloading_data == true) {
        showToastMessage('info', 'Message', 'Reloading data, please wait...');
        return;
    }
    if ($D('#checkout')) {
        $D('#checkout').click();
        if ($('settings_area'))
            $('settings_area').removeClassName('left0px');
    }
    indexedDB.deleteDatabase('webposLocalDB');
    localDelete('number_product_saved');
    localDelete('number_customer_saved');
    localDelete('products_results');
    localDelete('customers_results');
    localDelete('productsInfo');
    localDelete('customersInfo');
    localDelete('productlist');
    localDelete('updated_time');
    localDelete('webpos_custom_options');
    updateNumberPendingOrder();
    $('loading_status').innerHTML = '';
    number_product_saved = 0;
    number_customer_saved = 0;
	saved_tax_calculation = false;
	loading_data_type = 'product';
    showStatus(0, 0, 'firstTime');
    loadProductToLocal();
}

var searchProductByCategory = function (categoryId) {
    if (typeof webposDB != 'undefined') {
        webposDB.searchProductsByCategory(categoryId);
        return;
    }
    if (!$('product_list_wrapper')) {
        $('product_content').innerHTML = '\
			<div class="product_list">\
				<div class="product_list">\
					<ul class="product-slide" id="product_list_wrapper">\
					</ul>\
				</div>\
			</div>';
    }
    var productsInfo = getAllProductsInfo();
    if (productsInfo != '') {
        var results = [];
        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var categories = productData.category.split(',');
            if ($D.inArray(categoryId, categories) >= 0 || categoryId == '0') {
                results.push(productId);
            }
        }
        if (results.length == 0) {
            $('product_list_wrapper').innerHTML = "<li class='no-product'>There is no product</li>";
            return;
        }
        updateNumberProduct(results.length);
        $('product_list_wrapper').innerHTML = '';
        localSet('current_cat', categoryId);

        var maxKeyProduct = 0;
        var maxNewKey = 32;
        if (maxNewKey > results.length)
            maxNewKey = results.length;
        var countProduct = 1;
        var products = "<li class='rows'>";
        while (maxKeyProduct < maxNewKey) {
            if (results[maxKeyProduct] != null) {
                if (productsInfo[results[maxKeyProduct]] != null) {
                    var productData = productsInfo[results[maxKeyProduct]];
                    if (productData.html != null) {
                        var visibleProduct = $('product_list_wrapper').innerHTML;
                        products += productData.html;
                        if (countProduct == 4) {
                            $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                            products = "<li class='rows'>";
                            countProduct = 0;
                        } else if (results.length == maxNewKey && maxKeyProduct == (maxNewKey - 1)) {
                            $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                        }
                        loadProductImage();
                        countProduct++;
                        maxKeyProduct++;
                    }
                }
            }
        }
        localSet('maxKeyProduct', maxKeyProduct);
        localSet('productlist', $('product_content').innerHTML);
    }
}

var searchProductByKeyword = function (keyword) {
    if (keyword != null)
        keyword = keyword.toLowerCase();
    if (typeof webposDB != 'undefined') {
        webposDB.searchProductsByKeyword(keyword);
        return;
    }
    var productsInfo = getAllProductsInfo();
    if (productsInfo != '') {
        var categoryId = 0;
        if ($('category_dropdown'))
            categoryId = $('category_dropdown').getAttribute('selectedcategory');
        var results = [];
        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var categoryIds = productData.category.split(',');
            if (categoryId != 0 && typeof categoryId != 'undefined' && $D.inArray(categoryId, categoryIds) >= 0 && productData.searchstring.toLowerCase().indexOf(keyword) >= 0) {
                results.push(productId);
            } else
            if (productData.searchstring.toLowerCase().indexOf(keyword) >= 0) {
                results.push(productId);
            }
        }
        if (results.length == 0) {
            $('product_list_wrapper').innerHTML = "<li class='no-product'>There is no product</li>";
            return;
        }
        updateNumberProduct(results.length);
        $('product_list_wrapper').innerHTML = '';
        var maxKeyProduct = 0;
        var maxNewKey = results.length;
        var countProduct = 1;
        var products = "<li class='rows'>";
        while (maxKeyProduct < maxNewKey) {
            if (results[maxKeyProduct] != null) {
                if (productsInfo[results[maxKeyProduct]] != null) {
                    var productData = productsInfo[results[maxKeyProduct]];
                    var visibleProduct = $('product_list_wrapper').innerHTML;
                    products += productData.html;
                    if (countProduct == 4) {
                        $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                        products = "<li class='rows'>";
                        countProduct = 0;
                    } else if (maxNewKey < 4 && maxKeyProduct == (maxNewKey - 1)) {
                        $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                    }
                    loadProductImage();
                    countProduct++;

                }
            }
            maxKeyProduct++;
        }
        /**/
        var products = $$('#product_list_wrapper .product');
        if (products.length == 1) {
            var productItemId = products[0].getAttribute('id');
            if ($D('#' + productItemId + ' .item'))
                $D('#' + productItemId + ' .item').click();
        }
        /**/
    }
}

var loadmoreProductByCategory = function () {
    if (typeof webposDB != 'undefined') {
        webposDB.loadMoreProducts();
        return;
    }
    if (!$('product_list_wrapper')) {
        $('product_content').innerHTML = '\
			<div class="product_list">\
				<ul class="product-slide" id="product_list_wrapper">\
				</ul>\
			</div>';
    }
    var productsInfo = getAllProductsInfo();
    if (productsInfo != '') {
        var categoryId = localGet('current_cat');
        if (localGet('loading') == 'true') {
            return;
        } else {
            localSet('loading', 'true');
        }
        var results = [];

        for (var productId in productsInfo) {
            var productData = productsInfo[productId];
            var categories = productData.category;
            var categoryIds = categories.split(',');
            if ($D.inArray(categoryId, categoryIds) >= 0 || categoryId == '0') {
                results.push(productId);
            }
        }
        if (results.length == 0) {
            $('product_list_wrapper').innerHTML = "<li class='no-product'>There is no product</li>";
            return;
        }
        updateNumberProduct(results.length);
        var maxKeyProduct = 0;
        if (localGet('maxKeyProduct') != null) {
            maxKeyProduct = localGet('maxKeyProduct');
        }
        var maxNewKey = maxKeyProduct + 32;
        if (maxNewKey > results.length)
            maxNewKey = results.length;

        var countProduct = 1;
        var products = "<li class='rows'>";
        while (maxKeyProduct <= maxNewKey) {
            if (results[maxKeyProduct] != null) {
                if (productsInfo[results[maxKeyProduct]] != null) {
                    var productData = productsInfo[results[maxKeyProduct]];
                    if (productData.html != null) {
                        var visibleProduct = $('product_list_wrapper').innerHTML;
                        products += productData.html;
                        if (countProduct == 4) {
                            $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                            products = "<li class='rows'>";
                            countProduct = 0;
                        } else if (results.length == maxNewKey && maxKeyProduct == (maxNewKey - 1)) {
                            $('product_list_wrapper').innerHTML = visibleProduct + products + "</li>";
                        }
                        loadProductImage();
                        countProduct++;
                    }
                }
            }
            maxKeyProduct++;
        }
        localSet('maxKeyProduct', maxNewKey);
        localSet('loading', 'false');
    }
}

var loadMoreCustomerFromLocal = function () {
    if (typeof webposDB != undefined) {
        webposDB.loadMoreCustomers();
        return;
    }
    var customersInfo = getAllCustomersInfo();
    if (customersInfo != '') {
        if (localGet('loading') == 'true') {
            return;
        } else {
            localSet('loading', 'true');
        }
        var results = [];
        for (var customerId in customersInfo) {
            results.push(customerId);
        }
        if (results.length == 0) {
            $D('#popup-customer #customer_list').html("<li class='no-product'>There is no customer</li>");
            return;
        }
        var maxKeyCustomer = 0;
        if (localGet('maxKeyCustomer') != null) {
            maxKeyCustomer = localGet('maxKeyCustomer');
        }
        var newresult = '';
        var visibleCustomer = $D('#popup-customer #customer_list').html();
        $D('#popup-customer #customer_list').html('');
        var maxNewKey = maxKeyCustomer + 30;
        if (maxNewKey > results.length)
            maxNewKey = results.length;
        while (maxKeyCustomer <= maxNewKey) {
            if (results[maxKeyCustomer] != null) {
                if (customersInfo[results[maxKeyCustomer]] != null) {
                    var customerData = customersInfo[results[maxKeyCustomer]];
                    if (customerData.email != null) {
                        newresult += getCustomerHtml(results[maxKeyCustomer], customerData.email, customerData.firstname, customerData.lastname,customerData.telephone);
                    }
                }
            }
            maxKeyCustomer++;
        }
        $D('#popup-customer #customer_list').html(visibleCustomer + newresult)
        localSet('maxKeyCustomer', maxNewKey);
        localSet('loading', 'false');
    }
}

var searchCustomerFromLocal = function (keyword) {
    if (keyword != null)
        keyword = keyword.toLowerCase();
    if (typeof webposDB != 'undefined') {
        webposDB.searchCustomersByKeyword(keyword);
        return;
    }
    var customersInfo = getAllCustomersInfo();
    if (customersInfo != '') {
        var results = [];
        for (var customerId in customersInfo) {
            results.push(customerId);
        }
        if (results.length == 0) {
            $D('#popup-customer #customer_list').html("<li class='no-product'>There is no customer</li>");
            return;
        }
        var maxKeyCustomer = 0;
        var newresult = '';
        $D('#popup-customer #customer_list').html('');
        var maxNewKey = results.length;
        while (maxKeyCustomer <= maxNewKey) {
            if (results[maxKeyCustomer] != null) {
                if (customersInfo[results[maxKeyCustomer]] != null) {
                    var customerData = customersInfo[results[maxKeyCustomer]];
                    if (customerData.email != null) {
                        var telephone = (customerData.telephone != null) ? customerData.telephone.toLowerCase() : '';
                        var email = (customerData.email != null) ? customerData.email.toLowerCase() : '';
                        var firstname = (customerData.firstname != null) ? customerData.firstname.toLowerCase() : '';
                        var lastname = (customerData.lastname != null) ? customerData.lastname.toLowerCase() : '';
                        var searchstring = results[maxKeyCustomer] + " " + telephone + " " + email + " " + lastname + " " + firstname;
                        if (searchstring.indexOf(keyword) >= 0) {
                            newresult += getCustomerHtml(results[maxKeyCustomer], customerData.email, customerData.firstname, customerData.lastname,customerData.telephone);
                        }
                    }
                }
            }
            maxKeyCustomer++;
        }
        $D('#popup-customer #customer_list').html(newresult)
        localSet('maxKeyCustomer', maxNewKey);
    }
}

var saveBundleOption = function (productId, optionId, optionValue) {
    var bundle_options = {};
    if ($D.jStorage.get('bundle_options') != null) {
        bundle_options = JSON.parse($D.jStorage.get('bundle_options'));
        if (bundle_options[productId] == null)
            bundle_options[productId] = {};
        bundle_options[productId][optionId] = optionValue;
        $D.jStorage.set('bundle_options', JSON.stringify(bundle_options));
    } else {
        var bundle_options = {};
        bundle_options[productId] = {};
        bundle_options[productId][optionId] = optionValue;
        $D.jStorage.set('bundle_options', JSON.stringify(bundle_options));
    }
}

var getBundleOption = function (productId) {
    var bundle_options = {};
    if ($D.jStorage.get('bundle_options') != null) {
        bundle_options = JSON.parse($D.jStorage.get('bundle_options'));
        bundle_options[productId];
        return bundle_options[productId];
    }
    return '';
}

var getAllBundleOption = function () {
    $D.jStorage.reInit();
    var bundle_options = {};
    if ($D.jStorage.get('bundle_options') != null) {
        bundle_options = JSON.parse($D.jStorage.get('bundle_options'));
        return bundle_options;
    }
    return '';
}

var saveBundleOptionQty = function (productId, optionId, optionValue) {
    var bundle_options_qty = {};
    if ($D.jStorage.get('bundle_options_qty') != null) {
        bundle_options_qty = JSON.parse($D.jStorage.get('bundle_options_qty'));
        if (bundle_options_qty[productId] == null)
            bundle_options_qty[productId] = {};
        bundle_options_qty[productId][optionId] = optionValue;
        $D.jStorage.set('bundle_options_qty', JSON.stringify(bundle_options_qty));
    } else {
        var bundle_options = {};
        bundle_options_qty[productId] = {};
        bundle_options_qty[productId][optionId] = optionValue;
        $D.jStorage.set('bundle_options_qty', JSON.stringify(bundle_options_qty));
    }
}

var getBundleOptionQty = function (productId) {
    var bundle_options_qty = {};
    if ($D.jStorage.get('bundle_options_qty') != null) {
        bundle_options_qty = JSON.parse($D.jStorage.get('bundle_options_qty'));
        bundle_options_qty[productId];
        return bundle_options_qty[productId];
    }
    return '';
}

var getAllBundleOptionQty = function () {
    $D.jStorage.reInit();
    var bundle_options_qty = {};
    if ($D.jStorage.get('bundle_options_qty') != null) {
        bundle_options_qty = JSON.parse($D.jStorage.get('bundle_options_qty'));
        return bundle_options_qty;
    }
    return '';
}

var getCustomerHtml = function (customerId, email, firstname, lastname,telephone) {
    var html = "<li id='customer_result_" + customerId + "' firstname='" + firstname + "' lastname='" + lastname + "' email='" + email + "' customerid='" + customerId + "' onclick=\"addCustomerToCart(" + customerId + ")\" style='width:100%; float:left; cursor: pointer' class='email-customer col-lg-6 col-md-6'><span style='float:left;'>" + firstname + " " + lastname + "</span><span style='float:right'><a href='#'>" + email + "</a></span><br/><span style='float:right'>" + telephone + "</span></li>";
    return html;
}

var fillCustomerDataFromLocal = function (addressType) {
    var customerInCart = localGet('customerInCart');
    if (customerInCart != null) {
        var billingAddress = customerInCart.billingAddress;
        var shippingAddress = customerInCart.shippingAddress;
        if (addressType == 'billingAddress' && billingAddress != null) {
            var billingInputs = $$('.billingdata');
            if (billingInputs.length > 0)
                billingInputs.each(function (input) {
                    var fieldkey = input.getAttribute('fieldkey');
                    if (fieldkey == 'street1')
                        input.value = '';
                    else if (fieldkey == 'street0')
                        fieldkey = 'street';
                    if (fieldkey != null && billingAddress[fieldkey] != null) {
                        input.value = billingAddress[fieldkey];
                    }
                    if (fieldkey == 'email')
                        input.value = customerInCart.email;
                });
        }
        if (addressType == 'shippingAddress' && shippingAddress != null) {
            var shippingInputs = $$('.shippingdata');
            if (shippingInputs.length > 0)
                shippingInputs.each(function (input) {
                    var fieldkey = input.getAttribute('fieldkey');
                    if (fieldkey == 'street1')
                        input.value = '';
                    else if (fieldkey == 'street0')
                        fieldkey = 'street';
                    if (fieldkey != null && shippingAddress[fieldkey] != null) {
                        input.value = shippingAddress[fieldkey];
                    }
                });
        }
    }
}

var saveCustomerDataToLocal = function (addressType) {
    var customerInCart = localGet('customerInCart');
    if (customerInCart != null) {
        var billingAddress = customerInCart.billingAddress;
        var shippingAddress = customerInCart.shippingAddress;
        if (addressType == 'billingAddress' && billingAddress != null) {
            var streetString = '';
            var billingInputs = $$('.billingdata');
            if (billingInputs.length > 0)
                billingInputs.each(function (input) {
                    var fieldkey = input.getAttribute('fieldkey');
                    if (fieldkey.indexOf('street') >= 0) {
                        streetString += input.value + ' ';
                    } else
                    if (fieldkey != null && billingAddress[fieldkey] != null) {
                        billingAddress[fieldkey] = input.value;
                    }
                });
            billingAddress['street'] = streetString;
        }
        if (addressType == 'shippingAddress' && shippingAddress != null) {
            var streetString = '';
            var shippingInputs = $$('.shippingdata');
            if (shippingInputs.length > 0)
                shippingInputs.each(function (input) {
                    var fieldkey = input.getAttribute('fieldkey');
                    if (fieldkey.indexOf('street') >= 0) {
                        streetString += input.value + ' ';
                    } else
                    if (fieldkey != null && shippingAddress[fieldkey] != null) {
                        shippingAddress[fieldkey] = input.value;
                    }
                });
            shippingAddress['street'] = streetString;
        }
        customerInCart.billingAddress = billingAddress;
        customerInCart.shippingAddress = shippingAddress;
        localSet('customerInCart', customerInCart);
    }
}

var saveOrderToLocal = function (parameters) {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId] != null) {
            var orderArr = $D.map(pending_orders['user_' + currentUserId], function (value, index) {
                return [value];
            });
            var numberOrderSaved = orderArr.length;
        } else
            numberOrderSaved = 0;
        var newnumber = numberOrderSaved + 1;
        if (pending_orders['user_' + currentUserId] == null)
            pending_orders['user_' + currentUserId] = {};
        while (pending_orders['user_' + currentUserId]['order_' + newnumber] != null) {
            newnumber++;
        }
        pending_orders['user_' + currentUserId]['order_' + newnumber] = parameters;
        $D.jStorage.set('pending_orders', JSON.stringify(pending_orders));
        localSet('last_pending_order', 'order_' + newnumber);
    } else {
        var pending_orders = {};
        pending_orders['user_' + currentUserId] = {'order_0': parameters};
        $D.jStorage.set('pending_orders', JSON.stringify(pending_orders));
        localSet('last_pending_order', 'order_0');
    }
    number_pending_order = getNumberPendingOrders();
}

var savePendingOrder = function (key, parameters) {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId][key] != null) {
            pending_orders['user_' + currentUserId][key] = parameters;
            $D.jStorage.set('pending_orders', JSON.stringify(pending_orders));
        }
    }
    number_pending_order = getNumberPendingOrders();
    return '';
}

var getPendingOrder = function (key) {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId][key] != null)
            return pending_orders['user_' + currentUserId][key];
    }
    return '';
}

var getAllPendingOrders = function () {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId] != null)
            return pending_orders['user_' + currentUserId];
    }
    return [];
}

var deletePendingOrder = function (key) {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId][key] != null)
            delete  pending_orders['user_' + currentUserId][key];
        $D.jStorage.set('pending_orders', JSON.stringify(pending_orders));
    }
}

var getNumberPendingOrders = function () {
    var pending_orders = {};
    if ($D.jStorage.get('pending_orders') != null) {
        pending_orders = JSON.parse($D.jStorage.get('pending_orders'));
        if (pending_orders['user_' + currentUserId] != null) {
            var orderArr = $D.map(pending_orders['user_' + currentUserId], function (value, index) {
                return [value];
            });
            return numberOrderSaved = orderArr.length;
        }
    }
    return 0;
}

var saveConfigurableOptions = function (productId, optionCode, optionValue, optionLabel) {
    var configurable_options = {};
    if ($D.jStorage.get('configurable_options') != null) {
        configurable_options = JSON.parse($D.jStorage.get('configurable_options'));
        if (configurable_options[productId] == null)
            configurable_options[productId] = {};
        if (configurable_options[productId][optionCode] == null)
            configurable_options[productId][optionCode] = {};
        configurable_options[productId][optionCode]['value'] = optionValue;
        configurable_options[productId][optionCode]['label'] = optionLabel;
        $D.jStorage.set('configurable_options', JSON.stringify(configurable_options));
    } else {
        var configurable_options = {};
        configurable_options[productId] = {};
        configurable_options[productId][optionCode] = {};
        configurable_options[productId][optionCode]['value'] = optionValue;
        configurable_options[productId][optionCode]['label'] = optionLabel;
        $D.jStorage.set('configurable_options', JSON.stringify(configurable_options));
    }
}

var updateIdConfigurableOptions = function (productId, newId) {
    var configurable_options = {};
    if ($D.jStorage.get('configurable_options') != null) {
        configurable_options = JSON.parse($D.jStorage.get('configurable_options'));
        if (configurable_options[productId] != null) {
            configurable_options[newId] = configurable_options[productId];
            delete configurable_options[productId];
            $D.jStorage.set('configurable_options', JSON.stringify(configurable_options));
        }
    }
}

var getConfigurableOptions = function (productId) {
    var configurable_options = {};
    if ($D.jStorage.get('configurable_options') != null) {
        configurable_options = JSON.parse($D.jStorage.get('configurable_options'));
        if (configurable_options[productId] != null)
            return configurable_options[productId];
    }
    return '';
}

var saveWebposNewCustomOption = function(productId,custom_options,index){
	if ($D.jStorage.get('webpos_custom_options') != null) {
		var webpos_custom_options = JSON.parse($D.jStorage.get('webpos_custom_options'));
		if(webpos_custom_options[productId]== null){
			webpos_custom_options[productId] = {};
		}
		webpos_custom_options[productId][index] = custom_options;
		$D.jStorage.set('webpos_custom_options', JSON.stringify(webpos_custom_options));
	}else{
		var webpos_custom_options = {};
		if(webpos_custom_options[productId]== null){
			webpos_custom_options[productId] = {};
		}
		webpos_custom_options[productId][index] = custom_options;
		$D.jStorage.set('webpos_custom_options', JSON.stringify(webpos_custom_options));
	}				
}

var getWebposNewCustomOption = function(){
	var webpos_custom_options = {};
	if ($D.jStorage.get('webpos_custom_options') != null) {
		var webpos_custom_options = JSON.parse($D.jStorage.get('webpos_custom_options'));
	}			
	return webpos_custom_options;	
}