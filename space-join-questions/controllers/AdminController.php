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

use humhub\modules\spaceJoinQuestions\models\forms\QuestionForm;
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

        // Check if user is space admin
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
     * List all membership applications
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

        return $this->render('applications', [
            'dataProvider' => $dataProvider,
            'space' => $this->contentContainer,
        ]);
    }

    /**
     * View application details
     */
    public function actionApplicationDetail($membershipId)
    {
        $this->checkPermission(new ViewApplications());

        $membership = Membership::findOne(['id' => $membershipId, 'space_id' => $this->contentContainer->id]);
        
        if (!$membership) {
            throw new HttpException(404, 'Application not found');
        }

        $answers = SpaceJoinAnswer::find()
            ->where(['membership_id' => $membershipId])
            ->with(['question'])
            ->all();

        return $this->render('application-detail', [
            'application' => $membership,
            'answers' => $answers,
            'space' => $this->contentContainer,
            'isDeclined' => false,
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
            
            // Send email notification to user
            try {
                $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
                    $this->contentContainer->id, 
                    \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_ACCEPTED
                );
                
                if ($template && $template->is_active) {
                    // Use custom template
                    $this->sendCustomAcceptanceEmail($membership, $template);
                } else {
                    // Use default notification
                    $notification = new ApplicationAccepted();
                    $notification->source = $membership;
                    $notification->originator = Yii::$app->user->identity;
                    
                    if ($membership->user && $membership->user->id) {
                        $notification->sendDirect($membership->user);
                    }
                }
            } catch (\Exception $e) {
                Yii::error('Error sending notification: ' . $e->getMessage());
                // Don't fail the approval if notification fails
            }

            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Application approved successfully'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to approve application'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        }
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

        // Get the decline reason from POST data
        $declineReason = trim(Yii::$app->request->post('decline_reason', ''));

        // Validate that decline reason is provided
        if (empty($declineReason)) {
            Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'A decline reason is required.'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/application-detail', ['membershipId' => $membershipId]));
        }

        // Store necessary information before deleting the membership
        $user = $membership->user;
        $space = $membership->space;
        $membershipId = $membership->id;

        // For declined applications, we delete the membership record
        if ($membership->delete()) {
            // Send email notification to user
            try {
                $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
                    $this->contentContainer->id, 
                    \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_DECLINED
                );
                
                if ($template && $template->is_active) {
                    // Use custom template
                    $this->sendCustomDeclineEmail($user, $space, $declineReason, $template);
                } else {
                    // Use default notification
                    $notification = new ApplicationDeclined();
                    
                    // Create a mock membership object with the necessary information
                    $mockMembership = new \stdClass();
                    $mockMembership->id = $membershipId;
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
                // Don't fail the decline if notification fails
            }

            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Application declined successfully'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to decline application'));
            return $this->redirect($this->contentContainer->createUrl('/space-join-questions/admin/applications'));
        }
    }



    /**
     * Module settings
     */
    public function actionSettings()
    {
        $space = $this->contentContainer;
        $settings = $space->getSettings();
        $emailNotifications = $settings->get('emailNotifications', 'spaceJoinQuestions', true);

        if (Yii::$app->request->isPost) {
            $settingsData = Yii::$app->request->post('settings', []);
            $emailNotifications = isset($settingsData['emailNotifications']) ? (bool)$settingsData['emailNotifications'] : false;
            
            $settings->set('emailNotifications', $emailNotifications, 'spaceJoinQuestions');
            
            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Settings saved successfully'));
            return $this->redirect($space->createUrl('/space-join-questions/admin/settings'));
        }

        return $this->render('settings', [
            'space' => $space,
            'emailNotifications' => $emailNotifications,
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