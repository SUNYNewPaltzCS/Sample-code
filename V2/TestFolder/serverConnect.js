// must have connect installed through npm

var connect = require('connect');
var app = connect();

var logger = function(req, res, next){
  console.log(req.method, req.url);

  next();
};

var helloWorld = function(req, res, next){
  res.setHeader('Content-Type', 'text/plain');
  res.write('Hello World Robert.');
  res.end();
};

var goodbyeWorld = function(req, res, next){
  res.setHeader('Content-Type', 'text/plain');
  res.write('Goodbye Rob');
  res.end();
};

app.use(logger);
app.use('/hello', helloWorld);
app.use('/goodbye', goodbyeWorld);
app.listen(3000);

console.log('Server running at http;//localhost:3000/');
