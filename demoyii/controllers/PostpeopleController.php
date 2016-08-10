<?php
/* * ***************************************************
  #Created By : Bridge Global
  #Created On : 13-06-2016
  #Purpose : Post people Related Actions in the d2d project
 * *************************************************** */
namespace app\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\web\Controller;
use app\models\Utils;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\Postpeople;
use app\models\PostpeoplePayment;

class PostpeopleController extends \yii\rest\ActiveController {

    public $modelClass = 'app\models\Postpeople';
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
    
    /************** Setting the permissions just before callng the action ******* */
    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            $this->setPermissionName($this->action->id);
            return true; // or false if needed
        } else {
            return false;
        }
    }

    /************* Post people Listing ****************** */
    public function actionListing() {
        /********* Utils is the class used for all common data processing and response sending functions ******** */
        $utils = new Utils();
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /********** Fetching the custome list from customers table  ********* */
        $postPeopleList = Postpeople::listPostPeople($data);
        $utils->sendResponse($postPeopleList); // It sends out the response as a json object to the frontend*/
    }

    /************* Adding a new postpeople for Franchisee ********* */
    public function actionAdd() {
        /************* Utils is the class used for all common data processing and response sending functions ******** */
        $utils = new Utils();
        self::$requiredFields = array("franchisee_id","first_name","last_name","emails","telephones","addresses","payment_method","driver_yn");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /*         * ******** Adding the new post people details to post people table  ********* */
        $created = Postpeople::savePostPeople($data);
        $utils->sendResponse($created); // It sends out the response as a json object to the frontend*/
    }

    /********** Post people Editing***********/

    public function actionEdit() {
        /********* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /********* Fetching the specific postpeople from postpeople table  ********* */
        $customer = Postpeople::getPostPeople($data);
        $utils->sendResponse($customer); // It sends out the response as a json object to the frontend*/
    }

    /********* Updating an existing Post people *************/
    public function actionSave() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id","franchisee_id","first_name","last_name","emails","telephones","addresses","payment_method","driver_yn");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /* ******** saving the specific customer to the customers table  ******** */
        $updated = Postpeople::savePostPeople($data);
        $utils->sendResponse($updated); // It sends out the response as a json object to the frontend*/
    }
    
    /********* Updating delivery areas for an existing Post people *************/
    public function actionSaveareas() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array('postPeopleId');
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /* ******** saving the delivery areas of a postpeople to the postpeople_Area table  ******** */
        $updated = Postpeople::saveDeliveryAreas($data);
        $utils->sendResponse($updated); // It sends out the response as a json object to the frontend*/
    }
    /********* Updating activities for an existing Post people *************/
    public function actionSaveactivity() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array('postPeopleId');
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /* ******** saving the postpeople activity to the activity table  ******** */
        $updated = Postpeople::saveActivity($data);
        $utils->sendResponse($updated); // It sends out the response as a json object to the frontend*/
    }

    /******** Change the status of an existing Postpeople *********/
    public function actionChangestatus() {
        /*         * *** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id","status_yn");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;

        /*********** Chnage the status of the specific post people from the postpeople table  *********/
        $postpeople = new Postpeople();
        $changed = $postpeople->changeStatus($data);
        $utils->sendResponse($changed); // It sends out the response as a json object to the frontend*/
    }

    /******** Get the total count of postpeople  *********/
    public function actionGetpostpeoplecount() {
        /******* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /*********** Getting the count of all postpeoples  ******** */
        $postpeople = new Postpeople();
        $postpeopleCount = $postpeople->getPostpeopleCount($data);

        $utils->sendResponse($postpeopleCount); // It sends out the response as a json object to the frontend*/
    }
    
    /************ Setting permission names for actions ******** */
    private function setPermissionName($actionName) {
        switch ($actionName) {
            case 'listing' : self::$permissionName = "view_post_people";
                break;
            case 'add' : self::$permissionName = "add_post_people";
                break;
            case 'edit' : self::$permissionName = "edit_post_people";
                break;
            case 'save' : self::$permissionName = "edit_post_people";
                break;
            case 'saveareas' : self::$permissionName = "add_post_people";
                break;
            case 'saveactivity' : self::$permissionName = "add_post_people";
                break;
            case 'changestatus' : self::$permissionName = "delete_post_people";
                break;
        }
    }
}
