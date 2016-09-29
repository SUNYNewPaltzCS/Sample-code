// Copyright 2012-2016, Google, Inc.
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

'use strict';

var google = require('googleapis');
var fs = require("fs");
var OAuth2Client = google.auth.OAuth2;
var ft = google.fusiontables('v2');

// Client ID and client secret are available at
// https://code.google.com/apis/console
var client_secrets = JSON.parse(fs.readFileSync('client_secrets.json'));
var CLIENT_ID = client_secrets.web.client_id;
var CLIENT_SECRET = client_secrets.web.client_secret;
var REDIRECT_URL = 'https://builder2-deisingj1.c9users.io/fusiontable/auth';

var oauth2Client = new OAuth2Client(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL);
google.options({
    auth: oauth2Client
});

module.exports = {
    blank: function() {
        return {};
    },
    get: function(ret) {
        var url = oauth2Client.generateAuthUrl({
            access_type: 'offline', // will return a refresh token
            scope: 'https://www.googleapis.com/auth/fusiontables.readonly' // can be a space-delimited string or an array of scopes
        });
        ret(null,url);
    },
    oauthcallback: function(code, ret) {
        oauth2Client.getToken(code, function(err, tokens) {
            if (err) {
                return ret(err, null);
            }
            // set tokens to the client
            // TODO: tokens should be set by OAuth2 client.
            oauth2Client.setCredentials(tokens);
            ft.table.list({}, [], function(err, profile) {
                if (err) {
                    console.log('An error occured', err);
                }
                ret(err,tokens);
            });
        });
    },
    tables: function(ret) {
      ft.table.list([],[],function(err, list) {
          ret(err,list);
      });  
    }
};