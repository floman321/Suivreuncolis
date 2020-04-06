Description 
===

Ce plugin permet d'obtenir le suivi de colis grâce au site aftership, laposte etc ...

La récupération des informations de suivi se fait toutes les heures.
 
Configuration
===

Voir la page de configuration du plugin pour saisir les clef api nécessaire : 

1. Créer un nouveau colis
2. Remplir le n° de suivi
3. Remplir le nom du transporteur ou laissez vide si inconnu
4. Enregistrer, attendre 1 heure ou lancer le cron Plugin Cron Hourly dans le moteur des tâches.
5. Le cron va aller chercher les infos de suivi toutes les heures.

https://developer.laposte.fr/products/suivi/latest
Prendre l'abonnement suivi
Mettre la clef api de production

Afership :
https://docs.aftership.com/api/4/overview

=======
:icons:
== Suivre un colis

=== Description
Ce plugin permet d'obtenir le suivi vos colis

La récupération des informations de suivi ce fait toutes les heures

=== Configuration



=== Codes Etat Colis
Utile si vous voulez déclencher une action sur un code précis (Ouvrir un portier)

Code 0 : Introuvable 

Code 10 : En Transit

Code 20 : Expiré

Code 30 : Prêt pour être livré 
Votre colis est arrivé dans un point de distribution locale.
Votre colis est en cours de livraison.

Code 35 : Non Livré
Votre transporteur a tenté de livrer votre colis mais il n'a pu être livré. Contactez le transporteur pour de plus amples informations.

Code 40 : Livré
Votre colis a été livré avec succès.

Code 50 : Alerte

Il se peut que votre colis ait subi des conditions de transit inhabituelles (Douane, Refusé)



FAQ
===

-   Combien de temps faut-il attendre pour obtenir les premières données ?
-> Jusqu'à 1 heure

-   Est-ce que le plugin s’appuie sur des API tierces ?
Oui , le site www.aftership.com, Laposte.fr

-   Est-ce que le plugin s’appuie sur des plugins tiers ?
Non

-   Est-ce que le plugin gère Mondial Relay ?
Non

