<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TaskExchange;

/**
 * TaskExchangeSearch represents the model behind the search form of `app\models\TaskExchange`.
 */
class TaskExchangeSearch extends TaskExchange
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'chat_id', 'task_exe_id', 'exe_user_id', 'rec_user_id', 'created_at', 'updated_at'], 'integer'],
            [['info', 'file'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TaskExchange::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'task_exe_id' => $this->task_exe_id,
            'exe_user_id' => $this->exe_user_id,
            'rec_user_id' => $this->rec_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query
            ->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(['like', 'file', $this->info]);

        return $dataProvider;
    }
}
