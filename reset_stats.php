<?php

require_once('update_stats.php');

function resetStats()
{
    $games = file_get_contents('games.log');
    $games = explode("\n", $games);

    file_put_contents('games.stats', '');
    file_put_contents('players.stats', '');
    foreach ($games as $game) {
        if (empty($game)) {
            continue;
        }
        $game = explode("\t", $game);
        updateStats($game[0], $game[1], $game[2], $game[3], false);
    }
}
