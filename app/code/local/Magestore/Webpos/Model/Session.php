<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer session model
 *
 * @category   Mage
 * @package    Magestore_Webpos
 * @author      Webpos Team <core@magentocommerce.com>
 */
class Magestore_Webpos_Model_Session extends Mage_Core_Model_Session_Abstract {

    protected $_user;
    protected $_till;

    public function __construct() {
        $namespace = 'webpos';
        $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

        $this->init($namespace);
        Mage::dispatchEvent('webpos_session_init', array('webpos_session' => $this));
    }

    public function setUser(Magestore_Webpos_Model_User $user) {
        $this->_user = $user;
        $this->setId($user->getId());
        return $this;
    }

    public function getUser() {
        if ($this->_user instanceof Magestore_Webpos_Model_User) {
            return $this->_user;
        }

        $user = Mage::getModel('webpos/user');
        if ($this->getId()) {
            $user->load($this->getId());
        }

        $this->setUser($user);
        return $this->_user;
    }

    public function setUserId($id) {
        $this->setData('user_id', $id);
        return $this;
    }

    public function getUserId() {
        if ($this->getData('user_id')) {
            return $this->getData('user_id');
        }
        return ($this->isLoggedIn()) ? $this->getId() : null;
    }

    public function isLoggedIn() {
        return (bool) $this->getId();
    }

    public function login($username, $password) {
        $user = Mage::getModel('webpos/user');
        if ($user->authenticate($username, $password)) {
            $this->setUserAsLoggedIn($user);
            return true;
        }
        return false;
    }

    public function setUserAsLoggedIn($user) {
        $this->setUser($user);
        return $this;
    }

    public function loginById($userId) {
        $user = Mage::getModel('webpos/user')->load($userId);
        if ($user->getId()) {
            $this->setUserAsLoggedIn($user);
            return true;
        }
        return false;
    }

    public function logout() {
        if ($this->isLoggedIn()) {
            $this->setId(null);
        }
        return $this;
    }

    public function setTill(Magestore_Webpos_Model_Till $till) {
        $this->_till = $till;
        $this->setTillId($till->getId());
        return $this;
    }

    public function getTill() {
        if ($this->_till instanceof Magestore_Webpos_Model_Till) {
            return $this->_till;
        }

        $till = Mage::getModel('webpos/till');
        if ($this->getTillId()) {
            $till->load($this->getTillId());
        }

        $this->setTill($till);
        return $this->_till;
    }

    public function setTillId($id) {
        $this->setData('till_id', $id);
        return $this;
    }

    public function getTillId() {
        if ($this->getData('till_id')) {
            return $this->getData('till_id');
        }
        return 0;
    }

}
