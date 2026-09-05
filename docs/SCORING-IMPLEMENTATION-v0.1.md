# PWB 2026 scoring implementation v0.1

This is the first implementation slice of the 2026 scoring architecture.

## Deliberate boundaries

This code does **not** contain final numerical evidence weights. The project specification explicitly keeps those undefined until calibration is complete.

It implements only:

1. response normalisation;
2. neutral construct aggregation;
3. outcome-level structural grouping;
4. correlated-cluster separation;
5. provenance of the rule version.

It does not yet implement:

- final signal thresholds;
- goal priority boosts;
- modifiers;
- safety/recommendation constraints;
- food recommendations;
- product recommendations;
- commercial ranking;
- production report wording.

Those remain separate stages.

## Canonical answer representation

The scoring layer should receive **stable response positions**, not display text:

```php
$answers = [
    'Q20' => 3,
    'Q35' => 2,
];
```

For a five-option ordinal question, the positions are `0..4` and are normalised to `0.00..1.00`.

This prevents scoring from depending on wording changes.

## Outcome structure

The engine preserves the current architecture:

- Q20/Q35: independent Sleep & Recovery constructs;
- Q02/Q13/Q28: correlated Immune Resilience cluster;
- Q15/Q30/Q42/Q43: balanced Mood/Stress facets;
- Q04/Q14/Q29: Digestive & Gut primary convergence;
- Circulation: convergence only because there is no agreed direct 2026 question.

## Important calibration issue

The existing synthetic acceptance fixture still contains many legacy response labels that do not match the current interaction-design response options. Therefore the fixture must not be silently translated by the scoring engine. It needs to be regenerated or given stable response keys before numerical calibration is run.
