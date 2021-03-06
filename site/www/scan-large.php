<?php
   /**
    * Display page for a single scan with a given ID.
    */

    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'../lib');
    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'/usr/home/migurski/pear/lib');
    require_once 'init.php';
    require_once 'data.php';
    
    $scan_id = $_GET['id'] ? $_GET['id'] : null;
    list($user_id, $language) = read_userdata($_COOKIE['visitor'], $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    
    enforce_master_on_off_switch($language);

    /**** ... ****/
    
    $dbh =& get_db_connection();
    
    if($user_id)
        $user = get_user($dbh, $user_id);

    if($user)
        setcookie('visitor', write_userdata($user['id'], $language), time() + 86400 * 31);
    
    $scan = get_scan($dbh, $scan_id);
    
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if($scan)
        {
            $scan = array('id' => $scan_id,
                          'print_id' => $_POST['print_id'],
                          'last_step' => $_POST['last_step'],
                          'user_name' => $_POST['user_name'],
                          'min_row' => $_POST['min_row'],
                          'min_column' => $_POST['min_column'],
                          'min_zoom' => $_POST['min_zoom'],
                          'max_row' => $_POST['max_row'],
                          'max_column' => $_POST['max_column'],
                          'max_zoom' => $_POST['max_zoom'],
                          'description' => $_POST['description'],
                          'is_private' => $_POST['is_private'],
                          'will_edit' => $_POST['will_edit']);
            
            $dbh->query('START TRANSACTION');
            $scan = set_scan($dbh, $scan);
            $dbh->query('COMMIT');
        }
    }
    
    if($scan)
    {
        $print = get_print($dbh, $scan['print_id']);
        $notes = get_scan_notes($dbh, array('page' => 1, 'perpage' => 242), $scan['id']);
    }

    $sm = get_smarty_instance();
    $sm->assign('scan', $scan);
    $sm->assign('step', $step);
    $sm->assign('print', $print);
    $sm->assign('notes', $notes);
    $sm->assign('language', $language);
    
    scan_headers($scan);
    print_headers($print);
    
    header("Content-Type: text/html; charset=UTF-8");
    print $sm->fetch("scan-large.html.tpl");

?>
