/**
 * Main JavaScript
 * Global functionality
 */

(function($) {
    'use strict';
    
    const PTS = {
        
        init: function() {
            this.setupGlobalSearch();
            this.setupUserMenu();
            this.setupNotifications();
            this.setupToasts();
            this.setupModals();
            this.initRichEditors();
        },
        
        // Global Search
        setupGlobalSearch: function() {
            const $searchInput = $('#pts-global-search-input');
            const $dropdown = $('#pts-search-dropdown');
            let searchTimeout;
            
            $searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                
                if (query.length < 2) {
                    $dropdown.hide();
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    PTS.performSearch(query);
                }, 300);
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (! $(e.target).closest('.pts-global-search'). length) {
                    $dropdown.hide();
                }
            });
        },
        
        performSearch: function(query) {
            $. ajax({
                url: ptsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pts_search',
                    nonce: ptsAjax. nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success) {
                        PTS.displaySearchResults(response.data. results);
                    }
                },
                error: function() {
                    PTS.showToast(ptsAjax.strings.error || 'Search failed', 'error');
                }
            });
        },
        
        displaySearchResults: function(results) {
            const $dropdown = $('#pts-search-dropdown');
            
            if (results.length === 0) {
                $dropdown. html('<div class="pts-search-no-results">No results found</div>'). show();
                return;
            }
            
            let html = '<div class="pts-search-results">';
            results.forEach(function(result) {
                html += `
                    <a href="${result.url}" class="pts-search-result-item">
                        <div class="pts-search-result-type">${result.type}</div>
                        <div class="pts-search-result-title">${result.title}</div>
                        <div class="pts-search-result-excerpt">${result.excerpt}</div>
                    </a>
                `;
            });
            html += '</div>';
            
            $dropdown.html(html).show();
        },
        
        // User Menu
        setupUserMenu: function() {
            $('#pts-user-menu-btn').on('click', function(e) {
                e.stopPropagation();
                $('#pts-user-dropdown').toggle();
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('. pts-user-menu-wrapper').length) {
                    $('#pts-user-dropdown').hide();
                }
            });
        },
        
        // Notifications
        setupNotifications: function() {
            $('#pts-notifications-btn').on('click', function() {
                // TODO: Implement notifications panel
                alert('Notifications feature coming soon! ');
            });
        },
        
        // Toast Notifications
        setupToasts: function() {
            // Toast queue
            window.ptsToastQueue = [];
        },
        
        showToast: function(message, type = 'info', duration = 3000) {
            const $toast = $('<div>', {
                class: 'pts-toast pts-toast-' + type,
                text: message
            });
            
            $('body').append($toast);
            
            setTimeout(function() {
                $toast. fadeOut(function() {
                    $(this).remove();
                });
            }, duration);
        },
        
        // Modals
        setupModals: function() {
            // Close modal on overlay click
            $(document).on('click', '. pts-modal-overlay', function() {
                PTS.closeModal();
            });
            
            // Close modal on close button
            $(document).on('click', '.pts-close-modal, .pts-cancel-btn', function() {
                PTS.closeModal();
            });
            
            // Escape key to close
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    PTS.closeModal();
                }
            });
        },
        
        openModal: function(modalId) {
            $('#' + modalId).fadeIn(200);
        },
        
        closeModal: function() {
            $('. pts-modal'). fadeOut(200);
        },
        
        // Rich Text Editors
        initRichEditors: function() {
            $(document).on('click', '.pts-editor-btn', function(e) {
                e.preventDefault();
                const command = $(this).data('command');
                
                if (command === 'createLink') {
                    const url = prompt('Enter URL:');
                    if (url) {
                        document.execCommand(command, false, url);
                    }
                } else {
                    document.execCommand(command, false, null);
                }
                
                $(this).toggleClass('active');
            });
            
            // Character counter
            $('. pts-rich-editor').on('input', function() {
                const $counter = $(this).siblings('.pts-char-counter'). find('#pts-char-count');
                if ($counter.length) {
                    const length = $(this).text().length;
                    $counter.text(length);
                }
            });
        },
        
        // AJAX Helper
        ajax: function(action, data, successCallback, errorCallback) {
            $.ajax({
                url: ptsAjax.ajaxurl,
                type: 'POST',
                data: $. extend({
                    action: action,
                    nonce: ptsAjax.nonce
                }, data),
                success: function(response) {
                    if (response.success) {
                        if (successCallback) successCallback(response.data);
                    } else {
                        PTS.showToast(response. data.message || 'An error occurred', 'error');
                        if (errorCallback) errorCallback(response);
                    }
                },
                error: function(xhr, status, error) {
                    PTS.showToast('Request failed: ' + error, 'error');
                    if (errorCallback) errorCallback(xhr);
                }
            });
        },
        
        // Confirmation Dialog
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        PTS. init();
    });
    
    // Make PTS globally available
    window.PTS = PTS;
    
})(jQuery);
