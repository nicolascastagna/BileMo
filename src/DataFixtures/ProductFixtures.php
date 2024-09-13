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
