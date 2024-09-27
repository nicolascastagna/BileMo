<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $phoneNames = [
            'iPhone 15' => 'Apple',
            'iPhone 15 Pro' => 'Apple',
            'iPhone 14 Pro' => 'Apple',
            'iPhone 14 Pro Max' => 'Apple',
            'Galaxy S24' => 'Samsung',
            'Galaxy S24 Ultra' => 'Samsung',
            'Galaxy A54' => 'Samsung',
            'Galaxy Z Fold 5' => 'Samsung',
            'Pixel 8' => 'Google',
            'OnePlus 12' => 'OnePlus',
            'iPhone 13' => 'Apple',
            'iPhone 13 Mini' => 'Apple',
            'iPhone 12' => 'Apple',
            'Galaxy S23' => 'Samsung',
            'Galaxy S23 Ultra' => 'Samsung',
            'Galaxy A34' => 'Samsung',
            'Galaxy Z Flip 5' => 'Samsung',
            'Pixel 7' => 'Google',
            'Pixel 7 Pro' => 'Google',
            'OnePlus 11' => 'OnePlus',
            'OnePlus Nord 2' => 'OnePlus',
            'Xperia 1 V' => 'Sony',
            'Xperia 10 V' => 'Sony',
            'Moto G Power 2023' => 'Motorola',
            'Moto Edge 40' => 'Motorola',
            'Nokia G50' => 'Nokia',
            'Nokia XR20' => 'Nokia',
            'Huawei P50 Pro' => 'Huawei',
            'Huawei Mate 50 Pro' => 'Huawei',
            'Oppo Find X5' => 'Oppo',
            'Oppo Reno8 Pro' => 'Oppo',
        ];


        foreach ($phoneNames as $name => $brand) {
            $product = new Product();
            $product->setName($name)
                ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')
                ->setCreationDate($faker->dateTimeBetween('-1 month', 'now'))
                ->setImage($faker->imageUrl(640, 480, 'technics'))
                ->setPrice($faker->randomFloat(2, 500, 1000))
                ->setBrand($brand)
                ->setReference($faker->unique()->numberBetween(1, 100));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
