# Nom du Projet Symfony

API symfony

## Prérequis

- PHP 8.2.8 ou supérieur
- Symfony 6.3.1
- Composer, gestionnaire de dépendances pour PHP
- Symfony CLI
- PostgreSQL

## Installation

1. **Clonez le dépôt**

    ```
    git clone https://github.com/user/repo.git
    ```

2. **Installez les dépendances**

    Naviguez vers le répertoire du projet cloné et exécutez la commande suivante :

    ```
    composer install
    ```

3. **Configuration**

    - Configurez les informations de connexion à la base de données dans le fichier `.env` et `.env.test` en suivant les modèles `.env.example` et `.env.test.example`.

    - Générez la base de données :

    ```
    php bin/console doctrine:database:create    
    ```

    - Générez la base de données de test :

    ```
    php bin/console doctrine:database:create --env=test   
    ```

    - Appliquez les migrations :

    ```
    php bin/console doctrine:migrations:migrate
    ```

4. **Lancer le serveur de développement Symfony**

    ```
    symfony server:start
    ```

5. **Accéder à l'application**

    Ouvrez votre navigateur et allez à `http://localhost:8000`.

## Tests

Pour exécuter les tests, utilisez la commande suivante :

    ```
    symfony php bin/phpunit tests/Entity/CustomerOrder 
    ```

