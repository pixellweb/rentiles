## Notes
### Rentîles vers Ipsum
Les nouvelles réservations de Rentîles sont importées régulièrement vers Ipsum.
Cela concerne uniquement les réservations agences dont la réfèrence commence par A. Les devis et réservations agence ne sont pas concernées.

Aussi, quotidiennement les réservations de Rentîles, dont la date de retour n'est passée, sont mises à jour sur Ipsum.
Cela permet une mise à jour des réservations dans le cas ou le client modifie sa réservation (possible ?).
Par contre, toutes les modifications ne seront par contre pas prise en compte (exemple une anulation sur Rentîles). 

### Ipsum vers Rentîles

La création, modification et suppression des réservations sur Ipsum sont reportées sur Rentîles.
Seules les données des réservations permettant de gérer les disponibilités sur Rentîles sont assurées (dates, catégorie, status).

## Mise en place
1) Ajouter le fichier de config
2) Ajouter des custom_fields sur les catégories, les lieux, les commandes et les prestations
3) Ajouter les commandes dans le Kernel.

        $date_debut_synchronisation = '2026-06-26';
        $schedule->command('rentiles:import', [
            '--date_debut_synchronisation' => $date_debut_synchronisation
        ])->daily();
        $schedule->command('rentiles:import new', [
            '--date_debut_synchronisation' => $date_debut_synchronisation,
        ])->hourly();





