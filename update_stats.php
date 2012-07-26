<?php

require_once('ranking.php');

require_once('PHPSkills/Skills/TrueSkill/TwoTeamTrueSkillCalculator.php');
require_once('PHPSkills/Skills/GameInfo.php');
require_once('PHPSkills/Skills/Player.php');
require_once('PHPSkills/Skills/Rating.php');
require_once('PHPSkills/Skills/Team.php');
require_once('PHPSkills/Skills/Teams.php');
require_once('PHPSkills/Skills/SkillCalculator.php');

use Moserware\Skills\GameInfo;
use Moserware\Skills\Player;
use Moserware\Skills\Rating;
use Moserware\Skills\Team;
use Moserware\Skills\Teams;
use Moserware\Skills\SkillCalculator;
use Moserware\Skills\TrueSkill\TwoTeamTrueSkillCalculator;

function load_ratings()
{
    $stats = file_get_contents('games.stats');
    $stats = explode("\n", $stats);

    $ratings = array();
    foreach ($stats as $stat)
    {
        if (empty($stat)) continue;
        $stat = explode("\t", $stat);
        $playerId = (int)$stat[0];
        $mean = (double)$stat[1];
        $stddev = (double)$stat[2];
        $ratings[$playerId] = new Rating($mean, $stddev);
    }

    $gameInfo = new GameInfo();
    $players = load_players();
    foreach ($players as $playerId => $playerName)
    {
        if (!isset($ratings[$playerId])) $ratings[$playerId] = $gameInfo->getDefaultRating();
    }

    return $ratings;
}

function update_stats($time, $team1players, $team2players, $scores, $enableLogging = true)
{
    $scores = explode(',', $scores);
    if (count($scores) == 2 && $scores[0] == $scores[1]) return 'Draws are not supported.';

    $team1players = explode(',', $team1players);
    if (count($team1players) == 2) {
        if ($team1players[0] == 0) $team1players = array($team1players[1]);
        else if ($team1players[1] == 0) $team1players = array($team1players[0]);
    }

    $team2players = explode(',', $team2players);
    if (count($team2players) == 2) {
        if ($team2players[0] == 0) $team2players = array($team2players[1]);
        else if ($team2players[1] == 0) $team2players = array($team2players[0]);
    }

    if ($enableLogging)
        file_put_contents('games.log', $time . "\t" . join(',', $team1players) . "\t" . join(',', $team2players) . "\t" . join(',', $scores) . "\n", FILE_APPEND);





    $gameInfo = new GameInfo();
    $ratings = load_ratings();

    $team1 = new Team();
    foreach ($team1players as $playerId)
    {
        $player = new Player($playerId);
        $team1->addPlayer($player, $ratings[$playerId]);
    }

    $team2 = new Team();
    foreach ($team2players as $playerId)
    {
        $player = new Player($playerId);
        $team2->addPlayer($player, $ratings[$playerId]);
    }

    $teams = Teams::concat($team1, $team2);

    $calculator = new TwoTeamTrueSkillCalculator();
    $winner = count($scores) == 1 ? $scores : ($scores[0] > $scores[1] ? 1 : 2);
    $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, $winner == 1 ? array(1,2) : array(2,1));

    foreach ($newRatings->getAllPlayers() as $player)
    {
        $rating = $newRatings->getRating($player);
        $ratings[$player->getId()] = $rating;
    }





    $stats = array();
    foreach ($ratings as $playerId => $rating)
    {
        $stats[] = $playerId . "\t" . $rating->getMean() . "\t" . $rating->getStandardDeviation();
        file_put_contents('players.stats', $time . "\t" . $playerId . "\t" . $rating->getConservativeRating() . "\n", FILE_APPEND);
    }
    file_put_contents('games.stats', join("\n", $stats));

    return 'OK';
}
