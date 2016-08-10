<?php
namespace app\models;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "postpeople_activity".
 *
 * @property integer $id
 * @property integer $postpeople_id
 * @property string $title
 * @property integer $round_id
 * @property string $activity
 * @property string $activity_start_date
 * @property string $activity_end_date
 * @property integer $created_by
 * @property string $created_at
 * @property integer $updated_by
 * @property string $updated_at
 * @property integer $deleted_by
 * @property string $deleted_at
 */
class PostpeopleActivity extends \yii\db\ActiveRecord
{
    public static $success = 1;
    public static $message = '';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postpeople_activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['postpeople_id', 'title', 'activity', 'activity_start_date', 'activity_end_date', 'created_by'], 'required'],
            [['postpeople_id', 'round_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['activity_start_date', 'activity_end_date', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['activity'], 'string', 'max' => 4],
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
            'round_id' => Yii::t('app', 'Round ID'),
            'activity' => Yii::t('app', 'H-Holiday, WA - Work Assigned'),
            'activity_start_date' => Yii::t('app', 'Activity Start Date'),
            'activity_end_date' => Yii::t('app', 'Activity End Date'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }
}
