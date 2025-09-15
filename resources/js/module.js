/**
 * Space Join Questions Module JavaScript
 */

$(document).ready(function() {
    
    // Initialize sortable questions list
    if ($('#sortable-questions').length) {
        $('#sortable-questions').sortable({
            handle: '.question-item',
            cursor: 'move',
            placeholder: 'question-placeholder',
            update: function(event, ui) {
                var questionIds = [];
                $('#sortable-questions .question-item').each(function() {
                    questionIds.push($(this).data('question-id'));
                });
                
                // Save new order via AJAX
                $.ajax({
                    url: $('#sortable-questions').data('sort-url'),
                    type: 'POST',
                    data: { questions: questionIds },
                    success: function(response) {
                        if (response.success) {
                            humhub.modules.ui.status.success('Question order updated');
                        }
                    }
                });
            }
        });
    }

    // Field type change handler
    $(document).on('change', '#field-type-select', function() {
        var fieldType = $(this).val();
        var optionsRow = $('#field-options-row');
        
        if (fieldType === 'select' || fieldType === 'radio') {
            optionsRow.slideDown();
        } else {
            optionsRow.slideUp();
        }
    });

    // Form validation enhancements
    $('form').on('beforeSubmit', function() {
        var hasErrors = false;
        
        // Check required questions
        $(this).find('input[required], textarea[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('has-error');
                hasErrors = true;
            } else {
                $(this).removeClass('has-error');
            }
        });
        
        return !hasErrors;
    });

}); 