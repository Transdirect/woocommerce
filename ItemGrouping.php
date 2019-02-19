<?php


/**
* 
*/
class ItemGrouping
{
	private $_itemToBeGroup;

	private $_groupedItem = [];

	private $_itemBoxLimit;

	private $_intStopper;

	public function __construct ($arrItem, $limit) {
		$this->_itemToBeGroup = $arrItem;
		$this->_itemBoxLimit = $limit;
	}

	public function startGrouping () {

		// First we remove the big items
		foreach ($this->_itemToBeGroup as $index => $item) {
			if ($item['weight'] >= $this->_itemBoxLimit) {
				array_push($this->_groupedItem, array(
                    'weight'        => $item['weight'],
                    'height'        => $item['height'],
                    'width'         => $item['width'],
                    'length'        => $item['length'],
                    'quantity'      => 1,
                    'description'   => $item['description'],
                    'items_group'   => 1
                ));
                unset($this->_itemToBeGroup[$index]);
			}
        }
        $smallItemsGroup = $this->groupSmallItems($this->_itemToBeGroup);

        return array_merge($this->_groupedItem, $smallItemsGroup);
	}

	private function groupSmallItems ($arrList) {

        $arrDefaultBoxAboveHalf = [];
        $arrDefaultBoxBelowHalf = [];
        $defaultBoxHalf = round(($this->_itemBoxLimit/2), 2);

        //we separate item that are bigger and smaller than the half of default box size
        foreach ($arrList as $key => $item) {
            if ($item['weight'] >= $defaultBoxHalf) {
                array_push($arrDefaultBoxAboveHalf, $item);
            } else {
                array_push($arrDefaultBoxBelowHalf, $item);
            }
        }

        usort($arrDefaultBoxAboveHalf, [$this, "sort_weight_decs"]); //sort above half items desc
        usort($arrDefaultBoxBelowHalf, [$this, "sort_weight_asc"]); //sort below half items asc

        if (!empty($arrDefaultBoxAboveHalf) && !empty($arrDefaultBoxBelowHalf)) {
            $groupItems = $this->groupWithAboveHalf($arrDefaultBoxAboveHalf, $arrDefaultBoxBelowHalf);
        } else {
            if (!empty($arrDefaultBoxAboveHalf)) {
                $groupItems = $this->groupSingle($arrDefaultBoxAboveHalf);
            } else {
                $groupItems = $this->groupSingle($arrDefaultBoxBelowHalf);
            }
        }

        return $groupItems;
	}

    private function groupSingle ($arrList) {
        $groupItemsWeight = 0;
        $xx = 0;
        $tmpItem = array(); //This is used if there is a remaining small item that has not been grouped during the process
        $itemsCount = count($arrList);

        $tempGroupItems = [];

        foreach ($arrList as $index => $item) {

            if (($groupItemsWeight + $item['weight']) <= $this->_itemBoxLimit) {

                $groupItemsWeight += $item['weight'];
                $xx++;
                if (($index+1) == $itemsCount) {
                    $length = $width = $height = round(pow(250 * $groupItemsWeight, 1/3));
                    array_push($tempGroupItems, array(
                        'weight'        => $groupItemsWeight,
                        'height'        => $height,
                        'width'         => $width,
                        'length'        => $length,
                        'quantity'      => 1,
                        'description'   => $item['description'],
                        'items_group'   => $xx
                    ));
                }
                unset($arrList[$index]);
            } else {
                $length = $width = $height = round(pow(250 * $groupItemsWeight, 1/3));
                array_push($tempGroupItems, array(
                    'weight'        => $groupItemsWeight,
                    'height'        => $height,
                    'width'         => $width,
                    'length'        => $length,
                    'quantity'      => 1,
                    'description'   => $item['description'],
                    'items_group'   => $xx
                ));
                $xx = 0;
                $groupItemsWeight = 0;
            }
            
        }

        $filter_Item = array_filter($arrList);
        if (!empty($filter_Item)) {
            $temp = array_merge($tempGroupItems, $filter_Item);

            usort($temp, [$this, "sort_weight_asc"]);
            $this->_intStopper++;

            if ($this->_intStopper <= 3) {
                $this->groupSingle($temp);  
            }
        } else {
            $temp = $tempGroupItems;
        }

        return !empty($temp) ? $temp : array();
    }

    private function groupWithAboveHalf ($arrDefaultBoxAboveHalf, $arrDefaultBoxBelowHalf) {
        $groupedItems = [];
        $groupItemsWeight = 0;

        foreach ($arrDefaultBoxAboveHalf as $aboveKey => $aboveItem) {

            $groupItemsWeight = $aboveItem['weight'];
            $xx = 1;

            foreach ($arrDefaultBoxBelowHalf as $belowKey => $belowItem) {

                if (($groupItemsWeight + $belowItem['weight']) <= $this->_itemBoxLimit) {
                    $groupItemsWeight += $belowItem['weight'];
                    unset($arrDefaultBoxBelowHalf[$belowKey]);
                    unset($arrDefaultBoxAboveHalf[$aboveKey]);
                    $xx++;
                } else {
                    $length = $width = $height = round(pow(250 * $groupItemsWeight, 1/3));
                    array_push($groupedItems, array(
                        'weight'        => $groupItemsWeight,
                        'height'        => $height,
                        'width'         => $width,
                        'length'        => $length,
                        'quantity'      => 1,
                        'description'   => $aboveItem['description'],
                        'items_group'   => $xx
                    ));
                    continue 2;
                }
            }
        }

        $filterBelow = array_filter($arrDefaultBoxBelowHalf);
        if (!empty($filterBelow)) {
            $filterBelow = array_values($filterBelow);
            $singleGrouped = $this->groupSingle($filterBelow);    
        }

        return array_merge($groupedItems, $singleGrouped);
    }

    function sort_weight_asc ($a, $b) {
        return $a['weight']>$b['weight'];
    }

    function sort_weight_decs ($a, $b) {
        return $a['weight']<$b['weight'];
    }
}