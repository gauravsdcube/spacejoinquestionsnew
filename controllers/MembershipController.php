<?php

namespace humhub\modules\spaceJoinQuestions\controllers;

use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * MembershipController handles custom membership requests with questions
 */
class MembershipController extends SpaceController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * Handle membership request with custom questions
     */
    public function actionRequest()
    {
        $space = $this->contentContainer;

        // Check if user is already a member
        if ($space->isMember()) {
            throw new HttpException(400, 'You are already a member of this space');
        }

        // Check if user already has a pending application
        $existingMembership = Membership::find()
            ->where(['space_id' => $space->id, 'user_id' => Yii::$app->user->id])
            ->one();

        if ($existingMembership) {
            return $this->redirect(['status']);
        }

        // Get questions for this space
        $questions = SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Create membership application
                $membership = new Membership();
                $membership->space_id = $space->id;
                $membership->user_id = Yii::$app->user->id;
                $membership->status = Membership::STATUS_APPLICANT;
                $membership->request_message = Yii::$app->request->post('request_message', '');

                if (!$membership->save()) {
                    throw new \Exception('Failed to create membership application');
                }

                // Save answers to questions
                foreach ($questions as $question) {
                    $answerText = Yii::$app->request->post("question_{$question->id}", '');

                    if (!empty($answerText)) {
                        $answer = new SpaceJoinAnswer();
                        $answer->membership_id = $membership->id;
                        $answer->question_id = $question->id;
                        $answer->answer_text = $answerText;

                        if (!$answer->save()) {
                            throw new \Exception('Failed to save answer');
                        }
                    }
                }

                $transaction->commit();

                // Send notification to space administrators
                if ($space->getSettings()->get('emailNotifications', 'spaceJoinQuestions', true)) {
                    $notification = new \humhub\modules\spaceJoinQuestions\notifications\ApplicationReceived();
                    $notification->source = $space;
                    $notification->originator = Yii::$app->user->identity;

                    // Send to all space administrators
                    foreach ($space->getAdmins() as $admin) {
                        $notification->send($admin);
                    }
                }

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

        if ($membership->delete()) {
            $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Application cancelled successfully'));
        } else {
            $this->view->error(Yii::t('SpaceJoinQuestionsModule.base', 'Failed to cancel application'));
        }

        return $this->redirect($space->createUrl('/space/space'));
    }
}
