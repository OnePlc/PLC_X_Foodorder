<?php
/**
 * WebController.php - Web Controller
 *
 * Main Controller for Foorder Web Frontend
 *
 * @category Controller
 * @package Foodorder
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Foodorder\Controller;

use Application\Controller\CoreEntityController;
use Application\Controller\CoreController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Where;
use Laminas\Session\Container;
use OnePlace\Article\Model\ArticleTable;

class ApiController extends CoreController
{
    /**
     * User Table Object
     *
     * @var UserTable Gateway to UserTable
     * @since 1.0.0
     */
    private $oTableGateway;
    private $aPluginTbls;

    /**
     * UserController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param UserTable $oTableGateway
     * @param $oServiceManager
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, ArticleTable $oTableGateway, $oServiceManager,$aPluginTbls)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'foodorder-single';
        $this->aPluginTbls = $aPluginTbls;
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function indexAction()
    {
        # Set layout
        $this->layout('layout/json');

        return false;
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function zipAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $sZip = $oRequest->getPost('term');
            $aReturn = [
                'results' => [],
                'pagination' => (object)['more'=>false],
            ];

            $oWh = new Where();
            $oWh->like('zip',(int)$sZip.'%')->or->like('city',$sZip.'%');

            $oResultsDB = $this->aPluginTbls['zip']->select($oWh);

            foreach($oResultsDB as $oCat) {
                $aReturn['results'][] = (object)[
                    'id'=>$oCat->zip,
                    'text'=>$oCat->zip.' '.$oCat->city,
                ];
            }
        } else {
            $aReturn = [
                'state' => 'error',
                'message' => 'not allowed',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($aReturn);

        return false;
    }

    public function cartAction() {
        $this->layout('layout/json');

        if(!isset(CoreController::$oSession->aCartItems)) {
            CoreController::$oSession->aCartItems = [];
        }

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $fMin = CoreController::$oSession->oLocation->min_order;
            $iItemID = $oRequest->getPost('item_id');
            $fAmount = $oRequest->getPost('amount');
            if($iItemID != '' && is_numeric($iItemID)) {
                $oItem = $this->oTableGateway->getSingle($iItemID);
                $sKey = $iItemID.'-1';
                $bAlreadyInBasket = true;
                $bAdd = true;
                $iVar = 1;
                while($bAlreadyInBasket) {
                    if(!array_key_exists($iItemID.'-'.$iVar,CoreController::$oSession->aCartItems)) {
                        $bAlreadyInBasket = false;
                        $bAdd = false;
                        CoreController::$oSession->aCartItems[$sKey] = (object)[
                            'Item_ID' => $iItemID,
                            'label' => $oItem->getLabel(),
                            'price' => (float)$oItem->getTextField('price_sell'),
                            'amount' => $fAmount,
                            'bCustom' => false,
                        ];
                    } else {
                        if(!CoreController::$oSession->aCartItems[$iItemID.'-'.$iVar]->bCustom) {
                            $bAlreadyInBasket = false;
                            $bAdd = false;
                            $fCur = CoreController::$oSession->aCartItems[$iItemID.'-'.$iVar]->amount;
                            CoreController::$oSession->aCartItems[$iItemID.'-'.$iVar]->amount = $fCur+$fAmount;
                        }
                    }
                    $iVar++;
                }

                if($bAdd) {
                    CoreController::$oSession->aCartItems[$sKey] = (object)[
                        'Item_ID' => $iItemID,
                        'label' => $oItem->getLabel(),
                        'price' => (float)$oItem->getTextField('price_sell'),
                        'amount' => $fAmount,
                        'bCustom' => false,
                    ];
                }
            }
            return [
                'fMin' => $fMin,
                'aItems' => CoreController::$oSession->aCartItems,
            ];
        } else {
            echo 'not allowed';

            return false;
        }
    }
}
