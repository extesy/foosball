<?php

require_once('players.php');
require_once('update_stats.php');

function compareRatings($a, $b)
{
    $x = $a->getConservativeRating();
    $y = $b->getConservativeRating();
    return ($x == $y) ? 0 : (($x < $y) ? 1 : -1);
}

function ranking()
{
    $players = loadPlayers();
    $ratings = loadRatings();
    uasort($ratings, 'compareRatings');
    $result = array();
    foreach ($ratings as $playerId => $rating) {
        $result[] = array($playerId, $players[$playerId], round($rating->getConservativeRating() * 100));
    }
    return $result;
}
