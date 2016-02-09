<?php
	class HetNieuweWeb_CustomerNavigation_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
	{	
		public function removeLinkByName() {
			$NavigationLinks = array('account'=>'account', 'account_edit'=>'account_edit', 'address_book'=>'address_book', 'orders'=>'orders', 'billing_agreements'=>'billing_agreements', 'recurring_profiles'=>'recurring_profiles', 'reviews'=>'reviews', 'tags'=>'tags', 'wishlist'=>'wishlist', 'OAuth Customer Tokens'=>'oauth_customer_tokens', 'newsletter'=>'newsletter', 'downloadable_products'=>'downloadable_products');
			
			foreach ($NavigationLinks as $link => $configName) {
				$display = Mage::getStoreConfig('customer_navigation/display/'.$configName);
				if (isset($this->_links[$link]) && !$display) {
					unset($this->_links[$link]);
				}
			}
		}
		
		public function renameLinkByName() {
			$NavigationLinks = array('account'=>'account', 'account_edit'=>'account_edit', 'address_book'=>'address_book', 'orders'=>'orders', 'billing_agreements'=>'billing_agreements', 'recurring_profiles'=>'recurring_profiles', 'reviews'=>'reviews', 'tags'=>'tags', 'wishlist'=>'wishlist', 'OAuth Customer Tokens'=>'oauth_customer_tokens', 'newsletter'=>'newsletter', 'downloadable_products'=>'downloadable_products');			
			
			foreach ($NavigationLinks as $link => $configName) {
				$name = Mage::getStoreConfig('customer_navigation/rename/'.$configName);
				if (isset($this->_links[$link]) && $name != '') {					
					$this->_links[$link]["label"] = $name;					
				}
			}
		}
	}
?>