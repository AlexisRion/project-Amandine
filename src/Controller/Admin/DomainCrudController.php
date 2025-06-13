<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class DomainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Domain::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            //->setEntityLabelInSingular('Domaine')
            //->setEntityLabelInPlural('Domaines')
            ->renderContentMaximized()
            ->setSearchFields(['name'])
            ->setAutofocusSearch()
            ->setPaginatorPageSize(20)
            ->setPaginatorRangeSize(2)
            ->hideNullValues()
            ->setDefaultSort(['expireAt' => 'ASC'])
        ;
    }

    public function createEntity(string $entityFqcn)
    {
        $domain = new Domain();
        $domain->setCreatedAt(new \DateTimeImmutable());
        $domain->setIsHistory(false);
        // Set expiration date to creation date plus a year
        $domain->setExpireAt($domain->getCreatedAt()->add(new \DateInterval('P1Y')));

        return $domain;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Domaine'),
            DateField::new('expireAt', 'Expire'),
            BooleanField::new('isToSuppress', 'A supprimer'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isHistory', 'Historique'))
            ->add(BooleanFilter::new('isToSuppress', 'A supprimer'))
            ->add(DatetimeFilter::new('expireAt', 'Expire'))
            ;
    }
}
