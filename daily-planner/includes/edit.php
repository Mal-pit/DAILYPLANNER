<?php
require_once __DIR__ . '/config.php';
require_auth();
header('Location: ../tasks.php');
exit();
