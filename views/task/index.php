<?php

use app\models\Project;
use app\models\Status;
use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Задачи';
$this->params['breadcrumbs'][] = $this->title;
$myRole = \app\models\User::getMyRole();
    if($myRole === "superAdmin"){
        $template = '{view}{update}{delete}';
    }
    else{
        $template = '{view}';
    }
?>
<div class="task-index">

    <div class="card card-outline card-success">
        <div class="card-header">
            <h1 class="card-title text-bold">
                <?= Html::encode($this->title) ?>
                <?= ($myRole !== "simpleUser") ? Html::a('+', ['create'], ['class' => 'btn btn-info ml-2', 'title' => 'Создать Задача']) : "" ?>
            </h1>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
            <!-- /.card-tools -->
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'project_id' => [
                        'attribute' => 'project_id',
                        'filter'    =>  $projects,
                        'value' =>  function($data) {
                            return Project::getProjectById($data->project_id)->title;
                        }
                    ],
                    'title:ntext',
                    'user_id' => [
                        'attribute' => 'user_id',
                        'filter'    => $users,
                        'value'     =>  function($data) {
                            return User::getUserById($data->user_id)->fullname;
                        }
                    ],
                    'deadline' => [
                        'attribute' => 'deadline',
                        'value' =>  function($data) {
                            return date('d M Y H:i:s', strtotime($data->deadline));
                        }
                    ],
                    'status_id' => [
                        'attribute' => 'status_id',
                        'filter'    => $statuses,
                        'value'     =>  function($data) {
                            return Status::getStatusById($data->status_id)->title;
                        },
                        'contentOptions' => function($data) {
                            return ['class' => Status::getStatusColor($data->status_id)];
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template'  =>$template
                    ],
                ],
            ]); ?>
        </div>
        <!-- /.card-body -->
    </div>

</div>
