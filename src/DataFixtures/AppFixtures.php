<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use App\Entity\Domain;
use function Sodium\add;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        //Creation of 50 domains
        for ($i = 0; $i < 50; $i++) {
            $domain = new Domain();
            $domain->setName($faker->domainName);
            $domain->setCreatedAt(new \DateTimeImmutable());
            $domain->setExpireAt($domain->getCreatedAt()->add(new \DateInterval('P1Y')));
            $domain->setIsToSuppress($faker->boolean(15));
            $domain->setIsHistory($faker->boolean(5));
            $manager->persist($domain);
        }

        $manager->flush();
    }
}
