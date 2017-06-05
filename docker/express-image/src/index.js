var Chance = require('chance');
var chance = new Chance();

var express = require('express');
var app = express();

/* Méthode de callback lorsque l'application reçoit une 
 * méthode http de type get et la ressource est / */
app.get('/', function(request, response) {
	response.send(generateNewId())
});

/* Ecoute sur le port 3000 */
app.listen(3000, function() {
	console.log('Accepting HTTP requests on port 3000.');
});

function generateNewId() {
	var id = {
		firstName: chance.first(),
		lastName: chance.last(),
		email: chance.email(),
		passphrase: chance.sentence({words: 6})
	};

	 console.log(id);

	 return id;

}