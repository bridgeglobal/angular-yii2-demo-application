<?php
/***********************************************************************
  #Created By : Bridge Global
  #Created On : 13-06-2016
  #Purpose : Post people Related database processing in the d2d project
 ************************************************************************/
namespace app\models;

use Yii;
use yii\db\Query;
/**
 * This is the model class for table "postpeople".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property integer $user_id
 * @property string $street
 * @property string $town
 * @property string $city
 * @property string $country
 * @property string $postcode
 * @property string $telephone
 * @property string $fax
 * @property string $note1
 * @property string $note2
 * @property string $note3
 * @property integer $driver_yn
 * @property integer $deliverer_status
 * @property integer $payment_method
 * @property integer $area_id
 * @property string $composite_name
 * @property string $region
 * @property string $drop_off_location
 * @property integer $gps_yn
 * @property integer $rating
 * @property integer $pay_bonus
 * @property integer $created_by
 * @property string $created_at
 * @property integer $updated_by
 * @property string $updated_at
 * @property integer $deleted_by
 * @property string $deleted_at
 */
class Postpeople extends \yii\db\ActiveRecord {
    
    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postpeople';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['franchisee_id', 'payment_method', 'driver_yn','street','postcode'], 'required'],
            [['franchisee_id', 'user_id', 'driver_yn', 'deliverer_status', 'payment_method', 'area_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['note1', 'note2', 'note3'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['town', 'city', 'country', 'postcode', 'telephone', 'fax', 'composite_name', 'region'], 'string', 'max' => 50],
            [['street'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'franchisee_id' => Yii::t('app', 'Franchisee ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'street' => Yii::t('app', 'Street'),
            'town' => Yii::t('app', 'Town'),
            'city' => Yii::t('app', 'City'),
            'country' => Yii::t('app', 'Country'),
            'postcode' => Yii::t('app', 'Postcode'),
            'telephone' => Yii::t('app', 'Telephone'),
            'fax' => Yii::t('app', 'Fax'),
            'note1' => Yii::t('app', 'Note1'),
            'note2' => Yii::t('app', 'Note2'),
            'note3' => Yii::t('app', 'Note3'),
            'driver_yn' => Yii::t('app', 'Driver Status'),
            'deliverer_status' => Yii::t('app', 'Deliverer Status'),
            'payment_method' => Yii::t('app', 'Payment Method'),
            'area_id' => Yii::t('app', 'Area ID'),
            'composite_name' => Yii::t('app', 'Composite Name'),
            'region' => Yii::t('app', 'Region'),
            'drop_off_location' => Yii::t('app', 'Drop Off Location'),
            'gps_yn' => Yii::t('app', 'GPS Status'),
            'rating' => Yii::t('app', 'Rating'),
            'pay_bonus' => Yii::t('app', 'Pay Bonus'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }
    public function afterValidate() {
        self::$fieldErrors = self::getErrors();
        if(!empty(self::$fieldErrors)){
            Utils::sendValidationMessages(self::$fieldErrors);
        }
    }
    /**
     * List of Postpeople
     *
     */
    public function listpostPeople($params) {
        $query = new Query;
        // compose the query
        //Conditions for the query are created
        $where = "p.deleted_at IS NULL AND u.franchisee_id = (SELECT franchisee_id FROM users u1 WHERE u1.id = '".$params['userId']."')";
        /********** Checking the active/ inactive list begin ************/
        if(isset($params['status_yn']) && @$params['status_yn'] != "") {
           if(@$params['status_yn'] == "1") {
                $where .= " AND u.status_yn ='1'";
           }
           else{
                $where .= " AND ( u.status_yn ='0' OR  u.status_yn ='2' )";
           }           
        }
        /********** Checking the active/ inactive list end ************/
        $orderBy = "";
        $totalRecords = 0;
        /*         * ****************** Validates for certain parameters in the request ***************** */
        $validateParams = array('userId', 'sort-by', 'sort-order', 'name', 'page', 'count');
        $searchParams = Utils::processSearchParams($params, $validateParams);

        if (!empty($searchParams)) { // List criterias passed from frontend
            $searchKey = urldecode($searchParams["name"]);
            $searchKeys = explode(' ',$searchKey);
            $sortBy = $searchParams["sort-by"];
            $sortBy = ($sortBy == 'name')?'first_name':$sortBy;
            $sortOrder = strtolower($searchParams["sort-order"]) == 'dsc' ? 'DESC' : $searchParams["sort-order"];
            $count = $searchParams["count"];
            $page = $searchParams["page"];
            $userId = $searchParams["userId"];
            if(!empty($searchKeys)) {
                $where .= " AND ( u.first_name like '%" . $searchKey . "%' OR  u.last_name like '%" . $searchKey . "%' ";
                foreach ($searchKeys as $key) {
                    $where .= " OR u.first_name like '%" . $key . "%' OR  u.last_name like '%" . $key . "%' ";
                }
               $where .= " )  ";
            }
            $userFields = array("first_name","email","mobile","username");       
            $postPeopleFields = array("id","town");  

            if(in_array($sortBy, $userFields)) {
                $orderBy = "u." . $sortBy . " " . $sortOrder;
            }
            else if(in_array($sortBy, $postPeopleFields)) {
                $orderBy = "p." . $sortBy . " " . $sortOrder;
            }
            
            $offset = ($page - 1) * $count;
        }
        $query->select('p.id, p.user_id, p.street,p.city, p.town, p.country, p.postcode, p.telephone, p.created_by , u.username, u.email, u.title, u.status_yn, u.title, u.first_name, u.last_name')
                ->from('postpeople AS p')
                ->leftJoin('franchisee AS fr', "fr.id = p.franchisee_id")
                ->leftJoin('users AS u', "u.id = p.user_id")
                ->where($where)
                ->orderBy($orderBy);
        $totalRecords = $query->count(); // Total no of records without limit         
        // build and execute the query
        //print_r($query);
        $data = $query->limit($count)->offset($offset)->all();
        $pagination = Utils::generatePaginationArray($count, $page, $totalRecords);

        if ($data) {
            self::$message = "Postpeople List fetched Successfully";
        } else {
            self::$success = 0;
            self::$message = "Sorry. We are unable to find the Postpeople.";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $data, 'pagination' => $pagination, "sortBy" => @$sortBy, "sortOrder" => @$sortOrder);
    }
    
    /**
     * Add a new post people
     *
     */
    public static function savePostPeople($params) {
        $fieldsToBeInserted = self::attributes();
        $actionRequested = isset($params['id']) ? "edit" : "add";
        $emailsArray = isset($params['emails']) ? array_column(@$params['emails'], 'value'): array();// getting all the given emails to a single array
        if ($actionRequested == 'edit') {
            $id = $params['id'];
            $exists = Users::emailExists($emailsArray, $params['user_id']);
        } else {
            $exists = Users::emailExists($emailsArray);
        }
        if ($exists) {
            self::$success = 0;
            self::$message = "The email ".$exists." has already been taken. Please try again with another email.";
        } else {
            /********************** The record is a valid one , so we can proceed with saving ************** */
            $postPeopleData = ($actionRequested == 'edit') ? self::findOne($params['id']) : self::instantiate($params);
            if (!empty($postPeopleData)) {
                $params['telephone'] = (isset($params['telephones'][0]))?@$params['telephones'][0]['value']:NULL;
                $params['website'] = (isset($params['websites'][0]))?@$params['websites'][0]['value']:NULL;
                
                if(isset($params['addresses'])) {
                    $params['street'] = (isset($params['addresses'][0]))?@$params['addresses'][0]['street']:NULL;
                    $params['town'] = (isset($params['addresses'][0]))?@$params['addresses'][0]['town']:NULL;
                    $params['city'] = (isset($params['addresses'][0]))?@$params['addresses'][0]['city']:NULL;
                    $params['country'] = (isset($params['addresses'][0]))?@$params['addresses'][0]['country']:NULL;
                    $params['postcode'] = (isset($params['addresses'][0]))?@$params['addresses'][0]['postcode']:NULL;
                }
                
                $postPeopleData = Utils::processInsertParams($postPeopleData, $params, $fieldsToBeInserted); // Processsing the insert parameters
                $postPeopleData = Utils::addExtraInfo($postPeopleData,@$params['userId'],$actionRequested);// saving the created/updated info
                if ($postPeopleData->save() !== false) {
                    $primaryEmail = $emailsArray[0];
                    $postPeopleDetails = array('title' => @$params['title'], 'first_name' => @$params['first_name'],'last_name' => @$params['last_name'],'mobile'=>@$params['mobile'],'franchisee_id' => $params['franchisee_id'], 'email' => $primaryEmail, 'username' => @$params['username'], 'password' => @$params['password'], 'status_yn' => @$params['status_yn'], 'created_by' => @$params['userId'], 'image_type' => @$params['image_type'], 'emails'=> @$params['emails'], 'addresses' => @$params['addresses'], 'telephones' => @$params['telephones'], 'websites' => @$params['websites']);
                    if(@$params['link'] !=''){
                        $postPeopleDetails['link'] = @$params['link'];
                    }
                    if ($actionRequested == 'add') {
                        $id = Yii::$app->db->getLastInsertID();                      
                    } else {
                        $postPeopleDetails['user_id'] = $params['user_id'];
                    }

                    $postPeopleDetails['post_people_id'] = $id;
                    $postPeopleInfo = Users::createFranchiseeUser($postPeopleDetails, "post-people",$actionRequested); //passing the required information to users model.
                    /*************Creating the user as we have added a post people details *********** */
                    if ($postPeopleInfo['success'] == 0) { // if the admin creation has been stopped somewhere, we will give an error message to user.
                        self::$message = $postPeopleInfo['message'];
                    } else {
                        // saving the userid to the postpeople table
                        $postPeople = self::findOne($id); 
                        $postPeople->user_id = $postPeopleInfo['data']['userId'];
                        $postPeople->save();
                        $postPeopleInfo['data']['id'] = $id;
                        self::$message = "Postpeople details saved Successfully";
                    }
                } else {
                    self::$success = 0;
                    self::$message = "Some error occured while processing your request";
                }
            } else {
                self::$success = 0;
                self::$message = "Sorry. Your request cannot be processed, as the post people does not exist.";
            }   
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => @$postPeopleInfo['data']);
    }
    
    /**
    * Get the details of an existing post people
    *
    */
    public function getPostPeople($params) {
        $query = new Query;
        // compose the query
        $postPeopleData = array();
        $where = " u.franchisee_id = (SELECT franchisee_id FROM users u1 WHERE u1.id = '".$params['userId']."')";
        /*********Postperson login **********************/
        if(isset($params['userType']) && @$params['userType'] == '8') {
            $params['id'] = $params['PostpersonId'];
        }
        //Conditions for the query are created
        $basic = $query->select('p.id, p.franchisee_id, p.user_id, p.street, p.town, p.city, p.country, p.postcode, p.telephone, p.fax, p.note1, p.note2,
            p.note3, p.driver_yn, p.deliverer_status, p.payment_method, p.area_id, p.composite_name, p.region, p.drop_off_location, p.rating, p.gps_yn, p.pay_bonus, p.created_by,
            u.title, u.first_name,u.last_name,u.username,u.email,u.mobile,u.link,u.image_type,u.status_yn')
                        ->from('postpeople AS p')
                        ->leftJoin('users AS u', "u.id = p.user_id")
                        ->where($where)
                        ->andWhere(["p.id" => $params['id'], "p.deleted_at" => NULL])->one();
        if (empty($basic)) {
            self::$success = 0;
            self::$message = "Sorry. The record cannot be found.";
        }
        else{
            $postPeopleData['basic'] = $basic; // The basic profile details of a postperson

            // Most suited delivery areas list of a postperson
            $query = new Query;
            $most = $query->select('a.composite_name,a.area_name,a.region,a.postcode, a.total_households, pa.area_id as id')
                    ->from('postpeople_areas AS pa')
                    ->leftJoin('areas AS a','pa.area_id = a.id')
                    ->where(["pa.postpeople_id" => $params['id'],"area_type"=>"most"])->all();

            // Moderately suited delivery areas list of a postperson   
            $query = new Query;
            $moderate = $query->select('a.composite_name,a.area_name,a.region,a.postcode, a.total_households, pa.area_id as id')
                        ->from('postpeople_areas AS pa')
                        ->leftJoin('areas AS a','pa.area_id = a.id')
                        ->where(["pa.postpeople_id" => $params['id'],"area_type"=>"moderate"])->all();

            // Least suited delivery areas list of a postperson   
            $query = new Query;
            $least = $query->select('a.composite_name,a.area_name,a.region,a.postcode, a.total_households, pa.area_id as id')
                        ->from('postpeople_areas AS pa')
                        ->leftJoin('areas AS a','pa.area_id = a.id')
                        ->where(["pa.postpeople_id" => $params['id'],"area_type"=>"least"])->all();

            $postPeopleData['areas'] = array('most'=>$most,'moderate' => $moderate, 'least' => $least); 

            // Activities of a postperson  to mark over the calendar
            $query = new Query;
            $activity = $query->select('id, title, activity, activity_start_date as start, activity_end_date as end')->from('postpeople_activity AS pac')
                            ->where(["pac.postpeople_id" => $params['id']])->all();
            $postPeopleData['activity'] = $activity; 

            /***********Fetching the extra infoermation of  apostperson **************/
            $postPeopleData['addresses'] = UserAddress::find()->where(['user_id'=> $postPeopleData['basic']['user_id']])->all();
            $postPeopleData['emails'] = UserInfoExtra::find()->where(['user_id'=> $postPeopleData['basic']['user_id'],'info_id' => '1'])->all();
            $postPeopleData['telephones'] = UserInfoExtra::find()->where(['user_id'=> $postPeopleData['basic']['user_id'],'info_id' => '2'])->all();
            $postPeopleData['websites'] = UserInfoExtra::find()->where(['user_id'=> $postPeopleData['basic']['user_id'],'info_id' => '3'])->all();
            $postPeopleData['web'] = UserInfoExtra::find()->where(['user_id'=> $postPeopleData['basic']['user_id'],'info_id' => '3','url_type' => 'Website'])->all();
            $postPeopleData['social'] = UserInfoExtra::find()->where(['user_id'=> $postPeopleData['basic']['user_id'],'info_id' => '3','url_type' => 'Social'])->all();

            $postPeopleData['googlemap'] = Utils::getGoogleMapUrl($postPeopleData['basic']); // Fetching the google map location ofa postperson
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $postPeopleData);
    }
    
    
    /**
     * Add a new post people area
     *
     */
    public static function saveDeliveryAreas($params) {
        $fieldsToBeInserted = PostpeopleAreas::attributes();
        $areaIds =array();
        $areaTypes = array('most','moderate','least');
        foreach ($areaTypes as $type) {
            if(!empty($params[$type])){
                foreach($params[$type] as $area){
                    $areaParams = array('postpeople_id' => $params['postPeopleId'], "area_id" => $area['id'], 'area_type'=>$type);
                    $new_area = PostpeopleAreas::find()->where(["postpeople_id" => $params['postPeopleId'], "area_id" => $areaParams['area_id']])->one();
                    if(empty($new_area)){
                        $new_area = PostpeopleAreas::instantiate($areaParams);
                    }
                    $new_area = Utils::processInsertParams($new_area, $areaParams, $fieldsToBeInserted); 
                    if($new_area->save() !== false){
                        array_push($areaIds, $areaParams['area_id']); // pushing the saved areaids
                        self::$message = "Area saved successfully";
                    } 
                    else{
                        self::$success = 0;
                        self::$message = "Areas not saved";
                    }
                }
            }
        }
        self::removeDeliveryAreas($areaIds,$params['postPeopleId']); // Removing the deleted areas
        return array('success' => self::$success, 'message' => self::$message, 'data' => @$postPeopleInfo['data']);
    }
    
    /**
     * Remove Delivery area
     *
     */
    public function removeDeliveryAreas($areaIds,$postPeopleId) {
        $query = new Query;
        if(!empty($areaIds)){
            /******** Remove the areas of postpeople which are not present in the saved list *************/
            PostpeopleAreas::deleteAll(['and', 'postpeople_id = :postpeople_id', ['not in', 'area_id', $areaIds]], [
                    ':postpeople_id' => $postPeopleId
                ]);
        }
        else {
            PostpeopleAreas::deleteAll(['postpeople_id' => $postPeopleId]);
        }
        return true;
    }

    /**
     * Add a new activity
     *
     */
    public static function saveActivity($params) {
        $fieldsToBeInserted = PostpeopleActivity::attributes();
        $activityIds =array();
        if(!empty($params['events'])){ // If there are no events
            foreach ($params['events'] as $act) {
               $actionRequested = isset($act['id']) ? "edit" : "add";
               if ($actionRequested == 'edit') {
                    $id = $act['id'];
               } 
               if($act['activity'] == 'WA'){
                // If it is a round assignment
                }
                else{
                    $activityParams = array('postpeople_id' => $params['postPeopleId'], "activity" => $act['activity'], 'title'=> $act['title'], 'activity_start_date' => $act['start'], 'activity_end_date' => (isset($act['end']) && @$act['end'] !='')?@$act['end']:$act['start']);
                    $newActivity = ($actionRequested == 'edit') ? PostpeopleActivity::findOne(["postpeople_id" => $params['postPeopleId'], "id" => $id]) : PostpeopleActivity::instantiate($activityParams);
                    
                    $newActivity = Utils::processInsertParams($newActivity, $activityParams, $fieldsToBeInserted);
                    $newActivity = Utils::addExtraInfo($newActivity, $params['userId'], $actionRequested);

                    if($newActivity->save() !== false){
                        if($actionRequested == 'add') {
                            $id = Yii::$app->db->getLastInsertID();  
                        }
                        else {
                            $id = $act['id'];
                        }                         
                        array_push($activityIds, $id);// pushing the saved activity ids
                        self::$message = "Activity saved successfully";
                    } 
                    else{
                        self::$success = 0;
                        self::$message = "Activity not saved";
                    }
                }
            }
        }
        self::removeActivity($activityIds,$params['postPeopleId']); // Removing the deleted activities
        return array('success' => self::$success, 'message' => self::$message, 'data' => @$params);
    }
    /**
     * Remove Activity
     *
     */
    public function removeActivity($activityIds,$postPeopleId) {
        $query = new Query;
        if(!empty($activityIds)){
            /******** Remove the activity of postpeople which are not present in the saved list *************/
            PostpeopleActivity::deleteAll(['and', 'postpeople_id = :postpeople_id', ['not in', 'id', $activityIds]], [
                    ':postpeople_id' => $postPeopleId
                ]);
        }
        else {
            PostpeopleActivity::deleteAll(['postpeople_id' => $postPeopleId]);
        }
        return true;
    }
    
     /**
     * Change the status of an existing post people
     *
     */
    public function changeStatus($params) {
        $postpeople = self::find()->where(['id' => $params['id'], 'deleted_at' => null])->one();
        if (empty($postpeople)) {
            self::$success = 0;
            self::$message = "The post people does not exist.";
        } else {
            $newStatus = $params['status_yn'];
            $update = true;
            $user = Users::findOne($postpeople->user_id);
            $user->status_yn = $newStatus;
            $user->updated_at = date("Y-m-d G:i:s");
            $user->updated_by = $params['userId'];
            if($newStatus == '1'){ // user is to be activated
                $query = new Query;
                $franchisee = $query->from('franchisee AS fr')->leftJoin('users AS u', 'fr.user_id = u.id')->where(['fr.id' => $postpeople->franchisee_id, 'u.status_yn' => '1'])->one();
                if(empty($franchisee)){ // If the franchisee is not active you can't activate a postpeople under that franchisee
                    $update = false; 
                }
            }
            if($user->update()){
                self::$message = "The post people status has been changed successfully.";
            }
            else{
                self::$success = 0;
                self::$message = "Some error occured while processing the request";
            }
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $params);
    }

    /**
     * Get the count of all postpeople
     *
     */
    public function getPostpeopleCount($params) {
        $query = new Query;
        // compose the query
        //Conditions for the query are created

        $where = "p.deleted_at IS NULL  AND u.status_yn ='1' AND p.franchisee_id = (SELECT franchisee_id FROM users u1 WHERE u1.id = '".$params['userId']."')";
        
        $query->select("p.id")
                ->from('postpeople AS p')
                ->leftJoin('users AS u','u.id = p.user_id')
                ->where($where);
        $totalRecords = $query->count(); // Total no of records without limit ::$success, 'message' => self::$message, 'data' => $customers);
        return array('success' => self::$success, 'message' => self::$message, 'data' => $totalRecords);
    }
    

}
