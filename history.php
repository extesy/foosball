<?php

require_once('players.php');
require_once('update_stats.php');

function loadStats()
{
    $content = file_get_contents('players.stats');
    $content = explode("\n", $content);

    $stats = array();
    foreach ($content as $line) {
        if (empty($line)) {
            continue;
        }
        $columns = explode("\t", $line);
        $timestamp = (int)$columns[0];
        $playerId = (int)$columns[1];
        $rating = (double)$columns[2];
        $stats[] = array($timestamp, $playerId, $rating);
    }

    return $stats;
}

function getLogEntry($stat, $players)
{
    $timestamp = $stat[0];
    $playerId = $stat[1];
    $score = $stat[2];
    return array($timestamp - 86400, $playerId, $players[$playerId], round($score * 100));
}

function history()
{
    $id = (int)(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
    $days = (int)(isset($_REQUEST['days']) ? $_REQUEST['days'] : 10);

    $players = loadPlayers();
    $stats = loadStats();

    $last = array();
    $result = array();
    $dates = array();

    for ($i = 0; $i < count($stats); $i++) {
        $timestamp = (int)(round($stats[$i][0] / 86400) * 86400);
        if (!isset($dates[$timestamp])) {
            $dates[$timestamp] = 0;
        }
        $dates[$timestamp]++;
        $stats[$i][0] = $timestamp;
    }
    $dates = array_keys($dates);
    sort($dates);
    $startDate = ($days <= 0) ? 0 : $dates[count($dates) - $days];

    foreach ($stats as $stat) {
        $timestamp = $stat[0];
        $playerId = $stat[1];
        $score = $stat[2];
        if ($id != 0 && $id != $playerId) {
            continue;
        }

        if (isset($last[$playerId]) && ($last[$playerId][0] < $timestamp) && ($last[$playerId][2] != $score || $timestamp == $startDate) && $timestamp >= $startDate) {
            $result[] = getLogEntry($last[$playerId], $players);
        }

        $last[$playerId] = $stat;
    }

    foreach ($last as $stat) {
        $result[] = getLogEntry($stat, $players);
    }

    return $result;
}
