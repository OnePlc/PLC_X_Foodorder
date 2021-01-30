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

    public function rmcartposAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iItemID = (int)$oRequest->getPost('item_id');
            if($iItemID == 0) {
                $aReturn = [
                    'state' => 'error',
                    'message' => 'invalid id',
                ];
            } else {
                $sKey = $iItemID.'-1';

                if(array_key_exists($sKey,CoreController::$oSession->aCartItems)) {
                    unset(CoreController::$oSession->aCartItems[$sKey]);

                    $aReturn = [
                        'state' => 'success',
                    ];
                } else {
                    $aReturn = [
                        'state' => 'error',
                        'message' => 'not found',
                    ];
                }
            }

            header('Content-Type: application/json');
            echo json_encode($aReturn);
        }

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
                        $oCat = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $oItem->getMultiSelectFieldIDs('category')[0]])->current();
                        CoreController::$oSession->aCartItems[$sKey] = (object)[
                            'Item_ID' => $iItemID,
                            'label' => $oItem->getLabel(),
                            'price' => (float)$oItem->getTextField('price_sell'),
                            'amount' => $fAmount,
                            'oCat' => $oCat,
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
                    $oCat = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $oItem->getMultiSelectFieldIDs('category')[0]])->current();
                    CoreController::$oSession->aCartItems[$sKey] = (object)[
                        'Item_ID' => $iItemID,
                        'label' => $oItem->getLabel(),
                        'price' => (float)$oItem->getTextField('price_sell'),
                        'amount' => $fAmount,
                        'oCat' => $oCat,
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

    public function orderlistAction()
    {
        $this->layout('layout/json');

        $oCurrentJobsDB = $this->aPluginTbls['job']->fetchAll(true, ['state_idfs' => 15]);
        $oCurrentJobsDeliveryDB = $this->aPluginTbls['job']->fetchAll(true, ['state_idfs' => 17]);

        $bNewOrders = false;
        $aCurrentJobs = [];
        if(count($oCurrentJobsDB) > 0) {
            foreach($oCurrentJobsDB as $oJob) {
                $aCurrentJobs[] = $oJob;
            }
        }

        $aCurrentJobsDelivery = [];
        if(count($oCurrentJobsDeliveryDB) > 0) {
            foreach($oCurrentJobsDeliveryDB as $oJobD) {
                $aCurrentJobsDelivery[] = $oJobD;
            }
        }

        if(!isset(CoreController::$oSession->iCurrentOpenOrders)) {
            CoreController::$oSession->iCurrentOpenOrders = 0;
        }
        if(count($aCurrentJobs) > CoreController::$oSession->iCurrentOpenOrders) {
            $bNewOrders = true;
        }
        CoreController::$oSession->iCurrentOpenOrders = count($aCurrentJobs);

        $aReturn = [
            'state' => 'success',
            'aCurrentJobs' => $aCurrentJobs,
            'aCurrentJobsDelivery' => $aCurrentJobsDelivery,
            'bNewOrders' => $bNewOrders,
        ];

        echo json_encode($aReturn);

        return false;

        return new ViewModel();
    }

    public function deliveryajaxAction()
    {
        $this->layout('layout/json');

        $iJobID = $this->params()->fromRoute('id', 0);
        $this->aPluginTbls['job']->updateAttribute('state_idfs', 18, 'Job_ID', $iJobID);

        $aReturn = [
            'state' => 'success',
            'message' => 'done',
        ];

        echo json_encode($aReturn);

        return false;
    }

    public function deliveryAction()
    {
        $this->layout('layout/json');

        $iJobID = $this->params()->fromRoute('id', 0);
        $this->aPluginTbls['job']->updateAttribute('state_idfs', 18, 'Job_ID', $iJobID);

        $oJob = $this->aPluginTbls['job']->getSingle($iJobID);

        try {
            $oAdress = $this->aPluginTbls['contact-address']->getSingle($oJob->getSelectFieldID('contact_idfs'),'contact_idfs');

            return $this->redirect()->toUrl('https://www.google.com/maps/dir/Bahnhofstrasse+11,+8212+Neuhausen+am+Rheinfall/'.$oAdress->street.'+'.$oAdress->appartment.',+'.$oAdress->city);
        } catch(\RuntimeException $e) {
            return $this->redirect()->toUrl('https://www.google.com/maps/dir/Bahnhofstrasse+11,+8212+Neuhausen+am+Rheinfall/Sackstrass+34,+Marthalen');
        }

        return false;
    }

    public function viewAction() {
        $this->layout('layout/json');

        $iJobID = $this->params()->fromRoute('id', 0);

        $oJob = $this->aPluginTbls['job']->getSingle($iJobID);
        $aPositions = [];
        $oPositionsDB = $this->aPluginTbls['job-position']->fetchAll(false, ['job_idfs' => $iJobID]);
        if(count($oPositionsDB) > 0) {
            foreach($oPositionsDB as $oPos) {
                $oPos->oArticle = $this->oTableGateway->getSingle($oPos->article_idfs);
                $oCat = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $oPos->oArticle->getMultiSelectFieldIDs('category')[0]])->current();
                $oPos->oArticle->oCategory = (object)['id' => $oCat->Entitytag_ID, 'label' => $oCat->tag_value];
                $aPositions[] = $oPos;
            }
        }
        $oJob->aPositions = $aPositions;

        $oJob->oContact = $this->aPluginTbls['contact']->getSingle($oJob->contact_idfs);
        $oJob->oContact->oAddress = $this->aPluginTbls['contact-address']->getSingle($oJob->contact_idfs,'contact_idfs');

        $aReturn = [
            'state' => 'success',
            'oJob' => $oJob,
        ];

        echo json_encode($aReturn);

        return false;
    }

    public function confirmAction(){
        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iJobID = $oRequest->getPost('job_id');
            $sTimeVal = (int)$oRequest->getPost('deliverytime_est')*60;

            $this->aPluginTbls['job']->updateAttribute('state_idfs', 17, 'Job_ID', $iJobID);
            $this->aPluginTbls['job']->updateAttribute('deliverytime_est', date('Y-m-d H:i:s', time()+$sTimeVal), 'Job_ID', $iJobID);

            echo json_encode(['state' => 'success','message' => 'done']);
        }

        return false;
    }

    public function posorderAction() {
        $this->layout('layout/json');

        $oJSON = json_decode(urldecode($this->getRequest()->getContent()));
        if(!is_object($oJSON)) {
            $aReturn = [
                'state' => 'error',
                'message' => 'invalid json body',
            ];
        } else {
            $aItems = (array)$oJSON;
            if(count($aItems) > 0) {
                $oDoneTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'job-single',
                    'tag_key' => 'confirmed',
                    'tag_idfs' => 2,
                ])->current();

                $oOrderTypeTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'job-single',
                    'tag_key' => 'order',
                    'tag_idfs' => 9,
                ])->current();

                $oTakeawayTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'job-single',
                    'tag_key' => 'takeaway',
                    'tag_idfs' => 6
                ])->current();

                $sDesc = strip_tags(($_REQUEST['device']) ? $_REQUEST['device'] : '');

                $aOrderData = [
                    'contact_idfs' => 1,
                    'type_idfs' => $oOrderTypeTag->Entitytag_ID,
                    'state_idfs' => $oDoneTag->Entitytag_ID,
                    'created_by' => 1,
                    'modified_by' => 1,
                    'created_date' => date('Y-m-d', time()),
                    'modified_date' => date('Y-m-d', time()),
                    'description' => 'Bestellung über '.$sDesc,
                ];
                $aOrderData['label'] = 'Bestellung vom '.date('d.m.Y H:i', time()).' Vorort '.$sDesc;
                $aOrderData['deliverymethod_idfs'] = $oTakeawayTag->Entitytag_ID;

                $oNewOrder = $this->aPluginTbls['job']->generateNew();
                $oNewOrder->exchangeArray($aOrderData);

                $iOrderID = $this->aPluginTbls['job']->saveSingle($oNewOrder);

                $iSortID = 0;
                foreach($aItems as $oItem) {
                    $oNewPos = $this->aPluginTbls['job-position']->generateNew();
                    $oNewPos->exchangeArray([
                        'article_idfs' => $oItem->id,
                        'amount' => $oItem->amount,
                        'price' => $oItem->price,
                        'job_idfs' => $iOrderID,
                        'type' => 'article',
                        'sort_id' => $iSortID,
                        'created_by' => 1,
                        'modified_by' => 1,
                        'created_date' => date('Y-m-d', time()),
                        'modified_date' => date('Y-m-d', time()),
                    ]);
                    $this->aPluginTbls['job-position']->saveSingle($oNewPos);
                    $iSortID++;
                }
            }
            $aReturn = [
                'state' => 'success',
            ];
        }

        echo json_encode($aReturn);

        return false;
    }

    public function toggleshopAction()
    {
        $this->layout('layout/json');

        $bUpdate = (CoreController::$aGlobalSettings['shop-open'] == 1) ? 0 : 1;
        CoreController::$aCoreTables['settings']->update(['settings_value' => $bUpdate],['settings_key' => 'shop-open']);

        $aReturn = [
            'state' => 'success',
        ];

        echo json_encode($aReturn);

        return false;
    }

    public function selectAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iItemID = $oRequest->getPost('item_id');
            if ($iItemID != '' && is_numeric($iItemID)) {
                $oItem = $this->oTableGateway->getSingle($iItemID);

                $oVariantTbl = new TableGateway('article_variant', CoreController::$oDbAdapter);
                $oItem->oVariants = $oVariantTbl->select(['article_idfs' => $iItemID]);
                $oItem->oCat = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $oItem->getMultiSelectFieldIDs('category')[0]])->current();

                return new ViewModel([
                    'oItem' => $oItem,
                ]);
            }
        }
    }

    public function loginAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $sUser = $oRequest->getPost('order_login_email');
            $sPass = $oRequest->getPost('order_login_pass');

            if($sUser != '') {
                try {
                    $oContact = $this->aPluginTbls['contact']->getSingle($sUser, 'email_private');
                    $oContact->oAddress = $this->aPluginTbls['contact-address']->getSingle($oContact->getID(), 'contact_idfs');

                    CoreEntityController::$oSession->oContact = $oContact;

                    $this->flashMessenger()->addSuccessMessage('Anmeldung erfolgreich');
                    return $this->redirect()->toRoute('checkout');
                } catch(\RuntimeException $e) {
                    $this->flashMessenger()->addErrorMessage('E-Mail Addresse nicht gefunden');
                    return $this->redirect()->toRoute('checkout');
                }
            }

        }

        return false;
    }
}
