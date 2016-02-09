/**************************** DANIEL LOCALDB **************************/
if(typeof server =='undefined') {
    var server = '';
}

if(typeof DanielLocalDB =='undefined') {
    var DanielLocalDB = {};
}
DanielLocalDB.indexedDB = Class.create();
DanielLocalDB.indexedDB.prototype = { 
    
    initialize: function(){
        this.connectDatabase('webposLocalDB',1);
    },
     
    connectDatabase: function(dbName,version){
        db.open( {
			server: dbName,
			version: version,
			schema: {
				products: {
					key: { keyPath: 'productId'},
					indexes: {
						productId: { unique: true },
						category: {},
						outofstock: {}
					}
				},
				customers: {
					key: { keyPath: 'customerId'},
					indexes: {
						customerId: { unique: true },
						email: {},
						firstname: {},
						lastname: {},
						telephone: {},
						taxRate: {}
					}
				}
			}
		}).then( function(s){
			server = s;
		});      
    },
    
	addProduct: function(productJsonObj){
		if(typeof productJsonObj == 'undefined' || productJsonObj == '' || productJsonObj == null) return;
		server.products.add(productJsonObj);
		server.products.query().all().execute().then(function(p){
			number_product_saved = p.length;
			localSet('number_product_saved',p.length);
		});
	},
	
	saveProducts: function(products){
		if(typeof products == 'undefined' || products == '' || products == null || products.length <= 0) return;
		products.forEach(function(product){
			var productId = product.productId;
			server.products.query('productId').only(productId).execute().then(function(p){
				if(typeof p[0] != 'undefined' && p[0].productId == productId){
					server.products.query('productId').only(productId).modify(product).execute().then(function(e){});
				}else{
					server.products.add(product);
				}



			});
		});
	},
	
	saveCustomers: function(customers){
		if(typeof customers == 'undefined' || customers == '' || customers == null || customers.length <= 0) return;
		customers.forEach(function(customer){
			var customerId = customer.customerId;
			server.customers.query('customerId').only(customerId).execute().then(function(c){
				if(typeof c[0] != 'undefined' && c[0].customerId == customerId){
					server.customers.query('customerId').only(customerId).modify(customer).execute().then(function(e){});
				}else{
					server.customers.add(customer);
				}



			});
		});
	},
	
	updateNumberSavedProduct: function(){
		if(typeof server.products == 'undefined'){
			number_product_saved = 0;
			localSet('number_product_saved',0);
			return;
		}
		server.products.query().all().execute().then(function(p){
			number_product_saved = p.length;
			localSet('number_product_saved',p.length);
		});
	},
	updateNumberSavedCustomer: function(){
		if(typeof server.customers == 'undefined'){
			number_customer_saved = 0;
			localSet('number_customer_saved',0);
			return;
		}
		server.customers.query().all().execute().then(function(c){
			number_customer_saved = c.length;
			localSet('number_customer_saved',c.length);
		});
	},
	
	addCustomer: function(customerJsonObj){
		if(typeof customerJsonObj == 'undefined' || customerJsonObj == '' || customerJsonObj == null) return;
		server.customers.add(customerJsonObj);
		server.customers.query().all().execute().then(function(c){
			number_customer_saved = c.length;
			localSet('number_customer_saved',c.length);
		});
	},
    
	updateProduct: function(productId,productJsonObj){
		if(typeof productId == 'undefined' || productId == '' || productId == null) return;
		if(typeof productJsonObj == 'undefined' || productJsonObj == '' || productJsonObj == null) return;
		server.products.query('productId').only(productId).modify(productJsonObj).execute().then(function(e){
			console.log(e);
		});
	},
	
	updateCustomer: function(customerId,customerJsonObj){
		if(typeof customerId == 'undefined' || customerId == '' || customerId == null) return;
		if(typeof customerJsonObj == 'undefined' || customerJsonObj == '' || customerJsonObj == null) return;
		server.customers.query('customerId').only(customerId).modify(customerJsonObj).execute();
	},
	
	removeProduct: function(productId){
		if(typeof productId == 'undefined' || productId == '' || productId == null) return;
		server.products.remove(productId).execute();
		server.products.query().all().execute().then(function(p){
			number_product_saved = p.length;
			localSet('number_product_saved',p.length);
		});
	},
	
	removeCustomer: function(customerId){
		if(typeof customerId == 'undefined' || customerId == '' || customerId == null) return;
		server.customers.remove(customerId).execute();
		server.customers.query().all().execute().then(function(c){
			number_customer_saved = c.length;
			localSet('number_customer_saved',c.length);
		});
	},
	
	searchProductsByCategory:function(categoryId){
		if(typeof categoryId == 'undefined' || categoryId == '' || categoryId == null) return;
		showColleftAjaxloader();
		if(categoryId == 0 || categoryId == '0'){
			server.products.query().filter(function(product){
				return (product.outofstock == false || (show_outofstock == '1' && product.outofstock == true));
			}).execute().then(function(results){
				localSet('current_cat',categoryId);
				localSet('products_results',results);
				fillProductSearchResult(results,'false');
			});
			return;
		}
		server.products.query().filter(function(product){
			var categories = product.category.split(',');
			return ($D.inArray(categoryId,categories) >= 0 && (product.outofstock == false || (show_outofstock == '1' && product.outofstock == true)));
		}).execute().then(function(results){
			localSet('current_cat',categoryId);
			localSet('products_results',results);
			fillProductSearchResult(results,'false');
			
		});
	},
	searchProductsByKeyword:function(keyword){
		var categoryId = 0;
		if($('category_dropdown')) categoryId = $('category_dropdown').getAttribute('selectedcategory');
		if(typeof keyword == 'undefined' || keyword == null) return;
		keyword = keyword.toLowerCase();
		showColleftAjaxloader();
		server.products.query().filter(function(product){
			if(categoryId != 0 && typeof categoryId != 'undefined'){
				var categories = product.category.split(',');
				return ( $D.inArray(categoryId,categories) >= 0 && product.searchstring.toLowerCase().indexOf(keyword) >= 0 && (product.outofstock == false || (show_outofstock == '1' && product.outofstock == true)));
			}
			return (product.searchstring.toLowerCase().indexOf(keyword) >= 0 && (product.outofstock == false || (show_outofstock == '1' && product.outofstock == true)));
		}).execute().then(function(results){
			localSet('products_results',results);
			fillProductSearchResult(results,'true');
		});
	},
	
	searchCustomersByKeyword:function(keyword){
		if(typeof keyword == 'undefined' || keyword == null) return;
		keyword = keyword.toLowerCase();
		server.customers.query().filter(function(customer){
			var searchstring = customer.customerId +' '+ customer.telephone +' '+ customer.firstname  +' '+ customer.lastname +' '+ customer.email;
			return (searchstring.toLowerCase().indexOf(keyword) >= 0);
		}).execute().then(function(results){
			localSet('customers_results',results);
			fillCustomerSearchResult(results);
		});
	},
	loadMoreProducts:function(){
		var results = localGet('products_results');
		if(typeof results != 'undefined'){
			//fillMoreProductsResult(results);
			//return;
		}
		var categoryId = localGet('current_cat');
		if(categoryId == 0 || categoryId == '0'){
			server.products.query().filter(function(product){
				return (product.outofstock == false || (show_outofstock == '1' && product.outofstock == true));
			}).execute().then(function(results){
				localSet('current_cat',categoryId);
				localSet('products_results',results);
				fillMoreProductsResult(results);
			});
			return;
		}
		server.products.query().filter(function(product){
			return product.category.indexOf(categoryId) >= 0;
		}).execute().then(function(results){
			localSet('current_cat',categoryId);
			localSet('products_results',results);
			fillMoreProductsResult(results);
		});
	},
	loadMoreCustomers:function(){
		var results = localGet('customers_results');
		if(typeof results != 'undefined'){
			//fillMoreCustomersResult(results);
			//return;
		}
		server.customers.query().all().execute().then(function(results){
			fillMoreCustomersResult(results);
		});
	},
	getProductById:function(productId){
		if(typeof productId == 'undefined' || productId == null) return;
		productId = productId.toString();
		server.products.get(productId).then(function(product){
			if(product.outofstock == false || (show_outofstock == '1' && product.outofstock == true)){
				localSet('product_result',product);
			}
		});
	},
	getCustomerById:function(customerId){
		if(typeof customerId == 'undefined' || customerId == null) return;
		customerId = customerId.toString();
		server.customers.get(customerId).then(function(customer){
			localSet('customer_result',customer);
		});
	},
	clearAllProducts:function(){
		server.products.clear();
		number_product_saved = 0;
		localSet('number_product_saved',0);
		localDelete('products_results');
	},
	clearAllCustomers:function(){
		server.customers.clear();
		number_customer_saved = 0;
		localSet('number_customer_saved',0);
		localDelete('customers_results');
	}
}

function fillProductSearchResult(results,autoAdd){
	if(!$('product_list_wrapper')){
		$('product_content').innerHTML = '\
			<div class="product_list">\
				<div class="product_list">\
					<ul class="product-slide" id="product_list_wrapper">\
					</ul>\
				</div>\
			</div>';
	}
	if(results.length == 0){
		hideColleftAjaxloader();
		$('product_list_wrapper').innerHTML = "<li class='no-product'>There is no product</li>";
		return;
	}
	updateNumberProduct(results.length);
	$('product_list_wrapper').innerHTML = '';
	var maxKeyProduct = 0;
	var maxNewKey = 32;
	if(maxNewKey > results.length) maxNewKey = results.length;
	var countProduct = 1;
	var products = "<li class='rows'>";
	for(var maxKeyProduct = 0; maxKeyProduct < maxNewKey; maxKeyProduct++){
		if(results[maxKeyProduct] != null){
			var productData = results[maxKeyProduct];
			if(productData.html != null){
				var visibleProduct = $('product_list_wrapper').innerHTML;
				products += productData.html;
				if(countProduct == 4){
					$('product_list_wrapper').innerHTML = visibleProduct + products +"</li>";
					products = "<li class='rows'>";
					countProduct = 0;
				}else if(results.length == maxNewKey && maxKeyProduct == (maxNewKey -1)){
					$('product_list_wrapper').innerHTML = visibleProduct + products +"</li>";
				}
				loadProductImage();
				countProduct++;
			}
		}
	}
	localSet('maxKeyProduct',maxKeyProduct);
	localSet('productlist',$('product_content').innerHTML);
	
	if(autoAdd == 'true'){
		var products = $$('#product_list_wrapper .product');
		if(products.length == 1){
			var productItemId = products[0].getAttribute('id');
			if($D('#'+productItemId+' .item')) $D('#'+productItemId+' .item').click();
		}
	}
	hideColleftAjaxloader();
}

function fillCustomerSearchResult(results){
	if(results.length == 0){
		$D('#popup-customer #customer_list').html("<li class='no-product'>There is no customer</li>");
		return;
	}
	var maxKeyCustomer = 0;
	var newresult = '';
	$D('#popup-customer #customer_list').html('');
	var maxNewKey = results.length;
	for(var maxKeyCustomer = 0; maxKeyCustomer < maxNewKey; maxKeyCustomer++){
		if(results[maxKeyCustomer] != null){	
			var customerData = results[maxKeyCustomer];
			if(customerData.email != null){
				newresult += getCustomerHtml(customerData.customerId,customerData.email,customerData.firstname,customerData.lastname,customerData.telephone);
			}
		}
	}
	$D('#popup-customer #customer_list').html(newresult)
	localSet('maxKeyCustomer',maxNewKey);
}

function fillMoreProductsResult(results){
	if(!$('product_list_wrapper')){
		$('product_content').innerHTML = '\
			<div class="product_list">\
				<div class="product_list">\
					<ul class="product-slide" id="product_list_wrapper">\
					</ul>\
				</div>\
			</div>';
	}
	if(localGet('loading') == 'true'){
		return;
	}else{
		localSet('loading','true');
	}
	if(results.length == 0){
		hideColleftAjaxloader();
		$('product_list_wrapper').innerHTML = "<li class='no-product'>There is no product</li>";
		return;
	}
	showColleftAjaxloader();
	updateNumberProduct(results.length);
	var maxKeyProduct = 0;
	if(localGet('maxKeyProduct') != null){
		maxKeyProduct = localGet('maxKeyProduct');
	}
	var maxNewKey = maxKeyProduct + 32;
	if(maxNewKey > results.length) maxNewKey = results.length;
		
	var countProduct = 1;
	var products = "<li class='rows'>";
	for(var maxKeyProduct = maxKeyProduct; maxKeyProduct < maxNewKey; maxKeyProduct++){
		if(results[maxKeyProduct] != null){
			var productData = results[maxKeyProduct];
			if(productData.html != null){
				var visibleProduct = $('product_list_wrapper').innerHTML;
				products += productData.html;
				if(countProduct == 4){
					$('product_list_wrapper').innerHTML = visibleProduct + products +"</li>";
					products = "<li class='rows'>";
					countProduct = 0;
				}else if(results.length == maxNewKey && maxKeyProduct == (maxNewKey -1)){
					$('product_list_wrapper').innerHTML = visibleProduct + products +"</li>";
				}
				loadProductImage();
				countProduct++;
			}
		}
	}
	localSet('maxKeyProduct',maxNewKey);
	localSet('loading','false');
	hideColleftAjaxloader();
}

function fillMoreCustomersResult(results){
	if(localGet('loading') == 'true'){
		console.log('returned');
		return;
	}else{
		localSet('loading','true');
	}
	if(results.length == 0){
		$D('#popup-customer #customer_list').html("<li class='no-product'>There is no customer</li>");
		return;
	}
	var maxKeyCustomer = 0;
	if(localGet('maxKeyCustomer') != null){
		maxKeyCustomer = localGet('maxKeyCustomer');
	}
	var newresult = '';
	var visibleCustomer = $D('#popup-customer #customer_list').html();
	$D('#popup-customer #customer_list').html('');
	var maxNewKey = maxKeyCustomer + 30;
	if(maxNewKey > results.length) maxNewKey = results.length;
	while(maxKeyCustomer <= maxNewKey){
		if(results[maxKeyCustomer] != null){	
			var customerData = results[maxKeyCustomer];
				if(customerData.email != null){
					newresult += getCustomerHtml(customerData.customerId,customerData.email,customerData.firstname,customerData.lastname,customerData.telephone);
			}
		}
		maxKeyCustomer++;
	}
	$D('#popup-customer #customer_list').html(visibleCustomer + newresult)
	localSet('maxKeyCustomer',maxNewKey);
	localSet('loading','false');
}