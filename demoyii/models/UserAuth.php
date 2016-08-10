<?php
/**
 * This is the model library for all the user authentication functions
 *
 * Created by : Bridge Global
 * Created on :27-06-2016
 * Purpose    : The functions for users managemnt
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "user_auth".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $authentication_key
 * @property string $created_at
 */
class UserAuth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'authentication_key'], 'required'],
            [['user_id'], 'integer'],
            [['authentication_key'], 'string'],
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
            'authentication_key' => Yii::t('app', 'Auth Key'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
