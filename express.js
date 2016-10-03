var express = require('express'),
    app = express();
var bodyParser = require('body-parser');
var session = require('express-session');
var fusiontable = require('./model/fusiontable.js');
console.log(__dirname + '/public');
app.use(express.static(__dirname + '/public'));
app.use(bodyParser.urlencoded({
    extended: false
}));
app.use(bodyParser.json());
app.use(session({
    secret: 'Ralph The Turtle',
    resave: true,
    saveUninitialized: true,
}));
var site = "https://builder2-deisingj1.c9users.io/";
var loggedIn = false;
app.get("/fusiontable", function(req, res) {
        fusiontable.get(function(err, rows) {
            res.send(rows);
        });
    })
    .get("/fusiontable/auth", function(req, res) {
        fusiontable.oauthcallback(req.query.code, function(err, rows) {
            console.log("Grab " + rows);
            if (err != null) {
                loggedIn = true;
            }
            loggedIn = true;
            res.writeHead(301, {
                Location: site
            });
            res.end();
        });
    })
    .get("/fusiontable/table", function(req, res) {
        if (loggedIn) {
            fusiontable.tables(function(err, rows) {
                res.send(rows);
            });
        }
    })

app.listen(process.env.PORT);