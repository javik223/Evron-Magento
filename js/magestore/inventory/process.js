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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */

var IMProcessControl = Class.create();
IMProcessControl.prototype = {
    initialize: function (rowContainer, dataTypeUrl, getTotalUrl, runProcessUrl, redirectUrl, formKey) {
        this.rowContainer = rowContainer;
        this.dataTypeUrl = dataTypeUrl;
        this.getTotalUrl = getTotalUrl;
        this.runProcessUrl = runProcessUrl;
        this.redirectUrl = redirectUrl;
        this.formKey = formKey;
        this.dataTypes = [];
        this.curProcessId = 0;
        this.totalItems = 0;
    },
    initIcons: function (successIcon, errorIcon, noticeIcon) {
        this.successIcon = successIcon;
        this.errorIcon = errorIcon;
        this.noticeIcon = noticeIcon;
    },
    initMessages: function (errorMessage, finishMessage, redirectMessage) {
      this.errorMessage = errorMessage;  
      this.finishMessage = finishMessage;  
      this.redirectMessage = redirectMessage;  
    },
    initProcessBar: function (processBar) {
        this.processBar = processBar;
    },
    run: function () {
        new Ajax.Request(this.dataTypeUrl, {
            method: "post",
            parameters: {form_key: this.formKey},
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        this.errorProcess(response.msgs.error);
                        return;
                    }
                    this.dataTypes = response.list;
                    this.initProcesses();
                } else {
                    this.faildProcess();
                }
            }.bind(this)
        });
    },
    initProcesses: function () {
        if (this.dataTypes.length == 0) {
            return;
        }
        this.startProcess(this.dataTypes[this.curProcessId]);
    },
    startProcess: function (type) {
        new Ajax.Request(this.getTotalUrl, {
            method: "post",
            parameters: {form_key: this.formKey, type: type},
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        this.errorProcess(response.msgs.error);
                        return;
                    }
                    this.totalItems = response.total;
                    if (response.total == 0) {
                        this.addMessage('success', response.msgs.finish);
                        this.nextProcess();
                    } else {
                        this.addMessage('success', response.msgs.start);
                        this.doProcess(type);
                    }
                } else {
                    this.faildProcess();
                }
            }.bind(this)
        });
    },
    doProcess: function (type) {
        new Ajax.Request(this.runProcessUrl, {
            method: "post",
            parameters: {form_key: this.formKey, type: type},
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        this.errorProcess(response.msgs.error);
                        return;
                    }
                    if (response.remain <= 0) {
                        this.processBar.updateValue(100);
                        setTimeout(function () {
                            this.addMessage('success', response.msgs.finish);
                        }.bind(this), 2000);
                        setTimeout(function () {
                            this.nextProcess();
                        }.bind(this), 2000);
                        setTimeout(function () {
                            this.processBar.updateValue(0);
                        }.bind(this), 2000);
                    } else {
                        //this.addMessage('success', response.msgs.process);
                        var processed = this.totalItems - response.remain;
                        this.processBar.updateValue(parseInt(processed * 100 / this.totalItems));
                        this.doProcess(type);
                    }
                } else {
                    this.faildProcess();
                }
            }.bind(this),
        });
    },
    nextProcess: function () {
        this.curProcessId++;
        if (this.curProcessId < this.dataTypes.length) {
            this.initProcesses();
        } else {
            this.addMessage('notice', this.finishMessage);
            this.finishProcess();
        }
    },
    finishProcess: function () {
        this.doProcess("Finished");
        if (this.redirectUrl) {
            setTimeout(function () {
                this.addMessage('notice', this.redirectMessage);
                ;
            }.bind(this), 1000);
            setTimeout(function () {
                location.href = this.redirectUrl;
            }.bind(this), 3000);
        } else {
            setTimeout(function () {
                window.opener.location.reload()
            }, 1000);
            setTimeout(function () {
                window.close()
            }, 1000);
        }
    },
    errorProcess: function (msg) {
        this.addMessage('error', msg);
        this.addMessage('notice', this.redirectMessage);
        setTimeout(function () {
            location.href = this.redirectUrl;
        }.bind(this), 3000);
    },
    faildProcess: function () {
        this.addMessage('error', this.errorMessage);
        this.addMessage('notice', this.redirectMessage);
        setTimeout(function () {
            location.href = this.redirectUrl;
        }.bind(this), 3000);
    },
    addMessage: function (type, message) {
        var icon;
        switch (type) {
            case 'success':
                icon = this.successIcon;
                break;
            case 'error':
                icon = this.errorIcon;
                message = '<span style="color:#ff0000;font-weight:bold;">'+message+'</span>';
                break;
            case 'notice':
                icon = this.noticeIcon;
                break;
        }
        icon = '<img class="v-middle" src="' + icon + '"/>';
        var liItem = '<li style="background-color:#DDF;">' + icon + message + '</li>';
        $(this.rowContainer).insert({
            bottom: liItem
        });
    },
}
