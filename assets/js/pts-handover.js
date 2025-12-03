/**
 * Handover Report JavaScript
 * Create handover report functionality
 */

(function($) {
    'use strict';
    
    const PTSHandover = {
        
        autosaveInterval: null,
        
        init: function() {
            this. setupShiftTypeAutoFill();
            this.setupEditableTables();
            this.setupEmailChips();
            this.setupAutosave();
            this.setupFormSubmission();
        },
        
        // Shift Type Auto-fill
        setupShiftTypeAutoFill: function() {
            $('#pts-shift-type').on('change', function() {
                const $option = $(this). find('option:selected');
                const startTime = $option.data('start');
                const endTime = $option.data('end');
                
                // TODO: Update time fields if needed
                console.log('Shift times:', startTime, endTime);
            });
        },
        
        // Editable Tables
        setupEditableTables: function() {
            // Add production plan row
            $('#pts-add-production-plan').on('click', function() {
                PTSHandover.addTableRow('production-plan');
            });
            
            // Add upcoming task row
            $('#pts-add-upcoming-task').on('click', function() {
                PTSHandover.addTableRow('upcoming-production');
            });
            
            // Add follow-up task row
            $('#pts-add-followup-task').on('click', function() {
                PTSHandover.addTableRow('followup-tasks');
            });
            
            // Add issue row
            $('#pts-add-issue').on('click', function() {
                PTSHandover. addTableRow('issues-summary');
            });
            
            // Remove row
            $(document).on('click', '.pts-remove-row', function() {
                const $row = $(this).closest('tr');
                $row.fadeOut(function() {
                    $(this).remove();
                });
            });
        },
        
        addTableRow: function(tableType) {
            const $table = $('#pts-' + tableType + '-table tbody');
            const rowIndex = $table.find('tr'). length;
            
            // Remove empty row if exists
            $table.find('. pts-empty-row').remove();
            
            let rowHtml = '';
            
            switch(tableType) {
                case 'production-plan':
                    rowHtml = `
                        <tr>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][job_id]" placeholder="Job ID"></td>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][customer]" placeholder="Customer"></td>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][start_finish]" placeholder="HH:MM – HH:MM"></td>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][quantity]" placeholder="0 / 0"></td>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][machine]" placeholder="Machine"></td>
                            <td><select class="pts-select-cell" name="production_plan[${rowIndex}][priority]">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select></td>
                            <td><input type="text" class="pts-input-cell" name="production_plan[${rowIndex}][instructions]" placeholder="Instructions"></td>
                            <td><button type="button" class="pts-icon-btn pts-remove-row"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    `;
                    break;
                    
                case 'upcoming-production':
                    rowHtml = `
                        <tr>
                            <td><input type="text" class="pts-input-cell" name="upcoming_production[${rowIndex}][job_id]" placeholder="Job ID"></td>
                            <td><input type="text" class="pts-input-cell" name="upcoming_production[${rowIndex}][customer]" placeholder="Customer"></td>
                            <td><input type="text" class="pts-input-cell" name="upcoming_production[${rowIndex}][start_finish]" placeholder="HH:MM – HH:MM"></td>
                            <td><input type="text" class="pts-input-cell" name="upcoming_production[${rowIndex}][quantity]" placeholder="0"></td>
                            <td><input type="text" class="pts-input-cell" name="upcoming_production[${rowIndex}][machine]" placeholder="Machine"></td>
                            <td><select class="pts-select-cell" name="upcoming_production[${rowIndex}][priority]">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select></td>
                            <td><button type="button" class="pts-icon-btn pts-remove-row"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    `;
                    break;
                    
                case 'followup-tasks':
                    rowHtml = `
                        <tr>
                            <td><input type="text" class="pts-input-cell" name="followup_tasks[${rowIndex}][task_id]" placeholder="Auto"></td>
                            <td><input type="datetime-local" class="pts-input-cell" name="followup_tasks[${rowIndex}][date_time]"></td>
                            <td><input type="text" class="pts-input-cell" name="followup_tasks[${rowIndex}][issued_by]" placeholder="Name"></td>
                            <td><input type="text" class="pts-input-cell" name="followup_tasks[${rowIndex}][task]" placeholder="Task description"></td>
                            <td><input type="text" class="pts-input-cell" name="followup_tasks[${rowIndex}][category]" placeholder="Category"></td>
                            <td><select class="pts-select-cell" name="followup_tasks[${rowIndex}][priority]">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select></td>
                            <td><input type="text" class="pts-input-cell" name="followup_tasks[${rowIndex}][assign_to]" placeholder="Assign to"></td>
                            <td><button type="button" class="pts-icon-btn pts-remove-row"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    `;
                    break;
                    
                case 'issues-summary':
                    rowHtml = `
                        <tr>
                            <td><input type="text" class="pts-input-cell" name="issues_summary[${rowIndex}][title]" placeholder="Issue title"></td>
                            <td><input type="text" class="pts-input-cell" name="issues_summary[${rowIndex}][machine]" placeholder="Machine"></td>
                            <td><input type="datetime-local" class="pts-input-cell" name="issues_summary[${rowIndex}][date_time]"></td>
                            <td><input type="text" class="pts-input-cell" name="issues_summary[${rowIndex}][issued_by]" placeholder="Name"></td>
                            <td><input type="text" class="pts-input-cell" name="issues_summary[${rowIndex}][category]" placeholder="Category"></td>
                            <td><select class="pts-select-cell" name="issues_summary[${rowIndex}][priority]">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select></td>
                            <td><select class="pts-select-cell" name="issues_summary[${rowIndex}][status]">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select></td>
                            <td><button type="button" class="pts-icon-btn pts-remove-row"><span class="dashicons dashicons-trash"></span></button></td>
                        </tr>
                    `;
                    break;
            }
            
            $table.append(rowHtml);
        },
        
        // Email Chips
        setupEmailChips: function() {
            $('#pts-recipient-email').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e. preventDefault();
                    PTSHandover.addEmailChip($(this).val());
                    $(this).val('');
                }
            });
            
            $('#pts-add-recipient-btn').on('click', function() {
                const email = $('#pts-recipient-email').val();
                PTSHandover.addEmailChip(email);
                $('#pts-recipient-email').val('');
            });
            
            $(document).on('click', '.pts-email-chip button', function() {
                $(this).closest('.pts-email-chip').remove();
            });
        },
        
        addEmailChip: function(email) {
            email = email.trim();
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                PTS.showToast('Invalid email address', 'error');
                return;
            }
            
            const $chip = $('<div>', {
                class: 'pts-email-chip'
            }). html(`
                <span>${email}</span>
                <input type="hidden" name="recipients[]" value="${email}">
                <button type="button"><span class="dashicons dashicons-no-alt"></span></button>
            `);
            
            $('#pts-email-chips').append($chip);
        },
        
        // Autosave
        setupAutosave: function() {
            // Autosave every 30 seconds
            this.autosaveInterval = setInterval(function() {
                PTSHandover.saveDraft();
            }, 30000);
            
            // Cancel button
            $('#pts-cancel-report').on('click', function() {
                if (confirm(ptsAjax.strings.unsaved_changes || 'Discard changes?')) {
                    window.history.back();
                }
            });
        },
        
        saveDraft: function() {
            // TODO: Implement draft saving
            $('#pts-autosave-indicator').fadeIn(). delay(2000).fadeOut();
        },
        
        // Form Submission
        setupFormSubmission: function() {
            $('#pts-handover-report-form').on('submit', function(e) {
                e. preventDefault();
                PTSHandover.submitReport();
            });
        },
        
        submitReport: function() {
            const $form = $('#pts-handover-report-form');
            const $submitBtn = $('#pts-create-report-submit');
            
            // Serialize form data
            const formData = $form.serializeArray();
            
            // Get key notes
            const keyNotes = $('#pts-key-notes').html();
            formData.push({ name: 'key_notes', value: keyNotes });
            
            // Disable button
            $submitBtn.prop('disabled', true). text('Creating Report...');
            
            // Convert to object
            const data = {};
            formData.forEach(function(item) {
                if (item.name. includes('[')) {
                    // Handle array data
                    const match = item.name.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const [, section, index, field] = match;
                        if (! data[section]) data[section] = [];
                        if (!data[section][index]) data[section][index] = {};
                        data[section][index][field] = item.value;
                    }
                } else {
                    data[item.name] = item. value;
                }
            });
            
            // Submit
            PTS.ajax('pts_create_report', data, function(response) {
                PTS.showToast(response. message || 'Report created successfully', 'success');
                
                // Clear autosave interval
                clearInterval(PTSHandover.autosaveInterval);
                
                // Redirect
                setTimeout(function() {
                    window.location.href = '? page=report-history';
                }, 1000);
            }, function() {
                $submitBtn.prop('disabled', false).text('Create Report');
            });
        }
    };
    
    // Initialize
    $(document).ready(function() {
        PTSHandover.init();
    });
    
})(jQuery);
