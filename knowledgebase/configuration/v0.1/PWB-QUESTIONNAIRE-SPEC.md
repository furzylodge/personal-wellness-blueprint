# Personal Wellness Blueprint
## Questionnaire Specification Checkpoint

**Version:** 0.3 checkpoint
**Status:** Working architecture, pending canonical inventory reconciliation

## Purpose

Define the current questionnaire architecture without pretending that the final production wording and IDs have already been reconciled across all repository snapshots.

## Current question decisions

| ID / construct | Current decision | Role |
|---|---|---|
| Q01 | Goal/desire context, not health evidence | Goal relevance only |
| Q07 | Quantified alcohol exposure, typical weekly UK units | Wellness/supporting evidence |
| Q11 | Tobacco exposure including passive smoke context | Evidence / risk context |
| Q22 | Replace original question with modern social-media / digital-use question | Supporting wellness evidence |
| Q33 | Removed / empty slot | None |
| Q37 | Extend to amount + relevant timing | Supporting sleep/energy evidence |
| Q40 | Removed / empty slot | None |
| Q44 | Redesign around physical activity and strength activity | Direct/supporting physical-function evidence |
| Q46 | Menstrual-cycle concerns | Conditional life-stage evidence |
| Q47 | Perimenopause / menopause concerns | Conditional life-stage evidence |
| Q48 | Current medical treatment / diagnosed long-term condition | Safety/context only |
| New | Social connection / support | Direct Mood/Stress; supporting Healthy Ageing overlay |
| New | Sedentary / prolonged sitting | Supporting physical, metabolic and cardiovascular relevance |
| New | Ultra/highly processed food exposure | Supporting metabolic/dietary relevance |
| New | Sleep/circadian routine | Supporting Sleep & Recovery |

## Working wording decisions

### Social connection

> How often do you feel you have enough meaningful social connection and support in your life?

Working response scale:

- Always
- Often
- Sometimes
- Rarely
- Never

### Medical context

> Are you currently receiving medical treatment or managing a diagnosed long-term health condition?

A `Prefer not to say` option should be considered in final UX.

## Applicability rules

- Conditional questions must only be shown when their context makes them relevant.
- Q46 and Q47 must not be shown universally.
- Evidence applicability, question applicability and recommendation applicability remain separate concepts.
- Gender-specific evidence must remain context-specific rather than silently becoming generic evidence.

## Count

The working conversation count is **51 displayed question items**, but this is not yet the canonical production count. Older project files contain a 52-question snapshot and a 39-question prototype. The next conversation must reconcile the exact final inventory before implementation.

## Question design principles

- Avoid double-barrelled questions.
- Use defined recall periods where frequency is being measured.
- Prefer measurable exposure over vague labels such as "regularly" or "insufficient" where practical.
- Do not use questions that merely reflect a user's goal as evidence of a health deficit.
- Avoid low-specificity symptoms becoming broad body-system scores.
- Use conditional logic where biological/life-stage applicability matters.
