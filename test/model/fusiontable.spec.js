const fusiontable = require('../../model/fusiontable.js');
const expect = require('chai').expect;
const fs = require('fs');

var refresh_info = JSON.parse(fs.readFileSync('/home/ubuntu/workspace/private/refresh_tokens.json'));

describe('fusiontable.js', function() {
    it('should generate a url for authentication', function() {
        fusiontable.get(function(err, url) {
            expect(url).to.contain('https://accounts.google.com/o/oauth2/auth?access_type=offline&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Ffusiontables.readonly&response_type=code&client_id=99394339144-q0d4o512a907sb8te0b3ibql6h6g01pg.apps.googleusercontent.com&');
        });
    });
    it('should fail when there is no refresh token supplied', function() {
        fusiontable.tables(function(err, list) {
            expect(err.message).to.equal(Error("No access or refresh token is set.").message);
            fusiontable.setRefreshToken(refresh_info);
        });
    });
    it('should return a list of fusion tables the user has access to', function() {
        fusiontable.tables(function(err, list) {
            //chai.assert.equal(true, false, "test");
            console.log("test" + err.message);
            console.log(list);
        });
    });
    it('should return all of the column headers for a given fusion table', function() {

    });
    it('should submit a query to the fusion table and return the results', function() {

    });
})