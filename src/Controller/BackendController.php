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

class BackendController extends CoreController
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
        $this->setThemeBasedLayout('foodorder');

        return [];
    }

    /**
     * Foodorder View
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function viewAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        $iJobID = $this->params()->fromRoute('id', 0);

        $oJob = $this->aPluginTbls['job']->getSingle($iJobID);
        $aPositions = [];
        $oPositionsDB = $this->aPluginTbls['job-position']->fetchAll(false, ['job_idfs' => $iJobID]);
        if(count($oPositionsDB) > 0) {
            foreach($oPositionsDB as $oPos) {
                $oPos->oArticle = $this->oTableGateway->getSingle($oPos->article_idfs);
                $aPositions[] = $oPos;
            }
        }
        $oJob->aPositions = $aPositions;

        return new ViewModel([
            'oJob' => $oJob,
        ]);
    }

    public function touchscreenAction() {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $sUser = 'admin@1plc.ch';
            $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
            CoreController::$oSession->oUser = $oUser;
        }

        return new ViewModel([]);
    }

    public function worktimeAction() {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $sUser = 'admin@1plc.ch';
            $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
            CoreController::$oSession->oUser = $oUser;
        }

        $oWtTbl = new TableGateway('worktime', CoreController::$oDbAdapter);

        $aCurrentTimes = $oWtTbl->select();

        return new ViewModel([
            'aCurrentTimes' => $aCurrentTimes,
        ]);
    }
}
