<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "contract_execution".
 *
 * @property int $id
 *  @property string|null $title
 * @property int|null $contract_id
 * @property int|null $user_id
 * @property int|null $exe_user_id
 * @property int|null $status_id
 * @property string|null $info
 * @property string|null $done_date
 * @property int|null $mark
 * @property string|null $receive_date
 * @property int|null $receive_user
 * @property int $created_at
 * @property int $updated_at
 */
class ContractExecution extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contract_execution';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contract_id', 'user_id', 'exe_user_id', 'status_id', 'mark', 'receive_user', 'created_at', 'updated_at'], 'integer'],
            [['info', 'title'], 'string'],
            [['done_date', 'receive_date'], 'safe'],
//            [['created_at', 'updated_at'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'contract_id' => 'Контракт',
            'user_id' => 'Создатель',
            'exe_user_id' => 'Исполнитель',
            'status_id' => 'Статус',
            'info' => 'Информация',
            'done_date' => 'Done Date',
            'mark' => 'Оценка',
            'receive_date' => 'Receive Date',
            'receive_user' => 'Получатель',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
