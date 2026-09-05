# Personal Wellness Blueprint
## Handover Pack v1.0

**Purpose:** Complete handover for starting a fresh PWB conversation.  
**Current phase:** Questionnaire structural freeze complete; numerical scoring is the next phase.  
**Question count:** 54 displayed question items.

---

# 1. Current project position

PWB is an evidence-informed personalised wellness assessment. It is **not a diagnostic tool**.

The model separates:

1. Wellness evidence from questionnaire responses.
2. Context and priority modifiers such as age, family history, life stage and goals.
3. Safety and recommendation constraints such as medical treatment, allergies and sensitivities.

The nine core outcomes remain:

1. Energy & Vitality
2. Immune Resilience
3. Digestive & Gut Health
4. Mood, Stress & Emotional Balance
5. Sleep & Recovery
6. Cognitive Function
7. Mobility, Joints & Physical Function
8. Circulation & Cardiovascular Fitness
9. Healthy Weight & Metabolic Balance

Healthy Ageing & Resilience is an overlay, not a tenth headline numerical outcome.

The project methodology requires direct evidence to be distinguished from supporting evidence, context, modifiers and safety constraints. Correlated questions must not automatically be treated as independent scoring evidence.

---

# 2. Questionnaire status

## FINAL STRUCTURAL COUNT

**54 displayed question items.**

This is now the working/frozen count for the next phase. Do not reopen the 51/52/53 count discussion unless new evidence requires a genuine questionnaire redesign.

Counting convention:

- Q16b is a conditional displayed item.
- Q24a and Q24b are separate displayed items.
- Q26b is a conditional displayed item.
- Q37b is a conditional displayed item.
- Q44 is one displayed item containing physical-activity and strength measures.
- Q22 replaces the old Q22 construct.
- Q10, Q33 and Q40 are retired.
- Five modern additions are retained:
  - sedentary/prolonged sitting
  - ultra/highly processed food exposure
  - fruit/vegetable intake
  - sleep/circadian routine
  - social connection

Public positioning remains:

> **Over 50 personalised questions directly relating to your overall wellness.**

There is no need to advertise the exact number.

---

# 3. Question decisions that were explicitly finalised in the latest review

## Q22: Social media

Final wording:

> **On a typical day, how much time do you spend using social media?**

Response options:

1. I don't use social media
2. Less than 30 minutes
3. 30 minutes to 1 hour
4. 1 to 2 hours
5. 2 to 3 hours
6. More than 3 hours

Purpose:
- modern behavioural exposure
- potential stress/mood relevance
- potential sedentary relevance
- does not imply that all screen use is harmful

It replaces the old Q22 construct.

---

## Q37: Caffeine amount

Final wording:

> **How many caffeinated drinks do you typically have each day?**

Response options:

1. None
2. 1
3. 2
4. 3
5. 4
6. 5 or more

## Q37b: Caffeine timing

Shown when Q37 is not "None".

Final wording:

> **When do you usually have your last caffeinated drink of the day?**

Response options:

1. Before midday
2. 12pm to 2pm
3. 2pm to 4pm
4. 4pm to 6pm
5. After 6pm
6. It varies considerably

Q37 measures exposure magnitude. Q37b measures timing. They belong to the same caffeine convergence group.

---

## Q44: Physical activity + strength

Final wording:

> **How much moderate or vigorous physical activity do you typically do each week?**

Response options:

1. Less than 30 minutes
2. 30 to 59 minutes
3. 60 to 149 minutes
4. 150 to 299 minutes
5. 300 minutes or more
6. I'm not sure

Follow-up within the same displayed item:

> **How often do you do activities that strengthen your muscles?**

Response options:

1. Never
2. Less than once a week
3. 1 day a week
4. 2 days a week
5. 3 or more days a week

Q44 is one displayed item for counting purposes.

"I'm not sure" must be treated as uncertain/missing evidence, not equivalent to low activity.

---

# 4. Other important question decisions

- **Q01:** goal/context only. Wanting more energy is not evidence of an energy deficit.
- **Q07:** retain and modernise alcohol measurement using typical weekly UK units.
- **Q11:** retain tobacco exposure; passive smoke remains relevant context.
- **Q19:** flat/low motivation belongs primarily to Mood/Stress, not Energy.
- **Q33:** deliberately empty/removed slot.
- **Q40:** removed.
- **Q43:** irritability/frustration is primarily Mood/Stress and must not be used as an independent cardiovascular score.
- **Q46/Q47:** conditional life-stage pair; do not infer menopause from age or gender alone.
- **Q48:** medical treatment/diagnosed long-term condition is safety/context only and must never lower a wellness score.
- Family history belongs in profile/context, not core questionnaire scoring.
- Age is a relevance/priority modifier, not an automatic health-deficit score.

---

# 5. Modern additions

The following are retained as genuine additional constructs:

### Sedentary / prolonged sitting
Purpose:
- captures prolonged sitting independently of exercise
- supports Physical Function, Metabolic and Cardiovascular interpretation

### Ultra/highly processed food exposure
Purpose:
- captures a modern dietary exposure not adequately represented by broad eating-pattern questions
- primarily supports Metabolic/dietary interpretation

### Fruit / vegetable intake
Purpose:
- adds positive dietary behaviour evidence
- complements fibre and broader eating-pattern questions

### Sleep / circadian routine
Purpose:
- complements sleep-quality questions
- captures routine/behaviour rather than sleep outcome alone
- supports Sleep & Recovery

### Social connection / support

Final working wording:

> **How often do you feel you have enough meaningful social connection and support in your life?**

Response options:

1. Always
2. Often
3. Sometimes
4. Rarely
5. Never

Role:
- Direct: Mood, Stress & Emotional Balance
- Supporting: Healthy Ageing overlay
- No direct food/product recommendation

---

# 6. Permanent question ID architecture

Historical identifiers such as Q01, Q37b and N-series identifiers are legacy/provenance identifiers.

Production IDs use:

> **FUNCTIONAL PREFIX + THREE DIGITS**

Prefixes:

| Prefix | Cluster |
|---|---|
| CTX | Context / goals |
| PHY | Physical health & function |
| NUT | Nutrition & metabolism |
| DIG | Digestion & gut |
| MND | Mind, mood & stress |
| SLP | Sleep & recovery |
| LIF | Life stage |
| SAF | Safety & medical context |
| EXP | Environmental / behavioural exposure |
| IMM | Immune resilience |
| COG | Cognitive function |

Rules:
- IDs are permanent once assigned.
- Retired IDs are never reused.
- Legacy IDs remain available for migration/provenance.
- UI section and display order are separate from question identity.
- Scoring relationships are separate from IDs.

---

# 7. Current production ID registry

| # | Production ID | Legacy ID | Construct |
|---:|---|---|---|
| 1 | CTX001 | Q01 | Energy goal |
| 2 | IMM001 | Q02 | Frequency of becoming unwell |
| 3 | DIG001 | Q03 | Bad breath / unusual body odour |
| 4 | DIG002 | Q04 | Difficulty digesting certain foods |
| 5 | NUT001 | Q05 | Red meat frequency |
| 6 | SAF001 | Q06 | Antibiotic / medication context |
| 7 | NUT002 | Q07 | Alcohol exposure |
| 8 | MND001 | Q08 | Mood variability |
| 9 | SAF002 | Q09 | Allergy / sensitivity context |
| 10 | EXP001 | Q11 | Tobacco exposure |
| 11 | COG001 | Q12 | Concentration / memory |
| 12 | IMM002 | Q13 | Susceptibility to illness |
| 13 | DIG003 | Q14 | Post-meal symptoms |
| 14 | MND002 | Q15 | Stress |
| 15 | PHY001 | Q16 | Skin condition |
| 16 | PHY002 | Q16b | Skin-condition impact |
| 17 | NUT003 | Q17 | Food cravings |
| 18 | NUT004 | Q18 | Dairy consumption |
| 19 | MND003 | Q19 | Flat / low motivation |
| 20 | SLP001 | Q20 | Sleep quality |
| 21 | SLP002 | Q21 | Night-time urination |
| 22 | EXP002 | Q22 | Social-media usage |
| 23 | PHY003 | Q23 | Hair shedding / thinning |
| 24 | NUT005 | Q24a | Fried / ready-food exposure |
| 25 | SAF003 | Q24b | Diagnosed lipid issue |
| 26 | NUT006 | Q25 | Weight-management difficulty |
| 27 | PHY004 | Q26 | Physical stamina / capacity |
| 28 | PHY005 | Q26b | Breathlessness follow-up |
| 29 | NUT007 | Q27 | Overall eating pattern |
| 30 | IMM003 | Q28 | Recovery after illness |
| 31 | DIG004 | Q29 | Bowel regularity / comfort |
| 32 | MND004 | Q30 | Difficulty relaxing / switching off |
| 33 | NUT008 | Q31 | Fibre-rich foods |
| 34 | PHY006 | Q32 | Muscle discomfort |
| 35 | EXP003 | Q34 | Air-pollution exposure |
| 36 | SLP003 | Q35 | Daytime sleepiness |
| 37 | CTX002 | Q36 | Persistent appetite loss |
| 38 | SLP004 | Q37 | Caffeine amount |
| 39 | SLP005 | Q37b | Caffeine timing |
| 40 | MND005 | Q38 | Overwhelm / coping difficulty |
| 41 | SAF004 | Q39 | Food / chemical sensitivity |
| 42 | PHY007 | Q41 | Joint pain / stiffness / mobility |
| 43 | MND006 | Q42 | Excessive worry |
| 44 | MND007 | Q43 | Irritability / frustration |
| 45 | PHY008 | Q44 | Physical activity + strength activity |
| 46 | EXP004 | Q45 | Recurrent congestion / mucus |
| 47 | LIF001 | Q46 | Menstrual-cycle concerns |
| 48 | LIF002 | Q47 | Perimenopause / menopause concerns |
| 49 | SAF005 | Q48 | Medical treatment / diagnosed long-term condition |
| 50 | EXP005 | NEW-01 | Sedentary / prolonged sitting |
| 51 | NUT009 | NEW-02 | Ultra/highly processed food |
| 52 | NUT010 | NEW-03 | Fruit / vegetable intake |
| 53 | SLP006 | NEW-04 | Sleep duration / circadian routine |
| 54 | MND008 | NEW-05 | Social connection / support |

Q10, Q33 and Q40 are retired and have no production ID.

---

# 8. Important distinction: question content vs scoring

The questionnaire is now structurally frozen.

Do not mix this with the numerical scoring model.

The next phase must determine:

- how much each response contributes
- Direct versus Supporting contribution
- convergence/double-counting control
- outcome thresholds
- priority thresholds
- age relevance modifiers
- family-history modifiers
- handling of uncertain/missing answers
- conditional evidence
- positive versus negative evidence
- confidence/strength of signal

The question ID must never encode any numerical weight.

---

# 9. Next actions: numerical scoring phase

## Step 1: Define the scoring unit

Decide whether scoring operates on:
- answer-level points,
- normalised question scores,
- or another consistent scale.

The scale must support aggregation without allowing a single question to dominate.

## Step 2: Define response polarity

For every question establish:
- which response is favourable
- which is unfavourable
- which is neutral
- which is uncertain/missing
- whether the question provides positive evidence, negative evidence, or context only

## Step 3: Assign outcome mappings

For every question:
- primary outcome
- secondary/supporting outcomes
- evidence role
- convergence group

Do not assume correlation means independent evidence.

## Step 4: Build convergence controls

Initial clusters to review include:
- immune resilience: Q02/Q13/Q28
- digestive symptoms: Q04/Q14/Q29
- mood/stress: Q08/Q15/Q19/Q30/Q38/Q42/Q43/social connection
- sleep: Q20/Q21/Q30/Q35/Q37/Q37b/N06-equivalent
- physical function: Q26/Q26b/Q32/Q41/Q44/sedentary
- dietary/metabolic: Q05/Q07/Q17/Q18/Q24a/Q25/Q27/Q31/UPF/fruit-vegetable
- cardiovascular exposure: tobacco, alcohol, lipid context, sedentary and diet-related evidence
- life stage: Q46/Q47
- safety: allergies, sensitivities, medical treatment and relevant medication context

These are starting clusters, not final numerical rules.

## Step 5: Define outcome thresholds

Determine:
- low/no concern
- emerging
- moderate priority
- elevated priority

Avoid binary "healthy/unhealthy" labels.

## Step 6: Define modifiers

Age:
- 18-49: normal adult relevance
- 50-64: emerging Healthy Ageing relevance
- 65+: stronger relevance for strength, mobility, balance/function, cardiovascular prevention and cognitive resilience

Age modifies priority/relevance. It does not create health-deficit points.

Family history:
- cardiovascular disease
- type 2 diabetes
- selected cancers
- dementia/Parkinson's
- autoimmune disease
- known inherited/genetic conditions

Again, family history modifies relevance and prevention priority. It does not create current disease.

## Step 7: Test synthetic profiles

Use acceptance profiles covering:
- balanced
- sleep-led
- stress-led
- digestive-led
- lifestyle-led
- multi-signal
- context-heavy
- respiratory-led
- goal-led
- minimal-signal

The model should behave predictably before using historical data.

## Step 8: Historical dataset sanity checks

Use the historical respondent dataset for:
- response distributions
- redundancy checks
- extreme scoring checks
- missingness
- sensitivity to individual questions

Do not fit the new scoring model to the historical dataset and do not describe it as clinical validation.

## Step 9: Freeze scoring specification

Only after the above:
- freeze weights
- freeze thresholds
- freeze convergence rules
- freeze modifier behaviour
- version the scoring engine separately from the questionnaire.

---

# 10. Methodological guardrails

The PWB methodology explicitly requires:

- evidence-informed rather than clinically validated claims
- direct/supporting/context/modifier/safety separation
- conditional applicability
- convergence control
- separation of questionnaire evidence from recommendations
- no diagnostic inference
- no arbitrary disease points from age or family history

The intended processing chain is:

```text
Questionnaire evidence
    ↓
Outcome signal
    ↓
Priority
    ↓
Mechanisms
    ↓
Foods/products
    ↓
Evidence and safety filtering
```

Recommendations must not be allowed to create or retrospectively justify health scores.

---

# 11. Provenance and handover rules

The new conversation must:

1. Read this handover before continuing.
2. Treat the 54-question structural count as the current working freeze.
3. Treat Q22, Q37/Q37b and Q44 as explicitly finalised decisions.
4. Not restart the question review.
5. Not resurrect Q10, Q33 or Q40.
6. Not revert to older 39-, 51-, 52- or 53-question snapshots.
7. Not infer missing wording from older files.
8. Keep numerical scoring separate from question identity.
9. Treat the permanent IDs above as the current registry.
10. Start with numerical scoring methodology.

---

# 12. First task in the new conversation

The first question to ask is:

> **"Before assigning numerical weights, what should the PWB scoring scale and aggregation architecture look like?"**

Then work systematically through:
1. scoring scale,
2. answer polarity,
3. direct/supporting weighting,
4. convergence,
5. thresholds,
6. modifiers,
7. synthetic testing,
8. historical sanity checks,
9. scoring freeze.

Do not jump straight to assigning arbitrary points to individual questions.

---

## Final handover status

**Questionnaire:** 54 displayed items  
**Question structure:** frozen  
**Permanent ID architecture:** established  
**Q22:** frozen  
**Q37/Q37b:** frozen  
**Q44:** frozen  
**Q10/Q33/Q40:** retired  
**Nine outcomes:** frozen  
**Healthy Ageing overlay:** frozen  
**Age/family-history modifier concept:** frozen  
**Numerical scoring:** next phase  
**Clinical validation:** not claimed
