<?php
/*****************************************************
  #Created By : Bridge Global
  #Created On : 08-07-2016
  #Purpose : User note Related Actions in the d2d project
 *****************************************************/

namespace app\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\web\Controller;
use app\models\Utils;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\UserNotes;

class UsernoteController extends \yii\rest\ActiveController {

    public $modelClass = 'app\models\UserNotes';
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

    /******** Note Listing *********/

    public function actionListing() {
        /******* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;
        /*********** Fetching the ntes list from use notes table  of a specific user******** */
        $usernotes = new UserNotes();
        $notesList = $usernotes->listNotes($data);

        $utils->sendResponse($notesList); // It sends out the response as a json object to the frontend*/
    }

    /******** Adding a new user note to the user notes table ******** */
    public function actionAdd() {
        /********* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("note","user_id");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;

        /******* Adding the new note details to user_notes table  ******** */
        $usernotes = new UserNotes();
        $created = $usernotes->saveNote($data);
        $utils->sendResponse($created); // It sends out the response as a json object to the frontend*/
    }
    
    /******** Edit an existing user note  ******** */
    public function actionEdit() {
        /********* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;

        /******* Fetching the existing note details from user_notes table  ******** */
        $usernotes = new UserNotes();
        $noteData = $usernotes->getNote($data);
        $utils->sendResponse($noteData); // It sends out the response as a json object to the frontend*/
    }
    
    /******** Update an existing user note in the user notes table ******** */
    public function actionSave() {
        /********* Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id", "note","user_id");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;

        /******* Saving thre existing note details to user_notes table  ******** */
        $usernotes = new UserNotes();
        $saved = $usernotes->saveNote($data);
        $utils->sendResponse($saved); // It sends out the response as a json object to the frontend*/
    }

    /******** Deleting an existing note *********/
    public function actionRemove() {
        /*         * *** Utils is the class used for all common data processing and response sending functions ******* */
        $utils = new Utils();
        self::$requiredFields = array("id");
        $data = $utils->processRequest(self::$requiredFields,self::$permissionName); //It process the Json input to an array format;

        /*********** Removing the specific note from the user_notes table  *********/
        $usernote = new UserNotes();
        $deleted = $usernote->deleteNote($data);
        $utils->sendResponse($deleted); // It sends out the response as a json object to the frontend*/
    }
    /************ Setting permission names for actions ******** */
    private function setPermissionName($actionName) {
        //
    }


}
