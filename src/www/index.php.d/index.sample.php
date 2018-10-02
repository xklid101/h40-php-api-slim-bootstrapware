<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * @md
***GET /sample**
Sample action for slim framework

```javascript
Response:
{
    "message": "hello world", //string
}
```
*/
$app->get('/sample', function(Request $request, Response $response, array $args): Response {
    return $response->withJson([
        "message" => "hello world"
    ]);
});
