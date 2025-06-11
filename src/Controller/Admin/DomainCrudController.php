<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
        // Set expiration date to creation date plus a year
        $domain->setExpireAt($domain->getCreatedAt()->add(new \DateInterval('P1Y')));

        return $domain;
    }
}
