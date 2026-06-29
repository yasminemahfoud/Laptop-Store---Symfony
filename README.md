# Laptop Store — Symfony

Projet e-commerce de vente de laptops developpe avec Symfony 6.

## Installation

### 1. Cloner le projet
git clone https://github.com/HASSANASSAID/Laptop-Store---Symfony.git
cd Laptop-Store---Symfony

### 2. Installer les dependances
composer install

### 3. Configurer l'environnement
cp .env.example .env

Ouvrir .env et modifier si besoin :
DATABASE_URL="mysql://root:@127.0.0.1:3306/LAPSTORE_STORE?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

### 4. Creer la base de donnees
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

### 5. Lancer le serveur
symfony server:start

Ou avec XAMPP : http://localhost/Laptop_Store/public/

## Technologies
- Symfony 6
- Doctrine ORM
- MySQL / MariaDB
- Bootstrap 5
- Twig
