<?php
/*******************************************************
  #Created By : Bridge Global
  #Created On : 07-07-2016
  #Purpose : User extra info Related Actions in the d2d project
 *******************************************************/
namespace app\models;
use Yii;
use yii\db\Query;
/**
 * This is the model class for table "user_info_extra".
 *
 * @property integer $id
 * @property integer $info_id
 * @property integer $user_id
 * @property string $value
 * @property string $value_type
 * @property string $url_type
 */
class UserInfoExtra extends \yii\db\ActiveRecord
{
    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info_extra';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['info_id', 'user_id', 'value'], 'required'],
            [['info_id', 'user_id'], 'integer'],
            [['value'], 'string'],
            [['value_type','url_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'info_id' => Yii::t('app', 'Info ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'info_value' => Yii::t('app', 'Info Value'),
            'value_type' => Yii::t('app', 'Value Type'),
            'url_type' => Yii::t('app', 'Url Type'),
        ];
    }
    /**
     * Save the extra information of a user
     *
     */
    public static function saveExtraInfo($extraInfo, $userId) {
        /***************** Saving the extra infos ***********/
        $query = new Query;
        $extraIndexes = $query->from('info_types')->all();
        $ids = array();
        foreach ($extraIndexes as $extra) {
            $itemId = $extra['id'];
            $itemName = $extra['field_name'];

            if(!empty($extraInfo[$itemName])) {
                $fieldsToBeInserted = self::attributes(); // Getting the fields in the address table
                foreach($extraInfo[$itemName] as $records) {
                    $actionRequested = isset($records['id']) ? "edit" : "add";
                    $entryData = ($actionRequested == 'edit') ? self::findOne($records['id']) : self::instantiate($records);
                    if (!empty($entryData)) {
                        $records['user_id'] = $userId;
                        $records['info_id'] = $itemId;
                        $entryData = Utils::processInsertParams($entryData, $records, $fieldsToBeInserted); // Processsing the insert parameters
                        if($entryData->save() !== false) {
                            if($actionRequested == 'add') {
                                $id = Yii::$app->db->getLastInsertID();  
                            }
                            else {
                                $id = $records['id'];
                            }
                            array_push($ids, $id);// Pushing the info id to be kept in our db
                            self::$message = " Data Saved successfully";
                        }
                        else {
                            self::$success = 0;
                            self::$message = "Sorry. Your request cannot be processed.";
                        }
                    }
                } // End of foreach
            }
        }
        self::removeExtraInfo($ids, $userId);
        return array('success' => self::$success, 'message' => self::$message);
    }
    
    /**
     * Remove Extra info
     *
     */
    public function removeExtraInfo($ids,$userId) {
        if(!empty($ids)){// If the informations are not in the saved list
            self::deleteAll(['and', 'user_id = :user_id', ['not in', 'id', $ids]], [':user_id' => $userId
                ]);
        }
        else {// If the informations need not be saved
            self::deleteAll(['user_id' => $userId]);
        }
        return true;
    }
    
}
