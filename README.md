Connexion à la base de données noSQL mongoDB

Installation de bundle Doctrine MongoDB ODM (il fait le lien entre les objets PHP et les documents stockés dans MongoDB.
    Ex UserActionLog,  Doctrine ODM s’occupe de le convertir en documents MongoDB), 
    composer require doctrine/mongodb-odm-bundle

Configuration de connexion
    MONGODB_URL=mongodb://localhost:27017 
    MONGODB_DB=logs_db

Configuration de doctrine_mongodb.yaml

Création du Document pour les logs (une classe PHP qui représente un document stocké dans une collection MongoDB)
    Création de UserActionLog pour les actions de l'utilisateur

Création service pour enregistrer les actions
    DocumentManager : C’est le gestionnaire principal pour toutes les opérations sur les documents MongoDB : il permet de créer, lire, mettre à jour, supprimer des objets PHP qui seront stockés dans MongoDB. C’est l’équivalent du EntityManager pour les bases SQL.

Appel du service dans les routes d'actions (logout, register,..)


##
C’est le bundle Symfony qui intègre Doctrine MongoDB ODM à Symfony. Il gère la configuration, l’injection de dépendances, et facilite l’utilisation de MongoDB dans ton application Symfony
##