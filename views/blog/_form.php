<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    


    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'plugins' => [
                'clips',
                'fullscreen',
            ],
            'clips' => [
                ['Lorem ipsum...', 'Lorem...'],
                ['red', '<span class="label-red">red</span>'],
                ['green', '<span class="label-green">green</span>'],
                ['blue', '<span class="label-blue">blue</span>'],
            ],
            'imageUpload' => yii\helpers\Url::to(['site/save-redactor-img','sub'=>'blog']),
        ],
    ]);
    ?>
    <div class="row">
    <?=$form->field($model, 'file',['options'=>['class'=>'col-xs-6']])->widget(FileInput::classname(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => [
        'showCaption' => false,
        'showRemove' => false,
        'showUpload' => false,
        'browseClass' => 'btn btn-primary btn-block',
        'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
        'browseLabel' =>  'Выбрать фото'
    ],
    ]); ?>
    <?= $form->field($model, 'url',['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_id',['options'=>['class'=>'col-xs-6']])->dropDownList(\Michailweb\blog\models\Blog::STATUS_LIST) ?>

    <?= $form->field($model, 'sort',['options'=>['class'=>'col-xs-6']])->textInput() ?>
    
    <?= 
            $form->field($model, 'tags_array',['options'=>['class'=>'col-xs-6']])->widget(\kartik\select2\Select2::classname(), [
    'data' => yii\helpers\ArrayHelper::map(Michailweb\blog\models\Tag::find()->all(),'id', 'name'),
    'language' => 'ru',
    'options' => ['placeholder' => 'Выбрать ТЭГ ...', 'multiple' => true],
    'pluginOptions' => [
        'allowClear' => true,
        'tags' => true,
        'maximumInputLength' => 10
    ],
    ]); 
    ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?=    FileInput::widget([
        'name' => 'ImageManager[attachment]',
        'options'=>[
            'multiple'=>true
        ],
        'pluginOptions' => [
            'deleteUrl' => yii\helpers\Url::toRoute(['/blog/blog/delete-image']),
            'initialPreview'=>$model->imagesLinks,
            'initialPreviewAsData' => true,
            'overwriteInitial' => false,
            'initialPreviewConfig' => $model->imagesLinksData,
            'uploadUrl' => yii\helpers\Url::to(['/site/save-img']),
            'uploadExtraData' => [
                'ImageManager[class]' => $model->formName(),
                'ImageManager[item_id]' => $model->id
            ],
            'maxFileCount' => 10
        ],
        'pluginEvents' => [
            'filesorted' => new yii\web\JsExpression('function(event,params){$.post("'.yii\helpers\Url::toRoute(["/blog/blog/sort-image",'id'=>$model->id]).'",{sort:params});}' )
        ],
    ]); ?>
</div>
