<?php
/**
 * Mail configuration — nasa LABAS ng public_html para hindi accessible via browser.
 * Path: /home/enrollment.bcp-sms.com/config/mail.php
 *
 * Paalala: Pagkatapos gumawa ng bagong Gmail App Password,
 * i-paste dito ang 16-character na password (walang spaces).
 */

return [
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,               // 587 = STARTTLS, 465 = SMTPS
    'smtp_secure'   => 'tls',             // 'tls' para 587, 'ssl' para 465
    'smtp_username' => 'villanuevabea034@gmail.com',
    'smtp_password' => 'upegpjngpqbptthk',  // 16 chars, walang spaces
    'from_email'    => 'villanuevabea034@gmail.com',
    'from_name'     => 'Bestlink College Enrollment System',
    'portal_url'    => 'https://www.bestlink.edu.ph/',
    'debug'         => false,             // true lang kapag nag-de-debug
];