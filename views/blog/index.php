<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use Michailweb\blog\models\Blog;
/* @var $this yii\web\View */
/* @var $searchModel common\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">


    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
//            'text:ntext',
            'url:url',
            ['attribute'=>'status_id','filter'=> Blog::STATUS_LIST,'value' => 'statusName'],
            'smallImage:Image',
            'date_create:datetime',
            'date_update:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}, {check}',
                'buttons' =>[
                    'check' => function ($url, $model, $key) {
                        return HTML::a('<i class="fa fa-check" aria-hidden="true"></i>',$url,['class'=>'btn btn-success']);
                    }
                    ],
                ],
           ['attribute'=>'tags','value'=> 'tagsAsString'],

        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
