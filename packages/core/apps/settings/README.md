# ScopeX

This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 11.2.4.

## Development server

Run `ng serve` for a dev server. Navigate to `http://localhost:4200/`. The app will automatically reload if you change any of the source files.

## Code scaffolding

Run `ng generate component component-name` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module`.

## Build

Run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `--prod` flag for a production build.


To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI Overview and Command Reference](https://angular.io/cli) page.

Dependencies :

under /dist/shared/lib
```
npm link
```

under this project 
```
npm link shared-lib
```

## Doc


### services planifiables

pour les consommations,

* une consommation est crée par date pour chaque produit 'planifiable'
* lorsqu'il s'agit de packs, des créations sont crées pour chaque article du pack.

repas (pic-nic, gouter, buffet)
animation
"nuitée" (chambre disponible/ chambre occupée)

Par défaut lorsqu'on ajoute un service planifiable à un séjour, il est réservé pour le premier jour du séjour.

S'il s'agit d'un service comptabilisé au logement, il est planifié pour le nombre de nuitées, consécutivement à la date de début du séjour.
S'il s'agit d'un service comptabilité à la personne, il est planifié pour le premier jour du séjour.
S'il s'agit d'un pack, les services qui le composent sont planifiés de la même manière : c'est la quantité du pack qui donne l'indication du nombre de nuitées ou de personnes (en fonction du mode de comptabilisation défini pour le pack).

Les consommations peuvent toujours être déplacées manuellement d'un jour à l'autre


limitation : les jours de prestations au sein d'un séjour sont toujours consécutifs - il n'est pas possible d'avoir un "trou" dans une réservation (ex. des prestations le lundi et le mercredi, mais pas le mardi).

