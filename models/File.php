<?php

namespace app\models;

use Yii;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property string $nombre_file
 * @property string $mime
 * @property string $extension
 * @property string $uri
 */
class File extends \yii\db\ActiveRecord
{
    /**
    * Image Property
    */
    public $image_file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['nombre_file', 'required'],
            [['nombre_file', 'extension', 'uri'], 'string', 'max' => 256],
            [['mime'], 'string', 'max' => 56],
            [['image_file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, pdf']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre_file' => 'Nombre File',
            'mime' => 'Mime',
            'extension' => 'Extension',
            'uri' => 'Uri',
        ];
    }

    public function getImage()
    {
        try {
            if (Yii::$app->fs->has($this->nombre_file)) {
                $stream = Yii::$app->fs->readStream($this->nombre_file);
                $contents = stream_get_contents($stream);
                fclose($stream);
                return base64_encode($contents);
            }
        } catch (ServerErrorHttpException $e) {
            return null;
        }
    }

    /**
    * Uploads file to server
    * @return boolean
    */
    public function upload()
    {
        $this->nombre_file = $this->image_file->baseName .'.'. $this->image_file->extension;
        if ($this->validate()) {
            $this->mime = $this->image_file->type;

            $stream = fopen($this->image_file->tempName, 'r+');
            Yii::$app->fs->putStream($this->image_file->name, $stream);

            return $this->save(false);
        } else {
          return false;
        }
    }
}
