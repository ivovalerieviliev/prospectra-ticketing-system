/**
 * Inline Edit JavaScript
 * Handle inline editing of ticket metadata
 */

(function($) {
    'use strict';
    
    const PTSInlineEdit = {
        
        init: function() {
            this. setupInlineEditing();
        },
        
        setupInlineEditing: function() {
            // Handle inline edit changes
            $(document).on('change', '. pts-inline-edit', function() {
                const $field = $(this);
                const ticketId = $field.data('ticket-id');
                const metaKey = $field.data('meta-key');
                const newValue = $field.val();
                const oldValue = $field.data('old-value') || '';
                
                // Store old value if not already stored
                if (!$field.data('old-value')) {
                    $field.data('old-value', oldValue);
                }
                
                // Show loading state
                $field.addClass('pts-saving');
                
                // Update via AJAX
                PTS.ajax('pts_update_ticket_meta', {
                    ticket_id: ticketId,
                    meta_key: metaKey,
                    meta_value: newValue
                }, function(data) {
                    $field.removeClass('pts-saving');
                    $field.data('old-value', newValue);
                    
                    // Show toast notification
                    PTS.showToast(data. message || 'Updated successfully', 'success');
                    
                    // Update badge color if priority changed
                    if (metaKey === '_pts_ticket_priority') {
                        PTSInlineEdit.updatePriorityBadge(newValue);
                    }
                    
                    // Refresh page if status changed (to update timeline)
                    if (metaKey === '_pts_ticket_status') {
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }, function() {
                    // Revert on error
                    $field.removeClass('pts-saving');
                    $field.val(oldValue);
                });
            });
        },
        
        updatePriorityBadge: function(priority) {
            const badgeClass = 'pts-badge-' + priority. toLowerCase();
            $('. pts-ticket-meta . pts-badge').removeClass('pts-badge-low pts-badge-medium pts-badge-high pts-badge-emergency')
                                             .addClass(badgeClass)
                                             .text(priority);
        }
    };
    
    // Initialize
    $(document).ready(function() {
        PTSInlineEdit.init();
    });
    
})(jQuery);
