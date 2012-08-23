<?php

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once('reset_stats.php');
require_once('update_stats.php');
require_once('players.php');
require_once('ranking.php');
require_once('history.php');
require_once('log.php');
require_once('match.php');
require_once('profile.php');

$action = $_REQUEST['action'];
switch ($action) {
    case 'players':
        echo json_encode(players());
        break;
    case 'update':
        echo json_encode(updateStats(time(), $_REQUEST['team1'], $_REQUEST['team2'], $_REQUEST['scores']));
        break;
    case 'reset':
        resetStats();
        break;
    case 'ranking':
        echo json_encode(ranking());
        break;
    case 'history':
        echo json_encode(history());
        break;
    case 'log':
        echo json_encode(gameLog());
        break;
    case 'match':
        if (isset($_REQUEST['team2'])) {
            echo json_encode(match($_REQUEST['team1'], $_REQUEST['team2']));
        } else {
            echo json_encode(bestMatch($_REQUEST['team1']));
        }
        break;
    case 'profile':
        echo json_encode(profile($_REQUEST['id']));
        break;
}
