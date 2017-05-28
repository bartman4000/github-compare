WEBAPI : end-to-end tests
================================

Tests can be run using [frisby](http://frisbyjs.com/), you need [node](https://nodejs.org), [npm](https://www.npmjs.com/) and [jasmine](https://jasmine.github.io/) to be installed on you computer.

## Install ##

1- First install the required dependencies by running :
```
npm install
```
from this 'end-to-end' folder.

## Run ##

2- Run the tests (in local) by launching the following command (make sure your api is up and running before):

```
jasmine-node spec/app.spec.js --config ENV local
```

```local```  can be replaced in future by the desired environment (test). 
The different configurations can be located in the end-to-end/config folder.

Running jasmine without specifying the env configuration, like this :

```
jasmine-node spec
```
Should run the test picking local configuration file.
