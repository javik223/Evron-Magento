<?php

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

/**
 * Inventorybarcode Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Helper_Print extends Mage_Core_Helper_Data {

    /**
     * get Barcode list
     * 
     * return Array
     */
    public function getBarcodeList() {
        return $supportedBarcodes = array(
            // 1D
            'BCGcodabar.php' => 'Codabar',
            'BCGcode11.php' => 'Code 11',
            'BCGcode39.php' => 'Code 39',
            'BCGcode39extended.php' => 'Code 39 Extended',
            'BCGcode93.php' => 'Code 93',
            'BCGcode128.php' => 'Code 128',
            'BCGean8.php' => 'EAN-8',
            'BCGean13.php' => 'EAN-13',
            'BCGgs1128.php' => 'GS1-128 (EAN-128)',
            'BCGisbn.php' => 'ISBN',
            'BCGi25.php' => 'Interleaved 2 of 5',
            'BCGs25.php' => 'Standard 2 of 5',
            'BCGmsi.php' => 'MSI Plessey',
            'BCGupca.php' => 'UPC-A',
            'BCGupce.php' => 'UPC-E',
            'BCGupcext2.php' => 'UPC Extenstion 2 Digits',
            'BCGupcext5.php' => 'UPC Extenstion 5 Digits',
            'BCGpostnet.php' => 'Postnet',
            'BCGintelligentmail.php' => 'Intelligent Mail',
            'BCGothercode.php' => 'Other Barcode',
            // Databar
            'BCGdatabarexpanded.php' => 'Databar Expanded',
            'BCGdatabarlimited.php' => 'Databar Limited',
            'BCGdatabaromni.php' => 'Databar Omni',
            // 2D
            'BCGaztec.php' => 'Aztec',
            'BCGdatamatrix.php' => 'DataMatrix',
            'BCGmaxicode.php' => 'MaxiCode',
            'BCGpdf417.php' => 'PDF417',
            'BCGqrcode.php' => 'QRCode'
        );
    }

    public function getResponseBody($url) {
        if (ini_get('allow_url_fopen') != 1) {
            @ini_set('allow_url_fopen', '1');
        }

        if (ini_get('allow_url_fopen') != 1) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $contents = curl_exec($ch);
            curl_close($ch);
        } else {
            $contents = file_get_contents($url);
        }

        return $contents;
    }

}
