<?php
/*******************************************************
#Created By : Bridge Global
#Created On : 08-07-2016
#Purpose : User Notes Related Actions in the d2d project
 *******************************************************/
namespace app\models;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "user_notes".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $note
 * @property integer $created_by
 * @property string $created_at
 */
class UserNotes extends \yii\db\ActiveRecord
{
    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_notes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'note', 'created_by'], 'required'],
            [['user_id', 'created_by'], 'integer'],
            [['note'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'note' => Yii::t('app', 'Note'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
     public function afterValidate() {
        self::$fieldErrors = self::getErrors();
        if(!empty(self::$fieldErrors)){
            Utils::sendValidationMessages(self::$fieldErrors);
        }
    }
    /**
     *Select the list of all notes of user
     *
     */
    public static function listNotes($params) {
        $query = new Query;
        $where = "1 AND user_id ='".$params['user_id']."'";
        $orderBy = "";

        $totalRecords = 0;
        /************************* Validates for certain parameters in the request ***************** */
        $validateParams = array('userId', 'sort-by', 'sort-order', 'name', 'page', 'count');
        $searchFields = array("id"=>"n.id","created_at"=>"n.created_at","created_by"=>"n.created_by");
        $searchParams = Utils::processSearchParams($params, $validateParams,$searchFields);

        /****** Adding the where condition to the existing where condition *********/
        $where .= isset($searchParams['where']) ? $searchParams['where'] :'';
        $created_at = new \yii\db\Expression("DATE_FORMAT(`n`.`created_at`, '%Y-%m-%d') as created_at");
        
        $query->select("n.id, n.note, u.first_name as created_by")
                ->addSelect([$created_at])
                ->from('user_notes AS n')
                ->leftjoin('users AS u', "n.user_id = u.id")
                ->where($where);
         /****** Adding the sorting to query *********/    
        $query = isset($searchParams['orderBy']) ?  $query->orderBy($searchParams['orderBy']):$query;

        $totalRecords = $query->count(); // Total no of records without limit 

        if(!empty($searchParams)) {
            /****** Adding the limit to query *********/    
            $query = isset($searchParams['count']) ?  $query->limit($searchParams['count']):$query;
            $query = isset($searchParams['offset']) ?  $query->offset($searchParams['offset']):$query;
        }

        $data = $query->all(); // Executing the fetching query;
        /****** Generating the pagination array according to the fetched data *********/    
        $pagination = Utils::generatePaginationArray($searchParams['count'], $searchParams['page'], $totalRecords);

        if ($data) {
            self::$message = "Notes List fetched Successfully";
        } else {
            self::$success = 0;
            self::$message = "Sorry. We are unable to find the notes.";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $data, 'pagination' => $pagination, "sortBy" => @$sortBy, "sortOrder" => @$sortOrder);
   
    }
     /**
     * Add a new note
     *
     */
    public static function saveNote($params) {
        $fieldsToBeInserted = self::attributes();
        $actionRequested =(isset($params['id']) && $params['id'] !='') ? "edit" : "add";
        if ($actionRequested == 'edit') {
            $id = $params['id'];
        }
        $noteData = ($actionRequested == 'edit') ? self::findOne($params['id']) : self::instantiate($params);
        $params['user_id'] = $params['user_id'];
        $params['note'] = nl2br($params['note']);
        $noteData = Utils::processInsertParams($noteData, $params, $fieldsToBeInserted); // Processsing the insert parameters
        $noteData = Utils::addExtraInfo($noteData,@$params['userId'],$actionRequested);

        if ($noteData->save() !== false) {
            $id = Yii::$app->db->getLastInsertID();
            $params['id'] = $id;
            self::$message = "User Note saved Successfully";
        }
        else{
            self::$success = 0;
            self::$message = "Some error occured while processing your request";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => @$params);
    }
    
    /**
     * Get the details of an existing note
     *
     */
    public function getNote($params) {
        $query = new Query;
        // compose the query
        //Conditions for the query are created
        $noteData = self::findOne($params['id']);
        if (empty($noteData)) {
            self::$success = 0;
            self::$message = "Sorry. The record cannot be found.";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $noteData);
    }
    
     /**
     * Delete an existing note
     *
     */
    public function deleteNote($data) {
        $usernote = self::find()->where(['id' => $data['id']])->one();
        if (empty($usernote)) {
            self::$success = 0;
            self::$message = "The note has already been removed.";
        } 
        else {
            self::deleteAll(['id' => $data['id']]);
            self::$message = "The Note has been removed Successfully.";
        }
        return array('success' => self::$success, 'message' => self::$message, 'data' => $data);
    }
    
}
