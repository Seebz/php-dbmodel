## 0.4

Features

  - ajout de la validation `confirmation_of`
  - ajout de l'option de validation `skip_empty`

Changes:

  - utilisation de mysqli
  - auto complétion des champs `created_at/updated_at` avant les action `before_create/before_save`

Bugfixes:

  - correction de la validation `uniqueness`
  - correction des notices de passage par référence
  - correction du bug `bypass des validations` avec PHP < 5.3


## 0.3.2

Bugfixes:

  - correction du bug avec les espaces de noms
  - ajout des méthodes de casting 'date|time|datetime' manquantes

## 0.3.1

Features:

  - export basique en XML via la méthode `to_xml()`
  - support de connections multiples

Changes:

  - initialisation d'un objet avec les valeurs par défaut des champs de la table
  - `Casting` des propriétés des modèles selon leur type
  - implémentation de l'interface Serialize dans les modèles
  - amélioration des accès aux propriétés de modèles (ArrayAccess, références)
  - amélioration de la classe `DbTable`
  - ajout du paramètre `what` à la méthode `find()`
  - ajout du finder `last`
  - amélioration de l'option `sort`
  - ajout du nom de modèle en alias de table dans les requêtes
  - utilisation `DbConnection` en remplacement de `DB`

## 0.2

Features:

  - ajouts des premières méthodes de validations

Changes:

  - amélioration de l'option `conditions` des finders
  - utilisation de `DbModel`
  - utilisation de `DbTable` pour les intéractions avec les tables

Bugfixes:

  - correction du bug des stockages statiques

## 0.1

  - initial commit
