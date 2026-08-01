<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = Database::getInstance();

$q = $_POST['q'] ?? '';

if ($q === 'massup') {
    $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'marquee'");
    $marquee = $stmt->fetchColumn();
    if ($marquee) {
        echo $marquee;
    } else {
        echo "Disclaimer: viewing this website is on your own risk.All the information here is based on numeric astrology n is not related to any type of gambling . We warn you that gambling in our country may be banned or illegal .. We are not responsible for any issue or scam.. We respect all country rules/laws..if you not agree with our site disclaimer..please quit our site right now . ";
    }
}
