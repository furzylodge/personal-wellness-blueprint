# Personal Wellness Blueprint
## Questionnaire Registry v1.0

**Status:** STRUCTURAL FREEZE
**Displayed question items:** 54
**Numerical scoring:** not yet calibrated

## Purpose

This registry is the structural source of truth for the questionnaire. It records the current question set, permanent production IDs, legacy identifiers, constructs and evidence roles. It deliberately does not contain numerical weights.

## Counting convention

54 means 54 displayed question items. Q44 is one displayed item containing the physical-activity measure and its strength follow-up. Q16b, Q26b and Q37b remain conditional items. Q24a and Q24b are separate items. Q10, Q33 and Q40 are retired.

## Registry

| # | Production ID | Legacy ID | Construct | Wording / status | Evidence role |
|---:|---|---|---|---|---|
| 1 | `CTX001` | Q01 | Energy goal / desired improvement | Would you like to have more energy during the day? | Goal/context |
| 2 | `IMM001` | Q02 | Frequency of becoming unwell | How often do you find yourself becoming unwell? | Direct/supporting |
| 3 | `DIG001` | Q03 | Bad breath / unusual body odour | Which of the following have you noticed recently or repeatedly? Select any that apply. | Context |
| 4 | `DIG002` | Q04 | Difficulty digesting certain foods | How often do you have difficulty digesting certain foods? | Direct |
| 5 | `NUT001` | Q05 | Red meat frequency | How often do you eat red meat, such as beef, lamb or pork? | Supporting |
| 6 | `SAF001` | Q06 | Antibiotic / medication context | Retained construct; exact final wording requires canonical reconciliation. | Context/safety |
| 7 | `NUT002` | Q07 | Alcohol exposure | Quantified typical weekly UK units; exact final response wording requires committed question set. | Supporting |
| 8 | `MND001` | Q08 | Mood variability | Retained construct; exact final wording requires canonical reconciliation. | Supporting |
| 9 | `SAF002` | Q09 | Allergy / sensitivity context | Retained construct; exact final wording requires canonical reconciliation. | Safety |
| 10 | `EXP001` | Q11 | Tobacco exposure | Retained construct; exact final wording requires canonical reconciliation. | Evidence/context |
| 11 | `COG001` | Q12 | Concentration / memory | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 12 | `IMM002` | Q13 | Susceptibility to illness | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 13 | `DIG003` | Q14 | Post-meal symptoms | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 14 | `MND002` | Q15 | Stress | Retained construct; exact final wording requires canonical reconciliation. | Direct |
| 15 | `PHY001` | Q16 | Skin condition | Retained with conditional follow-up. | Context/supporting |
| 16 | `PHY002` | Q16b | Impact of skin condition | Conditional follow-up shown when Q16 establishes relevance. | Conditional |
| 17 | `NUT003` | Q17 | Food cravings | Retained construct; exact final wording requires canonical reconciliation. | Supporting |
| 18 | `NUT004` | Q18 | Dairy consumption | Retained construct; exact final wording requires canonical reconciliation. | Supporting/context |
| 19 | `MND003` | Q19 | Flat / low motivation | Retained construct; primarily Mood/Stress, not Energy. | Direct |
| 20 | `SLP001` | Q20 | Sleep quality | Retained construct; exact final wording requires canonical reconciliation. | Direct |
| 21 | `SLP002` | Q21 | Night-time urination | Retained construct; non-specific sleep/context signal. | Context/supporting |
| 22 | `EXP002` | Q22 | Social-media usage | On a typical day, how much time do you spend using social media? | Supporting |
| 23 | `PHY003` | Q23 | Hair shedding / thinning | Retained construct; exact final wording requires canonical reconciliation. | Context |
| 24 | `NUT005` | Q24a | Fried / ready-food exposure | Retained construct; exact final wording requires canonical reconciliation. | Supporting |
| 25 | `SAF003` | Q24b | Diagnosed lipid issue | Retained as context/modifier; does not itself create a wellness deficit. | Context/modifier |
| 26 | `NUT006` | Q25 | Weight-management difficulty | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 27 | `PHY004` | Q26 | Physical stamina / capacity | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 28 | `PHY005` | Q26b | Breathlessness follow-up | Conditional follow-up where Q26 establishes relevance. | Conditional |
| 29 | `NUT007` | Q27 | Overall eating pattern | Retained construct; exact final wording requires canonical reconciliation. | Supporting |
| 30 | `IMM003` | Q28 | Recovery after illness | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 31 | `DIG004` | Q29 | Bowel regularity / comfort | Retained construct; exact final wording requires canonical reconciliation. | Direct |
| 32 | `MND004` | Q30 | Difficulty relaxing / switching off | Retained construct; Mood/Stress and Sleep convergence. | Direct/supporting |
| 33 | `NUT008` | Q31 | Fibre-rich foods | Retained construct; exact final wording requires canonical reconciliation. | Supporting |
| 34 | `PHY006` | Q32 | Muscle discomfort | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 35 | `EXP003` | Q34 | Air-pollution exposure | Retained exposure/context construct; cannot alone establish respiratory disease. | Context/supporting |
| 36 | `SLP003` | Q35 | Daytime sleepiness | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 37 | `CTX002` | Q36 | Persistent appetite loss | Retained as non-specific context. | Context |
| 38 | `SLP004` | Q37 | Caffeine amount | How many caffeinated drinks do you typically have each day? | Supporting |
| 39 | `SLP005` | Q37b | Caffeine timing | When do you usually have your last caffeinated drink of the day? | Conditional supporting |
| 40 | `MND005` | Q38 | Overwhelm / coping difficulty | Retained construct; exact final wording requires canonical reconciliation. | Direct/supporting |
| 41 | `SAF004` | Q39 | Food / chemical sensitivity | Retained safety/filtering construct; exact final wording requires canonical reconciliation. | Safety |
| 42 | `PHY007` | Q41 | Joint pain / stiffness / mobility | Retained construct; exact final wording requires canonical reconciliation. | Direct |
| 43 | `MND006` | Q42 | Excessive worry | Retained construct; exact final wording requires canonical reconciliation. | Direct |
| 44 | `MND007` | Q43 | Irritability / frustration | Retained construct; primarily Mood/Stress; not an independent CVD score. | Direct |
| 45 | `PHY008` | Q44 | Physical activity + strength activity | How much moderate or vigorous physical activity do you typically do each week? Follow-up: How often do you do activities that strengthen your muscles? | Direct/supporting |
| 46 | `EXP004` | Q45 | Recurrent congestion / mucus | Retained respiratory construct; exact final wording requires canonical reconciliation. | Context/supporting |
| 47 | `LIF001` | Q46 | Menstrual-cycle concerns | Conditional life-stage question. | Conditional |
| 48 | `LIF002` | Q47 | Perimenopause / menopause concerns | Conditional life-stage question. | Conditional |
| 49 | `SAF005` | Q48 | Medical treatment / diagnosed long-term condition | Are you currently receiving medical treatment or managing a diagnosed long-term health condition? | Safety/context |
| 50 | `EXP005` | NEW-01 | Sedentary / prolonged sitting | Modern addition; final committed wording required. | Supporting |
| 51 | `NUT009` | NEW-02 | Ultra / highly processed food exposure | Modern addition; final committed wording required. | Supporting |
| 52 | `NUT010` | NEW-03 | Fruit / vegetable intake | Modern addition; final committed wording required. | Supporting |
| 53 | `SLP006` | NEW-04 | Sleep duration / circadian routine | Modern addition; final committed wording required. | Supporting |
| 54 | `MND008` | NEW-05 | Social connection / support | How often do you feel you have enough meaningful social connection and support in your life? | Direct/supporting |

## Permanent ID rules

- Production IDs use a functional prefix plus three digits.
- Legacy Q/N identifiers are retained for provenance.
- Production IDs are permanent and must not be recycled.
- UI section and display order are separate fields from question identity.
- A materially different construct receives a new production ID.

## Retired identifiers

- Q10: removed
- Q33: empty / removed
- Q40: removed

## Frozen review decisions

- Q22 is the modern social-media question and replaces the original Q22 construct.
- Q37 and Q37b separately capture caffeine amount and timing.
- Q44 captures physical activity and strength activity as one displayed question item.
- Q46 and Q47 are conditional life-stage questions.
- Q48 is safety/context only and cannot create a wellness deficit.
- Sedentary behaviour, UPF exposure, fruit/vegetable intake, sleep/circadian routine and social connection are retained as modern additions.

## Outcome architecture

1. Energy & Vitality
2. Immune Resilience
3. Digestive & Gut Health
4. Mood, Stress & Emotional Balance
5. Sleep & Recovery
6. Cognitive Function
7. Mobility, Joints & Physical Function
8. Circulation & Cardiovascular Fitness
9. Healthy Weight & Metabolic Balance

Healthy Ageing & Resilience remains an overlay rather than a tenth headline outcome.

## Wording provenance rule

Where the working specification did not preserve exact final wording, this registry does not reconstruct it from older snapshots. Such entries are explicitly marked for canonical reconciliation. This follows the project's stated rule not to infer missing wording from older files.

## Next phase

1. Confirm the remaining exact wording and response options in the implementation source.
2. Assign final UI sections and display order.
3. Complete outcome mappings.
4. Complete convergence groups.
5. Develop numerical scoring weights and thresholds.
6. Test synthetic acceptance profiles.
7. Run historical-data sanity and redundancy checks.

**Question set/count: FROZEN at 54 displayed items.**
**Scoring model: NEXT PHASE.**