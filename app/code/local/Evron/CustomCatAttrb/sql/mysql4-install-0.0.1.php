<?php
	$installer = $this;
	$installer->startSetup();
	$attribute = array(
		'type'			=>	'text',
		'label'			=>	'Banner Image',
		'input'			=>	'image',
		'global'		=>	Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'		=>	true,
		'required'		=>	false,
		'user_defined'	=>	true,
		'default'		=>	"",
		'group'			=>	"Generation Information"
	);
	$installer->addAttribute('catalog_category', 'categoryBanner', $attribute);
	$installer->endSetup();
?>