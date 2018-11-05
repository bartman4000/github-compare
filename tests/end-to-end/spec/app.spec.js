var mode = process.env.ENV||'local';
var fs = require('fs');
var configFile = 'config/'+mode+'.json';
var config = JSON.parse(fs.readFileSync(configFile));

var frisby = require(config.frisby);
var URL = config.api_url;
var repo1 = config.repo1;
var repo2 = config.repo2;

console.log('Running test in env : '+mode);

frisby.create('Test if API is up')
        .get(URL + '/hello')
        .addHeader('accept', 'application/json')
        .expectStatus(200)
		.expectHeaderContains('content-type', 'application/json')
        .expectJSONTypes({
            info: String,
            message: String
        })
    .toss();

frisby.create('Test Api returns comparison when 2 good params given')
    .get(URL + '/compare?repo1='+repo1+'&repo2='+repo2)
    .addHeader('accept', 'application/json')
    .expectStatus(200)
    .expectHeaderContains('Content-Type', 'application/json')
    .expectJSONTypes({
        comparison: Object,
        winner: String
    })
    .toss();

frisby.create('Test Api returns 404 when 1 param is missing')
    .get(URL + '/compare?repo1='+repo1)
    .addHeader('accept', 'application/json')
    .expectHeaderContains('content-type', 'application/json')
    .expectHeader( 'x-status-reason', 'One of repos is not found')
    .expectStatus(404)
    .expectJSON({
        code: 404,
        message: 'One of repos is not found'
    })
    .toss();

frisby.create('Test Api returns 404 when not existing repository is given')
    .get(URL + '/compare?repo1='+repo1+'&repo2=bull/shit')
    .addHeader('accept', 'application/json')
    .expectHeaderContains('content-type', 'application/json')
    .expectStatus(404)
    .expectHeader( 'x-status-reason', 'One of repos is not found')
    .expectJSON({
        code: 404,
        message: 'One of repos is not found'
    })
    .toss();