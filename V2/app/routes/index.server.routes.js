module.exports = function(app){
  // imports the index.server.controller I made
  var index = require('../controllers/index.server.controller');
  // if a an http request using a GET request to the root path
  // is called, then the render method of the index.server.controller is used
  app.get('/', index.render);
};
