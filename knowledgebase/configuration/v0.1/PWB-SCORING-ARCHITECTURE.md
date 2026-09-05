# Personal Wellness Blueprint
## Scoring Architecture Checkpoint

**Version:** 0.3 checkpoint
**Status:** Structural architecture agreed; numerical weights not frozen

## Five information roles

### 1. Direct evidence

A question directly measures the relevant wellness construct and carries the main outcome signal.

### 2. Supporting evidence

A question corroborates or explains an outcome signal but should not independently manufacture a strong outcome.

### 3. Context

Information useful for interpretation but not itself a wellness deficit.

### 4. Modifier

Information that changes relevance or priority after the underlying outcome evidence has been calculated.

### 5. Safety / filtering

Information that can remove or constrain recommendations but must not reduce a wellness score.

## Processing sequence

```text
Response
  -> answer normalisation
  -> construct scoring
  -> within-outcome clustering / convergence control
  -> nine core outcome signals
  -> context and priority modifiers
       - age
       - family health history
       - life stage
       - goals
  -> outcome priority
  -> mechanism mapping
  -> food/product ranking
  -> safety and eligibility filtering
```

The existing provisional scoring-stage specification uses the same sequence: answer normalisation, construct scoring, within-outcome clustering, modifiers, constraints, outcome priority, domain mapping and recommendation ranking.

## Convergence / double counting

Correlated questions should not be treated as fully independent evidence.

Important clusters to test include:

- Stress: Q15, Q30, Q38, Q42, Q43
- Sleep: Q20, Q35, Q30, Q37, sleep/circadian routine and relevant supporting questions
- Immune/resilience: Q02, Q13, Q28
- Digestive: Q04, Q14, Q29
- Activity/function: Q26, Q32, Q41, Q44, sedentary behaviour
- Diet/metabolic: Q17, Q24a, Q27, Q31, UPF and fruit/vegetable intake
- Respiratory: Q26b, Q34, Q45

The final numerical implementation should use diminishing returns, cluster caps or equivalent controls rather than simply summing all correlated items.

## Relative weighting

The previous experimental prototype used Direct = 1.0 and Supporting = 0.35. Those values are explicitly experimental and must not be treated as production weights.

Current working hierarchy for calibration:

- Direct evidence > Supporting evidence
- Strong direct > broad/weak direct
- Strong supporting > weak supporting
- Context / Modifier / Safety = no direct wellness points

A provisional calibration may test tiers such as D1/D2/D3 and S1/S2/S3, but the actual numerical values remain to be established through synthetic profile testing and distribution checks.

## Outcome confidence

PWB should distinguish **amount of signal** from **confidence in what the questionnaire can infer**.

Questionnaire-derived cardiovascular evidence, for example, should not be presented as equivalent to measured blood pressure, cholesterol or clinical cardiovascular risk.

The model must remain non-diagnostic.

## Goals

Goals personalise interpretation and recommendation priority. They must not create a health deficit.

A goal may break ties or increase relevance among outcomes already supported by questionnaire evidence.

## Age

Age is a relevance modifier, not a health penalty.

Working transitions:

- 18-49 baseline
- 50-64 emerging relevance
- 65+ stronger relevance

The modifier should particularly affect interpretation of strength, mobility, functional capacity and healthy ageing, with moderate relevance to cardiovascular and cognitive priorities.

## Family history

Family history is a risk/relevance modifier, not current health evidence.

It should increase prevention relevance when appropriate, but never manufacture disease or a poor wellness score.

## Healthy Ageing

Healthy Ageing & Resilience is an overlay/report/recommendation theme, not a tenth core outcome.

## Priority output

The current architecture targets a maximum of three priority outcomes. A low-signal profile is valid and should not be forced into recommendations.

## Historical data

The 1,390-record historical-derived benchmark is for:

- regression/sanity checks
- redundancy analysis
- distribution inspection

It must not be used to fit the new response weights, force reproduction of the historical live scoring algorithm, or claim clinical validation.
