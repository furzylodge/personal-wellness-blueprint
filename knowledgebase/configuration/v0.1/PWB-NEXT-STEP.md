# Personal Wellness Blueprint
## Next Conversation Starting Point

**Checkpoint:** v0.3

### Do not restart the questionnaire review from scratch.

The previous conversation has completed the conceptual questionnaire review and established the scoring architecture direction.

### Read first

1. `PWB-ARCHITECTURE-FREEZE.md`
2. `PWB-QUESTIONNAIRE-SPEC.md`
3. `PWB-SCORING-ARCHITECTURE.md`
4. `PWB-QUESTION-DECISION-LOG.md`
5. `PWB-METHODOLOGY.md`

### Immediate task

**Reconcile the canonical current question inventory against the latest repository `questions.json` and the decisions in the checkpoint documents.**

Do not assume that older 39-question or 52-question artefacts are current.

Then:

1. produce the definitive question list;
2. map every question to the nine outcomes using Direct / Supporting / Context / Modifier / Safety;
3. assign convergence groups;
4. identify any remaining mapping errors;
5. freeze the structural matrix;
6. calibrate relative numerical weights;
7. run the ten synthetic acceptance profiles;
8. inspect the historical-derived respondent dataset for sanity/distribution/redundancy checks;
9. freeze the numerical scoring model;
10. update the architecture and methodology documents to the next version.

### Important constraints

- Do not treat goals as health evidence.
- Do not treat medical conditions or family history as current wellness deficits.
- Do not let correlated questions double-count.
- Do not force recommendations for low-signal respondents.
- Do not make diagnostic claims.
- Do not use the historical-derived scores as the target for fitting the new weights.

### Current strategic direction

The questionnaire should be marketable as **over 50 personalised questions directly relating to overall wellness**, with conditional display so respondents only see relevant questions.

The next major workstream is **numerical scoring calibration**, not another broad questionnaire redesign, unless the canonical inventory reconciliation reveals a genuine unresolved conflict.
