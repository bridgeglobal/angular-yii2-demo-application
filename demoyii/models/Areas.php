<?php
/**
 * This is the model library for all the area processing functions
 *
 * Created by : Bridge Global
 * Created on : 17-06-2016
 * Purpose    : The functions for area management
 */
namespace app\models;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "areas".
 *
 * @property string $id
 * @property integer $franchisee_id
 * @property string $composite_name
 * @property string $area_name
 * @property string $region
 * @property string $postcode
 * @property string $area_map
 * @property integer $total_households
 * @property string $demographic_area_1_yn
 * @property string $demographic_area_2_yn
 * @property string $demographic_area_3_yn
 * @property string $demographic_area_4_yn
 * @property string $demographic_area_commercial_yn
 * @property double $pay_bonuses
 * @property string $secret_shopper_yn
 * @property string $description
 * @property integer $round_difficulty
 * @property integer $created_by
 * @property string $created_at
 * @property integer $updated_by
 * @property string $updated_at
 * @property integer $deleted_by
 * @property string $deleted_at
 */
class Areas extends \yii\db\ActiveRecord
{
    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'areas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'franchisee_id', 'composite_name', 'area_name', 'postcode', 'total_households'], 'required'],
            [['franchisee_id', 'total_households', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['description'], 'string'],
            [['pay_bonuses'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['composite_name', 'area_name'], 'string', 'max' => 100],
            [['region', 'postcode'], 'string', 'max' => 50],
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
            'composite_name' => Yii::t('app', 'Composite Name'),
            'area_name' => Yii::t('app', 'Area Name'),
            'region' => Yii::t('app', 'Region'),
            'postcode' => Yii::t('app', 'Postcode'),
            'area_map' => Yii::t('app', 'Area Map'),
            'total_households' => Yii::t('app', 'Total Households'),
            'demographic_area_1_yn' => Yii::t('app', 'Demographic Area 1 Yn'),
            'demographic_area_2_yn' => Yii::t('app', 'Demographic Area 2 Yn'),
            'demographic_area_3_yn' => Yii::t('app', 'Demographic Area 3 Yn'),
            'demographic_area_4_yn' => Yii::t('app', 'Demographic Area 4 Yn'),
            'demographic_area_commercial_yn' => Yii::t('app', 'Demographic Area Commercial Yn'),
            'pay_bonuses' => Yii::t('app', 'Pay Bonuses'),
            'secret_shopper_yn' => Yii::t('app', 'Secret Shopper Yn'),
            'description' => Yii::t('app', 'Description'),
            'round_difficulty' => Yii::t('app', 'Round Difficulty'),
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
     * List of Areas
     *
     */
    public function listAreas($params) {
        $query = new Query;
        // compose the query
        //Conditions for the query are created
        $where = "1";
        /********** Checking the active/ inactive list begin ************/
        if(isset($params['status_yn']) && @$params['status_yn'] != "") {
           if(@$params['status_yn'] == "1") {
                $where .= " AND (a.deleted_at IS NULL OR a.deleted_at ='') ";
           }
           else{
                $where .= " ( a.deleted_at IS NOT NULL AND  a.deleted_at <>'' )";
           }           
        }
        /********** Checking the active/ inactive list end ************/
        $orderBy = "";
        $totalRecords = 0;
        /******************* Validates for certain parameters in the request ***************** */
        $utils = new Utils();
        $validateParams = array('userId', 'sort-by', 'sort-order', 'name', 'page', 'count','pagination','type','from', 'to', 'demographic_area_1_yn', 'demographic_area_2_yn', 'demographic_area_3_yn','demographic_area_4_yn','demographic_area_commercial_yn','composites');
        $searchParams = $utils->processSearchParams($params, $validateParams);

        if (!empty($searchParams)) { // List criterias passed from frontend
            $searchKey = urldecode($searchParams["name"]);;
            $sortBy = $searchParams["sort-by"];
            $sortOrder = strtolower($searchParams["sort-order"]) == 'dsc' ? 'DESC' : $searchParams["sort-order"];
            $count = $searchParams["count"];
            $page = $searchParams["page"];
            $userId = $searchParams["userId"];
            $searchType = $searchParams["type"]?$searchParams["type"]:"mixed";
            $searchFrom = $searchParams["from"];
            $searchTo = $searchParams["to"];
            $searchComposites = isset($searchParams['composites'])?$searchParams['composites']:array();
            $composites = "";
            if(!empty($searchComposites)) { // The searched composite values are grouped into a string
                foreach ($searchComposites as $comp) {
                    $composites .= '"'.$comp.'",';
                }
                $composites = rtrim($composites, ',');
            }
            if($searchFrom !='') { // If the starting area id is not null
                $where .= " AND a.id >= '".$searchFrom."'";
            }
            if($searchTo !='') {// If the ending area id is not null
                $where .= " AND a.id <='".$searchTo."'";
            }
            $demographicTypes = array('demographic_area_1_yn', 'demographic_area_2_yn','demographic_area_3_yn','demographic_area_4_yn','demographic_area_commercial_yn');
            
            if($searchType == 'mixed') { // IF the search is mixed, it matches the records having the given properties. 
                                        //Bt they can have another properties. For eg: demographic_area_1_yn = '1' means , that area can have demographic_area_2_yn = '1'
                $sql_add = "";
                foreach ($demographicTypes as $demo) {
                    if($searchParams[$demo] == '1') {
                        $sql_add .= $demo." = '1' OR ";
                    }
                }
                $sql_add = rtrim($sql_add,"OR ");
                
                if($sql_add !=""){
                    $where .= " AND ( ".$sql_add." )";
                }
            }
                
            if($searchType== 'pure') {  // If the search is pure, it matches the records with only the given properties. They should not have any other properties.
                //For eg: demographic_area_1_yn = '1' means , that area cannot have demographic_area_2_yn = '1'
                $sql_add = "";
                foreach ($demographicTypes as $demo) {
                    $sql_add .= $demo." = '".(@$searchParams[$demo]?@$searchParams[$demo]:'0')."' AND ";
                }
                $sql_add = rtrim($sql_add,"AND ");
                if($sql_add !=""){
                    $where .= " AND ( ".$sql_add." )";
                }
            }
            if($composites !='') {
                $where .= " AND a.composite_name IN (".$composites.")";
            }
            if(isset($params['postpeopleId'])) {
                //If the postpeople id is present , the existing area ids will be ignored from listing
                $where .= " AND a.id NOT IN (SELECT area_id FROM postpeople_areas WHERE postpeople_id='".$params['postpeopleId']."')";
            }
            
            $where .= ($searchKey != '') ? " AND ( a.area_name like '%" . $searchKey . "%'  OR a.id like '%" . $searchKey . "%') " : "";

            if(isset($params['orderId'])) {
                $areaIds = OrderAreas::getOrderAreas($params['orderId']);
                if(!empty($areaIds)) {
                    $where .= " AND a.id NOT IN (SELECT area_id FROM order_areas WHERE order_id='".$params['orderId']."') ";
                }
            }
            $orderBy = "a." . $sortBy . " " . $sortOrder;
            $offset = ($page - 1) * $count;
        }
        $where .= " AND a.franchisee_id = (SELECT franchisee_id FROM users u1 WHERE u1.id = '".$userId."')";
        $query->select('a.*')
                ->from('areas AS a')
                ->where($where)
                ->orderBy($orderBy);

        $totalRecords = $query->count(); // Total no of records without limit    
             
        // build and execute the query
        
        if(!empty($searchParams) && $searchParams['pagination']==1) {
            $query->limit($count)->offset($offset);
        }
        
        $data = $query->all();
        $pagination = Utils::generatePaginationArray($count, $page, $totalRecords);

        if ($data) {
            self::$message = "Areas List fetched Successfully";
        } else {
            self::$success = 0;
            self::$message = "Sorry. We are unable to find the areas.";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $data, 'pagination' => $pagination, "sortBy" => @$sortBy, "sortOrder" => @$sortOrder);
    }
}
