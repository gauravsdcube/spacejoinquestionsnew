<?php

namespace humhub\modules\spaceJoinQuestions\controllers;

use Yii;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinApplication;
use humhub\modules\spaceJoinQuestions\Events;
use yii\web\HttpException;
use yii\web\Response;

/**
 * MembershipController handles the main membership management page and custom membership requests
 */
class MembershipController extends SpaceController
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

        if (in_array($action->id, ['index'], true) && !$this->contentContainer->isAdmin()) {
            throw new HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You must be a space administrator'));
        }

        return true;
    }

    /**
     * Main membership management page
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * Handle membership request with custom questions
     */
    public function actionRequest()
    {
        $space = $this->contentContainer;
        $user = Yii::$app->user->identity;

        if (!Events::shouldAskQuestionsForUser($space, $user)) {
            return $this->redirect($space->createUrl('/space/membership/request-membership-form'));
        }

        if ($space->isMember()) {
            throw new HttpException(400, 'You are already a member of this space');
        }

        $existingMembership = Membership::find()
            ->where(['space_id' => $space->id, 'user_id' => Yii::$app->user->id])
            ->one();

        // Allow applicants without answers to complete the form (invite-link demotion path).
        if ($existingMembership) {
            if ($existingMembership->status === Membership::STATUS_APPLICANT
                && !SpaceJoinAnswer::find()->where(['membership_id' => $existingMembership->id])->exists()
            ) {
                return $this->handleApplicationSubmission($space, $existingMembership, SpaceJoinApplication::SOURCE_INVITE);
            }

            return $this->redirect(['status']);
        }

        return $this->handleApplicationSubmission($space, null, SpaceJoinApplication::SOURCE_REQUEST);
    }

    /**
     * Accept invite by answering join questions, then wait for admin approval.
     */
    public function actionAcceptInvite()
    {
        $space = $this->contentContainer;
        $user = Yii::$app->user->identity;

        if (!Events::shouldRequireQuestionsOnInvite($space, $user)) {
            return $this->redirect($space->createUrl('/space/membership/invite-accept'));
        }

        $membership = Membership::find()
            ->where(['space_id' => $space->id, 'user_id' => Yii::$app->user->id])
            ->one();

        if ($membership === null || $membership->status !== Membership::STATUS_INVITED) {
            throw new HttpException(404, Yii::t('SpaceModule.base', 'There is no pending invite!'));
        }

        return $this->handleApplicationSubmission($space, $membership, SpaceJoinApplication::SOURCE_INVITE, true);
    }

    /**
     * Shared create/update application + answers flow.
     *
     * @param \humhub\modules\space\models\Space $space
     * @param Membership|null $membership existing INVITED or APPLICANT row, or null to create
     * @param string $source
     * @param bool $fromInvite
     */
    protected function handleApplicationSubmission($space, $membership, string $source, bool $fromInvite = false)
    {
        $questions = SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($questions as $question) {
                    if ($question->is_required && trim((string) Yii::$app->request->post('question_' . $question->id, '')) === '') {
                        throw new \Exception(Yii::t('SpaceJoinQuestionsModule.base', 'Please answer all required questions.'));
                    }
                }

                if ($membership === null) {
                    $membership = new Membership();
                    $membership->space_id = $space->id;
                    $membership->user_id = Yii::$app->user->id;
                    $membership->status = Membership::STATUS_APPLICANT;
                    $membership->request_message = Yii::$app->request->post('request_message', '');

                    if (!$membership->save()) {
                        throw new \Exception('Failed to create membership application');
                    }
                } else {
                    $membership->status = Membership::STATUS_APPLICANT;
                    $membership->request_message = Yii::$app->request->post('request_message', $membership->request_message);
                    if (!$membership->save(false)) {
                        throw new \Exception('Failed to update membership application');
                    }

                    // Replace any previous unfinished answers.
                    SpaceJoinAnswer::deleteAll(['membership_id' => $membership->id]);
                    SpaceJoinApplication::deleteAll([
                        'membership_id' => $membership->id,
                        'status' => SpaceJoinApplication::STATUS_PENDING,
                    ]);
                }

                SpaceJoinApplication::createPending($membership, $questions, $source);
                $transaction->commit();

                Events::notifyAdminsAboutNewApplication($membership);
                Events::sendApplicationReceivedConfirmation($membership);

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => Yii::t('SpaceJoinQuestionsModule.base', 'Your application has been submitted successfully. You will be notified when the administrators review your application.'),
                    ];
                }

                $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Application submitted successfully'));
                return $this->redirect($space->createUrl('/space-join-questions/membership/status'));
            } catch (\Exception $e) {
                $transaction->rollBack();

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'errors' => [$e->getMessage()],
                    ];
                }

                $this->view->error($e->getMessage());
            }
        }

        return $this->renderAjax('request', [
            'space' => $space,
            'questions' => $questions,
            'formAction' => $fromInvite
                ? $space->createUrl('/space-join-questions/membership/accept-invite')
                : $space->createUrl('/space-join-questions/membership/request'),
            'modalTitle' => $fromInvite
                ? Yii::t('SpaceJoinQuestionsModule.base', 'Accept Invite: {spaceName}', ['spaceName' => $space->name])
                : Yii::t('SpaceJoinQuestionsModule.base', 'Request Membership: {spaceName}', ['spaceName' => $space->name]),
            'submitLabel' => $fromInvite
                ? Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application')
                : Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application'),
        ]);
    }

    /**
     * Show application status
     */
    public function actionStatus()
    {
        $space = $this->contentContainer;

        $membership = Membership::find()
            ->where(['space_id' => $space->id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$membership) {
            throw new HttpException(404, 'No application found');
        }

        $answers = SpaceJoinAnswer::find()
            ->where(['membership_id' => $membership->id])
            ->with(['question'])
            ->all();

        return $this->render('application-status', [
            'space' => $space,
            'membership' => $membership,
            'answers' => $answers,
        ]);
    }

    /**
     * Cancel membership application
     */
    public function actionCancel()
    {
        $space = $this->contentContainer;

        $membership = Membership::find()
            ->where(['space_id' => $space->id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$membership) {
            throw new HttpException(404, 'No application found');
        }

        if ($membership->status !== Membership::STATUS_APPLICANT) {
            throw new HttpException(400, 'Cannot cancel application that is not pending');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $pending = SpaceJoinApplication::findPendingByMembership($membership->id);
            if ($pending) {
                SpaceJoinAnswer::deleteAll(['application_id' => $pending->id]);
                $pending->delete();
            } else {
                SpaceJoinAnswer::deleteAll(['membership_id' => $membership->id]);
            }

            if (!$membership->delete()) {
                throw new \Exception('Failed to delete membership application');
            }

            $transaction->commit();
            $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Application cancelled successfully'));
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Failed to cancel application: ' . $e->getMessage(), 'spaceJoinQuestions');
            $this->view->error(Yii::t('SpaceJoinQuestionsModule.base', 'Failed to cancel application'));
        }

        return $this->redirect($space->createUrl('/space/space'));
    }
}
