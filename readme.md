# Pour cloner et accéder à ce projet :

1. cloner le projet
   `git clone /repo/`

2. pour avoir accès au composer
   `composer install`

3. afin d'avoir accès à la base de données :
   `php bin/console doctrine:schema:update --force`

chemins pour avoir les requêtes postman :

1. pour les livres :

- livre/all => pour voir tous les livres
- livre/create => pour ajouter un livre
- livre/{id}/editer => pour modifier le livre selon l id
- livre/{id}/supprimer => pour supprimer un livre
