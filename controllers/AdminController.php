<?php

namespace humhub\modules\spaceJoinQuestions\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;

use humhub\modules\spaceJoinQuestions\models\forms\QuestionForm;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinApplication;
use humhub\modules\spaceJoinQuestions\permissions\ManageQuestions;
use humhub\modules\spaceJoinQuestions\permissions\ViewApplications;
use humhub\modules\spaceJoinQuestions\notifications\ApplicationAccepted;
use humhub\modules\spaceJoinQuestions\notifications\ApplicationDeclined;

/**
 * Admin Controller for Space Join Questions
 * 
 * Handles the administration of custom questions and membership applications
 */
class AdminController extends SpaceController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'sort' => ['POST'],
                    'approve' => ['POST'],
                    'decline' => ['POST'],
                    'save-recipients' => ['POST'],
                ],
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

        // Debug: Check if user is logged in
        if (!Yii::$app->user->isGuest) {
            Yii::error('User is logged in: ' . Yii::$app->user->identity->username);
        } else {
            Yii::error('User is not logged in');
        }

        // Debug: Check if content container is set
        if ($this->contentContainer) {
            Yii::error('Content container: ' . $this->contentContainer->name);
        } else {
            Yii::error('No content container');
        }

        // Allow broader access for settings-related actions
        if (in_array($action->id, ['settings', 'notification-recipients', 'save-recipients'], true)) {
            if (!$this->canManageSettings()) {
                throw new HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You do not have permission to manage settings in this space'));
            }
            return true;
        }

        // Check if user is space admin for all other actions
        if (!$this->contentContainer->isAdmin()) {
            throw new HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You must be a space administrator'));
        }

        return true;
    }

    /**
     * Check permission and throw exception if not allowed
     */
    protected function checkPermission($permission)
    {
        // First check if user is space admin (simplest check)
        if ($this->contentContainer->isAdmin()) {
            Yii::error('User is space admin - allowing access');
            return; // Allow access for space admins
        }

        // Then check specific permissions
        if (!$this->contentContainer->permissionManager->can($permission)) {
            Yii::error('User does not have permission: ' . get_class($permission));
            throw new HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You do not have permission to manage questions in this space'));
        }
    }

    /**
     * Check if current user can manage module settings.
     *
     * Allows space admins, system admins, and users in "Administrators"
     * or "Client Administrators" user groups.
     */
    protected function canManageSettings()
    {
        if ($this->contentContainer->isAdmin() || Yii::$app->user->isAdmin()) {
            return true;
        }

        $groupIds = Group::find()
            ->select('id')
            ->where(['name' => ['Administrators', 'Client Administrators']])
            ->column();

        if (empty($groupIds)) {
            return false;
        }

        return GroupUser::find()
            ->where(['user_id' => Yii::$app->user->id, 'group_id' => $groupIds])
            ->exists();
    }


    /**
     * List all questions for the space
     */
    public function actionIndex()
    {
        $questions = SpaceJoinQuestion::find()
            ->where(['space_id' => $this->contentContainer->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'questions' => $questions,
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * Create a new question
     */
    public function actionCreate()
    {
        // Debug: Log the current action and space
        Yii::error('Action: ' . $this->action->id);
        Yii::error('Content Container ID: ' . ($this->contentContainer ? $this->contentContainer->id : 'null'));
        Yii::error('Content Container Name: ' . ($this->contentContainer ? $this->contentContainer->name : 'null'));
        
        $model = new QuestionForm();
        $model->space_id = $this->contentContainer->id;

        // Debug: Log POST data
        Yii::error('POST data: ' . print_r(Yii::$app->request->post(), true));

        if ($model->load(Yii::$app->request->post())) {
            Yii::error('Model loaded with data: ' . print_r($model->attributes, true));
            
            // Auto-assign sort order if not provided
            if (empty($model->sort_order)) {
                $maxSortOrder = SpaceJoinQuestion::find()
                    ->where(['space_id' => $this->contentContainer->id])
                    ->max('sort_order');
                $model->sort_order = ($maxSortOrder !== null) ? $maxSortOrder + 10 : 0;
                Yii::error('Auto-assigned sort order: ' . $model->sort_order);
            }
            
            Yii::error('Model validation: ' . ($model->validate() ? 'PASS' : 'FAIL'));
            if (!$model->validate()) {
                Yii::error('Validation errors: ' . print_r($model->errors, true));
            }
            
            if ($model->save()) {
                Yii::error('Question saved successfully');
                $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Question created successfully'));
                return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/index'));
            } else {
                Yii::error('Failed to save question: ' . print_r($model->errors, true));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * Edit an existing question
     */
    public function actionEdit($id)
    {
        $question = SpaceJoinQuestion::findOne(['id' => $id, 'space_id' => $this->contentContainer->id]);
        if (!$question) {
            throw new HttpException(404, 'Question not found');
        }

        $model = new QuestionForm();
        $model->loadFromQuestion($question);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Question updated successfully'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/index'));
        }

        return $this->render('edit', [
            'model' => $model,
            'question' => $question,
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * Delete a question
     */
    public function actionDelete($id)
    {
        $question = SpaceJoinQuestion::findOne(['id' => $id, 'space_id' => $this->contentContainer->id]);
        if (!$question) {
            throw new HttpException(404, 'Question not found');
        }

        if ($question->delete()) {
            $this->view->success(Yii::t('SpaceJoinQuestionsModule.base', 'Question deleted successfully'));
        } else {
            $this->view->error(Yii::t('SpaceJoinQuestionsModule.base', 'Failed to delete question'));
        }

        return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/index'));
    }

    /**
     * Sort questions via AJAX
     */
    public function actionSort()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $questionIds = Yii::$app->request->post('questions', []);
        
        foreach ($questionIds as $index => $id) {
            $question = SpaceJoinQuestion::findOne(['id' => $id, 'space_id' => $this->contentContainer->id]);
            if ($question) {
                $question->sort_order = $index;
                $question->save();
            }
        }

        return ['success' => true];
    }

    /**
     * List pending membership applications and decided history
     */
    public function actionApplications()
    {
        $this->checkPermission(new ViewApplications());

        $dataProvider = new ActiveDataProvider([
            'query' => Membership::find()
                ->where(['space_id' => $this->contentContainer->id, 'status' => Membership::STATUS_APPLICANT])
                ->with(['user', 'user.profile'])
                ->orderBy(['created_at' => SORT_DESC]),
        ]);

        $historyProvider = new ActiveDataProvider([
            'query' => SpaceJoinApplication::find()
                ->where([
                    'space_id' => $this->contentContainer->id,
                    'status' => [SpaceJoinApplication::STATUS_APPROVED, SpaceJoinApplication::STATUS_DECLINED],
                ])
                ->with(['user', 'user.profile', 'decidedBy'])
                ->orderBy(['decided_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('applications', [
            'dataProvider' => $dataProvider,
            'historyProvider' => $historyProvider,
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * View application details (pending membership or history record)
     */
    public function actionApplicationDetail($membershipId = null, $applicationId = null)
    {
        $this->checkPermission(new ViewApplications());

        $applicationHistory = null;
        $membership = null;

        if ($applicationId) {
            $applicationHistory = SpaceJoinApplication::findOne([
                'id' => $applicationId,
                'space_id' => $this->contentContainer->id,
            ]);
            if (!$applicationHistory) {
                throw new HttpException(404, 'Application not found');
            }
            $membership = $applicationHistory->membership;
            $answers = SpaceJoinAnswer::find()
                ->where(['application_id' => $applicationHistory->id])
                ->with(['question'])
                ->all();
        } else {
            $membership = Membership::findOne(['id' => $membershipId, 'space_id' => $this->contentContainer->id]);
            if (!$membership) {
                throw new HttpException(404, 'Application not found');
            }
            $applicationHistory = SpaceJoinApplication::findPendingByMembership($membership->id);
            $answers = SpaceJoinAnswer::find()
                ->where(['membership_id' => $membership->id])
                ->with(['question'])
                ->all();
        }

        return $this->render('application-detail', [
            'application' => $membership ?: $applicationHistory,
            'applicationHistory' => $applicationHistory,
            'answers' => $answers,
            'space' => $this->contentContainer,
            'isDeclined' => $applicationHistory && $applicationHistory->status === SpaceJoinApplication::STATUS_DECLINED,
            'isHistory' => $applicationHistory && $applicationHistory->status !== SpaceJoinApplication::STATUS_PENDING,
        ]);
    }

    /**
     * Approve a membership application
     */
    public function actionApprove($membershipId)
    {
        $this->checkPermission(new ViewApplications());

        $membership = Membership::findOne(['id' => $membershipId, 'space_id' => $this->contentContainer->id]);
        if (!$membership) {
            throw new HttpException(404, 'Application not found');
        }

        $membership->status = Membership::STATUS_MEMBER;
        $membership->updated_at = date('Y-m-d H:i:s');

        if ($membership->save()) {
            $history = SpaceJoinApplication::findPendingByMembership($membership->id);
            if ($history) {
                $history->markApproved(Yii::$app->user->identity);
            }

            // Send email notification to user
            try {
                $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
                    $this->contentContainer->id,
                    \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_ACCEPTED
                );

                if ($template && $template->is_active) {
                    $this->sendCustomAcceptanceEmail($membership, $template);
                } else {
                    $notification = new ApplicationAccepted();
                    $notification->source = $membership;
                    $notification->originator = Yii::$app->user->identity;

                    if ($membership->user && $membership->user->id) {
                        $notification->sendDirect($membership->user);
                    }
                }
            } catch (\Exception $e) {
                Yii::error('Error sending notification: ' . $e->getMessage());
            }

            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Application approved successfully'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        }

        Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to approve application'));
        return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
    }

    /**
     * Decline a membership application
     */
    public function actionDecline($membershipId)
    {
        $this->checkPermission(new ViewApplications());

        $membership = Membership::findOne(['id' => $membershipId, 'space_id' => $this->contentContainer->id]);
        if (!$membership) {
            throw new HttpException(404, 'Application not found');
        }

        $declineReason = trim(Yii::$app->request->post('decline_reason', ''));

        if ($declineReason === '') {
            Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'A decline reason is required.'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/application-detail', ['membershipId' => $membershipId]));
        }

        $user = $membership->user;
        $space = $membership->space;
        $membershipIdValue = $membership->id;
        $history = SpaceJoinApplication::findPendingByMembership($membership->id);

        if ($history) {
            // Detach answers before membership delete so history is retained.
            SpaceJoinAnswer::updateAll(
                ['membership_id' => null],
                ['application_id' => $history->id]
            );
            $history->markDeclined($declineReason, Yii::$app->user->identity);
        }

        if ($membership->delete()) {
            try {
                $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
                    $this->contentContainer->id,
                    \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_DECLINED
                );

                if ($template && $template->is_active) {
                    $this->sendCustomDeclineEmail($user, $space, $declineReason, $template);
                } else {
                    $notification = new ApplicationDeclined();

                    $mockMembership = new \stdClass();
                    $mockMembership->id = $membershipIdValue;
                    $mockMembership->space = $space;
                    $mockMembership->user = $user;

                    $notification->source = $mockMembership;
                    $notification->originator = Yii::$app->user->identity;
                    $notification->setDeclineReason($declineReason);

                    if ($user && $user->id) {
                        $notification->sendDirect($user);
                    }
                }
            } catch (\Exception $e) {
                Yii::error('Error sending decline notification: ' . $e->getMessage());
            }

            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Application declined successfully'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        }

        Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to decline application'));
        return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
    }

    /**
     * Export all applications (pending, approved, declined) as CSV
     */
    public function actionExportApplications()
    {
        $this->checkPermission(new ViewApplications());

        $space = $this->contentContainer;
        $questions = SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        $applications = SpaceJoinApplication::find()
            ->where(['space_id' => $space->id])
            ->with(['user', 'decidedBy', 'answers'])
            ->orderBy([
                'submitted_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->all();

        $filename = 'space-' . $space->id . '-applications-' . date('Ymd-His') . '.csv';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $out = fopen('php://temp', 'r+');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

        $header = [
            'Application ID',
            'User',
            'Email',
            'Source',
            'Status',
            'Submitted At',
            'Decided At',
            'Decided By',
            'Decline Reason',
            'Request Message',
        ];
        foreach ($questions as $question) {
            $header[] = $question->question_text;
        }
        fputcsv($out, $header);

        foreach ($applications as $application) {
            $answersByQuestion = [];
            foreach ($application->answers as $answer) {
                $answersByQuestion[(int) $answer->question_id] = $answer->answer_text;
            }

            $row = [
                $application->id,
                $application->user ? $application->user->displayName : '',
                $application->user ? $application->user->email : '',
                $application->getSourceLabel(),
                $application->getStatusLabel(),
                $application->submitted_at ? Yii::$app->formatter->asDatetime($application->submitted_at) : '',
                $application->decided_at ? Yii::$app->formatter->asDatetime($application->decided_at) : '',
                $application->decidedBy ? $application->decidedBy->displayName : '',
                (string) $application->decline_reason,
                (string) $application->request_message,
            ];

            foreach ($questions as $question) {
                $row[] = $answersByQuestion[(int) $question->id] ?? '';
            }

            fputcsv($out, $row);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }

    /**
     * Module settings
     */
    public function actionSettings()
    {
        $space = $this->contentContainer;
        $settings = $space->getSettings();
        $emailNotifications = $settings->get('emailNotifications', 'spaceJoinQuestions', true);
        $requireQuestionsOnInvite = (bool) $settings->get('requireQuestionsOnInvite', 'spaceJoinQuestions', false);
        $selectedGroupIds = $settings->get('questionGroupIds', 'spaceJoinQuestions', '[]');

        if (is_string($selectedGroupIds)) {
            $decoded = json_decode($selectedGroupIds, true);
            $selectedGroupIds = is_array($decoded) ? $decoded : [];
        } elseif (is_array($selectedGroupIds)) {
            $selectedGroupIds = $selectedGroupIds;
        } else {
            $selectedGroupIds = [];
        }

        $selectedGroupIds = array_values(array_filter(array_map('intval', $selectedGroupIds)));

        $groups = Group::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            $settingsData = Yii::$app->request->post('settings', []);
            $emailNotifications = isset($settingsData['emailNotifications']) ? (bool) $settingsData['emailNotifications'] : false;
            $requireQuestionsOnInvite = isset($settingsData['requireQuestionsOnInvite']) ? (bool) $settingsData['requireQuestionsOnInvite'] : false;
            $selectedGroupIds = array_values(array_filter(array_map('intval', $settingsData['questionGroupIds'] ?? [])));

            $settings->set('emailNotifications', $emailNotifications, 'spaceJoinQuestions');
            $settings->set('requireQuestionsOnInvite', $requireQuestionsOnInvite, 'spaceJoinQuestions');
            $settings->set('questionGroupIds', json_encode($selectedGroupIds), 'spaceJoinQuestions');

            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Settings saved successfully'));
            return $this->redirect($space->createUrl('/space-join-questions/admin/settings'));
        }

        return $this->render('settings', [
            'space' => $space,
            'emailNotifications' => $emailNotifications,
            'requireQuestionsOnInvite' => $requireQuestionsOnInvite,
            'groups' => $groups,
            'selectedGroupIds' => $selectedGroupIds,
        ]);
    }

    /**
     * Send custom acceptance email
     */
    protected function sendCustomAcceptanceEmail($membership, $template)
    {
        $space = $membership->space;
        $user = $membership->user;
        
        // Prepare variables
        $variables = [
            'space_name' => $space->name,
            'admin_name' => Yii::$app->user->identity->displayName,
            'user_name' => $user->displayName,
            'user_email' => $user->email,
            'application_date' => $membership->created_at, // Already in correct format
            'accepted_date' => date('Y-m-d H:i:s'),
        ];
        
        // Add application answers if available
        $answers = \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer::find()
            ->where(['membership_id' => $membership->id])
            ->with('question')
            ->all();
            
        if (!empty($answers)) {
            $answersText = '';
            foreach ($answers as $answer) {
                $answersText .= "Q: " . $answer->question->question_text . "\n";
                $answersText .= "A: " . $answer->answer_text . "\n\n";
            }
            $variables['application_answers'] = trim($answersText);
        } else {
            $variables['application_answers'] = Yii::t('SpaceJoinQuestionsModule.base', 'No answers provided.');
        }
        
        // Process template with recipient user for proper file token generation
        $processed = $template->processTemplate($variables, $user);
        
        // Send email
        $mail = Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')])
            ->setTo($user->email)
            ->setSubject($processed['subject'])
            ->setHtmlBody($processed['body']);
            
        $mail->send();
    }

    /**
     * Send custom decline email
     */
    protected function sendCustomDeclineEmail($user, $space, $declineReason, $template)
    {
        // Prepare variables
        $variables = [
            'space_name' => $space->name,
            'admin_name' => Yii::$app->user->identity->displayName,
            'user_name' => $user->displayName,
            'user_email' => $user->email,
            'application_date' => date('Y-m-d H:i:s'),
            'declined_date' => date('Y-m-d H:i:s'),
            'decline_reason' => $declineReason,
            'admin_notes' => Yii::t('SpaceJoinQuestionsModule.base', 'Thank you for your interest. Please review our guidelines and consider applying again.'),
        ];
        
        // Process template with recipient user for proper file token generation
        $processed = $template->processTemplate($variables, $user);
        
        // Send email
        $mail = Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')])
            ->setTo($user->email)
            ->setSubject($processed['subject'])
            ->setHtmlBody($processed['body']);
            
        $mail->send();
    }

    /**
     * Manage notification recipients
     */
    public function actionNotificationRecipients()
    {
        $space = $this->contentContainer;
        
        // Get space administrators (including owner)
        $admins = $space->getAdmins();
        $owner = $space->getOwnerUser()->one();
        
        // Add owner to admins list if not already included
        $allAdmins = [];
        if ($owner) {
            $allAdmins[] = $owner;
        }
        foreach ($admins as $admin) {
            if (!$owner || $admin->id !== $owner->id) {
                $allAdmins[] = $admin;
            }
        }
        
        // Get currently selected recipients
        $selectedRecipients = \humhub\modules\spaceJoinQuestions\models\SpaceJoinNotificationRecipient::getRecipientsForSpace($space->id);
        $selectedUserIds = array_column($selectedRecipients, 'user_id');
        
        return $this->render('notification-recipients', [
            'space' => $space,
            'admins' => $allAdmins,
            'selectedUserIds' => $selectedUserIds,
        ]);
    }

    /**
     * Save admin notification recipients
     */
    public function actionSaveRecipients()
    {
        $space = $this->contentContainer;
        
        if (Yii::$app->request->isPost) {
            $selectedUserIds = Yii::$app->request->post('recipients', []);
            
            // Clear existing recipients
            \humhub\modules\spaceJoinQuestions\models\SpaceJoinNotificationRecipient::clearRecipientsForSpace($space->id);
            
            // Add selected recipients
            $success = true;
            foreach ($selectedUserIds as $userId) {
                if (!\humhub\modules\spaceJoinQuestions\models\SpaceJoinNotificationRecipient::addRecipient($space->id, $userId)) {
                    $success = false;
                }
            }
            
            if ($success) {
                $this->view->success('Notification recipients updated successfully');
            } else {
                $this->view->error('Some recipients could not be saved');
            }
        }
        
        return $this->redirect($space->createUrl('/space-join-questions/admin/notification-recipients'));
    }

} 