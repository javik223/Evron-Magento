<?php

/* 
 * Web POS by Magestore.com
 * Version 2.3
 * Updated by Daniel - 12/2015
 */
class Magestore_Webpos_Block_Sales_Root extends Mage_Core_Block_Template {

    const DEFAULT_FONT_TYPE = 'monospace';

    /*
     * Page's font
     * @type string
     */

    protected $font;

    public function getFont() {
        $settings = Mage::helper('webpos')->getReceiptSettings();
        $font = $settings['font_type'];
        return (!empty($font)) ? $font : self::DEFAULT_FONT_TYPE;
    }

}
