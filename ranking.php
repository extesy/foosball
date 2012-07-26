<?php

require_once('players.php');
require_once('update_stats.php');
require_once('PHPSkills/Skills/RankSorter.php');

use Moserware\Skills\RankSorter;

function compare_ratings($a, $b)
{
    $x = $a->getConservativeRating();
    $y = $b->getConservativeRating();
    if ($x == $y) return 0;
    return ($x < $y) ? 1 : -1;
}

function ranking()
{
    $players = load_players();
    $ratings = load_ratings();
    uasort($ratings, 'compare_ratings');
    $result = array();
    foreach ($ratings as $playerId => $rating)
    {
        $result[] = array($playerId, $players[$playerId], round($rating->getConservativeRating() * 100));
    }
    return $result;
}
