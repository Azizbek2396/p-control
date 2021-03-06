<?php

namespace app\controllers;

use app\models\FileUpload;
use app\models\Project;
use app\models\Status;
use app\models\Task;
use app\models\TaskExchange;
use app\models\TaskExchangeSearch;
use app\models\TaskExecution;
use app\models\TaskExecutionSearch;
use app\models\User;
use Cassandra\Date;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * TaskExecutionController implements the CRUD actions for TaskExecution model.
 */
class TaskExecutionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all TaskExecution models.
     * @return mixed
     */
    public function actionIndex()
    {
//        $myRole = User::getMyRole();

        $searchModel = new TaskExecutionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

//        if ($myRole === 'headOfDep'){
//            $dataProvider->query->andWhere(['user_id' =>  \Yii::$app->user->id])->orWhere(['receive_user' => \Yii::$app->user->id])->orWhere(['exe_user_id' => \Yii::$app->user->id]);
//            $dataProvider->setSort([
//                'defaultOrder' => ['id'=>SORT_DESC],
//            ]);
//        } elseif ($myRole === "simpleUser" || $myRole === "accountant") {
//            $dataProvider->query->andWhere(['exe_user_id' =>  \Yii::$app->user->id])->orWhere(['receive_user' => \Yii::$app->user->id]);
//            $dataProvider->setSort([
//                'defaultOrder' => ['id'=>SORT_DESC],
//            ]);
//        }

        return $this->render('index', [
            'searchModel'   => $searchModel,
            'dataProvider'  => $dataProvider,
            'tasks'         => Task::getTasks(),
            'users'         => User::getUsers(),
            'statuses'      => Status::getStatuses()
        ]);
    }

    /**
     * Displays a single TaskExecution model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $current_user_id = \Yii::$app->user->id;
        $model = $this->findModel($id);

        $lastItem = TaskExchange::find()->orWhere(['task_exe_id' => $id, 'exe_user_id' => $current_user_id])->orWhere(['task_exe_id' => $id, 'rec_user_id' => $current_user_id])->orderBy(['id' => SORT_DESC])->one();
        $taskExchanges = TaskExchange::find()->where(['task_exe_id' => $id])->all();
        $chat_ids = [];
        foreach($taskExchanges as $item) {
            array_push($chat_ids, $item['chat_id']);
        }
        $chat_ids = array_unique($chat_ids);

        if ($current_user_id === $model->exe_user_id) {
            if ($model->receive_date === NULL)
            {
                $receive_date = \date('Y-m-d H:i:s');
                $model->receive_date = $receive_date;
                $model->save();
            }
        }




        return $this->render('view', [
            'model'         => $this->findModel($id),
            'lastItem'      => $lastItem,
            'chats'         => $chat_ids,
            'taskExchanges' => $taskExchanges
//            'searchModel'  => $searchModel,
//            'dataProvider'  => $dataProvider
        ]);
    }

    /**
     * Creates a new TaskExecution model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($task_id = 1)
    {
        if (User::getMyRole() === 'admin' || User::getMyRole() === 'superAdmin') {
            $tasks = ArrayHelper::map(Task::find()->all(), 'id', 'title');
        } else {
            $tasks = ArrayHelper::map(Task::find()->where(['user_id' => \Yii::$app->user->id])->all(), 'id', 'title');
        }
        $model = new TaskExecution();

        if ($this->request->isPost) {
            $model->user_id = \Yii::$app->user->id;
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'tasks' => $tasks,
            'users' =>  User::getUsers(),
            'task_id' => $task_id
        ]);
    }

    /**
     * Updates an existing TaskExecution model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'tasks' => Task::getTasks(),
            'users' =>  User::getUsers()
        ]);
    }

    /**
     * Deletes an existing TaskExecution model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TaskExecution model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return TaskExecution the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TaskExecution::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionTaskExe($id)
    {
        $model = new TaskExchange;
        $fileUpload = new FileUpload();
        $taskExecution = $this->findModel($id);

        if ($this->request->isPost)
        {
            $taskExecution = $this->findModel($id);
            $file = UploadedFile::getInstance($fileUpload, 'file');
            $info = $this->request->post("TaskExchange")['info'];

            $exe_user_id = \Yii::$app->user->id;
            $rec_user_id = $this->request->post("new_receive_user");
            $query_exe = TaskExchange::find()->where(['task_exe_id' => $id, 'exe_user_id' => $exe_user_id, 'rec_user_id' => $rec_user_id])->orderBy(['id' => SORT_DESC])->one();
            $query_rec = TaskExchange::find()->where(['task_exe_id' => $id, 'exe_user_id' => $rec_user_id, 'rec_user_id' => $exe_user_id])->orderBy(['id' => SORT_DESC])->one();
            if($this->request->post("new_receive_user")){

                if ( $query_exe || $query_rec){
                    $taskExecution = $this->findModel($id);
                    $file = UploadedFile::getInstance($fileUpload, 'file');
                    $info = $this->request->post("TaskExchange")['info'];

                    $model->chat_id = $query_exe->chat_id;
                    $model->task_exe_id = $id;
                    $model->exe_user_id = $exe_user_id;
                    $model->rec_user_id = $rec_user_id;
                    $taskExecution->status_id = Status::findOne(['title' => '?? ????????????????'])->id;
                    $model->info =$info;
                    $model->saveFile($fileUpload->uploadFile($file, $model->file));
                    if ($model->save() && $taskExecution->save()) {
                        return $this->redirect(['view', 'id' => $taskExecution->id]);
                    }

                } else {
                    $taskExecution = $this->findModel($id);
                    $file = UploadedFile::getInstance($fileUpload, 'file');
                    $info = $this->request->post("TaskExchange")['info'];

                    $model->chat_id = rand(time(), 1000000);
                    $model->task_exe_id = $id;
                    $model->exe_user_id = $exe_user_id;
                    $model->rec_user_id = $rec_user_id;
                    $taskExecution->status_id = Status::findOne(['title' => '?? ????????????????'])->id;
                    $model->info =$info;
                    $model->saveFile($fileUpload->uploadFile($file, $model->file));
                    if ($model->save() && $taskExecution->save()) {
                        return $this->redirect(['view', 'id' => $taskExecution->id]);
                    }
                }
            }

            if ($taskExecution->exe_user_id !== \Yii::$app->user->id)
            {
                $chat_id = TaskExchange::find()->where(['task_exe_id' => $id, 'exe_user_id' => $taskExecution->exe_user_id, 'rec_user_id' => \Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one()->chat_id;

                $model->chat_id = $chat_id;
                $model->task_exe_id = $id;
                $model->exe_user_id = \Yii::$app->user->id;
                $model->rec_user_id = $taskExecution->exe_user_id;
                $model->info =$info;
                $model->saveFile($fileUpload->uploadFile($file, $model->file));

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $taskExecution->id]);
                }
            }

            if ( $query_exe || $query_rec) {

                $model->chat_id = $query_exe->chat_id;
                $model->task_exe_id = $id;
                $model->exe_user_id = \Yii::$app->user->id;
                $model->rec_user_id = $taskExecution->receive_user;
                $taskExecution->status_id = Status::findOne(['title' => '????????????????????????'])->id;
                $model->info =$info;
                $model->saveFile($fileUpload->uploadFile($file, $model->file));

                if ($model->save() && $taskExecution->save()) {
                    return $this->redirect(['view', 'id' => $taskExecution->id]);
                }
            }else {
                $model->chat_id = rand(time(), 1000000);
                $model->task_exe_id = $id;
                $model->exe_user_id = \Yii::$app->user->id;
                $model->rec_user_id = $taskExecution->receive_user;
                $taskExecution->status_id = Status::findOne(['title' => '????????????????????????'])->id;
                $model->info =$info;
                $model->saveFile($fileUpload->uploadFile($file, $model->file));

                if ($model->save() && $taskExecution->save()) {
                    return $this->redirect(['view', 'id' => $taskExecution->id]);
                }
            }

        }

        return $this->render('task-executor', [
            'model' => $model,
            'fileUpload' => $fileUpload,
            'taskExecution' => $taskExecution,
            'users' => User::getUsers()
        ]);
    }

    public function actionTaskCheck($id)
    {
        $model = $this->findModel($id);
        $taskExchange = TaskExchange::find()->where(['task_exe_id' => $id])->orderBy(['id' => SORT_DESC])->one();

        return $this->render('task-receiver',[
            'model' => $model,
            'taskExchange' => $taskExchange
        ]);
    }

    public function actionTaskDeny($id)
    {
        $model = new TaskExchange;
        $fileUpload = new FileUpload();

        if ($this->request->isPost)
        {
            $taskExecution = $this->findModel($id);
            $file = UploadedFile::getInstance($fileUpload, 'file');
            $info = $this->request->post("TaskExchange")['info'];
            $chat_id = TaskExchange::find()->where(['task_exe_id' => $id, 'exe_user_id' => $taskExecution->exe_user_id, 'rec_user_id' => \Yii::$app->user->id])->orderBy(['id' => SORT_DESC])->one()->chat_id;

            $model->chat_id = $chat_id;
            $model->task_exe_id = $id;
            $model->exe_user_id = \Yii::$app->user->id;
            $model->rec_user_id = $taskExecution->exe_user_id;
            $taskExecution->status_id = Status::findOne(['title' => '????????????????????'])->id;
            $model->info =$info;
            $model->saveFile($fileUpload->uploadFile($file, $model->file));

            if ($model->save() && $taskExecution->save()) {
                return $this->redirect(['view', 'id' => $taskExecution->id]);
            }

        }

        return $this->render('task-deny', [
            'model' => $model,
            'fileUpload' => $fileUpload,

        ]);
    }

    public function actionTaskApprove($id)
    {
        $taskExecution = $this->findModel($id);
        $marks = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
        ];



        if ($this->request->isPost)
        {
            $mark = $this->request->post('TaskExecution')['mark'];
            $info = $this->request->post('TaskExecution')['info'];
            $done_date = \date('Y-m-d H:i:s');
//            var_dump($done_date);die();

            $taskExecution->status_id   = Status::findOne(['title' => '????????????????????'])->id;
            $taskExecution->mark        = $mark;
            $taskExecution->info        = $info;
            $taskExecution->done_date   = $done_date;

            if($taskExecution->save())
            {
                return $this->redirect(['view', 'id' => $taskExecution->id]);
            }
        }

        return $this->render('task-mark',[
            'model' => $taskExecution,
            'marks' =>  $marks
        ]);
    }
}
