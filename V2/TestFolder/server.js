// An http example, connect is better, and express is better still

var http = require('http');

http.createServer(function(req, res){
  res.writeHead(200, {
    'Content-Type' : 'text/plain'
  });
  res.write('Hi Rob. Keep practicing!')
  res.end();
}).listen(3000);

console.log('Server running at http://localhost:3000/');
