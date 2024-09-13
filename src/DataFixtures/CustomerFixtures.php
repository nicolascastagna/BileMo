<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $customer1 = new Customer();
        $customer1->setCompany('Bouygues Telecom')
            ->setSiret('12345678901234')
            ->setPassword('12345678')
            ->setEmail('bouygues@gmail.com')
            ->setHeadOffice('Paris');

        $manager->persist($customer1);

        $customer2 = new Customer();
        $customer2->setCompany('Free')
            ->setSiret('12345678901234')
            ->setPassword('12345678')
            ->setEmail('free@gmail.com')
            ->setHeadOffice('Paris');

        $manager->persist($customer2);

        $this->addReference('customer_0', $customer1);
        $this->addReference('customer_1', $customer2);
        $manager->flush();
    }
}
