<?php
/**
 * Created by PHPStorm.
 * User: Serhii Kondratovec
 * Email: sergey@spheremall.com
 * Date: 10/29/2017
 * Time: 5:16 PM
 */

namespace SphereMall\MS\Tests\Lib\Basket;

use SphereMall\MS\Entities\Address;
use SphereMall\MS\Entities\DeliveryProvider;
use SphereMall\MS\Lib\Basket\Basket;
use SphereMall\MS\Lib\Basket\Delivery;
use SphereMall\MS\Tests\Resources\SetUpResourceTest;

class BasketTest extends SetUpResourceTest
{
    #region [Test methods]
    public function testBasketCreate()
    {
        $basket = $this->client->basket();
        $this->assertInstanceOf(Basket::class, $basket);

        $product = $this->client->products()->limit(1)->all()->current();
        $basket->add([
            [
                'id'     => $product->id,
                'amount' => 1,
            ],
        ]);

        $this->assertCount(1, $basket->items);
    }

    public function testGetExistingBasket()
    {
        $client = clone $this->client;
        $basket = $client->basket(570);
        $this->assertInstanceOf(Basket::class, $basket);

        $this->assertCount(1, $basket->items);
        $this->assertEquals(570, $basket->getId());
    }

    public function testItemRemoveFromBasket()
    {
        $basket = $this->client->basket();
        $this->assertInstanceOf(Basket::class, $basket);

        $products = $this->client->products()->limit(2)->all();
        $params = array_map(function ($product) {
            return [
                'id'     => $product->id,
                'amount' => 1,
            ];
        }, $products->asArray());

        $basket->add($params);

        $this->assertCount(2, $basket->items);

        $deleteParams = [['id' => $products->current()->id]];
        $basket->remove($deleteParams);
        $this->assertCount(1, $basket->items);
    }

    public function testChangeAmount()
    {
        $basket = $this->client->basket();
        $this->assertInstanceOf(Basket::class, $basket);

        $products = $this->client->products()->limit(1)->all();
        $params = array_map(function ($product) {
            return [
                'id'     => $product->id,
                'amount' => 1,
            ];
        }, $products->asArray());

        $basket->add($params);

        $item = $basket->items->current();
        $this->assertEquals(1, $item->amount);
        $this->assertCount(1, $basket->items);

        $itemParams = [
            [
                'id'     => $item->product->id,
                'amount' => 3,
            ],
        ];

        $basket->update($itemParams);

        $item = $basket->items->current();
        $this->assertEquals(3, $item->amount);
        $this->assertCount(1, $basket->items);

        $itemParams = [
            [
                'id'     => $item->product->id,
                'amount' => 2,
            ],
        ];

        $basket->update($itemParams);

        $item = $basket->items->current();
        $this->assertEquals(2, $item->amount);
        $this->assertCount(1, $basket->items);
    }

    public function testDeliveryMethod()
    {
        $basket = $this->client->basket(570);
        $this->assertInstanceOf(Basket::class, $basket);

        $deliveryProviders = $this->client->deliveryProviders()->limit(1)->all();
        $basket->setDelivery(new Delivery($deliveryProviders->current()));

        $client = clone $this->client;
        $basket = $client->basket(570);
        $this->assertInstanceOf(Delivery::class, $basket->getDelivery());
        $this->assertEquals($deliveryProviders->current()->id, $basket->getDelivery()->id);
    }

    public function testSetShipping()
    {
        $basket = $this->client->basket(570);
        $this->assertInstanceOf(Basket::class, $basket);

        $address = new Address([
            'name'    => 'test',
            'surname' => 'test',
        ]);
        $basket->setShippingAddress($address);

        $this->assertEquals($basket->getShippingAddress()->name, $address->name);
        $this->assertEquals($basket->getShippingAddress()->surname, $address->surname);
    }
    #endregion
}
