<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Picqer\Financials\Exact\Account;
use Picqer\Financials\Exact\Connection;
use Picqer\Financials\Exact\ApiException;
use Picqer\Financials\Exact\Item;
use Picqer\Financials\Exact\SalesItemPrice;
use Picqer\Financials\Exact\SalesOrder;
use Picqer\Financials\Exact\SalesOrderLine;
use Picqer\Financials\Exact\User;

class HomeController extends AppController
{

    public function index()
    {
        $connection = $this->connect();
        $accountName = null;
        if ($connection) {
            $account = (new User($connection))->get()[0];
            $accountName = $account->FullName;
        }
        $this->set('accountName', $accountName);

        $history = TableRegistry::getTableLocator()->get('History');
        $lastUpdate = $history->find()->where(['action' => 0])->last();
        $lastExport = $history->find()->where(['action' => 1])->last();

        $turfhistorie = TableRegistry::getTableLocator()->get('Turfhistorie');
        $turfjes = $turfhistorie->find()->where(["geexporteerd IS" => null])->count();

        $this->set('daysSinceUpdate', $this->getDateIndication($lastUpdate));
        $this->set('daysSinceExport', $this->getDateIndication($lastExport));
        $this->set('turfjes', $turfjes);
    }

    public function connectExact()
    {
        $this->connect(true);
        return $this->redirect(['action' => 'index']);
    }

    public function removeExact()
    {
        $userId = $this->Auth->user('id');
        $tokens = TableRegistry::getTableLocator()->get('Tokens');
        $token = $tokens->find()->where(['user_id' => $userId])->first();
        if ($token) {
            $tokens->delete($token);
        }
        return $this->redirect(['action' => 'index']);
    }

    public function updateProducts()
    {
        $connection = $this->connect();

        $products = TableRegistry::getTableLocator()->get('Producten');
        $products->deleteAll(['1' => '1']);
        $items = (new Item($connection))->get();

        $salesItemPrice = new SalesItemPrice($connection);
        $categorien = TableRegistry::getTableLocator()->get('Productcategorien');

        foreach ($items as $item) {
            $priceId = $salesItemPrice->findId($item->ID, "Item");
            $price = $salesItemPrice->find($priceId);

            $item->Notes;
            $categorieId = $categorien->getIdFromNotes($item->Notes);

            $product = $products->newEntity([
                'GUID_Exact' => $item->ID,
                'ID_Exact' => $item->Code,
                'ID_Categorie' => $categorieId,
                'caption' => $item->Description,
                'status' => $item->IsSalesItem ? 1 : 0,
                'prijs' => $price->Price,
                'btwcat' => $item->SalesVatCode ? $item->SalesVatCode : 0,
                'grootboek' => '8700',
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ]);

            try {
                $products->save($product);
            } catch (\Exception $e) {
                echo $e->getMessage() . '<br>';
            }
        }

        $this->saveAction(0);
        return $this->redirect(['action' => 'index']);
    }

    public function exportHistory()
    {
        $connection = $this->connect();
        $turfhistorie = TableRegistry::getTableLocator()->get('Turfhistorie');
        $turfjes = $turfhistorie->find()
            ->where(["geexporteerd IS" => null])
            ->order('datumtijd, ID_Lid')
            ->all();

        $accounts = new Account($connection);

        while ($turfjes->valid()) {
            $lid = $turfjes->current()->get('ID_Lid');
            $id = $accounts->findId("$lid");

            $date = date('d-m-Y', strtotime($turfjes->current()->get('datumtijd')));
            $salesOrder = new SalesOrder($connection);
            $salesOrder->OrderedBy = $id;
            $salesOrder->DeliverTo = $id;
            $salesOrder->InvoiceTo = $id;
            $salesOrder->Description = 'Geturfde Artikelen ' . $date;
            $salesOrder->DeliveryDate = $date;
            $salesOrder->OrderDate = $date;
            $salesOrder->PaymentCondition = 1;
            $salesOrder->Status = 6;

            $orderLines = [];
            while ($turfjes->valid() && $lid == $turfjes->current()->get('ID_Lid')) {
                $salesOrderLine = new SalesOrderLine($connection);
                $salesOrderLine->Item = $turfjes->current()->get('Guid_product');
                array_push($orderLines, $salesOrderLine);
                $turfjes->current()->set('geexporteerd', date('Y-m-d H:i:s'));
                $turfhistorie->save($turfjes->current());
                $turfjes->next();
            }
            $salesOrder->SalesOrderLines = $orderLines;
            $salesOrder->save();
        }

        $this->saveAction(1);
        return $this->redirect(['action' => 'index']);

    }

    private function connect($redirect = false)
    {
        $userId = $this->Auth->user('id');
        $this->set('user', $userId);
        $tokens = TableRegistry::getTableLocator()->get('Tokens');
        $token = $tokens->find()->where(['user_id' => $userId])->first();
        $connection = new Connection();
        $connection->setRedirectUrl(Configure::read('redirectUrl'));
        $connection->setExactClientId(Configure::read('clientId'));
        $connection->setExactClientSecret(Configure::read('clientSecret'));

        if ($token) {
            $connection->setAccessToken($token->access_token);
            $connection->setRefreshToken($token->refresh_token);
            $connection->setTokenExpires($token->expires);
        }

        if (!$connection->needsAuthentication() || $redirect) {
            try {
                $connection->connect();
                return $connection;
            } catch (ApiException $e) {
                $connection->redirectForAuthorization();
            }
        }

        return null;
    }

    private function saveAction($action)
    {
        $userId = $this->Auth->user('id');
        $history = TableRegistry::getTableLocator()->get('History');
        $action = $history->newEntity([
            'user_id' => $userId,
            'action' => $action,
        ]);
        $action->date = date('Y-m-d H:i:s');
        $history->save($action);
    }

    private function getDateIndication($date)
    {
        if ($date == null) {
            return "Nooit";
        }
        $datediff = time() - strtotime($date->date);
        $daysSince = round($datediff / (60 * 60 * 24)) - 1;
        if ($daysSince == 0) {
            return "Vandaag";
        } else if ($daysSince == 1) {
            return "Gisteren";
        } else {
            return "$daysSince dagen geleden";
        }
    }
}
?>
