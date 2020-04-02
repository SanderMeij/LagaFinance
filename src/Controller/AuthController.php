<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Picqer\Financials\Exact\Connection;

class AuthController extends AppController {

    public function index()
    {
        $code = $this->request->getQuery('code');

        $connection = new Connection();
        $connection->setRedirectUrl(Configure::read('redirectUrl'));
        $connection->setExactClientId(Configure::read('clientId'));
        $connection->setExactClientSecret(Configure::read('clientSecret'));
        $connection->setAuthorizationCode($code);
        $connection->connect();

        $userId = $this->Auth->user('id');
        $tokens = TableRegistry::getTableLocator()->get('Tokens');
        $token = $tokens->find()->where(['user_id' => $userId])->first();
        if (!$token) {
            $token = $tokens->newEntity(['user_id' => $userId]);
        }
        $token->access_token = $connection->getAccessToken();
        $token->refresh_token = $connection->getRefreshToken();
        $token->expires = $connection->getTokenExpires();
        $tokens->save($token);

        return $this->redirect(
            ['controller' => 'Home']
        );

    }

}