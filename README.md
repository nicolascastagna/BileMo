# Projet 7 - Créez un web service exposant une API
### Parcours Développeur d'application PHP/Symfony

## Prérequis

- Serveur local sous PHP 8.2 ([MAMP](https://www.wampserver.com/) pour macOs ou [WAMP](https://www.mamp.info/en/mamp/mac/) pour windows)
- [Symfony](https://symfony.com/download)
- Base de donnée MySQL
- [Composer](https://getcomposer.org/)
  
## Installation du projet

**1 - Cloner le dépôt GitHub pour télécharger le projet dans le répertoire de votre choix :**
```
https://github.com/nicolascastagna/BileMo.git
```

**2 - Installer les dépendances en exécutant la commande suivante :**
```
composer install
```

**3 - Renommer le fichier **.env.example** en **.env** et modifier les paramètres de connexion à la base de données ainsi que JWT_PASSPHRASE**

**4 - Créer la base de données :**   
    
    A. Effectuer les commandes suivantes :
        - php bin/console doctrine:database:create
        - php bin/console doctrine:migrations:migrate
    B. Insertion des données fictives :
        - php bin/console doctrine:fixtures:load
      

**5 - Démarrer le serveur symfony :**   

Démarrez le serveur en exécutant la commande suivante :
```
symfony server:start
```

**6 - Informations de connexions utilisateurs par défaut après éxécution des fixtures**

**Client 1 :**
```
{
    "username": "bouygues@gmail.com",
    "password": "12345678"
}
```

**Client 2 :**
```
{
    "username": "free@gmail.com",
    "password": "12345678"
}
```

**7 - Une fois le serveur démarré, vous pouvez accéder à la documentation de l'API en suivant le lien suivant :**    
```
http://localhost:8000/api/doc/
```

## Contexte
BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.

Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). Il s’agit donc de vente exclusivement en B2B (business to business).

Il va falloir que vous exposiez un certain nombre d’API pour que les applications des autres plateformes web puissent effectuer des opérations.

## Besoin client
BileMo est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.

Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). Il s’agit donc de vente exclusivement en B2B (business to business).

Il va falloir que vous exposiez un certain nombre d’API pour que les applications des autres plateformes web puissent effectuer des opérations.

## Présentation des données
Le premier partenaire de BileMo est très exigeant : il requiert que vous exposiez vos données en suivant les règles des niveaux 1, 2 et 3 du modèle de Richardson. Il a demandé à ce que vous serviez les données en JSON. Si possible, le client souhaite que les réponses soient mises en cache afin d’optimiser les performances des requêtes en direction de l’API.
