<?php

namespace Michailweb\blog\models;
use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use common\components\behaviors\StatusBehavior;
use yii\helpers\ArrayHelper;
use common\models\ImageManager;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string|null $text
 * @property string $url
 * @property int $status_id
 * @property int $sort
 */
class Blog extends \yii\db\ActiveRecord
{
    const STATUS_LIST = ['off','on'];
    public $tags_array;
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }

    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' => [
                'class' => StatusBehavior::className(),
                'statusList' => self::STATUS_LIST,
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['url'], 'unique'],
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'max' => 99, 'min' => 1],
            [['image'], 'string', 'max' => 100],
            [['file'], 'image'],
            [['title', 'url'], 'string', 'max' => 150],
            [['tags_array', 'date_update', 'date_create'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'ЧПУ',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Тэги',
            'tagsAsString' => 'Тэги',
            'image' => 'Картинка',
            'file' => 'Картинка',
            'author.username' => 'Имя автора',
            'author.email' => 'Почта автора', 
            'date_update' => 'Дата обновления',
            'date_create' => 'Дата создания',


        ];
    }

    public function getAuthor () {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getImagesLinks () {
        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }
    public function getImagesLinksData () {
        $arr = ArrayHelper::toArray($this->images,[ImageManager::className()=>[
            'caption'=>'name',
            'key'=>'id'
        ]]
                );
        return $arr;
    }
    public function getBlogTag () {
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
    }
    public function getTags () {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }
    public function getImages () {
        return $this->hasMany(ImageManager::className(), ['item_id' => 'id'])->andWhere(['class'=>self::tableName()])->orderBy('sort');
    }
    public function afterFind() {
        parent::afterFind();
        $this->tags_array = $this->tags;
    }
    public function getTagsAsString() {
        $arr = \yii\helpers\ArrayHelper::map($this->tags,'id', 'name');
        return implode(', ', $arr);
    }
    
    public function getSmallImage() {
        if ($this->image) {
             $path = str_replace('admin.', '', \yii\helpers\Url::home(true)).'uploads/images/blog/50x50/'.$this->image;
        } else {
             $path = str_replace('admin.', '', \yii\helpers\Url::home(true)).'uploads/images/blog/nophoto.jpg';
        }
           
        return $path;
    }
    public function beforeSave($insert) {
        if ($file = \yii\web\UploadedFile::getInstance($this, 'file')) {
            $dir = Yii::getAlias('@images').'/blog/';
             if (!is_dir($dir . $this->image)) {
                if (file_exists($dir.$this->image)) {
                    unlink($dir.$this->image);
                }
                if (file_exists($dir.'50x50/'.$this->image)) {
                    unlink($dir.'50x50/'.$this->image);
                }
                if (file_exists($dir.'800x/'.$this->image)) {
                    unlink($dir.'800x/'.$this->image);
                }
             }
            $this->image = strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(6).'.'.$file->extension;
            $file->saveAs($dir.$this->image);
            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('50','50', \yii\image\drivers\Image::INVERSE);
            $imag->crop('50','50');
            $imag->save($dir.'50x50/'.$this->image,90);
            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('800',null, \yii\image\drivers\Image::INVERSE);
            $imag->save($dir.'800x/'.$this->image,90);
        }
       return parent::beforeSave($insert);
    }
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        $arr = \yii\helpers\ArrayHelper::map($this->tags,'id', 'id');
        foreach ($this->tags_array as $one) {
            if (!in_array($one,$arr)) {
                $model = new BlogTag();
                $model->blog_id = $this->id;
                $model->tag_id = $one;
                $model->save();
            }
            if (isset($arr[$one])) {
                unset($arr[$one]);
            }
        }
        BlogTag::deleteAll(['tag_id'=>$arr,'blog_id' => $this->id]);
    }
}
