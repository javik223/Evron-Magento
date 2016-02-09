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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/********** Update JS Prototype - Fix for Magento 1.4 **********/
if (typeof Element.clone == 'undefined') {
    Element.clone = function (element, deep) {
        if (!(element = $(element))) return;
        var clone = element.cloneNode(deep);
        clone._prototypeUID = void 0;
        if (deep) {
            var descendants = Element.select(clone, '*'),
            i = descendants.length;
            while (i--) {
                descendants[i]._prototypeUID = void 0;
            }
        }
        return Element.extend(clone);
    };
}

/********** Reward Points Slider **********/
var RewardPointsSlider = Class.create();
RewardPointsSlider.prototype = {
    initialize: function(pointEl, trackEl, handleEl, zoomOutEl, zoomInEl, pointLbl, itemId) {
        this.pointEl = $(pointEl);
        this.trackEl = $(trackEl);
        this.handleEl = $(handleEl);
        this.pointLbl = $(pointLbl);
        this.itemId = itemId;
        
        this.minPoints = 0;
        this.maxPoints = 1;
        this.pointStep = 1;
        
        this.slider = new Control.Slider(this.handleEl, this.trackEl, {
            axis:'horizontal',
            range: $R(this.minPoints, this.maxPoints),
            values: this.availableValue(),
            onSlide: this.changePoint.bind(this),
            onChange: this.changePoint.bind(this)
        });
        this.changePointCallback = function(v, id){};
        
        Event.observe($(zoomOutEl), 'click', this.zoomOut.bind(this));
        Event.observe($(zoomInEl), 'click', this.zoomIn.bind(this));
    },
    availableValue: function() {
        var values = [];
        for (var i = this.minPoints; i <= this.maxPoints; i += this.pointStep) {
            values.push(i);
        }
        return values;
    },
    applyOptions: function(options) {
        this.minPoints = options.minPoints || this.minPoints;
        this.maxPoints = options.maxPoints || this.maxPoints;
        this.pointStep = options.pointStep || this.pointStep;
        
        this.slider.range = $R(this.minPoints, this.maxPoints);
        this.slider.allowedValues = this.availableValue();
        
        this.manualChange(this.slider.value);
    },
    changePoint: function(points) {
        this.pointEl.value = points;
        if (this.pointLbl) {
            this.pointLbl.innerHTML = points;
        }
        if (typeof this.changePointCallback == 'function') {
            this.changePointCallback(points, this.itemId);
        }
    },
    zoomOut: function() {
        var curVal = this.slider.value - this.pointStep;
        if (curVal >= this.minPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    zoomIn: function() {
        var curVal = this.slider.value + this.pointStep;
        if (curVal <= this.maxPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    manualChange: function(points) {
        points = this.slider.getNearestValue(parseInt(points));
        this.slider.value = points;
        this.slider.setValue(points);
        this.changePoint(points);
    },
    changeUseMaxpoint: function(event) {
        var checkEl = event.element();
        if (checkEl.checked) {
            this.manualChange(this.maxPoints);
        } else {
            this.manualChange(0);
        }
    },
    changeUseMaxpointEvent: function(checkEl) {
        Event.observe($(checkEl), 'click', this.changeUseMaxpoint.bind(this));
    },
    manualChangePoint: function(event) {
        var changeEl = event.element();
        this.manualChange(changeEl.value);
    },
    manualChangePointEvent: function(changeEl) {
        Event.observe($(changeEl), 'change', this.manualChangePoint.bind(this));
    }
}

/********** Reward Points Unique Ajax Request **********/
var RewardPointsAjax = Class.create();
RewardPointsAjax.prototype = {
    initialize: function(url) {
        this.addUrl(url);
        this.isRunningRequest = false;
        this.hasWaitingRequest = false;
    },
    addUrl: function(url) {
        if (window.location.href.match('https://') && !url.match('https://')) {
            url = url.replace('http://', 'https://');
        }
        if (!window.location.href.match('https://') && url.match('https://')) {
            url = url.replace('https://', 'http://');
        }
        this.url = url;
    },
    Request: function(url, config) {
        this.addUrl(url);
        this.config = config;
        this.ReRequest();
    },
    NewRequest: function(config) {
        this.config = config;
        this.ReRequest();
    },
    ReRequest: function() {
        if (this.isRunningRequest) {
            this.hasWaitingRequest = true;
            return;
        }
        this.isRunningRequest = true;
        config = this.config;
        if (typeof this.config.beforeRequest == "function") {
            this.config.beforeRequest();
        }
        if (typeof this.config.onComplete == "function") {
            this.orgComplete = this.config.onComplete;
        } else {
            this.orgComplete = function(response){};
        }
        config.onComplete = this.onComplete.bind(this);
        new Ajax.Request(this.url, config);
    },
    onComplete: function(response) {
        // this.orgComplete(response);
        this.isRunningRequest = false;
        if (this.hasWaitingRequest) {
            this.hasWaitingRequest = false;
            this.ReRequest();
        } else {
            this.orgComplete(response);
        }
    }
}

/********** Reward Points Slider **********/
var WebposRewardPointsItem = Class.create();
WebposRewardPointsItem.prototype = {
    initialize: function(itemId, rewardProductRules, convertPrice, jsonEncode) {      
        this.currentRuleOptions = null;        
        
        this.itemId = itemId;
        this.spendPopup = new Window({windowClassName:'webpos-dialog-item-rule', title:'Spend Points',zIndex:100, width:404,minimizable:false,maximizable:false,showEffectOptions:{duration:0.4},hideEffectOptions:{duration:0.4}, resizable:false, destroyOnClose: true});
        this.spendPopupId = this.spendPopup.getId();
        
        $('webpos-spend-points'+this.itemId).show();
        $('rewardpoints-slider-container'+this.itemId).show();
        this.rewardSlider = new RewardPointsSlider(
                                'reward_product_point'+this.itemId,
                                'rewardpoints-track'+this.itemId,
                                'rewardpoints-handle'+this.itemId,
                                'rewardpoints-slider-zoom-out'+this.itemId,
                                'rewardpoints-slider-zoom-in'+this.itemId,
                                'rewardpoints-slider-label'+this.itemId,
                                this.itemId
                            );
        this.rewardSlider.changePointCallback = changePointCallback;
        $('webpos-spend-points'+this.itemId).hide();
        $('rewardpoints-slider-container'+this.itemId).hide();
        
        this.setPrice(convertPrice, jsonEncode);
        
        this.rewardProductRules = rewardProductRules;
        this.changeRewardProductRule($('reward_product_rule'+this.itemId));
    },
    setPrice: function(convertPrice, jsonEncode){
        if(this.rewardPrice) this.rewardPrice.clearPrices();
        else this.rewardPrice = new RewardPointsPrice(
                'rewardpoints-price-template'+this.itemId, 
                $$('.webpos-price-box'+this.itemId+' .regular-price'), 
                convertPrice, 
                jsonEncode
            );
    },
    changeRewardProductRule: function(el) {
        var ruleId = el.value;       
        this.rewardPrice.clearPrices();
        if (ruleId) {
            this.currentRuleOptions = this.rewardProductRules[ruleId];
            switch (this.currentRuleOptions.optionType) {
                case 'login':
                    this.showRewardInfo('rewardpoints-login-msg'+this.itemId);
                    break;
                case 'needPoint':
                    this.showRewardInfo('rewardpoints-needmore-msg'+this.itemId);
                    $('rewardpoints-needmore-points'+this.itemId).innerHTML = this.currentRuleOptions.needPoint;
                    break;
                case 'slider':
                    this.showRewardInfo('rewardpoints-slider-container'+this.itemId);
                    this.rewardSlider.applyOptions(this.currentRuleOptions.sliderOption);
                    break;
                case 'static':
                    $('reward_product_point'+this.itemId).value = this.currentRuleOptions.sliderOption.minPoints;
                    this.rewardPrice.showPointPrices(this.currentRuleOptions.sliderOption.pointStep, this.currentRuleOptions);
                    this.showRewardInfo('');
                    break;
            }
        } else {
            this.showRewardInfo('');
        }
    },
    changePointCallback: function(points) {
        this.rewardPrice.showPointPrices(points, this.currentRuleOptions);
    },
    showRewardInfo: function(elId) {
        var elIds = ['rewardpoints-login-msg'+this.itemId, 'rewardpoints-needmore-msg'+this.itemId, 'rewardpoints-slider-container'+this.itemId];
        for (var i = 0; i < 3; i++){
            if (elIds[i] == elId) {
                $(elId).show();
            } else {
                $(elIds[i]).hide();
            }
        }
    },
    getSpendBox: function(content){ 
        this.spendPopup.setContent(content);
        this.spendPopup.showCenter(true);
    }
}
