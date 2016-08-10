<?php
namespace app\models;
use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\helpers\Json;

/**
 * This is the common library for all the basic data processing functions
 *
 * Created by : Bridge Global
 * Created on : 03-06-2016
 * Purpose    : The functions which are common to all http requests and data processing
 */
class Utils {
    public static $message = '';
    public static $success = 1;
    /**
     * Process Input Requests
     *
     * @param json objects
     * @return php array data
     */
    public function processRequest($requiredParams=array(),$permissionName='',$auth = true){
        //echo $permissionName;die();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        //read the post input (use this technique if you have no post variable name):
        $post = file_get_contents("php://input");
        $data = Json::decode($post, true); //decode json post input as php array:
        if ($auth) { // validating the auth key for a user
            self::authenticateApi($requiredParams,$data,$permissionName);
            $data['params']['userId'] = @$data['userId'];
            if(@$data['userId'] !='') {
                $data['params']['userType'] = Utils::fetchSingleRecord("type_id", "users"," id='".$data['userId']."'");
                if($data['params']['userType']== '8') { //postperson
                    $data['params']['PostpersonId'] = Utils::fetchSingleRecord("id","postpeople", " user_id ='".$data['userId']."'");
                }
            }
        }
        return $data['params'];
    }
    /**
     * Send Responses to Frontend 
     *
     * @param php array
     * @return json object
     */
    public function sendResponse($response){
        //respond with json content type:
        header('Content-type:application/json');
        echo Json::encode($response);//encode the response as json:
    
        exit();//use exit() ,if in debug mode and don't want to return debug output
    }
    /**
     * Authenticate all http requests with the auth key 
     *
     * @param php array
     * @return json object
     */
    public function authenticateApi($requiredParams,$data,$permissionName){
        $logout = 0;
        
        // validates the authkey with the given userid to prevent invalid user login
        $isValid = Users::authenticateApi(@$data['authKey'], @$data['userId']);
        if (!$isValid) {
            self::$message .= "User authentication failed. Please try after sometime.";
            $logout = 1;
        }
        else{ 
            if($permissionName !=''){
               $hasAccess = $this->authenticatePageRequest($data['userId'],$permissionName); // Checking whether the logged in user has the access to this particular feature.
                // validating the received parameters against the requested ones
                if(!empty($requiredParams)){
                    $receivedParams = array_keys($data['params']);
                    foreach($requiredParams as $param){
                        if(!in_array($param, $receivedParams) || $data['params'][$param] == ''){
                            self::$message .= $param." is required.<br>";
                        }
                    }
                } 
            }
        }
       
        if(self::$message != ''){
            self::$success = 0;
            $result = array('success' => self::$success, 'message' => self::$message, 'logout' => $logout);
            $this->sendResponse($result);
        }
        else{
            return true;
        }   
    }
    
    /**
     * Authenticate all http requests with the auth key 
     *
     * @param php array
     * @return json object
     */
    public function authenticatePageRequest($userId,$permissionName){
        if($userId !=''){
            //$typeId = $this->fetchSingleRecord('type_id', "users", $where = " id = ".$userId);
            $userHasAccess = Users::hasAccessPermission($userId,$permissionName);
            if(!$userHasAccess){
                self::$message = "Sorry. You are not allowed to access this page. Please contact your site administrator";
            }
        }
        else{
            self::$message .= "userId is required";
        }
        
        if(self::$message != ''){
            self::$success = 0;
            $result = array('success' => self::$success, 'message' => self::$message);
            $this->sendResponse($result);
        }
        else{
            return true;
        } 
    }
    
    /**
     * Process Params for add or update
     *
     * @param json objects
     * @return php object
     */
    public function processInsertParams($dataRecord, $params, $fieldsToBeInserted){
        
        foreach ($params as $key => $val) {
            if (in_array($key, $fieldsToBeInserted)) { // If the field need not be skipped
                $dataRecord->$key = $val;
            }
        }
        return $dataRecord;
    }
    /**
     * Add extra info such a created by created at for add or update
     *
     * @param json objects
     * @return php object
     */
    public function addExtraInfo($dataRecord, $userId, $action){
        if($action== 'add') {
            $dataRecord->created_by = $userId;
            $dataRecord->created_at = date('Y-m-d G:i:s');
        }
        else if($action== 'edit') {
            $dataRecord->updated_by = $userId;
            $dataRecord->updated_at = date('Y-m-d G:i:s');
        }
        return $dataRecord;
    }
    /**
     * Automatically generates a password using function
     *
     * @param php array
     * @return json object
     */
    public function generatePassword($length = 6){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }
    /**
     * Automatically generates a password using function
     *
     * @param php vars
     * @return php array
     */
    public function generatePaginationArray($limit, $page, $totalRecords){
        $totalPages = ceil($totalRecords / $limit);
        return array("count" => @$limit, "page" => @$page, "pages" => $totalPages, "size" => $totalRecords);
    }
    /**
     * Process Params for searching and sorting
     * @param json objects
     * @return php object
     */
    public function processSearchParams($params, $validateParams, $searchFields=''){
        foreach ($validateParams as $val) {
            if (!isset($params[$val]) || @$params[$val] == '') {
                $value = "";
                switch ($val) {
                    case "userId" : $value = 0;
                        break;
                    case "sort-by" : $value = "id";
                        break;
                    case "sort-order" : $value = "DESC";
                        break;
                    case "name" : $value = "";
                        break;
                    case "page" : $value = 1;
                        break;
                    case "count" : $value = 10;
                        break;
                    case "pagination" : $value = 1;
                        break;
                    case "userType" : $value = "all";
                        break;
                }
                @$params[$val] = $value;
            }
            else{
                if($val == 'sort-order') {
                    $params[$val] = (@$params[$val] == 'dsc') ? "DESC": @$params[$val];
                }
            }
        }
        $searchParams = $params;
        if($searchFields !='') {
            if (!empty($params)) { // List criterias passed from frontend
                $searchParams['searchKey'] = urldecode($params["name"]);
                $sortOrder = strtolower($params["sort-order"]) == 'dsc' ? 'DESC' : $params["sort-order"];
                $sortBy = $params["sort-by"];
                if(array_key_exists($sortBy, $searchFields)) {
                    $sortBy = $searchFields[$sortBy];
                }
                $where = "";
                if($searchParams['searchKey'] !='') {
                    $where = " AND (";
                    foreach ($searchFields as $key => $value) {
                        $where .=  $value ." like '%" . $searchParams['searchKey'] . "%' OR ";
                    }
                    $where = rtrim($where, "OR ");
                    $where .= " ) ";
                }
                $searchParams['where']  = $where;        
                $orderBy = $sortBy . " " . $sortOrder;
                $searchParams['orderBy']  = $orderBy;
                $searchParams['offset'] = ($searchParams['page'] - 1) * $searchParams['count'];
            }
        }
        
        return $searchParams;
    }
    
    /**
     * sends all the validation messages as a single string
     *
     * @param json objects
     * @return php object
     */
    public function sendValidationMessages($errors){
        $errorMessages = '';
        //print_r($errors);exit;
        foreach ($errors as $key => $val) {
            $errorMessages .= $val[0]."<br>";
        }
        $result = array('success'=> 0,'message'=> $errorMessages);
        self::sendResponse($result);
    }
    
    /**
     * Fetch a single field from a table
     *
     * @param json objects
     * @return php object
     */
    public function fetchSingleRecord($fieldName, $tableName, $where){
        $query = new Query; // compose the query
        $result = $query->select($fieldName)
                ->from($tableName)
                ->where($where)
                ->one();
        if(!empty($result)){
            return @$result[$fieldName];
        }
        return false;
    }
    /******** Function for generating unique username **********/
    public function generateUsername($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $newUsername = '';
        for ($i = 0; $i < $length; $i++) {
            $newUsername .= $characters[rand(0, $charactersLength - 1)];
        }
        $isUnique = Users::isUniqueUsername($newUsername);
        if (!$isUnique) {
             $newUsername = self::generateUsername(6);
        }
        return $newUsername;
    }
    /******** Function for sending emails **********/
    public function sendEmail($toEmail, $subject , $mailBody , $fromEmail ='info@d2dserver.co.uk'){
        try{

            $emailHeader = self::getEmailHeader();

            $emailBody   = self::getEmailBody($mailBody);

            $emailFooter = self::getEmailFooter();

            $mailBody = $emailHeader.$emailBody.$emailFooter;

           Yii::$app->mailer->compose()
            ->setFrom('d2dserveruser@gmail.com')
            ->setTo($toEmail)
            ->setSubject($subject)
            //->setTextBody($mailBody)
            ->setHtmlBody($mailBody)
            ->send();
            return true;
           } 
           catch(Exception $ex){
               print_r($ex);
           }
           return true;
    }

     /******** Function for fetching the email header **********/
    public function getEmailHeader() {
        $header = '<!DOCTYPE html>
<html>
<head>
<title>Email Template</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<style type="text/css">
    /* CLIENT-SPECIFIC STYLES */
    body, table, td, a{-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
    table, td{mso-table-lspace: 0pt; mso-table-rspace: 0pt;} /* Remove spacing between tables in Outlook 2007 and up */
    img{-ms-interpolation-mode: bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */

    /* RESET STYLES */
    img{border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none;}
    table{border-collapse: collapse !important;}
    body{height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important;}

    /* iOS BLUE LINKS */
    a[x-apple-data-detectors] {
        color: inherit !important;
        text-decoration: none !important;
        font-size: inherit !important;
        font-family: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
    }

    /* MOBILE STYLES */
    @media screen and (max-width: 525px) {

        /* ALLOWS FOR FLUID TABLES */
        .wrapper {
          width: 100% !important;
            max-width: 100% !important;
        }

        /* ADJUSTS LAYOUT OF LOGO IMAGE */
        .logo img {
          margin: 0 auto !important;
        }

        /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
        .mobile-hide {
          display: none !important;
        }

        .img-max {
          max-width: 100% !important;
          width: 100% !important;
          height: auto !important;
        }

        /* FULL-WIDTH TABLES */
        .responsive-table {
          width: 100% !important;
        }

        /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
        .padding {
          padding: 10px 5% 15px 5% !important;
        }

        .padding-meta {
          padding: 30px 5% 0px 5% !important;
          text-align: center;
        }

        .padding-copy {
             padding: 10px 5% 10px 5% !important;
          text-align: center;
        }

        .no-padding {
          padding: 0 !important;
        }

        .section-padding {
          padding: 50px 15px 50px 15px !important;
        }

        /* ADJUST BUTTONS ON MOBILE */
        .mobile-button-container {
            margin: 0 auto;
            width: 100% !important;
        }

        .mobile-button {
            padding: 15px !important;
            border: 0 !important;
            font-size: 16px !important;
            display: block !important;
        }

    }

    /* ANDROID CENTER FIX */
    div[style*="margin: 16px 0;"] { margin: 0 !important; }
</style>
</head>
<body style="margin: 0 !important; padding: 0 !important;">

<!-- HIDDEN PREHEADER TEXT -->
<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
    Entice the open with some amazing preheader text. Use a little mystery and get those subscribers to read through...
</div>

<!-- HEADER -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#ffffff" align="center">
            <!--[if (gte mso 9)|(IE)]>
            <table align="center" border="0" cellspacing="0" cellpadding="0" width="500">
            <tr>
            <td align="center" valign="top" width="500">
            <![endif]-->
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;" class="wrapper">
                <tr>
                    <td align="center" valign="top" style="padding: 15px 0;" class="logo">
                        <a href="" target="_blank">
                            <img alt="Logo" src="http://d2dworks.cloudbydesign.co.uk/d2dserver/web/images/logod2d.png" width="326" height="128" style="display: block; font-family: Helvetica, Arial, sans-serif; color: #ffffff; font-size: 16px;" border="0">
                        </a>
                    </td>
                </tr>
            </table>
            <!--[if (gte mso 9)|(IE)]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </td>
    </tr>';

        return $header;
    }

     /******** Function for fetching the email body **********/
    public function getEmailBody($emailContent) {
        $body = '<tr>
        <td bgcolor="#ffffff" align="center" style="padding: 15px;">
            <!--[if (gte mso 9)|(IE)]>
            <table align="center" border="0" cellspacing="0" cellpadding="0" width="500">
            <tr>
            <td align="center" valign="top" width="500">
            <![endif]-->
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;" class="responsive-table">
                <tr>
                    <td>
                        <!-- COPY -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">'.$emailContent.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!--[if (gte mso 9)|(IE)]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </td>
    </tr>';

        return $body;
    }


     /******** Function for fetching the email footer **********/
    public function getEmailFooter() {
        $footer = '<tr>
            <td bgcolor="#ffffff" align="center" style="padding: 15px;">
                <!--[if (gte mso 9)|(IE)]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" align="center" style="padding: 20px 0px;">
                <!--[if (gte mso 9)|(IE)]>
                <table align="center" border="0" cellspacing="0" cellpadding="0" width="500">
                <tr>
                <td align="center" valign="top" width="500">
                <![endif]-->
                <!-- UNSUBSCRIBE COPY -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="max-width: 500px;" class="responsive-table">
                    <tr>
                        <td align="center" style="font-size: 12px; line-height: 18px; font-family: Helvetica, Arial, sans-serif; color:#666666;">
                            d2d address here
                            <br>
                            <a href="" target="_blank" style="color: #666666; text-decoration: none;">your text</a>
                            <span style="font-family: Arial, sans-serif; font-size: 12px; color: #444444;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                            <a href="" target="_blank" style="color: #666666; text-decoration: none;">View this email in your browser</a>
                        </td>
                    </tr>
                </table>
                <!--[if (gte mso 9)|(IE)]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </table>

    </body>
    </html>';

        return $footer;
    }

    public function fetchImageFromUrl($url) {
        if(strpos($url, "facebook.com")=== false) {
            $imageData =  self::fetchImageFromSocialMedia($url, time());
        }
        else {
            $result = self::fetchUserIdFromFb($url);
            if($result['success'] == '1') {
                $fbUid  = $result['data'];
                $fbPictureUrl = "http://graph.facebook.com/".$fbUid."/picture?type=large";
                $imageData =  self::fetchImageFromSocialMedia($fbPictureUrl,$fbUid);
            }
        }
        return @$imageData;
    }

    public function fetchUserIdFromFb($profileUrl){
        if($profileUrl !=''){
            
            /*Getting user id from a third party*/
            $apiUrl = 'http://findmyfbid.com';

            $data = array('url' => $profileUrl );

            // use key 'http' even if you send the request to https://...
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ),
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($apiUrl, false, $context);

            $uid = self::getPageData($result);  // User ID
            if(!$uid) {
                self::$success = 0;
                self::$message = "Sorry. We are not able to process the provided url.";
            }
        }
        else{
            self::$success = 0;
            self::$message = "Please provide a url to fetch image.";
        }
        return array('success'=>self::$success,'message'=>self::$message,'data'=>@$uid);
    }


    public function getPageData($data) {

        $dom = new \DOMDocument;
        $dom -> loadHTML( $data );
        $divs = $dom -> getElementsByTagName('code');

        foreach ( $divs as $div ) {
            return $div -> nodeValue;
        }
    }

    public function fetchImageFromSocialMedia($url,$fbUid){
        if($url !=''){
            $imageData = self::get_remote_data($url);
            if($imageData !='') {
                $imageDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'd2dserver'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'users';

                $assignedname = "users".time().'_'.$fbUid.".jpg";
                $filename = $imageDir.'/'.$assignedname;
                file_put_contents($filename, $imageData);
                self::$success = 1;
            }
            else {
                self::$success = 0;
                self::$message = "Some temperory issue occured. Please try after sometime.";
            }
        }
        else{
            self::$success = 0;
            self::$message = "Please provide a url to fetch image.";
        }
        return array('success'=>self::$success,'message'=>self::$message,'data'=>@$assignedname);
    }



    public function get_remote_data($url, $post_paramtrs = false) {
        try{
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
            curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
            curl_setopt($c, CURLOPT_MAXREDIRS, 10);
            $follow_allowed = ( ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
            //if ($follow_allowed) {
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            //}
            $data = curl_exec($c);
            $status = curl_getinfo($c);
            curl_close($c);
            return $data;
        }
        catch(Exception $ex) {
            print_r($ex);die();
        }
    }

    public function getGoogleMapUrl($userData) {
        $address ="";
        if($userData['street'] != '') {
             $address .=$userData['street'].", ";
        }
        if($userData['city'] != '') {
             $address .=$userData['city'].", ";
        }
        if($userData['country'] != '') {
             $address .=$userData['country'].", ";
        }
        if($userData['postcode'] != '') {
             $address .=$userData['postcode'];
        }

        $mapUrl = "http://maps.google.com/?q=".$address;

        return $mapUrl;
    }


}
