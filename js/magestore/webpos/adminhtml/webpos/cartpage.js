/**********Js For Ajaxcart In Cart Page**********/

var Ajaxcartpage = Class.create();
Ajaxcartpage.prototype = {
	initialize: function(ajaxMask,ajaxPopup,popupContent,cartPage,links,instanceName){
		this.ajaxMask = ajaxMask;
		this.ajaxPopup = ajaxPopup;
		this.popupContent = popupContent;
		this.cartPage = cartPage;
		this.links = links;
		this.instanceName = instanceName;
		
		this.jsSource = [];
		this.jsCache = [];
		this.jsCount = 0;		
		this.intervalCache = 0;
		
		this.ajaxOnComplete = this.ajaxOnComplete.bindAsEventListener(this);
		this.addJsSource = this.addJsSource.bindAsEventListener(this);
		this.updateJscartEvent = this.updateJscartEvent.bindAsEventListener(this);
	},
	getCartPage: function(){
		if (!this.objCartPage){
			if ($$(this.cartPage).first()){
				this.objCartPage = $$(this.cartPage).first();
			}
		}
		return this.objCartPage;
	},
	addToCartHandle: function(requestUrl, params){
		this.url = requestUrl;
		if(window.location.href.match('https://') && !requestUrl.match('https://'))
			requestUrl = requestUrl.replace('http://','https://');
		if(!window.location.href.match('https://') && requestUrl.match('https://'))
			requestUrl = requestUrl.replace('https://','http://');
		if (requestUrl.indexOf('?') != -1)
			requestUrl += '&iswebposcart=true';
		else
			requestUrl += '?iswebposcart=true';
		if (this.getCartPage())
			requestUrl += '&iswebposcartpage=1'
		if (this.links)
			requestUrl += '&webposlinks=1';
		
		/* Daniel - updated - check error */
		if(params == 'fromsearch')
			requestUrl += '&tempadd=1';
                $(this.ajaxMask).show();
		/* end */
		
		new Ajax.Request(requestUrl,{
			method: 'post',
			postBody: params,
			parameters: params,
			onException: function (xhr, e){
				if(this.ajaxMask)
					$(this.ajaxMask).hide();
				if(this.ajaxPopup)
					$(this.ajaxPopup).hide();
				// window.location.href = this.url;
				window.location.href = redirect_error;
			},
			onComplete: this.ajaxOnComplete
		});
	},
	ajaxOnComplete: function(xhr){
		if (xhr.responseText.isJSON()){
			var response = xhr.responseText.evalJSON();
			/* notify that the product is out of stock */
			if(response.outofstock){
				alert("This product is currently out of stock!");
				$(this.ajaxMask).hide();
				$(this.ajaxPopup).hide();
			}else{
			/* end */
				if (response.hasOptions) {
					if (response.redirectUrl) this.addToCartHandle(response.redirectUrl,'');
					else this.popupContentWindow(response);
				} else {
					this.addToCartFinish(response);
				}
				/* Daniel - updated - check error */
				if (response.error) {
					alert(response.error);
				}
				/* end */
			/* notify that the product is out of stock */
			}
			/* end */
		} else {
			$(this.ajaxMask).hide();
			$(this.ajaxPopup).hide();
			// window.location.href = this.url;
			window.location.href = redirect_error;
		}
	},
	addToCartFinish: function(response){
		if (this.getCartPage() && response.cartPage){
			if (response.emptyCart){
				this.getCartPage().update(response.cartPage);
			} else {
				$(this.popupContent).innerHTML = response.cartPage;
				ajaxcartUpdateCartHtml(this.getCartPage(),$(this.popupContent));
				$(this.popupContent).innerHTML = '';
				this.updateJscartEvent();
			}
		}
		if (this.links && response.ajaxlinks){
			this.links.update(response.ajaxlinks);
			this.links.innerHTML = this.links.firstChild.innerHTML;
		}
		$(this.ajaxMask).hide();
		$(this.ajaxPopup).hide();
		/* Daniel - check cart empty */
		if($('checkcart-empty'))
			$('checkcart-empty').value = "0";
		/* end */
		save_address_information(save_address_url);
		if($('giftwrap_price'))
			reloadGiftwrap();
	},
	popupContentWindow: function(response){
		if (response.optionjs){
			for (var i=0;i<response.optionjs.length;i++){
				var pattern = 'script[src="'+response.optionjs[i]+'"]';
				if ($$(pattern).first()) continue;
				this.jsSource[this.jsSource.length] = response.optionjs[i];
			}
		}
		if (response.optionhtml){
			$(this.popupContent).innerHTML = response.optionhtml;
			this.jsCache = response.optionhtml.extractScripts();

					  var widthSite = document.viewport.getWidth();
					  if(widthSite <= 450) {
					  var els = $$('#product-options-wrapper p.required');
					  if(els.length > 0){
					   for(var i = 0; i < els.length ; i++){
						els[i].style.top = '25px';
						els[i].style.left = '15px';
					   }
					  }
					  
					  var els2 = $$('#product-options-wrapper dl dt');
					  if(els2.length > 0){
					   for(var i = 0; i < els2.length ; i++){
						els2[i].style.height = '35px';
					   }
					  }
					 }
		}
		this.intervalCache = setInterval(this.addJsSource,500);
	},
	addJsSource: function(){
		if (this.jsCount == this.jsSource.length){
			this.jsSource = [];
			this.jsCount = 0;
			clearInterval(this.intervalCache);
			this.addJsScript();
		} else {
			var headDoc = $$('head').first();
			var jsElement = new Element('script');
			jsElement.src = this.jsSource[this.jsCount];
			headDoc.appendChild(jsElement);
			this.jsCount++;
		}
	},
	addJsScript: function(){
		if (this.jsCache.length == 0) return false;
		try {
			for (var i=0;i<this.jsCache.length;i++){
				var script = this.jsCache[i];
				var headDoc = $$('head').first();
				var jsElement = new Element('script');
				jsElement.type = 'text/javascript';
				jsElement.text = script;
				headDoc.appendChild(jsElement);
			}
			this.jsCache = [];
			$(this.ajaxMask).hide();
			$(this.ajaxPopup).show();
			var content = $(this.popupContent);
			content.style.removeProperty('top');
			if (content.offsetHeight + content.offsetTop > window.innerHeight){
				content.style.position = 'absolute';
				content.style.top = window.pageYOffset+'px';
			}else{
				content.style.position = 'fixed';
			}
			ajaxMoreTemplateJs();

		} catch (e){}
	},
	updateJscartEvent: function(){
		var instanceName = this.instanceName;
		$$('a').each(function(el){
			if (el.href.search('/checkout/cart/delete/') != -1
				|| el.href.search('/checkout/cart/configure/') != -1)
					el.href = "javascript:"+instanceName+".addToCartHandle('"+el.href+"','')";
		});
		ajaxUpdateFormAction();
		if($('p_w') && $('p_w').value)
			updatewithpos();
	}
}