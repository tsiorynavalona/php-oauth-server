<?php 
    ini_set('display_errors',1);error_reporting(E_ALL);

    require_once './../vendor/autoload.php';

    // testclient:testpass http://localhost/token.php -d 'grant_type=client_credentials'

    if(isset($_POST['id']) && isset($_POST['pwd'])) {
        $id = $_POST['id'];
        $pwd = $_POST['pwd'];

        $url = 'http://localhost/OAuth-server/server/';

        $client = new \GuzzleHttp\Client();
        try {
                $response_token = $client->request('POST', $url.'/token.php', [
                    'auth' => [
                        $id, 
                        $pwd
                    ],
                    'form_params' => [
                        'grant_type' => 'client_credentials',

                    ]
                ]);        
                // echo $response->getStatusCode(); // 200
    // echo $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
    // echo $response->getBody(); // '{"id": 1420053, "name": "guzzle", ...}'

                $body = json_decode($response_token->getBody());

                $token = $body->access_token;

                $response_resource = $client->request('POST', $url.'/resource.php', [
                    // 'auth' => [
                    //     'testclient', 
                    //     'testpass'
                    // ],
                    'form_params' => [
                        'access_token' => $token,

                    ]
                ]);

                $body = json_decode($response_resource->getBody()); // '{"id": 1420053, "name": "guzzle", ...}'
                $resource = $body->success;

                if($resource) {

                    $response_author = $client->request('POST', $url.'/authorize.php?response_type=code&client_id='.$id.'&state=xyz', [
                    
                        'form_params' => [
                            'authorized' => 'yes',
                
                        ]
                    ]);

                }

                // $body = json_decode($response_author->getBody()); // '{"id": 1420053, "name": "guzzle", ...}'

                $code = explode(':', $response_author->getBody());
                $code = trim($code[1]);

                // curl -u testclient:testpass http://localhost/token.php -d 'grant_type=authorization_code&code=YOUR_CODE'

                $response = $client->request('POST', $url.'/token.php', [
                    'auth' => [
                        $id, 
                        $pwd
                    ],
                    
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code'=> $code

                    ]
                ]);

                echo $response->getBody(); // '{"id": 1420053, "name": "guzzle", ...}'
        } catch (GuzzleHttp\Exception\ClientException $e) {
            die($e->getMessage());
        }

    } else {
        die('client missing');
    }

  


    





