<?php

namespace humhub\modules\spaceJoinQuestions\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Space Join Questions module
 */
class SpaceJoinQuestionsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@spaceJoinQuestions/resources';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/module.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/module.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\AppAsset',
    ];
}