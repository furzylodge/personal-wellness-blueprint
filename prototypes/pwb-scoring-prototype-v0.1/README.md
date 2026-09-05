# PWB Signal Scoring Prototype v0.1

This is the first implementation layer for the new PWB scoring model.

## Deliberate scope

This prototype:

- uses the current ten-outcome signal taxonomy;
- preserves primary, supporting and context evidence roles;
- consumes already-normalised question values;
- calculates primary evidence intensity;
- records supporting evidence and convergence inputs;
- preserves provenance;
- does not calculate a medical/health score;
- does not yet assign Weak/Moderate/Strong thresholds;
- does not contain recommendation, food or product logic.

## Why thresholds are absent

The current signal specification defines the qualitative signal rules but explicitly leaves numerical weights and thresholds for calibration.

Therefore this prototype must not invent those values.

## Input contract

Question answers supplied to `SignalScoringEngine` must already be normalised to 0.0-1.0 according to `pwb-2026-question-definition-v0.7`.

## Next calibration step

Run this against the agreed synthetic personas and benchmark data, then determine:

1. evidence convergence rules;
2. correlation/double-counting treatment;
3. Weak/Moderate/Strong thresholds;
4. priority calculation.

Only after those are calibrated should this become the production scoring implementation.
