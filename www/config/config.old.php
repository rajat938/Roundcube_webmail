<?php
    $config['plugins'] = [
	'archive',
	'zipdownload',
        'newmail_notifier',
	'ident_switch',
];
    $config['log_driver'] = 'stdout';
    $config['zipdownload_selection'] = true;
    $config['des_key'] = '8ao8DCVihkcgjei2RuhDlVjf';
    $config['enable_spellcheck'] = true;
    $config['spellcheck_engine'] = 'pspell';
    include(__DIR__ . '/config.docker.inc.php');
    
$config['imap_cache'] = 'db';
$config['messages_cache'] = true;
$config['imap_cache_ttl'] = '10d';
$config['messages_cache_ttl'] = '10d';
$config['prefer_html'] = true;
$config['htmleditor'] = 1;
$config['ident_switch.check_mail'] = false;
$config['ident_switch.notify_check'] = 0;
$config['refresh_interval'] = 300;
$config['show_images'] = 0;
$config['default_host'] = 'dovecot';


// Force SSL context bypass globally for stream sockets (ident_switch fix)
$config['smtp_conn_options'] = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ],
];

$config['imap_conn_options'] = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ],
];



$config['show_images'] = 2;

// ---------------------------------------------------------------------
// ident_switch: preconfigured server settings for mirrored accounts.
// Without this, every identity you add falls back to Roundcube defaults
// instead of routing IMAP -> local dovecot mirror and SMTP -> Hostinger.
// '*' matches every domain since all mirrored accounts share the same
// IMAP/SMTP infrastructure.
// ---------------------------------------------------------------------
$config['ident_switch.preconfig'] = [
    '*' => [
        // dovecot has ssl = no (no STARTTLS), so no scheme prefix here.
        'imap_host' => 'dovecot:143',
        // Hostinger SMTP requires implicit SSL on 465.
        'smtp_host' => 'ssl://smtp.hostinger.com:465',
        // Login username is the full email address (matches dovecot_users.email).
        'user' => 'email',
        // Let users see/edit these per identity instead of locking them.
        'readonly' => false,
    ],
];
