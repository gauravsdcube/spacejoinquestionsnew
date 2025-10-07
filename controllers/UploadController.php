<?php

namespace humhub\modules\spaceJoinQuestions\controllers;

use Yii;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\file\models\File;
use humhub\modules\file\actions\UploadAction;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * Upload controller for email template images
 */
class UploadController extends SpaceController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::class,
                'guestAllowedActions' => [],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if user is space admin
        if (!$this->contentContainer->isAdmin()) {
            throw new \yii\web\HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You must be a space administrator'));
        }

        return true;
    }

    /**
     * Handle image uploads for TinyMCE editor
     */
    public function actionImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $uploadedFile = UploadedFile::getInstanceByName('file');
            
            if (!$uploadedFile) {
                return [
                    'success' => false,
                    'error' => 'No file uploaded'
                ];
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            if (!in_array($uploadedFile->type, $allowedTypes)) {
                return [
                    'success' => false,
                    'error' => 'Invalid file type. Only images are allowed.'
                ];
            }

            // Validate file size (max 5MB)
            if ($uploadedFile->size > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'error' => 'File too large. Maximum size is 5MB.'
                ];
            }

            // Create file record
            $file = new File();
            $file->file_name = $uploadedFile->name;
            $file->mime_type = $uploadedFile->type;
            $file->size = $uploadedFile->size;
            $file->created_by = Yii::$app->user->id;
            $file->updated_by = Yii::$app->user->id;

            if ($file->save()) {
                // Save the uploaded file
                $filePath = $file->store->get($file->file_name);
                if ($uploadedFile->saveAs($filePath)) {
                    // Generate public URL for email use
                    $publicUrl = $this->generatePublicUrl($file);
                    
                    return [
                        'success' => true,
                        'url' => $publicUrl,
                        'file_id' => $file->id
                    ];
                } else {
                    $file->delete();
                    return [
                        'success' => false,
                        'error' => 'Failed to save file'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to create file record: ' . implode(', ', $file->getFirstErrors())
                ];
            }

        } catch (\Exception $e) {
            Yii::error('Image upload error: ' . $e->getMessage(), 'spaceJoinQuestions');
            
            return [
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate a public URL for the uploaded file
     * 
     * @param File $file
     * @return string
     */
    protected function generatePublicUrl($file)
    {
        // For email templates, we need a public URL that works without authentication
        // Create a temporary public copy in the uploads directory
        
        $publicDir = Yii::getAlias('@webroot/uploads/email_images');
        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fileName = $file->guid . '_' . time() . '.' . pathinfo($file->file_name, PATHINFO_EXTENSION);
        $publicPath = $publicDir . '/' . $fileName;
        $sourcePath = $file->store->get($file->file_name);

        if (file_exists($sourcePath) && copy($sourcePath, $publicPath)) {
            chmod($publicPath, 0644);
            return Yii::getAlias('@web/uploads/email_images/' . $fileName);
        }

        // Fallback to regular file URL
        return $file->getUrl();
    }
}
