<?php
/**
 * This is the model library for all the user data processing functions
 *
 * Created by : Bridge Global
 * Created on : 03-06-2016
 * Purpose    : The functions for users managemnt
 */
namespace app\models;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "users".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property integer $type_id
 * @property string $username
 * @property string $password
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string $image_type
 * @property string $link
 * @property string $email
 * @property string $remember_token
 * @property string $status_yn
 * @property string $authentication_key
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class Users extends \yii\db\ActiveRecord {

    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();
    private static $user = [];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['franchisee_id', 'type_id', 'username', 'first_name', 'email'], 'required'],
            [['franchisee_id', 'type_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['username', 'email'], 'string', 'max' => 100],
            [['password', 'remember_token'], 'string', 'max' => 255],
            [['first_name'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'franchisee_id' => 'Franchisee ID',
            'type_id' => 'Type ID',
            'username' => 'auto gnerated',
            'password' => 'auto generated',
            'title' => 'Title',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'image_type' => 'Image Type',
            'link' => 'Link',
            'email' => 'Email',
            'remember_token' => 'Remember Token',
            'status_yn' => 'Status Yn',
            'authentication_key' => 'Authentication key',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    public function afterValidate() {
        self::$fieldErrors = self::getErrors();
        if(!empty(self::$fieldErrors)){
            Utils::sendValidationMessages(self::$fieldErrors);
        }
    }

    /**
     * @inheritdoc
     * @return UsersQuery the active query used by this AR class.
     */
    public static function find() {
        return new UsersQuery(get_called_class());
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        //$userdata = self::findOne(['username' => $username])->asArray();

        self::$user = self::findOne(['username' => $username,'deleted_at' => null]);
        if (!self::$user) {
            return false;
        }
        return self::$user;
    }
     /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email) {
        self::$user = self::findOne(['email' => $email,'deleted_at' => null]);
        //print_r(self::$user);exit;
        if (!self::$user) {
            return false;
        }
        return self::$user;
    }

    /**
     * Finds logged in user by username and password
     *
     * @param string $username
     * @param string $password
     * @return static|null
     */
    public static function findLoggedInUser($username, $password) {
        self::$user = self::findOne(['username' => $username, 'password' => md5($password),'deleted_at' => null,'status_yn'=>'1']);
        if(empty(self::$user)) {
            self::$user = self::findOne(['email' => $username, 'password' => md5($password),'deleted_at' => null,'status_yn'=>'1']);
        }
        return self::$user;
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return $this->password === $password;
    }

    /**
     * Updating a new auth key to a specific user after successfull login
     *
     */
    public function authenticateUser($userId) {
        $fieldsToBeInserted = UserAuth::attributes();
        session_start();
        $newAuthKey = $userId . session_id();
        $params = array('user_id' => $userId, 'authentication_key' => $newAuthKey);
        $authData = UserAuth::instantiate($params);
        $authData = Utils::processInsertParams($authData,$params,$fieldsToBeInserted);
        if ($authData->save() !== false) {
            return $authData;
        } else {
            return false;
        }
    }

    /**
     * authenticate the Api requests
     *
     */
    public function authenticateApi($authkey,$userId) {
        $query = new Query;
        /********* Checking whether a user with the auth key exists ************/
        $userdata = $query->select('u.id')->from('user_auth ua')
        ->leftJoin('users u', 'ua.user_id = u.id')
        ->where(['ua.user_id' => $userId, 'u.status_yn' => '1'])->all();
        
        if (!empty($userdata)) {
            return true;
        } else {
            return false;
        }
    }
    
      /**
     * Finds whether a user exists with the same email
     *
     * @param string $params
     * @return static|null
     */
    public static function checkEmailExists($params) {
        
        if(isset($params)) {
            $existingValue = self::emailExists(@$params['email'],@$params['user_id']);
        }
        if ($existingValue) {
            self::$success = 0;
            self::$message = "The email ".$existingValue." has already been taken. Please try again with another email";
        }
        
        return array('success' => self::$success, 'message' => self::$message, 'data' => @$existingValue);
    }

    /**
     * Finds whether a user exists with the same email
     *
     * @param string $email
     * @param string $userid
     * @return static|null
     */
    public static function emailExists($userEmail, $userId = '') {
        if(!is_array($userEmail)) { // if the received parameter is an array containing different emails 
            $emails = array($userEmail);
        }
        else {
            $emails = $userEmail;
        }
        
        $existingValue= '';
        if(!empty($emails)) {
            foreach ($emails as $email) {
                if ($userId != '') { // If the user is an existing one and they are performing edit action
                    $exists = self::find()->where(['email' => $email])->andWhere(['<>', 'id', $userId])->andWhere(['deleted_at' => null])->all();
                } else { // If the user is a new one and they are performing add action
                    $exists = self::find()->where(['email' => $email])->andWhere(['deleted_at' => null])->all();
                }
                if (!empty($exists)) {
                    $existingValue = $email;
                    break;
                }
            }
        }
        if ($existingValue !='') {
            return $existingValue;
        } else {
            return false;
        }
    }

    /**
     * Create a franchisee admin/customer/post people/ colleague automatically
     */
    public function createFranchiseeUser($userInfo, $userType = 'admin', $action = 'add') {
        $create = false;
        $userId = '';
        $newUser = '';
        if ($action == 'add') {
            $create = true;
        }
        if ($create) {
            $newUser = self::instantiate($userInfo);
            /******* Setting the required informationf for a franchisee admin/customer/post people *********** */
            switch ($userType) {
                case "post-people" : $typeId = 8;// User type id of postpeople
                    break;
            }

            $newUser->username = ($userInfo['username'] == '') ? Utils::generateUsername(6) : $userInfo['username'];
            $newUser->title = @$userInfo['title'];
            $newUser->first_name = (@$userInfo['first_name'] == '')?$newUser->username:@$userInfo['first_name'];
            $newUser->last_name = @$userInfo['last_name'];
            $newUser->email = $userInfo['email'];
            $password = ($userInfo['password'] != '') ? $userInfo['password'] : Utils::generatePassword(6);
            $newUser->password = md5($password);
            $newUser->franchisee_id = $userInfo['franchisee_id'];
            $newUser->type_id = $typeId;
            $newUser->mobile = @$userInfo['mobile'];
            $newUser->link = @$userInfo['link'];
            $newUser->image_type = @$userInfo['image_type'];

            if($newUser->image_type== 'url') { // If the image is in the form of a url
                $imageData = Utils::fetchImageFromUrl($newUser->link); // Processing the url
                if(@$imageData['success'] == "1") {
                    $newUser->link = $imageData['data'];
                    $newUser->image_type = "img";
                }
            }


            $newUser->status_yn = isset($userInfo['status_yn']) ? @$userInfo['status_yn'] : '1';
            $newUser->created_by = $userInfo['created_by'];
            $saved = $newUser->save();

            if ($saved !== false) {
                self::$message = " User has been created successfully";  
                $dataArray = array("username" => $newUser->username, "password" => $password);
                self::accountCreationEmail($newUser->email,$dataArray);
                $userInfo['userId'] = Yii::$app->db->getLastInsertID();
                
            } else {
                self::$success = 0;
                self::$message = "Some error occured while processing the request.";
            }
            $userData = array('username' => $newUser->username, 'password' => $password, 'franchiseeId' => $userInfo['franchisee_id'], 'userId' => @$userInfo['userId']);
        } else {
            $existingUser = self::findOne($userInfo['user_id']);
            $existingUser->title = @$userInfo['title'];
            $existingUser->first_name = @$userInfo['first_name'];
            $existingUser->last_name = @$userInfo['last_name'];
            $existingUser->mobile = @$userInfo['mobile'];
            if(@$userInfo['link']!=''){
                if(@$userInfo['image_type'] == 'url') {// If the image is in the form of a url
                    $imageData = Utils::fetchImageFromUrl(@$userInfo['link']);// Processing the url
                    if(@$imageData['success'] == "1") {
                        $existingUser->link = $imageData['data'];
                        $existingUser->image_type = "img";
                    }
                }
                else {
                   $existingUser->link = @$userInfo['link'];
                    $existingUser->image_type = @$userInfo['image_type']; 
                }                
            }
            
            $existingUser->updated_by = $userInfo['created_by'];
            $saved = $existingUser->save();

            if(isset($userInfo['status_yn']) && @$userInfo['status_yn'] !=''){// If the status changed at the time of updation

                $statusData = array('status_yn' => @$userInfo['status_yn'],'userId' => @$userInfo['created_by']);
                if($userType== 'post-people'){
                    $statusData['id'] = @$userInfo['post_people_id'];
                    Postpeople::changeStatus($statusData);
                }
            }
            if ($userInfo['email'] != '') {
                $existingUser->email = $userInfo['email'];
                $existingUser->save();
            }
            if ($userInfo['username'] != '') {
                if (isset($userInfo['username']) && $userInfo['username'] != '') {
                    $isUnique = Users::isUniqueUsername($userInfo['username'], $userInfo['user_id']);
                    if(!$isUnique){
                        self::$success = 0;
                        self::$message = "This username has already been taken. Please try again with another username.";
                    }
                    else{
                        $existingUser->username = $userInfo['username'];
                        $existingUser->save();
                    }
                }                
            }
            if (isset($userInfo['password']) && @$userInfo['password'] != '') {
                // Update the new password if it is present in the edited data
                $userDetails = array('email' => $userInfo['email'], 'password' => $userInfo['password'], 'userId' => $userInfo['user_id']);
                $passwordUpdate = Users::resetPassword($userDetails);
                if ($passwordUpdate['success'] == 0) {
                    self::$success = 0;
                    self::$message = $passwordUpdate['message'];
                }
            }   
            $userData = array('username' => $userInfo['username'], 'password' => $userInfo['password'], 'franchiseeId' => $userInfo['franchisee_id'], 'userId' => $userInfo['user_id']);   
        }
         /************ if the user has been updated successfully, adding the user informations **************/
        if(self::$success) {
            self::saveExtraUserInfo($userInfo,$userData['userId']);
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $userData);
    }
    
    /**
     * Saving the extra infoormation such as telephone, emails, websites and addresses
     */
    public function saveExtraUserInfo($extraInfo, $userId='') {
       /************Saving Addresses ************/
       if(isset($extraInfo['addresses'])) {
            $saved = UserAddress::saveAddress(@$extraInfo['addresses'], $userId);
       }
       
        /************Saving Extra info ************/
       if(isset($extraInfo))
       $saved = UserInfoExtra::saveExtraInfo($extraInfo, $userId);
       
       return $saved;
    }
    
     /**
     * Get the details of an existing user
     *
     */
    public function getUser($params){
        $query = new Query;
        // compose the query
        //Conditions for the query are created
        $userData = $query->select('u.*,u1.first_name as franchisee_name')
            ->from('users AS u')
            ->leftJoin('franchisee AS fr',"fr.id = u.franchisee_id")
            ->leftJoin('users AS u1',"u1.id = fr.user_id")
            ->where(["u.id"=>$params['id'],"u.deleted_at" => NULL])->one();
        if (empty($userData)) {
            self::$success = 0;
            self::$message  = "Sorry. The record cannot be found.";
        }
        return array ('success' => self::$success,'message'=>self::$message , 'data'=>$userData);
    }

    /**
     * Checking the username is unique
     *
     */
    public function isUniqueUsername($username,$userId='') {
        if($userId !=''){
            $newUser = self::find()->where(["username" => $username])->andWhere(['<>','id',$userId])->one();
        }
        else{
            $newUser = self::findOne(["username" => $username]);
        }
        if (empty($newUser)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Sending password reset email
     *
     */
    public function forgotPassword($params) {
        $frUser = self::find()->where(["email" => $params['email']])->one();
        if (!empty($frUser)) {
            session_start();
            $newToken = mt_rand(). session_id();
            $resetLink = "<a href='http://localhost:3000/#/reset-password?t=".md5($newToken)."'>http://localhost:3000/#/reset-password?t=".md5($newToken)."</a>";
            $frUser->passwordToken = $newToken;
            $frUser->save();
            self::forgotPasswordEmail($params['email'],array("resetLink"=>$resetLink)); // sending the forgot pass email
            self::$message = " A password reset link has been sent to your email id";
        }
        else{
            self::$success = 0;
            self::$message = "Sorry. This email has not been linked to any of the existing user accounts.";
        }
        return array('success' => self::$success, 'message' => self::$message);
    }

    /**
     * Pasword reset function 
     *
     */
    public function resetPassword($userInfo) {
        $frUser = self::findOne($userInfo["userId"]);
        $frUser->password = md5($userInfo['password']);
        if ($frUser->save() !== false) {
            self::$message = " Password saved successfully";
            self::passwordResetEmail($userInfo['email'],array("password"=>$userInfo['password']));
        }
        else{
            self::$success = 0;
            self::$message = "Sorry. Your password cannot be saved.";
        }
        return array('success' => self::$success, 'message' => self::$message);
    }
    /**
     * Pasword reset function while using an email link
     *
     */
    public function validatePasswordToken($params) {
        if(@$params['token'] !='') {
           $frUser = self::find()->where(["md5(passwordToken)"=>$params["token"]])->one();
            if(!empty($frUser)) {
                $userInfo = array('userId' => $frUser->id, 'email' => $frUser->email, 'password' => $params['password']);
                $result = self::resetPassword($userInfo);
                if($result['success'] == 1) {
                    $frUser->passwordToken = NULL;
                    $frUser->save();
                }
                self::$message = $result['message'];
            }
            else {
                self::$success = 0;
                self::$message = "Sorry. You are trying with an unauthenticated link.";
            } 
        }
        else {
            self::$success = 0;
            self::$message = "Sorry. You do not have a valid token.";
        } 
        return array('success' => self::$success, 'message' => self::$message, 'data' => $params);
    }
    
     /**
     * Saving the profile information of a user
     */
    public function editProfile($params) {
        $fieldsToBeInserted = self::attributes();
        $exists = Users::emailExists($params['email'], $params['userId']); // Checking whether the email exists in our db
        
        if ($exists) {
            self::$success = 0;
            self::$message = "This email has already been taken. Please try again with another email.";
        } else {
            /****************** The record is a valid one , so we can proceed with saving ************** */
            $userData = self::findOne($params['userId']);
            if (!empty($userData)) { 
                $userData = Utils::processInsertParams($userData, $params, $fieldsToBeInserted); // Processsing the insert parameters
                if ($userData->save() !== false) {
                    self::$message = "User details saved Successfully";
                    
                    if (isset($params['password']) && $params['password'] != "") {
                        // Update the new password if it is present in the edited data
                        $userInfo = array('email' => $params['email'], 'password' => $params['password'], 'userId' => $userData->id);
                        $passwordUpdate = Users::resetPassword($userInfo);
                        if ($passwordUpdate['success'] == 0) {
                            self::$message = $passwordUpdate['message'];
                        }
                    }
                    $imageName = Utils::fetchSingleRecord("link", "users", $where = " id = '".$params['userId']."'");
                    $params['link'] = $imageName;
                    
                } else {
                    self::$success = 0;
                    self::$message = "Some error occured while processing your request";
                }
            }
        }
        
        return array('success' => self::$success, 'message' => self::$message, 'data' => $params);
    }

    /**
     * Checking whether the particular user has access to this module
     *
     */
    public function hasAccessPermission($userId,$permissionName) {
        $query = new Query;
        /*********** Building the query*********/
        $where = " u.id ='".$userId."' AND u.deleted_at IS NULL AND u.status_yn = '1' AND ut.access_permissions like '%".$permissionName."%'";
        $permissionData = $query->select('u.id')
                ->from('users AS u')
                ->leftJoin('user_type AS ut', 'ut.id = u.type_id')
                ->where($where)->one();
        if(!empty($permissionData)){
            return true;
        }
        else{
            return false;
        }
    }
    
    public function forgotPasswordEmail($email, $dataArray){
        
        $subject = "Your password reset request has been received";
        
        $mailBody = "Hi,<br><br>
                    Thank you for your password reset request.Please click on the below link to reset your password. <br>".
                    $dataArray['resetLink']." <br><br>
                    Thanks & Regards, <br>
                    D2D Team.
                     ";
        Utils::sendEmail($email,$subject,$mailBody);
        return true;
    }
    
    public function passwordResetEmail($email, $dataArray){
        
        $subject = " Your password has been reset successfully";
        
        $mailBody = "Hi,<br><br>
                    Your password has been updated successfully.The new password is :".$dataArray['password']." <br><br>
                    Thanks & Regards, <br>
                    D2D Team.
                     ";
        Utils::sendEmail($email,$subject,$mailBody);
    }
    /******** Email for account creation **************/
    public function accountCreationEmail($email , $dataArray) {
        $subject = " New user account has been created";
        
        $mailBody = "Hi,<br><br>
                    <h4>Welcome to D2D!. </h4><br><br>
                    Here we have the login information for you.<br><br>
                    Username : ".$dataArray['username']."<br>
                    Password  : ".$dataArray['password']." <br><br>
                    Thanks & Regards, <br>
                    D2D Team.
                     ";
        Utils::sendEmail($email,$subject,$mailBody);
    }

    public function saveImage($params){
        $user = self::findOne($params['user_id']);
        $user->link = $params['link'];
        $user->image_type = $params['image_type'];
        if($user->update()){
            self::$message  = "The image has been saved successfully.";
        }
        else{
            self::$success = 0;
            self::$message  = "Some error occured while processing.";
        }
        return array ('success' => self::$success,'message'=>self::$message , 'data'=>$params);
    }
    
    public function uploadImage($imagedata){
        $user = self::findOne($imagedata['userId']);
        $user->link = $imagedata['image_name'];
        $user->save();
    }
}
