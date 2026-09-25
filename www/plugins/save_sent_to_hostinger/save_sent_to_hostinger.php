<?php
/**
 * save_sent_to_hostinger
 *
 * Roundcube sends mail through Hostinger's SMTP directly (see smtp_host
 * in config.inc.php), but its own "save sent copy" always APPENDs to
 * whatever IMAP host it's logged into -- the LOCAL Dovecot mirror
 * (imap_host = 'dovecot'), never Hostinger. There's no built-in setting
 * to point just that APPEND at a different server.
 *
 * This plugin does the save directly and deterministically instead:
 * right after a message is actually sent (message_sent hook), it opens
 * its own short-lived IMAP connection straight to Hostinger and appends
 * the exact same raw message into the account's real INBOX.Sent there.
 * The sync-daemon's existing periodic pull (housekeeper -> run_sync,
 * every FULL_SYNC_INTERVAL) then brings that copy down into the local
 * mirror like any other Hostinger message.
 *
 * Requires $config['no_save_sent_messages'] = true; in config.inc.php,
 * so Roundcube doesn't ALSO save a redundant local copy.
 */
class save_sent_to_hostinger extends rcube_plugin
{
    public $task = 'mail';

    function init()
    {
        $this->add_hook('message_sent', array($this, 'append_to_hostinger'));
    }

    function append_to_hostinger($args)
    {
        $rcmail = rcmail::get_instance();
        $email  = $rcmail->get_user_name(); // the logged-in mailbox's email address

        $creds = $this->hostinger_creds($email);
        if (!$creds) {
            rcube::write_log('save_sent_to_hostinger',
                "no active mirror_accounts row for $email -- not saved to Hostinger");
            return $args;
        }

        $body = $args['body'] ?? null;
        if (!is_string($body) || $body === '') {
            rcube::write_log('save_sent_to_hostinger', "empty message body for $email -- skipping");
            return $args;
        }

        $imap = new rcube_imap_generic();
        $connected = $imap->connect($creds['imap_host'], $email, $creds['hostinger_password'], array(
            'port'     => (int) $creds['imap_port'],
            'ssl_mode' => 'ssl',
            'timeout'  => 15,
        ));

        if (!$connected) {
            rcube::write_log('save_sent_to_hostinger',
                "IMAP connect to Hostinger failed for $email: " . $imap->error);
            return $args;
        }

        // Additive only -- create the folder if it's somehow missing, never
        // delete/rename anything. Safe to call even when it already exists.
        if (!$imap->select('INBOX.Sent')) {
            $imap->createFolder('INBOX.Sent');
        }

        $ok = $imap->append('INBOX.Sent', $body, array('\\Seen'));
        if (!$ok) {
            rcube::write_log('save_sent_to_hostinger',
                "APPEND to Hostinger INBOX.Sent failed for $email: " . $imap->error);
        }

        $imap->closeConnection();
        return $args;
    }

    private function hostinger_creds($email)
    {
        $db = rcmail::get_instance()->get_dbh();
        $result = $db->query(
            "SELECT hostinger_password, imap_host, imap_port FROM mirror_accounts WHERE email = ? AND active = 1",
            $email
        );
        $row = $db->fetch_assoc($result);
        return $row ?: null;
    }
}