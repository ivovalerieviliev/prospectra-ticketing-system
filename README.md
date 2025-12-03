# Prospectra Ticketing System

A comprehensive WordPress plugin for managing internal and external issue tickets with integrated shift handover reporting capabilities.

## 🚀 Features

### **Ticketing System**
- ✅ Create, view, and manage issue tickets
- ✅ Real-time conversation timeline with comments
- ✅ File attachments (JPG, PNG, PDF)
- ✅ Inline editing of ticket metadata (status, priority, assignee, due date)
- ✅ Custom statuses and priority levels
- ✅ Email integration (bidirectional IMAP/SMTP)
- ✅ Automatic email-to-ticket conversion
- ✅ @mentions and notifications
- ✅ Advanced filtering and search

### **Shift Handover Reports**
- ✅ Create comprehensive shift handover reports
- ✅ Production plan tracking
- ✅ Follow-up tasks management
- ✅ Issues summary
- ✅ Key notes and instructions
- ✅ PDF and Excel export with branding
- ✅ Email distribution to teams
- ✅ Report history with filters

### **Shift Overview Dashboard**
- ✅ Real-time metrics (production volume, issues reported, efficiency)
- ✅ Urgent tasks management
- ✅ Shift details and countdown timer
- ✅ Order list with progress tracking
- ✅ Ticket management with tabs (All, Pending, In-Process, Solved, Archived)

### **User Management**
- ✅ 5 custom roles: B2C Agent, B2B Agent, Shift Leader, Team Leader, Maintenance Team
- ✅ Granular capability management
- ✅ Organization-based access control

### **Design**
- ✅ Modern, clean UI with blue/white color scheme
- ✅ Responsive and mobile-friendly
- ✅ Matches reference UI designs pixel-perfectly
- ✅ AJAX-driven for smooth interactions

## 📦 Installation

1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/` directory
3. Extract the archive
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Navigate to **Prospectra Ticketing** in the WordPress admin menu

## 🎯 Quick Start

### **Creating Your First Ticket**

1. Go to **Prospectra Ticketing** → **Tickets** → **Add New**
2. Or use the shortcode `[pts_create_ticket]` on any page
3. Fill in the ticket details:
   - **Title**: Brief description of the issue
   - **Description**: Detailed explanation (minimum 20 characters)
   - **Category**: Select from predefined categories
   - **Priority**: Low, Medium, High, or Emergency
   - **Attachments**: Upload up to 5 files (2MB max each)
4. Click **Create Ticket**

### **Creating a Shift Handover Report**

1. Go to **Shift Overview** dashboard
2. Click **+ Create Report**
3. Fill in shift details (shift leader, shift type, date/time)
4. Add production plan data
5. Document follow-up tasks and issues
6. Add key notes for the next shift
7.  Optionally share via email (PDF/Excel)
8. Click **Create Report**

### **Using Shortcodes**

Add these shortcodes to any page or post:

```
[pts_shift_overview]          # Shift Overview Dashboard
[pts_ticket_details id="123"] # Specific Ticket Details
[pts_create_ticket]           # Create Ticket Modal
[pts_report_history]          # Shift Report History
[pts_create_handover]         # Create Handover Report
[pts_search]                  # Global Search Interface
```

## ⚙️ Configuration

### **General Settings**

Navigate to **Prospectra Ticketing** → **Settings** → **General**

- Enable/disable features (Tickets, Shift Reports, Shift Overview)
- Set timezone and date/time formats

### **Ticket Settings**

**Settings** → **Tickets**

- Configure custom statuses with colors
- Define priority levels
- Set attachment limits and allowed file types
- Configure default assignee rules

### **Shift Report Settings**

**Settings** → **Shift Reports**

- Define shift types with time ranges:
  - Morning: 08:00–14:00
  - Afternoon: 14:00–20:00
  - Evening: 20:00–02:00
  - Night: 02:00–08:00
- Toggle which sections appear in reports
- Set default template text

### **Email Integration**

**Settings** → **Email** (requires setup)

#### Inbound (IMAP/POP3):
```
Host: imap.example.com
Port: 993
Username: tickets@yourcompany.com
Password: ********
SSL/TLS: Enabled
```

#### Outbound (SMTP):
```
Host: smtp. example.com
Port: 587
Username: tickets@yourcompany.com
Password: ********
Security: TLS
```

**Auto-Assignment Rules:**
- Configure keywords to automatically assign tickets
- Example: "safety" → Assign to Safety Team

### **Notifications**

**Settings** → **Notifications**

Toggle email notifications for:
- New ticket created
- Status changed
- Ticket assigned
- New comment added
- New shift report available

Customize email templates with placeholders:
- `{ticket_id}` - Ticket ID number
- `{ticket_title}` - Ticket title
- `{status}` - Current status
- `{user_name}` - User's name
- `{comment_text}` - Comment content

### **Export Settings**

**Settings** → **Exports**

- Upload company logo (appears on PDF exports)
- Set footer text for exports
- Choose default export format (PDF or Excel)

## 👥 User Roles & Capabilities

### **B2C Agent**
- View tickets
- Create tickets
- Add comments
- View assigned tickets

### **B2B Agent**
- All B2C Agent capabilities
- Assign tickets
- Edit tickets

### **Shift Leader**
- All B2B Agent capabilities
- Create shift reports
- Export shift reports
- View all tickets in organization

### **Team Leader**
- All Shift Leader capabilities
- Delete tickets and reports
- Manage orders
- Access advanced reports

### **Maintenance Team**
- View tickets (filtered by category)
- Add comments on technical tickets
- View orders

### **Administrator**
- Full access to all features
- Manage settings
- Manage user roles and capabilities

## 🗄️ Database Structure

### **Custom Tables**

#### `wp_pts_comments`
Stores ticket comments and system events
```sql
id, ticket_id, user_id, content, created_at, updated_at, is_system_event, parent_id
```

#### `wp_pts_metrics_cache`
Caches calculated metrics for performance
```sql
id, user_id, metric_type, value, calculated_at
```

### **Post Meta Keys**

#### Tickets (`pts_ticket`)
- `_pts_ticket_status`
- `_pts_ticket_priority`
- `_pts_ticket_category`
- `_pts_ticket_assignee`
- `_pts_ticket_email_id`
- `_pts_ticket_due_date`
- `_pts_ticket_attachments`
- `_pts_email_message_id`

#### Shift Reports (`pts_shift_report`)
- `_pts_shift_leader`
- `_pts_shift_type`
- `_pts_shift_date`
- `_pts_production_plan` (JSON)
- `_pts_upcoming_production` (JSON)
- `_pts_followup_tasks` (JSON)
- `_pts_issues_summary` (JSON)
- `_pts_key_notes`

#### Orders (`pts_order`)
- `_pts_order_job_id`
- `_pts_order_customer`
- `_pts_order_start_time`
- `_pts_order_end_time`
- `_pts_order_produced`
- `_pts_order_planned`
- `_pts_order_machine`
- `_pts_order_priority`

## 🔒 Security

The plugin follows WordPress security best practices:

- ✅ Nonce verification on all AJAX requests
- ✅ Capability checks before any action
- ✅ Input sanitization (`sanitize_text_field`, `sanitize_email`, etc.)
- ✅ Output escaping (`esc_html`, `esc_url`, `esc_attr`)
- ✅ SQL injection prevention (`$wpdb->prepare()`)
- ✅ File upload validation (MIME type, size, extension)
- ✅ CSRF protection
- ✅ XSS prevention

## 📊 Metrics & Analytics

The plugin tracks the following metrics in real-time:

- **Production Volume**: Total units produced
- **Issues Reported**: Number of tickets created
- **Runtime**: System uptime percentage
- **Efficiency**: (Produced / Planned) × 100
- **Open Tickets**: Count by status
- **Tickets Per User**: Assignment distribution
- **Average Comments Per Ticket**: Engagement metric
- **Ticket Completion Rate**: Resolved vs. total

Metrics are cached for 5 minutes for performance. 

## 🛠️ Troubleshooting

### Email Integration Not Working

1.  Verify IMAP/SMTP credentials in Settings
2. Check firewall/port access (993 for IMAP, 587 for SMTP)
3. Enable WordPress debug mode to see errors:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
4. Check `/wp-content/debug.log` for errors

### Tickets Not Creating from Emails

1. Verify email credentials are correct
2. Check cron jobs are running: `wp cron test`
3. Manually trigger email fetch: `wp pts fetch_emails` (if WP-CLI is installed)
4. Check email inbox for unread messages

### File Uploads Failing

1. Check PHP `upload_max_filesize` and `post_max_size` settings
2. Verify WordPress `wp-content/uploads` folder is writable
3. Check plugin settings for file size limits (Settings → Tickets)
4. Ensure MIME type is allowed (JPG, PNG, PDF by default)

### Permissions Issues

1. Go to Settings → Permissions
2. Verify role capabilities are correctly assigned
3. Log out and log back in to refresh capabilities
4. Check user's role in Users → All Users

## 🔄 Updates & Maintenance

### Manual Update

1.  Deactivate the plugin
2. Delete old plugin folder
3. Upload new version
4. Reactivate the plugin
5. Check Settings page for any new options

### Database Updates

The plugin automatically runs database migrations on activation.  If you experience issues after an update:

1.  Deactivate the plugin
2.  Reactivate the plugin
3. This will re-run the activation hook and update database schema

## 🤝 Support & Contributing

### Getting Help

- **Documentation**: Full documentation in this README
- **GitHub Issues**: https://github.com/ivovalerieviliev/prospectra-ticketing-system/issues
- **Email Support**: Contact your system administrator

### Contributing

Contributions are welcome! Please:

1. Fork the repository
2.  Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4.  Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and release notes.

## 📄 License

This plugin is licensed under the GPL v2 or later. 

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details. 
```

## 👨‍💻 Author

**ivovalerieviliev**
- GitHub: [@ivovalerieviliev](https://github.com/ivovalerieviliev)

## 🙏 Credits

- WordPress Core Team
- TCPDF Library for PDF generation
- PHPMailer for email handling
- All contributors and testers

---

**Version:** 1.0.0  
**Last Updated:** 2025-12-03  
**Requires:** WordPress 6.4+, PHP 8.0+
