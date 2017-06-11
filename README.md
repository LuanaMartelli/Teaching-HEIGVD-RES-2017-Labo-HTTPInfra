# Labo HTTP Infrastructure

## Ludovic Richard & Luana Martelli

## Introduction 
L’objectif de ce laboratoire est de se familiariser avec des outils de développement logiciel pour construire une infrastructure web. Nous allons construire deux serveurs, l’un avec du contenu statique, l’autre avec du contenu dynamique, reliés par un proxy. Pour ce laboratoire, nous allons aussi apprendre à exécuter des requêtes asynchrones. Tous ces serveurs seront lancés via Docker. 

## Partie 1 : Serveur http static avec apache

### Objectifs
Installer un serveur apache via une image docker. Ecrire le Dockerfile correspondant et ajouter du contenu basique à une page HTML.

### Manipulations
Pour cette partie, nous sommes allés chercher une image officielle apache sur Docker hub. Nous avons choisi l’image php. Dans le Dockerfile, la première ligne (FROM) spécifie le nom de l’image ainsi que sa version. La deuxième ligne permet de copier des fichiers de note système de fichier local dans le système de fichier de l’image. On aurait pu démarrer l’image officielle sans utiliser de Dockerfile, afin d’effectuer quelques tests préliminaires (afin de devoir éviter de toujours reconstruire l’image). Dans ce cas, on va directement chercher l’image sur le site officiel. 
Entrer dans le container et accéder aux fichiers de config: 

`docker exec -it nom_image /bin/bash`

`cd /etc/apache2`

fichier apache2.conf

`cd sites-available`

Dans le fichier 000-default.conf, un ligne spécifie le chemin par défaut /var/www/html
Commandes utiles : 

`docker build -it res/apache_php .`

`docker run -p 9090:80 res/apache_php`  


## Partie 2 : Serveur http dynamique avec javascript

### Objectifs
Ecrire une application web dynamique avec javascript. Cette application doit retourner une chaîne de caractères sous format JSON après une requête GET.

### Manipulations
Dans le Dockerfile, les deux premières lignes ont le même but qu’au point 1. La dernière ligne représente la commande à effectuer lors du lancement du contnair. Il va donc effectuer la commande node index.js, qui va donc exécuter le script.
Npm init sert à créer le fichier package.json qui contient les informations relatives à l’application (entre-autres, les dépendances).    
Notre application dynamique renvoie une nouvelle identité sous format JSON. Pour tous les espions qui doivent changer de couverture, ils y trouveront un nouveau prénom, nom, adresse email ainsi qu’une phrase de 6 mots. Cette phrase est leur phrase de secours en cas de problème.   

## Partie 3 : Apache reverse proxy

### Objectifs
Développer un reverse proxy qui va être le lien entre nos deux containers et le reste du monde. Toutes les requêtes vont passer par le proxy.

### Manipulations
Dans cette configuration, les adresses IP des containers ont dû être hardcodées : c’est une mauvaise pratique puisque rien de garanti que, lorsque l’on va relancer les container docker, les adresses IP attribuées seront les mêmes. Il faut donc vérifier ce point à chaque fois que l’on relance l’application.
Le proxy n’a pas de contenu à proprement parlé, il doit juste rediriger le client suivant la requête effectuée. C’est pourquoi dans cette partie, nous avons dû modifier un fichier de configuration se trouvant dans le dossier /etc/apache2/sites-available. Nous avons créé un nouveau fichier de configuration pour que le proxy sache ce qu’il doit retourner au client selon la requête de ce dernier. Il existe deux mots-clés pour définir une route du proxy à l’hôte virtuel souhaité. Il s’agit de proxypass et de proxypassreverse. Pour que le fichier de configuration soit utilisé, il faut ajouter le module correspondant au Dockerfile avec la commande a2enmod (car, par défaut, les deux mots-clés ci-dessus sont inconnus). Finalement, nous avons dû modifier le fichier hosts dans nos machines afin de lier l’adresse IP de la machine virtuelle à un serveur DNS. Ceci nous permet d’effectuer des requêtes via le navigateur et pas juste telnet.

##Partie 4 : Ajax avec JQuery

### Objectif
Utiliser JQuery pour effectuer des requêtes AJAX. Le but est de pouvoir faire automatiquement des requêtes vers le service dynamique.

### Manipulations
Pour relancer les containers, on les redémarre dans un ordre particulier. Puisque pour le moment, les adresses sont hardcodées dans le fichier de configuration, on espère qu’ils gardent les mêmes adresses IP. 
Manipulation à faire si le paquet node_modules est absent : `npm install`.
Nous avons modifié la page html principale de note application pour y mettre un script javascript, qui effectue des requêtes auprès de notre container express, effectué à l’étape 2. Après avoir créé ce petit script, nous avons fait en sorte que la page principale se recharge toutes les deux secondes.
Remarque : Les navigateurs utilisent ce qu’on appelle same-origin policy. Cela signifie que si un script est exécuté avec un certain nom de domaine, alors il ne pourra faire des requêtes que vers ce même nom de domaine, et donc vers la même machine.
Dans le cas de notre labo, on veut faire des requêtes vers la machine express qui fournit du contenu dynamique alors que la requête vient de la machine qui fournit le contenu statique. Il ne serait donc à priori pas possible de faire les choses ainsi. 
Nous avons donc mis en place un reverse proxy, pour que la machine qui exécute le script ait l'impression que l'origine du script ainsi que la destination de ses requêtes respectivement vient et va au même point réseau, alors qu'en fait ce sont des machines différentes cachées derrière un intermédiaire (le reverse proxy).

## Partie 5 : configuration dynamique d’un reverse proxy

### Objectif
Modifier le fichier de configuration du reverse proxy afin de se débarrasser des adresses ip hardcodées dans le fichier.

### Manipulations
Il est possible, depuis la ligne de commande lors du lancement d’un container, de modifier les variables d’environnement de ce dernier. Ceci est très pratique, puisque nous pouvons donc avoir accès, depuis l’extérieur, à du contenu interne. Avec cette pratique, nous allons pouvoir modifier le fichier de configuration contenant les adresses IP des deux machines de manière dynamique. Pour se faire, nous devons exécuter des commandes pendant le lancement du reverse proxy. En allant regarder à la fin du Dockerfile de php (l’image utilisée pour lancer nos containers), on voit que la commande apache2 est lancée en premier plan à la fin du fichier. L’idée est d’exécuter notre propre script avant de lancer cette ultime commande. Nous avons donc repris ce script, apache2-foreground, afin d’ajouter notre code ; nous avons fait en sorte que les IP passées en paramètres de la commande lors du lancement du container modifient les variables d’environnements correspondantes. Finalement, nous avons écrit un script en php pour rediriger les adresses IP vers les variables d’environnement, et non plus vers les adresses hardcodées en dur. Pour le bon fonctionnement de la manipulation, l’étape ultime a été de copier ce fichier de configuration php dans le container sous le chemin /var/apache2/template et d’écraser le fichier de configuration par ledit fichier php dans le document apache2-foreground modifié.

## Partie bonus : load balancing

### Objectif
Lancer plusieurs serveurs fournissant le même service. Le proxy connaît ces serveurs et peut rediriger les requêtes sur tel ou tel serveur, suivant le trafic ou si un serveur tombe en panne.

### Source
https://httpd.apache.org/docs/2.4/howto/reverse_proxy.html

### Manipulations
Pour cette partie, nous avons modifié en premier lieu le fichier de configuration php. Nous avons ajouté les mots-clés (BalanceMember et ProxySet) pour indiquer au proxy que plusieurs serveurs fournissent le même service. Pour cette partie, nous avons mis à disposition deux serveurs pour la partie dynamique et deux serveurs pour la partie statique. Finalement, nous avons ajouté les modules permettant d’utiliser le load balancing dans le Dockerfile.

### Tests
Afin de vérifier le bon fonctionnement de cette étape, nous avons lancé deux containers statiques, 2 containers dynamiques et le proxy. Nous avons alors stoppé l’exécution d’un des containers (statique ou dynamique). Après recharge de la page, le proxy a réussi à envoyer les requêtes au serveur toujours actif, et nous avons pu observer le bon fonctionnement de l’application.
Commandes effectuées : 
Dans le dossier du serveur statique :

`docker build -t res/apache_php .`

`docker run -d res/apache_php` (x2)

Dans le dossier du serveur dynamique : 

`docker build -t res/express .`

`docker run -d res/express` (x2)

Dans le dossier du reverse proxy : 

`docker build -t res/apache_rp .`

`docker run -p 8080:80 -e STATIC_APP1=172.17.0.2:80 -e STATIC_APP2=172.17.0.3:80 -e DYNAMIC_APP1=172.17.0.4:3000 -e DYNAMIC_APP2=172.17.0.5:3000 res/apache_rp`

À noter que les adresses ip ont été trouvées avec la commande `docker inspect`.

Nous pouvons voir que l'application tourne correctement. Si on tue un container dynamique (par exemple), on peut voir que le proxy se tourne vers l'autre serveur actif au bout de quelques secondes.

## Partie bonus : Round-Robin vs Sticky Session

### Objectif
Comprendre la différence entre les deux termes. Faire en sorte que le proxy puisse passer de l'un à l'autre.

### Remarque
Pour cette partie, nous avons cherché une image de reverse proxy qui implémentait du load-balancing. Nous avons trouvé plusieurs résultats, tel que nginx ou traefik. Notre choix s'est porté sur traefix car la documentation et l'implémentation ont été plus facile à comprendre et mettre en place.

### Définitions 
Round-Robin : Pour un serveur ayant plusieurs adresses IP disponibles, une façon de répartir la charge entraînée par du trafic entre les machines est d'implémenter un round-robin. En d'autres termes, pour un sevreur donné, le proxy dispose d'une liste d'adresses IP à sa disposition. Pour chaque requête, il va la rediriger sur la première adresse de sa liste, puis mettre l'adresse IP en fin de liste, et ainsi de suite. 
Sticky-Session : Permet de lier la session d'un utilisateur avec une instance en particulier. Pour se faire, le proxy va utiliser un cookie de session. 

### Source
https://traefik.io/
https://hub.docker.com/_/traefik/
https://docs.traefik.io/toml/#docker-backend

### Manipulations 
La première étape a été d'écrire un Dockerfile. Ce Dockerfile contient uniquement le nom de l'image traefik et la commande pour copier le fichier de configuration local dans le container.
Ensuite, nous avons modifié ledit fichier de configuration selon les directives du site. Nous avons spécifié le nom de domaine ainsi que le port utilisé.
Finalement, dans les fichiers docker des serveurs dynamique et statiques, nous avons dû ajouter des labels. Par défaut, traefik a un certain comportement (comme le port). Nous avons donc mis les informations nécessaires pour le bon fonctionnement de l'application. Par défaut, traefik est implémenté en round robin (wrr). Pour activer le sticky session, il faut cependant mettre le paramètre à true. Nous l'avons mis pour le serveur statique. 

### Tests
Après le lancement des containers dynamiques et statiques (dans les bons dossiers)

`docker build -t res/apache_static .`

`docker run -d res/apache_static`

`docker build -t res/express .`

`docker run -d res/express`

On lance le traefik : 

`docker build -t res/traefik .`

`docker run -d -p 9090:8080 -p 8080:80 -v /var/run/docker.sock:/var/run/docker.sock res/traefik`

Le port 9090 permet d'accéder au tableau de bord de traefik et ainsi de contrôler les serveurs actifs. L'application est toujours active depuis le port 8080. De plus, l'option -v permet de réserver un certain volume (car par défaut, le volume accordé au container est limité).

## Dynamic cluster management 

### Remarques
Par défaut, traefik charge dynamiquement les containers. Il n'y a pas eu besoin de faire de manipulations supplémentaires pour cette partie. Afin de tester le bon fonctionnement de cette partie, nous pouvons lancer les containers, comme à l'étape précédante, supprimer un container, en relancer un et vérifier que l'application continue de tourner après quelques secondes. 
À noter que dans git, cette partie se trouve du coup aussi dans la branche labo_step2. 
