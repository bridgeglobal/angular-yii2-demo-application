<?php
/*****************************************************
  #Created By : Bridge Global
  #Created On : 17-06-2016
  #Purpose : Area Management
 *****************************************************/

namespace app\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\web\Controller;
use app\models\Utils;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\Areas;
use yii\base\ArrayableTrait;

class AreaController extends \yii\rest\ActiveController {

    public $modelClass = 'app\models\Areas';
    public static $requiredFields = array();
    public static $permissionName = '';
    
    public function behaviors() {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }
    
    /*************** Setting the permissions just before callng the action ********/
    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            $this->setPermissionName($this->action->id);
            return true; // or false if needed
        } else {
            return false;
        }
    }

    /******** Area Listing of a franchisee *********/
    public function actionListing() {
        /******* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /*********** Fetching the ares list from areas table  ******** */
        $areaList = Areas::listAreas($data);
        $utils->sendResponse($areaList); // It sends out the response as a json object to the frontend*/
    }

    /************ Setting permission names for actions ******** */
    private function setPermissionName($actionName) {
        switch ($actionName) {
            case 'listing' : self::$permissionName = "view_area";
                break;
            case 'add' : self::$permissionName = "add_area";
                break;
            case 'edit' : self::$permissionName = "edit_area";
                break;
            case 'save' : self::$permissionName = "edit_area";
                break;
            case 'remove' : self::$permissionName = "delete_area";
                break;
        }
    }


}
