# destination-finder-testproject
## Introduction
This is my solution to a test project.

Details and requirements here: https://github.com/outl1ne/laravel-destination-finder-test-project.

## Api

 Endpoint for fetching all stops is http://{DB_HOST}:{Port}/api/stops.
 
 When a user wants to find destinations from a stop, the endpoint for it is http://{DB_HOST}:{Port}/api/stops?stop={stopvalue}.

 Middleware that fetches data is in app/Http/Controllers/StopsController.php.
 
 ## Error handling

When a stop isn't found the server responds with errorcode 404 and a message "Provided stop does not exist".

If the stop does exist but It's not linked to any routes, the response will be errorcode 500 with errormessage "Stop exists but It Isn't associated with any routes".



