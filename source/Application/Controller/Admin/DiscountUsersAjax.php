<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Controller\Admin;

use oxDb;
use oxField;

/**
 * Class manages discount users
 */
class DiscountUsersAjax extends \OxidEsales\Eshop\Application\Controller\Admin\ListComponentAjax
{
    /**
     * Columns array
     *
     * @var array
     */
    protected $_aColumns = ['container1' => [ // field , table,  visible, multilanguage, ident
        ['oxusername', 'oxuser', 1, 0, 0],
        ['oxlname', 'oxuser', 0, 0, 0],
        ['oxfname', 'oxuser', 0, 0, 0],
        ['oxstreet', 'oxuser', 0, 0, 0],
        ['oxstreetnr', 'oxuser', 0, 0, 0],
        ['oxcity', 'oxuser', 0, 0, 0],
        ['oxzip', 'oxuser', 0, 0, 0],
        ['oxfon', 'oxuser', 0, 0, 0],
        ['oxbirthdate', 'oxuser', 0, 0, 0],
        ['oxid', 'oxuser', 0, 0, 1],
    ],
                                 'container2' => [
                                     ['oxusername', 'oxuser', 1, 0, 0],
                                     ['oxlname', 'oxuser', 0, 0, 0],
                                     ['oxfname', 'oxuser', 0, 0, 0],
                                     ['oxstreet', 'oxuser', 0, 0, 0],
                                     ['oxstreetnr', 'oxuser', 0, 0, 0],
                                     ['oxcity', 'oxuser', 0, 0, 0],
                                     ['oxzip', 'oxuser', 0, 0, 0],
                                     ['oxfon', 'oxuser', 0, 0, 0],
                                     ['oxbirthdate', 'oxuser', 0, 0, 0],
                                     ['oxid', 'oxobject2discount', 0, 0, 1],
                                 ]
    ];

    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function _getQuery()
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

        $sUserTable = $this->_getViewName('oxuser');
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $sId = $oConfig->getRequestParameter('oxid');
        $sSynchId = $oConfig->getRequestParameter('synchoxid');

        // category selected or not ?
        if (!$sId) {
            $sQAdd = " from $sUserTable where 1 ";
            if (!$oConfig->getConfigParam('blMallUsers')) {
                $sQAdd .= " and oxshopid = '" . $oConfig->getShopId() . "' ";
            }
        } else {
            // selected group ?
            if ($sSynchId && $sSynchId != $sId) {
                $sQAdd = " from oxobject2group left join $sUserTable on $sUserTable.oxid = oxobject2group.oxobjectid where oxobject2group.oxgroupsid = " . $oDb->quote($sId);
                if (!$oConfig->getConfigParam('blMallUsers')) {
                    $sQAdd .= " and $sUserTable.oxshopid = '" . $oConfig->getShopId() . "' ";
                }
            } else {
                $sQAdd = " from oxobject2discount, $sUserTable where $sUserTable.oxid=oxobject2discount.oxobjectid ";
                $sQAdd .= " and oxobject2discount.oxdiscountid = " . $oDb->quote($sId) . " and oxobject2discount.oxtype = 'oxuser' ";
            }
        }

        if ($sSynchId && $sSynchId != $sId) {
            $sQAdd .= " and $sUserTable.oxid not in ( select $sUserTable.oxid from oxobject2discount, $sUserTable where $sUserTable.oxid=oxobject2discount.oxobjectid ";
            $sQAdd .= " and oxobject2discount.oxdiscountid = " . $oDb->quote($sSynchId) . " and oxobject2discount.oxtype = 'oxuser' ) ";
        }

        return $sQAdd;
    }

    /**
     * Removes user from discount config
     */
    public function removeDiscUser()
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $aRemoveGroups = $this->_getActionIds('oxobject2discount.oxid');
        if ($oConfig->getRequestParameter('all')) {
            $sQ = $this->_addFilter("delete oxobject2discount.* " . $this->_getQuery());
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->Execute($sQ);
        } elseif ($aRemoveGroups && is_array($aRemoveGroups)) {
            $sQ = "delete from oxobject2discount where oxobject2discount.oxid in (" . implode(", ", \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quoteArray($aRemoveGroups)) . ") ";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->Execute($sQ);
        }
    }

    /**
     * Adds user to discount config
     */
    public function addDiscUser()
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $aChosenUsr = $this->_getActionIds('oxuser.oxid');
        $soxId = $oConfig->getRequestParameter('synchoxid');

        if ($oConfig->getRequestParameter('all')) {
            $sUserTable = $this->_getViewName('oxuser');
            $aChosenUsr = $this->_getAll($this->_addFilter("select $sUserTable.oxid " . $this->_getQuery()));
        }
        if ($soxId && $soxId != "-1" && is_array($aChosenUsr)) {
            foreach ($aChosenUsr as $sChosenUsr) {
                $oObject2Discount = oxNew(\OxidEsales\Eshop\Core\Model\BaseModel::class);
                $oObject2Discount->init('oxobject2discount');
                $oObject2Discount->oxobject2discount__oxdiscountid = new \OxidEsales\Eshop\Core\Field($soxId);
                $oObject2Discount->oxobject2discount__oxobjectid = new \OxidEsales\Eshop\Core\Field($sChosenUsr);
                $oObject2Discount->oxobject2discount__oxtype = new \OxidEsales\Eshop\Core\Field("oxuser");
                $oObject2Discount->save();
            }
        }
    }
}
