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
