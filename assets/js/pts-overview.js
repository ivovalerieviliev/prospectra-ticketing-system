/**
 * Shift Overview JavaScript
 * Dashboard functionality
 */

(function($) {
    'use strict';
    
    const PTSOverview = {
        
        init: function() {
            this.setupUrgentTasks();
            this.setupShiftCountdown();
            this.setupMetrics();
            this.setupFilters();
            this.setupActionMenus();
        },
        
        // Urgent Tasks
        setupUrgentTasks: function() {
            // Mark task complete
            $(document).on('click', '.pts-mark-complete', function(e) {
                e.stopPropagation();
                const taskId = $(this).data('task-id');
                const $row = $(this).closest('tr');
                
                PTS.ajax('pts_mark_task_complete', {
                    task_id: taskId
                }, function(data) {
                    PTS.showToast(data. message || 'Task marked as complete', 'success');
                    $row.fadeOut(function() {
                        $(this).remove();
                        
                        // Check if table is empty
                        if ($('. pts-urgent-tasks tbody tr').length === 0) {
                            $('. pts-urgent-tasks tbody').html('<tr><td colspan="6" class="pts-empty-state">No urgent tasks at this time. </td></tr>');
                        }
                    });
                });
            });
        },
        
        // Shift Countdown
        setupShiftCountdown: function() {
            const $countdown = $('. pts-countdown');
            if ($countdown.length === 0) return;
            
            const endTime = $countdown.data('end-time');
            if (!endTime) return;
            
            // Parse end time (format: HH:MM)
            const now = new Date();
            const [hours, minutes] = endTime. split(':');
            const endDate = new Date(now);
            endDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
            
            // If end time is earlier than now, it's tomorrow
            if (endDate < now) {
                endDate.setDate(endDate.getDate() + 1);
            }
            
            // Update countdown every second
            const interval = setInterval(function() {
                const now = new Date();
                const diff = endDate - now;
                
                if (diff <= 0) {
                    $countdown.text('00:00:00');
                    clearInterval(interval);
                    return;
                }
                
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                $countdown.text(
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0')
                );
            }, 1000);
        },
        
        // Metrics
        setupMetrics: function() {
            // Refresh metrics every 5 minutes
            setInterval(function() {
                PTSOverview.refreshMetrics();
            }, 5 * 60 * 1000);
            
            // Click metric to filter
            $('. pts-metric'). on('click', function() {
                const metricType = $(this).find('.pts-metric-label').text(). toLowerCase();
                // TODO: Filter tickets/orders by metric type
                console.log('Filter by:', metricType);
            });
        },
        
        refreshMetrics: function() {
            PTS.ajax('pts_calculate_metrics', {}, function(data) {
                if (data.metrics) {
                    // Update metric values
                    Object.keys(data.metrics).forEach(function(key) {
                        const value = data.metrics[key];
                        $('[data-metric="' + key + '"] .pts-metric-value').text(value);
                    });
                }
            });
        },
        
        // Filters
        setupFilters: function() {
            // Order filters
            $('#pts-filter-orders').on('click', function() {
                // TODO: Open filter panel for orders
                alert('Order filters coming soon! ');
            });
        },
        
        // Action Menus
        setupActionMenus: function() {
            // Toggle action menu
            $(document).on('click', '.pts-action-menu-btn', function(e) {
                e.stopPropagation();
                const $menu = $(this).closest('.pts-action-menu');
                
                // Close other menus
                $('.pts-action-menu').not($menu).removeClass('active');
                
                // Toggle this menu
                $menu.toggleClass('active');
            });
            
            // Close menus when clicking outside
            $(document).on('click', function(e) {
                if (! $(e.target).closest('.pts-action-menu').length) {
                    $('.pts-action-menu').removeClass('active');
                }
            });
            
            // Quick actions
            $(document).on('click', '.pts-change-status', function(e) {
                e.stopPropagation();
                const ticketId = $(this).data('ticket-id');
                // TODO: Open status change modal
                console.log('Change status for ticket:', ticketId);
            });
            
            $(document).on('click', '.pts-reassign', function(e) {
                e.stopPropagation();
                const ticketId = $(this).data('ticket-id');
                // TODO: Open reassign modal
                console.log('Reassign ticket:', ticketId);
            });
            
            $(document).on('click', '.pts-mark-solved', function(e) {
                e.stopPropagation();
                const ticketId = $(this).data('ticket-id');
                
                PTS.ajax('pts_update_ticket_meta', {
                    ticket_id: ticketId,
                    meta_key: '_pts_ticket_status',
                    meta_value: 'Resolved'
                }, function(data) {
                    PTS.showToast(data.message || 'Ticket marked as solved', 'success');
                    location.reload();
                });
            });
        }
    };
    
    // Initialize
    $(document).ready(function() {
        PTSOverview.init();
    });
    
})(jQuery);
