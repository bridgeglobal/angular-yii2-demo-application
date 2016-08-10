<?php
/*******************************************************
  #Created By : Bridge Global
  #Created On : 07-07-2016
  #Purpose : User address Related Actions in the d2d project
 *******************************************************/
namespace app\models;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "user_addresses".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $street
 * @property string $town
 * @property string $city
 * @property string $country
 * @property string $postcode
 * @property string $address_type
 */
class UserAddress extends \yii\db\ActiveRecord
{
    public static $message = '';
    public static $success = 1;
    public static $fieldErrors = array();
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_addresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'street', 'postcode', 'address_type'], 'required'],
            [['user_id'], 'integer'],
            [['street'], 'string'],
            [['town', 'city', 'country'], 'string', 'max' => 100],
            [['address_type'], 'string', 'max' => 50],
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
            'street' => Yii::t('app', 'Street'),
            'town' => Yii::t('app', 'Town'),
            'city' => Yii::t('app', 'City'),
            'country' => Yii::t('app', 'Country'),
            'postcode' => Yii::t('app', 'Postcode'),
            'address_type' => Yii::t('app', 'Address Type'),
        ];
    }

    public function afterValidate() {
        self::$fieldErrors = self::getErrors();
        if(!empty(self::$fieldErrors)){
            Utils::sendValidationMessages(self::$fieldErrors);
        }
    }
    
     /**
     * Save the address of a user
     *
     */
    public static function saveAddress($addresses, $userId) {
        $addressIds = array();
        if(!empty($addresses)) {
            $fieldsToBeInserted = self::attributes(); // Getting the fields in the address table
            
            foreach ($addresses as $userAddress) {
                $userAddress['user_id'] = $userId;
                $actionRequested = isset($userAddress['id']) ? "edit" : "add";
                $addressData = ($actionRequested == 'edit') ? self::findOne($userAddress['id']) : self::instantiate($userAddress);
                if (!empty($addressData)) {
                    $addressData = Utils::processInsertParams($addressData, $userAddress, $fieldsToBeInserted); // Processsing the insert parameters
                    if ($addressData->save()!== false) {
                        if($actionRequested == 'add') {
                            $id = Yii::$app->db->getLastInsertID();  
                        }
                        else {
                            $id = $userAddress['id'];
                        }
                        array_push($addressIds, $id);
                        self::$message = " Address Saved successfully";
                    }
                    else {
                        self::$success = 0;
                        self::$message = "Sorry. Your request cannot be processed.";
                    }
                }
            } // End of foreach
        }
        self::removeAddress($addressIds,$userId);
        return array('success' => self::$success, 'message' => self::$message);
    }
     /**
     * Remove User Addresses
     *
     */
    public function removeAddress($addressIds,$userId) {
        $query = new Query;
        if(!empty($addressIds)){ // If the addresses are not in the saved list
            self::deleteAll(['and', 'user_id = :user_id', ['not in', 'id', $addressIds]], [
                    ':user_id' => $userId
                ]);
        }
        else {// If the addresses need not be saved
            self::deleteAll(['user_id' => $userId]);
        }
        return true;
    }
    
}
