<?php

require_once('players.php');
require_once('update_stats.php');

function load_stats()
{
    $content = file_get_contents('players.stats');
    $content = explode("\n", $content);

    $stats = array();
    foreach ($content as $line)
    {
        if (empty($line)) continue;
        $columns = explode("\t", $line);
        $timestamp = (int)$columns[0];
        $playerId = (int)$columns[1];
        $rating = (double)$columns[2];
        $stats[] = array($timestamp, $playerId, $rating);
    }

    return $stats;
}

function get_log_entry($stat, $players)
{
    $timestamp = $stat[0];
    $playerId = $stat[1];
    $score = $stat[2];
    return array(round($timestamp / 86400) * 86400 - 86400, $playerId, $players[$playerId], round($score * 100));
}

function history()
{
    $id = (int)(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);

    $players = load_players();
    $stats = load_stats();

    $last = array();
    $result = array();

    foreach ($stats as $stat)
    {
        $timestamp = $stat[0];
        $playerId = $stat[1];
        $score = $stat[2];
        if ($id != 0 && $id != $playerId) continue;

        if (isset($last[$playerId]) && (round($last[$playerId][0] / 86400) < round($timestamp / 86400)) && ($last[$playerId][2] != $score))
            $result[] = get_log_entry($last[$playerId], $players);

        $last[$playerId] = $stat;
    }

    foreach ($last as $stat)
    {
        $result[] = get_log_entry($stat, $players);
    }

    return $result;
}
