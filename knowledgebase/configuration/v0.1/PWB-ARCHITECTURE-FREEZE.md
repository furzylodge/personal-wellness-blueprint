# Personal Wellness Blueprint
## Architecture Checkpoint / Handover

**Version:** 0.3 checkpoint
**Status:** Architectural checkpoint before numerical calibration
**Purpose:** Canonical handover for the next PWB conversation

> This is a checkpoint, not yet the final production freeze. The next phase is numerical scoring calibration and empirical testing. Where older project files conflict with decisions made after them, the decisions recorded in this checkpoint are the current working direction, but the final canonical question inventory still needs one reconciliation pass.

## Current direction

PWB is an evidence-informed personalised wellness assessment. It is not a diagnostic tool and must not imply diagnosis or causation from questionnaire responses.

The model separates:

1. **Wellness evidence**: direct and supporting questionnaire signals.
2. **Context / priority modifiers**: age, family health history, life stage and goals.
3. **Safety / recommendation constraints**: medical treatment, allergies, sensitivities and other eligibility information.

## Core outcomes

The scoring model retains **nine core outcomes**:

1. Energy & Vitality
2. Immune Resilience
3. Digestive & Gut Health
4. Mood, Stress & Emotional Balance
5. Sleep & Recovery
6. Cognitive Function
7. Mobility, Joints & Physical Function
8. Circulation & Cardiovascular Fitness
9. Healthy Weight & Metabolic Balance

**Healthy Ageing & Resilience is an overlay / report and recommendation theme, not a tenth headline numerical outcome.** Existing architecture explicitly treats it as an umbrella theme rather than a headline outcome. 

## Questionnaire direction

The working design is intended to be advertised as **over 50 personalised questions directly relating to overall wellness**, with conditional presentation so not every respondent sees every question.

The working count discussed in this conversation is **51 displayed question items**, including conditional items. This count must be reconciled against the latest canonical `questions.json` before production freeze because older repository artefacts contain 39- and 52-question snapshots.

Key decisions already made:

- **Q07 alcohol**: retained and modernised to quantify typical weekly UK units. No additional alcohol question is required.
- **Q22**: original construct is retired/replaced by a modern social-media / digital-use question. The preferred direction is social-media usage because it can capture a modern behavioural exposure with relevance to stress and sedentary behaviour without treating all screen use as harmful.
- **Q33**: empty / removed slot.
- **Q40**: removed / empty slot.
- **Q37**: extend to capture caffeine amount and relevant timing rather than amount alone.
- **Q44**: redesign to capture meaningful physical activity and strength activity, rather than a vague judgement of insufficient exercise.
- **Q46/Q47**: retain as conditional life-stage constructs covering menstrual-cycle concerns and perimenopause/menopause concerns. Do not infer menopause from age or gender alone.
- **Q48**: medical treatment / diagnosed long-term condition. Safety/context only, never a wellness penalty.
- **Social connection**: add as a core questionnaire construct, primarily supporting Mood, Stress & Emotional Balance and secondarily Healthy Ageing interpretation.
- **Modern behavioural additions**: sedentary/prolonged sitting, ultra/highly processed food exposure, and sleep/circadian routine are retained as additional modern signals. The social/digital construct replaces Q22 rather than adding another question.

## Scoring principles already agreed

- Direct evidence is stronger than supporting evidence.
- Supporting evidence corroborates a signal but should not independently manufacture a strong outcome.
- Correlated questions must not be treated as fully independent evidence.
- Convergence should normally be required for moderate/elevated prioritisation rather than allowing one extreme answer to dominate.
- Positive behaviours can contribute positive evidence, not merely reduce negative evidence.
- Goals cannot create a health outcome.
- Medical/context information cannot create a wellness deficit.
- Family history cannot be treated as current disease.
- Exposure alone should not create a symptom/outcome signal where no symptom evidence exists.
- Safety constraints filter recommendations rather than reducing wellness scores.
- The model must be able to return a low-signal / no-priority result rather than force recommendations.
- The questionnaire is non-diagnostic.

These principles are consistent with the current scoring specification, which explicitly separates direct, supporting, context, goal and diagnostic boundaries. 

## Age modifier

Age is an **evidence-based relevance modifier**, not a health penalty.

Working design:

- **18-49:** baseline relevance
- **50-64:** emerging healthy-ageing relevance
- **65+:** stronger relevance

The strongest age-sensitive areas are:

- Mobility, Joints & Physical Function
- Healthy Ageing interpretation

Moderate relevance may apply to:

- Circulation & Cardiovascular Fitness
- Cognitive Function

Smaller relevance may apply to Sleep, Mood/Stress and Energy.

Age must modify **priority/relevance after outcome evidence is generated**, not manufacture outcome points.

## Family health history

Family health history is a **profile/context modifier**, not a scored questionnaire outcome.

High-value areas include:

- premature cardiovascular disease / stroke
- familial high cholesterol
- type 2 diabetes
- significant or patterned cancer history
- dementia / Alzheimer's
- selected autoimmune or inherited conditions

The eventual implementation should distinguish broad family history from higher-value details such as first-degree relative, age at onset and repeated family pattern where those details materially change interpretation.

Family history may increase prevention relevance but must not turn an otherwise healthy questionnaire profile into a disease score.

## Methodology position

The design should be described publicly as **evidence-informed / evidence-led**, not clinically validated, until formal empirical validation has been completed.

The development process has included:

- outcome definition
- question-by-question review
- evidence and relevance assessment
- removal of weak or non-specific proxies
- modernisation of outdated wording
- conditional applicability
- separation of direct/supporting/context/safety information
- redundancy and convergence consideration
- age and family-history modifiers
- planned empirical testing against synthetic acceptance profiles and historical respondent data

## Immediate next step

1. Reconcile the canonical current question inventory against the latest `questions.json` and all decisions above.
2. Produce the final structural question-to-outcome matrix.
3. Calibrate relative numerical weights.
4. Run the ten synthetic acceptance profiles.
5. Use the 1,390-record historical-derived dataset for regression/sanity checks, redundancy analysis and distribution inspection only. It must not be used to fit new weights or treated as clinical validation.
6. Freeze the scoring model and update this document to the next version.
