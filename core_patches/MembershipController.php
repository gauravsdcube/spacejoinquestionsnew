<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Exception;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\space\permissions\InviteUsers;
use humhub\modules\space\widgets\MembershipButton;
use humhub\modules\user\models\UserPicker;
use humhub\modules\user\widgets\UserListBox;
use humhub\widgets\ModalClose;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * SpaceController is the main controller for spaces.
 *
 * It show the space itself and handles all related tasks like following or
 * memberships.
 *
 * @author Luke
 * @property Module $module
 * @since 0.5
 */
class MembershipController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permission' => [InviteUsers::class], 'actions' => ['invite']],
            [ContentContainerControllerAccess::RULE_LOGGED_IN_ONLY => ['revoke-membership']],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_MEMBER],
                'actions' => [
                    'revoke-notifications',
                    'receive-notifications',
                    'search-invite',
                    'switch-dashboard-display',
                ],
            ],
            [ContentContainerControllerAccess::RULE_AJAX_ONLY => ['members-list']],
        ];
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionSearch()
    {
        Yii::$app->response->format = 'json';

        $space = $this->getSpace();
        $visibility = (int)$space->visibility;
        if ($visibility === Space::VISIBILITY_NONE && !$space->isMember() ||
            ($visibility === Space::VISIBILITY_REGISTERED_ONLY && Yii::$app->user->isGuest)
        ) {
            throw new HttpException(404, Yii::t(
                'SpaceModule.base',
                'This action is only available for workspace members!',
            ));
        }

        return UserPicker::filter([
            'query' => $space->getMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disabledText' => Yii::t(
                'SpaceModule.base',
                'This user is not a member of this space.',
            ),
        ]);
    }

    /**
     * Requests Membership for this Space
     */
    public function actionRequestMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id)) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'You are not allowed to join this space!'),
            );
        }

        // Check if space requires applications (Space Join Questions Module)
        if ($space->getSetting('requireApplication', 'spaceJoinQuestions')) {
            // Redirect to application form instead of direct membership
            return $this->redirect(['/space-join-questions/application/create', 'spaceId' => $space->id]);
        }

        // Original behavior for spaces without application requirement
        $space->addMember(Yii::$app->user->id);

        return $this->getActionResult($space);
    }

    /**
     * Requests Membership Form for this Space
     * (If a message is required.)
     *
     */
    public function actionRequestMembershipForm()
    {
        $space = $this->getSpace();

        // Check if we have already some sort of membership
        if (Yii::$app->user->isGuest || $space->getMembership(Yii::$app->user->id) != null) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Could not request membership!'),
            );
        }

        $model = new RequestMembershipForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->requestMembership(Yii::$app->user->id, $model->message);

            return $this->renderAjax('requestMembershipSave', [
                'spaceId' => $space->id,
                'newMembershipButton' => MembershipButton::widget([
                    'space' => $space,
                    'options' => empty($model->options) ? [] : Json::decode($model->options),
                ]),
            ]);
        }

        $model->options = $this->request->get('options');

        return $this->renderAjax('requestMembership', ['model' => $model, 'space' => $space]);
    }

    public function actionRevokeNotifications()
    {
        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, false);

        return $this->redirect($space->getUrl());
    }

    public function actionReceiveNotifications()
    {
        $space = $this->getSpace();
        Yii::$app->notification->setSpaceSetting(Yii::$app->user->getIdentity(), $space, true);

        return $this->redirect($space->getUrl());
    }

    /**
     * Revokes Membership for this workspace
     * @return Response
     * @throws HttpException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionRevokeMembership()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if ($space->isSpaceOwner()) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Space owners cannot revoke their membership!'),
            );
        }

        $membership = $space->getMembership(Yii::$app->user->id);
        if ($membership === null) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Could not revoke membership!'),
            );
        }

        $membership->delete();

        return $this->getActionResult($space);
    }

    /**
     * Provides a searchable user list for space invites in json.
     *
     */
    public function actionSearchInvite()
    {
        Yii::$app->response->format = 'json';

        $space = $this->getSpace();

        return UserPicker::filter([
            'query' => $space->getNonMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disabledText' => Yii::t(
                'SpaceModule.base',
                'This user is already a member of this space.',
            ),
        ]);
    }

    /**
     * Invites a user to the space
     * @return Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionInvite()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        $form = new InviteForm();
        $form->space = $space;

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $form->invite();
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Resets the invite link for this space
     * @return Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionResetInviteLink()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->isSpaceOwner()) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Only space owners can reset the invite link!'),
            );
        }

        $space->resetInviteLink();

        return $this->redirect($space->getUrl());
    }

    /**
     * Accepts an invite to a space
     * @return Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionInviteAccept()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        if (!$space->canJoin(Yii::$app->user->id)) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'You are not allowed to join this space!'),
            );
        }

        $space->addMember(Yii::$app->user->id);

        return $this->redirect($space->getUrl());
    }

    /**
     * Switches the dashboard display mode
     * @param int $show
     * @return Response
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionSwitchDashboardDisplay($show = 0)
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        $membership = $space->getMembership(Yii::$app->user->id);
        if ($membership === null) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'Could not switch dashboard display!'),
            );
        }

        $membership->show_dashboard = (bool)$show;
        $membership->save();

        return $this->redirect($space->getUrl());
    }

    /**
     * Provides a searchable user list of all workspace members in json.
     *
     */
    public function actionMembersList()
    {
        Yii::$app->response->format = 'json';

        $space = $this->getSpace();

        if (!$this->canViewMembers()) {
            throw new HttpException(
                500,
                Yii::t('SpaceModule.base', 'You are not allowed to view members!'),
            );
        }

        return UserListBox::filter([
            'query' => $space->getMembershipUser(),
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
        ]);
    }

    /**
     * Returns the result of a membership action
     * @param Space $space
     * @return Response
     */
    protected function getActionResult(Space $space)
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('membershipResult', [
                'spaceId' => $space->id,
                'newMembershipButton' => MembershipButton::widget(['space' => $space]),
            ]);
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Checks if the current user can view members
     * @return bool
     */
    private function canViewMembers(): bool
    {
        $space = $this->getSpace();
        return $space->isMember() || $space->visibility === Space::VISIBILITY_ALL;
    }
} 