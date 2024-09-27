<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $customer1 = $this->getReference('customer_0');
        $customer2 = $this->getReference('customer_1');

        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $user->setLastname($faker->lastName)
                ->setFirstname($faker->firstName)
                ->setEmail($faker->unique()->email)
                ->setPassword(password_hash($faker->password(10), PASSWORD_DEFAULT))
                ->setCreationDate(new \DateTime())
                ->setBillingAddress($faker->address)
                ->setCustomer($customer1)
                ->setPhoneNumber('04' . $faker->numberBetween(10000000, 99999999));
            $manager->persist($user);
            $users[] = $user;
        }


        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $user->setLastname($faker->lastName)
                ->setFirstname($faker->firstName)
                ->setEmail($faker->unique()->email)
                ->setPassword(password_hash($faker->password(10), PASSWORD_DEFAULT))
                ->setCreationDate(new \DateTime())
                ->setBillingAddress($faker->address)
                ->setCustomer($customer2)
                ->setPhoneNumber('04' . $faker->numberBetween(10000000, 99999999));
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CustomerFixtures::class
        ];
    }
}
