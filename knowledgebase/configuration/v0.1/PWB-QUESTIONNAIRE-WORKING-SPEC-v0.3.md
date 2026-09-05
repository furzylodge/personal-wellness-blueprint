# Personal Wellness Blueprint
## Questionnaire Working Specification - Conversation Checkpoint

**Version:** 0.3  
**Status:** WORKING SPECIFICATION - NOT FROZEN  
**Purpose:** Preserve the latest agreed questionnaire state so the next PWB conversation can continue without reconstructing decisions from conversation history.

---

## Important status note

This document is a **working snapshot**, not the final production questionnaire.

The current project repository contains older questionnaire snapshots, including a 52-question version and earlier prototypes. The latest conversation decisions have superseded parts of those files, but the exact final inventory still needs reconciliation against the canonical `questions.json` before implementation.

The working conversation count was **51 displayed question items**, including conditional items. This count remains provisional until the canonical inventory is reconciled.

**Do not infer missing wording from older files.** Where this checkpoint does not preserve exact final wording, it records the agreed construct/decision and marks the wording for reconciliation.

---

# 1. Current questionnaire architecture

The questionnaire is intended to be presented as:

> **Over 50 personalised questions directly relating to your overall wellness.**

Not every respondent will necessarily see every question. Conditional questions are shown or hidden according to previous selections and relevant life-stage/context information.

The questionnaire is designed to provide a personalised wellness profile, not a medical diagnosis.

---

# 2. Question inventory and current status

| ID | Current working status / construct | Exact wording status | Notes |
|---|---|---|---|
| Q01 | Energy goal / desired improvement | Preserved: **“Would you like to have more energy during the day?”** | Goal relevance only. Does not create health deficit. |
| Q02 | Frequency of becoming unwell | Preserved in earlier spec: **“How often do you find yourself becoming unwell?”** | Immune/resilience signal. Recall-period refinement remains relevant. |
| Q03 | Bad breath / unusual body odour | Earlier wording preserved: **“Which of the following have you noticed recently or repeatedly? Select any that apply.”** | Low/contextual signal. Do not treat as generic gut-health diagnosis. |
| Q04 | Difficulty digesting certain foods | Preserved: **“How often do you have difficulty digesting certain foods?”** | Direct Digestive & Gut Health signal. |
| Q05 | Red meat frequency | Preserved in earlier spec: **“How often do you eat red meat, such as beef, lamb or pork?”** | Dietary supporting signal. |
| Q06 | Antibiotic / medication context | Current construct retained | Context/safety; exact final wording requires canonical reconciliation. |
| Q07 | Alcohol exposure | **Quantified weekly UK units** | Retained and modernised. Typical weekly units, not vague “regularly”. |
| Q08 | Mood variability | Retained | Mood/Stress cluster. Exact final wording requires reconciliation. |
| Q09 | Allergy / sensitivity context | Retained | Safety/filtering, not wellness deficit. |
| Q10 | **Removed** | N/A | Not part of current working set. |
| Q11 | Tobacco exposure | Retained | High-impact lifestyle evidence. Passive smoke remains relevant context. |
| Q12 | Concentration / memory | Retained | Cognitive signal. |
| Q13 | Susceptibility to illness | Retained | Immune/resilience cluster; interpret with Q02/Q28. |
| Q14 | Post-meal symptoms | Retained | Direct/supporting Digestive & Gut Health signal. |
| Q15 | Stress | Retained | Direct Mood/Stress signal. |
| Q16 | Skin condition | Retained with conditional follow-up | Q16b captures impact/severity where applicable. |
| Q16b | Impact of skin condition | Conditional | Shown only when Q16 establishes relevance. |
| Q17 | Food cravings | Retained | Supporting metabolic/energy signal. |
| Q18 | Dairy consumption | Retained | Dietary context/supporting evidence. |
| Q19 | Flat / low motivation | Retained | Primarily Mood/Stress, not Energy. |
| Q20 | Sleep quality | Retained | Direct Sleep & Recovery signal. |
| Q21 | Night-time urination | Retained | Context/supporting sleep signal; non-specific. |
| Q22 | **Modern social-media / digital-use question** | Working wording not frozen | Replaces original Q22. Intended to capture social-media/digital exposure, with stress and sedentary relevance. |
| Q23 | Hair shedding/thinning | Retained provisionally | Low-specificity/contextual signal; avoid broad system scoring. |
| Q24a | Fried/ready-food exposure | Retained | Dietary/metabolic supporting signal. |
| Q24b | Diagnosed lipid issue | Retained as context/modifier | Does not create a wellness deficit by itself. |
| Q25 | Weight-management difficulty | Retained | Metabolic signal. |
| Q26 | Physical stamina/capacity | Retained | Energy/physical-function signal. |
| Q26b | Breathlessness follow-up | Conditional | Used where Q26 establishes relevance; non-diagnostic. |
| Q27 | Overall eating pattern | Retained | Dietary/metabolic supporting evidence. |
| Q28 | Recovery after illness | Retained | Immune/resilience signal. |
| Q29 | Bowel regularity/comfort | Retained | Direct Digestive & Gut Health signal. |
| Q30 | Difficulty relaxing / switching off | Retained | Mood/Stress and Sleep convergence cluster. |
| Q31 | Fibre-rich foods | Retained | Gut/metabolic supporting evidence. |
| Q32 | Muscle discomfort | Retained | Physical-function supporting/direct signal. |
| Q33 | **EMPTY / UNUSED SLOT** | N/A | Deliberately left open. Do not repopulate without a new design decision. |
| Q34 | Air-pollution exposure | Retained | Exposure/context; cannot by itself establish respiratory disease. |
| Q35 | Daytime sleepiness | Retained | Direct/supporting Sleep & Recovery signal. |
| Q36 | Persistent appetite loss | Retained as context | Non-specific; should not create broad health deficits. |
| Q37 | **Caffeine amount + timing** | Extended, final wording not frozen | Amount alone was insufficient; timing added because of sleep relevance. |
| Q38 | Overwhelm / coping difficulty | Retained | Mood/Stress convergence cluster. |
| Q39 | Food/chemical sensitivity | Retained | Safety/filtering. |
| Q40 | **REMOVED** | N/A | Deliberately removed. |
| Q41 | Joint pain / stiffness / mobility | Retained | Direct Mobility, Joints & Physical Function signal. |
| Q42 | Excessive worry | Retained | Direct Mood/Stress convergence cluster. |
| Q43 | Irritability / frustration | Retained | Direct Mood/Stress. Do not use as an independent CVD score. |
| Q44 | **Physical activity + strength activity** | Redesigned, final wording not frozen | Must measure actual activity and strength rather than subjective “insufficient exercise”. |
| Q45 | Recurrent congestion / mucus | Retained | Respiratory signal. |
| Q46 | Menstrual-cycle concerns | Conditional | Life-stage context; not universally displayed. |
| Q47 | Perimenopause / menopause concerns | Conditional | Life-stage context; not universally displayed. |
| Q48 | Medical treatment / diagnosed long-term condition | Working wording preserved: **“Are you currently receiving medical treatment or managing a diagnosed long-term health condition?”** | Safety/context only. Consider “Prefer not to say” in final UX. |

---

# 3. Modern additions / N-series

The exact final IDs for these additions need to be reconciled with the latest canonical question file. The project contains historical N-series naming that has changed during the review, so the constructs are preserved here independently of final ID assignment.

### Social-media / digital behaviour
This is the modern replacement for Q22.

Purpose:
- current social-media/digital exposure
- potential stress relevance
- potential sedentary relevance
- should not imply that all screen use is harmful

### Sedentary / prolonged sitting
Purpose:
- captures prolonged sitting independently of exercise
- supports Physical Function, Metabolic and Cardiovascular interpretation

### Ultra/highly processed food exposure
Purpose:
- captures a modern dietary exposure not adequately represented by broad eating-pattern questions
- primarily supports Metabolic and dietary interpretation

### Sleep/circadian routine
Purpose:
- complements sleep-quality questions
- captures routine/behaviour rather than sleep outcome alone
- supports Sleep & Recovery

### Social connection / support
**Working wording:**

> **How often do you feel you have enough meaningful social connection and support in your life?**

Working response scale:

- Always
- Often
- Sometimes
- Rarely
- Never

Role:
- Direct: Mood, Stress & Emotional Balance
- Supporting: Healthy Ageing overlay
- No direct food/product recommendation

This is an agreed new construct, not merely a future candidate.

---

# 4. Family health history

Family health history is **not part of the 51 scored questionnaire items**.

It belongs in the profile/context layer.

Agreed principle:

- premature cardiovascular disease: strong cardiovascular relevance modifier
- type 2 diabetes: metabolic relevance modifier
- selected cancer patterns: medical-risk context, not a wellness score
- dementia/Parkinson’s: healthy-ageing context
- autoimmune disease: contextual relevance
- known inherited/genetic condition: medical/safety context

Family history must **not** manufacture current disease or lower a person's wellness score.

---

# 5. Age relevance modifier

Age is also **not an additional questionnaire question**.

Agreed architecture:

### 18-49
Normal adult relevance.

### 50-64
Emerging healthy-ageing relevance.

### 65+
Stronger relevance, particularly for:
- strength
- mobility
- balance/functional capacity
- cardiovascular prevention
- cognitive resilience

Age modifies **priority/relevance**, not the underlying health evidence.

It must not operate as:

> “Age over 60 = additional health-deficit points.”

Healthy Ageing is an **overlay**, not a tenth headline outcome.

---

# 6. Current outcome architecture

Nine core outcomes remain:

1. Energy & Vitality
2. Immune Resilience
3. Digestive & Gut Health
4. Mood, Stress & Emotional Balance
5. Sleep & Recovery
6. Cognitive Function
7. Mobility, Joints & Physical Function
8. Circulation & Cardiovascular Fitness
9. Healthy Weight & Metabolic Balance

Healthy Ageing & Resilience is an interpretive/priority overlay, not a tenth core score.

---

# 7. Scoring role rules already agreed

Every question/field must eventually be classified as:

- **Direct** - direct evidence for an outcome
- **Supporting** - weaker but useful supporting evidence
- **Context** - informative but not a score contributor
- **Modifier** - changes priority/relevance after outcome evidence is established
- **Safety** - constrains recommendations; never creates a wellness deficit

Additional rule:

> Correlation is not automatically independent scoring evidence.

Correlated question clusters require convergence/double-counting control.

---

# 8. Important unresolved items for the next conversation

These are intentionally **not frozen**:

1. Reconcile the complete canonical 51-item inventory.
2. Confirm exact final IDs for the modern/N-series additions.
3. Confirm exact final wording for Q22, Q37, Q44 and the modern additions.
4. Confirm the final Q16/Q16b structure.
5. Map every final question to the nine outcomes.
6. Assign convergence groups.
7. Determine relative weights.
8. Test the numerical model against the 10 synthetic acceptance profiles.
9. Run regression/sanity checks against the historical respondent dataset.
10. Freeze the numerical scoring specification.

---

# 9. Source/provenance rule

This snapshot deliberately distinguishes:

- wording that is preserved in the available project specifications,
- decisions made during the current conversation,
- and items whose exact wording still needs reconciliation.

It must **not** be treated as permission to silently reconstruct missing wording from older files.

The existing project evidence specification requires source, review date and evidence scope, and explicitly states that evidence supporting why a question is relevant does not automatically support a numerical scoring relationship.

---

## Handover instruction

The next PWB conversation should read this document **before continuing the questionnaire work**.

It should not restart the question review.

The immediate task is:

> **Reconcile this working specification against the latest canonical question file, resolve any remaining wording/ID discrepancies, then freeze the structural questionnaire before numerical scoring calibration.**
