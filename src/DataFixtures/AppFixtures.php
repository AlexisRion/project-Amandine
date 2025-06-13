<?php

namespace App\DataFixtures;

use App\Entity\User;
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
        $password = password_hash('test', PASSWORD_BCRYPT);

        //Creation of 50 domains
        for ($i = 0; $i < 50; $i++) {
            $domain = new Domain();
            $domain->setName($faker->domainName);
            $domain->setCreatedAt(new \DateTimeImmutable());
            $rdmDuration = 'P' . $faker->numberBetween(1, 12) . 'M';
            $domain->setExpireAt($domain->getCreatedAt()->add(new \DateInterval($rdmDuration)));
            $domain->setIsToSuppress($faker->boolean(15));
            $domain->setIsHistory($faker->boolean(5));
            $manager->persist($domain);
        }

        //Creation of 10 Users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setPassword($password);
            $user->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
