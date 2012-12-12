(function(win) {
    'use strict';

    win.App = Ember.Application.create();

})(window);

(function(app) {
    'use strict';

    app.ApplicationController = Ember.Controller.extend({
        isGame: stateFlag('game'),
        isRankings: stateFlag('rankings'),
        isLog: stateFlag('log'),
        isProfile: stateFlag('profile')
    });

    app.GameController = Ember.Controller.extend();

    app.RankingsController = Ember.Controller.extend();

    app.LogController = Ember.Controller.extend();

    app.ProfileController = Ember.Controller.extend();

})(window.App);

(function(app) {
    'use strict';

    app.ApplicationView = Ember.View.extend({
        templateName: 'application'
    });

    app.GameView = Ember.View.extend({
        templateName: 'game'
    });

    app.GameController = Ember.ArrayController.extend({
        selectedPlayer11: null,
        selectedPlayer12: null,
        selectedPlayer21: null,
        selectedPlayer22: null,
        score1: 0,
        score2: 0,

        selectedPlayers: function() {
            return [
                this.selectedPlayer11 === null || this.selectedPlayer11.id === 0 ? null : this.selectedPlayer11,
                this.selectedPlayer12 === null || this.selectedPlayer12.id === 0 ? null : this.selectedPlayer12,
                this.selectedPlayer21 === null || this.selectedPlayer21.id === 0 ? null : this.selectedPlayer21,
                this.selectedPlayer22 === null || this.selectedPlayer22.id === 0 ? null : this.selectedPlayer22
            ];
        }.property('selectedPlayer11', 'selectedPlayer12', 'selectedPlayer21', 'selectedPlayer22'),

        selectedPlayerIds: function() {
            return [
                this.selectedPlayer11 === null ? 0 : this.selectedPlayer11.id,
                this.selectedPlayer12 === null ? 0 : this.selectedPlayer12.id,
                this.selectedPlayer21 === null ? 0 : this.selectedPlayer21.id,
                this.selectedPlayer22 === null ? 0 : this.selectedPlayer22.id
            ];
        }.property('selectedPlayer11', 'selectedPlayer12', 'selectedPlayer21', 'selectedPlayer22'),

        team1name: function() {
            var selectedPlayerIds = this.get('selectedPlayerIds');
            return selectedPlayerIds[0] === 0 && selectedPlayerIds[1] === 0 ? 'Team 1' : this.getTeamPlayersText(selectedPlayerIds[0], selectedPlayerIds[1]);
        }.property('selectedPlayerIds'),

        team2name: function() {
            var selectedPlayerIds = this.get('selectedPlayerIds');
            return selectedPlayerIds[2] === 0 && selectedPlayerIds[3] === 0 ? 'Team 2' : this.getTeamPlayersText(selectedPlayerIds[2], selectedPlayerIds[3]);
        }.property('selectedPlayerIds'),

        invalid: function() {
            return !this.isvalid(2);
        }.property('score1', 'score2', 'selectedPlayerIds'),

        isvalid: function(numTeams) {
            if (numTeams < 1 || numTeams > 2) return false;
            var scores = [this.get('score1'), this.get('score2')];
            if (!(scores[0] === 5 && scores[1] !== 5 || scores[0] !== 5 && scores[1] === 5)) return false;
            var players = this.get('selectedPlayerIds');
            if (numTeams === 1) players = players.slice(0,2);
            if ((players[0] === 0 && players[1] === 0) || (numTeams === 2 && (players[2] === 0 && players[3] === 0))) return false;
            var sorted = players.slice(0);
            sorted.sort();
            if ((sorted[0] === sorted[1] && sorted[0] !== 0) || (numTeams === 2 && (sorted[1] === sorted[2] || sorted[2] === sorted[3]))) return false;
            return true;
        },

        init: function() {
            this._super();
            this.refresh();
            var self = this;
            $('body').on('click', '.team1score,.team2score', function(e) {
                e.stopImmediatePropagation();
                $(this).button('toggle');
                $($(this).hasClass('team1score') ? '#score25' : '#score15').button('toggle');
                self.set('score1', parseInt($('.team1score.active').prop('id').substr(6)));
                self.set('score2', parseInt($('.team2score.active').prop('id').substr(6)));
            });
        },

        refresh: function() {
            var self = this;
            $.getJSON('api.php?action=players', function(data) {
                self.populatePlayers(data);
            });
        },

        populatePlayers: function(data) {
            data.sort(function(a, b) { a = a[1]; b = b[1]; return a < b ? -1 : (a > b ? 1 : 0); });
            var items = [{ id: 0, index: 0, fullName: 'No Player' }];
            var position = 1;
            $.each(data, function(key, value) {
                items.push({ id: parseInt(value[0]), index: position, fullName: value[1] });
                position++;
            });
            this.set('content', Ember.A(items));
        },

        getPlayerName: function(playerid, full) {
            var name = null;
            var players = this.get('content');
            for (var i = 0; i < players.length; i++) {
                if (players[i].id === playerid) {
                    name = full ? players[i].fullName : players[i].fullName.substr(0, players[i].fullName.indexOf(' '));
                    break;
                }
            }
            return name;
        },

        /*getPlayerIndex: function(playerid) {
            var index = null;
            var players = this.get('content');
            for (var i = 0; i < players.length; i++) {
                if (players[i].id === playerid) {
                    index = i + 1;
                    break;
                }
            }
            return index;
        },*/

        getTeamPlayersText: function(player1id, player2id) {
            if (player1id === 0) {
                return this.getPlayerName(player2id, false);
            }
            if (player2id === 0) {
                return this.getPlayerName(player1id, false);
            }
            return this.getPlayerName(player1id, false) + ' & ' + this.getPlayerName(player2id, false);
        }
    });

    app.RankingsController = Ember.ArrayController.extend({
        init: function() {
            this._super();
            this.refresh();
        },

        refresh: function() {
            var self = this;
            $.getJSON('api.php?action=ranking', function(data) {
                self.populateRankings(data);
            });
        },

        populateRankings: function(data) {
            var items = [];
            var position = 1;
            $.each(data, function(key, value) {
                items.push({ position: position, id: parseInt(value[0]), name: value[1], points: parseInt(value[2]) });
                position++;
            });
            this.set('content', Ember.A(items));
        }
    });

    app.RankingsView = Ember.View.extend({
        templateName: 'rankings'
    });

    app.LogController = Ember.ArrayController.extend({
        playersBinding: 'App.router.gameController.content',

        init: function() {
            this._super();
            this.refresh();
        },

        refresh: function() {
            var self = this;
            var players = this.get('players');
            if (players) {
                $.getJSON('api.php?action=log', function(data) {
                    self.populateLogEntries(data);
                });
            }
        }.observes('players'),

        populateLogEntries: function(data) {
            var items = [];
            var gameController = app.router.gameController;
            $.each(data, function(key, value) {
                var date = new Date(value[0] * 1000);
                date = (date.getMonth()+1) + '/' + date.getDate() + '/' + date.getFullYear() + ' ' + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
                var team1 = value[1].split(',');
                var team2 = value[2].split(',');
                team1 = gameController.getTeamPlayersText(parseInt(team1[0]), team1.length > 1 ? parseInt(team1[1]) : 0);
                team2 = gameController.getTeamPlayersText(parseInt(team2[0]), team2.length > 1 ? parseInt(team2[1]) : 0);
                var scores = value[3].split(',');
                scores = scores[0] + ':' + scores[1];
                items.push({ date: date, team1: team1, team2: team2, score: scores });
            });
            this.set('content', Ember.A(items));
        }
    });

    app.LogView = Ember.View.extend({
        templateName: 'log'
    });

    app.ProfileController = Ember.Controller.extend({
        id: 0,

        refresh: function() {
            var self = this;
            var id = this.get('id');
            if (id !== 0) {
                this.set('name', app.router.gameController.getPlayerName(id, true));
                $.getJSON('api.php?action=profile&id=' + id, function(data) {
                    self.populateProfile(data);
                });
            }
        },

        populateProfile: function(data) {
            var statNames = {
                'games': 'Total games played',
                'wins': 'Number of wins',
                'losses': 'Number of losses',
                'wins_goalee': 'Wins as a goalee',
                'losses_goalee': 'Losses as a goalee',
                'wins_midfield': 'Wins as a midfield',
                'losses_midfield': 'Losses as a midfield',
                'skunk_wins': 'Number of skunk wins',
                'skunk_losses': 'Number of skunk losses',
                'best_partner': 'Best partner',
                'worst_partner': 'Worst partner',
                'best_opponent': 'Best opponent',
                'worst_opponent': 'Worst opponent',
                'winning_score': 'Top winning score',
                'losing_score': 'Top losing score',
                'winning_streak': 'Longest winning streak',
                'losing_streak': 'Longest losing streak'
            };

            var items = [];
            $.each(statNames, function(key, value) {
                var val = data[key];
                if (key.indexOf('partner') !== -1 || key.indexOf('opponent') !== -1) {
                    val = app.router.gameController.getPlayerName(val, false);
                }
                if (key === 'wins' || key === 'losses') {
                    val += ' (' + Math.round(val * 100 / data['games']) + '%)';
                }
                if (key.indexOf('wins_') !== -1) {
                    val += ' (' + Math.round(val * 100 / data['wins']) + '%)';
                }
                if (key.indexOf('losses_') !== -1) {
                    val += ' (' + Math.round(val * 100 / data['losses']) + '%)';
                }
                items.push({ name: value, value: val });
            });
            this.set('content', Ember.A(items));
        }
    });

    app.ProfileView = Ember.View.extend({
        templateName: 'profile'
    });

    app.HistoryController = Ember.ArrayController.extend({
        init: function() {
            this._super();
            this.refresh();
            $(window).on('resize', this.chart.debounce(200));
        },

        refresh: function() {
            var self = this;
            $.getJSON('api.php?action=history', function(data) {
                self.populateHistory(data);
            });
        },

        populateHistory: function(data) {
            var d = [];
            $.each(data, function(key, value) {
                if (d[value[1]] === null || d[value[1]] === undefined) {
                    d[value[1]] = { data: [], label: value[2], lines : { show : true }, points : { show : true } };
                }
                d[value[1]].data.push([new Date(value[0] * 1000), value[3]]);
            });
            this.set('content', d.splice(1));
            this.chart();
        },

        chart: function() {
            var el = $('#graph');
            if (!el.is(':visible')) {
                return;
            }
            el.height(Math.max(300, $(window).height() - el.position().top - 40));
            var options = {
                xaxis : {
                    mode : 'time',
                    noTicks: 10
                },
                yaxis : {
                    showLabels : false,
                    autoscale: true,
                    autoscaleMargin: 0.05
                },
                legend: {
                    backgroundOpacity: 0.75
                }
            };
            el.html('');
            var graph = document.getElementById('graph');
            Flotr.draw(graph, this.get('content'), options);
        }
    });

})(window.App);

(function(app) {
    'use strict';

    app.Router = Ember.Router.extend({
        root: Ember.Route.extend({
            doGame: function(router, event) {
                router.transitionTo('game');
                router.gameController.refresh();
            },
            doRankings: function(router, event) {
                router.transitionTo('rankings');
                router.historyController.refresh();
            },
            doLog: function(router, event) {
                router.transitionTo('log');
                router.logController.refresh();
            },
            doProfile: function(router, event) {
                router.transitionTo('profile', { player_id: event.context.id });
            },
            game: Ember.Route.extend({
                route: '/',
                connectOutlets: function(router, event) {
                    router.get('applicationController').connectOutlet('game');
                }
            }),
            rankings: Ember.Route.extend({
                route: '/rankings',
                connectOutlets: function(router, event) {
                    router.get('applicationController').connectOutlet('rankings');
                }
            }),
            log: Ember.Route.extend({
                route: '/log',
                connectOutlets: function(router, event) {
                    router.get('applicationController').connectOutlet('log');
                }
            }),
            profile: Ember.Route.extend({
                route: '/profile/:player_id',
                connectOutlets: function(router, context) {
                    router.get('profileController').set('id', context.player_id).refresh();
                    router.get('applicationController').connectOutlet('profile');
                }
            })
        })
    });

})(window.App);

// A helper function to define a property used to render the navigation. Returns
// true if a state with the specified name is somewhere along the current route.
function stateFlag(name) {
    return Ember.computed(function() {
        var state = App.router.currentState;
        while (state) {
            if (state.name === name) return true;
            state = state.get('parentState');
        }
        return false;
    }).property('App.router.currentState');
}

/*function updateMatchScore()
{
    $('#matchscore').html('');
    var selectedPlayers = getSelectedPlayers();
    if (typeof selectedPlayers === 'string') {
        return;
    }
    var url = 'api.php?action=match&team1=' + selectedPlayers[0] + ',' + selectedPlayers[1] + '&team2=' + selectedPlayers[2] + ',' + selectedPlayers[3];
    $.getJSON(url, function(data) {
        $('#matchscore').html(' (' + data + '% match)');
    });
}

function findBestMatch()
{
    var players = getSelectedPlayers(true); // only first team
    var url = 'api.php?action=match&team1=' + players[0] + ',' + players[1];
    $.getJSON(url, function(data) {
        var select1 = $('#game select')[2];
        var select2 = $('#game select')[3];
        $(select1).prop('selectedIndex', getPlayerIndex(data[0]));
        $(select2).prop('selectedIndex', data.length > 1 ? getPlayerIndex(data[1]) : 0);
        updateTeamNames();
        validate();
        updateMatchScore();
    });
}*/

function submit() {
    var players = getSelectedPlayers();
    var scores = getScores();
    var teamNames = [getTeamPlayersText(players[0], players[1]), getTeamPlayersText(players[2], players[3])];
    var winningTeam = scores[0] > scores[1] ? 0 : 1;
    var losingTeam = 1 - winningTeam;
    var msg = 'Please confirm that ' + teamNames[winningTeam] + ' ' + (scores[losingTeam] === 0 ? 'skunked ' + teamNames[losingTeam] : 'won the match') + ' with score ' + scores[winningTeam] + ':' + scores[losingTeam];
    if (confirm(msg)) {
        $('#submit').button('loading');
        var url = 'api.php?action=update&team1=' + players[0] + ',' + players[1] + '&team2=' + players[2] + ',' + players[3] + '&scores=' + scores[0] + ',' + scores[1];
        $.getJSON(url, function(data) {
            $('#submit').button('reset');
            if (data !== 'OK') {
                alert(data);
            } else {
                setTimeout(resetScores, 0);
            }
        });
    }
}

function swap(player1, player2) {
    var select1 = $('#game select')[player1-1];
    var select2 = $('#game select')[player2-1];
    var index1 = $(select1).prop('selectedIndex');
    var index2 = $(select2).prop('selectedIndex');
    $(select1).prop('selectedIndex', index2);
    $(select2).prop('selectedIndex', index1);
    updateTeamNames();
    validate();
    updateMatchScore();
}

function resetScores() {
    $('#score10').button('toggle');
    $('#score20').button('toggle');
}

$(window).on('load', function() { new FastClick(document.body); });
