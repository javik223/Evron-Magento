<?php
function baseCustomSetup($barcode, $get) {
    $font_dir = Mage::getBaseDir().DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Magestore'.DIRECTORY_SEPARATOR.'Inventorybarcode'.DIRECTORY_SEPARATOR.'font';

    if (isset($get['thickness'])) {
        $barcode->setThickness(max(9, min(90, intval($get['thickness']))));
    }

    $font = 0;
    if ($get['font_family'] !== '0' && intval($get['font_size']) >= 1) {
        $font = new BCGFontFile($font_dir . '/' . $get['font_family'], intval($get['font_size']));
    }

    $barcode->setFont($font);
}
?>