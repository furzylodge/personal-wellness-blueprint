# Personal Wellness Blueprint

# Food Monographs Index

This index provides a human-readable catalogue of all food monographs in
the Personal Wellness Blueprint knowledge base.

Each food has:

-   A machine-readable knowledge object (`foods/Fxxx-*.json`)
-   A human-readable monograph (`monographs/Fxxx-*.md`)

The JSON object powers the Personalisation Engine and APIs, while the
monograph serves as the authoritative reference document.

------------------------------------------------------------------------

## Status Key

  Status      Meaning
  ----------- ------------------------------------------
  Draft       Initial content under development
  Review      Technical or clinical review in progress
  Approved    Reviewed and approved for publication
  Published   Available to applications and reports
  Archived    Retained for historical reference

------------------------------------------------------------------------

## Food Catalogue

  ----------------------------------------------------------------------------------
  ID     Food   Scientific Name   Food Group    Evidence   Status     Monograph
  ------ ------ ----------------- ------------- ---------- ---------- --------------
  F001   Oats   *Avena sativa*    Wholegrains   High       Approved   F001-oats.md

  ----------------------------------------------------------------------------------

------------------------------------------------------------------------

## Statistics

  Metric          Value
  ------------- -------
  Total Foods         1
  Published           1
  Approved            1
  Draft               0
  Archived            0

------------------------------------------------------------------------

## Naming Convention

### Knowledge Objects

    foods/F001-oats.json

### Monographs

    monographs/F001-oats.md

------------------------------------------------------------------------

## Adding a New Food

1.  Create the next available food ID.
2.  Add the food to `taxonomy/foods.json`.
3.  Create the JSON knowledge object in `foods/`.
4.  Create the Markdown monograph in `monographs/`.
5.  Validate against `schemas/food.schema.json`.
6.  Add the new entry to the catalogue above.
7.  Update the statistics section.

------------------------------------------------------------------------

## Version History

  Version   Date         Notes
  --------- ------------ ----------------------------------
  1.0       2026-07-18   Initial monograph index created.
