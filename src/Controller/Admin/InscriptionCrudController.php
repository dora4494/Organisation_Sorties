<?php

namespace App\Controller\Admin;

use App\Entity\Inscription;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;


class InscriptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Inscription::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('dateInscription'),
        ];
    }
}
