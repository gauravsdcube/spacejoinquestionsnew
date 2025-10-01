<?php

use humhub\modules\spaceJoinQuestions\Events;
use humhub\modules\spaceJoinQuestions\Module;

return [
    'id' => 'space-join-questions',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\spaceJoinQuestions',
    'version' => '2.0.02',
    'urlManagerRules' => [
        '<spaceContainer>/membership/request-membership-form' => 'space-join-questions/membership/request-membership-form',
        '<spaceContainer>/membership/status' => 'space-join-questions/membership/status',
        '<spaceContainer>/membership/cancel' => 'space-join-questions/membership/cancel',
        '<spaceContainer>/membership' => 'space-join-questions/membership/index',
        '<spaceContainer>/admin/approve' => 'space-join-questions/admin/approve',
        '<spaceContainer>/admin/decline' => 'space-join-questions/admin/decline',
    ],
    'events' => [
        [
            'class' => \humhub\modules\space\widgets\Menu::class,
            'event' => \humhub\modules\space\widgets\Menu::EVENT_INIT,
            'callback' => [Events::class, 'onSpaceMenuInit'],
        ],
        [
            'class' => \humhub\modules\space\widgets\MembershipButton::class,
            'event' => \humhub\modules\space\widgets\MembershipButton::EVENT_INIT,
            'callback' => [Events::class, 'onMembershipButtonInit'],
        ],
        [
            'class' => \humhub\modules\space\models\Membership::class,
            'event' => \humhub\modules\space\models\Membership::EVENT_BEFORE_INSERT,
            'callback' => [Events::class, 'onMembershipBeforeInsert'],
        ],
        [
            'class' => \humhub\modules\space\models\Membership::class,
            'event' => \humhub\modules\space\models\Membership::EVENT_AFTER_INSERT,
            'callback' => [Events::class, 'onMembershipAfterInsert'],
        ],
        [
            'class' => \humhub\modules\space\models\Membership::class,
            'event' => \humhub\modules\space\models\Membership::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onMembershipBeforeDelete'],
        ],
    ],
    'notifications' => [
        \humhub\modules\spaceJoinQuestions\notifications\ApplicationAccepted::class,
        \humhub\modules\spaceJoinQuestions\notifications\ApplicationDeclined::class,
        \humhub\modules\spaceJoinQuestions\notifications\ApplicationReceived::class,
    ],
];
