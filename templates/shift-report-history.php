<?php
/**
 * Shift Report History (Screen 3/Image 2)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get reports
$reports_args = array(
    'post_type' => 'pts_shift_report',
    'post_status' => 'publish',
    'posts_per_page' => 20,
    'orderby' => 'date',
    'order' => 'DESC',
);

$reports = get_posts($reports_args);
?>

<div class="pts-wrapper">
    <? php include PTS_PLUGIN_DIR . 'templates/partials/global-header.php'; ?>
    
    <div class="pts-content">
        
        <!-- Page Header -->
        <div class="pts-page-header">
            <div class="pts-page-header-left">
                <button class="pts-back-btn" onclick="history.back()">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h1><? php _e('Report History', 'prospectra-ticketing-system'); ?></h1>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="pts-filter-bar">
            <div class="pts-filter-pills">
                <button class="pts-pill active" data-range="24h">
                    <?php _e('Last 24 Hours', 'prospectra-ticketing-system'); ?>
                </button>
                <button class="pts-pill" data-range="week">
                    <?php _e('Last Week', 'prospectra-ticketing-system'); ?>
                </button>
                <button class="pts-pill" data-range="custom">
                    <?php _e('Custom', 'prospectra-ticketing-system'); ?>
                </button>
            </div>
            
            <div class="pts-filter-actions">
                <button class="pts-btn pts-btn-outline" id="pts-filter-btn">
                    <span class="dashicons dashicons-filter"></span>
                    <? php _e('Filter', 'prospectra-ticketing-system'); ?>
                </button>
                
                <div class="pts-search-box">
                    <input 
                        type="text" 
                        id="pts-report-search" 
                        class="pts-input" 
                        placeholder="<? php esc_attr_e('Search reports... ', 'prospectra-ticketing-system'); ?>"
                    >
                    <span class="dashicons dashicons-search"></span>
                </div>
            </div>
        </div>
        
        <!-- Active Filters -->
        <div id="pts-active-filters" class="pts-active-filters" style="display: none;">
            <!-- Filter chips will be added here dynamically -->
        </div>
        
        <!-- Reports Table -->
        <div class="pts-card">
            <div class="pts-card-body">
                <table class="pts-table pts-reports-table">
                    <thead>
                        <tr>
                            <th><?php _e('Submitted on', 'prospectra-ticketing-system'); ?></th>
                            <th><?php _e('Shift Type', 'prospectra-ticketing-system'); ?></th>
                            <th><?php _e('Shift Leader', 'prospectra-ticketing-system'); ?></th>
                            <th><?php _e('Issues Reported', 'prospectra-ticketing-system'); ?></th>
                            <th><?php _e('Key Notes / Instructions', 'prospectra-ticketing-system'); ?></th>
                            <th><?php _e('Action', 'prospectra-ticketing-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="pts-reports-table-body">
                        <? php if (!empty($reports)): ?>
                            <?php foreach ($reports as $report): 
                                include PTS_PLUGIN_DIR .  'templates/partials/report-row.php';
                            endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="pts-empty-state">
                                    <?php _e('No reports found. ', 'prospectra-ticketing-system'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="pts-pagination">
            <button class="pts-btn pts-btn-outline" disabled>
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <? php _e('Previous', 'prospectra-ticketing-system'); ?>
            </button>
            
            <div class="pts-page-numbers">
                <button class="pts-page-number active">1</button>
                <button class="pts-page-number">2</button>
                <button class="pts-page-number">3</button>
                <span>...</span>
                <button class="pts-page-number">10</button>
            </div>
            
            <button class="pts-btn pts-btn-outline">
                <? php _e('Next', 'prospectra-ticketing-system'); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
        </div>
        
    </div>
</div>

<!-- Filter Panel (Slide-in) -->
<div id="pts-filter-panel" class="pts-filter-panel">
    <div class="pts-filter-panel-header">
        <h3><?php _e('Filter Reports', 'prospectra-ticketing-system'); ?></h3>
        <button class="pts-close-panel">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
    </div>
    
    <div class="pts-filter-panel-body">
        
        <div class="pts-filter-group">
            <label><? php _e('Shift Type', 'prospectra-ticketing-system'); ?></label>
            <select class="pts-select" name="filter_shift_type">
                <option value=""><?php _e('All', 'prospectra-ticketing-system'); ?></option>
                <option value="Morning"><?php _e('Morning', 'prospectra-ticketing-system'); ?></option>
                <option value="Afternoon"><? php _e('Afternoon', 'prospectra-ticketing-system'); ?></option>
                <option value="Evening"><?php _e('Evening', 'prospectra-ticketing-system'); ?></option>
                <option value="Night"><?php _e('Night', 'prospectra-ticketing-system'); ?></option>
            </select>
        </div>
        
        <div class="pts-filter-group">
            <label><?php _e('Shift Leader', 'prospectra-ticketing-system'); ?></label>
            <select class="pts-select" name="filter_shift_leader">
                <option value=""><?php _e('All', 'prospectra-ticketing-system'); ?></option>
                <?php
                $shift_leaders = get_users(array('role__in' => array('pts_shift_leader', 'pts_team_leader')));
                foreach ($shift_leaders as $leader):
                ?>
                    <option value="<? php echo $leader->ID; ?>"><?php echo esc_html($leader->display_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="pts-filter-group">
            <label><?php _e('Number of Issues', 'prospectra-ticketing-system'); ?></label>
            <div class="pts-range-inputs">
                <input type="number" class="pts-input" name="filter_issues_min" placeholder="<?php esc_attr_e('Min', 'prospectra-ticketing-system'); ?>">
                <span>-</span>
                <input type="number" class="pts-input" name="filter_issues_max" placeholder="<?php esc_attr_e('Max', 'prospectra-ticketing-system'); ?>">
            </div>
        </div>
        
        <div class="pts-filter-group">
            <label><?php _e('Organization', 'prospectra-ticketing-system'); ?></label>
            <select class="pts-select" name="filter_organization">
                <option value=""><?php _e('All', 'prospectra-ticketing-system'); ?></option>
                <?php
                $organizations = get_terms(array('taxonomy' => 'pts_organization', 'hide_empty' => false));
                foreach ($organizations as $org):
                ?>
                    <option value="<? php echo $org->term_id; ?>"><?php echo esc_html($org->name); ?></option>
                <? php endforeach; ?>
            </select>
        </div>
        
    </div>
    
    <div class="pts-filter-panel-footer">
        <button class="pts-btn pts-btn-outline" id="pts-clear-filters">
            <?php _e('Clear All', 'prospectra-ticketing-system'); ?>
        </button>
        <button class="pts-btn pts-btn-primary" id="pts-apply-filters">
            <?php _e('Apply Filters', 'prospectra-ticketing-system'); ?>
        </button>
    </div>
</div>
