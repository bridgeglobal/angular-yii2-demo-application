<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "postpeople_areas".
 *
 * @property integer $id
 * @property integer $postpeople_id
 * @property integer $area_id
 * @property string $area_type
 */
class PostpeopleAreas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postpeople_areas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['postpeople_id', 'area_id'], 'required'],
            [['postpeople_id', 'area_id'], 'integer'],
            [['area_type'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'postpeople_id' => Yii::t('app', 'Postpeople ID'),
            'area_id' => Yii::t('app', 'Area ID'),
            'area_type' => Yii::t('app', 'most suitable,moderate,least'),
        ];
    }
}
