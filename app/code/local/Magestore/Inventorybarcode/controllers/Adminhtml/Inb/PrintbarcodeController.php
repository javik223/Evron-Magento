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
 * Inventorybarcode Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Adminhtml_Inb_PrintbarcodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * select template to print barcode
     *
     * @return Magestore_Inventory_Adminhtml_InventoryController
     */
    public function selecttemplateAction() {
        
        /*  add by Kai - print barcode */   
        $checkdata = Mage::getStoreConfig('Inventorybarcode/printbarcode/editdata');
           
        if (!$checkdata) {          		
            Mage::getConfig()->saveConfig('Inventorybarcode/printbarcode/editdata',now());       
            
            $model=Mage::getModel('inventorybarcode/barcodetemplate');
            
            $template1='<table style="width:75mm; height:22mm;text-align: center;">
                                <tr> 
                                <td style="width:25mm;"> 
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/> 
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span> 
                                </td>                             
                                <td style="width:25mm;"> 
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/> 
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span> 
                                </td> 
                                <td style="width:25mm;">
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/> 
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span> 
                                </td> 
                                </tr>                             
                        </table>';
            
            $template2='<table style="width:75mm; height:22mm;text-align: center;">	
                                <tr>		
                                <td style="width:25mm;">				
                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 							
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                </td>                             		
                                <td style="width:25mm;">  				
                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 			   			
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                </td>                             		
                                <td style="width:25mm;">				
                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 							
                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                </td>                             	
                                </tr>                             
                        </table>';
            
            $template3='<table style="width:75mm; height:22mm;text-align: center;">	
                                <tr>		
                                        <td style="width:25mm;">				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Price</span>				
                                                <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                        </td>                             		
                                        <td style="width:25mm;">  				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Price</span> 				
                                                <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                        </td>                             		
                                        <td style="width:25mm;">				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Price</span>				
                                                <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                        </td>                             	
                                </tr>                             
                        </table>';
            
            $template4='<table style="width:75mm; height:22mm;text-align: center;">	
                                        <tr>		
                                                <td style="width:25mm;">				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Sku</span>				
                                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                                </td>                             		
                                                <td style="width:25mm;">  				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Sku</span> 				
                                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                                </td>                             		
                                                <td style="width:25mm;">				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span> 				
                                                        <span style="float: left; width: 100%; font-size: 10px; text-align: left; margin-left: 14px;">Product Sku</span>				
                                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/>                               				
                                                        <span style="float: left; text-align: center; width: 100%; font-size: 10px;">010091930191421</span>
                                                </td>                             	
                                        </tr>                             
                        </table>';
            
            $template5='<div style="width: 80mm; text-align: center; ">                                                     
                                <table id ="kai" style=" width : 80; height:20; line-height:0.3; ">                                                    
                                        <tr width = 80mm>                                                    
                                                <td id="kai" width = 40mm>                                                    
                                                        <span style="float: left; width: 20mm; font-size: 10px; text-align: left; margin-left: 14px;">Product Name</span></br>                                                    
                                                        <span style="float: left; width: 20mm; font-size: 10px; text-align: left; margin-left: 14px;">Product Sku</span> </br>                            
                                                        <span style="float: left; width: 20mm; font-size: 12px; text-align: left; margin-left: 14px;">Price</span>                          
                                                </td>                                                    
                                                <td id="kai"  style="line-height: 0.5; " >                                                    
                                                        <img style="width: 100px;" src="{{media url="/inventorybarcode/source/barcode.jpg"}}"/></br></br>                                                    
                                                        <span style="float: left; text-align: left; margin-left: 5px;  font-size: 10px;">010091930191421</span>                                                   
                                                </td>                                                    
                                        </tr>                                                    
                                </table>                                                
                        </div>';
      
            $model->setId(1)->setBarcodeTemplateName("Barcode (3 items per row)")->setHtml($template1)->save(); 
            $model->setId(2)->setBarcodeTemplateName("Name, Barcode (3 items per row)")->setHtml($template2)->save();
            $model->setId(3)->setBarcodeTemplateName("Name, Price, Barcode (3 items per row)")->setHtml($template3)->save();
            $model->setId(4)->setBarcodeTemplateName("Name, Sku, Barcode (3s item per row)")->setHtml($template4)->save();
            $model->setId()->setBarcodeTemplateName("Barcode for jewelry ")->setHtml($template5)->save();
   
        }
        /* end add by Kai - print barcode */
        $function = Mage::getModel('inventorybarcode/printbarcode_function');
        echo $this->getLayout()->createBlock('inventorybarcode/adminhtml_printbarcode')->setTemplate('inventorybarcode/printbarcode/selecttemplate.phtml')->toHtml();
    }


    public function getimageAction() {

        $params = $this->getRequest()->getParams();

        $type = $params['type'];
        $code = $params['text'];

        if (isset($params['customize']) && $params['customize']) {
            $heigth = $params['heigth_barcode'];
            $barcodeOptions = array('text' => $code,
                'barHeight' => $heigth,
                'fontSize' => $params['font_size'],
                'withQuietZones' => true
            );
        } else {
            $barcodeOptions = array('text' => $code,
                'fontSize' => $params['font_size'],
                'withQuietZones' => true
            );
        }


        // No required options
        $rendererOptions = array();

        // Draw the barcode in a new image,
        // send the headers and the image
        $imageResource = Zend_Barcode::factory(
                        $type, 'image', $barcodeOptions, $rendererOptions
        );
        imagepng($imageResource->draw(), Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'inventorybarcode' . DS . 'images' . DS . 'barcode' . DS . 'barcode.png');
        $imageResource->render();
    }

    public function printBarcodeAction() {
        
        $params = $this->getRequest()->getParams();
        
		/* Added by Magnus 08/04/2015 (File pdf hien thi cac ki tu dc biet-ko hieu dc) */
		$this->loadLayout();
                $this->renderLayout();
		return;
		/* End Magnus 08/04/2015 */
               
        $contents = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/printbarcode/printbarcode.phtml')
                ->assign('barcodeId', $params['barcodeId'])
                ->assign('qty', $params['number_of_barcode'])
                ->assign('barcodeTemplate', $params['barcode_template'])
                ->assign('fontSize', $params['font_size'])
                ->assign('imageWidth', $params['image_width']);
        if (isset($params['border'])) {
            $contents->assign('border', $params['border']);
        } else {
            $contents->assign('border', 0);
        }
       
        include("lib/MPDF56/mpdf.php");
        $top = '10';
        $bottom = '10';
        $left = '10';
        $right = '10';
      // $params['printing_format']
        $mpdf = new mPDF('', 'B6', 8, '', '', '', '', '');

        $mpdf->WriteHTML($contents->toHtml());

        echo $mpdf->Output();
		die;
    }
    
    public function massprintBarcodeAction(){
        $this->loadLayout();
        $this->renderLayout();
        return;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/settings/barcode/manage_barcode');
    }        

}
