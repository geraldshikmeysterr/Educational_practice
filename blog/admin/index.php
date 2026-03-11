<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
redirect('posts.php');
