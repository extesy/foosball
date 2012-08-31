from gevent import monkey; monkey.patch_all(time=False)
from flask import Flask, request, Response, send_file
from socketio import socketio_manage
from socketio.namespace import BaseNamespace
from socketio.mixins import RoomsMixin
from socketio.server import SocketIOServer
import csv

class FoosNamespace(BaseNamespace, RoomsMixin):
    players = {}
    teams = {}

    def log(self, message):
        self.logger.info("[{0}] {1}".format(self.socket.sessid, message))

    def initialize(self):
        self.logger = app.logger
        BaseNamespace.initialize(self)
        for row in csv.reader(open('players.txt', 'r'), delimeter='\t'):
            (id, name) = row
            self.players[id] = name
        for row in csv.reader(open('teams.txt', 'r'), delimeter='\t'):
            (id, name) = row
            self.teams[id] = name
        self.log('Read %d teams and %d players' % len(self.teams), len(self.players))

    def disconnect(self, silent=False):
        BaseNamespace.disconnect(self, silent)

    def get_initial_acl(self):
        return ['on_connect', 'on_login']

    def on_connect(self):
        self.emit('players', self.players)
        self.emit('teams', self.teams)
        return True

    def on_login(self, username, team):
        self.session['username'] = username
        self.session['team'] = team
        self.join(team)
        self.lift_acl_restrictions()
        return True

    def on_user_message(self, msg):
        if len(self.socket.session) <= 1:
            self.emit('reset')
        else:
            self.emit_to_room(self.session['room'], 'msg_to_room', self.session['username'], msg)
        return True

app = Flask(__name__)

@app.route('/')
def index():
    return send_file('index.html')

@app.route("/socket.io/<path:path>")
def run_socketio(path):
    socketio_manage(request.environ, {'': FoosNamespace}, request)
    return Response()

if __name__ == '__main__':
    print 'Listening on http://localhost:5000'
    app.debug = True
    #from werkzeug.wsgi import SharedDataMiddleware
    #from os import path
    #app = SharedDataMiddleware(app, {
    #    '/': path.join(path.dirname(__file__), 'static')
    #})
    SocketIOServer(('', 5000), app, policy_server=False).serve_forever()
