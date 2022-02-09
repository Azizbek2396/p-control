<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contract".
 *
 * @property int $id
 * @property int|null $project_id
 * @property string|null $title
 * @property string|null $description
 * @property float|null $price
 * @property int|null $user_id
 * @property string|null $file_url
 * @property int|null $status_id
 * @property string|null $deadline
 * @property int $created_at
 * @property int $updated_at
 */
class Contract extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contract';
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
            [['project_id', 'user_id', 'currency_id', 'status_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['price'], 'number'],
            [['deadline'], 'safe'],
            [['title', 'file_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Проект',
            'title' => 'Название',
            'description' => 'Описание',
            'price' => 'Цена',
            'currency_id' => 'Тип валюты',
            'user_id' => 'Создатель',
            'file_url' => 'File Url',
            'status_id' => 'Статус',
            'deadline' => 'Срок',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProjects()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    public function saveImage($filename)
    {
        $this->file_url = $filename;
        return $this->save(false);
    }

//    public function getImage()
//    {
//        return ($this->file_url) ? ('/uploads/' . $this->file_url) : '/no-image.png';
//    }

    public function deleteImage()
    {
        $imageUploadModel = new ImageUpload();
        $imageUploadModel->deleteCurrentImage($this->file_url);
    }

    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    public static function getContracts()
    {
        return ArrayHelper::map(Contract::find()->all(), 'id', 'title');
    }

    public static function getContrctById($id)
    {
        return Contract::find()->where(['id' => $id])->one();
    }
}
