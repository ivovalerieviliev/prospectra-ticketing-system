<? php
/**
 * Email Connector - IMAP/SMTP Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTS_Email_Connector {
    
    /**
     * Test IMAP connection
     */
    public static function test_imap_connection($host, $port, $username, $password, $ssl = true) {
        $connection_string = sprintf(
            '{%s:%d/imap%s}INBOX',
            $host,
            $port,
            $ssl ? '/ssl' : ''
        );
        
        $mailbox = @imap_open($connection_string, $username, $password);
        
        if (!$mailbox) {
            return new WP_Error('connection_failed', imap_last_error());
        }
        
        imap_close($mailbox);
        return true;
    }
    
    /**
     * Fetch emails from mailbox
     */
    public static function fetch_emails($limit = 10) {
        $settings = get_option('pts_settings', array());
        $email_settings = isset($settings['email']) ? $settings['email'] : array();
        
        if (empty($email_settings['imap_host'])) {
            return new WP_Error('no_config', __('Email settings not configured.', 'prospectra-ticketing-system'));
        }
        
        $connection_string = sprintf(
            '{%s:%d/imap%s}INBOX',
            $email_settings['imap_host'],
            $email_settings['imap_port'],
            ! empty($email_settings['imap_ssl']) ? '/ssl' : ''
        );
        
        $mailbox = @imap_open(
            $connection_string,
            $email_settings['imap_username'],
            $email_settings['imap_password']
        );
        
        if (! $mailbox) {
            return new WP_Error('connection_failed', imap_last_error());
        }
        
        $emails = imap_search($mailbox, 'UNSEEN', SE_UID);
        
        if (! $emails) {
            imap_close($mailbox);
            return array();
        }
        
        $emails = array_slice($emails, 0, $limit);
        $messages = array();
        
        foreach ($emails as $email_uid) {
            $message = self::parse_email($mailbox, $email_uid);
            if ($message) {
                $messages[] = $message;
                
                // Mark as seen
                imap_setflag_full($mailbox, $email_uid, '\\Seen', ST_UID);
            }
        }
        
        imap_close($mailbox);
        return $messages;
    }
    
    /**
     * Parse email message
     */
    private static function parse_email($mailbox, $email_uid) {
        $header = imap_headerinfo($mailbox, imap_msgno($mailbox, $email_uid));
        $structure = imap_fetchstructure($mailbox, $email_uid, FT_UID);
        
        $from = isset($header->from[0]) ? $header->from[0] : null;
        $from_email = $from ? $from->mailbox .  '@' . $from->host : '';
        $from_name = $from && isset($from->personal) ? $from->personal : $from_email;
        
        $subject = isset($header->subject) ? imap_utf8($header->subject) : __('No Subject', 'prospectra-ticketing-system');
        $message_id = isset($header->message_id) ? $header->message_id : '';
        $in_reply_to = isset($header->in_reply_to) ?  $header->in_reply_to : '';
        
        $body = self::get_email_body($mailbox, $email_uid, $structure);
        $attachments = self::get_email_attachments($mailbox, $email_uid, $structure);
        
        return array(
            'from_email' => $from_email,
            'from_name' => $from_name,
            'subject' => $subject,
            'body' => $body,
            'message_id' => $message_id,
            'in_reply_to' => $in_reply_to,
            'attachments' => $attachments,
            'uid' => $email_uid,
        );
    }
    
    /**
     * Get email body
     */
    private static function get_email_body($mailbox, $email_uid, $structure) {
        $body = '';
        
        // Try to get HTML part first
        if (isset($structure->parts)) {
            foreach ($structure->parts as $part_num => $part) {
                if ($part->subtype === 'HTML') {
                    $body = imap_fetchbody($mailbox, $email_uid, $part_num + 1, FT_UID);
                    
                    if ($part->encoding == 3) { // Base64
                        $body = base64_decode($body);
                    } elseif ($part->encoding == 4) { // Quoted-printable
                        $body = quoted_printable_decode($body);
                    }
                    
                    break;
                }
            }
        }
        
        // Fallback to plain text
        if (empty($body)) {
            $body = imap_body($mailbox, $email_uid, FT_UID);
        }
        
        return $body;
    }
    
    /**
     * Get email attachments
     */
    private static function get_email_attachments($mailbox, $email_uid, $structure) {
        $attachments = array();
        
        if (! isset($structure->parts)) {
            return $attachments;
        }
        
        foreach ($structure->parts as $part_num => $part) {
            if (isset($part->disposition) && strtolower($part->disposition) === 'attachment') {
                $filename = 'unknown';
                
                if (isset($part->dparameters)) {
                    foreach ($part->dparameters as $param) {
                        if (strtolower($param->attribute) === 'filename') {
                            $filename = $param->value;
                            break;
                        }
                    }
                }
                
                $data = imap_fetchbody($mailbox, $email_uid, $part_num + 1, FT_UID);
                
                if ($part->encoding == 3) { // Base64
                    $data = base64_decode($data);
                } elseif ($part->encoding == 4) { // Quoted-printable
                    $data = quoted_printable_decode($data);
                }
                
                $attachments[] = array(
                    'filename' => $filename,
                    'data' => $data,
                    'mime' => $part->subtype,
                );
            }
        }
        
        return $attachments;
    }
    
    /**
     * Send email via SMTP
     */
    public static function send_email($to, $subject, $body, $attachments = array()) {
        $settings = get_option('pts_settings', array());
        $email_settings = isset($settings['email']) ? $settings['email'] : array();
        
        // Configure PHPMailer (WordPress uses this internally)
        add_action('phpmailer_init', function($phpmailer) use ($email_settings) {
            if (!empty($email_settings['smtp_host'])) {
                $phpmailer->isSMTP();
                $phpmailer->Host = $email_settings['smtp_host'];
                $phpmailer->Port = $email_settings['smtp_port'];
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $email_settings['smtp_username'];
                $phpmailer->Password = $email_settings['smtp_password'];
                $phpmailer->SMTPSecure = ! empty($email_settings['smtp_ssl']) ? 'ssl' : 'tls';
            }
        });
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $sent = wp_mail($to, $subject, $body, $headers, $attachments);
        
        return $sent;
    }
}
