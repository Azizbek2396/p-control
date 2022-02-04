<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Task */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Задачи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$myRole = \app\models\User::getMyRole();
?>
<div class="task-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= ($myRole === "admin" || $myRole === "superAdmin") ? Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : "" ?>
        <?= ($myRole === "admin" || $myRole === "superAdmin") ? Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) : "" ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
//            'project_id',
//            'title:ntext',
//            'price',
//            'deadline',
//            'user_id',
//            'status_id',
//            'created_at',
//            'updated_at',

            [
                'label' => 'Проект',
                'value' =>  function($data) {
                    return \app\models\Project::find(['id' => $data->project_id])->one()->title;
                }
            ],
            [
                'label' => 'Название',
                'value' =>  function($data) {
                    return $data->title;
                }
            ],
            [
                'label' => 'Цена',
                'value' =>  function($data) {
                    return $data->price;
                }
            ],
            [
                'label' => 'Срок',
                'value' =>  function($data) {
                    return $data->deadline;
                }
            ],
            [
                'label' => 'Создатель',
                'value' =>  function($data) {
                    return \app\models\User::find()->where(['id' => $data->user_id])->one()->username;
                }
            ],
            [
                'label' => 'Статус',
                'value' =>  function($data) {
                    return \app\models\Status::find(['id' => $data->status_id])->one()->title;
                }
            ],
            [
                'label' => 'Создан',
                'value' =>  function($data) {
                    date_default_timezone_set('Asia/Tashkent');
                    return date('d M Y H:i:s',$data->created_at);
                }
            ],
        ],
    ]) ?>

    <br>
    <div class="row">
        <div class="col-9">
            <h1>Исполнение по <span style="color: rgba(0, 0, 0, 0.4);"><?= $model->title?></span></h1>
        </div>
        <div class="col-3">
            <?= (Yii::$app->user->id === $model->user_id) ? Html::a('Создать Исп по Задачу', ['task-execution/create', 'task_id' => $model->id], ['class' => 'btn btn-success float-right']) : "" ?>
        </div>
    </div>
    <br>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
//            'title',
//            'task_id',
//            'user_id',
//            'exe_user_id',
//            'status_id',
            //'info:ntext',
            //'done_date',
            //'mark',
            //'receive_date',
            //'receive_user',
            //'created_at',
            //'updated_at',
            [
                'label' => 'Название',
                'value' =>  function($data) {
                    return $data->title;
                }
            ],
            [
                'label' => 'Задача',
                'value' =>  function($data) {
                    return \app\models\Task::find()->where(['id' => $data->task_id])->one()->title;
                }
            ],
            [
                'label' => 'Исполнитель',
                'value' =>  function($data) {
                    return \app\models\User::find()->where(['id' => $data->exe_user_id])->one()->username;
                }
            ],
            [
                'label' => 'Статус',
                'value' =>  function($data) {
                    return \app\models\Status::find()->where(['id' => $data->status_id])->one()->title;
                }
            ],
            [
                'header' => 'Меню',
                'format' => 'raw',
                'value' => function($data){
                    return Html::a('Перейти', ['task-execution/view', 'id'=>$data->id]);
                }
            ],

//            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
