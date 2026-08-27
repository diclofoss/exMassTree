<?php

if (!isset($_POST['domain'])) {
    print json_encode(array("result" => "internalerror"));
    return;
}
if (!isset($_POST['text'])) {
    print json_encode(array("result" => "internalerror"));
    return;
}

$TDomain = $application->compDriver($element->domainSource);
$domain = $TDomain->getByFields((array("domain" => $_POST['domain'])));
$TDomain_integration = $application->compDriver($element->domainSource . "_integration");

$_POST['text'] = str_replace(" ", "%", $_POST['text']);
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
    $dbQuery = $pdo->query("SELECT u.name as author, b.entity_id, b.body_value, nf.title, nf.created, nf.changed FROM {$databases[\'default\'][\'default\'][\'prefix\']}node__body b LEFT JOIN {$databases[\'default\'][\'default\'][\'prefix\']}node_field_data nf on nf.nid = b.entity_id LEFT JOIN {$databases[\'default\'][\'default\'][\'prefix\']}users_field_data u ON u.uid = nf.uid WHERE b.body_value LIKE \'%' . $_POST['text'] . '%\'");
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $rows = $dbQuery->fetchAll(PDO::FETCH_CLASS);
    if (!$rows) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }
    foreach ($rows as $row) {
        $row->publicDate = date("Y-m-d H:i:s", $row->created);
        $row->editDate = date("Y-m-d H:i:s", $row->changed);
    }
    print json_encode(array("result" => "success", "additionalData" => "", "data" => $rows));
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
    $query->select("*")
            ->from($db->qn("#__content"))
            ->where("`introtext` LIKE \'%' . $_POST['text'] . '%\' OR `fulltext` LIKE \'%' . $_POST['text'] . '%\'");
    $db->setQuery($query);
    if (!$db) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
    $articles = $db->loadObjectList();
    if ($articles) {
        foreach($articles as $article) {
            $article->publicDate = $article->created;
            $article->editDate = $article->modified;
            $query = $db->getQuery(true);
            $query->select("*")
                ->from($db->qn("#__users"))
                ->where("id = " . $article->created_by);
            $db->setQuery($query);
            $user = $db->loadObject();
            $article->author = $user->username;
        }
        print json_encode(array("result" => "success", "additionalData" => "", "data" => $articles));
        exit();
    } else {
        $query = $db->getQuery(true);
        $query->select("*")->from($db->qn("#__k2_items"))->where("`introtext` LIKE \'%' . $_POST['text'] . '%\' OR `fulltext` LIKE \'%' . $_POST['text'] . '%\'");
        $db->setQuery($query);
        if (!$db) {
            print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
            exit();
        }
        try {
            $articles = $db->loadObjectList();
        } catch(Exception $e) {
            print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
            exit();
        }
        if (!$articles) {
            print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
            exit();
        }
        foreach($articles as $article) {
            $article->publicDate = $article->created;
            $article->editDate = $article->modified;
            $query = $db->getQuery(true);
            $query->select("*")
                ->from($db->qn("#__users"))
                ->where("id = " . $article->created_by);
            $db->setQuery($query);
            $user = $db->loadObject();
            $article->author = $user->username;
        }
        print json_encode(array("result" => "success", "additionalData" => "k2", "data" => $articles));
        exit();
    }
    ';
} else if ($domain->type == 0) {
    // Wordpress
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
    $sql = $wpdb->prepare("SELECT u.user_login, p.ID, post_title, post_content, post_modified, post_date, post_name FROM $wpdb->posts p, $wpdb->users u WHERE p.post_author = u.ID AND post_status = \'publish\' AND (post_title LIKE \'%' . $_POST['text'] . '%\' OR post_content LIKE \'%' . $_POST['text'] . '%\')", null);
    if (!$sql) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();
    }
    $results = $wpdb->get_results($sql);
    if (!$results) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();    
    }

    foreach($results as $article) {
        $article->publicDate = $article->post_date;
        $article->editDate = $article->post_modified;
    }
    
    print json_encode(array("result" => "success", "additionalData" => "", "data" => $results));
    exit();
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
if ($obj->result == "empty") {
    $TDomain->updateFields($domain->id, array("datetime" => "NOW()", "checkStatus" => "3"));
    $TDomain_integration->createByFields(array("parent_id" => $domain->id, "status" => "3", "datetime" => "NOW()"));
    print json_encode(array("result" => $obj->result));
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
    //J
    foreach ($obj->data as $o) {
        $o->pretext = $o->introtext;
        $o->text = $o->fulltext;
        $o->entity_id = $o->id;
        $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/" . $o->catid . "/" . $o->id;
        if ($domain->additionalData == "k2") {
            $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/component/k2/item/" . $o->id;
        } else {
            $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/" . $o->catid . "/" . $o->id;
        }
    }
} else if ($domain->type == 0) {
    //WP
    foreach ($obj->data as $o) {
        $o->title = $o->post_title;
        $o->pretext = "";
        $o->text = $o->post_content;
        $o->entity_id = $o->ID;
        $o->author = $o->user_login;
        $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . date("/Y/m/d/", strtotime($o->post_date)) . $o->post_name;
    }
} else if ($domain->type == 2) {
    //D
    foreach ($obj->data as $o) {
        $o->pretext = "";
        $o->text = $o->body_value;
        $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/node/" . $o->entity_id;
    }
}

foreach ($obj->data as $o) {
    $offset = mb_strpos($o->text, $_POST['text']);
    if ($offset < 20) {
        $o->findText = mb_strcut($o->text, 0, strlen($_POST['text']) + 20);
    } else {
        $o->findText = mb_strcut($o->text, $offset - 20, (strlen($_POST['text']) + 40));
    }
    if (!$o->findText) {
        $o->findText = $o->title;
    }
    $o->findText = str_replace($_POST['text'], "<strong>" . $_POST['text'] . "</strong>", $o->findText);
}

// Common part
foreach ($obj->data as $o) {
    $post = $TPost->getByFields(array("foreinId" => $o->entity_id, "additionalData" => $obj->additionalData, "domain_id" => $domain->id));
    if (!$post) {
        $id = $TPost->createByFields(array(
            "title" => $o->title,
            "pretext" => $o->pretext,
            "text" => $o->text,
            "foreinId" => $o->entity_id,
            "domain_id" => $domain->id,
            "additionalData" => $obj->additionalData,
            "editDate" => $o->editDate,
            "publicDate" => $o->publicDate,
            "url" => $o->extUrl
        ));
        $o->url = $application->dirName . "/?category={$_GET['category']}&component={$element->postCacheElement}&element={$element->postCache}&id=$id&action=edit";
    } else {
        $o->url = $application->dirName . "/?category={$_GET['category']}&component={$element->postCacheElement}&element={$element->postCache}&id={$post->id}&action=edit";
        $TPost->updateFields($post->id, array(
            "title" => $o->title,
            "pretext" => $o->pretext,
            "text" => $o->text,
            "foreinId" => $o->entity_id,
            "additionalData" => $obj->additionalData,
            "editDate" => $o->editDate,
            "publicDate" => $o->publicDate,
            "url" => $o->extUrl
        ));
    }
}
print json_encode(array("result" => "success", "data" => $obj->data));
