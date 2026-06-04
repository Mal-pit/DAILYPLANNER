<?php

session_start();

define('DATA_DIR', __DIR__ . '/../data/');

function data_dir_init() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
}

function users_file() {
    data_dir_init();
    return DATA_DIR . 'users.json';
}

function load_users() {
    $file = users_file();
    if (!file_exists($file)) {
        // Default users seeded on first run
        $defaults = [
            'admin' => [
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'name'     => 'Admin User',
                'role'     => 'Administrator',
                'email'    => 'admin@planner.com',
            ],
        ];
        save_users($defaults);
        return $defaults;
    }
    return json_decode(file_get_contents($file), true) ?? [];
}

function save_users($users) {
    file_put_contents(users_file(), json_encode($users, JSON_PRETTY_PRINT));
}

function verify_login($username, $password) {
    $users = load_users();
    if (!isset($users[$username])) return false;
    $hash = $users[$username]['password'];
    // Support both bcrypt hashes and legacy plain text (for demo credentials file)
    if (password_verify($password, $hash)) return $users[$username];
    // Fallback: plain text match (backward compat)
    if ($hash === $password) return $users[$username];
    return false;
}

function register_user($username, $password, $name, $email) {
    $users = load_users();
    if (isset($users[$username])) return false; // already taken
    $users[$username] = [
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'name'     => $name,
        'role'     => 'Member',
        'email'    => $email,
    ];
    save_users($users);
    return true;
}

function username_exists($username) {
    $users = load_users();
    return isset($users[$username]);
}

function require_auth() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }
}

function get_user() {
    return $_SESSION['user'] ?? null;
}

function user_data_file($username) {
    data_dir_init();
    $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '', $username);
    return DATA_DIR . 'user_' . $safe . '.json';
}

function load_user_data($username) {
    $file = user_data_file($username);
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function save_user_data($username, $data) {
    file_put_contents(user_data_file($username), json_encode($data, JSON_PRETTY_PRINT));
}

function default_tasks() {
    return [
        ['id'=>1,  'title'=>'Design landing page mockup',   'category'=>'Work',     'priority'=>'High',   'due'=>'2025-07-05','status'=>'In Progress','progress'=>65, 'notes'=>''],
        ['id'=>2,  'title'=>'Review project proposal',       'category'=>'Work',     'priority'=>'High',   'due'=>'2025-07-04','status'=>'Pending',    'progress'=>0,  'notes'=>''],
        ['id'=>3,  'title'=>'Morning jog – 5 km',            'category'=>'Health',   'priority'=>'Medium', 'due'=>'2025-07-04','status'=>'Done',       'progress'=>100,'notes'=>''],
        ['id'=>4,  'title'=>'Read "Atomic Habits" ch. 6',    'category'=>'Personal', 'priority'=>'Low',    'due'=>'2025-07-06','status'=>'Pending',    'progress'=>0,  'notes'=>''],
        ['id'=>5,  'title'=>'Weekly grocery shopping',       'category'=>'Personal', 'priority'=>'Medium', 'due'=>'2025-07-05','status'=>'Pending',    'progress'=>0,  'notes'=>''],
        ['id'=>6,  'title'=>'Prepare internship report',     'category'=>'Work',     'priority'=>'High',   'due'=>'2025-07-07','status'=>'In Progress','progress'=>40, 'notes'=>''],
        ['id'=>7,  'title'=>'Call dentist – reschedule',     'category'=>'Health',   'priority'=>'Low',    'due'=>'2025-07-08','status'=>'Pending',    'progress'=>0,  'notes'=>''],
        ['id'=>8,  'title'=>'Update portfolio website',      'category'=>'Work',     'priority'=>'Medium', 'due'=>'2025-07-09','status'=>'In Progress','progress'=>30, 'notes'=>''],
        ['id'=>9,  'title'=>'Meditate – 15 minutes',         'category'=>'Health',   'priority'=>'Low',    'due'=>'2025-07-04','status'=>'Done',       'progress'=>100,'notes'=>''],
        ['id'=>10, 'title'=>'Send invoice to client',        'category'=>'Work',     'priority'=>'High',   'due'=>'2025-07-04','status'=>'Done',       'progress'=>100,'notes'=>''],
    ];
}

function default_schedule() {
    return [
        ['id'=>1,'time'=>'07:00','title'=>'Morning Jog',            'type'=>'green','duration'=>'45 min',   'location'=>'City Park'],
        ['id'=>2,'time'=>'09:00','title'=>'Team Standup Meeting',   'type'=>'blue', 'duration'=>'30 min',   'location'=>'Zoom Call'],
        ['id'=>3,'time'=>'10:00','title'=>'Work on IMS566 Project', 'type'=>'amber','duration'=>'2 hours',  'location'=>'Home Office'],
        ['id'=>4,'time'=>'12:30','title'=>'Lunch Break',            'type'=>'green','duration'=>'1 hour',   'location'=>'Canteen'],
        ['id'=>5,'time'=>'14:00','title'=>'Review Project Proposal','type'=>'amber','duration'=>'1.5 hours','location'=>'Library'],
        ['id'=>6,'time'=>'16:00','title'=>'Grocery Shopping',       'type'=>'blue', 'duration'=>'1 hour',   'location'=>'Aeon Mall'],
        ['id'=>7,'time'=>'19:00','title'=>'Evening Meditation',     'type'=>'green','duration'=>'15 min',   'location'=>'Bedroom'],
        ['id'=>8,'time'=>'20:00','title'=>'Read Atomic Habits',     'type'=>'amber','duration'=>'45 min',   'location'=>'Living Room'],
    ];
}

function default_habits() {
    return [
        ['id'=>1,'name'=>'Morning Exercise',    'icon'=>'🏃','streak'=>12,'target'=>30,'completed'=>[1,2,3,4,5,6,7,8,9,10,11,12],'category'=>'Health'],
        ['id'=>2,'name'=>'Read 20 Pages',        'icon'=>'📖','streak'=>7, 'target'=>30,'completed'=>[1,2,3,4,5,6,7],             'category'=>'Personal'],
        ['id'=>3,'name'=>'Drink 8 Glasses',      'icon'=>'💧','streak'=>20,'target'=>30,'completed'=>[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20],'category'=>'Health'],
        ['id'=>4,'name'=>'Study 2 Hours',         'icon'=>'💻','streak'=>5, 'target'=>30,'completed'=>[1,2,3,4,5],                 'category'=>'Work'],
        ['id'=>5,'name'=>'No Social Media 9pm+',  'icon'=>'📵','streak'=>3, 'target'=>30,'completed'=>[1,2,3],                     'category'=>'Personal'],
        ['id'=>6,'name'=>'Daily Journaling',      'icon'=>'📝','streak'=>9, 'target'=>30,'completed'=>[1,2,3,4,5,6,7,8,9],        'category'=>'Personal'],
    ];
}

function init_user_data() {
    $username = $_SESSION['user']['username'] ?? null;
    if (!$username) return;

    // Only load from file if not yet in session this request
    if (!isset($_SESSION['_data_loaded'])) {
        $stored = load_user_data($username);
        if ($stored) {
            if (isset($stored['tasks']))    $_SESSION['tasks']    = $stored['tasks'];
            if (isset($stored['schedule'])) $_SESSION['schedule'] = $stored['schedule'];
            if (isset($stored['habits']))   $_SESSION['habits']   = $stored['habits'];
        }
        $_SESSION['_data_loaded'] = true;
    }
}

function persist_user_data() {
    $username = $_SESSION['user']['username'] ?? null;
    if (!$username) return;
    save_user_data($username, [
        'tasks'    => $_SESSION['tasks']    ?? default_tasks(),
        'schedule' => $_SESSION['schedule'] ?? default_schedule(),
        'habits'   => $_SESSION['habits']   ?? default_habits(),
    ]);
}

function init_tasks() {
    init_user_data();
    if (!isset($_SESSION['tasks'])) {
        $_SESSION['tasks'] = default_tasks();
    }
}
function get_tasks()          { init_tasks(); return $_SESSION['tasks']; }
function add_task($data) {
    init_tasks();
    $max_id = 0;
    foreach ($_SESSION['tasks'] as $t) { if ($t['id'] > $max_id) $max_id = $t['id']; }
    $_SESSION['tasks'][] = [
        'id'       => $max_id + 1,
        'title'    => trim($data['title']),
        'category' => $data['category'],
        'priority' => $data['priority'],
        'due'      => $data['due'],
        'status'   => $data['status'] ?? 'Pending',
        'progress' => (int)($data['progress'] ?? 0),
        'notes'    => trim($data['notes'] ?? ''),
    ];
    persist_user_data();
}
function update_task($id, $data) {
    init_tasks();
    foreach ($_SESSION['tasks'] as &$t) {
        if ($t['id'] == $id) {
            $t['title']    = trim($data['title']);
            $t['category'] = $data['category'];
            $t['priority'] = $data['priority'];
            $t['due']      = $data['due'];
            $t['status']   = $data['status'];
            $t['progress'] = (int)$data['progress'];
            $t['notes']    = trim($data['notes'] ?? '');
            break;
        }
    }
    persist_user_data();
}
function delete_task($id) {
    init_tasks();
    $_SESSION['tasks'] = array_values(array_filter($_SESSION['tasks'], fn($t) => $t['id'] != $id));
    persist_user_data();
}
function find_task($id) {
    foreach (get_tasks() as $t) { if ($t['id'] == $id) return $t; }
    return null;
}

function init_schedule() {
    init_user_data();
    if (!isset($_SESSION['schedule'])) $_SESSION['schedule'] = default_schedule();
}
function get_schedule() { init_schedule(); return $_SESSION['schedule']; }
function add_event($data) {
    init_schedule();
    $max_id = 0;
    foreach ($_SESSION['schedule'] as $e) { if ($e['id'] > $max_id) $max_id = $e['id']; }
    $_SESSION['schedule'][] = [
        'id'       => $max_id + 1,
        'time'     => $data['time'],
        'title'    => trim($data['title']),
        'type'     => $data['type'],
        'duration' => trim($data['duration']),
        'location' => trim($data['location']),
    ];
    usort($_SESSION['schedule'], fn($a,$b) => strcmp($a['time'],$b['time']));
    persist_user_data();
}
function update_event($id, $data) {
    init_schedule();
    foreach ($_SESSION['schedule'] as &$e) {
        if ($e['id'] == $id) {
            $e['time']     = $data['time'];
            $e['title']    = trim($data['title']);
            $e['type']     = $data['type'];
            $e['duration'] = trim($data['duration']);
            $e['location'] = trim($data['location']);
            break;
        }
    }
    usort($_SESSION['schedule'], fn($a,$b) => strcmp($a['time'],$b['time']));
    persist_user_data();
}
function delete_event($id) {
    init_schedule();
    $_SESSION['schedule'] = array_values(array_filter($_SESSION['schedule'], fn($e) => $e['id'] != $id));
    persist_user_data();
}
function find_event($id) {
    foreach (get_schedule() as $e) { if ($e['id'] == $id) return $e; }
    return null;
}

function init_habits() {
    init_user_data();
    if (!isset($_SESSION['habits'])) $_SESSION['habits'] = default_habits();
}
function get_habits() { init_habits(); return $_SESSION['habits']; }
function add_habit($data) {
    init_habits();
    $max_id = 0;
    foreach ($_SESSION['habits'] as $h) { if ($h['id'] > $max_id) $max_id = $h['id']; }
    $_SESSION['habits'][] = [
        'id'        => $max_id + 1,
        'name'      => trim($data['name']),
        'icon'      => trim($data['icon']) ?: '✅',
        'streak'    => 0,
        'target'    => (int)$data['target'],
        'completed' => [],
        'category'  => $data['category'],
    ];
    persist_user_data();
}
function update_habit($id, $data) {
    init_habits();
    foreach ($_SESSION['habits'] as &$h) {
        if ($h['id'] == $id) {
            $h['name']     = trim($data['name']);
            $h['icon']     = trim($data['icon']) ?: '✅';
            $h['target']   = (int)$data['target'];
            $h['category'] = $data['category'];
            break;
        }
    }
    persist_user_data();
}
function delete_habit($id) {
    init_habits();
    $_SESSION['habits'] = array_values(array_filter($_SESSION['habits'], fn($h) => $h['id'] != $id));
    persist_user_data();
}
function find_habit($id) {
    foreach (get_habits() as $h) { if ($h['id'] == $id) return $h; }
    return null;
}
