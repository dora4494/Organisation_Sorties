<?php

namespace App\Controller\Admin;

use App\Entity\Sortie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SortieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sortie::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            DateTimeField::new('dateHeureDebut'),
            Field::new('duree'),
            DateTimeField::new('dateCloture'),
            Field::new('nbInscriptionsMax'),
            TextField::new('descriptionInfos'),
            AssociationField::new('etats_no_etat'),
            TextField::new('urlPhoto'),
            AssociationField::new('idOrganisateur'),
            AssociationField::new('Site'),
        ];
    }
}
