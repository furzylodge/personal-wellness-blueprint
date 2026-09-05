# Personal Wellness Blueprint
## Question ID Architecture

**Version:** 0.1  
**Status:** DESIGN DECISION - AGREED

### 1. Decision

Historical identifiers such as `Q01`, `Q37b` and `N01` will not be permanent production identifiers. They remain legacy/provenance identifiers.

Production questions will use:

> **FUNCTIONAL PREFIX + THREE-DIGIT SEQUENCE**

Examples: `MND001`, `SLP001`, `PHY001`, `NUT001`, `DIG001`, `EXP001`, `LIF001`, `SAF001`.

The prefix describes the question's **primary questionnaire construct/UI cluster**, not every wellness outcome it may influence.

### 2. Functional prefixes

| Prefix | Cluster | Typical constructs |
|---|---|---|
| `PHY` | Physical health & function | stamina, activity, strength, muscle discomfort, mobility |
| `NUT` | Nutrition & metabolism | food patterns, alcohol, fibre, UPF, weight-related behaviour |
| `DIG` | Digestion & gut | digestive symptoms, post-meal symptoms, bowel function |
| `MND` | Mind, mood & stress | stress, worry, motivation, irritability, social connection |
| `SLP` | Sleep & recovery | sleep quality, daytime sleepiness, caffeine timing, circadian routine |
| `LIF` | Life stage | menstrual cycle, perimenopause, menopause |
| `SAF` | Safety & medical context | treatment, diagnosed conditions, allergies, sensitivities |
| `EXP` | Environmental / behavioural exposure | tobacco, air pollution, sedentary behaviour, social-media exposure |

### 3. Identity is separate from UI placement

A question has three independent properties:

- **Permanent ID**, e.g. `MND004`
- **UI section**, e.g. `mind_emotional`
- **Display order**, e.g. `17`

Display order can change without changing the question ID.

### 4. ID stability rules

1. IDs are permanent once assigned.
2. Retired IDs are never reused.
3. New questions receive the next available sequence in their prefix.
4. Wording refinement does not normally require a new ID.
5. A materially different construct requires a new ID.
6. Every migrated question retains its historical identifier where one exists.

Example:

```yaml
id: MND004
legacy_id: Q38
status: active
```

New question:

```yaml
id: MND007
legacy_id: null
status: active
```

Retired historical question:

```yaml
legacy_id: Q33
id: null
status: retired
```

### 5. UI clustering

Suggested user-facing sections:

- Mind & Emotional Wellbeing
- Sleep & Recovery
- Nutrition & Metabolism
- Physical Health & Function
- Digestion & Gut
- Life Stage
- Personal, Environmental & Behavioural Factors
- Health & Safety Context

The UI may interleave questions for better user experience. IDs must not be used as display ordering.

### 6. Scoring separation

Question IDs do not encode scoring relationships. These remain separate fields:

```text
question_id
primary_construct
ui_section
display_order
outcome_mappings
evidence_role
convergence_group
applicability
response_type
legacy_id
version_introduced
version_retired
```

A question can therefore be, for example:

```text
question_id: MND004
primary_construct: stress
outcome_mappings:
  - Mood, Stress & Emotional Balance: Direct
  - Sleep & Recovery: Supporting
convergence_group: stress_cluster
```

### 7. Relationship to the current questionnaire

This redesign **does not change the agreed 51-question working count**. It replaces the historical identity scheme only.

The exact mapping of all 51 questions to permanent IDs is the next reconciliation task. Older 39- and 52-question repository snapshots must not be used to alter the current count.

### 8. Current migration examples

| Legacy | Proposed ID | Primary construct |
|---|---|---|
| Q07 | `NUT001` | Alcohol exposure |
| Q11 | `EXP001` | Tobacco exposure |
| Q15 | `MND001` | Stress |
| Q19 | `MND002` | Flat / low motivation |
| Q20 | `SLP001` | Sleep quality |
| Q22 | `EXP002` | Social-media / digital exposure |
| Q29 | `DIG001` | Bowel regularity |
| Q30 | `MND003` | Difficulty switching off |
| Q35 | `SLP002` | Daytime sleepiness |
| Q37 | `SLP003` | Caffeine amount |
| Q37b | `SLP004` | Caffeine timing |
| Q38 | `MND004` | Overwhelm / coping |
| Q42 | `MND005` | Excessive worry |
| Q43 | `MND006` | Irritability / frustration |
| Q44 | `PHY001` | Physical activity |
| Q44b | `PHY002` | Strength activity |
| Q46 | `LIF001` | Menstrual-cycle concerns |
| Q47 | `LIF002` | Perimenopause / menopause |
| Q48 | `SAF001` | Medical treatment / diagnosed condition |
| Modern addition | `EXP003` | Sedentary behaviour |
| Modern addition | `NUT002` | Ultra/highly processed food |
| Modern addition | `SLP005` | Sleep/circadian routine |
| Modern addition | `MND007` | Social connection |

These examples are not the final 51-row registry until the working inventory is reconciled.

### 9. What this does not change

It does not change:

- the 51-question working count
- the nine core outcomes
- Healthy Ageing as an overlay
- the age modifier
- the family-history modifier
- Direct / Supporting / Context / Modifier / Safety
- questionnaire decisions already agreed
- the scoring methodology

### 10. Next action

Create the permanent 51-question registry by:

1. taking the current working specification;
2. assigning each question a permanent ID;
3. retaining its legacy Q/N identifier;
4. assigning primary construct and UI section;
5. assigning display order separately;
6. assigning applicability;
7. mapping outcomes and evidence role;
8. assigning convergence groups;
9. freezing the registry.

**ID architecture: AGREED.**

**Permanent 51-question registry: NOT YET CREATED.**
