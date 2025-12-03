/**
 * Tickets JavaScript
 * Ticket Details, Create Ticket, Comments
 */

(function($) {
    'use strict';
    
    const PTSTickets = {
        
        init: function() {
            this.setupCreateTicketModal();
            this.setupCommentComposer();
            this.setupFileUpload();
            this.setupCommentActions();
            this.setupTicketFilters();
        },
        
        // Create Ticket Modal
        setupCreateTicketModal: function() {
            // Open modal
            $(document).on('click', '#pts-create-ticket-btn, .pts-create-ticket-trigger', function(e) {
                e.preventDefault();
                PTS.openModal('pts-create-ticket-modal');
            });
            
            // Form submission
            $('#pts-ticket-form').on('submit', function(e) {
                e. preventDefault();
                PTSTickets.submitTicket();
            });
            
            // Character counter for description
            $('#pts-ticket-description').on('input', function() {
                const length = $(this).text().trim().length;
                $('#pts-char-count').text(length);
                
                // Validate minimum length
                if (length < 20) {
                    $(this).addClass('pts-invalid');
                } else {
                    $(this).removeClass('pts-invalid');
                }
            });
        },
        
        submitTicket: function() {
            const $form = $('#pts-ticket-form');
            const $submitBtn = $('#pts-create-ticket-submit');
            
            // Get form data
            const title = $('#pts-ticket-title').val(). trim();
            const description = $('#pts-ticket-description').text().trim();
            const shiftLeader = $('#pts-shift-leader').val();
            const emailId = $('#pts-email-id'). val(). trim();
            const category = $('#pts-category').val();
            const priority = $('#pts-priority').val();
            
            // Validate
            if (!title) {
                PTS.showToast('Please enter a ticket title', 'error');
                return;
            }
            
            if (description.length < 20) {
                PTS.showToast('Description must be at least 20 characters', 'error');
                return;
            }
            
            // Get attached files
            const attachments = [];
            $('. pts-uploaded-files . pts-attached-file').each(function() {
                attachments.push($(this).data('attachment-id'));
            });
            
            // Disable submit button
            $submitBtn. prop('disabled', true). html('<span class="pts-loading"></span> Creating.. .');
            
            // Submit via AJAX
            PTS.ajax('pts_create_ticket', {
                title: title,
                description: description,
                shift_leader: shiftLeader,
                email_id: emailId,
                category: category,
                priority: priority,
                attachments: attachments
            }, function(data) {
                PTS.showToast(data. message || 'Ticket created successfully', 'success');
                PTS.closeModal();
                
                // Redirect to ticket details
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.ticket_id) {
                    window. location.href = '? page=ticket-details&ticket_id=' + data.ticket_id;
                } else {
                    location.reload();
                }
            }, function() {
                $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Create Ticket');
            });
        },
        
        // Comment Composer
        setupCommentComposer: function() {
            // Submit reply
            $('#pts-submit-reply'). on('click', function() {
                PTSTickets.submitComment();
            });
            
            // Attach file button
            $('#pts-attach-file').on('click', function() {
                $('#pts-file-input').click();
            });
            
            // File input change
            $('#pts-file-input').on('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    PTSTickets.uploadCommentFiles(files);
                }
            });
        },
        
        submitComment: function() {
            const $editor = $('#pts-comment-editor');
            const content = $editor.html(). trim();
            const ticketId = $('#pts-submit-reply').data('ticket-id');
            
            if (!content) {
                PTS.showToast('Please enter a comment', 'error');
                return;
            }
            
            // Get attachments
            const attachments = [];
            $('#pts-attached-files .pts-attached-file').each(function() {
                attachments.push($(this). data('attachment-id'));
            });
            
            // Disable button
            const $btn = $('#pts-submit-reply');
            $btn.prop('disabled', true).text('Posting...');
            
            PTS.ajax('pts_add_comment', {
                ticket_id: ticketId,
                content: content,
                attachments: attachments
            }, function(data) {
                PTS.showToast(data.message || 'Comment posted successfully', 'success');
                
                // Clear editor
                $editor.html('');
                $('#pts-attached-files'). empty();
                
                // Add comment to timeline
                if (data.comment_html) {
                    $('#pts-comments-list').prepend(data.comment_html);
                } else {
                    location.reload();
                }
                
                $btn.prop('disabled', false). text('Reply');
            }, function() {
                $btn.prop('disabled', false).text('Reply');
            });
        },
        
        uploadCommentFiles: function(files) {
            const $container = $('#pts-attached-files');
            
            Array.from(files).forEach(function(file) {
                // Validate file
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                
                if (file.size > maxSize) {
                    PTS.showToast(ptsAjax.strings. file_too_large, 'error');
                    return;
                }
                
                if (!allowedTypes.includes(file.type)) {
                    PTS. showToast(ptsAjax.strings.invalid_file_type, 'error');
                    return;
                }
                
                // Upload file
                const formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'pts_upload_attachment');
                formData.append('nonce', ptsAjax.nonce);
                
                $. ajax({
                    url: ptsAjax.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            const $file = $('<div>', {
                                class: 'pts-attached-file',
                                'data-attachment-id': data.attachment_id
                            }). html(`
                                <span class="dashicons dashicons-media-document"></span>
                                <span>${data.filename}</span>
                                <button type="button" class="pts-remove-attachment" data-attachment-id="${data.attachment_id}">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            `);
                            
                            $container.append($file);
                        } else {
                            PTS.showToast(response.data.message || 'Upload failed', 'error');
                        }
                    },
                    error: function() {
                        PTS.showToast('Upload failed', 'error');
                    }
                });
            });
        },
        
        // File Upload for Create Ticket
        setupFileUpload: function() {
            const $dropZone = $('#pts-file-drop-zone');
            const $fileInput = $('#pts-file-upload-input');
            const $selectBtn = $('#pts-select-file-btn');
            
            // Click to select
            $selectBtn.on('click', function() {
                $fileInput.click();
            });
            
            // File input change
            $fileInput.on('change', function() {
                const files = this. files;
                if (files. length > 0) {
                    PTSTickets.uploadTicketFiles(files);
                }
            });
            
            // Drag and drop
            $dropZone. on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });
            
            $dropZone.on('dragleave', function() {
                $(this).removeClass('drag-over');
            });
            
            $dropZone. on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    PTSTickets. uploadTicketFiles(files);
                }
            });
            
            // Remove attachment
            $(document).on('click', '.pts-remove-attachment', function() {
                $(this).closest('.pts-attached-file').remove();
            });
        },
        
        uploadTicketFiles: function(files) {
            const $uploadedFiles = $('#pts-uploaded-files');
            const $progress = $('#pts-upload-progress');
            
            Array.from(files).forEach(function(file) {
                // Validate
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                
                if (file.size > maxSize) {
                    PTS.showToast(ptsAjax.strings.file_too_large, 'error');
                    return;
                }
                
                if (!allowedTypes.includes(file.type)) {
                    PTS. showToast(ptsAjax.strings.invalid_file_type, 'error');
                    return;
                }
                
                // Show progress
                $progress.show(). find('.pts-upload-filename').text(file.name);
                
                // Upload
                const formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'pts_upload_attachment');
                formData.append('nonce', ptsAjax.nonce);
                
                $.ajax({
                    url: ptsAjax.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window. XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e. total) * 100;
                                $progress.find('.pts-progress-fill').css('width', percent + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $progress.hide();
                        
                        if (response.success) {
                            const data = response.data;
                            const $file = $('<div>', {
                                class: 'pts-attached-file',
                                'data-attachment-id': data. attachment_id
                            }).html(`
                                <span class="dashicons dashicons-media-document"></span>
                                <span>${data.filename}</span>
                                <button type="button" class="pts-remove-attachment">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            `);
                            
                            $uploadedFiles.append($file);
                        } else {
                            PTS.showToast(response.data.message || 'Upload failed', 'error');
                        }
                    },
                    error: function() {
                        $progress.hide();
                        PTS.showToast('Upload failed', 'error');
                    }
                });
            });
        },
        
        // Comment Actions
        setupCommentActions: function() {
            // Delete comment
            $(document).on('click', '.pts-delete-comment', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const commentId = $(this).data('comment-id');
                const $comment = $(this).closest('.pts-comment');
                
                PTS.confirm(ptsAjax.strings.confirm_delete || 'Are you sure? ', function() {
                    PTS.ajax('pts_delete_comment', {
                        comment_id: commentId
                    }, function(data) {
                        PTS.showToast(data. message || 'Comment deleted', 'success');
                        $comment.fadeOut(function() {
                            $(this). remove();
                        });
                    });
                });
            });
        },
        
        // Ticket Filters
        setupTicketFilters: function() {
            // Tab filtering
            $('. pts-tab'). on('click', function() {
                const status = $(this).data('status');
                
                $('. pts-tab').removeClass('active');
                $(this).addClass('active');
                
                PTSTickets.filterTickets({ status: status });
            });
        },
        
        filterTickets: function(filters) {
            const $table = $('#pts-tickets-table tbody');
            const $loading = $('<tr><td colspan="8" class="pts-empty-state"><span class="pts-loading"></span> Loading...</td></tr>');
            
            $table.html($loading);
            
            PTS.ajax('pts_filter_tickets', filters, function(data) {
                if (data.html) {
                    $table. html(data.html);
                } else {
                    $table.html('<tr><td colspan="8" class="pts-empty-state">No tickets found</td></tr>');
                }
            });
        }
    };
    
    // Initialize
    $(document).ready(function() {
        PTSTickets.init();
    });
    
    // Make globally available
    window.PTSTickets = PTSTickets;
    
})(jQuery);
