<?php
    class Magestore_Webpos_Block_Adminhtml_Report_Abstract extends Mage_Adminhtml_Block_Widget_Grid {
	    
	protected $_filterConditions = array('period' =>'','from' =>'','to' =>'');
	protected $locationIds = array();
        protected $userIds = array();
        public function setFilterCondition($conditions){
		$conditionLabels = array('range','period','order_statuses','from','to','rp_settings');
		foreach($conditions as $conditionLabel => $value){
			if(in_array($conditionLabel,$conditionLabels))
				$this->_filterConditions[$conditionLabel] = $value;
		}
		$session = Mage::getModel('core/session');
		$session->setData('rp_conditions',$this->_filterConditions);
	}
	
	public function getSalesCollection($timeFrom,$endTime,$contidions){
                $timeFrom = $timeFrom.' 23:59:59';
                if(strtotime($endTime) > strtotime($timeFrom))
                    $endTime = $endTime.' 23:59:59';
                else
                    $endTime = $endTime.' 23:59:59';
		$posorderCollection = Mage::getModel('webpos/posorder')->getCollection();   
		$posorderCollection->addFieldToFilter('created_date',array('gteq' => $timeFrom));
		$posorderCollection->addFieldToFilter('created_date',array('lt' => $endTime));
                /*order status*/
                $filterConditions = Mage::getModel('core/session')->getRpConditions();
                $orderStatuses = $filterConditions['order_statuses'];
                if(!empty($orderStatuses[0]))
                    $posorderCollection->addFieldToFilter('order_status',array('in' => $orderStatuses));
                /**/
		if(is_array($contidions) && count($contidions) > 0)
		foreach($contidions as $fieldKey => $fieldValue){
			$posorderCollection->addFieldToFilter($fieldKey,$fieldValue);
		}
                $posorderCollection->getSelect()->columns(array(
                        'totals' => 'SUM(order_totals)',
                ))->group('order_id');
		return $posorderCollection;
	}
        public function getSalesTotal($timeFrom,$endTime,$contidions){
            $timeFrom = $timeFrom.' 23:59:59';
            $endTime = $endTime.' 23:59:59';
            $posorderCollection = Mage::getModel('webpos/posorder')->getCollection();   
            $posorderCollection->addFieldToFilter('created_date',array('from' => $timeFrom,'to' => $endTime));
            /*order status*/
            $filterConditions = Mage::getModel('core/session')->getRpConditions();
            $orderStatuses = $filterConditions['order_statuses'];
            if(!empty($orderStatuses[0]))
                $posorderCollection->addFieldToFilter('order_status',array('in' => $orderStatuses));
            /**/
            if(is_array($contidions) && count($contidions) > 0)
            foreach($contidions as $fieldKey => $fieldValue){
                    $posorderCollection->addFieldToFilter($fieldKey,$fieldValue);
            }
            $posorderCollection->getSelect()->columns(array(
                    'totals' => 'SUM(order_totals)',
            ))->group('order_id');
            return $posorderCollection;
        }
        function lastDayOf($period, DateTime $date = null)
        {
            $period = strtolower($period);
            $validPeriods = array('year', 'quarter', 'month', 'week');

            if ( ! in_array($period, $validPeriods))
                throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

            $newDate = ($date === null) ? new DateTime() : clone $date;

            switch ($period)
            {
                case 'year':
                    $newDate->modify('last day of december ' . $newDate->format('Y'));
                    break;
                case 'quarter':
                    $month = $newDate->format('n') ;

                    if ($month < 4) {
                        $newDate->modify('last day of march ' . $newDate->format('Y'));
                    } elseif ($month > 3 && $month < 7) {
                        $newDate->modify('last day of june ' . $newDate->format('Y'));
                    } elseif ($month > 6 && $month < 10) {
                        $newDate->modify('last day of september ' . $newDate->format('Y'));
                    } elseif ($month > 9) {
                        $newDate->modify('last day of december ' . $newDate->format('Y'));
                    }
                    break;
                case 'month':
                    $newDate->modify('last day of this month');
                    break;
                case 'week':
                    $newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
                    break;
            }

            return $newDate;
        }
    }