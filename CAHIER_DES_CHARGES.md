# Cahier des charges - Buildflow

## 1. Presentation du projet

Buildflow est une application web de gestion de projets de genie civil. Elle permet au chef projet, aux ingenieurs, aux dessinateurs et aux clients de suivre les projets, les taches, les plans, les rapports, les documents, les validations et les notifications.

## 2. Objectifs

- Centraliser le suivi des projets de construction.
- Faciliter les echanges entre chef projet, ingenieurs, dessinateurs et clients.
- Gerer les documents techniques et administratifs.
- Permettre la validation des plans et rapports avant partage client.
- Permettre au client d'approuver ou refuser les fichiers avant telechargement.
- Suivre les factures, paiements, demandes, alertes et notifications.

## 3. Utilisateurs et roles

### Chef projet / Admin

- Gerer les utilisateurs.
- Creer, modifier et suivre les projets.
- Affecter les membres aux projets.
- Creer et suivre les taches.
- Valider ou rejeter les plans et rapports.
- Gerer les factures et paiements.
- Consulter les statistiques et notifications.

### Ingenieur

- Consulter les projets affectes.
- Suivre et mettre a jour ses taches.
- Soumettre des rapports.
- Consulter les documents techniques.
- Valider ou rejeter les plans selon le workflow.
- Signaler des alertes.
- Echanger via la messagerie.

### Dessinateur

- Consulter les projets affectes.
- Deposer des plans.
- Suivre le statut des plans: brouillon, soumis, valide, rejete, archive.
- Consulter les commentaires de validation.
- Echanger via la messagerie.

### Client

- Suivre l'avancement de ses projets.
- Consulter les plans et rapports partages.
- Approuver ou refuser les fichiers.
- Telecharger uniquement les fichiers approuves.
- Consulter les factures.
- Envoyer des demandes.
- Recevoir des notifications.

## 4. Fonctionnalites principales

### Authentification

- Connexion par email et mot de passe.
- Redirection automatique selon le role.
- Deconnexion securisee.
- Activation de compte par email.
- Reinitialisation du mot de passe par email.
- Possibilite de desactiver un utilisateur.

### Gestion des utilisateurs

- Creation d'un utilisateur par le chef projet.
- Envoi d'un email d'activation.
- Activation du compte par l'utilisateur.
- Modification du role, du statut, du telephone et des informations personnelles.
- Desactivation ou activation d'un compte.

### Gestion des projets

- Creation et suivi des projets.
- Association du client, des ingenieurs et des dessinateurs.
- Suivi du statut et du pourcentage d'avancement.
- Consultation des details du projet.

### Gestion des taches

- Creation des taches par le chef projet.
- Affectation aux ingenieurs.
- Suivi du statut, de la priorite, de l'echeance et du pourcentage.
- Consultation detaillee des taches.

### Gestion documentaire

- Upload de fichiers techniques et administratifs.
- Lecture ou telechargement selon les droits.
- Validation interne avant partage client.
- Decision client: en attente, approuve, refuse.
- Telechargement client active uniquement apres approbation.

### Notifications

- Notification lors de la creation d'un projet.
- Notification lors de la soumission d'un plan ou rapport.
- Notification lors d'une validation ou d'un rejet.
- Notification lors d'une decision client.

### Facturation

- Creation et suivi des factures.
- Suivi des paiements.
- Consultation des factures cote client.

## 5. Contraintes techniques

- Application PHP avec base MySQL.
- Execution locale via XAMPP.
- Utilisation de PHPMailer pour les emails.
- Protection des pages par role.
- Controle des fichiers uploades par extension, taille et droits d'acces.
- Encodage UTF-8 recommande.

## 6. Regles de securite

- Les pages internes sont accessibles uniquement apres connexion.
- Chaque role accede uniquement a ses interfaces.
- Les fichiers client ne sont telechargeables qu'apres approbation.
- Les comptes inactifs ne peuvent pas se connecter.
- Les liens de reinitialisation de mot de passe expirent.

## 7. Evolutions possibles

- Tableau de bord statistique avance.
- Historique complet des validations.
- Signature electronique des documents.
- Export PDF des rapports et factures.
- Journal d'audit des actions sensibles.
