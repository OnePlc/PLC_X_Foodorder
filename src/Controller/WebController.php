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

class WebController extends CoreController
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
    public function __construct(AdapterInterface $oDbAdapter, ArticleTable $oTableGateway, $oServiceManager, $aPluginTbls)
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
    public function homeAction()
    {
        # Set layout
        $this->layout('layout/web');

        return new ViewModel([]);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function listAction()
    {
        # Set layout
        $this->layout('layout/web');

        $iZip = $this->params()->fromRoute('zip', 0);
        $oLocationTbl = new TableGateway('contact_address_zipcity', CoreController::$oDbAdapter);
        $oLocation = $oLocationTbl->select(['zip' => $iZip])->current();

        CoreController::$oSession->oLocation = $oLocation;

        $oVariantTbl = new TableGateway('article_variant', CoreController::$oDbAdapter);
        $oMealsDB = $this->oTableGateway->fetchAll(false, []);
        $aMeals = [];
        if(count($oMealsDB) > 0) {
            foreach($oMealsDB as $oMeal) {
                $oMeal->oVariants = $oVariantTbl->select(['article_idfs' => $oMeal->getID()]);
                $iCategoryID = $oMeal->getMultiSelectFieldIDs('category')[0];
                if(!array_key_exists($iCategoryID,$aMeals)) {
                    $aMeals[$iCategoryID] = [];
                }
                $aMeals[$iCategoryID][] = $oMeal;
            }
        }
        return new ViewModel([
            'aMeals' => $aMeals,
            'oLocation' => $oLocation,
        ]);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function viewAction()
    {
        # Set layout
        $this->layout('layout/web');

        return new ViewModel([]);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function checkoutAction()
    {
        # Set layout
        $this->layout('layout/web');

        $oRequest = $this->getRequest();

        if(!$oRequest->isPost()) {
            return new ViewModel([
                'aItems' => CoreController::$oSession->aCartItems,
            ]);
        } else {
            # Add Order
            $oDeliveryTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                'entity_form_idfs' => 'job-single',
                'tag_key' => 'delivery',
            ]);
            if(count($oDeliveryTag) > 0) {
                $oDeliveryTag = $oDeliveryTag->current();

                $iContactID = (int)$oRequest->getPost('contact_idfs');
                if($iContactID != 0) {
                    $oAddressFound = $this->aPluginTbls['contact-address']->getSingle($iContactID, 'contact_idfs');

                    CoreController::$oSession->oLocation->street = $oAddressFound->street;
                    CoreController::$oSession->oLocation->street_nr = $oAddressFound->appartment;
                } else {
                    $aContactData = [
                        'firstname' => $oRequest->getPost('order_contact_firstname'),
                        'lastname' => $oRequest->getPost('order_contact_lastname'),
                        'street' => $oRequest->getPost('order_address_street'),
                        'email_private' => $oRequest->getPost('order_contact_email'),
                        'phone_private' => $oRequest->getPost('order_contact_phone'),
                        'street_nr' => $oRequest->getPost('order_address_streetnr'),
                        'zip' => $oRequest->getPost('order_address_zip'),
                        'city' => $oRequest->getPost('order_address_city'),
                    ];

                    $oAddressFound = $this->aPluginTbls['contact-address']->fetchAll(true, ['street-like' => $aContactData['street'],'zip-like' => $aContactData['zip'],'appartment-like' => $aContactData['street_nr']]);
                    $oContactFound = false;
                    if(count($oAddressFound) > 0) {
                        foreach($oAddressFound as $oAddr) {
                            var_dump($oAddr);
                            $oContact = $this->aPluginTbls['contact']->getSingle($oAddr->contact_idfs);
                            if($oContact->firstname == $aContactData['firstname'] && $oContact->lastname == $aContactData['lastname']) {
                                $oContactFound = $oContact;
                                $iContactID = $oContactFound->getID();
                                $iAddressID = $oAddr->getID();
                            }
                        }
                    }

                    if($oContactFound) {

                    } else {
                        $oContactFound = $this->aPluginTbls['contact']->generateNew();
                        $oContactFound->exchangeArray([
                            'firstname' => $aContactData['firstname'],
                            'lastname' => $aContactData['lastname'],
                            'email_private' => $aContactData['email_private'],
                            'phone_private' => $aContactData['phone_private'],
                        ]);
                        $iContactID = $this->aPluginTbls['contact']->saveSingle($oContactFound);
                        $oAddress = $this->aPluginTbls['contact-address']->generateNew();
                        $oAddress->exchangeArray([
                            'street' => $aContactData['street'],
                            'appartment' => $aContactData['street_nr'],
                            'zip' => $aContactData['zip'],
                            'city' => $aContactData['city'],
                            'contact_idfs' => $iContactID,
                            'created_by' => 1,
                            'modified_by' => 1,
                            'created_date' => date('Y-m-d', time()),
                            'modified_date' => date('Y-m-d', time()),
                        ]);
                        $iAddressID = $this->aPluginTbls['contact-address']->saveSingle($oAddress);

                        CoreController::$oSession->oLocation->street = $aContactData['street'];
                        CoreController::$oSession->oLocation->street_nr = $aContactData['street_nr'];
                    }
                }

                $oNewTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'job-single',
                    'tag_key' => 'new',
                    'tag_idfs' => 2,
                ])->current();

                $oOrderTypeTag = CoreEntityController::$aCoreTables['core-entity-tag']->select([
                    'entity_form_idfs' => 'job-single',
                    'tag_key' => 'order',
                    'tag_idfs' => 9,
                ])->current();

                $aOrderData = [
                    'contact_idfs' => $iContactID,
                    'type_idfs' => $oOrderTypeTag->Entitytag_ID,
                    'state_idfs' => $oNewTag->Entitytag_ID,
                    'created_by' => 1,
                    'modified_by' => 1,
                    'created_date' => date('Y-m-d', time()),
                    'modified_date' => date('Y-m-d', time()),
                    'description' => strip_tags(($oRequest->getPost('order_comment')) ? $oRequest->getPost('order_comment') : ''),
                ];
                $aOrderData['label'] = 'Bestellung vom '.date('d.m.Y H:i', time()).' aus '.$aContactData['city'];
                $aOrderData['deliverymethod_idfs'] = $oDeliveryTag->Entitytag_ID;

                $oNewOrder = $this->aPluginTbls['job']->generateNew();
                $oNewOrder->exchangeArray($aOrderData);

                $iOrderID = $this->aPluginTbls['job']->saveSingle($oNewOrder);

                $iSortID = 0;
                foreach(CoreController::$oSession->aCartItems as $oCartPos) {
                    $oNewPos = $this->aPluginTbls['job-position']->generateNew();
                    $oNewPos->exchangeArray([
                        'article_idfs' => $oCartPos->Item_ID,
                        'amount' => $oCartPos->amount,
                        'price' => $oCartPos->price,
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

                CoreController::$oSession->aCartItems = [];

                unset(CoreController::$oSession->oContact);

                return $this->redirect()->toRoute('food-tracking', ['id' => $iOrderID]);
            } else {
                echo 'Error';

                return false;
            }
        }
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function trackingAction()
    {
        # Set layout
        $this->layout('layout/web');

        $iOrderID = $this->params()->fromRoute('id', 0);

        $oOrder = $this->aPluginTbls['job']->getSingle($iOrderID);
        $oAddressFound = $this->aPluginTbls['contact-address']->getSingle($oOrder->contact_idfs, 'contact_idfs');

        CoreController::$oSession->oLocation->street = $oAddressFound->street;
        CoreController::$oSession->oLocation->street_nr = $oAddressFound->appartment;

        return new ViewModel([
            'oOrder' => $oOrder,
        ]);
    }

    public function wtdemoAction() {
        # Set layout
        $this->layout('layout/json');

        $oWtTbl = new TableGateway('worktime', CoreController::$oDbAdapter);

        $oWtOpen = $oWtTbl->select(['time_end' => '0000-00-00 00:00:00']);

        if(count($oWtOpen) > 0) {
            $oWtOpen = $oWtOpen->current();

            $oWtTbl->update([
                'time_end' => date('Y-m-d H:i:s', time()),
            ],'Worktime_ID = '.$oWtOpen->Worktime_ID);

            echo '<h1>Auf Wiedersehen Nathanael</h1>';
        } else {
            $oWtTbl->insert([
                'time_start' => date('Y-m-d H:i:s', time()),
            ],'Worktime_ID = '.$oWtOpen->Worktime_ID);

            echo '<h1>Willkommen Nathanael</h1>';
        }

        return false;
    }
}
