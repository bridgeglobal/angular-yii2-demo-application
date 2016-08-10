<?php
/*****************************************************
  #Created By : Bridge Global
  #Created On : 02-06-2016
  #Purpose : User Related Actions in the d2d project
 * ************************************************* */

namespace app\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Utils;
use app\models\LoginForm;
use app\models\Users;
use app\models\UserType;
use app\models\SystemActions;

class UserController extends ActiveController {

    public $modelClass = 'app\models\Users';
    public static $requiredFields = array();
    public static $permissionName = '';

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
            ],
        ];
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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

    /************** User Login function *************/
    public function actionLogin() {
        /************* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array('username', "password");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName, $auth = false); //It process the Json input to an array format;auth variable is for validating auth key which is not present at the time of login
        //loginform is a Yii model used for user login:
        $loginform = new LoginForm();
        $loginform->attributes = $data;      //load json data into model:
        /************* Validating the received login request against username and password ******** */
        $userdata = $loginform->login();
        $utils->sendResponse($userdata); // It sends out the response as a json object to the frontend
    }
   
     /********* Edit profile of a specific user **********/
    public function actionEditprofile() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id","name","email");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName); //It process the Json input to an array format;
        /*************** Saving the updated information in users table  ******** */
        $user = Users::editProfile($data);
        $utils->sendResponse($user); // It sends out the response as a json object to the frontend*/
    }
     /********* Send reset password request to a user **********/
    public function actionForgotpassword() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("email");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName,false); //It process the Json input to an array format;
        /*************** Checking the given email and sending a reset password link to the user  ******** */
        $user = Users::forgotPassword($data);
        $utils->sendResponse($user); // It sends out the response as a json object to the frontend*/
    }
    /********* Set New password from the email link **********/
    public function actionNewpassword() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("password","token");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName,false); //It process the Json input to an array format;
        /*************** Checking the given token and setting the new password  ******** */
        $passwordData = Users::validatePasswordToken($data);
        $utils->sendResponse($passwordData); // It sends out the response as a json object to the frontend*/
    }
    
    /********* Checking whether the given email exists **********/
    public function actionEmailexists() {
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("email");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName); //It process the Json input to an array format;
        /*************** Checking the given email exists in our database  ******** */
        $emailExists = Users::checkEmailExists($data);
        $utils->sendResponse($emailExists); // It sends out the response as a json object to the frontend*/
    }

    public function actionSaveimage(){
        /******** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("link","image_type");
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName); //It process the Json input to an array format;
        /*************** saving the given image in our database  ******** */
        $saved = Users::saveImage($data);
        $utils->sendResponse($saved); // It sends out the response as a json object to the frontend*/
    }
    
    public function actionImageupload(){
        Users::uploadImage($_POST);
    }

    /************ Setting permission names for actions ******** */
    private function setPermissionName($actionName) {
        switch ($actionName) {
            case 'listing' : self::$permissionName = "view_fr_user";
                break;
            case 'add' : self::$permissionName = "add_fr_user";
                break;
            case 'edit' : self::$permissionName = "edit_fr_user";
                break;
            case 'save' : self::$permissionName = "edit_fr_user";
                break;
            case 'remove' : self::$permissionName = "delete_fr_user";
                break;
        }
    }

    public function actionAddsystemactions() {
        $utils = new Utils();
        self::$requiredFields = array();
        $data = $utils->processRequest(self::$requiredFields, self::$permissionName,false); //It process the Json input to an array format;
        $result =  SystemActions::insertSystemActions($data);

         $utils->sendResponse($result); // It sends out the response as a json object to the frontend*/
    }

}
