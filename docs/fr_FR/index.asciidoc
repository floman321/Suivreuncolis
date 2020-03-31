:icons:
== Suivre un colis

=== Description
Ce plugin permet d'obtenir le suivi vos colis

La récupération des informations de suivi ce fait toutes les heures

'''
=== Configuration
include::partie_1.asciidoc[]



'''
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

Voir cette page
https://www.17track.net/fr/helpcenter/packagestatus


'''
=== FAQ
include::faq.asciidoc[]
