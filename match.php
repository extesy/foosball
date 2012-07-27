<?php

require_once('update_stats.php');

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

function matchScore($team1players, $team2players, $ratings)
{
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

    $teams = array($team1, $team2);

    $gameInfo = new GameInfo();
    $calculator = new TwoTeamTrueSkillCalculator();
    return $calculator->calculateMatchQuality($gameInfo, $teams);
}

function match($team1players, $team2players)
{
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

    $ratings = load_ratings();
    return round(100 * matchScore($team1players, $team2players, $ratings));
}

function bestMatch($team1players)
{
    $team1players = explode(',', $team1players);
    if (count($team1players) == 2) {
        if ($team1players[0] == 0) $team1players = array($team1players[1]);
        else if ($team1players[1] == 0) $team1players = array($team1players[0]);
    }

    $players = load_players();
    $ratings = load_ratings();

    $bestScore = 0;
    $bestMatch = array();
    foreach ($players as $id1 => $name1) {
        foreach ($players as $id2 => $name2) {
            if (in_array($id1, $team1players) || in_array($id2, $team1players)) continue;
            $team2players = array($id1);
            if ($id1 != $id2) $team2players[] = $id2;
            $score = matchScore($team1players, $team2players, $ratings);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $team2players;
            }
        }
    }
    return $bestMatch;
}
